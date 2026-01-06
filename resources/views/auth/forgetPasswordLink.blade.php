<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Nutriflow - Login</title>

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
                                    <h2 class="text-white font-weight-bolder text-center my-2">Nutriflow</h2>
                                </div>
                            </div>
                            <div class="card-body">
                                <form role="form" id="password_reset" method="POST" action="{{ route('reset.password.post') }}"  class="text-start">
                                    @csrf
                                    <input type="hidden" name="token" value="{{ $token }}">
                                    
                                    <div class="input-group input-group-outline mt-3">
                                        <input type="text" id="email_address" class="form-control" name="email" required autofocus placeholder="Email">
                                    </div>
                                    @if ($errors->has('email'))
                                    <p class='text-danger inputerror'>{{ $errors->first('email') }}</p>
                                    @enderror

                                    <div class="input-group input-group-outline mt-3">
                                        <input type="password" id="password" class="form-control" name="password" required autofocus placeholder="Password">
                                        <span class="input-group-text eye-icon" data-type="password" onclick="togglePassword(this)" style="cursor: pointer;">
                                            <span class="material-symbols-outlined" id="eyeIcon" style="font-size: 24px;">visibility</span>
                                        </span>
                                    </div>
                                    @if ($errors->has('password'))
                                    <p class='text-danger inputerror'>{{ $errors->first('password') }}</p>
                                    @enderror

                                    <div class="input-group input-group-outline mt-3">
                                        <input type="password" id="password-confirm" class="form-control" name="password_confirmation" required autofocus placeholder="Confirm Password">
                                        <span class="input-group-text eye-icon" data-type="password-confirm" onclick="togglePassword(this)" style="cursor: pointer;">
                                            <span class="material-symbols-outlined" id="eyeIcon1" style="font-size: 24px;">visibility</span>
                                        </span>
                                    </div>
                                    @if ($errors->has('password_confirmation'))
                                        <span class="text-danger inputerror">{{ $errors->first('password_confirmation') }}</span>
                                    @endif
                             
                                    <div class="text-center">
                                        <button type="button"  onclick="form_signup()" class="btn btn-secondary-blue w-100 my-4 mb-2"> Reset Password</button>
                                    </div>
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function togglePassword(_this) {

            if($(_this).data('type') == "password"){
                var passwordInput = document.getElementById("password");
                var eyeIcon = document.getElementById("eyeIcon");
                if (passwordInput.type === "password") {
                    passwordInput.type = "text";
                    eyeIcon.textContent = "visibility_off";
                } else {
                    passwordInput.type = "password";
                    eyeIcon.textContent = "visibility";
                }
            }else{
                var passwordInput = document.getElementById("password-confirm");
                var eyeIcon = document.getElementById("eyeIcon1");
                if (passwordInput.type === "password") {
                    passwordInput.type = "text";
                    eyeIcon.textContent = "visibility_off";
                } else {
                    passwordInput.type = "password";
                    eyeIcon.textContent = "visibility";
                }
            }
        }


        function form_signup(){
            $.ajax({
                url: $('form#password_reset').attr('action'),
                method: $('form#password_reset').attr('method'),
                data: $('form#password_reset').serialize(),
                success: function(response) {
                    if (response.status == true) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: response.message,
                            timer: 2000
                        }).then(() => {
                            window.location.href = "{{ route('login') }}";
                        });
                    }else{
                        Swal.fire({
                            icon: 'warning',
                            title: 'Warning!',
                            text: response.message
                        });
                    }
                }
            });
        }
    </script>
</body>

</html>