<nav class="navbar navbar-main linear-background navbar-expand-lg position-sticky mt-4 top-1 px-0 mx-4 shadow-none border-radius-xl z-index-sticky"
    id="navbarBlur" data-scroll="true">
    <div class="container-fluid py-1 px-3">
        <div class="sidenav-toggler sidenav-toggler-inner">
            <a href="javascript:;" class="nav-link text-body p-0">
                <div class="sidenav-toggler-inner">
                    <i class="sidenav-toggler-line"></i>
                    <i class="sidenav-toggler-line"></i>
                    <i class="sidenav-toggler-line"></i>
                </div>
            </a>
        </div>
        <div class="nav-home">
            <a href="#" style="display:none;">
                <img src="{{ asset('assets') }}/img/home.png" alt="Logo">
            </a>
        </div>
        <div class="collapse navbar-collapse mt-sm-0 mt-2 me-md-0 me-sm-4" id="navbar">
            <!-- <div class="ms-md-auto pe-md-3 d-flex align-items-center">
                <div class="input-group input-group-outline">
                    <label class="form-label">Search here</label>
                    <input type="text" class="form-control">
                </div> -->
        </div>
        <ul class="navbar-nav  align-items-center">

            <!--
            <li class="nav-item d-xl-none ps-3 d-flex align-items-center" style="display:none;">
                <a href="javascript:;" class="nav-link text-body p-0" id="iconNavbarSidenav">
                    <div class="sidenav-toggler-inner">
                        <i class="sidenav-toggler-line"></i>
                        <i class="sidenav-toggler-line"></i>
                        <i class="sidenav-toggler-line"></i>
                    </div>
                </a>
            </li>

            <li class="nav-item px-3">
                <a href="javascript:;" class="nav-link text-body p-0">
                    <i class="material-symbols-outlined fixed-plugin-button-nav cursor-pointer">
                        settings
                    </i>
                </a>
            </li>
            -->

            <li class="nav-item dropdown pe-2 mx-3">
                <a href="javascript:;" class="nav-link text-body p-0 position-relative" id="dropdownMenuButton"
                    data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="material-symbols-outlined cursor-pointer">notifications</i>
                </a>
                <!-- <ul class="dropdown-menu dropdown-menu-end p-2 me-sm-n4" aria-labelledby="dropdownMenuButton">

                </ul> -->
            </li>
            @php
                use App\Models\{User};
                $auth0user = auth()->user();
                $user = User::where('email', $auth0user->email)->first();
                $url = ($user->picture)? asset('assets/img/profile/' . $user->picture) :asset('assets/img/user-profile.png');
                $roleID = Session::get('role_id');
                $clientID = Session::get('client');
                $workspace = Session::get('workspace');
            @endphp
            <li class="nav-item dropdown pe-2">
                <a href="javascript:;" class="nav-link text-body p-0 position-relative" id="accountbutton" data-bs-toggle="dropdown" aria-expanded="false">
                    <img src="{{ $url }}" alt="user profile" class="rounded-circle" width="30" height="30">
                </a>
                <ul class="dropdown-menu dropdown-menu-end p-2 me-sm-n4" aria-labelledby="dropdownMenuButton">
                    <!-- Logged-in user details -->
                    <li class="">
                        <a class="dropdown-item account border-radius-md" href="javascript:;">
                            <div class="d-flex align-items-center py-1">
                                <img src="{{ $url }}" alt="user profile" class="rounded-circle" width="45" height="45">
                                <div class="ms-2">
                                    <p class="text-sm font-weight-normal my-auto text-dark-mud">{{ $user->name }}</p>
                                    <p class="text-xs text-muted my-auto text-dark-mud" style="word-break: break-all; white-space: normal;">{{ $user->email }}</p>
                                </div>
                            </div>
                        </a>
                    </li>
                    <!-- Profile link -->
                    <li class="">
                        <a class="dropdown-item account border-radius-md" href="{{ route('user-profile') }}">
                            <div class="d-flex align-items-center py-1">
                                <div class="ms-2">
                                    <p class="text-sm font-weight-normal my-auto text-dark-mud">Profile</p>
                                </div>
                            </div>
                        </a>
                    </li>
                    <!-- Settings link (Optional, if required functionality) -->
                    @if(in_array($roleID, [1,2,3]))
                        <li class="" id="client_Settings">
                            <a class="dropdown-item account border-radius-md" href="{{ route('client.company-profile', ['client_id' => $clientID]) }}">
                                <div class="d-flex align-items-center py-1">
                                    <div class="ms-2">
                                        <p class="text-sm font-weight-normal my-auto text-dark-mud">Settings</p>
                                    </div>
                                </div>
                            </a>
                        </li>
                    @endif
                    @if(in_array($roleID, [1,2]))
                    <li class="" id="client_Settings">
                        <a class="dropdown-item account border-radius-md" href="{{ route('client.subscription.show') }}">
                            <div class="d-flex align-items-center py-1">
                                <div class="ms-2">
                                    <p class="text-sm font-weight-normal my-auto text-dark-mud">Billing</p>
                                </div>
                            </div>
                        </a>
                    </li>
                    @endif
                    
                    <!-- Nutriflow Admin link -->
                    @if($roleID == 1)
                    <li class="">
                        <a class="dropdown-item account border-radius-md" href="{{ route('clients.index') }}">
                            <div class="d-flex align-items-center py-1">
                                <div class="ms-2">
                                    <p class="text-sm font-weight-normal my-auto text-dark-mud">Batchbase Admin</p>
                                </div>
                            </div>
                        </a>
                    </li>
                    @endif

                    <!-- Logout -->
                    <form method="POST" action="{{ route('logout') }}" class="d-none" id="logout-form">
                        @csrf
                    </form>
                    <li class="">
                        <a class="dropdown-item account border-radius-md" href="{{ route('logout') }}"
                            onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            <div class="d-flex align-items-center py-1">
                                <div class="ms-2">
                                    <p class="text-sm font-weight-normal my-auto text-dark-mud">Sign Out</p>
                                </div>
                            </div>
                        </a>
                    </li>
                </ul>
            </li>
        </ul>
    </div>
    </div>
</nav>