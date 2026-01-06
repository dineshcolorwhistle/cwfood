<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use XeroAPI\XeroPHP\Api\AccountingApi;
use XeroAPI\XeroPHP\Configuration;
use Illuminate\Support\Facades\{DB,Log};
use App\Models\{XeroConnection,Client_contact,XeroInvoice,XeroInvoiceLine,XeroCreditNote,XeroCreditNoteLine,Client_company};

class XeroController extends Controller
{
    private $user_id;
    private $role_id;
    private $clientID;
    private $ws_id;

    public function __construct()
    {
        $this->user_id = session('user_id');
        $this->role_id = session('role_id');
        $this->clientID = session('client');
        $this->ws_id = session('workspace');
    } 

    /** Begin Xero OAuth */
    public function connect(Request $r)
    {
        if ($r->filled('return_to')) {
            session(['xero_return_to' => $r->get('return_to')]);
        }

        $scopes = config('services.xero.scopes');
        $scopeString = is_array($scopes) ? implode(' ', $scopes) : trim((string) $scopes);

        $query = http_build_query([
            'response_type' => 'code',
            'client_id'     => config('services.xero.client_id'),
            'redirect_uri'  => config('services.xero.redirect'),
            'scope'         => $scopeString,
            'state'         => csrf_token(),
        ]);
        return redirect()->away('https://login.xero.com/identity/connect/authorize?'.$query);
    }

    /** OAuth callback: token exchange + store tenant(s) */
    public function callback(Request $r)
    {
        abort_unless($r->filled('code'), 400, 'Missing code');
        if (!Auth::check()) {
            return redirect()->route('login')->withErrors('Session expired. Please log in and try again.');
        }

        $clientId = $this->clientID ?: (Auth::user()->client_id ?? null);
        abort_unless($clientId, 400, 'Missing client context');

        $tokenRes = Http::asForm()->post('https://identity.xero.com/connect/token', [
            'grant_type'    => 'authorization_code',
            'code'          => $r->get('code'),
            'redirect_uri'  => config('services.xero.redirect'),
            'client_id'     => config('services.xero.client_id'),
            'client_secret' => config('services.xero.client_secret'),
        ])->throw()->json();

        $accessToken  = $tokenRes['access_token'];
        $refreshToken = $tokenRes['refresh_token'];
        $expiresAt    = now()->addSeconds($tokenRes['expires_in']);

        $userInfo   = Http::withToken($accessToken)->get('https://identity.xero.com/connect/userinfo')->throw()->json();
        $xeroUserId = $userInfo['sub'] ?? null;

        $conns = Http::withToken($accessToken)->get('https://api.xero.com/connections')->throw()->json();

        foreach ($conns as $c) {
            XeroConnection::updateOrCreate(
                [
                    'user_id'   => $this->user_id,
                    'client_id' => $clientId,
                    'tenant_id' => $c['tenantId'] ?? null,
                ],
                [
                    'tenant_name'   => $c['tenantName'] ?? null,
                    'xero_user_id'  => $xeroUserId,
                    'access_token'  => $accessToken,
                    'refresh_token' => $refreshToken,
                    'expires_at'    => $expiresAt,
                ]
            );
        }

        $returnTo = session('xero_return_to') ?: route('client.integrations.show');
        session()->forget('xero_return_to');

        return redirect($returnTo)->with('status', 'Xero connected');
    }

    /** Build AccountingApi, auto-refreshing tokens when near expiry */
    protected function accountingApi(XeroConnection $conn): AccountingApi
    {
        if (now()->gte(Carbon::parse($conn->expires_at)->subMinutes(2))) {
            $refresh = Http::asForm()->post('https://identity.xero.com/connect/token', [
                'grant_type'    => 'refresh_token',
                'refresh_token' => $conn->refresh_token,
                'client_id'     => config('services.xero.client_id'),
                'client_secret' => config('services.xero.client_secret'),
            ])->throw()->json();

            $conn->update([
                'access_token'  => $refresh['access_token'],
                'refresh_token' => $refresh['refresh_token'] ?? $conn->refresh_token,
                'expires_at'    => now()->addSeconds($refresh['expires_in']),
            ]);
        }

        $config = Configuration::getDefaultConfiguration()->setAccessToken($conn->access_token);
        $client = new \GuzzleHttp\Client([
                    'timeout' => 180,   // request timeout in seconds
                    'connect_timeout' => 120, // connection phase timeout
                ]);
        return new AccountingApi($client, $config);
    }

