@extends('backend.master', [
'pageTitle' => 'Company Management',
'activeMenu' => [
'item' => 'Company',
'subitem' => 'Company',
'additional' => '',
],
'features' => [
'datatables' => '1',
'google_map' => '1'
],
'breadcrumbItems' => [
['label' => 'CW Food Admin', 'url' => '#'],
['label' => 'Company']
],])

@push('styles')
<style>
.company-information{padding:10px}
.pac-container {
    z-index: 1051 !important; /* Bootstrap modal z-index is 1050 */
}

   /* Custom ColVis dropdown */
    .colvis-dropdown {
        position: absolute;
        background: #fff;
        border: 1px solid #ddd;
        padding: 8px;
        border-radius: 5px;
        box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
        width: 200px;
    }

    /* Align checkboxes properly */
    .colvis-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 5px;
        cursor: pointer;
    }

    /* Ensure checkboxes are on the right */
    .colvis-checkbox {
        margin-left: auto;
        transform: scale(1.2); /* Slightly larger checkboxes */
        cursor: pointer;
    }

</style>
@endpush 


@section('content')
<div class="container-fluid clients px-0">
    <div class="">
        <!-- <div class="card-header d-flex justify-content-between">
            <h1 class="page-title">Company</h1>
        </div> -->
        <div class="card-body">

            <!-- Nav tabs -->
            <ul class="nav nav-tabs" id="myTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="home-tab" data-bs-toggle="tab" data-bs-target="#home-tab-pane" type="button" role="tab" aria-controls="home-tab-pane" aria-selected="true">
                    Company
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="category" data-bs-toggle="tab" data-bs-target="#category-pane" type="button" role="tab" aria-controls="category-pane" aria-selected="false">
                    Category
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile-tab-pane" type="button" role="tab" aria-controls="profile-tab-pane" aria-selected="false">
                    Tags
                    </button>
                </li>
            </ul>

            <!-- Tab panes -->
            <div class="tab-content p-3 border border-top-0" id="myTabContent">
                <div class="tab-pane fade show active" id="home-tab-pane" role="tabpanel" aria-labelledby="home-tab" tabindex="0">
                    <div class="right-side mb-5">
                        <input type="hidden" class="selectedCols" id="selectedCols">
                        <div class="text-end">

                            <div class="btn-group click-dropdown me-2">
                                <button type="button" class="btn btn-primary-orange plus-icon" title="List">
                                    <span class="material-symbols-outlined">inventory</span>
                                </button>
                                <ul class="dropdown-menu">
                                    <a class="dropdown-item sort_record" href="javascript:void(0)" data-value="all"><li >All</li></a>
                                    <a class="dropdown-item sort_record" href="javascript:void(0)" data-value="1"><li>Archive</li></a>
                                    <a class="dropdown-item sort_record" href="javascript:void(0)" data-value="0"><li>Active</li></a>
                                </ul>
                            </div>
                            <input type="hidden" id="customFilter" value="0">

                            <div class="btn-group click-dropdown me-2">
                                <a href="{{ route('companies.import') }}" class="btn btn-primary-orange plus-icon" title="Upload / Import data">
                                    <span class="material-symbols-outlined">upload</span>
                                </a>
                            </div>
                            <div class="btn-group click-dropdown me-2">
                                <button type="button" class="btn btn-primary-orange plus-icon" title="Download Company">
                                    <span class="material-symbols-outlined">download</span>
                                </button>
                                <ul class="dropdown-menu">
                                    <li>
                                        <a class="dropdown-item export-csv" href="javascript:void(0);" data-url="/download/csv/companies">
                                            Download as CSV
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item export-excel" href="javascript:void(0);" data-url="/download/excel/companies">
                                            Download as Excel
                                        </a>
                                    </li>
                                </ul>
                            </div>

                            <button type="button" class="btn btn-primary-orange plus-icon me-2" onclick="delete_selected_companies()" title="Delete Companies">
                                <span class="material-symbols-outlined">delete</span>
                            </button>

                            <button type="button" class="btn btn-primary-orange plus-icon" data-toggle="modal" data-target="#actionModal" id="addCompanyBtn" title="Add Company">
                                <span class="material-symbols-outlined">add</span>
                            </button>
                        </div>
                    </div>

                    <x-shimmer />

                    <table class="table responsiveness custom-wrap" id="dtCompanyRecordsView" style="display:none;">
                        <thead>
                            <tr>
                                <th class="text-primary-blue">
                                    <div class="form-check-temp p-1">
                                        <input class="form-check-input" type="checkbox" id="companyDefault">
                                    </div>
                                </th>
                                <th class="text-primary-orange">Company Name</th>
                                <th class="text-primary-orange">Contact Person</th>
                                <th class="text-primary-orange">Category</th>
                                <th class="text-primary-orange">Tags</th>
                                <th class="text-primary-orange">Website</th>
                                <th class="text-primary-orange">ABN</th>
                                <th class="text-primary-orange">ACN</th>
                                <th class="text-primary-orange">Billing Address</th>
                                <th class="text-primary-orange">Delivery Address</th> 
                                <th class="text-primary-orange">Notes</th> 
                                <th class="text-primary-orange"></th>
                                <th class="text-primary-orange"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($companies as $company)
                            <tr>
                                <td class="text-primary-dark-mud">
                                    <div class="form-check-temp p-1">
                                        <input class="form-check-input" data-company="{{$company['id']}}" type="checkbox" id="company_{{$company['id']}}">
                                    </div>
                                </td>
                                <td>{{$company['company_name']}}</td>
                                <td>@if($company['primary_contact']){{$company['primary_contact']['first_name']}} {{$company['primary_contact']['last_name']}}@endif</td>
                                <td>@if($company['category']){{$company['category']['name']}}@endif</td>
                                <td>{{$company['Comp_Tags'] ? $company['Comp_Tags'] : ""}}</td>
                                <td>@if($company['website'])<a href="{{$company['website']}}" target="_blank">URL</a>@endif</td>
                                <td>{{$company['ABN']}}</td>
                                <td>{{$company['ACN']}}</td>
                                <td>{{$company['billing_address']}}</td>
                                <td>{{$company['delivery_address']}}</td>
                                <td>{{$company['notes']}}</td>
                                <td>{{$company['archive']}}</td>
                                <td class="actions-menu-area">
                                    <div class="">
                                        <!-- 3-Dot Icon Menu for Grid View -->
                                        <div class="dropdown d-flex justify-content-end">
                                            <button class="icon-primary-orange me-0" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                                                <span class="material-symbols-outlined">more_vert</span>
                                            </button>
                                            <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                                <li>
                                                    <span class="dropdown-item text-primary-dark-mud me-2 edit-company"
                                                        data-id="{{ $company['id'] }}"
                                                        data-company="{{ $company['company_name'] }}"
                                                        data-first-name="@if($company['primary_contact']){{ $company['primary_contact']['first_name'] }}@endif"
                                                        data-last-name="@if($company['primary_contact']){{ $company['primary_contact']['last_name'] }}@endif"
                                                        data-email="@if($company['primary_contact']){{ $company['primary_contact']['email'] }}@endif"
                                                        data-website="{{ $company['website'] }}"
                                                        data-phone="@if($company['primary_contact']){{ $company['primary_contact']['phone'] }}@endif"
                                                        data-billing-address="{{ $company['billing_address'] }}"
                                                        data-delivery-address="{{ $company['delivery_address'] }}"
                                                        data-notes="{{ $company['notes'] }}"
                                                        data-tags="{{ $company['company_tags'] }}"
                                                        data-abn="{{ $company['ABN'] }}"
                                                        data-acn="{{ $company['ACN'] }}"
                                                        data-category="{{ $company['company_category'] }}">
                                                        <span class="sidenav-normal ms-2 ps-1">Edit</span>
                                                    </span>
                                                </li>
                                                <li>
                                                    <span class="dropdown-item text-primary-dark-mud delete-company" data-archive="{{ $company['archive'] }}" data-id="{{ $company['id'] }}">
                                                        <span class="sidenav-normal ms-2 ps-1">@if($company['archive'] == 1) Delete @else Archive @endif</span>
                                                    </span>
                                                </li>
                                                @if($company['archive'] == 1)
                                                <li>
                                                    <span class="dropdown-item text-primary-dark-mud unarchive-data" data-archive="{{ $company['archive'] }}" data-id="{{ $company['id'] }}">
                                                        <span class="sidenav-normal ms-2 ps-1">Unarchive</span>
                                                    </span>
                                                </li>
                                                @endif
                                            </ul>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="tab-pane fade" id="category-pane" role="tabpanel" aria-labelledby="category" tabindex="0">
                    <div class="right-side mb-5">
                        <div class="text-end">
                            <button type="button" class="btn btn-primary-orange plus-icon" data-toggle="modal" data-target="#actionModal" id="addCompanyCategoryBtn" title="Add Company Category">
                                <span class="material-symbols-outlined">add</span>
                            </button>
                        </div>
                    </div>
                    <table class="table responsiveness custom-wrap" id="dtRecordsView2">
                        <thead>
                            <tr>
                                <th class="text-primary-orange">Name</th>
                                <th class="text-primary-orange">Created By</th>
                                <th class="text-primary-orange"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($company_categories as $category)
                            <tr>
                                <td>{{$category['name']}}</td>
                                <td>@if(isset($category['creator']['name'])){{$category['creator']['name']}}@endif</td>
                                <td class="actions-menu-area">
                                    <div class="">
                                        <!-- 3-Dot Icon Menu for Grid View -->
                                        <div class="dropdown d-flex justify-content-end">
                                            <button class="icon-primary-orange me-0" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                                                <span class="material-symbols-outlined">more_vert</span>
                                            </button>
                                            <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                                <li>
                                                    <span class="dropdown-item text-primary-dark-mud me-2 edit-category"
                                                        data-id="{{ $category['id'] }}"
                                                        data-name="{{ $category['name'] }}">
                                                        <span class="sidenav-normal ms-2 ps-1">Edit</span>
                                                    </span>
                                                </li>
                                                <li>
                                                    <span class="dropdown-item text-primary-dark-mud delete-category" data-id="{{ $category['id'] }}">
                                                        <span class="sidenav-normal ms-2 ps-1">Delete</span>
                                                    </span>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="tab-pane fade" id="profile-tab-pane" role="tabpanel" aria-labelledby="profile-tab" tabindex="0">
                    <div class="right-side mb-5">
                        <div class="text-end">
                            <button type="button" class="btn btn-primary-orange plus-icon" data-toggle="modal" data-target="#actionModal" id="addCompanyTagBtn" title="Add Company Tags">
                                <span class="material-symbols-outlined">add</span>
                            </button>
                        </div>
                    </div>
                    <table class="table responsiveness custom-wrap" id="dtRecordsView1">
                        <thead>
                            <tr>
                                <th class="text-primary-orange">Name</th>
                                <th class="text-primary-orange">Created By</th>
                                <th class="text-primary-orange"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($company_tags as $tag)
                            <tr>
                                <td>{{$tag['name']}}</td>
                                <td>@if(isset($tag['creator']['name'])){{$tag['creator']['name']}}@endif</td>
                                <td class="actions-menu-area">
                                    <div class="">
                                        <!-- 3-Dot Icon Menu for Grid View -->
                                        <div class="dropdown d-flex justify-content-end">
                                            <button class="icon-primary-orange me-0" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                                                <span class="material-symbols-outlined">more_vert</span>
                                            </button>
                                            <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                                <li>
                                                    <span class="dropdown-item text-primary-dark-mud me-2 edit-tag"
                                                        data-id="{{ $tag['id'] }}"
                                                        data-name="{{ $tag['name'] }}">
                                                        <span class="sidenav-normal ms-2 ps-1">Edit</span>
                                                    </span>
                                                </li>
                                                <li>
                                                    <span class="dropdown-item text-primary-dark-mud delete-tag" data-id="{{ $tag['id'] }}">
                                                        <span class="sidenav-normal ms-2 ps-1">Delete</span>
                                                    </span>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            
        </div>
    </div>

    <!-- Company Tag Modal -->
    <div class="modal fade" id="companyTag" tabindex="-1" aria-labelledby="companyTagLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title text-primary-orange" id="tagModalLabel">Add Company Tag</h4>
                </div>
                <form id="comp_tag" >
                    @csrf
                    <input type="hidden" name="tagID" id="tagID">
                    <input type="hidden" name="company_model" id="company_model">
                    <div class="modal-body">
                        <div class="row">    
                            <div class="form-group">
                                <label class="text-primary-orange" for="name">Name</label>
                                <input type="text" name="name" id="name" class="form-control" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                    <button type="button" class="btn btn-secondary-white" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-secondary-blue" id="saveTagBtn">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Company Modal -->
    <div class="modal fade" id="actionModal" tabindex="-1" role="dialog" aria-labelledby="actionModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title text-primary-orange" id="actionModalLabel">Add Company</h4>
                </div>
                <form id="companyForm">
                    @csrf
                    <input type="hidden" name="compID" id="compID">
                    <div class="modal-body">
                        <h5>Company</h5>
                        <div class="company-information">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="text-primary-orange" for="company_name">Name <span class="text-danger">*</span></label>
                                        <input type="text" name="company_name" id="company_name" class="form-control" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="text-primary-orange" for="website">Website</label>
                                        <input type="text" name="website" id="website" class="form-control">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="text-primary-orange" for="ABN">ABN</label>
                                        <input type="text" name="ABN" id="ABN" class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="text-primary-orange" for="ACN">ACN</label>
                                        <input type="text" name="ACN" id="ACN" class="form-control">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="text-primary-orange" for="billing_address">Billing Address</label>
                                        <input type="text" name="billing_address" id="billing_address" class="form-control address-field">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="text-primary-orange" for="delivery_address">Delivery Address</label>
                                        <input  type="text" name="delivery_address" id="delivery_address" class="form-control address-field">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="text-primary-orange" for="company_category">Category</label>
                                        <select name="company_category" id="company_category" class="form-select js-example-basic-single ">
                                            <option selected disabled>Select Category</option>
                                            @foreach($company_categories as $category)
                                            <option value="{{$category['id']}}">{{$category['name']}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="text-primary-orange" for="tags">Tags</label>
                                        <select name="company_tags[]" id="company_tags" class="form-select fa-basic-multiple" multiple>
                                            @foreach($company_tags as $tag)
                                            <option value="{{$tag['id']}}">{{$tag['name']}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="form-group">
                                    <label class="text-primary-orange" for="notes">Notes</label>
                                    <textarea name="notes" id="notes" class="form-control"></textarea>
                                </div>
                            </div>
                        </div>
 
                        <div class="company-information">
                            <h5>Primary Person</h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="text-primary-orange" for="email">Email</label>
                                        <input type="text" name="email" id="email" class="form-control">
                                        <div id="email-suggestions" class="list-group" style="position: absolute; z-index: 1000;"></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="text-primary-orange" for="phone">Phone</label>
                                        <input type="number" name="phone" id="phone" class="form-control">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="text-primary-orange" for="first_name">First Name</label>
                                        <input type="text" name="first_name" id="first_name" class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="text-primary-orange" for="last_name">Last Name</label>
                                        <input type="text" name="last_name" id="last_name" class="form-control">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary-white" data-dismiss="modal" id="closeActionModal">Close</button>
                        <button type="submit" class="btn btn-secondary-blue" id="saveCompanyBtn">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')


<script>
    $(document).ready(function() {

        const table = $('#dtCompanyRecordsView').DataTable({
            "order": [],
            responsive: true,
            dom: "<'row mb-4'<'col-md-6 col-sm-6'fB><'col-md-6 col-sm-6 custom-dropdown'l>>" +
                "<'row table-scroll'<'col-sm-12 overflow-container'tr>>" +
                "<'row'<'col-md-5'i><'col-md-7'p>>",
            buttons: [
                    {
                        extend: 'colvis',
                        columns: ':not(:last, :first)',
                        text: '<span class="material-symbols-outlined" style="font-size: 30px; margin-top: -6px;"> view_column </span>',
                        action: function (e, dt, button, config) {
                            // Override default action to prevent default dropdown
                            if ($('.colvis-dropdown').length === 0) {
                                createColVisDropdown(dt);
                            }
                        }
                    }
                ],
            columnDefs: [
                {
                    targets: -1,
                    className: 'noVis always-visible',
                    orderable: false
                },
                {
                    targets: [5,6,7,8,9,10,11], // Specify columns that should be hidden initially
                    visible: false
                }
            ],
            language: {
                search: "",
                searchPlaceholder: "Search",
                lengthMenu: "_MENU_ per page",
                paginate: {
                    previous: "<i class='material-symbols-outlined'>chevron_left</i>",
                    next: "<i class='material-symbols-outlined'>chevron_right</i>"
                }
            },
            pageLength: 25,
            initComplete: function() {
                $("#tableSkeleton").fadeOut(200, ()=>{
                    $("#dtCompanyRecordsView").fadeIn(250);
                });

                // Move the search box to the left and entries dropdown to the right
                const tableWrapper = $(this).closest('.dataTables_wrapper');
                const lengthDropdown = tableWrapper.find('.dataTables_length');
                const colvisButton = tableWrapper.find('.buttons-colvis');
                colvisButton.insertBefore(lengthDropdown); // Move the colvis button before the length dropdown (right side)
                const searchBox = tableWrapper.find('.dataTables_filter');
                searchBox.css({
                    'float': 'left',
                    'margin-top': '0',
                    'margin-right': '20px'
                });
                $('.custom-dropdown').css({
                    'display': 'flex',
                    'justify-content': 'flex-end',
                    'gap': '15px',
                    'align-items': 'center'
                });
                var table = $('#dtCompanyRecordsView').dataTable().api();
                table.columns(11).search(0, true, false).draw(); 
                $('#dtCompanyRecordsView').css('visibility', 'visible');
            }
        });

        function createColVisDropdown(dt) {
            let dropdownHtml = '<div class="colvis-dropdown">';  
            let initiallyCheckedColumns = [1,2,3,4]; // Define which columns should be checked by default
            dt.columns().every(function(idx) {
                let column = this;
                let columnTitle = column.header().textContent;
                if (columnTitle !== "") {
                    if(idx > 1){
                        dropdownHtml += `<label class="colvis-item">
                                    <span>${columnTitle}</span>
                                    <input type="checkbox" class="colvis-checkbox form-check-input" data-column="${idx}" 
                                        ${initiallyCheckedColumns.includes(idx) ? 'checked' : ''}>
                                </label>`;
                    }
                }
            });
            dropdownHtml += '</div>';
            // Remove any existing dropdown before adding a new one
            $('.colvis-dropdown').remove();
            $('body').append(dropdownHtml);

            // Position the dropdown near the button
            let buttonOffset = $('.buttons-colvis').offset();
            $('.colvis-dropdown').css({
                position: 'absolute',
                top: buttonOffset.top + $('.buttons-colvis').outerHeight(),
                left: buttonOffset.left,
                background: '#fff',
                border: '1px solid #ddd',
                padding: '8px',
                borderRadius: '5px',
                boxShadow: '0px 4px 6px rgba(0, 0, 0, 0.1)',
                zIndex: 999
            });

            // Handle checkbox change
            $('.colvis-checkbox').on('change', function () {
                let columnIdx = $(this).data('column');
                let column = dt.column(columnIdx);
                column.visible($(this).prop('checked'));
            });

            // Close dropdown on outside click
            $(document).on('click', function (e) {
                if (!$(e.target).closest('.colvis-dropdown, .buttons-colvis').length) {
                    $('.colvis-dropdown').remove();
                }
            });
        }


        $('#dtRecordsView1,#dtRecordsView2').DataTable({
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
        $('#company_tags').select2({
            dropdownParent: $('#actionModal'),
            width: '100%'
        });
    });    
    // Add Client Button Click
    $(document).on('click', '#addCompanyBtn', function() {
        $('#actionModalLabel').text('Add Company');
        $('#saveCompanyBtn').text('Create');
        $('#actionModal').modal('show');     
    });  

    $(document).on('click', '#addCompanyTagBtn,#addCompanyCategoryBtn', function() {
        const clickedId = this.id;
        var title, module
        if (clickedId === 'addCompanyTagBtn') {
            title = "Add Company Tags"
            module = "Tag"
        } else if (clickedId === 'addCompanyCategoryBtn') {
            title = "Add Company Category"
            module = "Company"
        }
        $('#tagModalLabel').html(title)
        $('#company_model').val(module)
        $('#companyTag').modal('show');    
    });  


    $('#comp_tag').on('submit', function(e) {
        e.preventDefault();
        const tagtId = $('#tagID').val();
        const module = $('#company_model').val()
        var url
        if(module == "Tag"){
            url = tagtId ?
            "{{ route('update.company-tag', ':id') }}".replace(':id', tagtId) :
            "{{ route('save.company-tag') }}";
        }else{
            url = tagtId ?
            "{{ route('update.company-category', ':id') }}".replace(':id', tagtId) :
            "{{ route('save.company-category') }}";
        }
      
        const method = tagtId ? 'PUT' : 'POST';
        $.ajax({
            url: url,
            method: method,
            data: $(this).serialize(),
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: response.message,
                        timer: 2000
                    }).then(() => {
                        location.reload();
                    });
                }else{
                    Swal.fire({
                        icon: 'warning',
                        title: 'Warning!',
                        text: response.message
                    })
                }
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    let errorMessage = '';

                    for (const [field, messages] of Object.entries(errors)) {
                        errorMessage += `${field}: ${messages.join(', ')}\n`;
                    }

                    Swal.fire({
                        icon: 'warning',
                        title: 'Validation Errors',
                        text: errorMessage
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: xhr.responseJSON.message || 'An error occurred'
                    });
                }
            }
        });
    });

    $(document).on('click', '.edit-tag,.edit-category', function() {
        var title, module
        if (this.classList.contains('edit-tag')) {
            title = "Edit Tag"
            module = "Tag"
        } else if (this.classList.contains('edit-category')) {
            title = "Edit Category"
            module = "Company"
        }
        const id = $(this).data('id');
        const name = $(this).data('name');
        $('#tagID').val(id);
        $('#name').val(name);
        $('#tagModalLabel').text(title);
        $('#company_model').val(module)
        $('#saveTagBtn').text('Update');
        $('#companyTag').modal('show');    
    });

    $(document).on('click', '.delete-tag,.delete-category', function() { 
        const id = $(this).data('id');
        var url
        if (this.classList.contains('delete-tag')) {
            url = "{{ route('delete.company-tag', ':id') }}".replace(':id', id);
        }else if (this.classList.contains('delete-category')) {
            url = "{{ route('delete.company-category', ':id') }}".replace(':id', id);
        }
        
        Swal.fire({
            title: 'Are you sure?',
            text: 'You won\'t be able to revert this!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: url,
                    method: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Deleted!',
                                text: response.message,
                                timer: 2000
                            }).then(() => {
                                location.reload();
                            });
                        }else{
                            Swal.fire({
                                icon: 'warning',
                                title: 'Warning!',
                                text: response.message
                            })
                        }
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: xhr.responseJSON.message || 'An error occurred'
                        });
                    }
                });
            }
        });
    });

    $('#companyForm').on('submit', function(e) {
      e.preventDefault();
      const compID = $('#compID').val();
      const url = compID ?
          "{{ route('update.company', ':id') }}".replace(':id', compID) :
          "{{ route('save.company') }}";
      const method = compID ? 'PUT' : 'POST';
      $.ajax({
            url: url,
            method: method,
            data: $(this).serialize(),
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: response.message,
                        timer: 2000
                    }).then(() => {
                        location.reload();
                    });
                }else{
                    Swal.fire({
                        icon: 'warning',
                        title: 'Warning!',
                        text: response.message
                    })
                }
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    let errorMessage = '';

                    for (const [field, messages] of Object.entries(errors)) {
                        errorMessage += `${field}: ${messages.join(', ')}\n`;
                    }

                    Swal.fire({
                        icon: 'warning',
                        title: 'Validation Errors',
                        text: errorMessage
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: xhr.responseJSON.message || 'An error occurred'
                    });
                }
            }
        });
    });

    $(document).on('click', '.edit-company', function() {
        const id = $(this).data('id');
        $('#compID').val(id);
        $('#company_name').val($(this).data('company'));
        $('#first_name').val($(this).data('first-name'));
        $('#last_name').val($(this).data('last-name'));
        $('#email').val($(this).data('email'));
        $('#phone').val($(this).data('phone'));
        $('#billing_address').val($(this).data('billing-address'));
        $('#delivery_address').val($(this).data('delivery-address'));
        $('#notes').val($(this).data('notes'));
        $('#ABN').val($(this).data('abn'));
        $('#ACN').val($(this).data('acn'));
        $('#website').val($(this).data('website'));
        if ($(this).attr('data-tags')) {
            var jsonString = $(this).attr('data-tags'); // Get raw string
            var array = JSON.parse(jsonString); // Safely parse JSON

            $('#company_tags').val(array).trigger('change'); // Set Select2 
        }
        $('#company_category').val($(this).data('category')).trigger('change')
        $('#actionModalLabel').text('Edit Company');
        $('#saveCompanyBtn').text('Update');
        $('#actionModal').modal('show');    
    });

    $(document).on('click', '.delete-company', function() { 
        const id = $(this).data('id');
        const archive = $(this).data('archive');
        const url = "{{ route('delete.company', ':id') }}".replace(':id', id);
        Swal.fire({
            title: 'Are you sure?',
            text: (archive == 0) ? 'You want to move this record to archive status.': 'You won\'t be able to revert this!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: (archive == 0)? 'Yes, archive it!': 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: url,
                    method: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: (archive == 0)?'Archived':'Deleted!',
                                text: response.message,
                                timer: 2000
                            }).then(() => {
                                location.reload();
                            });
                        }else{
                            Swal.fire({
                                icon: 'warning',
                                title: 'Warning!',
                                text: response.message
                            })
                        }
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: xhr.responseJSON.message || 'An error occurred'
                        });
                    }
                });
            }
        });
    });

    let selectedCols = [];
    $(document).on('change', '.colvis-dropdown .colvis-item input[type="checkbox"]', function () {
        selectedCols = [];

        $('.colvis-dropdown .colvis-item input[type="checkbox"]:checked').each(function () {
            let colName = $(this).closest('label').find('span').text().trim();
            selectedCols.push(colName);
        });

        // Store in a hidden input or variable
        $('#selectedCols').val(JSON.stringify(selectedCols));
    });



    $(document).on('click','.export-csv, .export-excel', function(){
        let url = $(this).attr('data-url')
        if($('#selectedCols').val() != ''){
            let selectedCols = JSON.parse($('#selectedCols').val() || '[]');
            let data = {'_token':$('meta[name="csrf-token"]').attr('content'),'selectedCols':selectedCols,'model':'companies'};	
            $.ajax({
                type: "POST",
                url: "{{route('save.download.attr')}}",
                dataType: 'json',
                data: data,
                beforeSend: function () {
                },
                success: function (response) {
                    if(response.status == false){
                        Swal.fire({
                            title: "warrning!",
                            text: response.message,
                            icon: "warning"
                        });
                    }else{
                        window.open(url,'_blank');
                    }
                },
                complete: function(){
                }
            });
        }else{
            window.open(url,'_blank');
        }
    })
    

    $('#email').on('input', function () {
        let query = $(this).val();
        if (query.length >= 2) {
            $.ajax({
                url: "{{route('contact.search')}}", // Route to get matched emails
                type: 'GET',
                data: { email: query },
                success: function (data) {
                    let suggestions = '';
                    if (data.length) {
                        data.forEach(contact => {
                            suggestions += `<a href="#" class="list-group-item list-group-item-action" data-contact='${JSON.stringify(contact)}'>${contact.email}</a>`;
                        });
                    }
                    $('#email-suggestions').html(suggestions).show();
                }
            });
        } else {
            $('#email-suggestions').hide();
        }
    });

    // When a suggestion is clicked
    $(document).on('click', '#email-suggestions a', function (e) {
        e.preventDefault();
        let contact = $(this).data('contact');
        $('#email').val(contact.email);
        $('#phone').val(contact.phone);
        $('#first_name').val(contact.first_name);
        $('#last_name').val(contact.last_name);
        $('#email-suggestions').hide();
    });

    // Hide suggestions when clicking outside
    $(document).click(function (e) {
        if (!$(e.target).closest('#email').length && !$(e.target).closest('#email-suggestions').length) {
            $('#email-suggestions').hide();
        }
    });

    $(document).on('click','.sort_record',function(){
        let search_val = $(this).data('value')
        $('#customFilter').val(search_val)
        var table = $('#dtCompanyRecordsView').dataTable().api();
		if (search_val == 0 || search_val == 1){
			table.columns(11).search(search_val, true, false).draw();
		}else{ 
			table.columns().search('').draw(); 
		} 
    })

    $(document).on('click', '.unarchive-data', function() {
        const archive = $(this).data('archive');
        const id = $(this).data('id');
        const url = "{{ route('unarchive.company', ':id') }}".replace(':id', id);
        Swal.fire({
            title: 'Are you sure?',
            text: 'You want to Unarchive this record',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, Unarchive it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: url,
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Unarchived!',
                                text: response.message,
                                timer: 2000
                            }).then(() => {
                                location.reload();
                            });
                        }
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: xhr.responseJSON.message || 'An error occurred'
                        });
                    }
                });
            }
        });
    });

    $(document).on('click','#companyDefault',function() {
        let selectvalue = $('#companyDefault').is(':checked')
        $('#dtCompanyRecordsView tbody tr').each(function() {
            $(this).find('td:eq(0) input').prop('checked',selectvalue)
        });
    });


    function delete_selected_companies(){
        let companyobj = [];
        $("table#dtCompanyRecordsView tbody tr").each(function () {
            if($(this).find('td:eq(0) input').prop('checked') == true){
                let id = $(this).find('td:eq(0) input').data('company')
                companyobj.push(id)
            }
        });
        if(companyobj.length == 0){
            Swal.fire({
                icon: 'warning',
                title: 'Warning!',
                text: 'No Companies select'
            });
        }else{
            let archiveVal = $('#customFilter').val()
            let html,title,confirmBtn,inputText
            if(archiveVal == "1"){
                title = 'Delete Companies'
                confirmBtn = 'Delete'
                inputText = 'delete'
                html = `<p>The <strong>${companyobj.length}</strong> selected items will be permanently deleted and cannot be retrieved. <strong>Are you sure you want to delete them?</strong></p>
                        <p>To confirm, enter the phrase <strong>delete</strong>.</p>
                        <input id="confirmInput" class="swal2-input" placeholder="Type delete here">`
            }else{
                title = 'Archive Companies'
                confirmBtn = 'Archive'
                inputText = 'archive'
                html = `<p>The <strong>${companyobj.length}</strong> selected items will be archived. <strong>Are you sure you want to archive them?</strong></p>
                        <p>To confirm, enter the phrase <strong>archive</strong>.</p>
                        <input id="confirmInput" class="swal2-input" placeholder="Type archive here">`
            }            
            Swal.fire({
                title: title,
                html: html,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: confirmBtn,
                confirmButtonColor: '#d33',
                cancelButtonText: 'Cancel',
                preConfirm: () => {
                const input = document.getElementById('confirmInput').value;
                if (input !== inputText) {
                    Swal.showValidationMessage(`Please type "${inputText}" to confirm.`);
                }
                return input;
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    var companies = JSON.stringify(companyobj);
                    let data = {'archive':archiveVal,'companyobj':companies,'_token':$('meta[name="csrf-token"]').attr('content')}
                    $.ajax({
                        type: "POST",
                        url: "{{route('bulk-delete.company')}}",
                        dataType: 'json',
                        data: data,
                        beforeSend: function () {},
                        success: function (response) {
                            if(response.status == true){
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Success!',
                                    text: response.message,
                                    timer: 2000
                                }).then(() => {
                                    location.reload();
                                });
                            }else{
                                Swal.fire({
                                    icon: 'warning',
                                    title: 'Validation Errors',
                                    text: response.message
                                });
                            }
                        }
                    });
                }
            }); 
        }
    }

