@extends('backend.master', [
'pageTitle' => $pageTitle,
'activeMenu' => [
'item' => 'Users',
'subitem' => $pageTitle,
'additional' => '',
],
'features' => [
'datatables' => '1',
],
'breadcrumbItems' => [
['label' => 'Batchbase Admin', 'url' => '#'],
['label' => $pageTitle]
],])


@push('styles')

@endpush

@section('content')
<div class="container-fluid users px-0">
    <div class="">
        <div class="card-header d-flex justify-content-between">
            <h1 class="page-title">Batchbase Admin - {{$pageTitle}}</h1>
            <div class="right-side content d-flex flex-row-reverse align-items-center">
                <div class="text-end">
                    <button type="button" class="btn btn-primary-orange plus-icon" id="addUserBtn" title="Add Member">
                        <span class="material-symbols-outlined">add</span>
                    </button>
                </div>
            </div>
        </div>
        <div class="card-body">
            <table class="table responsiveness" id="dtRecordsView">
                <thead>
                    <tr>
                        <th class="text-primary-orange">Name</th>
                        <th class="text-primary-orange">Email</th>
                        <th class="text-primary-orange">Role</th>
                        <th class="text-primary-orange" style="display:none;">Client</th>
                        <th class="text-primary-orange"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                    <tr data-id="{{ $user->id }}">
                        <td class="text-primary-dark-mud">{{ $user->name }}</td>
                        <td class="text-primary-dark-mud">{{ $user->email }}</td>
                        <td class="text-primary-dark-mud">{{ $user->role->name ?? 'N/A' }}</td>
                        <td class="text-primary-dark-mud" style="display:none;">{{ $user->client->name ?? 'N/A' }}</td>
                        <td class="actions-menu-area">
                            <div class="">
                                <!-- 3-Dot Icon Menu for Grid View -->
                                @if($user->id >1)
                                <div class="dropdown d-flex justify-content-end">
                                    <button class="icon-primary-orange me-0" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                                        <span class="material-symbols-outlined">more_vert</span>
                                    </button>
                                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                        <li>
                                            <span class="dropdown-item text-primary-dark-mud me-2 edit-user"
                                                data-id="{{ $user->id }}"
                                                data-name="{{ $user->name }}"
                                                data-email="{{ $user->email }}"
                                                data-role-id="{{ $user->role_id }}"
                                                data-role-name="{{ $user->role->name ?? '' }}"
                                                data-role-scope="{{ $user->role->scope ?? '' }}"
                                                data-client-id="{{ $user->client_id }}"
                                                data-client-name="{{ $user->client->name ?? '' }}">
                                                <span class="sidenav-normal ms-2 ps-1">Edit</span>
                                            </span>
                                        </li>

                                        <li>
                                            <span class="dropdown-item text-primary-dark-mud delete-user" data-id="{{ $user->id }}">
                                                <span class="sidenav-normal ms-2 ps-1">Delete</span>
                                            </span>
                                        </li>

                                    </ul>
                                </div>
                                @endif
                            </div>
                        </td>

                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- User Modal -->
    <div class="modal fade" id="actionModal" tabindex="-1" role="dialog" aria-labelledby="actionModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title text-primary-orange" id="actionModalLabel">Add/Edit User</h4>
                </div>
                <form id="userForm" enctype="multipart/form-data">
                    @csrf

                    <div class="modal-body">
                        <input type="hidden" name="user_id" id="user_id">

                        <div class="form-group">
                            <label class="text-primary-orange" for="name">Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="name" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label class="text-primary-orange" for="email">Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" id="email" class="form-control" required>
                        </div>

                        <!-- <div class="form-group">
                            <label class="text-primary-orange" for="password">Password</label>
                            <input type="password" name="password" id="password" class="form-control" value="Secret">
                            <small class="form-text text-muted">Leave blank to keep current password<i>(Default Password: Secret)</i></small>
                        </div> -->

                        <div class="form-group d-flex flex-column gap-1">
                            <label class="text-primary-orange" for="role_id">Role <span class="text-danger">*</span></label>
                            <select name="role_id" id="role_id" class="form-control select2" required>
                                @foreach($roles as $role)
                                <option value="{{ $role->id }}" data-scope="{{ $role->scope }}">{{ $role->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        {{--
                        <div class="form-group hidden" id="clientFieldGroup">
                            <label class="text-primary-orange" for="client_id">Client</label>
                            <select name="client_id" id="client_id" class="form-control">
                                <option value="">Select Client</option>
                                @foreach($clients as $client)
                                <option value="{{ $client->id }}">{{ $client->name }}</option>
                        @endforeach
                        </select>
                    </div>
                    --}}
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary-white" data-dismiss="modal" id="closeActionModal">Close</button>
                <button type="submit" class="btn btn-secondary-blue" id="saveUserBtn">Save User</button>
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
        // Function to toggle client field visibility
        function toggleClientField() {
            const selectedRole = $('#role_id option:selected');
            const roleScope = selectedRole.data('scope');
            const clientFieldGroup = $('#clientFieldGroup');

            if (roleScope === 'platform') {
                clientFieldGroup.hide();
                $('#client_id').val('');
            } else {
                clientFieldGroup.show();
            }
        }

        // Toggle client field on role change
        $('#role_id').on('change', function() {
            toggleClientField();
        });

        // Add User Modal Handling
        $('#addUserBtn').on('click', function() {
            // Reset the form
            $('#userForm')[0].reset();
            $('#user_id').val('');
            $('#role_id').val(null).trigger('change');
            $('#client_id').val('');
            $('#password').val('Secret');
            $('#actionModalLabel').text('Add ' + (window.location.pathname.includes('team') ? 'Team Member' : 'Member'));
            $('#saveUserBtn').text('Create');

            // Trigger client field visibility
            toggleClientField();

            // Show the modal
            $('#actionModal').modal('show');
        });

        // Edit User Modal Handling
        $(document).on('click', '.edit-user', function() {
            const id = $(this).data('id');
            const name = $(this).data('name');
            const email = $(this).data('email');
            const roleId = $(this).data('role-id');
            const roleName = $(this).data('role-name');
            const roleScope = $(this).data('role-scope');
            const clientId = $(this).data('client-id');
            const clientName = $(this).data('client-name');

            $('#user_id').val(id);
            $('#name').val(name);
            $('#email').val(email);
            $('#password').val('');

            // Properly set the role dropdown
            if (roleId && roleName) {
                var newOption = new Option(roleName, roleId, true, true);
                $('#role_id').val(roleId).trigger('change');
            }

            // Set client dropdown and visibility
            if (roleScope === 'platform') {
                $('#clientFieldGroup').hide();
                $('#client_id').val('');
            } else {
                $('#clientFieldGroup').show();
                $('#client_id').val(clientId || '');
            }

            $('#actionModalLabel').text('Edit ' + (window.location.pathname.includes('team') ? 'Team Member' : 'Member'));
            $('#saveUserBtn').text('Update');
            $('#actionModal').modal('show');
        });

        // Form Submission
        $('#userForm').on('submit', function(e) {
            e.preventDefault();

            const $form = $(this);
            const $submitButton = $('#saveUserBtn');

            // Disable submit button to prevent multiple submissions
            $submitButton.prop('disabled', true);

            const userId = $('#user_id').val();
            const isTeamMember = window.location.pathname.includes('team');

            // Determine the correct route based on context
            let url;
            if (userId) {
                // Update routes
                url = isTeamMember ?
                    "{{ route('team-members.update', ':id') }}".replace(':id', userId) :
                    "{{ route('members.update', ':id') }}".replace(':id', userId);
            } else {
                // Store routes
                url = isTeamMember ?
                    "{{ route('team-members.store') }}" :
                    "{{ route('members.store') }}";
            }

            const method = userId ? 'POST' : 'POST';
            const formData = new FormData(this);

            if (userId) {
                formData.append('_method', 'PUT');
            }

            // If platform role, remove client_id
            const roleScope = $('#role_id option:selected').data('scope');
            if (roleScope === 'platform') {
                formData.delete('client_id');
            }

            // Ensure password is 'Secret' if blank for new users
            if (!userId && !formData.get('password')) {
                formData.set('password', 'Secret');
            }

            $.ajax({
                url: url,
                method: method,
                data: formData,
                processData: false,
                contentType: false,
                complete: function() {
                    // Re-enable submit button
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

        // Delete User Handling
        $(document).on('click', '.delete-user', function() {
            const id = $(this).data('id');
            const isTeamMember = window.location.pathname.includes('team');

            // Determine the correct delete route based on context
            const url = isTeamMember ?
                "{{ route('team-members.destroy', ':id') }}".replace(':id', id) :
                "{{ route('members.destroy', ':id') }}".replace(':id', id);

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
    });
</script>
@endpush