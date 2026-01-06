@props(['activeItem', 'activeSubitem'])

<div class="sidenav-main">
    <div class="sidenav-header">
        <i class="fas fa-times p-3 cursor-pointer text-white opacity-5 position-absolute end-0 top-0 d-none d-xl-none"
            aria-hidden="true" id="iconSidenav"></i>
        <a class="navbar-brand m-0 d-flex align-items-center text-wrap" href="{{ route('ana.dashboard') }}">
        <img 
            src="{{ get_client_logo() }}" 
            class="main_logo navbar-brand-img h-100" 
            alt="Company Logo">
            <!-- <img src="{{ asset('assets') }}/img/company-logo-mini.png" class="mini_logo navbar-brand-img h-100 d-none" alt="mini_logo"> -->
            <span class="ms-2 font-weight-bold text-white"></span>
        </a>
    </div>
    <hr class="horizontal light mt-0 mb-2">

    <div class="collapse navbar-collapse w-auto h-auto" id="sidenav-collapse-main">
        @php
            $clientID = Session::get('client');
            $workspace = Session::get('workspace');            
            $user_roleID = Session::get('role_id');
            $user_ID = Session::get('user_id');
            if($user_roleID == 1){
                $details = get_default_client_list();
                $wsArray = get_ws_list_based_clientID($clientID);
            }else{
                $details = get_client_list_using_userID($user_ID);
                if(in_array($user_roleID,[2,3])){
                    $wsArray = get_ws_list_based_clientID($clientID);
                }else{
                    $wsArray = get_ws_list_based_usreID($clientID,$user_ID);
                }
            }
        @endphp
        <select name="client_list" id="client_list" class="form-select" style="width: 91%;" onchange="get_workspace_based_client(this)">
            <option disabled>Select Client</option>
            @foreach($details['client_list'] as $client)
            <option value="{{$client['id']}}" @if($client['id'] == $clientID) selected @endif>{{$client['name']}}</option>
            @endforeach
        </select>
        <select name="ws_list" id="ws_list" class="form-select" style="width: 91%;" onchange="display_company_details(this)">
            <option disabled>Select Workspace</option>
            @foreach($wsArray as $ws)
            <option value="{{$ws['id']}}" @if($ws['id'] == $workspace) selected @endif>{{$ws['name']}}</option>
            @endforeach
        </select>
        <ul class="navbar-nav default">
            <!-- Resource View Section -->
            @php
            $isViewSection = (str_contains(request()->path(), '-views'))
            @endphp
            <li class="nav-item">
                <a data-bs-toggle="collapse" href="#resourceViewExamples"
                    class="nav-link text-white {{ $isViewSection ? 'active' : '' }}"
                    aria-controls="resourceViewExamples" role="button" aria-expanded="$isViewSection? 'true' : 'false' }">
                    <i class="material-symbols-outlined opacity-10">dashboard</i>
                    <span class="nav-link-text ms-2 ps-1">Products Views</span>
                </a>
                <div class="collapse  {{ $isViewSection ? 'show' : '' }}" id="resourceViewExamples">
                    <ul class="nav sub-menu">
                        <li class="nav-item {{ (str_contains(request()->path(), 'product-views')) ? 'active' : '' }}">
                            <a class="nav-link text-white {{ (str_contains(request()->path(), 'product-views')) ? 'active' : '' }}"
                                href="{{ route('views.products') }}">
                                <span class="sidenav-normal ms-2 ps-1">Products</span>
                            </a>
                        </li>
                        <li class="nav-item {{ (str_contains(request()->path(), 'raw-material-views')) ? 'active' : '' }}">
                            <a class="nav-link text-white {{ (str_contains(request()->path(), 'raw-material-views')) ? 'active' : '' }}"
                                href="{{ route('views.raw-materials') }}">
                                <span class="sidenav-normal ms-2 ps-1">Raw Materials</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            <!-- Analytics Section -->
            @php
                $reqPath = request()->path();
                $reqTab  = request()->query('tab');
                // Open the Analytics accordion for both the normal dashboard and Xero tab
                $isDashboardSection = (str_contains($reqPath, 'ana/') || str_contains($reqPath, 'dashboard'));
                $isXeroActive       = (str_contains($reqPath, 'dashboard') && $reqTab === 'xero');
                $isDashActive       = (str_contains($reqPath, 'dashboard') && ($reqTab === null || $reqTab === 'overview'));

                $xeroCount = get_xeroconnection_count($clientID);
                
            @endphp
            <li class="nav-item">
                <a data-bs-toggle="collapse" href="#analyticsExamples"
                    class="nav-link text-white {{ $isDashboardSection ? 'active' : '' }}"
                    aria-controls="analyticsExamples" role="button" aria-expanded="$isDashboardSection? 'true' : 'false' }">
                    <i class="material-symbols-outlined opacity-10">bar_chart</i>
                    <span class="nav-link-text ms-2 ps-1">Analytics</span>
                </a>
                <div class="collapse {{ $isDashboardSection ? 'show' : '' }}" id="analyticsExamples">
                    <ul class="nav sub-menu">
                        @if($xeroCount > 0)
                        {{-- Direct link to Xero Sales Performance --}}
                        <li class="nav-item {{ request()->routeIs('ana.xero.sales_performance') ? 'active' : '' }}">
                            <a class="nav-link text-white {{ request()->routeIs('ana.xero.sales_performance') ? 'active' : '' }}"
                            href="{{ route('ana.xero.sales_performance') }}">
                                <span class="sidenav-normal ms-2 ps-1">Xero</span>
                            </a>
                        </li>
                        @endif

                        {{--  Unit Economics Analysis --}}
                        <li class="nav-item {{ request()->routeIs('ana.unit_analysis') ? 'active' : '' }}">
                            <a class="nav-link text-white {{ request()->routeIs('ana.unit_analysis') ? 'active' : '' }}"
                            href="{{ route('ana.unit_analysis') }}">
                                <span class="sidenav-normal ms-2 ps-1">Unit Economics</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            @if($user_roleID == 1 || $user_roleID == 2 || $user_roleID == 3 || $user_roleID == 4 || $user_roleID == 6)
                <!-- Data Entry Section -->
                @php
                $keywords = ['data/', 'products', 'product_v2', 'rawmaterial_v2'];
                $isDataSection = collect($keywords)->contains(fn($word) => str_contains(request()->path(), $word));
                @endphp
                <li class="nav-item">
                    <a data-bs-toggle="collapse" href="#dataExamples"
                        class="nav-link text-white {{ $isDataSection ? 'active' : '' }}"
                        aria-controls="dataExamples" role="button" aria-expanded="{{ $isDataSection ? 'true' : 'false' }}">
                        <i class="material-symbols-outlined opacity-10">create</i>
                        <span class="nav-link-text ms-2 ps-1">Manage Resources</span>
                    </a>
                    <div class="collapse {{ $isDataSection ? 'show' : '' }}" id="dataExamples">
                        <ul class="nav sub-menu">
                            <!-- Keep other data entry items -->
                            <li class="nav-item {{ request()->is('products') ? 'active' : '' }}">
                                <a class="nav-link text-white {{ request()->is('products') ? 'active' : '' }}"
                                    href="{{ route('products.index') }}">
                                    <span class="sidenav-normal ms-2 ps-1">Products</span>
                                </a>
                            </li>
                            
                            
                            <!-- <li class="nav-item {{ str_contains(request()->path(), 'product_v2') ? 'active' : '' }}">
                                <a class="nav-link text-white {{ str_contains(request()->path(), 'product_v2') ? 'active' : '' }}"
                                    href="{{ route('product_v2.index') }}">
                                    <span class="sidenav-normal ms-2 ps-1">products</span>
                                </a>
                            </li>
                             -->

                            <li class="nav-item {{ str_contains(request()->path(), 'raw-materials') ? 'active' : '' }}">
                                <a class="nav-link text-white {{ str_contains(request()->path(), 'raw-materials') ? 'active' : '' }}"
                                    href="{{ route('manage.raw-materials') }}">
                                    <span class="sidenav-normal ms-2 ps-1">Raw Materials</span>
                                </a>
                            </li>

                            <!-- <li class="nav-item {{ str_contains(request()->path(), 'rawmaterial_v2') ? 'active' : '' }}">
                                <a class="nav-link text-white {{ str_contains(request()->path(), 'rawmaterial_v2') ? 'active' : '' }}"
                                    href="{{ route('rawmaterial_v2.manage') }}">
                                    <span class="sidenav-normal ms-2 ps-1">Rawmaterials</span>
                                </a>
                            </li> -->
                            

                            <li class="nav-item {{ str_contains(request()->path(), 'labour') ? 'active' : '' }}">
                                <a class="nav-link text-white {{ str_contains(request()->path(), 'labour') ? 'active' : '' }}"
                                    href="{{ route('labours.index') }}">
                                    <span class="sidenav-normal ms-2 ps-1">Labour</span>
                                </a>
                            </li>
                            <li class="nav-item {{ str_contains(request()->path(), 'machinery') ? 'active' : '' }}">
                                <a class="nav-link text-white {{ str_contains(request()->path(), 'machinery') ? 'active' : '' }}"
                                    href="{{ route('machinery.index') }}">
                                    <span class="sidenav-normal ms-2 ps-1">Machinery</span>
                                </a>
                            </li>

                            <li class="nav-item {{ str_contains(request()->path(), 'packaging') ? 'active' : '' }}">
                                <a class="nav-link text-white {{ str_contains(request()->path(), 'packaging') ? 'active' : '' }}"
                                    href="{{ route('packaging.index') }}">
                                    <span class="sidenav-normal ms-2 ps-1">Packaging</span>
                                </a>
                            </li>

                            <li class="nav-item {{ str_contains(request()->path(), 'freight') ? 'active' : '' }}">
                                <a class="nav-link text-white {{ str_contains(request()->path(), 'freight') ? 'active' : '' }}"
                                    href="{{ route('freight.index') }}">
                                    <span class="sidenav-normal ms-2 ps-1">Freight</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
            @endif

            @if(in_array($user_roleID, [1,2,3,6]))
                @php
                $isDataSection = (str_contains(request()->path(), 'batchbase_agent/'));
                @endphp
            <!-- <li class="nav-item">
                <a data-bs-toggle="collapse" href="#BatchbaseAgent"
                    class="nav-link text-white {{ $isDataSection ? 'active' : '' }}"
                    aria-controls="BatchbaseAgent" role="button" aria-expanded="{{ $isDataSection ? 'true' : 'false' }}">
                    <i class="material-symbols-outlined opacity-10">text_fields_alt</i>
                    <span class="nav-link-text ms-2 ps-1">Batchbase Agent</span>
                </a>
                <div class="collapse {{ $isDataSection ? 'show' : '' }}" id="BatchbaseAgent">
                    <ul class="nav sub-menu">
                        <li class="nav-item {{ str_contains(request()->path(), 'specifications') ? 'active' : '' }}">
                            <a class="nav-link text-white {{ str_contains(request()->path(), 'specifications') ? 'active' : '' }}"
                                href="{{ route('batchbase_agent.specifications') }}">
                                <span class="sidenav-normal ms-2 ps-1">Specifications</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li> -->
            @endif


            @php
            $isDataSection = str_contains(request()->path(), 'fsanz');
            @endphp
            <li class="nav-item">
                <a data-bs-toggle="collapse" href="#Industrydata"
                    class="nav-link text-white {{ $isDataSection ? 'active' : '' }}"
                    aria-controls="Industrydata" role="button" aria-expanded="{{ $isDataSection ? 'true' : 'false' }}">
                    <i class="material-symbols-outlined opacity-10">app_registration</i>
                    <span class="nav-link-text ms-2 ps-1">Industry Data</span>
                </a>
                <div class="collapse {{ $isDataSection ? 'show' : '' }}" id="Industrydata">
                    <ul class="nav sub-menu">

                        <!-- Keep other data entry items -->
                        <li class="nav-item {{ str_contains(request()->path(), 'specifications') ? 'active' : '' }}">
                            <a class="nav-link text-white {{ str_contains(request()->path(), 'specifications') ? 'active' : '' }}"
                                href="{{ route('batchbase_agent.specifications') }}">
                                <span class="sidenav-normal ms-2 ps-1">Specifications</span>
                            </a>
                        </li>

                        <!-- Keep other data entry items -->
                        <li class="nav-item {{ str_contains(request()->path(), 'fsanz_food') ? 'active' : '' }}">
                            <a class="nav-link text-white {{ str_contains(request()->path(), 'fsanz_food') ? 'active' : '' }}"
                                href="{{ route('fsanz_food.manage') }}">
                                <span class="sidenav-normal ms-2 ps-1">FSANZ Nutrition</span>
                            </a>
                        </li>

                        <!-- <li class="nav-item {{ str_contains(request()->path(), 'fsanz/nutrition') ? 'active' : '' }}">
                            <a class="nav-link text-white {{ str_contains(request()->path(), 'fsanz/nutrition') ? 'active' : '' }}"
                                href="{{ route('fsanz.nutrition') }}">
                                <span class="sidenav-normal ms-2 ps-1">FSANZ Nutrition</span>
                            </a>
                        </li> -->

                        <li class="nav-item {{ str_contains(request()->path(), 'fsanz_weight') ? 'active' : '' }}">
                            <a class="nav-link text-white {{ str_contains(request()->path(), 'fsanz_weight') ? 'active' : '' }}"
                                href="{{ route('fsanz_weight.nutrition') }}">
                                <span class="sidenav-normal ms-2 ps-1">FSANZ Weight</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            <!-- Admin Section -->
            @if($user_roleID == 1 || $user_roleID == 2 || $user_roleID == 3 || $user_roleID == 6)
                <li class="nav-item">
                    <a data-bs-toggle="collapse" href="#adminExamples"
                        class="nav-link text-white {{ str_contains(request()->path(), 'admin/') ? 'active' : '' }}"
                        aria-controls="adminExamples" role="button" aria-expanded="{{ str_contains(request()->path(), 'admin/') ? 'true' : 'false' }}">
                        <i class="material-symbols-outlined opacity-10">account_circle</i>
                        <span class="nav-link-text ms-2 ps-1">Admin</span>
                    </a>

                    <div class="collapse  {{ str_contains(request()->path(), 'admin/') ? 'show' : '' }}" id="adminExamples">
                        <ul class="nav sub-menu">
                            <li class="nav-item {{ str_contains(request()->path(), 'image_library') ? 'active' : '' }}">
                                <a class="nav-link text-white {{ str_contains(request()->path(), 'image_library') ? 'active' : '' }}"
                                    href="{{ route('manage.image_library') }}">
                                    <span class="sidenav-normal ms-2 ps-1">Image Library</span>
                                </a>
                            </li>
                            <li class="nav-item {{ str_contains(request()->path(), 'preferences') ? 'active' : '' }}">
                                <a class="nav-link text-white {{ str_contains(request()->path(), 'preferences') ? 'active' : '' }}"
                                    href="{{ route('preference.manage') }}">
                                    <span class="sidenav-normal ms-2 ps-1">Tags</span>
                                </a>
                            </li>
                            {{-- Overview (existing dashboard) --}}
                            <li class="nav-item {{ str_contains(request()->path(), 'dashboard') ? 'active' : '' }}">
                                <a class="nav-link text-white {{ str_contains(request()->path(), 'dashboard') ? 'active' : '' }}"
                                href="{{ route('ana.dashboard') }}">
                                    <span class="sidenav-normal ms-2 ps-1">Image Metadata</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
            @endif


            <li class="nav-item {{ request()->is('support') ? 'active' : '' }}">
                <a class="nav-link text-white {{ request()->is('support') ? 'active' : '' }}"
                    href="{{ route('support.manage') }}">
                    <i class="material-symbols-outlined opacity-10">mail</i>
                    <span class="sidenav-normal ms-2 ps-1">Support</span>
                </a>
            </li>
        </ul>
    </div>
