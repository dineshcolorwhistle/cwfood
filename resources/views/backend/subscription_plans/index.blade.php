@extends('backend.master', [
'pageTitle' => 'Subscription Plans',
'activeMenu' => [
'item' => 'Subscription Plans',
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
</style>
@endpush
@section('content')
<div class="">
    <div class="">
        <div class="card-header d-flex justify-content-between">
            <h1 class="page-title">Batchbase Admin - Subscription Plans</h1>
            <div class="right-side content d-flex flex-row-reverse align-items-center">
                <div class="text-end">
                    <div class="billing-toggle btn-group" role="group" aria-label="Billing cycle">
                        <button type="button" class="btn toggle-monthly">Monthly</button>
                        <button type="button" class="btn toggle-annual">Annually</button>
                    </div>

                    <button class="btn btn-primary-orange plus-icon" data-bs-toggle="modal" data-bs-target="#createModal" title="Add Subscription">
                        <span class="material-symbols-outlined">add</span>
                    </button>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="d-flex flex-wrap gap-3">
                @foreach ($plans as $plan)

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
                @endphp

                
                <div class="subscription-card">
                    <!-- Card header -->
                    <div class="subscription-card-header pb-2">
                    <h4 class="subscription-name">{{ $plan->subscription_name }}</h4>
                    </div>

                    <!-- Card body -->
                    <div class="subscription-card-body">
                    {{-- Monthly pricing (shown when Monthly is selected) --}}
                    <div class="price-row price-monthly">
                        <span class="amount">$ {{ $fmt0($m) }}</span>
                        <span class="subline">Per User | Per Month | Paid Monthly</span>
                    </div>

                    {{-- Annual pricing (monthly-equivalent; shown when Annual is selected) --}}
                    <div class="price-row price-annual d-none">
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

                    
                    <div class="features">
                        <div class="feature-row">
                            <span class="feature-label">Users</span>
                            <span class="feature-value">{{ $usersCount }}</span>
                        </div>

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

                    <!-- Card footer -->
                    <div class="subscription-card-footer">
                        <button class="btn btn-sm icon-primary-blue" data-bs-toggle="modal" data-bs-target="#editModal" data-plan="{{ json_encode($plan) }}">
                            <span class="material-symbols-outlined">edit</span>
                        </button>
                        <button
                            class="btn btn-sm icon-primary-orange delete-plan"
                            data-id="{{ $plan->id }}"
                            data-name="{{ $plan->subscription_name }}">
                            <span class="material-symbols-outlined">delete</span>
                        </button>
                    </div>
                </div>

                
                @endforeach
            </div>
        </div>
    </div>
</div>

<!-- Create Modal -->
<div class="modal fade" id="createModal" tabindex="-1" aria-labelledby="createModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-primary-orange" id="createModalLabel">Create Subscription Plan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="createForm" action="{{ route('subscription-plans.store') }}" method="POST">
                    @csrf
                    @include('backend.subscription_plans.partials.form', ['buttonText' => 'Create'])
                </form>
            </div>
        </div>
    </div>
</div>


<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-primary-orange" id="editModalLabel">Edit Subscription Plan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editForm" method="POST">
                    @csrf
                    @method('PUT')
                    @include('backend.subscription_plans.partials.form', ['buttonText' => 'Update'])
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>

    (function () {
        function applyCycle(cycle) {
        // Toggle button active state
        document.querySelectorAll('.billing-toggle .btn').forEach(b => b.classList.remove('active'));
        const b = document.querySelector('.toggle-' + cycle);
        if (b) b.classList.add('active');

        // Toggle visibility per card
        document.querySelectorAll('.subscription-card').forEach(card => {
            const m = card.querySelector('.price-monthly');
            const a = card.querySelector('.price-annual');
            if (!m || !a) return;

            if (cycle === 'annual') {
            m.classList.add('d-none');
            a.classList.remove('d-none');
            } else {
            a.classList.add('d-none');
            m.classList.remove('d-none');
            }

            // Attach selected cycle to CTA for backend
            const btn = card.querySelector('button[data-plan]');
            if (btn) btn.setAttribute('data-cycle', cycle);
        });

        // Persist selection
        localStorage.setItem('billing_cycle', cycle);
        const root = document.getElementById('subscription-page');
        if (root) root.setAttribute('data-billing-cycle', cycle);
        }

        document.addEventListener('DOMContentLoaded', function () {
        const defaultCycle = (document.getElementById('subscription-page')?.dataset.billingCycle) || 'monthly';
        const stored = localStorage.getItem('billing_cycle') || defaultCycle;
        applyCycle(stored);

        const mBtn = document.querySelector('.toggle-monthly');
        const aBtn = document.querySelector('.toggle-annual');
        if (mBtn) mBtn.addEventListener('click', () => applyCycle('monthly'));
        if (aBtn) aBtn.addEventListener('click', () => applyCycle('annual'));
        });
    })();


    // Handling the dynamic population of the Edit Modal
    $('#editModal').on('show.bs.modal', function(event) {
        var button = $(event.relatedTarget);
        var plan = button.data('plan');

        var form = $('#editForm');
        form.attr('action', './subscription-plans/' + plan.id); // Set the action URL dynamically

        // Fill the form fields with the plan data
        form.find('[name="plan_code"]').val(plan.plan_code);
        form.find('[name="subscription_name"]').val(plan.subscription_name);
        form.find('[name="ideal_for"]').val(plan.ideal_for);
        form.find('[name="monthly_cost_per_user"]').val(plan.monthly_cost_per_user);
        form.find('[name="annual_cost_per_user"]').val(plan.annual_cost_per_user);
        form.find('[name="min_annual_cost"]').val(plan.min_annual_cost);
        form.find('[name="max_users"]').val(plan.max_users);
        form.find('[name="min_users"]').val(plan.min_users);
        form.find('[name="max_raw_materials"]').val(plan.max_raw_materials);
        form.find('[name="max_skus"]').val(plan.max_skus);
        form.find('[name="max_work_spaces"]').val(plan.max_work_spaces);
        form.find('[name="custom_pricing"]').val(plan.custom_pricing);
    });

    // Handle form submission via AJAX for both create and edit forms
    $('#createForm, #editForm').on('submit', function(e) {
        e.preventDefault();
        let formData = new FormData(this);
        let actionUrl = $(this).attr('action');
        let form = $(this);

        // Remove previous error messages
        form.find('.text-danger').remove();
        form.find('.is-invalid').removeClass('is-invalid');

        $.ajax({
            url: actionUrl,
            method: $(this).attr('method'),
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                Swal.fire('Success!', response.message, 'success');
                location.reload(); // Reload the page to see the updated list
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    // Handle validation errors
                    let errors = xhr.responseJSON.errors;

                    $.each(errors, function(field, messages) {
                        let input = form.find(`[name="${field}"]`);
                        input.addClass('is-invalid');
                        input.after(`<div class="text-danger">${messages[0]}</div>`);
                    });

                    Swal.fire('Validation Error!', 'Please check the form fields.', 'error');
                } else {
                    Swal.fire('Error!', 'Something went wrong. Please try again.', 'error');
                }
            }
        });
    });


    $(document).on('click', '.delete-plan', function() {
        let planId = $(this).data('id');
        let planName = $(this).data('name');

        Swal.fire({
            title: `Are you sure you want to delete the subscription plan "${planName}"?`,
            text: "This action cannot be undone!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `{{ url('subscription-plans') }}/${planId}`,
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        _method: 'DELETE'
                    },
                    success: function(response) {
                        Swal.fire(
                            'Deleted!',
                            response.message,
                            'success'
                        );
                        location.reload(); // Reload the page to see the changes
                    },
                    error: function(xhr) {
                        Swal.fire(
                            'Error!',
                            'Something went wrong. Please try again.',
                            'error'
                        );
                    }
                });
            }
        });
    });
</script>
@endpush