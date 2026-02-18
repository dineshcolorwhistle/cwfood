<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>CW Food - Login</title>

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

</head>

<body class="bg-gray-200">
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg position-absolute top-0 z-index-3 w-100 shadow-none my-3 navbar-transparent mt-4">
        <!-- Add your navbar content here if needed -->
    </nav>
    <!-- End Navbar -->

    <main class="main-content mt-0">
        <div class="page-header align-items-start min-vh-100"
            style="background-image: url('{{ asset('assets/img/login-bg2.jpg') }}');">
            <span class="mask bg-gradient-dark opacity-6"></span>
            <div class="container my-5 login_page_container">
                <div class="row signin-margin">
                    <div class="col-lg-5 col-md-8 col-12 mx-auto">
                        <div class="card z-index-0 fadeIn3 fadeInBottom">
                            <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2" style="margin-top: -30px;">
                                <div class="linear-background shadow-primary border-radius-lg py-3 pe-1">
                                    <h2 class="text-white font-weight-bolder text-center my-2">CW Food - Login page</h2>
                                </div>
                            </div>
                            <div class="card-body">
                                <form role="form" method="POST" action="{{ route('authenticate') }}" class="text-start">
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
                                        <!-- <label class="form-label">Email</label> -->
                                        <input type="email" class="form-control" name="email" value="{{ old('email') }}" required autofocus placeholder="Email">
                                    </div>
                                    @error('email')
                                    <p class='text-danger inputerror'>{{ $message }}</p>
                                    @enderror

                                    <div class="input-group input-group-outline mt-3">
                                        <!-- <label class="form-label">Password</label> -->
                                        <input type="password" class="form-control" name="password" id="password" required placeholder="Password">
                                        <span class="input-group-text eye-icon" onclick="togglePassword()" style="cursor: pointer;">
                                            <span class="material-symbols-outlined" id="eyeIcon" style="font-size: 24px;">visibility</span>
                                        </span>
                                    </div>
                                    @error('password')
                                    <p class='text-danger inputerror'>{{ $message }}</p>
                                    @enderror
                                    <div class="form-check form-switch d-flex align-items-center my-3">
                                        <input class="form-check-input" type="checkbox" id="remember" name="remember">
                                        <label class="form-check-label mb-0 ms-3 mt-1" for="remember">Remember me</label>
                                    </div>
                                    <div class="text-center">
                                        <button type="submit" class="btn btn-secondary-blue w-100 my-4 mb-2">Sign in</button>
                                        <!-- <p class="mt-3">Don't have an account <a href="{{ route('signup') }}">Start free trial</a></p> -->
                                    </div>
                                    <!-- <p class="text-sm text-center mt-3"> Forgot your password? Reset your password <a href="{{ route('password.request') }}" class="text-primary text-gradient font-weight-bold">here</a> </p> -->
                                </form>
                            </div>
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
                        window.location.href = "{{ route('login') }}";
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
    </script>
    <script>
    function togglePassword() {
        var passwordInput = document.getElementById("password");
        var eyeIcon = document.getElementById("eyeIcon");

        if (passwordInput.type === "password") {
            passwordInput.type = "text";
            eyeIcon.textContent = "visibility_off";
        } else {
            passwordInput.type = "password";
            eyeIcon.textContent = "visibility";
        }
    }
</script>
</body>

</html>