@extends('backend.master', [
'pageTitle' => 'Raw Materials',
'activeMenu' => [
'item' => 'Raw Materials',
'subitem' => 'Raw Materials',
'additional' => '',
],
'features' => [
'datatables' => '1',
],
'breadcrumbItems' => [
['label' => 'Data Entry', 'url' => '#'],
['label' => 'Product']
],
])

@push('styles')
<style>
    .btn-hidden {
        display: none !important;
    }
    .fixed-bottom-right {
        position: fixed;
        bottom: 20px;
        right: 20px;
        z-index: 1000;
    }
    #dtRecordsView1 {
        visibility: hidden;
    }

    .scrollable-results {
        overflow-y: auto;
        max-height: 600px;
    }

    .list-view-item:hover {
        background-color: #f8f9fa;
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
        height: 500px;
        overflow: auto;
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
    table#dtRecordsView1 thead tr th.hide,table#dtRecordsView1 tbody tr td.hide{display: none !important;}
    button.custom-search-style {color: var(--bs-white) !important;background: var(--primary-color);background-color: var(--primary-color) !important;border: 1px solid var(--primary-color) !important;}
    .hidden {display: none;}
    .visible {display: block;}
</style>
@endpush



@section('content')
<div class="container-fluid product-search dataTable-wrapper">
    <div class="card-header">
        <div class="title-add">
            <h1 class="page-title">Raw Materials</h1>  
            <input type="hidden" class="selectedCols" id="selectedCols">
            <div class="right-side content d-flex flex-row-reverse align-items-center">
                <div class="view-toggle me-lg-4 me-3">
                    <button id="listViewBtn" class="icon-primary-orange {{ $viewType == 'list' ? 'active' : '' }} px-3 me-0" title="List View">
                        <a href="{{route('manage.raw-materials')}}"><i class="material-symbols-outlined">list</i></a>
                    </button>
                    <button id="gridViewBtn" class="icon-primary-orange {{ $viewType == 'grid' ? 'active' : '' }} me-0 pe-3" title="Grid View">
                        <a href="{{route('manage.grid.raw-materials')}}"><i class="material-symbols-outlined">grid_view</i></a>
                    </button>
                </div>

                @if($user_role != 4)

                <div class="btn-group click-dropdown me-2">
                    <button type="button" class="btn btn-primary-orange plus-icon" title="Add Ingredient">
                        <span class="material-symbols-outlined">add</span>
                    </button>
                    <ul class="dropdown-menu">
                        <li>
                            <a class="dropdown-item" href="{{ route('add.raw-materials') }}">
                                Manual
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="javascript:void(0)" onclick="rawMaterialFlow.open()">
                                Create from Specification
                            </a>
                        </li>
                    </ul>
                </div>
                @endif

                @if(in_array($user_role, [1,2,3]))
                    <button type="button" class="btn btn-primary-orange plus-icon me-2" onclick="delete_selected_rawmaterial()" title="Delete Ingredient">
                        <span class="material-symbols-outlined">delete</span>
                    </button>
                @endif
                @if($user_role == 1)        
                    <div class="btn-group click-dropdown me-2">
                        <button type="button" class="btn btn-primary-orange plus-icon" title="Download Ingredient">
                            <span class="material-symbols-outlined">download</span>
                        </button>
                        <ul class="dropdown-menu">
                            <li>
                                <a class="dropdown-item export-csv" href="javascript:void(0);" data-url="/download/csv/ingredient">
                                    Download as CSV
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item export-excel" href="javascript:void(0);" data-url="/download/excel/ingredient">
                                    Download as Excel
                                </a>
                            </li>
                        </ul>
                    </div>
                    <div class="Export-btn me-2">
                        <div class="btn-group click-dropdown">
                            <a href="{{ route('ingredients.import') }}" class="btn btn-primary-orange plus-icon" title="Upload / Import data">
                                <span class="material-symbols-outlined">upload</span>
                            </a>
                        </div>
                    </div>
                @endif

                @if(in_array($user_role, [1,2,3]))
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
                @endif
                <div class="Export-btn me-2">
                    <div class="btn-group click-dropdown">
                        <button type="button" class="btn btn-primary-orange plus-icon" title="Filter">
                            <span class="material-symbols-outlined" id="custom_search">filter_list</span>
                        </button>
                    </div>
                </div>

            </div>
        </div>
       
    </div>
    <div class="card-body">
             <!-- Loader -->
            <div id="tableSkeleton" class="skeleton-wrapper">
                @for($i=0;$i<6;$i++)
                <div class="skeleton-row"></div>
                @endfor
            </div>
            <table class="table responsiveness custom-wrap" id="dtRecordsView1" style="display:none;">
                <thead>
                    <tr>
                        <th class="text-primary-blue">
                            <div class="form-check-temp p-1">
                                <input class="form-check-input raw_material_check" type="checkbox" id="rawmaterialDefault">
                            </div>
                        </th>
                        <th class="text-primary-blue">Photo</th>
                        <th class="text-primary-blue">Name</th>
                        <th class="text-primary-blue">Supplier</th>
                        <th class="text-primary-blue">Spec</th>
                        <th class="text-primary-blue">Category</th>
                        <th class="text-primary-blue">Tags</th>
                        <th class="text-primary-blue">Status</th>
                        <th class="text-primary-blue">Ranging</th>
                        <th class="text-primary-blue">Source</th>
                        <th class="text-primary-blue">GTIN</th>
                        <th class="text-primary-blue">Supplier Code</th>
                        <th class="text-primary-blue">Ingredients List</th>
                        <th class="text-primary-blue">Allergens</th>
                        <th class="text-primary-blue text-end">Price Per Item</th>
                        <th class="text-primary-blue text-end">Units Per Item</th>
                        <th class="text-primary-blue">Ingredient Units</th>
                        <th class="text-primary-blue">Is Liquid (Yes/No)</th>
                        <th class="text-primary-blue">Price Per KG/L</th>
                        <th class="text-primary-blue">Country of Origin</th>
                        <th class="text-primary-blue">Australian %</th>
                        <th class="text-primary-blue text-end">Specific Gravity</th>
                        <th class="text-primary-blue text-end">Energy (kJ)</th>
                        <th class="text-primary-blue text-end">Protein (g)</th>
                        <th class="text-primary-blue text-end">Total Fat (g)</th>
                        <th class="text-primary-blue text-end">Saturated Fat (g)</th>
                        <th class="text-primary-blue text-end">Carbohydrate (g)</th>
                        <th class="text-primary-blue text-end">Sugars (g)</th>
                        <th class="text-primary-blue text-end">Sodium (mg)</th>
                        <th class="text-primary-blue">Shelf Life</th>
                        <th class="text-primary-blue">Description</th>
                        <th class="text-primary-blue hide"></th>
                        <th class="text-primary-blue"></th>
                    </tr>
                </thead>
                <tbody>
                @foreach($lists as $list)
                    <tr class="search_table_row">
                        <td class="text-primary-dark-mud">
                            <div class="form-check-temp p-1">
                                <input class="form-check-input raw_check" data-rawmaterial="{{$list->id}}" type="checkbox" id="raw_{{$list->id}}">
                            </div>
                        </td>
                        <td class="align-middle">
                            <!-- Product image with thumbnail styling -->
                            @php
                            $imgUrl = '';
                            if($list->ing_image){
                            $imgUrl = get_default_image_url('raw_material',$list->ing_image,$list->id);
                            }else{
                            $imgUrl = env('APP_URL')."/assets/img/ing_default.png";
                            }
                            @endphp
                            <img src="{{ $imgUrl }}" alt="Product Image" class="product-thumbnail list">
                        </td>
                        <td class="align-middle">
                            <div class="product_name text-primary-dark-mud mb-1">{{ $list->name_by_kitchen }}</div>
                            <div class="product_sku text-primary-dark-mud-sm">{{ $list->ing_sku }}</div>
                        </td>
                        <td class="text-primary-dark-mud">{{ $list->supplier ? $list->supplier->company_name : 'N/A' }}</td>
                        <td class="align-middle">@if($list->supplier_spec_url) <a href="{{ $list->supplier_spec_url }}" target="_blank">URL</a> @else N/A @endif</td>
                        <td class="align-middle">{{ $list->raw_category ? $list->raw_category->name : 'N/A' }}</td>
                        <td class="align-middle">@if($list->ing_tags) {{$list->ing_tags}}@endif</td>
                        <td class="align-middle">@if($list->raw_material_status){{ $list->raw_material_status }}@endif</td>
                        <td class="align-middle">@if($list->raw_material_ranging){{ $list->raw_material_ranging }}@endif</td>
                        <td class="align-middle">{{ $list->source ?? 'Raw Material' }}</td>
                        <td class="align-middle">{{ $list->gtin }}</td>
                        <td class="align-middle">{{ $list->supplier_code }}</td>
                        <td class="align-middle">{{ $list->ingredients_list_supplier }}</td>
                        <td class="align-middle">{{ $list->allergens }}</td>
                        <td class="align-middle text-end">{{ $list->price_per_item }}</td>
                        <td class="align-middle text-end">{{ $list->units_per_item }}</td>
                        <td class="align-middle">{{ $list->ingredient_units }}</td>
                        <td class="align-middle">{{ $list->purchase_units }}</td>
                        <td class="align-middle text-end">{{ $list->price_per_kg_l }}</td>
                        <td class="align-middle">{{ $list->Country ? $list->Country->name : 'N/A' }}</td>
                        <td class="align-middle">{{ $list->australian_percent }}</td>
                        <td class="align-middle text-end">{{ $list->specific_gravity }}</td>
                        <td class="align-middle text-end">{{ $list->energy_kj }}</td>
                        <td class="align-middle text-end">{{ $list->protein_g }}</td>
                        <td class="align-middle text-end">{{ $list->fat_total_g }}</td>
                        <td class="align-middle text-end">{{ $list->fat_saturated_g }}</td>
                        <td class="align-middle text-end">{{ $list->carbohydrate_g }}</td>
                        <td class="align-middle text-end">{{ $list->sugars_g }}</td>
                        <td class="align-middle text-end">{{ $list->sodium_mg }}</td>
                        <td class="align-middle">{{ $list->shelf_life }}</td>
                        
                        <td class="align-middle">
                            <div class="product_des text-primary-dark-mud">{!!truncateDescription($list->raw_material_description , 120)!!}</div>
                        </td>
                        <td class="text-primary-dark-mud hide">{{ $list->archive }}</td>
                        <td class="align-middle icon-section">

                            <!-- Edit and Delete Actions (inline) -->
                            <div class="d-flex justify-content-end">
                                @if($user_role != 4)
                                <button type="button" class="icon-primary-orange me-2" title="Favourite">
                                    <span class="material-symbols-outlined" onclick="make_favorite(this)" id="makefavorite_{{$list->id}}" data-favor="{{$list->favorite}}" data-url="{{route('favorite.raw-materials',['id'=>$list->id])}}">favorite</span>
                                </button>
                                <!-- 3-Dot Icon for Menu -->
                                <div class="dropdown d-inline">
                                    <button class="icon-primary-orange me-0" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                                        <span class="material-symbols-outlined">more_vert</span>
                                    </button>
                                    <ul class="dropdown-menu prod" aria-labelledby="dropdownMenuButton">
                                        <li>
                                            <a href="{{ route('edit.raw-materials', ['id'=>$list->id]) }}" class="dropdown-item text-primary-dark-mud me-2">
                                                <span class="sidenav-normal ms-2 ps-1">Edit</span>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0)" data-url="{{ route('destroy.raw-materials', ['id'=>$list->id]) }}" data-archive="{{ $list->archive }}" onclick="commonDelete(this)" class="dropdown-item text-primary-dark-mud me-2">
                                                <span class="sidenav-normal ms-2 ps-1">@if($list->archive == 1) Delete @else Archive @endif</span>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0)" data-type="ingr" data-url="{{ route('duplicate.raw-materials', ['id'=>$list->id]) }}" onclick="make_duplicate(this)" class="dropdown-item text-primary-dark-mud me-2">
                                                <span class="sidenav-normal ms-2 ps-1">Duplicate</span>
                                            </a>
                                        </li>
                                        @if($list->archive == 1)
                                        <li>
                                            <span class="dropdown-item text-primary-dark-mud unarchive-data" data-archive="{{ $list->archive }}" data-id="{{ $list->id }}">
                                                <span class="sidenav-normal ms-2 ps-1">Unarchive</span>
                                            </span>
                                        </li>
                                        @endif
                                    </ul>
                                    @endif
                                </div>
                            </div>
                            
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
</div>

