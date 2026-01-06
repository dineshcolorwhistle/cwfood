@extends('backend.master', [
'pageTitle' => 'User Profile',
'activeMenu' => [
'item' => 'Profile',
'subitem' => 'Update Profile',
'additional' => '',
],
'breadcrumbItems' => [
['label' => 'Profile', 'url' => route('ana.dashboard')],
['label' => 'Update Profile']
],])

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
@endpush

@section('content')
<div class="container-fluid user-profile px-0">
    <div class="">
        <div class="card-header">
            <h1 class="page-title text-primary-orange">Update Profile</h1>
        </div>
        <div class="card-body">
            <form id="profileForm" enctype="multipart/form-data">
                @csrf
                <div class="row">
                    <div class="col-md-12 text-center">
                    @php
                        use App\Models\{User};
                        $auth0user = auth()->user();
                        $user = User::where('email', $auth0user->email)->first();
                        $url = ($user->picture)? asset('assets/img/profile/' . $user->picture) :asset('assets/img/user-profile.png');
                    @endphp
                        <div class="profile-picture-container d-flex align-items-center mb-5">
                            <img src="{{$url}}" class="img-fluid profile-picture me-4" alt="Profile Picture">

                            <input type="file" name="picture" id="pictureInput" class="d-none" accept="image/*">
                            <div class="mt-2">
                                <a type="button" class="text-primary-blue text-decoration-none hover me-1" id="changePictureBtn">
                                    Change Picture
                                </a>
                                <a type="button" class="text-primary-orange text-decoration-none hover" id="removePictureBtn"
                                    {{ auth()->user()->picture ? '' : 'style=display:none;' }}>
                                    Remove Picture
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="row">
                            <div class="col-md-4 form-group">
                                <label class="text-primary-orange" for="name">Full Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" id="name"
                                    class="form-control"
                                    value="{{ auth()->user()->name }}"
                                    required>
                            </div>
                            <div class="col-md-4 form-group">
                                <label class="text-primary-orange" for="email">Email Address <span class="text-danger">*</span></label>
                                <input type="email" name="email" id="email"
                                    class="form-control"
                                    value="{{ auth()->user()->email }}"
                                    required>
                            </div>
                            <div class="col-md-8 form-group mt-3">
                                <a type="button" class="text-primary-blue text-decoration-none hover" id="changePasswordBtn">
                                    Click here to Change Password
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-12 text-right">
                        <button type="submit" class="btn btn-secondary-blue" id="updateProfileBtn">
                            Save Changes
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Change Password Modal -->
<div class="modal fade" id="actionModal" tabindex="-1" role="dialog" aria-labelledby="actionModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-primary-orange" id="actionModalLabel">Change Password</h5>
            </div>
            <form id="passwordForm">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label class="text-primary-orange" for="old_password">Current Password</label>
                        <input type="password" name="old_password" id="old_password"
                            class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="text-primary-orange" for="password">New Password</label>
                        <input type="password" name="password" id="password"
                            class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="text-primary-orange" for="password_confirmation">Confirm New Password</label>
                        <input type="password" name="password_confirmation" id="password_confirmation"
                            class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary-white" data-dismiss="modal" id="closeActionModal">Close</button>
                    <button type="submit" class="btn btn-secondary-blue">Change Password</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).on('click', '#closeActionModal', function() {
        $('#actionModal').modal('hide');
    });
    $(document).ready(function() {
        // Profile Picture Change
        $('#changePictureBtn').click(function() {
            $('#pictureInput').click();
        });

        $('#pictureInput').change(function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    $('.profile-picture').attr('src', event.target.result);
                    $('#removePictureBtn').show();
                }
                reader.readAsDataURL(file);
            }
        });

        // Remove Profile Picture
        $('#removePictureBtn').click(function() {
            $.ajax({
                url: "{{ route('profile.remove-picture') }}",
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    $('.profile-picture').attr('src', "{{ asset('assets/img/default-avatar.png') }}");
                    $('#removePictureBtn').hide();
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: response.message,
                        timer: 2000
                    });
                },
                error: function(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to remove profile picture'
                    });
                }
            });
        });

        // Change Password Button
        $('#changePasswordBtn').click(function() {
            $('#actionModal').modal('show');
        });

        // Profile Update Form
        $('#profileForm').on('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);

            $.ajax({
                url: "{{ route('profile.update') }}",
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: response.message,
                            timer: 2000
                        });
                        // Update profile picture src if a new picture URL is provided
                        if (response.picture_url) {
                            $('.profile-picture').attr('src', response.picture_url);
                            $('#removePictureBtn').show();
                        }
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
                            title: 'Error',
                            text: 'An unexpected error occurred'
                        });
                    }
                }
            });
        });

        // Password Change Form
        $('#passwordForm').on('submit', function(e) {
            e.preventDefault();

            $.ajax({
                url: "{{ route('password.change') }}",
                method: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: response.message,
                            timer: 2000
                        });
                        // Reset password fields
                        $('#old_password, #password, #password_confirmation').val('');
                        $('#actionModal').modal('hide');
                    }
                },
                error: function(xhr) {
                    if (xhr.status === 422) {
                        const errors = xhr.responseJSON.errors;

                        Swal.fire({
                            icon: 'warning',
                            title: 'Validation Errors',
                            text: errors
                        });

                        // Optional: Clear and highlight fields
                        $('#old_password, #password, #password_confirmation')
                            .removeClass('is-invalid')
                            .next('.invalid-feedback')
                            .remove();
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'An unexpected error occurred'
                        });
                    }
                }
            });
        });

        // Clear error styles when input is focused
        $('#old_password, #password, #password_confirmation').on('focus', function() {
            $(this).removeClass('is-invalid');
            $(this).next('.invalid-feedback').remove();
        });

    });
</script>
@endpush