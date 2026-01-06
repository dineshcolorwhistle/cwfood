<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\{Client,ClientSubscription,SubscriptionPlan,Member,Ingredient,Product,Workspace,Client_billing};
use Stripe\Stripe;
use Stripe\Customer;
use Stripe\Invoice;
use Stripe\PaymentMethod;
use Stripe\Subscription as StripeSubscription;
use Illuminate\Support\Facades\Log;
use Stripe\Webhook;
use Stripe\Exception\SignatureVerificationException;


class ClientBillingController extends Controller
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

        // ✅ Set Stripe API key once for all methods
        \Stripe\Stripe::setApiKey(config('services.stripe.secret'));
    }

    /**
     * Show the client's current subscription with plan details
     */
    public function show(Request $request)
    {
        $clientID = $this->clientID;
        $subscription = ClientSubscription::with(['plan'])->where('client_id', $clientID)->first();
        $remainingDays = 0;
        if($subscription){   
            $start_date = Carbon::parse(now()->toDateString());
            $end_date = Carbon::parse($subscription->end_date);
            $remainingDays = $start_date->diffInDays($end_date);
        }  
        $billing = Client_billing::where('client_id', $clientID)->first();

        $customerID = $billing->stripe_customer_id??null;
        
        /**
         * Payment Method Details
         */
        $paymentMethod = null;
        if (!empty($billing) && !empty($billing->payment_method_id)) {
            $paymentMethod = \Stripe\PaymentMethod::retrieve($billing->payment_method_id);
        }
        /**
         * Billing History Details
        */
        $billing_history = [];
        if (!empty($billing)) {
            $params = ['customer' => $billing->stripe_customer_id, 'limit' => 100];
            $invoices = Invoice::all($params);
            foreach ($invoices->data as $invoice) {
                // Expand line items to access product name
                $invoiceWithLines = \Stripe\Invoice::retrieve($invoice->id, [
                    'expand' => ['lines.data.price.product'],
                ]);
                $productName = null;
                if (!empty($invoiceWithLines->lines->data)) {
                    // Assuming each invoice has at least one line item
                    $lineItem = end($invoiceWithLines->lines->data);
                    if (!empty($lineItem->pricing->price_details->product)) {
                        // Retrieve the product details using the ID
                        $product = \Stripe\Product::retrieve($lineItem->pricing->price_details->product);
                        $productName = $product->name ?? null;
                    }
                }
                $cardBrand = $paymentMethod->card->brand ?? null;
                $billing_history[] = [
                    'invoice_id' => $invoice->id,
                    'amount' => $invoice->amount_paid / 100,
                    'currency' => strtoupper($invoice->currency),
                    'status' => $invoice->status,
                    'paid_at' => $invoice->status_transitions->paid_at 
                                    ? date('Y-m-d', $invoice->status_transitions->paid_at) 
                                    : null,
                    'invoice_pdf' => $invoice->hosted_invoice_url,
                    'card_brand' => $cardBrand,
                    'product_name' => $productName
                ];
            }
        }
        
        return view('backend.client_billing.manage', compact('clientID','subscription','paymentMethod','billing_history','remainingDays','customerID'));
    }

    public function saveCard(Request $request)
    {
        try {
            $paymentMethodId = $request->payment_method;
            $email = $request->email;
            $clientDetails = Client::findOrfail($this->clientID);
            $name = $clientDetails->name;

            // ✅ Validation (recommended)
            if (!$paymentMethodId || !$email) {
                return response()->json([
                    'success' => false,
                    'message' => 'Missing required fields.',
                ], 422);
            }

            // ✅ Create Stripe Customer
            $customer = \Stripe\Customer::create([
                'name' => $name,
                'email' => $email,
            ]);

            // ✅ Attach Payment Method to Customer
            $paymentMethod = \Stripe\PaymentMethod::retrieve($paymentMethodId);
            $paymentMethod->attach(['customer' => $customer->id]);

            // ✅ Set Default Payment Method
            \Stripe\Customer::update($customer->id, [
                'invoice_settings' => [
                    'default_payment_method' => $paymentMethodId,
                ],
            ]);

            // ✅ Save to your database
            $billing_data = [
                'client_id' => $this->clientID, // ensure $this->clientID is available
                'stripe_customer_id' => $customer->id,
                'payment_method_id' => $paymentMethodId,
            ];

            Client_billing::create($billing_data);
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function GetCard(){
        return view('backend.client_billing.card-update');
    }

    public function UpdateCard(Request $request)
    {
        try {
            $paymentMethodId = $request->payment_method;

            // ✅ Validation
            if (!$paymentMethodId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Missing required fields.',
                ], 422);
            }

            $billing = Client_billing::where('client_id', $this->clientID)->first();

            if (!$billing) {
                return response()->json([
                    'success' => false,
                    'message' => 'Billing information not found.',
                ], 404);
            }

            $stripeCustomerId = $billing->stripe_customer_id;

            // ✅ Retrieve the new payment method
            $paymentMethod = \Stripe\PaymentMethod::retrieve($paymentMethodId);

            // ✅ Detach existing payment method (if any)
            if ($billing->payment_method_id && $billing->payment_method_id !== $paymentMethodId) {
                $existingPaymentMethod = \Stripe\PaymentMethod::retrieve($billing->payment_method_id);
                $existingPaymentMethod->detach();
            }

            // ✅ Attach the new payment method to the customer
            $paymentMethod->attach(['customer' => $stripeCustomerId]);

            // ✅ Set the new default payment method
            \Stripe\Customer::update($stripeCustomerId, [
                'invoice_settings' => [
                    'default_payment_method' => $paymentMethodId,
                ],
            ]);

            // ✅ Save the new payment method in your database
            $billing->update([
                'payment_method_id' => $paymentMethodId
            ]);

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }


    public function createSubscription($plan,$period,$client){
        try {
            $plan_name = $plan->subscription_name;
            $period = ($period == 'annual')? 'year': 'month';
            $products = \Stripe\Product::all(['limit' => 100]);
            $matched_price = null;

            foreach ($products->data as $product) {
                if (strtolower($product->name) === strtolower($plan_name)) {
                    // Get prices for this product
                    $prices = \Stripe\Price::all(['product' => $product->id]);

                    foreach ($prices->data as $price) {
                        if (
                            isset($price->recurring) &&
                            strtolower($price->recurring->interval) === strtolower($period)
                        ) {
                            $matched_price = [
                                'product_name' => $product->name,
                                'price_id'     => $price->id,
                                'amount'       => $price->unit_amount / 100,
                                'currency'     => strtoupper($price->currency),
                                'interval'     => $price->recurring->interval,
                            ];
                            break 2; // Stop looping once match found
                        }
                    }
                }
            }

            if ($matched_price) {
                $billing = Client_billing::where('client_id', $this->clientID)->first();
                $appMemberCount = Member::where('client_id', $this->clientID)->count();
                $plan_minuser = $plan->min_users ?? 1;
                $quantity = max($appMemberCount, $plan_minuser);

                $customerDiscountPercent = $client->discount ?? 0; // example field
                // 🏷️ Create coupon only if discount > 0
                $couponId = null;
                $discounts = [];
                if ($customerDiscountPercent > 0) {
                    $coupon = \Stripe\Coupon::create([
                        'percent_off' => $customerDiscountPercent,
                        'duration'    => 'forever', // or 'once'
                        'name'        => 'Customer Discount ',
                    ]);

                    $discounts[] = ['coupon' => $coupon->id];
                }


                // Create subscription in Stripe
                $subscriptionData = [
                    'customer' => $billing->stripe_customer_id,
                    'items' => [[
                        'price' => $matched_price['price_id'],
                        'quantity' => $quantity,
                    ]],
                    'expand' => ['latest_invoice.payment_intent'],
                ];

                if (!empty($discounts)) {
                    $subscriptionData['discounts'] = $discounts;
                }

                $stripeSubscription = StripeSubscription::create($subscriptionData);
                $subscription_data = [                                
                                        'subscription_id' => $stripeSubscription->id,
                                        'price_id' => $matched_price['price_id'],
                                        'subscription_plan' => $matched_price['product_name']
                                    ];
                Client_billing::where('client_id', $this->clientID)->update($subscription_data);
                $response['status'] = true;
            } else {
                $response['status'] = false;
                $response['message'] = "No matching price found for {$plan_name} ({$period})";
            }
        } catch (\Exception $e) {
            $response['status'] = false;
            $response['message'] = $e->getMessage();
        }
        return $response;
    }

     /**
     * Update the subscription mid-cycle (e.g., user count change).
     * Stripe automatically prorates unless you tell it not to.
     */
    public function updateSubscription($billing,$userCount)
    {
        try {
            // Retrieve subscription from DB (store subscription_id when created)
            $subscriptionId = $billing->subscription_id;

            $subscription = StripeSubscription::retrieve($subscriptionId);

            // Get the subscription item ID (for updating quantity)
            $item = $subscription->items->data[0];
            $itemId = $item->id;
            $currentQty = $item->quantity;

            // Default: No invoice (for qty decrease or same count)
            $prorationBehavior = 'none';

            // Determine behavior based on count
            if ($userCount > $currentQty) {
                // When count increases, create invoice & charge
                $prorationBehavior = 'always_invoice';
            } else {
                // When same or lower, just update silently (no charge)
                $prorationBehavior = 'none';
            }

            // Update quantity — Stripe will prorate by default
            $updatedSubscription = StripeSubscription::update($subscriptionId, [
                'items' => [[
                    'id' => $itemId,
                    'quantity' => $userCount,
                ]],
                'proration_behavior' => $prorationBehavior
            ]);
           $response['status'] = true;
        } catch (\Exception $e) {
            $response['status'] = false;
            $response['message'] = $e->getMessage();
        }
        return $response;
    }

    /**
     * Get Subscription Details
     */
    public function getSubscriptionDetails($subscriptionId){
        return StripeSubscription::retrieve($subscriptionId);
    }


    /**
     * Update subscription plan
     */

    
//     public function updateSubscriptionPlan($plan, $period, $billing)
// {
//     try {
//         // ---------------------------------------------------------
//         // 1) Resolve target Stripe price (product + interval)
//         // ---------------------------------------------------------
//         $planName = $plan->subscription_name;
//         $interval = ($period === 'annual') ? 'year' : 'month';

//         $prices = \Stripe\Price::all([
//             'limit'  => 100,
//             'expand' => ['data.product']
//         ]);

//         $matchedPrice = null;

//         foreach ($prices->data as $price) {
//             if (
//                 isset($price->recurring) &&
//                 strtolower($price->product->name) === strtolower($planName) &&
//                 strtolower($price->recurring->interval) === strtolower($interval)
//             ) {
//                 $matchedPrice = [
//                     'product_name' => $price->product->name,
//                     'price_id'     => $price->id,
//                     'amount'       => $price->unit_amount / 100,
//                     'currency'     => strtoupper($price->currency),
//                     'interval'     => strtolower($price->recurring->interval),
//                 ];
//                 break;
//             }
//         }

//         if (!$matchedPrice) {
//             return [
//                 'status'  => false,
//                 'message' => "No price found for {$planName} ({$interval})"
//             ];
//         }

//         // ---------------------------------------------------------
//         // 2) Current subscription from Stripe
//         // ---------------------------------------------------------
//         $subscription = \Stripe\Subscription::retrieve([
//             'id'     => $billing->subscription_id,
//             'expand' => ['items.data.price']
//         ]);

//         $subscriptionItem   = $subscription->items->data[0];
//         $subscriptionItemId = $subscriptionItem->id;

//         $currentPrice       = $subscriptionItem->price->unit_amount / 100;
//         $currentInterval    = strtolower($subscriptionItem->price->recurring->interval);
//         $targetInterval     = $matchedPrice['interval'];

//         // dd([
//         //     'currentPrice' => $currentPrice,
//         //     'targetprice' => $matchedPrice['amount']
//         // ]);

//         // ---------------------------------------------------------
//         // 3) Clear scheduled cancel (if any)
//         // ---------------------------------------------------------
//         if ($subscription->cancel_at_period_end) {
//             \Stripe\Subscription::update($billing->subscription_id, [
//                 'cancel_at_period_end' => false,
//                 // no proration just for removing cancel flag
//                 'proration_behavior'   => 'none',
//             ]);
//         }

//         // ---------------------------------------------------------
//         // 4) Quantity based on members / min users
//         // ---------------------------------------------------------
//         $appMemberCount = Member::where('client_id', $this->clientID)->count();
//         $planMinUser    = $plan->min_users ?? 1;
//         $quantity       = max($appMemberCount, $planMinUser);

//         // ---------------------------------------------------------
//         // 5) Upgrade / downgrade / interval change detection
//         // ---------------------------------------------------------
//         $sameInterval = ($currentInterval === $targetInterval);
//         $isUpgrade    = $matchedPrice['amount'] > $currentPrice;
//         $isDowngrade  = $matchedPrice['amount'] < $currentPrice;

//         // ---------------------------------------------------------
//         // 6) Base update params
//         // ---------------------------------------------------------
//         $updateParams = [
//             'items' => [[
//                 'id'       => $subscriptionItemId,
//                 'price'    => $matchedPrice['price_id'],
//                 'quantity' => $quantity,
//             ]],
//             // let Stripe create invoice when needed, but not for a no-op
//             'payment_behavior' => 'allow_incomplete',
//         ];

//         // ---------------------------------------------------------
//         // 7) Proration rules
//         // ---------------------------------------------------------
//         if ($sameInterval) {
//             // ===== Same interval: monthly → monthly, annual → annual =====

//             if ($isUpgrade) {
//                 // Upgrade: prorate and charge (only if > 0)
//                 $updateParams['proration_behavior']   = 'create_prorations';
//                 $updateParams['billing_cycle_anchor'] = 'unchanged';
//             } else {
//                 // Downgrade or same price: no proration, no invoice
//                 $updateParams['proration_behavior']   = 'none';
//                 $updateParams['billing_cycle_anchor'] = 'unchanged';
//             }
//         } else {
//             if ($isUpgrade) {
//                 $updateParams['proration_behavior']   = 'create_prorations';
//                 $updateParams['billing_cycle_anchor'] = 'now';
//             } else {
//                 // Prevent $0 invoices on interval downgrade
//                 $updateParams['proration_behavior']   = 'none';
//                 $updateParams['billing_cycle_anchor'] = 'unchanged';
//             }
//         }


//         // IMPORTANT:
//         // We do NOT use 'always_invoice' anywhere.
//         // That’s what was forcing $0 invoices before.

//         // ---------------------------------------------------------
//         // 8) Apply Stripe update
//         // ---------------------------------------------------------
//         $updatedSubscription = \Stripe\Subscription::update(
//             $billing->subscription_id,
//             $updateParams
//         );

//         // ---------------------------------------------------------
//         // 9) Update local billing data
//         // ---------------------------------------------------------
//         Client_billing::where('client_id', $this->clientID)->update([
//             'price_id'          => $matchedPrice['price_id'],
//             'subscription_plan' => $matchedPrice['product_name']
//         ]);

//         return [
//             'status'  => true,
//             'message' => 'Subscription updated successfully.'
//         ];

//     } catch (\Exception $e) {
//         return [
//             'status'  => false,
//             'message' => $e->getMessage()
//         ];
//     }
// }



    // public function updateSubscriptionPlan($plan, $period, $billing, $plan_update)
    // {
    //     try {
    //         // ---------------------------------------------------------
    //         // 1️⃣ Identify Stripe Product & Price
    //         // ---------------------------------------------------------
    //         $planName = $plan->subscription_name;
    //         $interval = ($period === 'annual') ? 'year' : 'month';

    //         // Fetch prices directly (cheaper & faster than fetching products)
    //         $prices = \Stripe\Price::all([
    //             'limit'   => 100,
    //             'expand'  => ['data.product']
    //         ]);

    //         $matchedPrice = null;

    //         foreach ($prices->data as $price) {
    //             if (
    //                 strtolower($price->product->name) === strtolower($planName) &&
    //                 isset($price->recurring) &&
    //                 strtolower($price->recurring->interval) === strtolower($interval)
    //             ) {
    //                 $matchedPrice = [
    //                     'product_name' => $price->product->name,
    //                     'price_id'     => $price->id,
    //                     'amount'       => $price->unit_amount / 100,
    //                     'currency'     => strtoupper($price->currency),
    //                     'interval'     => $price->recurring->interval,
    //                 ];
    //                 break;
    //             }
    //         }

    //         if (!$matchedPrice) {
    //             return [
    //                 'status' => false,
    //                 'message' => "No matching price found for {$planName} ({$interval})"
    //             ];
    //         }   

    //         // ---------------------------------------------------------
    //         // 2️⃣ Retrieve Customer's Subscription
    //         // ---------------------------------------------------------
    //         $subscription = StripeSubscription::retrieve([
    //             'id'     => $billing->subscription_id,
    //             'expand' => ['items.data.price']
    //         ]);

    //         $subscriptionItemId = $subscription->items->data[0]->id;
    //         $currentInterval    = $subscription->items->data[0]->price->recurring->interval;

    //         // User count calculation
    //         $appMemberCount = Member::where('client_id', $this->clientID)->count();
    //         $planMinUser    = $plan->min_users ?? 1;
    //         $quantity       = max($appMemberCount, $planMinUser);

    //         // ---------------------------------------------------------
    //         // 3️⃣ Build Update Payload With Correct Proration Logic
    //         // ---------------------------------------------------------
    //         $updateParams = [
    //             'items' => [[
    //                 'id'       => $subscriptionItemId,
    //                 'price'    => $matchedPrice['price_id'],
    //                 'quantity' => $quantity,
    //             ]],
    //             'cancel_at_period_end' => false,
    //         ];

    //         // ---- SAME INTERVAL (monthly → monthly OR yearly → yearly)
    //         if ($currentInterval === $matchedPrice['interval']) {

    //             if ($plan_update === 'Upgrade') {
    //                 // Charge prorated amount immediately
    //                 $updateParams['proration_behavior'] = 'always_invoice';
    //             } else {
    //                 // Downgrade or same plan change → no charge, same cycle
    //                 $updateParams['proration_behavior'] = 'none';
    //                 $updateParams['billing_cycle_anchor'] = 'unchanged';
    //             }
    //         }

    //         // ---- DIFFERENT INTERVAL (monthly ↔ yearly)
    //         else {
    //             // Stripe will compute proper prorations
    //             $updateParams['proration_behavior'] = 'create_prorations';
    //             // DO NOT manually set billing cycle anchor here
    //         }

    //         // ---------------------------------------------------------
    //         // 4️⃣ Apply Update
    //         // ---------------------------------------------------------
    //         $updatedSubscription = StripeSubscription::update(
    //             $billing->subscription_id,
    //             $updateParams
    //         );

    //         // ---------------------------------------------------------
    //         // 5️⃣ Update Your Local Billing Table
    //         // ---------------------------------------------------------
    //         Client_billing::where('client_id', $this->clientID)->update([
    //             'price_id'           => $matchedPrice['price_id'],
    //             'subscription_plan'  => $matchedPrice['product_name']
    //         ]);

    //         return [
    //             'status'       => true,
    //             'message'      => 'Subscription plan updated successfully.',
    //             'new_interval' => $matchedPrice['interval']
    //         ];

    //     } catch (\Exception $e) {
    //         return [
    //             'status'  => false,
    //             'message' => $e->getMessage()
    //         ];
    //     }
    // }

    /**
     * Handle Stripe recurring payment
     */

    public function handleWebhook(Request $request)
    {
        $endpoint_secret = config('services.stripe.webhook_secret');
        $payload = $request->getContent();
        $sig_header = $request->header('Stripe-Signature');

        try {
            $event = Webhook::constructEvent(
                $payload, $sig_header, $endpoint_secret
            );
        } catch (\UnexpectedValueException $e) {
            return response('Invalid payload', 400);
        } catch (SignatureVerificationException $e) {
            return response('Invalid signature', 400);
        }

        // Log all events for debugging
        Log::info('Stripe webhook received: ' . $event->type);

        switch ($event->type) {
            case 'invoice.payment_succeeded':
                $invoice = $event->data->object;

                // ✅ Get Subscription ID
                $subscriptionId = $invoice->subscription;

                // You can also get customer ID
                $customerId = $invoice->customer;

                // Example: update your local subscription record
                $this->handleSuccessfulPayment($subscriptionId, $invoice);
                break;

            case 'invoice.payment_failed':
                $invoice = $event->data->object;
                $subscriptionId = $invoice->subscription;
                $this->handleFailedPayment($subscriptionId, $invoice);
                break;
            // case 'customer.subscription.created':
            // case 'customer.subscription.updated':
            //     $subscription = $event->data->object;
            //     $subscriptionId = $subscription->id;
            //     $customerId = $subscription->customer;
            //     $this->handleSubscriptionUpdate($subscriptionId, $subscription);
            //     break;

            case 'customer.subscription.deleted':
                $subscription = $event->data->object;
                $subscriptionId = $subscription->id;
                $this->handleSubscriptionCancellation($subscription);
                break;
        }

        return response('Webhook processed', 200);
    }

    private function handleSuccessfulPayment($subscriptionId, $invoice)
    {
        $CS_details = Client_billing::with(['clientSubscription'])->where('subscription_id', $subscriptionId)->first();
        if($CS_details){
            $client_sibscription_period = $CS_details->clientSubscription->plan_period;
            $update_data['start_date'] = now()->toDateString();
            $update_data['end_date'] = ($client_sibscription_period == 1) ? now()->addYear()->toDateString() : now()->addMonth()->toDateString();          
            ClientSubscription::where('client_id',$CS_details->client_id)->update($update_data);
        }
    }

    private function handleFailedPayment($subscriptionId, $invoice)
    {
        ClientSubscription::where('client_id',$this->clientID)->update([
            'active_status' => 'inactive'
        ]);
        
        Log::warning("Payment failed for subscription: $subscriptionId");
    }

    protected function handleSubscriptionCancellation($subscription)
    {
        try {
            $subscriptionId = $subscription->id;
            $customerId = $subscription->customer;

            Log::info("Auto cancellation received from Stripe for subscription: $subscriptionId");

            // Find local billing record
            $billing = Client_billing::where('subscription_id', $subscriptionId)->first();

            if (!$billing) {
                Log::warning("Billing record not found for subscription: $subscriptionId");
                return;
            }

            // 1️⃣ Remove Payment Method if stored
            if (!empty($billing->payment_method_id)) {
                try {
                    $pm = \Stripe\PaymentMethod::retrieve($billing->payment_method_id);

                    if (!empty($pm->customer)) {
                        $pm->detach();
                        Log::info("Payment method detached for subscription: $subscriptionId");
                    }
                } catch (\Exception $e) {
                    Log::error("PM detach error: " . $e->getMessage());
                }
            }

            // 2️⃣ Update Billing Table
            $billing->update([
                'subscription_id'   => null,
                'payment_method_id' => null,
                'price_id'          => null,
                'subscription_plan' => null
            ]);

            // 3️⃣ Update Client Subscription Table
            ClientSubscription::where('client_id', $billing->client_id)
                ->update([
                    'active_status' => 'cancel'
                ]);

            Log::info("Subscription cancelled and cleaned locally: $subscriptionId");

        } catch (\Exception $e) {
            Log::error("Subscription cancellation handler failed: " . $e->getMessage());
        }
    }



    /**
     * Discount update (Coupon update)
     */
    public function updateDiscount($cid, $discountAmount)
    {
        try {
            $billing = Client_billing::where('client_id', $cid)->first();

            if ($billing && $billing->subscription_id) {
                $subscriptionId = $billing->subscription_id;
                $subscription = StripeSubscription::retrieve($subscriptionId);

                // Remove existing discounts (if any)
                if (!empty($subscription->discounts)) {
                    StripeSubscription::update($subscriptionId, ['discounts' => []]);
                }

                // Create new coupon
                $coupon = \Stripe\Coupon::create([
                    'percent_off' => $discountAmount,
                    'duration'    => 'forever', // or 'once' / 'repeating' based on your logic
                    'name'        => 'Customer Discount ' . $discountAmount . '%',
                ]);

                // Apply new coupon to subscription
                StripeSubscription::update($subscriptionId, [
                    'discounts' => [[ 'coupon' => $coupon->id ]],
                ]);
            }
            $response['status'] = true;
        } catch (\Exception $e) {
            $response['status'] = false;
            $response['message'] = $e->getMessage();
        }

        return $response;
    }


    /**
     * Stripe customer remove
     */
    public function removeCustomer($billing)
    {
        try {

            if(!empty($billing->subscription_id)){
                $subscriptionId = $billing->subscription_id;
                $sub = StripeSubscription::retrieve($subscriptionId);
                $sub->cancel();
            }

            if(!empty($billing->payment_method_id)){
                $paymentmethodId = $billing->payment_method_id;
                $existingPaymentMethod = \Stripe\PaymentMethod::retrieve($paymentmethodId);
                $existingPaymentMethod->detach();
            }


            if(!empty($billing->stripe_customer_id)){
                $customerId = $billing->stripe_customer_id; // Stored in your DB

                $invoices = \Stripe\Invoice::all(['customer' => $customerId]);
                foreach ($invoices->data as $invoice) {
                    if ($invoice->status != 'paid') {
                        \Stripe\Invoice::voidInvoice($invoice->id);
                    }
                }

                $customer = \Stripe\Customer::retrieve($customerId);
                $customer->delete();
            }
            
            $response['status'] = true;
        } catch (\Exception $e) {
            $response['status'] = false;
            $response['message'] = $e->getMessage();
        }
        return $response;
    }


     /**
     * Stripe subscription cancel
     */
    public function subscription_cancel(Request $request)
    {
        try {
            $clientID = session('client');
            $billing = Client_billing::where('client_id', $clientID)->first();

            if (empty($billing)) {
                return [
                    'status' => false,
                    'message' => 'Billing details not found'
                ];
            }

            // ----------------------------------------------------------------
            // 1️⃣ Schedule subscription cancellation at period end
            // ----------------------------------------------------------------
            if (!empty($billing->subscription_id)) {
                $subscription = \Stripe\Subscription::update(
                    $billing->subscription_id,
                    ['cancel_at_period_end' => true]
                );
            }

            // ----------------------------------------------------------------
            // 2️⃣ Update local DB: Mark subscription as “scheduled for cancel”
            // ----------------------------------------------------------------
            $currentPlan = ClientSubscription::where('client_id', $clientID)->first();

            $endDate = $currentPlan->end_date;

            $currentPlan->update([
                'active_status' => 'scheduled_cancel'
            ]);

            $response = [
                'status' => true,
                'message' => "Your subscription has been scheduled for cancellation. 
                                You’ll continue to have access until $endDate."
            ];

        } catch (\Exception $e) {
            $response = [
                'status' => false,
                'message' => $e->getMessage()
            ];
        }

        return $response;
    }

     /**
     * Stripe subscription resume
     */
    public function subscription_resume(Request $request)
    {
        try {
            $clientID = session('client');
            $billing = Client_billing::where('client_id', $clientID)->first();

            if (empty($billing)) {
                return [
                    'status' => false,
                    'message' => 'Billing details not found.'
                ];
            }

            if (empty($billing->subscription_id)) {
                return [
                    'status' => false,
                    'message' => 'No subscription found to resume.'
                ];
            }

            // ----------------------------------------------------------------
            // 1️⃣ Remove scheduled cancellation in Stripe
            // ----------------------------------------------------------------
            $subscription = \Stripe\Subscription::update(
                $billing->subscription_id,
                ['cancel_at_period_end' => false]
            );

            // ----------------------------------------------------------------
            // 2️⃣ Update local DB: set back to active
            // ----------------------------------------------------------------
            $currentPlan = ClientSubscription::where('client_id', $clientID)->first();

            $currentPlan->update([
                'active_status' => 'active'
            ]);

            return [
                'status' => true,
                'message' => 'Your subscription has been successfully reactivated.'
            ];

        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => $e->getMessage()
            ];
        }
    }




    public function updatePaymentMethod(Request $request)
    {
        try {
            $paymentMethodId = $request->payment_method;

            $clientID = session('client');
            $billing = Client_billing::where('client_id', $clientID)->first();

            // ✅ Validation (recommended)
            if (!$paymentMethodId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Missing required fields.',
                ], 422);
            }

           

            // ✅ Attach Payment Method to Customer
            $paymentMethod = \Stripe\PaymentMethod::retrieve($paymentMethodId);
            $paymentMethod->attach(['customer' => $billing->stripe_customer_id]);

            // ✅ Set Default Payment Method
            \Stripe\Customer::update($billing->stripe_customer_id, [
                'invoice_settings' => [
                    'default_payment_method' => $paymentMethodId,
                ],
            ]);

            $billing->update(['payment_method_id' => $paymentMethodId]);

            $explan = ClientSubscription::where('client_id',$clientID)->first();
            $plan = SubscriptionPlan::findOrfail($explan->plan_id);
            $period = $explan->plan_period == 0 ? 'monthly' : 'annual';
            $client = Client::findOrfail($clientID);

            $response = $this->createSubscription($plan,$period,$client);
            if($response['status'] == false){
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ]);
            }

            $explan->update([
                'start_date' => now()->toDateString(),
                'end_date' => ($period == 'annual') ? now()->addYear()->toDateString() : now()->addMonth()->toDateString(),
                'active_status' => 'active'
            ]);
            
            return response()->json(['success' => true,'message' => "A new payment method has been added, and your subscription has been successfully created."]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }



}