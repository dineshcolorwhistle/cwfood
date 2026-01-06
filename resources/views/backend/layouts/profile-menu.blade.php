<div class="sidenav-main">
    <div class="collapse navbar-collapse w-auto h-auto" id="sidenav-collapse-main">
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link text-white" href="{{ route('views.products') }}">
                    <span class="material-symbols-outlined" style="font-size: 24px;">
                        arrow_back
                    </span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-white {{ request()->is('user-profile*') ? 'active' : '' }}"
                    href="{{ route('user-profile') }}">
                    <span class="sidenav-normal ms-2 ps-1">Profile</span>
                </a>
            </li>
        </ul>
    </div>
</div>