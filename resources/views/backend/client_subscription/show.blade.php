@extends('backend.master', [
'pageTitle' => 'My Subscription',
'activeMenu' => [
'item' => 'Subscription',
'subitem' => '',
]
])

@push('styles')
<style>
    .info-group {
        margin-top: 15px;
    }

    .resource-meter {
        margin-bottom: 15px;
    }

    .progress {
        height: 8px;
        margin-top: 5px;
    }

    .bg-primary-blue {
        background-color: #3F20B9;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="">
        <div class="card-header">
            <h1 class="page-title">Subscription</h1>
        </div>
        <div class="card-body">
            @if($subscription)
            <div class="subscription-details">
                <div class="row">
                    <div class="col-md-6">
                        <div class="card box-shadow">
                            <div class="card-body">
                                <h3 class="text-primary-orange">Plan Details</h3>
                                <div class="info-group">
                                    <p><strong>Plan Name:</strong> {{ $subscription->plan->subscription_name }}</p>
                                    <p><strong>Plan Code:</strong> {{ $subscription->plan->plan_code }}</p>
                                    <p><strong>Monthly Cost:</strong> AUD {{ number_format($subscription->plan->monthly_cost_per_user, 2) }} per user</p>
                                    <p><strong>Subscription Period:</strong> {{ \Carbon\Carbon::parse($subscription->start_date)->format('d M Y') }} - {{ \Carbon\Carbon::parse($subscription->end_date)->format('d M Y') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card box-shadow">
                            <div class="card-body">
                                <h3 class="text-primary-orange">Resource Allocation</h3>
                                <div class="info-group">
                                    @php
                                    $userPercent = number_format(($subscription->users_allocated / $subscription->plan->max_users * 100), 1);
                                    $materialsPercent = number_format(($subscription->raw_materials_allocated / $subscription->plan->max_raw_materials * 100), 1);
                                    $skusPercent = number_format(($subscription->skus_allocated / $subscription->plan->max_skus * 100), 1);
                                    $workspacesPercent = number_format(($subscription->work_spaces_allocated / $subscription->plan->max_work_spaces * 100), 1);
                                    @endphp

                                    <div class="resource-meter">
                                        <p>
                                            <strong>Users:</strong>
                                            <span>{{ $subscription->users_allocated }} / {{ $subscription->plan->max_users }}</span>
                                        <div class="progress">
                                            <div class="progress-bar bg-primary-blue"
                                                role="progressbar"
                                                style="width: {{ $userPercent }}%">
                                            </div>
                                        </div>
                                        </p>
                                    </div>
                                    <div class="resource-meter">
                                        <p>
                                            <strong>Raw Materials:</strong>
                                            <span>{{ $subscription->raw_materials_allocated }} / {{ $subscription->plan->max_raw_materials }}</span>
                                        <div class="progress">
                                            <div class="progress-bar bg-primary-blue"
                                                role="progressbar"
                                                style="width: {{ $materialsPercent }}%">
                                            </div>
                                        </div>
                                        </p>
                                    </div>
                                    <div class="resource-meter">
                                        <p>
                                            <strong>SKUs:</strong>
                                            <span>{{ $subscription->skus_allocated }} / {{ $subscription->plan->max_skus }}</span>
                                        <div class="progress">
                                            <div class="progress-bar bg-primary-blue"
                                                role="progressbar"
                                                style="width: {{ $skusPercent }}%">
                                            </div>
                                        </div>
                                        </p>
                                    </div>
                                    <div class="resource-meter">
                                        <p>
                                            <strong>Work Spaces:</strong>
                                            <span>{{ $subscription->work_spaces_allocated }} / {{ $subscription->plan->max_work_spaces }}</span>
                                        <div class="progress">
                                            <div class="progress-bar bg-primary-blue"
                                                role="progressbar"
                                                style="width: {{ $workspacesPercent }}%">
                                            </div>
                                        </div>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @else
            <div class="alert alert-warning">
                No active subscription found. Please contact your administrator.
            </div>
            @endif
        </div>
    </div>
</div>
@endsection