<?php

namespace App\Services;

use Stripe\Stripe;
use Stripe\Price;
use Stripe\Subscription;
use Stripe\Invoice;
use Stripe\Customer;
use Illuminate\Support\Facades\Log;
use App\Models\Member;
use App\Models\Client_billing;

class SubscriptionService
{
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    public function updateSubscriptionPlan($plan, string $period, $billing, int $clientId): array
    {
        try {
            if (!$billing?->subscription_id || !$billing?->stripe_customer_id) {
                return ['status' => false, 'message' => 'Stripe customer or subscription not found'];
            }

            /* ---------------------------------------------------
             * 1. Find correct Stripe price
             * --------------------------------------------------- */
            $matchedPrice = $this->findStripePriceForPlan($plan, $period);
            if (!$matchedPrice) {
                return ['status' => false, 'message' => 'Stripe price not found'];
            }

            /* ---------------------------------------------------
             * 2. Load subscription info
             * --------------------------------------------------- */
            $subscription = Subscription::retrieve([
                'id' => $billing->subscription_id,
                'expand' => ['items.data.price', 'items.data.price.product'],
            ]);

            $item = $subscription->items->data[0] ?? null;
            if (!$item) {
                return ['status' => false, 'message' => 'Subscription item missing'];
            }

            $currentInterval = strtolower($item->price->recurring->interval);
            $targetInterval  = strtolower($matchedPrice['interval']);

            $currentPlanName = strtolower($billing->subscription_plan ?? $item->price->product->name);
            $targetPlanName  = strtolower($plan->subscription_name);

            // Cancel flag reset
            if ($subscription->cancel_at_period_end) {
                Subscription::update($subscription->id, ['cancel_at_period_end' => false]);
            }

            /* ---------------------------------------------------
             * 3. Calculate quantity
             * --------------------------------------------------- */
            $quantity = $this->calculateQuantity($clientId, $plan);

            /* ---------------------------------------------------
             * 4. Determine change type
             * --------------------------------------------------- */
            $currentAmount = $item->price->unit_amount ?? 0;
            $targetAmount = ($matchedPrice['amount'] ?? 0) * 100; // Convert to cents
            
            // Downgrade: year→month OR same interval with lower price
            $isDowngrade = ($currentInterval === 'year' && $targetInterval === 'month') ||
                           ($currentInterval === $targetInterval && $targetAmount < $currentAmount);
            
            // Interval change (month↔year)
            $isIntervalChange = ($currentInterval !== $targetInterval);

            Log::info("Subscription change type", [
                'current_interval' => $currentInterval,
                'target_interval' => $targetInterval,
                'current_amount' => $currentAmount,
                'target_amount' => $targetAmount,
                'is_downgrade' => $isDowngrade,
                'is_interval_change' => $isIntervalChange
            ]);

            /* ---------------------------------------------------
             * 5. Apply plan change
             * Use 'always_invoice' for upgrades to create immediate invoice
             * Use 'none' for downgrades (change at period end)
             * --------------------------------------------------- */
            $updateParams = [
                'items' => [
                    [
                        'id'       => $item->id,
                        'price'    => $matchedPrice['price_id'],
                        'quantity' => $quantity,
                    ]
                ],
                'payment_behavior' => 'default_incomplete',
            ];

            if ($isDowngrade) {
                // Downgrade: no proration, change takes effect at period end
                $updateParams['proration_behavior'] = 'none';
            } else {
                // Upgrade: create immediate proration invoice
                $updateParams['proration_behavior'] = 'always_invoice';
                
                // For interval upgrades (month→year): reset billing cycle
                if ($isIntervalChange && $currentInterval === 'month' && $targetInterval === 'year') {
                    $updateParams['billing_cycle_anchor'] = 'now';
                }
            }

            $updatedSub = Subscription::update($subscription->id, $updateParams);

            /* ---------------------------------------------------
             * 6. Handle invoice - Auto behavior with $0 protection
             * --------------------------------------------------- */
            if ($updatedSub->latest_invoice) {
                $invoice = Invoice::retrieve($updatedSub->latest_invoice, ['expand' => ['payment_intent', 'charge']]);

                Log::info("Invoice details after subscription update", [
                    'invoice_id'   => $invoice->id,
                    'status'       => $invoice->status,
                    'amount_due'   => $invoice->amount_due,
                    'amount_paid'  => $invoice->amount_paid,
                    'total'        => $invoice->total,
                    'subtotal'     => $invoice->subtotal,
                ]);

                // Minimum threshold to generate an invoice (100 cents = $1)
                $minimumInvoiceAmount = 100; // cents

                try {
                    // Stop auto-finalize/auto-pay while we inspect
                    if ($invoice->status === 'draft' && ($invoice->auto_advance ?? true) === true) {
                        $invoice = Invoice::update($invoice->id, ['auto_advance' => false]);
                    }

                    $amountDue = $invoice->amount_due ?? 0;

                    /* ---------------------------------------------------
                     * Case A: Below threshold ($0 or tiny) → delete/void
                     * --------------------------------------------------- */
                    if ($amountDue < $minimumInvoiceAmount) {
                        $this->notify($clientId, "Your subscription was updated. No payment required.");
                        $this->deleteOrVoidInvoice($invoice);

                        Log::info("Invoice removed - below minimum threshold", [
                            'invoice_id' => $invoice->id,
                            'amount_due' => $amountDue,
                            'threshold'  => $minimumInvoiceAmount
                        ]);
                        return ['status' => true, 'message' => 'Subscription updated (no charge)'];
                    }

                    /* ---------------------------------------------------
                     * Case B: Real amount → finalize then pay
                     * Credit is auto-applied first; remainder hits card
                     * --------------------------------------------------- */
                    if ($invoice->status === 'draft') {
                        $invoice = $invoice->finalizeInvoice();
                    }

                    if ($invoice->status === 'open') {
                        $invoice->pay();
                    }

                    // Re-retrieve to inspect how it was paid
                    $paidInvoice = Invoice::retrieve($invoice->id, ['expand' => ['charge']]);

                    if ($paidInvoice->charge !== null) {
                        // Card charged (after credit applied)
                        $this->notify($clientId, "Subscription updated. Payment charged to your card.");
                        Log::info("Invoice paid - Card charged", [
                            'invoice_id'   => $paidInvoice->id,
                            'charge_id'    => $paidInvoice->charge,
                            'amount_paid'  => $paidInvoice->amount_paid
                        ]);
                    } else {
                        // Fully paid via credit balance
                        $this->notify($clientId, "Subscription updated. Paid from your account credit.");
                        Log::info("Invoice paid via credit only", [
                            'invoice_id'   => $paidInvoice->id,
                            'amount_paid'  => $paidInvoice->amount_paid
                        ]);
                    }
                } catch (\Exception $paymentError) {
                    $this->notify($clientId,
                        "Payment failed for your subscription update. Please update your payment method."
                    );

                    Log::warning("Payment failed", [
                        'invoice_id' => $invoice->id,
                        'error' => $paymentError->getMessage()
                    ]);
                }
            }

            /* ---------------------------------------------------
             * 7. Update DB Billing Info
             * --------------------------------------------------- */
            Client_billing::where('client_id', $clientId)
                ->update([
                    'price_id'          => $matchedPrice['price_id'],
                    'subscription_plan' => $matchedPrice['product_name']
                ]);

            return ['status' => true, 'message' => 'Subscription updated successfully'];

        } catch (\Throwable $e) {

            Log::error("Subscription update failed", [
                'clientId' => $clientId,
                'error' => $e->getMessage()
            ]);

            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    /* ---------------------------------------------------------
     * Support Methods
     * --------------------------------------------------------- */

    protected function findStripePriceForPlan($plan, string $period): ?array
    {
        $interval = $period === 'annual' ? 'year' : 'month';
        $planName = strtolower($plan->subscription_name);

        $prices = Price::all(['limit' => 100, 'expand' => ['data.product']]);

        foreach ($prices->data as $price) {
            if (!isset($price->recurring)) continue;

            if (
                strtolower($price->product->name) === $planName &&
                strtolower($price->recurring->interval) === $interval
            ) {
                return [
                    'product_name' => $price->product->name,
                    'price_id'     => $price->id,
                    'amount'       => $price->unit_amount / 100,
                    'interval'     => $interval,
                ];
            }
        }

        return null;
    }

    protected function calculateQuantity(int $clientId, $plan): int
    {
        return max(Member::where('client_id', $clientId)->count(), $plan->min_users ?? 1);
    }

    protected function notify($clientId, $message)
    {
        Log::info("Notify {$clientId}: {$message}");
        // Integrate email/slack/push notification here
    }

    /**
     * Delete or void an invoice based on its status
     * Draft invoices can be deleted, open invoices can be voided
     * Paid invoices cannot be modified but $0 paid invoices don't send receipts
     */
    protected function deleteOrVoidInvoice($invoice): void
    {
        try {
            // Draft invoices can be deleted entirely
            if ($invoice->status === 'draft') {
                $invoice->delete();
                Log::info("Invoice deleted (draft)", ['invoice_id' => $invoice->id]);
                return;
            }
            
            // Open invoices can be voided
            if ($invoice->status === 'open') {
                $invoice->voidInvoice();
                Log::info("Invoice voided (open)", ['invoice_id' => $invoice->id]);
                return;
            }
            
            // Paid invoices with $0 - can't void but no receipt is sent for $0 charges
            if ($invoice->status === 'paid') {
                Log::info("Invoice already paid - $0 invoices don't generate receipts", [
                    'invoice_id' => $invoice->id,
                    'amount_paid' => $invoice->amount_paid
                ]);
                return;
            }

            // Other statuses (void, uncollectible) - already handled
            Log::info("Invoice status already final", [
                'invoice_id' => $invoice->id,
                'status' => $invoice->status
            ]);

        } catch (\Throwable $e) {
            Log::warning("Could not delete/void invoice", [
                'invoice_id' => $invoice->id,
                'status' => $invoice->status ?? 'unknown',
                'error' => $e->getMessage()
            ]);
        }
    }
}
