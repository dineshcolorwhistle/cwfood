<aside class="sidenav navbar navbar-vertical navbar-expand-xs border-0 border-radius-xl fixed-start bg-gradient-dark" id="sidenav-main">
    @switch($sideMenuType ?? 'default')
    @case('profile')
    @include('backend.layouts.profile-menu')
    @break
    @case('nutriflow_admin')
    @include('backend.layouts.nutriflow-admin-menu')
    @break
    @case('client_super_admin')
    @include('backend.layouts.client-super-admin-menu')
    @break
    @case('admin_billing')
    @include('backend.layouts.admin-billing-menu')
    @break
    @default
    @include('backend.layouts.default-menu')
    @endswitch
</aside>