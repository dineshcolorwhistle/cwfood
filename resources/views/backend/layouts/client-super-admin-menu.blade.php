@php
    $clientID = Session::get('client');
    $ws_id = Session::get('workspace');
@endphp

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

            <!-- Company Profile Link -->
            <li class="nav-item">
                <a class="nav-link text-white {{ request()->is('client/*/company-profile*') ? 'active' : '' }}"
                    @if($clientID && $ws_id)
                        href="{{ route('client.company-w-ws', ['client_id' =>  $clientID, 'ws_id' =>  $ws_id ]) }}"
                    @elseif($clientID)
                        href="{{ route('client.company-profile', ['client_id' =>  $clientID ]) }}"
                    @endif >
                    <span class="sidenav-normal ms-2 ps-1">Company Profile</span>
                </a>
            </li>

            <!-- Workspaces Link -->
            <li class="nav-item">
                <a class="nav-link text-white {{ request()->is('client/*/workspaces*') ? 'active' : '' }}" 
                @if($clientID && $ws_id)
                    href="{{ route('client.workspaces-w-ws', ['client_id' => $clientID, 'ws_id' =>  $ws_id]) }}"
                @elseif($clientID)
                    href="{{ route('client.workspaces.index', ['client_id' => $clientID ]) }}"
                @endif
                >
                    <span class="sidenav-normal ms-2 ps-1">Workspaces</span>
                </a>
            </li>

            <!-- User role Link -->
            <!-- <li class="nav-item">
                    <a class="nav-link text-white {{ request()->is('client/role*') ? 'active' : '' }}" href="{{ route('client.role.index') }}">
                    <span class="sidenav-normal ms-2 ps-1">User Role</span>
                </a>
            </li> -->


            <!-- Members Link -->
            <li class="nav-item">
                <a class="nav-link text-white {{ request()->is('members.manage*') ? 'active' : '' }}" href="{{ route('members.manage') }}">
                    <span class="sidenav-normal ms-2 ps-1">Members</span>
                </a>
            </li>

            <!-- Permissions Link -->
            <!-- <li class="nav-item {{ request()->is('admin/permissions') ? 'active' : '' }}">
                <a class="nav-link text-white {{ request()->is('admin/permissions') ? 'active' : '' }}"
                    href="{{ route('permission.manage') }}">
                    <span class="sidenav-normal ms-2 ps-1">Permissions</span>
                </a>
            </li> -->
            

            <!-- Companies Link -->
           <li class="nav-item {{ request()->is('admin/companies') ? 'active' : '' }}">
                <a class="nav-link text-white {{ request()->is('admin/companies') ? 'active' : '' }}"
                    href="{{ route('manage.client_company') }}">
                    <span class="sidenav-normal ms-2 ps-1">Companies</span>
                </a>
            </li>

            <!-- Contacts Link -->
           <li class="nav-item {{ request()->is('admin/contacts') ? 'active' : '' }}">
                <a class="nav-link text-white {{ request()->is('admin/contacts') ? 'active' : '' }}"
                    href="{{ route('manage.client_contact') }}">
                    <span class="sidenav-normal ms-2 ps-1">Contacts</span>
                </a>
            </li>

            <!-- Integrations Link -->
            <li class="nav-item {{ request()->routeIs('client.integrations.show') ? 'active' : '' }}">
                <a class="nav-link text-white {{ request()->routeIs('client.integrations.show') ? 'active' : '' }}"
                href="{{ route('client.integrations.show') }}">
                    <span class="sidenav-normal ms-2 ps-1">Integrations</span>
                </a>
            </li>

        </ul>
    </div>
</div>