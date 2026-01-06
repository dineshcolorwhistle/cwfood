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

            <!-- Subscription Link -->
            <li class="nav-item">
                <a class="nav-link text-white {{ request()->is('admin/subscription*') ? 'active' : '' }}" href="{{ route('client.subscription.show') }}">
                    <span class="sidenav-normal ms-2 ps-1">Subscription</span>
                </a>
            </li>

            <!-- Billing Link -->
            <li class="nav-item">
                <a class="nav-link text-white {{ request()->is('admin/billing*') ? 'active' : '' }}" href="{{ route('client.billing.show') }}">
                    <span class="sidenav-normal ms-2 ps-1">Billing</span>
                </a>
            </li>
        </ul>
    </div>
</div>