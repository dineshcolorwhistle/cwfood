@extends('backend.master', [
'pageTitle' => 'Edit Subscription Plan',
'activeMenu' => [
'item' => 'Subscription Plans',
'subitem' => 'Edit',
]
])

@section('content')
<div class="my-4">
    <div class="card">
        <div class="card-header">
            <h5>Edit Subscription Plan</h5>
        </div>
        <div class="card-body px-5 py-5">
            <form action="{{ route('subscription-plans.update', $subscriptionPlan->id) }}" method="POST">
                @csrf
                @method('PUT')
                @php
                $plan = $subscriptionPlan;
                $buttonText = 'Update';
                @endphp
                @include('backend.subscription_plans.partials.form')
            </form>
        </div>
    </div>
</div>
@endsection