{{-- Include Specification Search Modal --}}
@include('components.raw-materials.specification-search-modal')

{{-- Include Raw Material Details Modal --}}
@include('components.raw-materials.details-modal')

{{-- Include Confirmation Modal --}}
@include('components.raw-materials.confirm-modal')

@endsection

@push('scripts')
<script src="{{ asset('assets') }}/js/ingredient.js"></script>
<script src="{{ asset('assets') }}/js/raw-material-creation.js"></script>
<script>
    $(document).ready(function() {
        var tableArray = @json($lists);
         var categories = @json($categories); 
        var tags = @json($ingtags);
        var prod_status = @json($prod_status);
        var prod_ranging = @json($prod_ranging); 

        var user_role = "{{$user_role}}"
        const table = $('#dtRecordsView1').DataTable({
            "order": [],
            responsive: true,
            deferRender: true,
            autoWidth:false,
            dom: "<'row mb-4'<'col-md-6 col-sm-6'fB><'col-md-6 col-sm-6 custom-dropdown'l>>" +
                "<'row table-scroll'<'col-sm-12 overflow-container'tr>>" +
                "<'row'<'col-md-5'i><'col-md-7'p>>", 

            buttons: [{
                    extend: 'csvHtml5',
                    text: 'CSV',
                    className: 'btn-hidden buttons-csv',
                    exportOptions: {
                        columns: function (idx, data, node) {
                            // Exclude the last column
                            let totalColumns = $('#dtRecordsView1').DataTable().columns().count();
                            return idx < totalColumns - 1; 
                        }
                    },
                    title: ""
                },
                {
                    extend: 'excelHtml5',
                    text: 'Excel',
                    className: 'btn-hidden buttons-excel',
                    exportOptions: {
                        columns: function (idx, data, node) {
                            // Exclude the last column
                            let totalColumns = $('#dtRecordsView1').DataTable().columns().count();
                            let excludedColumns = [totalColumns - 1]; // Exclude last column (adjust index if needed)
                            return !excludedColumns.includes(idx) && $('#dtRecordsView1').DataTable().column(idx).visible();

                            // return idx < totalColumns - 1; 
                        }
                    },
                    title: ""
                },
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
                },
                // {
                //     text: 'Delete Selected Items',
                //     className: 'btn btn-secondary-blue custom-btn',
                //     action: function (e, dt, node, config) {
                //         delete_selected_rawmaterial();
                //     }
                // }
            ],

            columnDefs: [
                {
                    targets: -1,
                    className: 'noVis always-visible',
                    orderable: false
                },
                {   
                    targets: [10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30], // Specify columns that should be hidden initially
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
                    $("#dtRecordsView1").fadeIn(250);
                });
                // Move the search box to the left and entries dropdown to the right
                const tableWrapper = $(this).closest('.dataTables_wrapper');
                const lengthDropdown = tableWrapper.find('.dataTables_length');
                // Create the new filter dropdown row
                const customFilterRow = `
                        <div class="row mt-4 me-1 hidden" id="customFilterRow">
                        <div class="col-md-3 mb-2">
                            <label class="form-label">Category</label>
                            <select name="prod_category" id="filterCategory" class="form-control-select js-example-basic-single" data-module="category" onchange="custom_search(this)">
                                <option disabled selected>Select Category</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-2">
                            <label class="form-label">Tags</label>
                            <select name="prod_tags[]" id="filterTags" class="form-control select2-tags"  data-module="tags" onchange="custom_search(this)" multiple >
                            </select>
                        </div>
                        <div class="col-md-3 mb-2">
                            <label class="form-label">Status</label>
                            <select name="product_status" id="filterStatus" class="form-control-select js-example-basic-single" data-module="status" onchange="custom_search(this)">
                                <option disabled selected>Select Status</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-2">
                            <label class="form-label">Ranging</label>
                            <select name="product_ranging" id="filterRanging" class="form-control-select js-example-basic-single" data-module="ranging" onchange="custom_search(this)">
                                <option disabled selected>Select Ranging</option>
                            </select>
                        </div>
                        <div class="col-lg-3">
                            <div class="Export-btn mt-2 text-end">
                                <div class="btn-group click-dropdown">
                                    <button type="button" class="btn btn-primary-orange plus-icon" title="Reset" id="reset_filters">
                                        <span class="material-symbols-outlined">refresh</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>`;
                // Insert the new row after the search input row
                tableWrapper.find('.dataTables_length').parent().after(customFilterRow);

                categories.forEach(cat => {
                    $('#filterCategory').append(`<option value="${cat.name}">${cat.name}</option>`);
                });

                // Populate tags (multi-select)
                tags.forEach(tag => {                    
                    $('#filterTags').append(`<option value="${tag.name}">${tag.name}</option>`);
                });

                // Populate product status
                prod_status.forEach(status => {
                    $('#filterStatus').append(`<option value="${status}">${status}</option>`);
                });

                // Populate product ranging
                prod_ranging.forEach(ranging => {
                    $('#filterRanging').append(`<option value="${ranging}">${ranging}</option>`);
                });

                $('.js-example-basic-single, .select2-tags').select2({
                    width: '100%'
                });

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
                //$('#customFilter').css('height', '38px');
                var table = $('#dtRecordsView1').dataTable().api();
                table.columns(31).search(0, true, false).draw(); 
                $('#dtRecordsView1').css('visibility', 'visible');
                if (tableArray.length < 7) {
                    $('.table-scroll').removeClass('table-scroll');
                }
            }
        });

        // Export button handlers for dropdown
        // $('.export-csv').on('click', function() {
        //     table.button('.buttons-csv').trigger();
        // });

        // $('.export-excel').on('click', function() {
        //     table.button('.buttons-excel').trigger();
        // });
    });


    function createColVisDropdown(dt) {
        let dropdownHtml = '<div class="colvis-dropdown">';        
        dt.columns().every(function (idx) {
            let column = this;
            let columnTitle = column.header().textContent;

            if((columnTitle != "" || columnTitle != '') && idx > 1){                
                dropdownHtml += `<label class="colvis-item">
                                <span>${columnTitle}</span>
                                <input type="checkbox" class="colvis-checkbox form-check-input" data-column="${idx}" 
                                    ${column.visible() ? 'checked' : ''}>
                            </label>`;
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

        // View toggle functionality
        $('#listViewBtn, #gridViewBtn').on('click', function() {
            // Remove active class from all buttons
            $('#listViewBtn, #gridViewBtn').removeClass('active');

            // Add active class to clicked button
            $(this).addClass('active');

            // Set the view type input
            $('#viewTypeInput').val(
                $(this).attr('id') === 'listViewBtn' ? 'list' : 'grid'
            );
        });
    }


    $(document).on('click','#rawmaterialDefault',function() {
        let selectvalue = $('#rawmaterialDefault').is(':checked')
        $('#dtRecordsView1 tbody tr').each(function() {
            $(this).find('td:eq(0) input').prop('checked',selectvalue)
        });
    });

    function delete_selected_rawmaterial(){
        let ingobj = [];
        // $('table#dtRecordsView1').DataTable().destroy();
        // $('table#dtRecordsView1').dataTable({ paging: false, ordering: false });
        $("table#dtRecordsView1 tbody tr").each(function () {
            if($(this).find('td:eq(0) input').prop('checked') == true){
                let id = $(this).find('td:eq(0) input').data('rawmaterial')
                ingobj.push(id)
            }
        });
        if(ingobj.length == 0){
            Swal.fire({
                icon: 'warning',
                title: 'Warning!',
                text: 'No Rawmaterial select'
            });
        }else{
            let archiveVal = $('#customFilter').val()
            let html,title,confirmBtn,inputText
            if(archiveVal == "1"){
                title = 'Delete Raw Materials'
                confirmBtn = 'Delete'
                inputText = 'delete'
                html = `<p>The <strong>${ingobj.length}</strong> selected items will be permanently deleted and cannot be retrieved. <strong>Are you sure you want to delete them?</strong></p>
                        <p>To confirm, enter the phrase <strong>delete</strong>.</p>
                        <input id="confirmInput" class="swal2-input" placeholder="Type delete here">`
            }else{
                title = 'Archive Raw Materials'
                confirmBtn = 'Archive'
                inputText = 'archive'
                html = `<p>The <strong>${ingobj.length}</strong> selected items will be archived. <strong>Are you sure you want to archive them?</strong></p>
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
                    var ingr = JSON.stringify(ingobj);
                    let data = {'archive':archiveVal,'ingobj':ingr,'_token':$('meta[name="csrf-token"]').attr('content')}
                    $.ajax({
                        type: "POST",
                        url: "{{route('delete.raw-materials')}}",
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
                        },
                        complete: function () {
                        }
                    });
                }
            }); 
            
        }
    }

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
            let data = {'_token':$('meta[name="csrf-token"]').attr('content'),'selectedCols':selectedCols,'model':'ingredient'};	
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

    $(document).on('click','.sort_record',function(){
        let search_val = $(this).data('value')
        $('#customFilter').val(search_val)
        var table = $('#dtRecordsView1').dataTable().api();
		if (search_val == 0 || search_val == 1){
			table.columns(31).search(search_val, true, false).draw();
		}else{ 
			table.columns().search('').draw(); 
		} 
    })


    $(document).on('click', '.unarchive-data', function() {
        const archive = $(this).data('archive');
        const id = $(this).data('id');
        const url = "{{ route('unarchive.raw-materials', ':id') }}".replace(':id', id);
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

    $(document).on('click', '#custom_search', function () {
        const $searchBtn = $(this);
        const $customFilter = $('#customFilterRow');

        $searchBtn.toggleClass('custom-search-style');
        $customFilter.toggleClass('hidden visible');
    });

    function custom_search(_this){
        let val = $(_this).val()
        console.log(val);
        
        let module = $(_this).data('module')
        let columnCount
        switch (module) {
            case 'category':
                columnCount = 5
                break;

            case 'status':
                columnCount = 7
                break;

            case 'ranging':
                columnCount = 8
                break;

            case 'tags':
                columnCount = 6
                break;
        }
        var table = $('#dtRecordsView1').dataTable().api();
        if (val && val.length > 0) {
            if (Array.isArray(val) && val.length > 0) {
                // create OR regex: ^(tag1|tag2|tag3)$
                let pattern = '^(' + val.map(v => escapeRegex(v)).join('|') + ')$';

                table.columns(columnCount).search(pattern, true, false).draw();
            } else if (val) {
                // exact match for single value
                table.columns(columnCount).search('^' + val + '$', true, false).draw();
            } else {
                table.columns(columnCount).search('', true, false).draw();
            }

        } else {
            table.columns(columnCount).search('', true, false).draw();
        }
    }

    $(document).on('click','#reset_filters',function(){
        $('#filterCategory, #filterStatus, #filterRanging').val(null).trigger('change');
        $('#filterTags').val([]).trigger('change');
    })

    function escapeRegex(text) {
        return text.replace(/[-\/\\^$*+?.()|[\]{}]/g, '\\$&');
    }

</script>

@endpush