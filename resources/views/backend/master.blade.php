<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <link rel="apple-touch-icon" sizes="76x76" href="{{ asset('assets/img/apple-icon.png') }}">
    <link rel="icon" type="image/png" href="{{ asset('assets/img/favicon.png') }}">
    <title>{{ $pageTitle ?? 'Nutriflow' }}</title>
    <!-- Fonts and icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700&family=Poppins:wght@400&display=swap" rel="stylesheet">
    <!-- Material Symbols - Self-hosted (see custom-style.css @font-face) -->
    <!-- CSS Files -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/select2.min.css') }}">
    <script src="{{ asset('assets/js/custom.js') }}"></script>

    @if(isset($features) && isset($features['datatables']) && $features['datatables'] == '1')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/select/1.6.3/css/select.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.3/css/responsive.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/colvis/1.2.1/css/colvis.dataTables.min.css">
    @endif

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.12.313/pdf.min.js"></script>

    @stack('styles')
    <link href="{{ asset('assets/css/theme-style.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/custom-style.css') }}" rel="stylesheet">
    <style>
        
        div#AIloader,div#loader {opacity: 0.9;}
.ai-loader-backdrop {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(255, 255, 255, 0.7);
    backdrop-filter: blur(4px);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 99999;
}

/* Loader box */
.ai-loader-container {
    background: #fff;
    border-radius: 16px;
    padding: 40px;
    width: 50%;
    max-width: 95%;
    text-align: center;
    box-shadow: 0 8px 28px rgba(0,0,0,0.12);
    border: 1px solid #e5e7eb;
    animation: aiFadeIn 0.3s ease-out;
}

/* Title */
.ai-loader-title {
    font-weight: 600;
    color: #0c756e;
}

/* Sub text */
.ai-loader-desc {
    color: #4b5563;
    font-size: 1.00rem;
    line-height: 1.5;
}

.ai-loader-bar {
    position: relative;
    width: 100%;
    height: 20px;
    background: #e5e7eb;
    border-radius: 50px;
    overflow: hidden;
    margin-top: 16px;
}

