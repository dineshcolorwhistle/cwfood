@extends('backend.master', [
'pageTitle' => 'My Billing',
'activeMenu' => [
'item' => 'Billing',
'subitem' => '',
]
])

@push('styles')
<style>
.btn-free {background-color:var(--bs-gray-200);color: #0d1b2a;border-radius: 12px;font-weight: 600;border: none;padding: 0.6rem 1rem;}
.btn-free i {color: #0d1b2a;}
.btn-upgrade {background-color: var(--secondary-color);color: #fff;border-radius: 30px;font-weight: 700;letter-spacing: 0.5px;padding: 0.6rem 1.5rem;border: none;display: inline-flex;align-items: center;justify-content: center;transition: background-color 0.2s ease-in-out;}
.btn-upgrade:hover {background-color: var(--primary-color);color: #fff;}
.btn-upgrade span {background-color: #fff;color: #0d1b2a;border-radius: 50%;font-size: 1.5rem;padding: 2px 4px;margin-left: 15px;}
#billing-table td {font-size:15px;}
span.pdf-icon{font-size: 35px;margin: 5px;}
#billing-table td a{text-decoration:none;}
.btn-subs-cancel {background-color: var(--primary-color);color: #fff;border-radius: 30px;font-weight: 700;letter-spacing: 0.5px;padding: 0.6rem 1.5rem;border: none;display: inline-flex;align-items: center;justify-content: center;transition: background-color 0.2s ease-in-out;}
.btn-subs-cancel:hover {background-color: var(--secondary-color);color: #fff;}
h3 strong{color:var(--secondary-color);}
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="">
        <div class="card-header">
            <h1 class="page-title">Billing</h1>
        </div>
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

            <div class="border-0 text-center pb-2" style="width:300px;">
                <!-- <h4 class="fw-semibold mb-3 text-start">Your plan</h4> -->
                <!-- Free Plan button -->
                <button type="button" class="btn btn-free w-100 mb-3">
                    <i class="bi bi-lightning-charge me-1"></i> {{$subscription->plan->subscription_name}} @if($subscription->plan->id != 7) | {{$subscription->plan_period == 0 ? 'Monthly': 'Annual' }} @endif
                </button>
                @if($paymentMethod != null && $subscription->active_status != 'scheduled_cancel')
                <!-- Upgrade button -->
                <a href="{{route('client.subscription.show')}}"><button type="button" class="btn btn-upgrade w-100">
                    UPGRADE <span class="material-symbols-outlined">arrow_forward</span>
                </button></a>
                @endif

                @if(sizeof($billing_history)> 0 && $subscription->active_status != 'scheduled_cancel')
                <!-- Subscription cancel button -->
                <button type="button" class="btn btn-subs-cancel mt-3 w-100" id="subscription_cancel">CANCEL SUBSCRIPTION</button>
                @endif

                @if($isScheduled)
                <!-- Subscription cancel button -->
                <button type="button" class="btn btn-subs-cancel mt-3 w-100" id="subscription_resume">SUBSCRIPTION RESUME</button>
                @endif
            </div>
                        
            <hr>

            <h4 class="mt-3 mb-3">Payment Method  @if($paymentMethod != null) <a href="{{route('billing.getCard')}}"><span class="material-symbols-outlined"> edit</span></a>@endif</h4>
            @if($paymentMethod == null)
            <div id="card-element" class="col-lg-6 mt-3 card p-3">  
                <div class="mt-3">
                    <h5 class="mb-2">Card Information</h5>
                    <div id="card-number" class="p-2 border rounded mb-2"></div>
                    <div class="row">
                        <div class="col-md-6">
                            <div id="card-expiry" class="p-2 border rounded"></div>
                        </div>
                        <div class="col-md-6">
                            <div id="card-cvc" class="p-2 border rounded"></div>
                        </div>
                    </div>
                    <h5 class="mt-3">Cardholder name</h5>
                    <input type="text" class="form-control" name="card-name" placeholder="Full name on card">

                    <h5 class="mt-3">Customer Email</h5>
                    <input type="text" class="form-control" name="card-email" placeholder="Enter email">
                </div>
                <button class="btn btn-primary mt-3" id="saveCardBtn">Save Card</button>
            </div>
            @else
            <!-- <div class="payment-method-details-section">
                <img src="{{ ($paymentMethod->card->brand ?? '') == 'visa' ? asset('assets/img/visacard.jpg') : asset('assets/img/mastercard.png') }}" alt="payment-method-image">
                <h4 class="pt-3">{{$paymentMethod->card->brand}} : **** **** {{$paymentMethod->card->last4}}</h4>
            </div> -->


            <div class="card shadow-sm border-0 mb-3" style="max-width: 420px;">
                <div class="card-body d-flex align-items-center">

                    <!-- Card Logo -->
                    <div class="me-3">
                        <img src="{{ ($paymentMethod->card->brand ?? '') == 'visa' ? asset('assets/img/visacard.jpg') : asset('assets/img/mastercard.png') }}"
                            alt="Mastercard" 
                            style="width:55px;">
                    </div>

                    <!-- Card Details -->
                    <div class="flex-grow-1">
                        <div class="fw-semibold text-dark">
                            {{strtoupper($paymentMethod->card->brand)}} •••• {{$paymentMethod->card->last4}}
                        </div>

                        <div class="small text-muted">
                            Exp: {{$paymentMethod->card->exp_month}}/{{$paymentMethod->card->exp_year}}
                        </div>

                        <div class="small text-muted mt-1">
                            Default Payment Method
                        </div>
                        
                    </div>

                    <!-- Right Icon (Optional) -->
                    <div>
                        <span class="material-symbols-outlined text-secondary" style="font-size: 30px !important;">
                            credit_card
                        </span>
                    </div>
                </div>
            </div>

<!-- <p class="small text-muted mt-1">
    <strong>Important!</strong> PDF invoicing is available only for Visa / Mastercard payments. PayPal is not supported.
</p> -->

            @endif

            <hr class="pt-2">

            @if(sizeof($billing_history)> 0)
                <h4 class="mt-3 mb-3">Billing History</h4>

                <table id="billing-table">
                    <thead>
                        <th>Date</th>
                        <th>Product</th>
                        <th>Gateway</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th></th>
                    </thead>
                    <tbody>
                        @foreach($billing_history as $billing)
                        <tr>
                            <td>{{$billing['paid_at']}}</td>
                            <td>{{$billing['product_name']}}</td>
                            <td>{{$billing['card_brand'] ?? 'N/A'}}</td>
                            <td>${{$billing['amount']}}</td>
                            <td>{{$billing['status']}}</td>
                            <td><a href="{{$billing['invoice_pdf']}}" target="_blank"> <span class="material-symbols-outlined pdf-icon">picture_as_pdf</span>Open web link</a></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif

        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://js.stripe.com/v3/"></script>
<script>

    let payment_method = @json($paymentMethod);
    let customerID = @json($customerID);
    const url = (customerID) ? "{{ route('billing.updatePaymentmethod') }}" : "{{ route('billing.saveCard') }}";
    console.log(url);
    
    if(payment_method == null){
        const stripe = Stripe("{{ config('services.stripe.key') }}");
        const elements = stripe.elements();
        const cardNumber = elements.create("cardNumber");
        const cardExpiry = elements.create("cardExpiry");
        const cardCvc = elements.create("cardCvc");

        cardNumber.mount("#card-number");
        cardExpiry.mount("#card-expiry");
        cardCvc.mount("#card-cvc");

        // Track validation states
        let cardComplete = {
            number: false,
            expiry: false,
            cvc: false
        };

        cardNumber.on('change', (event) => {
            cardComplete.number = event.complete;
        });
        cardExpiry.on('change', (event) => {
            cardComplete.expiry = event.complete;
        });
        cardCvc.on('change', (event) => {
            cardComplete.cvc = event.complete;
        });

        document.getElementById("saveCardBtn").addEventListener("click", async () => {
            const btn = document.getElementById('saveCardBtn');
            btn.disabled = true;                 // Disable button
            btn.innerText = 'Processing...'; 

            const cardholderName = document.querySelector('input[name="card-name"]').value;
            const cardholderEmail = document.querySelector('input[name="card-email"]').value;

            // Validate text fields
            if (!cardholderName || !cardholderEmail) {
                alert("Please fill in all fields before saving the card.");
                return;
            }

            // Basic email format check
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(cardholderEmail)) {
                alert("Please enter a valid email address.");
                return;
            }

            // Validate card fields
            if (!cardComplete.number || !cardComplete.expiry || !cardComplete.cvc) {
                alert("Please enter complete and valid card details.");
                return;
            }


            // 1️⃣ Create PaymentMethod with card
            const { paymentMethod, error } = await stripe.createPaymentMethod({
                type: 'card',
                card: cardNumber,
                billing_details: {
                    name: cardholderName,
                    email: cardholderEmail
                }
            });

            if (error) {
                alert(error.message);
                return;
            }

            // 2️⃣ Send payment method to backend
            fetch(url, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({
                    payment_method: paymentMethod.id,
                    name: cardholderName,
                    email: cardholderEmail
                })
            })
            .then(res => res.json())
            .then(data => {
                /**
                 * Card button reset
                 */
                btn.disabled = false;
                btn.innerText = "SAVE CARD";

                if (data.success) {
                    window.location.reload();
                } else {
                    alert(data.message || "Something went wrong.");
                }
            })
            .catch(err => console.error(err));
        });
    }


    $(document).on('click','#subscription_cancel',function(){
        Swal.fire({
            title: 'Are you sure?',
            text: 'You want to cancel the subscription!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Okay'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "{{route('subscription.cancel')}}",
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.status) {
                            Swal.fire({
                                icon: 'success',
                                text: response.message,
                                timer: 2000
                            }).then(() => {
                                location.reload();
                            });
                        }else{
                            Swal.fire({
                                icon: 'warning',
                                title: 'Warning!',
                                text: response.message
                            }); 
                        }
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: xhr.responseJSON.message || 'An error occurred'
                        });
                    }
                });
            }
        });
    });

    

    $(document).on('click','#subscription_resume',function(){
        Swal.fire({
            title: 'Are you sure?',
            text: 'You want to resume the subscription!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Okay'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "{{route('subscription.resume')}}",
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.status) {
                            Swal.fire({
                                icon: 'success',
                                text: response.message,
                                timer: 2000
                            }).then(() => {
                                location.reload();
                            });
                        }else{
                            Swal.fire({
                                icon: 'warning',
                                title: 'Warning!',
                                text: response.message
                            }); 
                        }
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: xhr.responseJSON.message || 'An error occurred'
                        });
                    }
                });
            }
        });
    });
</script>

@endpush