</div>
@php
$activeItem = request()->segment(2);
@endphp
<div class="sidenav-footer w-100 bottom-0 mt-2 ">
    <div class="sidenav-footer-img mb-3">
        <img src="{{ asset('assets') }}/img/BB-Logo-transparent.png" class="fh_main_logo" alt="user profile">
        <img src="{{ asset('assets') }}/img/BB-favicon-transparent.png" class="fh_mini_logo d-none" alt="user profile">
    </div>
    <div class="mx-1">
        <a class="nav-link text-white {{ $activeItem == 'terms' ? ' active' : '' }}  "
            href="{{ route('page.show', 'terms') }}">
            <span class="sidenav-normal"> Terms of Use </span>
        </a>
    </div>
    <div class="mx-1">
        <a class="nav-link text-white {{ $activeItem == 'privacy' ? ' active' : '' }}  "
            href="{{ route('page.show', 'privacy')}}">
            <span class="sidenav-normal"> Privacy Policy </span>
        </a>
    </div>
    <div class="mx-1">
        <a class="nav-link text-white {{ $activeItem == 'about' ? ' active' : '' }}  "
            href="{{ route('page.show', 'about')}}">
            <span class="sidenav-normal"> About Us </span>
        </a>
    </div>
    <div class="mx-1">
        <a class="nav-link text-white" href="https://www.batchbase.com.au/contact" target="_blank">
            <span class="sidenav-normal"> Contact Us </span>
        </a>
    </div>
</div>