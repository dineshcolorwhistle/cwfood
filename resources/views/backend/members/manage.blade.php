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
['label' => 'Nutriflow Admin', 'url' => '#'],
['label' => $pageTitle]
],])


@push('styles')
<style>
    div#custom-text p {font-size: 11px;color: #808080ab !important;}
</style>
@endpush

@section('content')
<div class="container-fluid users px-0">
    <div class="">
        <div class="card-header d-flex justify-content-between">
            <h1 class="page-title">{{$pageTitle}}</h1>
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
                        <th class="text-primary-orange">Workspace</th>
                        <th class="text-primary-orange"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                    <tr data-id="{{ $user->id }}">
                        <td class="text-primary-dark-mud">{{ $user->name }}</td>
                        <td class="text-primary-dark-mud">{{ $user->email }}</td>
                        <td class="text-primary-dark-mud">{{ $user->role->name ?? 'N/A' }}</td>
                        <td class="text-primary-dark-mud">@if($user->role_id == 2) ALL @else {{ $user->assign_ws_name ?? 'N/A' }} @endif</td>
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
                                                data-user-id="{{ $user->user_id }}"
                                                data-name="{{ $user->name }}"
                                                data-email="{{ $user->email }}"
                                                data-role-id="{{ $user->role_id }}"
                                                data-role-name="{{ $user->role->name ?? '' }}"
                                                data-role-scope="{{ $user->role->scope ?? '' }}"
                                                data-client-id="{{ $user->client_id }}"
                                                data-workspaceassign="{{ $user->assign ?? '' }}">
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
                        <input type="hidden" name="member_id" id="member_id">
                        <div class="form-group">
                            <label class="text-primary-orange" for="name">Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="name" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label class="text-primary-orange" for="email">Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" id="email" class="form-control" required>
                        </div>

                        <div class="form-group" style="display:none;">
                            <label class="text-primary-orange" for="password">Password</label>
                            <input type="password" name="password" id="password" class="form-control" value="Secret">
                            <small class="form-text text-muted">Leave blank to keep current password<i>(Default Password: Secret)</i></small>
                        </div>

                        <div class="form-group d-flex flex-column gap-1">
                            <label class="text-primary-orange" for="role_id">Role <span class="text-danger">*</span></label>
                            <select name="role_id" id="role_id" class="form-control select2" required>
                                @foreach($roles as $role)
                                <option value="{{ $role->id }}" data-scope="{{ $role->scope }}" data-desc="{{ $role->description }}">{{ $role->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div id="custom-text"></div>

                        <div class="form-group d-flex flex-column gap-1" id="export_column">
                            <label class="text-primary-orange" for="role_id">Workspaces</label>

                            <div class="form-check-temp p-1">
                                <input class="form-check-input" type="checkbox" value="" id="workspaceDefault">
                                <label class="form-check-label" for="workspaceDefault">ALL</label>
                            </div>
                            @foreach($workspaces as $workspace)
                            <div class="form-check-temp p-1">
                                <input class="form-check-input wordpress_check" type="checkbox" value="" id="ws_{{$workspace['id']}}">
                                <label class="form-check-label" for="ws_{{$workspace['id']}}">{{$workspace['name']}}</label>
                            </div>
                            @endforeach
                        </div>
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
            let val = $(this).val()
            var desc = $(this).find('option:selected').data('desc');
            (desc != undefined) ?$('#custom-text').html(`<p>${desc}</p>`):"";
            if(val == 2 || val == 3){
                $('#export_column').attr('style', 'display: none !important;');
                $('#workspaceDefault').prop('checked', true).prop('disabled', true);
                $('input.wordpress_check').each(function() {
                    $(this).prop('checked', true).prop('disabled', true);
                });
            }else if(val == 4 || val == 6 || val == 7){
                $('#export_column').attr('style', 'display: block !important;');
                $('#workspaceDefault').prop('checked', false).prop('disabled', false);
                $('input.wordpress_check').each(function() {
                    $(this).prop('checked', false).prop('disabled', false);
                });
            }else{
                $('#export_column').attr('style', 'display: none !important;');
            }
            // toggleClientField();
        });

        // Add User Modal Handling
        $('#addUserBtn').on('click', function() {
            // Reset the form
            $('#userForm')[0].reset();
            $('#user_id,#member_id').val('');
            $('#role_id').val(null).trigger('change');
            $('#client_id').val('');
            $('#password').val('Secret');
            $('#actionModalLabel').text('Add Member');
            $('#saveUserBtn').text('Create');

            // Trigger client field visibility
            toggleClientField();

            // Show the modal
            $('#actionModal').modal('show');
        });

        // Edit User Modal Handling
        $(document).on('click', '.edit-user', function() {
            const id = $(this).data('id');
            const user_id = $(this).data('user-id');
            const name = $(this).data('name');
            const email = $(this).data('email');
            const roleId = $(this).data('role-id');
            const roleName = $(this).data('role-name');
            const roleScope = $(this).data('role-scope');
            const clientId = $(this).data('client-id');
            const workspace_Assign = $(this).data('workspaceassign');
            $('#member_id').val(id);
            $('#user_id').val(user_id);
            $('#name').val(name);
            $('#email').val(email);
            $('#password').val('');

            // Properly set the role dropdown
            if (roleId && roleName) {
                var newOption = new Option(roleName, roleId, true, true);
                $('#role_id').val(roleId).trigger('change');
            }

            if(workspace_Assign){
                const dr_length = $('#export_column').find('div.form-check-temp').length - 1
                const myArr =  workspace_Assign.toString().split(',');
                console.log(myArr);
                
                $('#export_column').find('div.form-check-temp input').each(function() {
                    if($(this).attr('id') == "workspaceDefault"){
                        if(dr_length == myArr.length){
                            $(this).prop('checked', true)
                        }
                    }else{
                        let label_id =  $(this).attr('id').replace('ws_',''); // Get the label text
                        if(jQuery.inArray(label_id, myArr) != -1) {
                            $(this).prop('checked', true)
                        }
                    }
                });
            } 
            $('#actionModalLabel').text('Edit Member');
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
            const memberId = $('#member_id').val();
            // Determine the correct route based on context
            let url;
            url = "{{ route('members.store') }}";
            const method = userId ? 'POST' : 'POST';
            const formData = new FormData(this);
            if (userId) {
                url = "{{ route('members.update', ':id') }}".replace(':id', memberId);;
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

            let selectedLabels = [];
            $('#export_column').find('div.form-check-temp input').each(function() {
                if ($(this).is(':checked')) {
                    if($(this).attr('id') != "workspaceDefault"){
                        let label =  $(this).attr('id').replace('ws_',''); // Get the label text
                        if (label) {
                            selectedLabels.push(label);
                        }
                    }
                }
            });
            formData.append('assign_workspace', selectedLabels);
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
                    }else{
                        Swal.fire({
                            icon: 'warning',
                            title: 'Warning!',
                            text: response.message,
                        })
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
            // Determine the correct delete route based on context
            const url = "{{ route('members.destroy', ':id') }}".replace(':id', id);
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

    $(document).on('click','#workspaceDefault',function() {
        var _this = this;
        $('input.wordpress_check').each(function() {
        if ($(_this).is(':checked')) {
            $(this).prop('checked', true);
        } else {
            $(this).prop('checked', false);
        }
        });
    });

</script>
@endpush