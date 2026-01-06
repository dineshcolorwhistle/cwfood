<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class XeroSalesPerformanceController extends Controller
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

    public function index(Request $request)
    {
        return view('backend.analytics.partials.sales_performance', [
            'pageTitle' => 'Sales Performance'
        ]);
    }
    /**
     * Data feed:
     * - Returns invoice lines (INVOICE) + credit note lines (CREDIT, amounts/qty negated)
     * - SALES only: Invoices.type='ACCREC' & CreditNotes.type='ACCRECCREDIT'
     * - Requires tenant. If not given, use first connection for (client,user).
     * - Date defaults to prior full month.
     * - Union-safe collation on text columns.
     */
    public function data(Request $request)
    {
        $clientId = $this->clientID;
        $userId   = $this->user_id;

        // Tenant
        $tenantId = $request->input('tenant_id');
        if (!$tenantId) {
            $conn = \App\Models\XeroConnection::where('client_id', $clientId)->first();
            if ($conn) $tenantId = $conn->tenant_id;
        }
        if (!$tenantId) return response()->json([]);

        // Date range
        $from = $request->input('from');
        $to   = $request->input('to');
        if (!$from || !$to) {
            $today = now()->startOfDay();
            $end   = $today->copy()->startOfMonth()->subDay(); // last day of prior month
            $start = $end->copy()->startOfMonth();
            $from  = $from ?: $start->toDateString();
            $to    = $to   ?: $end->toDateString();
        }
        if ($from > $to) { [$from, $to] = [$to, $from]; }

        // Normalize strings for UNION
        $uc = fn ($expr) => "CONVERT($expr USING utf8mb4) COLLATE utf8mb4_unicode_ci";

        $contactNameExpr = $uc("
            TRIM(
                CASE
                    WHEN CHAR_LENGTH(
                        TRIM(CONCAT_WS(' ', COALESCE(cc.first_name,''), COALESCE(cc.last_name,'')))
                    ) > 0 THEN TRIM(CONCAT_WS(' ', COALESCE(cc.first_name,''), COALESCE(cc.last_name,'')))
                    WHEN cc.company IS NOT NULL AND cc.company <> '' THEN cc.company
                    ELSE NULL
                END
            )
        ");

        // INVOICES (ACCREC)
        $inv = DB::table('xero_invoice_lines as xil')
            ->join('xero_invoices as xi', 'xil.invoice_id', '=', 'xi.id')
            ->leftJoin('client_contacts as cc', function ($j) use ($clientId) {
                $j->on('cc.id', '=', 'xi.client_contact_id')->where('cc.client_id', $clientId);
            })
            ->leftJoin('client_companies as ccom', 'ccom.id', '=', 'cc.company')
            ->where('xi.client_id', $clientId)
            ->where('xi.xero_tenant_id', $tenantId)
            ->where('xi.type', 'ACCREC')
            ->whereBetween('xi.date', [$from, $to])
            ->whereNotIn('xi.status', ['VOIDED','DELETED','DRAFT'])
            ->select([
                DB::raw("'INVOICE' COLLATE utf8mb4_unicode_ci as Types"),
                DB::raw($uc('xi.number')        . ' as InvoiceNumber'),
                DB::raw($uc('xi.reference')     . ' as Reference'),
                DB::raw($uc('xi.status')        . ' as Status'),
                'xi.date                         as Date',
                'xi.due_date                     as DueDate',
                'xi.total                        as Total',
                'xi.amount_paid                  as AmountPaid',
                'xi.amount_due                   as AmountDue',
                'xi.amount_credited              as AmountCredited',
                'xi.sub_total                    as SubTotal',
                'xi.total_tax                    as TotalTax',
                DB::raw($uc('xi.currency_code') . ' as CurrencyCode'),
                'xi.xero_updated_at_utc          as UpdatedDateUTC',
                DB::raw($uc('xi.xero_tenant_id'). ' as TenantId'),
                // DB::raw("$contactNameExpr as ContactName"),
                DB::raw($uc('ccom.company_name') . ' as ContactName'),
                DB::raw($uc('xil.description')  . ' as LineItem_Description'),
                'xil.quantity                    as LineItem_Quantity',
                'xil.unit_amount                 as LineItem_UnitAmount',
                DB::raw($uc('xil.item_code')    . ' as LineItem_ItemCode'),
                DB::raw($uc('xil.account_code') . ' as LineItem_AccountCode'),
                DB::raw($uc('xil.tax_type')     . ' as LineItem_TaxType'),
                'xil.tax_amount                  as LineItem_TaxAmount',
                'xil.line_amount                 as LineItem_LineAmount',
            ]);

        

        // CREDIT NOTES (ACCRECCREDIT)
        $cred = DB::table('xero_credit_note_lines as xcnl')
            ->join('xero_credit_notes as xcn', 'xcnl.credit_note_id', '=', 'xcn.id')
            ->leftJoin('client_contacts as cc', function ($j) use ($clientId) {
                $j->on('cc.id', '=', 'xcn.client_contact_id')->where('cc.client_id', $clientId);
            })
            ->leftJoin('client_companies as ccom', 'ccom.id', '=', 'cc.company')
            ->where('xcn.client_id', $clientId)
            ->where('xcn.xero_tenant_id', $tenantId)
            ->where('xcn.type', 'ACCRECCREDIT')
            ->whereBetween('xcn.date', [$from, $to])
            ->whereNotIn('xcn.status', ['VOIDED','DELETED','DRAFT'])
            ->select([
                DB::raw("'CREDIT' COLLATE utf8mb4_unicode_ci as Types"),
                DB::raw($uc('xcn.number')        . ' as InvoiceNumber'),
                DB::raw($uc('xcn.reference')     . ' as Reference'),
                DB::raw($uc('xcn.status')        . ' as Status'),
                'xcn.date                         as Date',
                DB::raw('xcn.date                  as DueDate'),
                'xcn.total                        as Total',
                DB::raw('xcn.amount_applied        as AmountPaid'),
                DB::raw('xcn.remaining_credit      as AmountDue'),
                DB::raw('0                         as AmountCredited'),
                'xcn.sub_total                    as SubTotal',
                'xcn.total_tax                    as TotalTax',
                DB::raw($uc('xcn.currency_code') . ' as CurrencyCode'),
                'xcn.xero_updated_at_utc          as UpdatedDateUTC',
                DB::raw($uc('xcn.xero_tenant_id'). ' as TenantId'),
                // DB::raw("$contactNameExpr as ContactName"),
                DB::raw($uc('ccom.company_name') . ' as ContactName'),
                DB::raw($uc('xcnl.description')  . ' as LineItem_Description'),
                DB::raw('-(xcnl.quantity)         as LineItem_Quantity'),
                'xcnl.unit_amount                 as LineItem_UnitAmount',
                DB::raw($uc('xcnl.item_code')    . ' as LineItem_ItemCode'),
                DB::raw($uc('xcnl.account_code') . ' as LineItem_AccountCode'),
                DB::raw($uc('xcnl.tax_type')     . ' as LineItem_TaxType'),
                DB::raw('-(xcnl.tax_amount)       as LineItem_TaxAmount'),
                DB::raw('-(xcnl.line_amount)      as LineItem_LineAmount'),
            ]);
        $rows = $inv->unionAll($cred)->orderBy('Date', 'asc')->get();
        return response()->json($rows);
    }



    public function customer(Request $request)
    {
        $clientId = $this->clientID;
        $userId   = $this->user_id;
        // Tenant
        $tenantId = $request->input('tenant_id');
        if (!$tenantId) {
            $conn = \App\Models\XeroConnection::where('client_id', $clientId)->first();
            if ($conn) $tenantId = $conn->tenant_id;
        }
        if (!$tenantId) return response()->json([]);
        
        // Date range
        $from = $request->input('from');
        $to   = $request->input('to');
        if (!$from || !$to) {
            $today = now()->startOfDay();
            $end   = $today->copy()->startOfMonth()->subDay(); // last day of prior month
            $start = $end->copy()->startOfMonth();
            $from  = $from ?: $start->toDateString();
            $to    = $to   ?: $end->toDateString();
        }
        if ($from > $to) { [$from, $to] = [$to, $from]; }

        // INVOICES (ACCREC)
        $invCount = DB::table('xero_invoice_lines as xil')
            ->join('xero_invoices as xi', 'xil.invoice_id', '=', 'xi.id')
            ->where('xi.client_id', $clientId)
            ->where('xi.xero_tenant_id', $tenantId)
            ->where('xi.type', 'ACCREC')
            ->whereBetween('xi.date', [$from, $to])
            ->whereNotIn('xi.status', ['VOIDED','DELETED','DRAFT'])
            ->distinct('xi.id')->count('xi.id');

        return response()->json([
            'totalContact' => DB::table('client_contacts')->where('client_id', $clientId)->where('xero_tenant_id', $tenantId)->count(),
            'totalInvoice' => $invCount
        ]);
    }


    public function options(Request $request)
    {
        $clientId = (int) session('client');
        // $userId   = (int) session('user_id');

        $tenants = \App\Models\XeroConnection::query()            
            ->where('client_id', $clientId)
            ->orderByRaw("COALESCE(NULLIF(tenant_name,''), tenant_id)")
            ->get(['tenant_id', 'tenant_name'])
            ->map(fn ($r) => [
                'id'   => $r->tenant_id,
                'name' => $r->tenant_name ?: $r->tenant_id,
            ])->values();

        return response()->json(['tenants' => $tenants]);
    }
}