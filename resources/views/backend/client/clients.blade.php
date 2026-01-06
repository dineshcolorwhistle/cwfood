@extends('backend.master', [
'pageTitle' => 'Clients Management',
'activeMenu' => [
'item' => 'Clients',
'subitem' => 'Clients',
'additional' => '',
],
'features' => [
'datatables' => '1',
],
'breadcrumbItems' => [
['label' => 'Batchbase Admin', 'url' => '#'],
['label' => 'Clients']
],])

@push('styles')
@endpush

@section('content')
<div class="container-fluid clients px-0">
    <div class="">
        <div class="card-header d-flex justify-content-between">
            <h1 class="page-title">Batchbase Admin - Clients</h1>
            <div class="right-side content d-flex flex-row-reverse align-items-center">
                <div class="text-end">
                    <button type="button" class="btn btn-primary-orange plus-icon" data-toggle="modal" data-target="#actionModal" id="addClientBtn" title="Add Client">
                        <span class="material-symbols-outlined">add</span>
                    </button>
                </div>
            </div>
        </div>
        <div class="card-body">
            <!-- Loader -->
            <div id="tableSkeleton" class="skeleton-wrapper">
                @for($i=0;$i<6;$i++)
                <div class="skeleton-row"></div>
                @endfor
            </div>

            <table class="table responsiveness custom-wrap" id="dtRecordsView" style="display:none;">
                <thead>
                    <tr>
                        <th class="text-primary-orange align-bottom" width="20%">Name</th>
                        <th class="text-primary-orange align-bottom" width="50%">Description</th>
                        <th class="text-primary-orange align-bottom" width="10%">Subscription Plan</th>
                        <th class="text-primary-orange align-bottom" width="10%">Status</th>
                        <th class="text-primary-orange align-bottom" width="10%"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($clients as $client)
                    <tr data-id="{{ $client->id }}">
                        <td class="text-primary-dark-mud">{{ $client->name }}</td>
                        <td class="text-primary-dark-mud">{{ $client->description }}</td>
                        <td class="text-primary-dark-mud">{{ $client->currentSubscription->plan->subscription_name ?? 'No Plan' }}</td>
                        <td class="text-primary-dark-mud">{{ $client->currentSubscription->active_status ?? 'Active'}}</td>
                        <td class="actions-menu-area">
                            <div class="">
                                <!-- 3-Dot Icon Menu for Grid View -->
                                <div class="dropdown d-flex justify-content-end">
                                    <button class="icon-primary-orange me-0" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                                        <span class="material-symbols-outlined">more_vert</span>
                                    </button>
                                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                        <li>
                                            <span class="dropdown-item text-primary-dark-mud me-2 edit-client"
                                                data-id="{{ $client->id }}"
                                                data-name="{{ $client->name }}"
                                                data-description="{{ $client->description }}"
                                                data-status="{{ $client->status }}"
                                                data-subscription-plan-id="{{ $client->currentSubscription->plan_id ?? '' }}"
                                                data-subscription-type="{{$client->currentSubscription->plan_period?? '0'}}"
                                                data-subscription-discount="{{$client->discount}}"
                                                data-users-allocated="{{ $client->currentSubscription->plan->max_users ?? '' }}"
                                                data-raw-materials-allocated="{{ $client->currentSubscription->plan->max_raw_materials ?? '' }}"
                                                data-skus-allocated="{{ $client->currentSubscription->plan->max_skus ?? '' }}"
                                                data-work-spaces-allocated="{{ $client->currentSubscription->plan->max_work_spaces ?? '' }}"
                                                data-start-date="{{ $client->currentSubscription->start_date ?? '' }}"
                                                data-end-date="{{ $client->currentSubscription->end_date ?? '' }}">
                                                <span class="sidenav-normal ms-2 ps-1">Edit</span>
                                            </span>
                                        </li>
                                        <li>
                                            <span class="dropdown-item text-primary-dark-mud delete-client" data-id="{{ $client->id }}">
                                                <span class="sidenav-normal ms-2 ps-1">Delete</span>
                                            </span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <!-- <span class="icon-primary-orange edit-client"
                                data-id="{{ $client->id }}"
                                data-name="{{ $client->name }}"
                                data-description="{{ $client->description }}"
                                data-status="{{ $client->status }}"
                                data-subscription-plan-id="{{ $client->currentSubscription->plan_id ?? '' }}"
                                data-users-allocated="{{ $client->currentSubscription->users_allocated ?? '' }}"
                                data-raw-materials-allocated="{{ $client->currentSubscription->raw_materials_allocated ?? '' }}"
                                data-skus-allocated="{{ $client->currentSubscription->skus_allocated ?? '' }}"
                                data-work-spaces-allocated="{{ $client->currentSubscription->work_spaces_allocated ?? '' }}">
                                <span class="material-symbols-outlined">edit</span>
                            </span>
                            <span class="icon-primary-orange delete-client" data-id="{{ $client->id }}">
                                <span class="material-symbols-outlined">delete</span>
                            </span> -->
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Client Modal -->
    <div class="modal fade" id="actionModal" tabindex="-1" role="dialog" aria-labelledby="actionModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title text-primary-orange" id="actionModalLabel">Add/Edit Client</h4>
                </div>

                <form id="clientForm">
                    @csrf
                    <div class="modal-body">
                        <input type="hidden" name="client_id" id="client_id">
                        <input type="hidden" name="selected_plan_id" id="selected_plan_id">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="text-primary-orange" for="name">Client Name <span class="text-danger">*</span></label>
                                    <input type="text" name="name" id="name" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="text-primary-orange" for="status">Status</label>
                                    <select name="status" id="status" class="form-control">
                                        @foreach($statuses as $key => $value)
                                        <option value="{{ $key }}">{{ $value }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="text-primary-orange" for="description">Description</label>
                            <textarea name="description" id="description" class="form-control"></textarea>
                        </div>

                        <div class="form-group">
                            <label class="text-primary-orange" for="subscription_plan_id">Subscription Plan</label>
                            <select name="subscription_plan_id" id="subscription_plan_id" class="form-select">
                                <option value="">Select Subscription Plan</option>
                                @foreach($subscriptionPlans as $plan)
                                <option value="{{ $plan->id }}" @if($plan->id != 7) disabled @endif>{{ $plan->subscription_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="row additional">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="text-primary-orange" for="type">Subscription Type</label>
                                    <select name="type" id="type" class="form-control">
                                        <option value="">Select Subscription Type</option>
                                        <option value="Monthly">Monthly</option>
                                        <option value="Yearly">Yearly</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="text-primary-orange" for="discount">Discount (%)</label>
                                    <select name="discount" id="discount" class="form-control">
                                        <option value="0">Select Discount</option>
                                        @for($i = 5; $i <= 100; $i += 5)
                                            <option value="{{ $i }}">{{ $i }}</option>
                                        @endfor
                                    </select>
                                </div>
                            </div>
                        </div>



                        <div id="subscriptionDetails" style="display: none;">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group readonly">
                                        <label class="text-primary-orange" for="users_allocated">Users Allocated</label>
                                        <input type="number" name="users_allocated" id="users_allocated" class="form-control" min="0" readonly>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group readonly">
                                        <label class="text-primary-orange" for="raw_materials_allocated">Raw Materials Allocated</label>
                                        <input type="number" name="raw_materials_allocated" id="raw_materials_allocated" class="form-control" min="0" readonly>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group readonly">
                                        <label class="text-primary-orange" for="skus_allocated">SKUs Allocated</label>
                                        <input type="number" name="skus_allocated" id="skus_allocated" class="form-control" min="0" readonly>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group readonly">
                                        <label class="text-primary-orange" for="work_spaces_allocated">Workspaces Allocated</label>
                                        <input type="number" name="work_spaces_allocated" id="work_spaces_allocated" class="form-control" min="1" readonly>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group readonly">
                                        <label class="text-primary-orange" for="start_date">Start Date</label>
                                        <input type="date" name="start_date" id="start_date" class="form-control" readonly>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group readonly">
                                        <label class="text-primary-orange" for="end_date">End Date</label>
                                        <input type="date" name="end_date" id="end_date" class="form-control" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary-white" data-dismiss="modal" id="closeActionModal">Close</button>
                        <button type="submit" class="btn btn-secondary-blue" id="saveClientBtn">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')

<script type="application/json" id="subscriptionPlansData">
    @json($subscriptionPlans)
</script>

<script>
    $(document).ready(function() {
        const subscriptionPlans = JSON.parse(document.getElementById('subscriptionPlansData').textContent);

        $('#subscription_plan_id').on('change', function() {
            const subscriptionPlanId = $(this).val();

            if(subscriptionPlanId == "7" || subscriptionPlanId == 7){
                $(`.additional`).css('display','none')
            }else{
                $(`.additional`).css('display','flex')
            }

            const $subscriptionDetails = $('#subscriptionDetails');

            if (subscriptionPlanId) {
                // Find the selected plan
                const selectedPlan = subscriptionPlans.find(plan => plan.id == subscriptionPlanId);

                if (selectedPlan) {
                    // Pre-fill allocated values with plan defaults
                    $('#users_allocated').val(selectedPlan.min_users);
                    $('#raw_materials_allocated').val(selectedPlan.max_raw_materials);
                    $('#skus_allocated').val(selectedPlan.max_skus);
                    $('#work_spaces_allocated').val(selectedPlan.max_work_spaces);
                    $('#type').val('Yearly');

                    if(subscriptionPlanId == "7"){
                        // Set default dates
                        const today = new Date();
                        const fourteenDaysLater = new Date(today);
                        fourteenDaysLater.setDate(today.getDate() + 14);

                        $('#start_date').val(today.toISOString().split('T')[0]);
                        $('#end_date').val(fourteenDaysLater.toISOString().split('T')[0]);

                    }else{
                        // Set default dates
                        const today = new Date();
                        const oneYearLater = new Date(today);
                        oneYearLater.setFullYear(today.getFullYear() + 1);

                        $('#start_date').val(today.toISOString().split('T')[0]);
                        $('#end_date').val(oneYearLater.toISOString().split('T')[0]);
                    }

                    $subscriptionDetails.show();
                }
            } else {
                $subscriptionDetails.hide();
            }
        });

        // Add Client Button Click
        $(document).on('click', '#addClientBtn', function() {
            // Reset the form
            $('#clientForm')[0].reset();
            $('#client_id,#selected_plan_id').val('');
            $('#actionModalLabel').text('Add Client');
            $('#saveClientBtn').text('Create');
            $('#subscriptionDetails').hide();
            $('#subscription_plan_id').val("7").trigger('change');
            $('#end_date').prop('readonly',false);
            // Show the modal
            $('#actionModal').modal('show');
        });

        // Edit Client Button Click (using event delegation)
        $(document).on('click', '.edit-client', function() {
            const id = $(this).data('id');
            const name = $(this).data('name');
            const description = $(this).data('description');
            const status = $(this).data('status');
            const subscriptionPlanId = $(this).data('subscription-plan-id');
            const subscriptionPlanType = $(this).data('subscription-type') == 0 ?'Monthly':'Yearly' ;
            const subscriptionPlanDiscount = $(this).data('subscription-discount');
            const usersAllocated = $(this).data('users-allocated');
            const rawMaterialsAllocated = $(this).data('raw-materials-allocated');
            const skusAllocated = $(this).data('skus-allocated');
            const workSpacesAllocated = $(this).data('work-spaces-allocated');
            const startDate = $(this).data('start-date');
            const endDate = $(this).data('end-date');

            $('#client_id').val(id);
            $('#selected_plan_id').val(subscriptionPlanId);
            $('#name').val(name);
            $('#description').val(description);
            $('#status').val(status);
            $('#type').val(subscriptionPlanType).attr('disabled',true);
            $('#discount').val(subscriptionPlanDiscount);

            // Set subscription details
            if (subscriptionPlanId) {
                $('#subscription_plan_id').val(subscriptionPlanId).attr('disabled',true);
                $('#users_allocated').val(usersAllocated);
                $('#raw_materials_allocated').val(rawMaterialsAllocated);
                $('#skus_allocated').val(skusAllocated);
                $('#work_spaces_allocated').val(workSpacesAllocated);
                $('#start_date').val(startDate);
                $('#end_date').val(endDate);
                $('#subscriptionDetails').show();
            } else {
                $('#subscriptionDetails').hide();
            }

            $('#actionModalLabel').text('Edit Client');
            $('#saveClientBtn').text('Update');
            if(subscriptionPlanId == 7){
                $('#end_date').prop('readonly',false);
                $(`.additional`).css('display','none');
            }else{
                $('#end_date').prop('readonly',true);
                $(`.additional`).css('display','flex');
            }
            $('#actionModal').modal('show');


        });

        // Save/Update Client Form Submit
        $('#clientForm').on('submit', function(e) {
            e.preventDefault();
            const clientId = $('#client_id').val();
            const url = clientId ?
                "{{ route('clients.update', ':id') }}".replace(':id', clientId) :
                "{{ route('clients.store') }}";
            const method = clientId ? 'PUT' : 'POST';

            $.ajax({
                url: url,
                method: method,
                data: $(this).serialize(),
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: response.message,
                            timer: 2000
                        }).then(() => {
                            location.reload();
                        });
                    }
                },
                error: function(xhr) {
                    if (xhr.status === 422) {
                        const errors = xhr.responseJSON.errors;
                        let errorMessage = '';

                        for (const [field, messages] of Object.entries(errors)) {
                            errorMessage += `${field}: ${messages.join(', ')}\n`;
                        }

                        Swal.fire({
                            icon: 'warning',
                            title: 'Validation Errors',
                            text: errorMessage
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: xhr.responseJSON.message || 'An error occurred'
                        });
                    }
                }
            });
        });

        // Delete Client Button Click (using event delegation)
        $(document).on('click', '.delete-client', function() {
            const id = $(this).data('id');
            const url = "{{ route('clients.destroy', ':id') }}".replace(':id', id);

            Swal.fire({
                title: 'Are you sure?',
                text: 'You won\'t be able to revert this!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: url,
                        method: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            if (response.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Deleted!',
                                    text: response.message,
                                    timer: 2000
                                }).then(() => {
                                    location.reload();
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


        $(document).on('focusout','#work_spaces_allocated',function(){
            let value = parseInt($(this).val(), 10);
            if (isNaN(value) || value <= 0) {
                $(this).val(1);
            }
        })

    });

    $(document).on('change','#type',function(){
        let val = $(this).val()
        if(val == "Monthly"){
            $('#end_date').val('').prop('disabled',true)
        }else{
            let dateStr = $('#start_date').val()
            if(dateStr){
                let date = new Date(dateStr);
                date.setFullYear(date.getFullYear() + 1);
                let updatedDate = date.toISOString().split('T')[0];
                $('#end_date').val(updatedDate).prop('disabled',false);
            }
        }
    })
</script>
@endpush