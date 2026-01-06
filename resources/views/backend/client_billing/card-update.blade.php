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
.btn-upgrade {background-color: var(--secondary-color);      /* orange-red */color: #fff;border-radius: 30px;font-weight: 700;letter-spacing: 0.5px;padding: 0.6rem 1.5rem;border: none;display: inline-flex;align-items: center;justify-content: center;transition: background-color 0.2s ease-in-out;}
.btn-upgrade:hover {background-color: var(--primary-color);color: #fff;}
.btn-upgrade span {background-color: #fff;color: #0d1b2a;border-radius: 50%;font-size: 1.5rem;padding: 2px 4px;margin-left: 15px;}
#billing-table td {font-size:15px;}
span.pdf-icon{font-size: 35px;margin: 5px;}
#billing-table td a{text-decoration:none;}
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="">
        <div class="card-header">
            <h1 class="page-title">Card Update</h1>
        </div>
        <div class="card-body">
            
            <h4 class="mt-3 mb-3">Payment Method</h4>
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
                </div>
                <button class="btn btn-primary mt-3" id="saveCardBtn">Save Card</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://js.stripe.com/v3/"></script>
<script>
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
        const cardholderName = document.querySelector('input[name="card-name"]').value;

        // Validate text fields
        if (!cardholderName) {
            alert("Please fill in all fields before saving the card.");
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
                name: cardholderName
            }
        });

        if (error) {
            alert(error.message);
            return;
        }

        // 2️⃣ Send payment method to backend
        fetch("{{ route('billing.updateCard') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
            },
            body: JSON.stringify({
                payment_method: paymentMethod.id,
                name: cardholderName
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                window.location.href="{{route('client.billing.show')}}";
            } else {
                alert(data.message || "Something went wrong.");
            }
        })
        .catch(err => console.error(err));
    });
</script>

@endpush