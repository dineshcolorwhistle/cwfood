@extends('backend.master', [
'pageTitle' => 'Subscription Plans',
'activeMenu' => [
'item' => 'Subscription Plans',
'subitem' => '',
]
])

@section('content')
<div class="my-4">
    <!-- <div class="card">
        <div class="card-header d-flex justify-content-between">
            <h5>Subscription Plans</h5>
            <a href="{{ route('subscription-plans.create') }}" class="btn btn-primary-orange plus-icon"><span class="material-symbols-outlined">add</span></a>
        </div>
        <div class="card-body">
            <table class="table dataTable-table">
                <thead>
                    <tr>
                        <th class="text-primary-orange">Code</th>
                        <th class="text-primary-orange">Name</th>
                        <th class="text-primary-orange">Monthly Cost/User</th>
                        <th class="text-primary-orange">Annual Cost/User</th>
                        <th class="text-primary-orange">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($plans as $plan)
                    <tr>
                        <td class="text-primary-dark-mud">{{ $plan->plan_code }}</td>
                        <td class="text-primary-dark-mud">{{ $plan->subscription_name }}</td>
                        <td class="text-primary-dark-mud">${{ number_format($plan->monthly_cost_per_user, 2) }}</td>
                        <td class="text-primary-dark-mud">${{ number_format($plan->annual_cost_per_user, 2) }}</td>
                        <td>
                            <a href="{{ route('subscription-plans.edit', $plan->id) }}" class="btn btn-sm icon-primary-orange"><span class="material-symbols-outlined">edit</span></a>
                            <form action="{{ route('subscription-plans.destroy', $plan->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm icon-primary-orange"><span class="material-symbols-outlined">delete</span></button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div> -->
    <div class="">
        <div class="card-header d-flex justify-content-between">
            <h5>Subscription Plans</h5>
            <a href="{{ route('subscription-plans.create') }}" class="btn btn-primary-orange plus-icon">
                <span class="material-symbols-outlined">add</span>
            </a>
        </div>
        <div class="card-body">
            <div class="d-flex flex-wrap gap-3">
                @foreach ($plans as $plan)
                <div class="subscription-card">
                    <div class="subscription-card-header">
                        <h4 class="subscription-name">{{ $plan->subscription_name }}</h4>
                        <!-- <p class="plan-code mb-0">Code: {{ $plan->plan_code }}</p> -->
                    </div>
                    <div class="subscription-card-body">
                        <p class="cost"><sup class="text-primary-orange-sm">AUD</sup><span class="text-primary-orange-lg pe-1"> {{ number_format($plan->monthly_cost_per_user) }}</span><span class="text-dark-mud-sm fw-bold">Per Month</span></p>
                        <!-- <p class="cost"><sup class="text-primary-orange-sm">AUD:</sup><span class="text-primary-orange-lg"> ${{ number_format($plan->annual_cost_per_user, 2) }}</span><span class="text-dark-mud">Per Month</span></p> -->
                        <p><Span class="text-primary-orange-sm fw-bold pe-1">{{ $plan->max_raw_materials }}</span><span class="text-dark-mud-sm"> Raw Materials</span></p>
                        <p><Span class="text-primary-orange-sm fw-bold pe-1">{{ $plan->max_skus }}</span><span class="text-dark-mud-sm"> SKU's</span></p>
                        <p><Span class="text-primary-orange-sm fw-bold pe-1">{{ $plan->max_users }}</span><span class="text-dark-mud-sm"> Users</span></p>
                        <p><Span class="text-primary-orange-sm fw-bold pe-1">{{ $plan->max_work_spaces }}</span><span class="text-dark-mud-sm"> Word Spaces</span></p>
                    </div>
                    <div class="subscription-card-footer">
                        <a href="{{ route('subscription-plans.edit', $plan->id) }}" class="btn btn-sm icon-primary-blue">
                            <span class="material-symbols-outlined">edit</span>
                        </a>
                        <form action="{{ route('subscription-plans.destroy', $plan->id) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm icon-primary-orange"><span class="material-symbols-outlined">delete</span></button>
                        </form>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $('#subscriptionForm').on('submit', function(e) {
        e.preventDefault(); // Prevent the default form submission

        let formData = new FormData(this);

        $.ajax({
            url: $(this).attr('action'),
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                Swal.fire('Success!', 'Subscription Plan Created Successfully!', 'success');
                // Optionally reload or redirect
            },
            error: function(xhr) {
                Swal.fire('Error!', 'Something went wrong. Please try again.', 'error');
            }
        });
    });
</script>
@endpush