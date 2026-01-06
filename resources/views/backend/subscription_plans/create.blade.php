@extends('backend.master', [
'pageTitle' => 'Create Subscription Plan',
'activeMenu' => [
'item' => 'Subscription Plans',
'subitem' => 'Create',
]
])

@section('content')
<div class="my-4">
    <div class="card">
        <div class="card-header">
            <h5>Create Subscription Plan</h5>
        </div>
        <div class="card-body px-5 py-5">
            <form action="{{ route('subscription-plans.store') }}" method="POST">
                @csrf
                @php
                $plan = null;
                $buttonText = 'Create';
                @endphp
                @include('backend.subscription_plans.partials.form')
            </form>
        </div>
    </div>
</div>
@endsection