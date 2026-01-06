@extends('backend.master', [
'pageTitle' => $pageTitle,
'activeMenu' => [
'item' => 'Roles',
'subitem' => $pageTitle,
'additional' => '',
],
'features' => [
'datatables' => '1',
],
'breadcrumbItems' => [
['label' => 'Batchbase Admin', 'url' => '#'],
['label' => $pageTitle]
],
])


@section('content')  
<div class="container-fluid roles px-0">
    <div class="">
        <div class="card-header d-flex justify-content-between">
            <h1 class="page-title">Batchbase Admin - {{ $pageTitle }}</h1>
            @if (!str_contains(request()->path(), 'team-member-roles'))
            <div class="right-side content d-flex flex-row-reverse align-items-center">
                <div class="text-end">
                    <button type="button" class="btn btn-primary-orange plus-icon" data-toggle="modal" data-target="#actionModal" id="addRoleBtn" title="Add User Role">
                        <span class="material-symbols-outlined">add</span>
                    </button>
                </div>
            </div>
            @endif
        </div>
        <div class="card-body">
            <table class="table responsiveness" id="dtRecordsView">
                <thead>
                    <tr>
                        <th class="text-primary-orange">Order</th>
                        <th class="text-primary-orange">Name</th>
                        <th class="text-primary-orange">Description</th>
                        <th class="text-primary-orange">Scope</th>
                        <th class="text-primary-orange"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($roles as $role)
                    <tr data-id="{{ $role->id }}">
                        <td class="text-primary-dark-mud">{{ $role->order_number }}</td>
                        <td class="text-primary-dark-mud">{{ $role->name }}</td>
                        <td class="text-primary-dark-mud">{{ $role->description }}</td>
                        <td class="text-primary-dark-mud">{{ $scopes[$role->scope] ?? $role->scope }}</td>
                        <td class="actions-menu-area">
                            <div class="action">
                                <!-- 3-Dot Icon Menu for Grid View -->
                                <div class="dropdown d-flex justify-content-end">
                                    <button class="icon-primary-orange me-0" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                                        <span class="material-symbols-outlined">more_vert</span>
                                    </button>
                                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                        <li>
                                            <span class="dropdown-item text-primary-dark-mud me-2 edit-role"
                                                data-id="{{ $role->id }}"
                                                data-order="{{ $role->order_number }}"
                                                data-name="{{ $role->name }}"
                                                data-description="{{ $role->description }}"
                                                data-scope="{{ $role->scope }}">
                                                <span class="sidenav-normal ms-2 ps-1">Edit</span>
                                            </span>
                                        </li>
                                        <li>
                                            <span class="dropdown-item text-primary-dark-mud delete-role" data-id="{{ $role->id }}">
                                                <span class="sidenav-normal ms-2 ps-1">Delete</span>
                                            </span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </td>
                        {{--
                        <td>
                            <span class="icon-primary-orange edit-role"
                                data-id="{{ $role->id }}"
                        data-name="{{ $role->name }}"
                        data-description="{{ $role->description }}"
                        data-scope="{{ $role->scope }}">
                        <span class="material-symbols-outlined">edit</span>
                        </span>
                        <span class="icon-primary-orange delete-role" data-id="{{ $role->id }}">
                            <span class="material-symbols-outlined">delete</span>
                        </span>
                        </td>
                        --}}
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Role Modal -->
    <div class="modal fade" id="actionModal" tabindex="-1" role="dialog" aria-labelledby="actionModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title text-primary-orange" id="actionModalLabel">Add/Edit Role</h4>
                </div>

                <form id="roleForm">
                    @csrf
                    <div class="modal-body">
                        <input type="hidden" name="role_id" id="role_id">
                        
                        <div class="form-group">
                            <label class="text-primary-orange" for="name">Oreder Number <span class="text-danger">*</span></label>
                            <select name="order_number" id="order_number" class="form-control" required>
                                <option selected disabled>Select Number</option>
                                @for($i=1; $i < 11; $i++)
                                    <option value="{{ $i }}">{{ $i }}</option>
                                @endfor
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="text-primary-orange" for="name">Role Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="name" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label class="text-primary-orange" for="description">Description</label>
                            <textarea name="description" id="description" class="form-control"></textarea>
                        </div>

                        <div class="form-group">
                            <label class="text-primary-orange" for="scope">Scope <span class="text-danger">*</span></label>
                            <select name="scope" id="scope" class="form-control" required>
                                @if (str_contains(request()->path(), 'team-member-roles'))
                                @foreach($scopes as $key => $value)
                                @if (strtolower($value) == 'platform')
                                <option value="{{ $key }}">{{ $value }}</option>
                                @endif
                                @endforeach
                                @else
                                @foreach($scopes as $key => $value)
                                @if (strtolower($value) != 'platform')
                                <option value="{{ $key }}">{{ $value }}</option>
                                @endif
                                @endforeach
                                @endif

                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary-white" data-dismiss="modal" id="closeActionModal">Close</button>
                        <button type="submit" class="btn btn-secondary-blue" id="saveRoleBtn">Save</button>
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
        // Use event delegation for dynamically added elements
        $(document).on('click', '#addRoleBtn', function() {
            // Reset the form
            $('#roleForm')[0].reset();
            $('#role_id').val('');
            $('#actionModalLabel').text('Add Role');
            $('#saveRoleBtn').text('Create');

            // Show the modal
            $('#actionModal').modal('show');
        });

        // Edit Role Button Click (using event delegation)
        $(document).on('click', '.edit-role', function() {
            const id = $(this).data('id');
            const order = $(this).data('order');
            const name = $(this).data('name');
            const description = $(this).data('description');
            const scope = $(this).data('scope');

            $('#role_id').val(id);
            $('#order_number').val(order);
            $('#name').val(name);
            $('#description').val(description);
            $('#scope').val(scope);

            $('#actionModalLabel').text('Edit Role');
            $('#saveRoleBtn').text('Update');
            $('#actionModal').modal('show');
        });

        // Save/Update Role Form Submit
        $('#roleForm').on('submit', function(e) {
            e.preventDefault();
            const roleId = $('#role_id').val();
            const url = roleId ?
                "{{ route('roles.update', ':id') }}".replace(':id', roleId) :
                "{{ route('roles.store') }}";
            const method = roleId ? 'PUT' : 'POST';

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

        // Delete Role Button Click (using event delegation)
        $(document).on('click', '.delete-role', function() {
            const id = $(this).data('id');
            const url = "{{ route('roles.destroy', ':id') }}".replace(':id', id);

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
    });
</script>
@endpush