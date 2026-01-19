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
            <!-- Clients Link -->
            <li class="nav-item">
                <a class="nav-link text-white {{ request()->is('clients*') ? 'active' : '' }}"
                    href="{{ route('clients.index') }}">
                    <span class="sidenav-normal ms-2 ps-1">Clients</span>
                </a>
            </li>

            <!-- User Roles Link -->
            <li class="nav-item">
                <a class="nav-link text-white {{ request()->routeIs('roles.index') ? 'active' : '' }}"
                    href="{{ route('roles.index') }}">
                    <span class="sidenav-normal ms-2 ps-1">User Roles</span>
                </a>
            </li>

            <!-- Web Pages Link -->
            <li class="nav-item">
                <a class="nav-link text-white {{ request()->is('pages*') ? 'active' : '' }}"
                    href="{{ route('pages.index') }}">
                    <span class="sidenav-normal ms-2 ps-1">Web Pages</span>
                </a>
            </li>

            <!-- Team Members Link -->
            <li class="nav-item">
                <a class="nav-link text-white {{ request()->routeIs('team-members.*') ? 'active' : '' }}"
                    href="{{ route('team-members.index') }}">
                    <span class="sidenav-normal ms-2 ps-1">Team Members</span>
                </a>
            </li>

            <!-- Team Members Roles Link -->
            <li class="nav-item">
                <a class="nav-link text-white {{ request()->routeIs('team-member-roles.index') ? 'active' : '' }}"
                    href="{{ route('team-member-roles.index') }}">
                    <span class="sidenav-normal ms-2 ps-1">Team Members Roles</span>
                </a>
            </li>

            <!-- Subscriptions Link -->
            <li class="nav-item">
                <a class="nav-link text-white {{ request()->is('subscription-plans*') ? 'active' : '' }}"
                    href="{{ route('subscription-plans.index') }}">
                    <span class="sidenav-normal ms-2 ps-1">Subscriptions</span>
                </a>
            </li>

            <!-- Support Link -->
            <li class="nav-item">
                <a class="nav-link text-white {{ request()->is('admin.support.manage') ? 'active' : '' }}"
                    href="{{ route('admin.support.manage') }}">
                    <span class="sidenav-normal ms-2 ps-1">Support</span>
                </a>
            </li>

            <!-- AI Prompt for Specification -->
            <li class="nav-item">
                <a class="nav-link text-white {{ request()->is('admin.ai_prompt.manage') ? 'active' : '' }}"
                    href="{{ route('admin.ai_prompt.manage') }}">
                    <span class="sidenav-normal ms-2 ps-1">AI Prompt</span>
                </a>
            </li>

            <!-- Notifications Link -->
            <li class="nav-item">
                <a class="nav-link text-white {{ request()->is('notifications*') ? 'active' : '' }}"
                    href="#">
                    <span class="sidenav-normal ms-2 ps-1">Notifications</span>
                </a>
            </li>
        </ul>
    </div>
</div>