    /** Parse Xero DateTime to UTC Carbon (supports /Date(ms)/ and normal strings) */
    private function toCarbonUtc($value): ?Carbon
    {
        if ($value instanceof \DateTimeInterface) return Carbon::instance($value)->utc();

        if (is_string($value)) {
            if (preg_match('/^\/Date\((\d+)([+-]\d{4})?\)\/$/', $value, $m)) {
                return Carbon::createFromTimestampMs((int)$m[1], 'UTC')->utc();
            }
            try { return Carbon::parse($value)->utc(); } catch (\Throwable) { return null; }
        }
        return null;
    }

    /** Parse Xero date (no time) to Y-m-d */
    private function parseXeroDate($value): ?string
    {
        if (!$value) return null;

        if ($value instanceof \DateTimeInterface) {
            return Carbon::instance($value)->toDateString();
        }
        if (is_string($value) && preg_match('/^\/Date\((\d+)(?:[+-]\d{4})?\)\/$/', $value, $m)) {
            return Carbon::createFromTimestampMs((int)$m[1], 'UTC')->toDateString();
        }
        try { return Carbon::parse($value)->toDateString(); } catch (\Throwable) { return null; }
    }

    public function syncSchedule()
    {
        $failedConnections = []; // track failed connections

        try {
            $connections = XeroConnection::get();
            if ($connections->isNotEmpty()) {
                foreach ($connections as $conn) {
                    try {
                        $api      = $this->accountingApi($conn);
                        $clientId = $conn->client_id;
                        $tenantId = $conn->tenant_id;
                        $forceFull = false;
                        $days      = 0;

                        foreach (['Contacts', 'Invoices', 'CreditNotes'] as $type) {
                            $method = "sync{$type}";
                            $result = $this->{$method}($clientId, $tenantId, $forceFull, $days, $api, $conn);

                            if (!$result['status']) {
                                $conn->forceFill(['last_synced_status' => 1])->save();
                                continue;
                            }
                        }

                    } catch (\Exception $e) {
                        $conn->forceFill(['last_synced_status' => 1])->save();
                        $failedConnections[] = [
                            'id' => $conn->id,
                            'client_id' => $conn->client_id,
                            'tenant_id' => $conn->tenant_id,
                            'error' => $e->getMessage()
                        ];
                        continue; // move to next connection
                    }
                }
                Log::info('Xero sync finished. Failed connections: ' . count($failedConnections));
            } else {
                Log::info('Connection was empty');
            }
            // send mail if failures found
            if (!empty($failedConnections)) {
                // try {
                //     Mail::to(config('mail.admin_email')) // keep this in config/env
                //         ->send(new XeroSyncFailed($failedConnections));
                // } catch (\Exception $mailException) {
                //     Log::error('Failed to send failure notification email: ' . $mailException->getMessage());
                // }
            }
            return response()->json(['status' => 'success']);
        } catch (\Exception $e) {
            Log::error('Xero sync global failure: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }


    public function syncXero(Request $r)
    {
        try {
            
            $clientId = $this->clientID ?: (Auth::user()->client_id ?? null);
            abort_unless($clientId, 400, 'Missing client context');

            $tenantId   = $r->input('tenant_id');
            $forceFull  = $r->boolean('full');
            $days       = env('XERO_FETCH_DAYS');
            $conn = XeroConnection::where('client_id', $clientId)->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))->firstOrFail();
            $api = $this->accountingApi($conn);
            foreach (['Contacts', 'Invoices', 'CreditNotes'] as $type) {
                $method = "sync{$type}";
                $result = $this->{$method}($clientId, $tenantId, $forceFull, $days, $api, $conn);
                if (!$result['status']) {
                    $conn->forceFill(['last_synced_status' => 1])->save();
                    return response()->json(['ok' => false, 'message' => $result['message']]);
                }
            }
            $conn->forceFill(['last_synced_status' => 0])->save();
            $message = $forceFull ? 'Full sync complete' : 'Delta sync complete'; 
            $status = true;
        } catch (\Exception $e) {
            $status  = false;
            $message = $e->getMessage();
            $conn->forceFill(['last_synced_status' => 1])->save();
        }

        if (request()->expectsJson()) {
            return response()->json([
                'ok'      => $status,
                'message' => $message ?? null
            ]);
        }
        return back()->with('status', $message ?? 'Unknown error');
    }

    /** Sync contacts (full or delta by last_synced_contacts_at) */
    public function syncContacts($clientId, $tenantId, $forceFull, $days, $api, $conn)
    {
        $contact_response = ['status' => false, 'message' => ''];
        try {
            abort_unless($clientId, 400, 'Missing client context');
            $modifiedSince = null;
            if (!$forceFull && $conn->last_synced_contacts_at) {
                $modifiedSince = $conn->last_synced_contacts_at->copy()->utc()->format('D, d M Y H:i:s') . ' GMT';
            }
            app()->instance('xero.syncing', true);

            $page = 1;
            $hasMore = true;
            $maxRetries = 5;

            while ($hasMore) {
                $attempt = 0;
                $success = false;

                // Retry loop for 429 Too Many Requests
                while (!$success && $attempt < $maxRetries) {
                    try {
                        $result = $api->getContacts(
                            $conn->tenant_id,
                            $modifiedSince,
                            null, null, null,
                            $page,
                            true,   // includeArchived
                            true,   // summaryOnly
                            null
                        );
                        $success = true;
                    } catch (\GuzzleHttp\Exception\ClientException $e) {
                        if ($e->getCode() == 429) {
                            $attempt++;
                            // Use Retry-After header if present
                            $retryAfter = 10; // default 10 seconds
                            if ($e->hasResponse() && $e->getResponse()->hasHeader('Retry-After')) {
                                $retryAfter = (int)$e->getResponse()->getHeader('Retry-After')[0];
                            }
                            sleep($retryAfter);
                        } else {
                            throw $e;
                        }
                    }
                }

                if (!$success) {
                    throw new \Exception("Failed to fetch Xero contacts after $maxRetries retries due to rate limit.");
                }

                $contacts = $result->getContacts() ?? [];

                foreach ($contacts as $xc) {
                    $xeroContactId = (string) $xc->getContactId();
                    $name          = trim((string) $xc->getName());
                    $first         = trim((string) $xc->getFirstName());
                    $last          = trim((string) $xc->getLastName());
                    $email         = trim((string) $xc->getEmailAddress());
                    $phones        = $xc->getPhones() ?: [];
                    $phone         = count($phones) ? trim((string) $phones[0]->getPhoneNumber()) : null;
                    $updatedUtc    = $xc->getUpdatedDateUTC();

                    $firstName = $first ?: ($name ? strtok($name, ' ') : null);
                    $lastName  = $last ?: ($name && $firstName ? trim(substr($name, strlen($firstName))) : null);

                    // Company create or fetch
                    $companyID = null;
                    if ($name) {
                        $clientCompany = Client_company::where('company_name', $name)
                                            ->where('client_id', $clientId)
                                            ->first();
                        if ($clientCompany) {
                            $companyID = $clientCompany->id;
                        } else {
                            $companyDetails = Client_company::create([
                                'company_name'   => $name,
                                'client_id'      => $clientId,
                                'source'         => 'Xero',
                                'xero_tenant_id' => $conn->tenant_id
                            ]);
                            $companyID = $companyDetails->id;
                        }
                    }

                    $parsedUpdatedAt = $updatedUtc
                        ? (
                            $updatedUtc instanceof \DateTimeInterface
                                ? Carbon::instance($updatedUtc)->utc()
                                : (preg_match('/^\/Date\((\d+)(?:[+-]\d+)?\)\/$/', $updatedUtc, $m)
                                    ? Carbon::createFromTimestampMs((int)$m[1], 'UTC')->utc()
                                    : Carbon::parse($updatedUtc)->utc()
                                )
                        )
                        : now()->utc();

                    // Lookup by Xero tenant & contact ID
                    $contact = Client_contact::where('client_id', $clientId)
                                ->where('xero_tenant_id', $conn->tenant_id)
                                ->where('xero_contact_id', $xeroContactId)
                                ->first();

                    // Lookup by client + company + email if not found
                    if (!$contact ) {
                        $contact = Client_contact::where('client_id', $clientId)
                                    ->where('company', $companyID)
                                    ->where('email', $email)
                                    ->first();
                    }

                    // Update or create
                    if ($contact) {
                        $contact->update([
                            'first_name'           => $firstName ?: $contact->first_name,
                            'last_name'            => $lastName ?: $contact->last_name,
                            'phone'                => $phone ?? $contact->phone,
                            'xero_updated_at_utc'  => $parsedUpdatedAt,
                            'xero_is_archived'     => $xc->getContactStatus() === 'ARCHIVED',
                            'source'               => 'Xero',
                            'company'              => $companyID ?? $contact->company,
                        ]);
                    } else {
                        Client_contact::create([
                            'client_id'           => $clientId,
                            'xero_tenant_id'      => $conn->tenant_id,
                            'xero_contact_id'     => $xeroContactId,
                            'first_name'          => $firstName ?: '',
                            'last_name'           => $lastName ?: '',
                            'email'               => $email?:null,
                            'phone'               => $phone,
                            'xero_updated_at_utc' => $parsedUpdatedAt,
                            'xero_is_archived'    => $xc->getContactStatus() === 'ARCHIVED',
                            'source'              => 'Xero',
                            'company'             => $companyID,
                        ]);
                    }
                }

                // Throttle between pages to avoid rate limits
                sleep(1);

                $page++;
                $hasMore = count($contacts) === 100;

            } while ($hasMore);

            $conn->forceFill(['last_synced_contacts_at' => now()])->save();
            $contact_response['status'] = true;
            Log::info("Contact sync Success");

        } catch (\Exception $e) {
            $contact_response['status'] = false;
            $contact_response['message'] = 'Contact sync failed: ' . $e->getMessage();
            Log::info('Contact sync failed: ' . $e->getMessage());
        } finally {
            app()->forgetInstance('xero.syncing');
        }

        return $contact_response;
    }

    /** Sync invoices (full or delta by last_synced_invoices_at) */
    public function syncInvoices($clientId, $tenantId, $forceFull, $days, $api, $conn)
    {
        abort_unless($clientId, 400, 'Missing client context');

        $invoice_response = ['status' => false, 'message' => ''];

        // Determine modifiedSince for delta or full sync
        $modifiedSince = null;
        if ($forceFull) {
            // "Full" = only fetch invoices updated in the last 122 days
            // $modifiedSince = null;
            $modifiedSince = now()->subDays(env('XERO_FETCH_DAYS', 122))->utc();
        } elseif ($conn->last_synced_invoices_at) {
            // Delta = fetch since last successful sync
            $modifiedSince = $conn->last_synced_invoices_at->copy()->utc();
        }


        app()->instance('xero.syncing', true);

        try {
            $page = 1;
            $hasMore = true;
            $maxRetries = 5;

            while ($hasMore) {
                $attempt = 0;
                $success = false;

                // Retry loop for 429 Too Many Requests
                while (!$success && $attempt < $maxRetries) {
                    try {
                        $result = $api->getInvoices(
                            $conn->tenant_id,
                            $modifiedSince,
                            null,
                            'Date DESC',
                            null,
                            null,
                            null,
                            null,
                            $page,
                            null,
                            null,
                            false
                        );
                        $success = true;
                    } catch (\GuzzleHttp\Exception\ClientException $e) {
                        if ($e->getCode() === 429) {
                            $attempt++;
                            $retryAfter = 10; // default 10s
                            if ($e->hasResponse() && $e->getResponse()->hasHeader('Retry-After')) {
                                $retryAfter = (int)$e->getResponse()->getHeader('Retry-After')[0];
                            }
                            sleep($retryAfter);
                        } else {
                            throw $e;
                        }
                    }
                }

                if (!$success) {
                    throw new \Exception("Failed to fetch Xero invoices after $maxRetries retries due to rate limit.");
                }

                $invoices = $result->getInvoices() ?? [];

                foreach ($invoices as $xi) {
                    try {
                        $xiInvoiceId   = method_exists($xi, 'getInvoiceID') ? $xi->getInvoiceID() : $xi->getInvoiceId();
                        $xeroInvoiceId = (string) $xiInvoiceId;

                        $contactObj    = $xi->getContact();
                        $xiContactId   = $contactObj
                            ? (method_exists($contactObj, 'getContactID') ? $contactObj->getContactID() : $contactObj->getContactId())
                            : null;
                        $xeroContactId = $xiContactId ? (string) $xiContactId : null;

                        $updatedAt     = $this->toCarbonUtc($xi->getUpdatedDateUTC()) ?? now()->utc();

                        $clientContact = null;
                        if ($xeroContactId) {
                            $clientContact = Client_contact::where([
                                'client_id'       => $clientId,
                                'xero_tenant_id'  => $conn->tenant_id,
                                'xero_contact_id' => $xeroContactId,
                            ])->first();
                        }

                        // Update or create invoice
                        $invoice = XeroInvoice::updateOrCreate(
                            [
                                'client_id'       => $clientId,
                                'xero_tenant_id'  => $conn->tenant_id,
                                'xero_invoice_id' => $xeroInvoiceId,
                            ],
                            [
                                'client_contact_id'   => $clientContact?->id,
                                'xero_contact_id'     => $xeroContactId,
                                'type'                => (string) $xi->getType(),
                                'status'              => (string) $xi->getStatus(),
                                'number'              => (string) $xi->getInvoiceNumber(),
                                'reference'           => (string) $xi->getReference(),
                                'date'                => $this->parseXeroDate($xi->getDate()),
                                'due_date'            => $this->parseXeroDate($xi->getDueDate()),
                                'currency_code'       => (string) $xi->getCurrencyCode(),
                                'sub_total'           => $xi->getSubTotal(),
                                'total_tax'           => $xi->getTotalTax(),
                                'total'               => $xi->getTotal(),
                                'amount_due'          => $xi->getAmountDue(),
                                'amount_paid'         => $xi->getAmountPaid(),
                                'amount_credited'     => $xi->getAmountCredited(),
                                'xero_updated_at_utc' => $updatedAt,
                                'xero_is_archived'    => (string)$xi->getStatus() === 'DELETED',
                                'source'              => 'Xero',
                            ]
                        );

                        // Sync line items
                        $keep  = [];
                        $lines = $xi->getLineItems() ?? [];
                        foreach ($lines as $li) {
                            $liIdObj = method_exists($li, 'getLineItemID') ? $li->getLineItemID() : $li->getLineItemId();
                            $lineId  = $liIdObj ? (string)$liIdObj : null;

                            $row = XeroInvoiceLine::updateOrCreate(
                                [
                                    'invoice_id'        => $invoice->id,
                                    'xero_line_item_id' => $lineId,
                                ],
                                [
                                    'item_code'     => (string)$li->getItemCode(),
                                    'description'   => (string)$li->getDescription(),
                                    'quantity'      => $li->getQuantity(),
                                    'unit_amount'   => $li->getUnitAmount(),
                                    'line_amount'   => $li->getLineAmount(),
                                    'account_code'  => (string)$li->getAccountCode(),
                                    'tax_type'      => (string)$li->getTaxType(),
                                    'tax_amount'    => $li->getTaxAmount(),
                                    'discount_rate' => $li->getDiscountRate(),
                                ]
                            );

                            $keep[] = $row->id;
                        }

                        // Remove deleted line items
                        if (count($keep)) {
                            XeroInvoiceLine::where('invoice_id', $invoice->id)
                                ->whereNotIn('id', $keep)
                                ->delete();
                        } else {
                            XeroInvoiceLine::where('invoice_id', $invoice->id)->delete();
                        }

                    } catch (\Throwable $e) {
                        // Log bad invoice but continue
                        Log::error("Failed to sync Xero invoice {$xi->getInvoiceNumber()}: ".$e->getMessage());
                    }
                }

                // Throttle between pages to avoid 429
                sleep(1);

                $page++;
                $hasMore = count($invoices) === 100;

            } // end while $hasMore

            $conn->forceFill(['last_synced_invoices_at' => now()])->save();
            $invoice_response['status'] = true;
            Log::info("Invoice sync Success");
        } catch (\Throwable $e) {
            $invoice_response['status'] = false;
            $invoice_response['message'] = 'Invoice sync failed: '.$e->getMessage();
            Log::error('Xero Invoice Sync Error: '.$e->getMessage());
        } finally {
            app()->forgetInstance('xero.syncing');
        }

        return $invoice_response;
    }

    /** Sync credit notes (full or delta by last_synced_credit_notes_at) */
    public function syncCreditNotes($clientId, $tenantId, $forceFull, $days, $api, $conn)
    {
        abort_unless($clientId, 400, 'Missing client context');
        $notes_response = ['status' => false, 'message' => ''];
        $modifiedSinceHeader = null;
        if ($forceFull) {
            // $modifiedSinceHeader = null;
            $modifiedSinceHeader = now()->subDays(env('XERO_FETCH_DAYS', 122))->utc()->toRfc7231String();
        } elseif ($conn->last_synced_credit_notes_at) {
            $modifiedSinceHeader = $conn->last_synced_credit_notes_at->copy()->utc()->toRfc7231String();
        }
        app()->instance('xero.syncing', true);

        try {
            $page = 1;
            $hasMore = true;
            $maxRetries = 5;

            while ($hasMore) {
                $attempt = 0;
                $success = false;

                while (!$success && $attempt < $maxRetries) {
                    try {
                        $req = Http::withToken($conn->access_token)
                            ->withHeaders([
                                'xero-tenant-id' => $conn->tenant_id,
                                'Accept'         => 'application/json',
                            ]);

                        if ($modifiedSinceHeader) {
                            $req = $req->withHeaders(['If-Modified-Since' => $modifiedSinceHeader]);
                        }

                        $payload = $req->get('https://api.xero.com/api.xro/2.0/CreditNotes', [
                            'order' => 'Date DESC',
                            'page'  => $page,
                        ])->throw()->json();

                        $success = true;
                    } catch (\Illuminate\Http\Client\RequestException $e) {
                        $attempt++;
                        if ($e->response && $e->response->status() === 429) {
                            $retryAfter = 10;
                            if ($e->response->hasHeader('Retry-After')) {
                                $retryAfter = (int)$e->response->header('Retry-After');
                            }
                            sleep($retryAfter);
                        } else {
                            throw $e;
                        }
                    }
                }

                if (!$success) {
                    throw new \Exception("Failed to fetch Xero credit notes after $maxRetries retries due to rate limit.");
                }

                $creditNotes = $payload['CreditNotes'] ?? [];

                foreach ($creditNotes as $cn) {
                    try {
                        $xeroCreditNoteId = (string) ($cn['CreditNoteID'] ?? $cn['CreditNoteId'] ?? '');
                        if (!$xeroCreditNoteId) continue;

                        $contactId = $cn['Contact']['ContactID'] ?? $cn['Contact']['ContactId'] ?? null;
                        if ($contactId) $contactId = (string) $contactId;

                        $clientContact = null;
                        if ($contactId) {
                            $clientContact = Client_contact::where([
                                'client_id'       => $clientId,
                                'xero_tenant_id'  => $conn->tenant_id,
                                'xero_contact_id' => $contactId,
                            ])->first();
                        }

                        $date      = $this->parseXeroDate($cn['Date'] ?? null);
                        $updatedAt = $this->toCarbonUtc($cn['UpdatedDateUTC'] ?? null) ?? now()->utc();

                        $subTotal  = $cn['SubTotal'] ?? null;
                        $totalTax  = $cn['TotalTax'] ?? null;
                        $total     = $cn['Total'] ?? null;

                        $applied = $cn['AppliedAmount']
                            ?? (!empty($cn['Allocations'])
                                ? round(array_sum(array_map(fn($a) => (float) ($a['AppliedAmount'] ?? $a['Amount'] ?? 0), $cn['Allocations'])), 2)
                                : null);

                        $remaining = $cn['RemainingCredit']
                            ?? (($total !== null && $applied !== null) ? round((float)$total - (float)$applied, 2) : null);

                        $credit = XeroCreditNote::updateOrCreate(
                            [
                                'client_id'           => $clientId,
                                'xero_tenant_id'      => $conn->tenant_id,
                                'xero_credit_note_id' => $xeroCreditNoteId,
                            ],
                            [
                                'client_contact_id'   => $clientContact?->id,
                                'xero_contact_id'     => $contactId,
                                'type'                => (string) ($cn['Type'] ?? ''),
                                'status'              => (string) ($cn['Status'] ?? ''),
                                'number'              => (string) ($cn['CreditNoteNumber'] ?? ''),
                                'reference'           => (string) ($cn['Reference'] ?? ''),
                                'date'                => $date,
                                'currency_code'       => (string) ($cn['CurrencyCode'] ?? ''),
                                'sub_total'           => $subTotal,
                                'total_tax'           => $totalTax,
                                'total'               => $total,
                                'amount_applied'      => $applied,
                                'remaining_credit'    => $remaining,
                                'xero_updated_at_utc' => $updatedAt,
                                'xero_is_archived'    => (($cn['Status'] ?? '') === 'DELETED'),
                                'source'              => 'Xero',
                            ]
                        );

                        $keep = [];
                        $lines = $cn['LineItems'] ?? [];
                        foreach ($lines as $li) {
                            $lineId = $li['LineItemID'] ?? $li['LineItemId'] ?? null;

                            $row = XeroCreditNoteLine::updateOrCreate(
                                [
                                    'credit_note_id'    => $credit->id,
                                    'xero_line_item_id' => $lineId,
                                ],
                                [
                                    'item_code'     => (string) ($li['ItemCode'] ?? ''),
                                    'description'   => (string) ($li['Description'] ?? ''),
                                    'quantity'      => $li['Quantity'] ?? null,
                                    'unit_amount'   => $li['UnitAmount'] ?? null,
                                    'line_amount'   => $li['LineAmount'] ?? null,
                                    'account_code'  => (string) ($li['AccountCode'] ?? ''),
                                    'tax_type'      => (string) ($li['TaxType'] ?? ''),
                                    'tax_amount'    => $li['TaxAmount'] ?? null,
                                    'discount_rate' => $li['DiscountRate'] ?? null,
                                ]
                            );

                            $keep[] = $row->id;
                        }

                        if (count($keep)) {
                            XeroCreditNoteLine::where('credit_note_id', $credit->id)
                                ->whereNotIn('id', $keep)
                                ->delete();
                        } else {
                            XeroCreditNoteLine::where('credit_note_id', $credit->id)->delete();
                        }

                    } catch (\Throwable $e) {
                        Log::error("Failed to sync Xero credit note: ".$e->getMessage());
                    }
                }

                sleep(1); // throttle between pages
                $page++;
                $hasMore = count($creditNotes) === 100;

            } // end while $hasMore

            $conn->forceFill(['last_synced_credit_notes_at' => now()])->save();
            $notes_response['status'] = true;
            Log::info("Credit notes sync Success");
        } catch (\Throwable $e) {
            $notes_response['status'] = false;
            $notes_response['message'] = 'Credit note sync failed: '.$e->getMessage();
            Log::error('Xero Credit Note Sync Error: '.$e->getMessage());
        } finally {
            app()->forgetInstance('xero.syncing');
        }
        return $notes_response;
    }


    /** Disconnect (scoped to current client) */
    public function disconnect(Request $r)
    {
        $clientId = $this->clientID ?: (Auth::user()->client_id ?? null);
        abort_unless($clientId, 400, 'Missing client context');

        $tenantId = $r->input('tenant_id');
        abort_unless($tenantId, 400, 'Missing tenant');

        // Ensure the connection belongs to the current user/client
        $conn = XeroConnection::where('client_id', $clientId)
            ->where('tenant_id', $tenantId)
            ->firstOrFail();

        DB::transaction(function () use ($clientId, $tenantId) {
            // Invoices + lines
            $invoiceIds = XeroInvoice::where('client_id', $clientId)
                ->where('xero_tenant_id', $tenantId)
                ->pluck('id');

            if ($invoiceIds->isNotEmpty()) {
                XeroInvoiceLine::whereIn('invoice_id', $invoiceIds)->delete();
                XeroInvoice::whereIn('id', $invoiceIds)->delete();
            }

            // Credit notes + lines
            $creditIds = XeroCreditNote::where('client_id', $clientId)
                ->where('xero_tenant_id', $tenantId)
                ->pluck('id');

            if ($creditIds->isNotEmpty()) {
                XeroCreditNoteLine::whereIn('credit_note_id', $creditIds)->delete();
                XeroCreditNote::whereIn('id', $creditIds)->delete();
            }

            // Contacts
            Client_contact::where('client_id', $clientId)
                ->where('xero_tenant_id', $tenantId)
                ->delete();

            // Companies
            Client_company::where('client_id', $clientId)
                ->where('xero_tenant_id', $tenantId)
                ->delete(); 

            // Connection
            XeroConnection::where('client_id', $clientId)
                ->where('tenant_id', $tenantId)
                ->delete();
        });

        return back()->with('status', 'Xero disconnected and tenant data deleted.');
    }
}