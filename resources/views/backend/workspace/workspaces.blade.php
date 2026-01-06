@extends('backend.master', [
'pageTitle' => 'Workspace Management',
'activeMenu' => [
'item' => 'Workspace',
'subitem' => 'Workspaces',
'additional' => '',
],
'features' => [
'datatables' => '1',
],
'breadcrumbItems' => [
['label' => 'Nutriflow Admin', 'url' => '#'],
['label' => 'Workspace Management']
],
])

@push('styles')
@endpush

@section('content')
<div class="container-fluid workspaces my-4">
    <div class="">
        <div class="card-header d-flex justify-content-between">
            <h1 class="page-title">Workspaces</h1>
            <div class="right-side content d-flex flex-row-reverse align-items-center">
                <div class="text-end">
                    <button type="button" class="btn btn-primary-orange plus-icon" id="addWorkspaceBtn" title="Add Workspace">
                        <span class="material-symbols-outlined">add</span>
                    </button>
                </div>
            </div>
        </div>
        <div class="card-body">
            <table class="table responsiveness" id="dtRecordsView">
                <thead>
                    <tr>
                        <th class="text-primary-orange">Workspace Name</th>
                        <th class="text-primary-orange">Description</th>
                        <th class="text-primary-orange">Status</th>
                        <th class="text-primary-orange">Created At</th>
                        <th class="text-primary-orange text-center">Primary</th>
                        <th class="text-primary-orange"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($workspaces as $workspace)
                    <tr data-id="{{ $workspace->id }}">
                        <td class="text-primary-dark-mud">{{ $workspace->name }}</td>
                        <td class="text-primary-dark-mud">{{ $workspace->description ?? '-' }}</td>
                        <td class="text-primary-dark-mud">
                            {{ $workspace->status ? 'Active' : 'Inactive' }}
                        </td>
                        <td class="text-primary-dark-mud">
                            {{ $workspace->created_at->format('Y-m-d H:i:s') }}
                        </td>
                        <td class="text-primary-dark-mud text-center">
                            <input class="form-check-input" type="checkbox"  name="workspace_primary" @if($workspace->ws_primary == 1) checked @endif>
                        </td>
                        <td class="actions-menu-area">
                            <div class="">
                                <!-- 3-Dot Icon Menu for Grid View -->
                                <div class="dropdown d-flex justify-content-end">
                                    <button class="icon-primary-orange me-0" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                                        <span class="material-symbols-outlined">more_vert</span>
                                    </button>
                                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                        <li>
                                            <span class="dropdown-item text-primary-dark-mud me-2 edit-workspace"
                                                data-id="{{ $workspace->id }}"
                                                data-name="{{ $workspace->name }}"
                                                data-status="{{ $workspace->status }}"
                                                data-description="{{ $workspace->description }}">
                                                <span class="sidenav-normal ms-2 ps-1">Edit</span>
                                            </span>
                                        </li>
                                        <li>
                                            <span class="dropdown-item text-primary-dark-mud delete-workspace" data-id="{{ $workspace->id }}">
                                                <span class="sidenav-normal ms-2 ps-1">Delete</span>
                                            </span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Workspace Modal for Add/Edit -->
    <div class="modal fade" id="actionModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title text-primary-orange" id="actionModalLabel">Add/Edit Workspace</h2>
                </div>
                <form id="workspaceForm">
                    @csrf
                    <input type="hidden" id="client_id" value="{{ $clientId }}">
                    <input type="hidden" id="workspace_id" value="">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label class="text-primary-orange" for="name">Workspace Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" id="name" class="form-control" required>
                            </div>

                            <div class="col-md-6 form-group">
                                <label class="text-primary-orange" for="status">Status <span class="text-danger">*</span></label>
                                <select name="status" id="status" class="form-control">
                                    <option value="1">Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                            </div>

                            <div class="col-md-12 form-group">
                                <label class="text-primary-orange" for="description">Description</label>
                                <textarea name="description" id="description" class="form-control" rows="3"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary-blue" id="closeActionModal">Close</button>
                        <button type="submit" class="btn btn-secondary-blue" id="saveWorkspaceBtn">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Add Workspace Modal Handling
        $('#addWorkspaceBtn').on('click', function() {
            $('#workspaceForm')[0].reset();
            $('#workspace_id').val('');
            $('#actionModalLabel').text('Add Workspace');
            $('#saveWorkspaceBtn').text('Create');
            $('#actionModal').modal('show');
        });

        // Edit Workspace Modal Handling
        $('.edit-workspace').on('click', function() {
            $('#workspace_id').val($(this).data('id'));
            $('#name').val($(this).data('name'));
            $('#status').val($(this).data('status'));
            $('#description').val($(this).data('description'));
            $('#actionModalLabel').text('Edit Workspace');
            $('#saveWorkspaceBtn').text('Update');
            $('#actionModal').modal('show');
        });

        // Form Submission
        $('#workspaceForm').on('submit', function(e) {
            e.preventDefault();

            const $form = $(this);
            const $submitButton = $('#saveWorkspaceBtn');
            $submitButton.prop('disabled', true);

            const workspaceId = $('#workspace_id').val();
            const url = workspaceId ?
                "{{ route('client.workspaces.update', ['client_id' => $clientId, 'workspace' => ':id']) }}".replace(':id', workspaceId) :
                "{{ route('client.workspaces.store', ['client_id' => $clientId]) }}";
            const method = workspaceId ? 'POST' : 'POST';

            const formData = new FormData(this);
            if (workspaceId) {
                formData.append('_method', 'PUT');
            }

            $.ajax({
                url: url,
                method: method,
                data: formData,
                processData: false,
                contentType: false,
                complete: function() {
                    $submitButton.prop('disabled', false);
                },
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

        // Delete Workspace Handling
        $('.delete-workspace').on('click', function() {
            const id = $(this).data('id');
            const url = "{{ route('client.workspaces.destroy', ['client_id' => $clientId, 'workspace' => ':id']) }}".replace(':id', id);

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

        $(document).on('click','input[name="workspace_primary"]',function(){
            let cid = $('#client_id').val()
            let ws_id = $(this).closest('tr').attr('data-id')
            let pr_value
            if ($(this).prop('checked')==true){ 
                pr_value = true
            }else{
                pr_value = false
            }

            let data = {'_token':$('meta[name="csrf-token"]').attr('content'),'cid':cid,'ws_id':ws_id,'pr_value':pr_value};	
            $.ajax({
                type: "POST",
                url: "{{route('make_primary')}}",
                dataType: 'json',
                data: data,
                beforeSend: function () {
                },
                success: function (response) {    
                    if(response['status'] == true){
                        show_swal(1, response.message);
                    }else{
                        show_swal(0, response.message,);
                    }
                    window.location.href="{{ route('client.workspaces.index', ['client_id' => $clientId]) }}";
                },
                complete: function(){}
            });
            
        })
    });
</script>
@endpush