.ai-loader-progress,.loader-progress {
    width: 10%;
    height: 100%;
    background: linear-gradient(90deg, #0c756e, #4ac7b5);
    border-radius: 50px;
    transition: width 0.6s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
}

.ai-loader-percent,  .loader-percent{
    color: #fff;
    font-size: 0.80rem;
    font-weight: 600;
    position: absolute;
    width: 100%;
    text-align: center;
}



    </style>
</head>

<body class="@yield('body-class', 'g-sidenav-show bg-gray-200')">
    @php
    // Default values to prevent errors
    $activeMenu = $activeMenu ?? null;
    $breadcrumbItems = $breadcrumbItems ?? [
    ['icon' => 'home', 'label' => 'Home', 'url' => route('ana.dashboard')]
    ];
    $pageActions = $pageActions ?? [];
    @endphp

    @auth
    @include('backend.layouts.navbar')
    @include('backend.layouts.sidebar')
    <main class="main-content position-relative max-height-vh-100 border-radius-lg background-body">
        <div class="main-content-area background-body">
            <div id="AIloader" class="d-none position-fixed top-0 start-0 w-100 h-100 bg-white  d-flex justify-content-center align-items-center" style="z-index:1050;">
                <div class="w-50">
                    <div class="ai-loader-backdrop">
                        <div class="ai-loader-container">

                            <h2 class="text-center mb-2 ai-loader-title">
                                AI Extraction in Progress
                            </h2>

                            <p class="text-center ai-loader-desc">
                                The system is analyzing your document using OCR, detecting fields,
                                and preparing structured data.
                                <br> This may take a few seconds.
                            </p>

                            <div class="ai-loader-bar mt-5">
                                <div class="ai-loader-progress">
                                    <span class="ai-loader-percent">10%</span>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>

            <div id="loader" class="d-none position-fixed top-0 start-0 w-100 h-100 bg-white  d-flex justify-content-center align-items-center" style="z-index:1050;">
                <div class="w-50">
                    <div class="ai-loader-backdrop">
                        <div class="ai-loader-container">

                            <h2 class="text-center mb-2 ai-loader-title">
                                Submitting Your Information
                            </h2>

                            <p class="text-center ai-loader-desc">
                                Please wait while we process your request.
                            </p>

                            <div class="ai-loader-bar mt-5">
                                <div class="loader-progress">
                                    <span class="loader-percent">10%</span>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>


            @yield('content')
        </div>
    </main>
    @else
    @yield('content')
    @endauth

    <!-- JS Files -->
    <script src="{{ asset('assets') }}/js/plugins/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"></script>
    <script src="{{ asset('assets/js/select2.min.js') }}"></script>

    @if(isset($features) && isset($features['datatables']) && $features['datatables'] == '1')
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/select/1.6.3/js/dataTables.select.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.3/js/dataTables.responsive.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.colVis.min.js"></script>

    <script>
        $(document).ready(function() {
            // Initialize DataTable
            $('#dtRecordsView').DataTable({
                responsive: true,
                dom: "<'row mb-4'<'col-md-6 col-6 col-sm-6'f><'col-md-6 col-6 col-sm-6'l>>" + // Search box (f) and entries dropdown (l)
                    "<'row table-responsiveness'<'col-sm-12'tr>>" + // Table rows
                    "<'row'<'col-md-5'i><'col-md-7'p>>", // Info text (i) and pagination (p)
                language: {
                    search: "", // Removes "Search:" label
                    searchPlaceholder: "Search", // Adds placeholder to the search box
                    lengthMenu: "_MENU_ per page", // Customizes "entries per page" text
                    paginate: {
                        previous: "<i class='material-symbols-outlined'>chevron_left</i>", // Replace "Previous" text with '<'
                        next: "<i class='material-symbols-outlined'>chevron_right</i>" // Replace "Next" text with '>'
                    }
                },
                pageLength: 25,
                initComplete: function() {
                    $("#tableSkeleton").fadeOut(200, ()=>{
                        $("#dtRecordsView").fadeIn(250);
                    });
                    // Move the search box to the left and entries dropdown to the right
                    const tableWrapper = $(this).closest('.dataTables_wrapper');
                    const searchBox = tableWrapper.find('.dataTables_filter');
                    const lengthDropdown = tableWrapper.find('.dataTables_length');

                    searchBox.css({
                        'float': 'left',
                        'margin-top': '0'
                    });
                    lengthDropdown.css('float', 'right');
                }
            });


            
        });
    </script>
    @endif
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script src="{{ asset('assets/js/material-dashboard.js') }}"></script>

    <script>
        $(document).on('click', '#closeActionModal', function() {
            $('#actionModal').modal('hide');
        });
    </script>


    @stack('scripts')
       <script>
        document.addEventListener("DOMContentLoaded", function () {

            let AIprogressBar = document.querySelector(".ai-loader-progress");
            let AIpercentText = document.querySelector(".ai-loader-percent");

            let progressBar = document.querySelector(".loader-progress");
            let percentText = document.querySelector(".loader-percent");

            let progress = 10;        
            let step = 5;             
            let maxLimit = 95;        
            let intervalTime = 3000;

            AIprogressBar.style.width = progress + "%";
            AIpercentText.innerHTML = progress + "%";

            progressBar.style.width = progress + "%";
            percentText.innerHTML = progress + "%";

            let timer = setInterval(() => {

                if (progress >= maxLimit) {
                    clearInterval(timer);
                    return;
                }

                progress += step;
                if (progress > maxLimit) progress = maxLimit;

                AIprogressBar.style.width = progress + "%";
                progressBar.style.width = progress + "%";

                AIpercentText.innerHTML = progress + "%";
                percentText.innerHTML = progress + "%";

            }, intervalTime);

        });
        </script>



    <script>
        // let fakeInterval = null;
        // let currentFake = 10;
        // let fakeStopPoint = 85; 
        // let progressLocked = false;


        // function updateLoaderProgress(val) {
        //     if (progressLocked) return; // lock after finish

        //     val = Math.min(100, val);
        //     currentFake = val;

        //     $("#loaderProgressBar")
        //         .css("width", val + "%")
        //         .text(val + "%");
        // }

        // function startFakeProgress() {
        //     currentFake = 10; // START AT 10%
        //     updateLoaderProgress(10);

        //     fakeStopPoint = Math.floor(Math.random() * (90 - 80 + 1)) + 80; // random 80–90

        //     fakeInterval = setInterval(() => {
        //         if (currentFake >= fakeStopPoint) {
        //             return; // freeze here until AJAX completes
        //         }

        //         updateLoaderProgress(currentFake + 5);

        //     }, 3000); // update every 3 seconds
        // }

        // function stopFakeProgress() {
        //     clearInterval(fakeInterval);
        // }

        // function showLoaderProgress() {
        //     $("#loader").removeClass("d-none");
        // }

        // function hideLoaderProgress() {
        //     setTimeout(() => {
        //         $("#loader").addClass("d-none");
        //     }, 500);
        // }



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
            // setupCsrfRefresh();
        });
    </script>

    <script>
        let session_idleTime = 0;
        const timeoutMinutes = {{ config('session.lifetime') * 60 }};
        let pingTimeout;

        setInterval(() => {
            session_idleTime++;
            console.log(`idle time : ${session_idleTime} set timeout ${timeoutMinutes}`);
            if (session_idleTime >= timeoutMinutes) {
                document.getElementById('logout-form').submit();
            }
        }, 60000);

        function schedulePing() {
            if (pingTimeout) clearTimeout(pingTimeout);

            pingTimeout = setTimeout(() => {
                fetch("{{ route('session.ping') }}", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                });
            }, 30000); // send ping 30 seconds after last activity
        }

        ['mousemove', 'keydown', 'scroll', 'click'].forEach(event => {
            document.addEventListener(event, () => {
                session_idleTime = 0;
                schedulePing();
            });
        });

    </script>
</body>

</html>