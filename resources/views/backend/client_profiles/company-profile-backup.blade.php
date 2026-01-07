@extends('backend.master', [
    'pageTitle' => 'Company Profile',
    'activeMenu' => [
        'item' => 'Client',
        'subitem' => 'Company',
        'additional' => '',
    ],
    'breadcrumbItems' => [
        ['label' => 'CW Food Admin', 'url' => '#'],
        ['label' => 'Company Profile']
    ],
])

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
@endpush

@section('content')
    <div class="container-fluid company-profile px-0">
        <div class="">
            <div class="card-header d-flex justify-content-start gap-5 align-content-center">
                <h1 class="page-title">Company Profile</h1>
                <!-- Add Edit Icon -->
                <div class="edit-icon-container">
                    <span class="material-symbols-outlined" id="editIcon" style="cursor: pointer;">edit</span>
                </div>
            </div>
            <div class="card-body">
                <form id="companyProfileForm" enctype="multipart/form-data" method="POST">
                    @csrf
                    @method('POST')
                    <!-- Company Logo Section -->
                    <div class="row">
                        <div class="col-md-12 text-center">
                            <div class="profile-picture-container d-flex align-items-center mb-5">
                                <img 
                                    src="{{ isset($profile) && $profile->company_logo_url 
                                        ? asset($profile->company_logo_url) 
                                        : asset('assets/img/default-company-logo.png') }}" 
                                    class="img-fluid company-profile-picture me-4" 
                                    alt="Company Logo"
                                    data-default-avatar="{{ asset('assets/img/default-company-logo.png') }}"
                                >

                                <input type="file" name="company_logo" id="logoInput" class="d-none" accept="image/*">
                                <div id="changeremovelogo" class="mt-2" style="display: none;">
                                    <a type="button" class="text-primary-blue text-decoration-none hover me-1" id="changeLogoBtn">
                                        Change Logo
                                    </a>
                                    <input type="hidden" name="remove_logo" id="removeLogo" value="0">
                                    <a type="button" class="text-primary-orange text-decoration-none hover" id="removeLogoBtn"
                                        {{ isset($profile) && $profile->company_logo_url ? '' : 'style=display:none;' }}>
                                        Remove Logo
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Company Info Form Fields -->
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label class="text-primary-orange" for="company_name">Company Name <span class="text-danger">*</span></label>
                            <input type="text" name="company_name" id="company_name" class="form-control" value="{{ old('company_name', $client->name) }}" required>
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="text-primary-orange" for="company_email">Company Email <span class="text-danger">*</span></label>
                            <input type="email" name="company_email" id="company_email" class="form-control" value="{{ old('company_email', $profile->company_email) }}" required>
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="text-primary-orange" for="phone_number">Phone Number <span class="text-danger">*</span></label>
                            <input type="text" name="phone_number" id="phone_number" class="form-control" value="{{ old('phone_number', $profile->phone_number) }}" required>
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="text-primary-orange" for="website_url">Website URL</label>
                            <input type="url" name="website_url" id="website_url" class="form-control" value="{{ old('website_url', $profile->website_url) }}">
                        </div>
                        <div class="col-md-12 form-group">
                            <label class="text-primary-orange" for="company_description">Company Description <span class="text-danger">*</span></label>
                            <textarea name="company_description" id="company_description" class="form-control" rows="3" required>{{ old('company_description', $profile->company_description) }}</textarea>
                        </div>
                    </div>

                    <!-- Company Address and Additional Info -->
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label class="text-primary-orange" for="address">Address</label>
                            <input type="text" name="address" id="address" class="form-control" value="{{ old('address', $profile->address) }}">
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="text-primary-orange" for="city">City</label>
                            <input type="text" name="city" id="city" class="form-control" value="{{ old('city', $profile->city) }}">
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="text-primary-orange" for="state">State</label>
                            <input type="text" name="state" id="state" class="form-control" value="{{ old('state', $profile->state) }}">
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="text-primary-orange" for="zip_code">Zip Code</label>
                            <input type="text" name="zip_code" id="zip_code" class="form-control" value="{{ old('zip_code', $profile->zip_code) }}">
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="text-primary-orange" for="country">Country</label>
                            <input type="text" name="country" id="country" class="form-control" value="{{ old('country', $profile->country) }}">
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="text-primary-orange" for="established_date">Established Date</label>
                            <input type="date" name="established_date" id="established_date" class="form-control" value="{{ old('established_date', $profile->established_date) }}">
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="text-primary-orange" for="legal_structure">Legal Structure</label>
                            <input type="text" name="legal_structure" id="legal_structure" class="form-control" value="{{ old('legal_structure', $profile->legal_structure) }}">
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="text-primary-orange" for="number_of_employees">Number of Employees</label>
                            <input type="number" name="number_of_employees" id="number_of_employees" class="form-control" value="{{ old('number_of_employees', $profile->number_of_employees) }}">
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="text-primary-orange" for="annual_revenue">Annual Revenue</label>
                            <input type="number" name="annual_revenue" id="annual_revenue" class="form-control" value="{{ old('annual_revenue', $profile->annual_revenue) }}">
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="text-primary-orange" for="social_media_links">Social Media Links</label>
                            <input type="text" name="social_media_links" id="social_media_links" class="form-control" value="{{ old('social_media_links', $profile->social_media_links) }}">
                        </div>
                        <div class="col-md-12 form-group mt-4" id="saveCancelButtons" style="display: none;">
                            <button type="submit" class="btn btn-secondary-blue" id="updateCompanyProfileBtn">
                                Save Changes
                            </button>
                            <button type="button" class="btn btn-secondary-blue ms-2" id="cancelBtn">
                                Cancel
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).ready(function() {
            // Change logo button action
            $('#changeLogoBtn').click(function() {
                $('#logoInput').click();
            });

            // Handle logo input change
            $('#logoInput').change(function() {
                if (this.files[0]) {
                    var reader = new FileReader();
                    reader.onload = function(e) {
                        $('.company-profile-picture').attr('src', e.target.result);
                        $('#removeLogoBtn').show();
                    };
                    reader.readAsDataURL(this.files[0]);
                }
            });

            $('#removeLogoBtn').click(function() {
                const defaultAvatar = $('.company-profile-picture').data('default-avatar');
                
                // Set the default avatar as the image source
                $('.company-profile-picture').attr('src', defaultAvatar);
                
                // Hide the remove button and clear the file input
                $('#removeLogoBtn').hide();
                $('#logoInput').val('');

                // Set the hidden field to indicate the logo should be removed
                $('#removeLogo').val('1');
            });

            // Handle form submission (AJAX)
            $('#companyProfileForm').submit(function(e) {
                e.preventDefault(); // Prevent the default form submission

                var formData = new FormData(this); // Create FormData object from form data
                console.log(formData);
                formData.append('_token', '{{ csrf_token() }}');

                $.ajax({
                    url: "{{ route('client.company-profile.update', ['client_id' => $client->id]) }}", // The route URL
                    type: "POST",
                    data: formData,
                    processData: false, // Required for file uploads
                    contentType: false, // Required for file uploads
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success!',
                                text: 'Profile updated successfully!',
                                confirmButtonText: 'OK',
                                confirmButtonColor: '#4CAF50'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    window.location.href = response.redirect_url; // Redirect to the same page or another page
                                }
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Oops!',
                                text: 'Something went wrong. Please try again.',
                                confirmButtonColor: '#F44336'
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: 'An error occurred: ' + xhr.responseText,
                            confirmButtonColor: '#F44336'
                        });
                    }
                });
            });
        });

        $(document).ready(function() {
            // Initially, set all fields to readonly (view mode)
            toggleFormFields(true);

            // Initially hide Save/Cancel buttons
            $('#saveCancelButtons').hide();
            //$('#changeremovelogo').hide();

            // When the Edit icon is clicked
            $('#editIcon').click(function() {
                // Switch to edit mode
                toggleFormFields(false);

                // Hide Edit icon and show Save/Cancel buttons
                $('#editIcon').hide();
                $('#saveCancelButtons').show();
                $('#changeremovelogo').show();
            });

            // When the Cancel button is clicked
            $('#cancelBtn').click(function() {
                // Reset form fields to readonly (view mode)
                toggleFormFields(true);

                // Hide Save/Cancel buttons and show Edit icon
                $('#editIcon').show();
                $('#saveCancelButtons').hide();
                $('#changeremovelogo').hide();
            });

            // When the Save button is clicked
            $('#saveBtn').click(function() {
                // Your save logic here
                // Reset form fields to readonly (view mode)
                toggleFormFields(true);

                // Hide Save/Cancel buttons and show Edit icon
                $('#editIcon').show();
                $('#saveCancelButtons').hide();
            });

            function toggleFormFields(isReadonly) {
                // Set form fields to readonly based on the state
                $('#company_name').prop('readonly', isReadonly);
                $('#company_email').prop('readonly', isReadonly);
                $('#phone_number').prop('readonly', isReadonly);
                $('#website_url').prop('readonly', isReadonly);
                $('#company_description').prop('readonly', isReadonly);
                $('#address').prop('readonly', isReadonly);
                $('#city').prop('readonly', isReadonly);
                $('#state').prop('readonly', isReadonly);
                $('#zip_code').prop('readonly', isReadonly);
                $('#country').prop('readonly', isReadonly);
                $('#established_date').prop('readonly', isReadonly);
                $('#legal_structure').prop('readonly', isReadonly);
                $('#number_of_employees').prop('readonly', isReadonly);
                $('#annual_revenue').prop('readonly', isReadonly);
                $('#social_media_links').prop('readonly', isReadonly);

                // Also toggle button visibility if form is readonly
                if (isReadonly) {
                    $('#saveCancelButtons').hide();
                }
            }
        });
    </script>
@endpush