@extends('backend.master', [
'pageTitle' => 'My Subscription',
'activeMenu' => [
'item' => 'Subscription',
'subitem' => '',
]
])

@push('styles')
<style>
    :root {
        --brand-primary:   var(--primary-color,   #3F20B9);
        --brand-secondary: var(--secondary-color, #6c757d);
    }
    .subscription-grid {display: grid;grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));gap: 16px;}
    .subscription-card { border: 1px solid #e6e6e6; border-radius: .75rem; background: #fff; display: flex; flex-direction: column; min-height: 460px;   /* reduced height */ font-size: 1.0em; }
    .subscription-card.subscribed {background: #a1f3b0;}
    .subscription-card-header {padding: 1.1rem 1.25rem .7rem;border-bottom: 1px solid #f0f0f0;}
    .subscription-name {margin: 0;font-weight: 700;}
    .subscription-card-body {padding: 1.25rem 1.25rem 1.75rem;display: flex;flex-direction: column;flex: 1 1 auto;}
    .subscription-card-footer {margin-top: 0;padding: 1.75rem 1.25rem;border-top: 5px solid #f0f0f0;text-align: center;}
    .billing-toggle .btn {padding: .4rem .85rem;border-radius: 9999px;font-weight: 600;background: #fff;border-color: var(--brand-secondary);color: var(--brand-secondary);}
    .billing-toggle .btn.active {color: #fff;background: var(--brand-primary);border-color: var(--brand-primary);}
    .price-row {margin: .25rem 0 .45rem;padding: .25rem 0;border: 0;}
    .price-row .amount {font-weight: 800;font-size: 1.925rem;display: block;color: var(--brand-primary);}
    .price-row .subline {display: block;font-size: .9rem;color: #666;margin-top: .2rem;text-align: left;}
    .save-badge {display: inline-block;font-size: .9rem;font-weight: 700;margin-top: .4rem;color: #257942;}
    .features {margin-top: auto;padding-top: 1rem;}
    .feature-row {display: flex;align-items: center;justify-content: space-between;margin: .45rem 2%;}
    .feature-label {color: #2b2b2b;}
    .feature-value {font-weight: 700;font-size: 1.00em;color: #009fff;text-align: right;min-width: 3ch;}
    .text-muted { color: #666; }
    .d-none     { display: none !important; }
    .info-group {margin-top: 15px;}
    .resource-meter {margin-bottom: 15px;}
    .progress {height: 8px;margin-top: 5px;}
    .bg-primary-blue {background-color: var(--brand-primary);}
    h3 strong{color:var(--secondary-color);}
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="">
        <div class="card-header d-flex justify-content-between">
            <h1 class="page-title">Subscription</h1>
            <div class="right-side content d-flex flex-row-reverse align-items-center">
                <div class="text-end">
                    <div class="billing-toggle btn-group" role="group" aria-label="Billing cycle">
                        <button type="button" class="btn toggle-monthly">Monthly</button>
                        <button type="button" class="btn toggle-annual">Annually</button>
                    </div>
                </div>
            </div>
        </div>

        <input type="hidden" id="payment_method_id" value="{{ $paymentDetails?->payment_method_id }}">

        <div class="card-body">
            @if ($subscription)

                @php
                    $isTrial     = $subscription->plan_id == env('TRIAL_PLAN');
                    $isActive    = $subscription->active_status == 'active';
                    $isScheduled = $subscription->active_status == 'scheduled_cancel';
                    $expired     = $remainingDays < 0;
                    $planName    = $subscription->plan->subscription_name ?? '';
                    $daysAbs     = str_replace('-', '', $remainingDays);
                @endphp

                {{-- ---------------- Trial Plan ---------------- --}}
                @if ($isTrial)
                    <div class="row">
                        <h3>
                            You are currently subscribed to the <strong>Trial Plan</strong>.
                            @if ($expired)
                                It expired <strong>{{ $daysAbs }} days</strong> ago.
                            @else
                                It will expire in <strong>{{ $remainingDays }} days</strong>.
                            @endif
                        </h3>
                    </div>

                {{-- ---------------- Scheduled Cancellation ---------------- --}}
                @elseif ($isScheduled)
                    <div class="row">
                        <h3>
                            Your subscription has been scheduled for cancellation.  
                            You’ll continue to have access until <strong>{{ $subscription->end_date }}</strong>.
                        </h3>
                    </div>

                {{-- ---------------- Active Paid Plan ---------------- --}}
                @elseif ($isActive)
                    <div class="row">
                        @if (!$paymentMethod)
                            {{-- No Payment Method Cases --}}
                            <h3>
                                Your current plan (<strong>{{ $planName }}</strong>)
                                @if ($expired)
                                    expired <strong>{{ $daysAbs }}</strong> days ago.
                                    Please add a payment method to reactivate your subscription.
                                @else
                                    will expire in <strong>{{ $remainingDays }}</strong> days.
                                    Please add a payment method to continue your subscription.
                                @endif
                            </h3>
                        @else
                            {{-- Normal Billing Status --}}
                            <h3>
                                You are currently subscribed to the <strong>{{ $planName }} Plan</strong>.
                                @if ($expired)
                                    Payment is overdue by <strong>{{ $daysAbs }} days</strong>.
                                @else
                                    Next payment is due in <strong>{{ $remainingDays }} days</strong>.
                                @endif
                            </h3>
                        @endif
                    </div>

                @endif

            @else
                <div class="alert alert-warning">
                    No active subscription found. Please contact your administrator.
                </div>
            @endif

            <input type="hidden" id="billingInterval" value="">
            <div class="d-flex flex-wrap gap-3 mt-4">
                @foreach ($all_subscription as $plan)

                @php
                    // Monthly and annual (monthly-equivalent) amounts
                    $m    = (float)($plan->computed['monthly'] ?? $plan->monthly_cost_per_user ?? 0);
                    $am   = (float)($plan->computed['annualMonthly'] ?? $m);  // annual_cost_per_user / 12
                    $svP  = (int)($plan->computed['savingsPct'] ?? 0);
                    $fmt0 = fn($v) => number_format((float)$v, 0);

                    // Users display: min–max or single when equal
                    $min = (int)($plan->min_users ?? 0);
                    $max = (int)($plan->max_users ?? 0);
                    $usersCount = ($min && $max)
                    ? ($min === $max ? "{$min}" : "{$min}–{$max}")
                    : ($max ? "{$max}" : ($min ? "{$min}" : ''));

                    // Determine current plan / CTA label
                    $isSubscribed = $subscription && $subscription->plan_id == $plan->id;
                    $currentMax   = isset($currentMaxUsers) ? (int)$currentMaxUsers : null;

                    $ctaLabel = 'Upgrade';
                    if ($subscription && !$isSubscribed && $currentMax !== null && $subscription->plan_id != 7) {
                        if ($plan->max_users > $currentMax){
                            $ctaLabel = 'Upgrade';
                        }elseif ($plan->max_users < $currentMax){
                            $ctaLabel = 'Downgrade';
                        }  
                    }
                @endphp
                <div class="subscription-card {{ $isSubscribed ? 'subscribed' : '' }}" data-cta-label="{{$ctaLabel}}" data-max-users="{{ $plan->max_users }}" data-plan="{{ $plan->id }}">
                    <div class="subscription-card-header">
                        <h4 class="subscription-name">{{ $plan->subscription_name }}</h4>
                    </div>
                    <div class="subscription-card-body">
                        {{-- Monthly pricing (shown when Monthly is selected) --}}
                        <div class="price-row price-monthly {{ ($billingCycle ?? 'monthly') === 'annual' ? 'd-none' : '' }}">
                            <span class="amount">$ {{ $fmt0($m) }}</span>
                            <span class="subline">Per User | Per Month | Paid Monthly</span>
                        </div>

                        {{-- Annual pricing (monthly-equivalent; shown when Annual is selected) --}}
                        <div class="price-row price-annual {{ ($billingCycle ?? 'monthly') === 'monthly' ? 'd-none' : '' }}">
                            <span class="amount">$ {{ $fmt0($am) }}</span>
                            <span class="subline">Per User | Per Month | Paid Annually</span>
                            @if($svP > 0)
                            <span class="save-badge">Save {{ $svP }}% vs monthly</span>
                            @endif
                        </div>

                        {{-- Plan description (ideal_for) --}}
                        @if(!empty($plan->ideal_for))
                            <p class="mt-2 mb-3 text-muted">{!! nl2br(e($plan->ideal_for)) !!}</p>
                        @endif

                        {{-- Features (bottom-aligned) --}}
                        <div class="features">
                            @if($usersCount !== '')
                            <div class="feature-row">
                                <span class="feature-label">Users</span>
                                <span class="feature-value">{{ $usersCount }}</span>
                            </div>
                            @endif

                            <div class="feature-row">
                            <span class="feature-label">Raw Materials</span>
                            <span class="feature-value">{{ $plan->max_raw_materials }}</span>
                            </div>

                            <div class="feature-row">
                            <span class="feature-label">SKUs</span>
                            <span class="feature-value">{{ $plan->max_skus }}</span>
                            </div>

                            <div class="feature-row">
                            <span class="feature-label">Workspaces</span>
                            <span class="feature-value">{{ $plan->max_work_spaces }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="subscription-card-footer p-2">
                        @if($isSubscribed)
                            {{-- Current plan indicator --}}
                            <button type="button" class="btn btn-secondary-blue btn-plan subscribed" 
                            data-client="{{ $clientID }}"
                            data-plan="{{ $plan->id }}"
                            data-cycle="{{ $billingCycle ?? 'monthly' }}"
                            data-original-label="{{ $ctaLabel }}"
                            id="planupdate_{{ $plan->id }}"
                            onclick="update_plan(this)"
                            disabled>
                            CURRENT PLAN
                            </button>
                        @else
                            {{-- Action button (Subscribe / Upgrade / Downgrade) --}}
                            <button
                            type="button"
                            class="btn btn-secondary-blue btn-plan"
                            data-client="{{ $clientID }}"
                            data-plan="{{ $plan->id }}"
                            data-cycle="{{ $billingCycle ?? 'monthly' }}"
                            data-original-label="{{ $ctaLabel }}"
                            id="planupdate_{{ $plan->id }}"
                            onclick="update_plan(this)"
                            >
                            {{ $ctaLabel }}
                            </button>
                        @endif                        
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection

<script>
    // Server-provided values
    let subscription = @json($subscription ?? []);
    let subscription_plan_id = subscription && subscription.plan_id ? String(subscription.plan_id) : null;
    let subscription_period = "{{ $billingCycle ?? 'monthly' }}"; // the user's current subscription cycle on server
    document.addEventListener("DOMContentLoaded", function () {
        let element = document.getElementById("billingInterval");
        if (element) {
            element.value = subscription_period;
        }
    });


    (function () {
        function debug(...args) {
            // comment this out in production if you prefer
            console.log(...args);
        }

        function initOriginalLabelsAndAttrs() {
            document.querySelectorAll('.subscription-card').forEach(card => {
                const btn = card.querySelector('button.btn-plan');
                if (!btn) return;

                // If server didn't set data-original-label (some cards do), ensure it's set now
                if (!btn.hasAttribute('data-original-label')) {
                    const cur = btn.innerText?.trim() || '';
                    btn.setAttribute('data-original-label', cur);
                }

                // Ensure the card itself also has data-original-cta for backup
                if (!card.hasAttribute('data-original-cta')) {
                    card.setAttribute('data-original-cta', btn.getAttribute('data-original-label') || '');
                }

                // Ensure data-plan is stringified so comparisons are predictable
                if (card.dataset.plan) card.dataset.plan = String(card.dataset.plan);
            });
        }

        function getCardsSorted() {
            const cards = Array.from(document.querySelectorAll('.subscription-card'));
            // sort by max users numeric (if missing -> 0)
            cards.sort((a, b) => {
                const aUsers = parseInt(a.dataset.maxUsers || 0, 10);
                const bUsers = parseInt(b.dataset.maxUsers || 0, 10);
                return aUsers - bUsers;
            });
            return cards;
        }

        function findCurrentCardIndex(cards) {
            if (!subscription_plan_id) return -1;
            return cards.findIndex(c => String(c.dataset.plan) === String(subscription_plan_id));
        }

        function applyCycle(cycle) {
            debug('applyCycle ->', cycle, 'server subscription_period:', subscription_period);

            let element = document.getElementById("billingInterval");
            if (element) {
                element.value = cycle;
            }

            // Toggle active state on top buttons
            document.querySelectorAll('.billing-toggle .btn').forEach(b => b.classList.remove('active'));
            const toggleBtn = document.querySelector('.toggle-' + cycle);
            if (toggleBtn) toggleBtn.classList.add('active');

            const cards = getCardsSorted();
            const currentIndex = findCurrentCardIndex(cards);

            debug('cards order (plan:maxUsers):', cards.map(c => `${c.dataset.plan}:${c.dataset.maxUsers}`), 'currentIndex=', currentIndex);

            cards.forEach((card, idx) => {
                const m = card.querySelector('.price-monthly');
                const a = card.querySelector('.price-annual');
                const btn = card.querySelector('button.btn-plan');
                if (!btn) return;

                // toggle price visibility
                if (cycle === 'annual') {
                    m?.classList.add('d-none');
                    a?.classList.remove('d-none');
                } else {
                    a?.classList.add('d-none');
                    m?.classList.remove('d-none');
                }

                // Default: restore original label
                const original = btn.getAttribute('data-original-label') || card.getAttribute('data-original-cta') || 'Subscribe';

                // Determine label
                let newLabel = original;
                let disabled = false;

                if (cycle === subscription_period) {
                    // Same cycle as user's current subscription -> show normal labels
                    if (idx === currentIndex && currentIndex !== -1) {
                        newLabel = 'CURRENT PLAN';
                        disabled = true;
                    } else {
                        newLabel = original;
                        disabled = false;
                    }
                } else {
                    // User switched billing cycle
                    if (currentIndex === -1) {
                        newLabel = original;
                        disabled = false;
                    } else {
                        if (idx === currentIndex) {
                            // 👇 This is the key change
                            if (subscription_period === 'annual' && cycle === 'monthly') {
                                newLabel = 'Downgrade';
                            } else if (subscription_period === 'monthly' && cycle === 'annual') {
                                newLabel = 'Upgrade';
                            } else {
                                newLabel = 'Upgrade';
                            }
                        } else if (idx < currentIndex) {
                            newLabel = 'Downgrade';
                        } else {
                            newLabel = 'Upgrade';
                        }
                        disabled = false;
                    }
                }

                // Apply label and attributes
                btn.innerText = newLabel;
                if (disabled) {
                    btn.setAttribute('disabled', 'true');
                    card.classList.add('subscribed');
                } else {
                    btn.removeAttribute('disabled');
                    card.classList.remove('subscribed');
                }

                // Ensure the correct data-cycle attribute for backend on click
                btn.setAttribute('data-cycle', cycle);
            });

            // persist choice locally (optional)
            localStorage.setItem('billing_cycle', cycle);
        }

        // Init & event wiring
        document.addEventListener('DOMContentLoaded', function () {
            initOriginalLabelsAndAttrs();

            // apply stored or server billingCycle
            const stored = subscription_period;
            applyCycle(stored);

            document.querySelector('.toggle-monthly')?.addEventListener('click', () => applyCycle('monthly'));
            document.querySelector('.toggle-annual')?.addEventListener('click', () => applyCycle('annual'));
        });
    })();


    function update_plan(_this){
        let button_id = $(_this).attr('id');
        let button_text = $(_this).html();
        const btn = document.getElementById(button_id);
        btn.disabled = true;                 // Disable button
        btn.innerText = 'Processing...'; 
        
        let paymentID = $('#payment_method_id').val();
        if(paymentID == ''){
            window.location.href="{{route('client.billing.show')}}"
        }else{
            let cid = $(_this).data('client')
            let plan = $(_this).data('plan')    
            let billing_period = $("#billingInterval").val()
            let data = {'_token':$('meta[name="csrf-token"]').attr('content'),'client':cid,'plan':plan,'period':billing_period, 'plan_update':button_text};	
            $.ajax({
                url: "{{route('client.subscription.update')}}",
                dataType: 'json',
                data: data,
                method: 'POST',
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: response.success,
                            timer: 2000
                        }).then(() => {
                            location.reload();
                        });
                    }else if(response.error){
                        Swal.fire({
                        icon: 'warning',
                        title: 'Warning!',
                        text: response.error,
                    });
                    }
                },
                complete: function() {
                    // Re-enable if needed
                    btn.disabled = false;
                    btn.innerText = button_text;
                }
            });
        }
        
    }
</script>

<script>

    // let subscription = @json($subscription ?? []);
    // let subscription_plan_id = subscription && subscription.plan_id ? subscription.plan_id : null;
    // let subscription_period = "{{$billingCycle}}";

    // (function () {
    //     function applyCycle(cycle,period='') {
    //     // Toggle button active state
    //     document.querySelectorAll('.billing-toggle .btn').forEach(b => b.classList.remove('active'));
    //     const b = document.querySelector('.toggle-' + cycle);
    //     if (b) b.classList.add('active');

    //     // Toggle visibility per card
    //     document.querySelectorAll('.subscription-card').forEach(card => {
    //         const m = card.querySelector('.price-monthly');
    //         const a = card.querySelector('.price-annual');
    //         if (!m || !a) return;

    //         if (cycle === 'annual') {
    //         m.classList.add('d-none');
    //         a.classList.remove('d-none');
    //         } else {
    //         a.classList.add('d-none');
    //         m.classList.remove('d-none');
    //         }

    //         // Attach selected cycle to CTA for backend
    //         const btn = card.querySelector('button[data-plan]');
    //         if (btn) btn.setAttribute('data-cycle', cycle);

    //         const cardBtn = card.querySelector('button.btn-plan');            
    //         // Update data-cycle attribute
    //         cardBtn.setAttribute('data-cycle', cycle);

    //         // Handle button text logic
    //         const isSubscribed = cardBtn && !cardBtn.hasAttribute('data-plan');

    //         if (cycle !== subscription_period) {
    //             // If user toggled billing cycle different from their current one
    //             cardBtn.removeAttribute('disabled');
    //             cardBtn.classList.remove('subscribed');
    //             cardBtn.innerHTML = 'Upgrade';
    //             card.classList.remove('subscribed');
    //         } else {
    //             // Restore original button labels
    //             if (isSubscribed) {
    //                 cardBtn.innerHTML = 'CURRENT PLAN';
    //                 cardBtn.setAttribute('disabled', true);
    //                 card.classList.add('subscribed');
    //             } else {
    //                 // Get back original label from server (Subscribe / Upgrade / Downgrade)
    //                 const ctaLabel = card.getAttribute('data-cta-label');
    //                 cardBtn.innerHTML = ctaLabel;
    //                 cardBtn.removeAttribute('disabled');
    //                 card.classList.remove('subscribed');
    //             }
    //         }

    //     });

    //     // Persist selection
    //     localStorage.setItem('billing_cycle', cycle);
    //     const root = document.getElementById('subscription-page');
    //     if (root) root.setAttribute('data-billing-cycle', cycle);
    //     }

    //     document.addEventListener('DOMContentLoaded', function () {
    //         // const defaultCycle = (document.getElementById('subscription-page')?.dataset.billingCycle) || 'monthly';
    //         // const stored = localStorage.getItem('billing_cycle') || defaultCycle;
    //         const stored = "{{$billingCycle}}";
    //         applyCycle(stored);
    //         const mBtn = document.querySelector('.toggle-monthly');
    //         const aBtn = document.querySelector('.toggle-annual');
    //         if (mBtn) mBtn.addEventListener('click', () => applyCycle('monthly',stored));
    //         if (aBtn) aBtn.addEventListener('click', () => applyCycle('annual',stored));
    //     });
    // })();



   

</script>