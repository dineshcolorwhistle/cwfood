<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Batchbase - Signup</title>

    <link rel="icon" type="image/png" href="{{ asset('assets/img/favicon.png') }}">

    <!--     Fonts and icons     -->
    <link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700,900|Roboto+Slab:400,700" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
    <!-- Nucleo Icons -->
    <link href="{{ asset('assets') }}/css/nucleo-icons.css" rel="stylesheet" />
    <link href="{{ asset('assets') }}/css/nucleo-svg.css" rel="stylesheet" />
    <!-- Font Awesome Icons -->
    <script src="https://kit.fontawesome.com/42d5adcbca.js" crossorigin="anonymous"></script>
    <!-- Material Icons -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet">
    <!-- CSS Files -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ asset('assets/css/custom-style.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/theme-style.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
        #signUpForm input:not([readonly]), #signUpForm textarea#description, #signUpForm input[readonly]{background-color: #ffffff00 !important; color: white !important; font-weight: 400 !important;border-bottom: 1px solid #d2d6da !important;border-top: 0px !important; border-left: 0px !important; border-right: 0px !important;border-top-left-radius: 0px !important; border-bottom-left-radius: 0px !important;border-radius: 0px !important;}
        #signUpForm .btn-secondary-blue{background: var(--bs-white);color: black;transition: 0.5s;background-color: var(--bs-white);font-size: 13px;text-transform: uppercase;font-weight: 600;box-shadow: 0px 3px 6px 0px rgba(0, 0, 0, 0.25);line-height: 1;padding: 15px 15px;border-radius: 15px;border:none;}
        #signUpForm a{color:white;}
        .signup-logo{text-align:center;}
        .signup-heading h1{text-align:left; color:white;font-weight:600;font-size: 25px;}
        #signUpForm input::placeholder, #signUpForm textarea::placeholder { color: white;opacity: 1;}
        .plan-details span{color:white !important;font-size:23px;}
        .plan-details p{color:white !important;margin-top: 5px;font-weight: 700;}
        .login-route{color:white;font-size:15px;}
    </style>
</head>

<body class="bg-gray-200">
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg position-absolute top-0 z-index-3 w-100 shadow-none my-3 navbar-transparent mt-4">
        <!-- Add your navbar content here if needed -->
    </nav>
    <!-- End Navbar -->


    <main class="main-content mt-0">
        <div class="page-header align-items-start min-vh-100"
            style="background-image: url('{{ asset('assets/img/batchbase_login.png') }}');">
            <span class="mask opacity-6"></span>
            <div class="container my-5 login_page_container">
                <div class="row signin-margin">
                    <div class="col-lg-5 col-md-8 col-12">
                        <div class="signup-section">
                            <div class="signup-logo">
                                <img src="{{ asset('assets/img/signup-logo.png') }}" width="40%">
                            </div>
                            <div class="signup-heading">
                                <h1 class="mt-5">Sign Up to Batchbase</h1>
                            </div>
                        </div>
                        <div class="form-section pt-2">
                             <form id="signUpForm"role="form" method="POST" action="{{ route('company.authenticate') }}" class="text-start">
                                    @csrf
                                    @if (session('status'))
                                    <div class="alert alert-success alert-dismissible text-white" role="alert">
                                        <span class="text-sm">{{ session('status') }}</span>
                                        <button type="button" class="btn-close text-lg py-3 opacity-10"
                                            data-bs-dismiss="alert" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    @endif

                                    <div class="input-group input-group-outline mt-3">
                                        <!-- <label class="text-primary-orange" for="name">Client Name <span class="text-danger">*</span></label> -->
                                        <input type="text" name="company_name" id="company_name" class="form-control" placeholder="Company Name" required>
                                    </div>
                                    

                                    <div class="input-group input-group-outline mt-3">
                                        <!-- <label class="text-primary-orange" for="description">Description</label> -->
                                        <textarea name="description" id="description" class="form-control" placeholder="Company Description" required></textarea>
                                    </div>

                                    <div class="input-group input-group-outline mt-3">
                                        <!-- <label class="form-label">Email</label> -->
                                        <input type="text" class="form-control" name="name" value="{{ old('name') }}" required  placeholder="Full Name">
                                    </div>


                                    <div class="input-group input-group-outline mt-3">
                                        <!-- <label class="form-label">Email</label> -->
                                        <input type="email" class="form-control" name="email" value="{{ old('email') }}" required autofocus placeholder="Email">
                                    </div>
                                    @error('email')
                                    <p class='text-danger inputerror'>{{ $message }}</p>
                                    @enderror

                                    <div class="input-group input-group-outline mt-3 plan-details">
                                        <p><span class="material-symbols-outlined">label_important</span> Trial Plan</p>
                                        <input type="hidden" class="form-control" name="subscription_plan_id" value="{{env('TRIAL_PLAN')}}">
                                    </div>

                                    <div class="text-center">
                                        <button type="button" id="signupBtn" class="btn btn-secondary-blue w-100 my-4 mb-2" onclick="form_signup()">Lets Go!</button>
                                        <h3 class="mt-3 login-route">Already have an account <a href="{{ env('APP_URL') }}/login">Login</a></h3>
                                    </div>
                                </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <!-- <p>Session Lifetime: {{ config('session.lifetime') }} minutes</p> -->
    <script src="{{ asset('assets') }}/js/plugins/jquery-3.6.0.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"></script>

    <script src="{{ asset('assets') }}/js/plugins/perfect-scrollbar.min.js"></script>
    <!-- Kanban scripts -->
    <script src="{{ asset('assets') }}/js/plugins/dragula/dragula.min.js"></script>
    <script src="{{ asset('assets') }}/js/plugins/jkanban/jkanban.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    @stack('js')
    <script>
        var win = navigator.platform.indexOf('Win') > -1;
        if (win && document.querySelector('#sidenav-scrollbar')) {
            var options = {
                damping: '0.5'
            }
            Scrollbar.init(document.querySelector('#sidenav-scrollbar'), options);
        }
    </script>
    <!-- Github buttons -->
    <script async defer src="https://buttons.github.io/buttons.js"></script>
    <!-- Control Center for Material Dashboard: parallax effects, scripts for the example pages etc -->
    <script src="{{ asset('assets') }}/js/material-dashboard.min.js?v=3.0.1"></script>
    <script>
        $(function() {
            function checkForInput(element) {
                const $label = $(element).parent();
                if ($(element).val().length > 0) {
                    $label.addClass('is-filled');
                } else {
                    $label.removeClass('is-filled');
                }
            }
            var input = $(".input-group input");
            input.focusin(function() {
                $(this).parent().addClass("focused is-focused");
            });

            $('input').each(function() {
                checkForInput(this);
            });

            $('input').on('change keyup', function() {
                checkForInput(this);
            });

            input.focusout(function() {
                $(this).parent().removeClass("focused is-focused");
            });
        });
    </script>


    <script>
        function setupCsrfRefresh() {
            const sessionLifetimeMinutes = "{{ config('session.lifetime', 120) }}"; // Get from Laravel config
            const triggerMinutes = sessionLifetimeMinutes * 0.9; // Refresh at 90% time of session
            const triggerMilliseconds = triggerMinutes * 60 * 1000;


            setInterval(function() {
                $.ajax({
                    url: "{{ route('csrf.refresh') }}",
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        // Update CSRF token in meta tag
                        $('meta[name="csrf-token"]').attr('content', response.token);

                        // Update CSRF token in forms
                        $('input[name="_token"]').val(response.token);

                        // Update Ajax setup
                        $.ajaxSetup({
                            headers: {
                                'X-CSRF-TOKEN': response.token
                            }
                        });


                        console.log(sessionLifetimeMinutes, triggerMinutes, triggerMilliseconds, response.token);
                    },
                    error: function(xhr, status, error) {
                        console.error('Error refreshing CSRF token:', error);
                        window.location.href = "{{ route('signup') }}";
                    }
                });
            }, triggerMilliseconds);
        }

        // Setup Ajax defaults
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $(document).ready(function() {
            setupCsrfRefresh();
        });


        function form_signup(){
            const btn = document.getElementById('signupBtn');
            btn.disabled = true;                 // Disable button
            btn.innerText = 'Processing...';     // Optional: change text

            $.ajax({
                url: $('form#signUpForm').attr('action'),
                method: $('form#signUpForm').attr('method'),
                data: $('form#signUpForm').serialize(),
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: response.success,
                            timer: 2000
                        }).then(() => {
                            window.location.href = "{{ env('APP_URL') }}/login";
                            // location.reload();
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

                        console.log(errorMessage);
                        
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
                },
                complete: function() {
                    // Re-enable if needed
                    btn.disabled = false;
                    btn.innerText = "Let's Go!";
                }
            });
        }
    </script>
   
</body>

</html>