</script>

<script>
    // This function is called once the Google API is fully loaded
    function initAutocomplete() {
        const modal = document.getElementById('actionModal');

        // Initialize autocomplete only when modal is shown
        modal.addEventListener('shown.bs.modal', () => {
            const inputs = modal.querySelectorAll('.address-field');

            inputs.forEach((input) => {
                const autocomplete = new google.maps.places.Autocomplete(input, {
                    componentRestrictions: { country: "au" },
                    fields: ["formatted_address", "geometry", "name"],
                    types: ["address"]
                });

                // Handle address selection
                autocomplete.addListener('place_changed', function () {
                    const place = autocomplete.getPlace();
                    console.log(`${input.id} selected:`, place.formatted_address);
                });
            });

            // Fix dropdown positioning within the modal
            setTimeout(() => {
                const pacContainers = document.querySelectorAll('.pac-container');
                pacContainers.forEach(container => {
                    modal.appendChild(container);
                });
            }, 300);
        });
    }
</script>

<!-- ✅ Load Google Maps API asynchronously (best practice) -->
<script
    src="https://maps.googleapis.com/maps/api/js?key=AIzaSyA9TcalC1Y0fdR_BJSRh6wvmZL4dbOMoeo&libraries=places&region=AU&callback=initAutocomplete"
    async
    defer>
</script>
@endpush