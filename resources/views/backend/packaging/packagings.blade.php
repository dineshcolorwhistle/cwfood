@extends('backend.master', [
'pageTitle' => 'Packaging Management',
'activeMenu' => [
'item' => 'Packaging',
'subitem' => 'Packagings',
'additional' => '',
],
'features' => [
'datatables' => '1',
],
'breadcrumbItems' => [
['label' => 'Nutriflow Admin', 'url' => '#'],
['label' => 'Packaging Management']
],
])

@push('styles')
<style>
    .btn-hidden {
        display: none !important;
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
    table#dtRecordsView1 thead tr th.hide,table#dtRecordsView1 tbody tr td.hide{display: none !important;}
    #dtRecordsView1 {visibility: hidden;}
</style>
@endpush

@section('content')
<div class="container-fluid packagings my-4">
    <div class="">
        <div class="card-header d-flex justify-content-between">
            <h1 class="page-title">Packaging</h1>
            <input type="hidden" class="selectedCols" id="selectedCols">
           
            <div class="Export-btn">
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

                @if($user_role == 1)
                <div class="btn-group click-dropdown me-2">
                    <a href="{{ route('packaging.import') }}" class="btn btn-primary-orange plus-icon" title="Upload / Import data">
                        <span class="material-symbols-outlined">upload</span>
                    </a>
                </div>
                <div class="btn-group click-dropdown me-2">
                    <button type="button" class="btn btn-primary-orange plus-icon" title="Download Packaging">
                        <span class="material-symbols-outlined">download</span>
                    </button>
                    <ul class="dropdown-menu">
                        <li>
                            <a class="dropdown-item export-csv" href="javascript:void(0);" data-url="/download/csv/packaging">
                                Download as CSV
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item export-excel" href="javascript:void(0);" data-url="/download/excel/packaging">
                                Download as Excel
                            </a>
                        </li>
                    </ul>
                </div>
                @endif

                @if($user_role == 1 || $user_role == 2 || $user_role == 3)
                    <button type="button" class="btn btn-primary-orange plus-icon me-2" onclick="delete_selected_package()" title="Delete Packaging">
                        <span class="material-symbols-outlined">delete</span>
                    </button>
                @endif

                @if($user_role != 4)
                <button type="button" class="btn btn-primary-orange plus-icon me-2" id="addPackagingBtn" title="Add Packaging">
                    <span class="material-symbols-outlined">add</span>
                </button>
                @endif
            </div>
            
        </div>
        <div class="card-body">
             <!-- Loader -->
        <div id="tableSkeleton" class="skeleton-wrapper">
            @for($i=0;$i<6;$i++)
            <div class="skeleton-row"></div>
            @endfor
        </div>
            <table class="table responsiveness" id="dtRecordsView1" style="display:none;">
                <thead>
                    <tr>
                        <th class="text-primary-blue">
                            <div class="form-check-temp p-1">
                                <input class="form-check-input" type="checkbox" id="packageDefault">
                            </div>
                        </th>
                        <th class="text-primary-blue">Name</th>
                        <th class="text-primary-blue">SKU</th>
                        <th class="text-primary-blue text-end">Purchase Price</th>
                        <th class="text-primary-blue text-end">Purchase Units</th>
                        <th class="text-primary-blue text-end">Price per Unit</th>
                        <th class="text-primary-blue">Type</th>
                        <th class="text-primary-blue">Supplier</th>
                        <th class="text-primary-blue">Supplier SKU</th>
                        <th class="text-primary-blue ">Sales Channel</th>
                        <th class="text-primary-blue ">Environmental</th>
                        <th class="text-primary-blue ">Description</th>
                        <th class="text-primary-blue hide"></th>
                        <th class="text-primary-blue"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($packagings as $packaging)
                    @php $packaging = (object) $packaging; @endphp
                    <tr data-id="{{ $packaging->id }}">
                        <td class="text-primary-dark-mud">
                            <div class="form-check-temp p-1">
                                <input class="form-check-input package_check" data-package="{{$packaging->id}}" type="checkbox" id="package_{{$packaging->id}}">
                            </div>
                        </td> 
                        <td class="text-primary-dark-mud">{{ $packaging->name }}</td>    
                        <td class="text-primary-dark-mud">{{ $packaging->pack_sku }}</td>
                        <td class="text-primary-dark-mud text-end">{{ number_format($packaging->purchase_price, 2) }}</td>
                        <td class="text-primary-dark-mud text-end">{{ number_format($packaging->purchase_units,2) }}</td>
                        <td class="text-primary-dark-mud text-end">{{ number_format($packaging->price_per_unit, 2) }}</td>
                        <td class="text-primary-dark-mud">{{ $packaging->type }}</td>
                        <td class="text-primary-dark-mud">{{ $packaging->supplier ? $packaging->supplier->company_name : '-' }}</td>
                        <td class="text-primary-dark-mud">{{ $packaging->supplier_sku }}</td>
                        <td class="text-primary-dark-mud ">{{ $packaging->sales_channel }}</td>
                        <td class="text-primary-dark-mud ">{{ $packaging->environmental }}</td>
                        <td class="text-primary-dark-mud ">{{ $packaging->description }}</td>
                        <td class="text-primary-dark-mud hide">{{ $packaging->archive }}</td>
                        <td class="actions-menu-area">
                        <div class="">
                            <div class="dropdown d-flex justify-content-end">
                                @if($user_role != 4)
                                <button type="button" class="icon-primary-orange me-2" title="Favourite">
                                    <span class="material-symbols-outlined" onclick="make_favorite(this)" id="makefavorite_{{$packaging->id}}" data-favor="{{$packaging->favorite}}" data-url="{{route('packaging.favorite',['id'=>$packaging->id])}}">favorite</span>
                                </button>
                                <button class="icon-primary-orange me-0" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                                    <span class="material-symbols-outlined">more_vert</span>
                                </button>
                                <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                    <li>
                                        <span class="dropdown-item text-primary-dark-mud me-2 edit-row-data"
                                            data-id="{{ $packaging->id }}"
                                            data-name="{{ $packaging->name }}"
                                            data-pack-sku="{{ $packaging->pack_sku }}"
                                            data-description="{{ $packaging->description }}"
                                            data-purchase-price="{{ $packaging->purchase_price }}"
                                            data-purchase-units="{{ $packaging->purchase_units }}"
                                            data-price-per-units="{{ $packaging->price_per_unit }}"
                                            data-type="{{ $packaging->type }}"
                                            data-supplier-id="{{ $packaging->supplier_id }}"
                                            data-supplier-sku="{{ $packaging->supplier_sku }}"
                                            data-sales-channel="{{ $packaging->sales_channel }}"
                                            data-environmental="{{ $packaging->environmental }}">
                                            <span class="sidenav-normal ms-2 ps-1">Edit</span>
                                        </span>
                                    </li>
                                    <li>
                                        <span class="dropdown-item text-primary-dark-mud delete-row-data" data-archive="{{ $packaging->archive }}" data-id="{{ $packaging->id }}">
                                            <span class="sidenav-normal ms-2 ps-1">@if($packaging->archive == 1) Delete @else Archive @endif</span>
                                        </span>
                                    </li>
                                    @if($packaging->archive == 1)
                                    <li>
                                        <span class="dropdown-item text-primary-dark-mud unarchive-data" data-archive="{{ $packaging->archive }}" data-id="{{ $packaging->id }}">
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

    <!-- Packaging Modal -->
    <div class="modal fade" id="actionModal" tabindex="-1" role="dialog" aria-labelledby="actionModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title text-primary-orange" id="actionModalLabel">Add/Edit Packaging</h2>
                </div>
                <form id="packagingForm" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="packaging_id" id="packaging_id">

                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label class="text-primary-orange" for="name">Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" id="name" class="form-control">
                            </div>

                            <div class="col-md-6 form-group">
                                <label class="text-primary-orange" for="pack_sku">SKU <span class="text-danger">*</span></label>
                                <input type="text" name="pack_sku" id="pack_sku" class="form-control">
                            </div>

                            <div class="col-md-6 form-group">
                                <label class="text-primary-orange" for="purchase_price">Purchase Price<span class="text-danger">*</span></label>
                                <input type="number" name="purchase_price" id="purchase_price" class="form-control" step="0.01">
                            </div>

                            <div class="col-md-6 form-group">
                                <label class="text-primary-orange" for="purchase_units">Purchase Units<span class="text-danger">*</span></label>
                                <input type="number" name="purchase_units" id="purchase_units" class="form-control">
                            </div>

                            <div class="col-md-6 form-group">
                                <label class="text-primary-orange" for="price_per_unit">Price per Unit</label>
                                <input type="number" name="price_per_unit" id="price_per_unit" class="form-control" readonly>
                            </div>
                            
                            <div class="col-md-6 form-group d-flex flex-column">
                                <label class="text-primary-orange" for="type">Packaging Type <span class="text-danger">*</span></label>
                                <select name="type" id="type" class="form-control-select">
                                    <option value="Ind Unit">Ind Unit</option>
                                    <option value="Sell Unit">Sell Unit</option>
                                    <option value="Carton">Carton</option>
                                    <option value="Pallet">Pallet</option>
                                </select>
                            </div>

                            <div class="col-md-6 form-group d-flex flex-column">
                                <label class="text-primary-orange" for="supplier_id">Supplier <span class="text-danger">*</span></label>
                                <select name="supplier_id" id="supplier_id" class="form-control-select js-example-basic-single">
                                    <option value="" selected disabled>Select Supplier</option>
                                    @foreach($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}">{{ $supplier->company_name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6 form-group">
                                <label class="text-primary-orange" for="supplier_sku">Supplier SKU</label>
                                <input type="text" name="supplier_sku" id="supplier_sku" class="form-control">
                            </div>

                            <div class="col-md-6 form-group d-flex flex-column">
                                <label class="text-primary-orange" for="sales_channel">Sales Channel</label>
                                <select name="sales_channel" id="sales_channel" class="form-control-select">
                                    <option value="Retail">Retail</option>
                                    <option value="Major Wholesale">Major Wholesale</option>
                                    <option value="Ind Wholesale">Ind Wholesale</option>
                                    <option value="Food Services">Food Services</option>
                                </select>
                            </div>

                            <div class="col-md-6 form-group d-flex flex-column">
                                <label class="text-primary-orange" for="environmental">Environmental</label>
                                <select name="environmental" id="environmental" class="form-control-select">
                                    <option value="PET (Polyethylene Terephthalate)">PET (Polyethylene Terephthalate)</option>
                                    <option value="HDPE (High-Density Polyethylene)">HDPE (High-Density Polyethylene)</option>
                                    <option value="PVC (Polyvinyl Chloride)">PVC (Polyvinyl Chloride)</option>
                                    <option value="LDPE (Low-Density Polyethylene)">LDPE (Low-Density Polyethylene)</option>
                                    <option value="PP (Polypropylene)">PP (Polypropylene)</option>
                                    <option value="PS (Polystyrene)">PS (Polystyrene)</option>
                                    <option value="Other (Mixed Plastics)">Other (Mixed Plastics)</option>
                                    <option value="Biodegradable Plastics (PLA, PHA)">Biodegradable Plastics (PLA, PHA)</option>
                                    <option value="Flexible Plastic Pouches">Flexible Plastic Pouches</option>
                                    <option value="Cling Film (Stretch Wrap)">Cling Film (Stretch Wrap)</option>
                                    <option value="Corrugated Cardboard">Corrugated Cardboard</option>
                                    <option value="Cartonboard (Folding Cartons)">Cartonboard (Folding Cartons)</option>
                                    <option value="Paper Bags & Wrapping">Paper Bags & Wrapping</option>
                                    <option value="Wax-Coated Paper">Wax-Coated Paper</option>
                                    <option value="Moulded Pulp Packaging">Moulded Pulp Packaging</option>
                                    <option value="Aluminium Cans">Aluminium Cans</option>
                                    <option value="Tinplate Steel Cans">Tinplate Steel Cans</option>
                                    <option value="Aluminium Foil Packaging">Aluminium Foil Packaging</option>
                                    <option value="Metal Drums & Barrels">Metal Drums & Barrels</option>
                                    <option value="Clear Glass Bottles">Clear Glass Bottles</option>
                                    <option value="Coloured Glass (Green/Brown)">Coloured Glass (Green/Brown)</option>
                                    <option value="Glass Jars">Glass Jars</option>
                                    <option value="Tetra Pak (Liquid Cartons)">Tetra Pak (Liquid Cartons)</option>
                                    <option value="Foil Laminates">Foil Laminates</option>
                                    <option value="Paper-Based Blister Packs">Paper-Based Blister Packs</option>
                                    <option value="Cloth/Fabric Bags">Cloth/Fabric Bags</option>
                                    <option value="Jute & Hemp Packaging">Jute & Hemp Packaging</option>
                                    <option value="Wooden Crates & Boxes">Wooden Crates & Boxes</option>
                                    <option value="Bamboo Packaging">Bamboo Packaging</option>
                                    <option value="Sugarcane Bagasse">Sugarcane Bagasse</option>
                                    <option value="Edible Packaging">Edible Packaging</option>
                                    <option value="Cornstarch Packaging">Cornstarch Packaging</option>
                                    <option value="Mushroom-Based Packaging">Mushroom-Based Packaging</option>
                                    <option value="Aerogel Insulated Packaging">Aerogel Insulated Packaging</option>
                                    <option value="EVacuum Packaging">EVacuum Packaging</option>
                                    <option value="Bubble Wrap (Plastic or Paper-Based)">Bubble Wrap (Plastic or Paper-Based)</option>
                                    <option value="Foam Packaging (EPE, EPS)">Foam Packaging (EPE, EPS)</option>
                                    <option value="Metalized Plastic Films">Metalized Plastic Films</option>
                                    <option value="Tyvek (Synthetic Paper)">Tyvek (Synthetic Paper)</option>
                                    <option value="Plant-Based Film Wraps">Plant-Based Film Wraps</option>
                                </select>
                            </div>
                            <div class="col-md-12 form-group">
                                <label class="text-primary-orange" for="description">Description</label>
                                <textarea name="description" id="description" class="form-control" rows="3"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary-white" data-dismiss="modal" id="closeActionModal">Close</button>
                        <button type="submit" class="btn btn-secondary-blue" id="savePackagingBtn">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let isFormChanged = false;
    let ignorePopState = false;
    $(document).ready(function() {

        var tableArray = @json($packagings);
        var user_role = "{{$user_role}}"
        const table = $('#dtRecordsView1').DataTable({
            "order": [],
            responsive: true,
            autoWidth: false,
            deferRender:true,
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
                    }
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
                //         delete_selected_package();
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
                    targets: [6,7,8,9,10,11], // Specify columns that should be hidden initially
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
                $('#customFilter').css('height', '38px');

                var table = $('#dtRecordsView1').dataTable().api();
                table.columns(12).search(0, true, false).draw(); 
                $('#dtRecordsView1').css('visibility', 'visible');
                if (tableArray.length < 7) {
                    $('.table-scroll').removeClass('table-scroll');
                }
            }
        });


        $('#packagingForm').on('change input', 'input, select, textarea', function () {
            isFormChanged = true;
        });

        $('#packagingForm').on('submit', function () {
            isFormChanged = false;
        });

        window.addEventListener("beforeunload", function (e) {
            if (isFormChanged) {
                e.preventDefault();
                e.returnValue = ''; // Required for Chrome
            }
        });

        window.history.pushState(null, null, window.location.href);
        window.addEventListener('popstate', function (event) {
            if (ignorePopState) return;
            if (isFormChanged) {
                // Push back state again to stop browser back
                window.history.pushState(null, null, window.location.href);
                Swal.fire({
                    title: 'Are you sure you want to exit?',
                    text: "You have unsaved changes. What would you like to do?",
                    icon: 'warning',
                    showCancelButton: true,
                    showDenyButton: true,
                    confirmButtonText: 'Save and Exit',
                    denyButtonText: 'Discard Changes and Exit',
                    cancelButtonText: 'Continue Editing',
                    reverseButtons: true,
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    allowEnterKey: false
                }).then((result) => {
                    if (result.isConfirmed) {  
                        ignorePopState = true;
                        $('#saveLabourBtn').click();
                        setTimeout(() => {
                            window.location.href = "{{route('packaging.index')}}";    
                        }, 1000);
                    } else if (result.isDenied) {
                        isFormChanged = false;
                        ignorePopState = true;
                        window.location.href = "{{route('packaging.index')}}";    
                    } else {
                        // Stay on the page
                    }
                });
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
        let initiallyCheckedColumns = [1,2,3,4,5]; // Define which columns should be checked by default   
        dt.columns().every(function(idx) {
            let column = this;
            let columnTitle = column.header().textContent;
            if (columnTitle !== "") {
                if(idx > 0){
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


    $(document).ready(function() {
        $('#addPackagingBtn').on('click', function() {
            $('#packagingForm')[0].reset();
            $('#packaging_id').val('');
            $('#actionModalLabel').text('Add Packaging');
            $('#savePackagingBtn').text('Create');
            $('#actionModal').modal('show');

            $('.js-example-basic-single').select2({
                width: '100%',
                dropdownParent: $('#actionModal')
            });
        });

        $(document).on('click', '.edit-row-data', function() {
            $('#packaging_id').val($(this).data('id'));
            $('#pack_sku').val($(this).data('pack-sku'));
            $('#supplier_id').val($(this).data('supplier-id'));
            $('#name').val($(this).data('name'));
            $('#description').val($(this).data('description'));
            $('#purchase_price').val($(this).data('purchase-price'));
            $('#purchase_units').val($(this).data('purchase-units'));
            $('#price_per_unit').val($(this).data('price-per-units'));
            $('#supplier_sku').val($(this).data('supplier-sku'));
            // Function to set selected option based on case-insensitive comparison
            function setSelectedOption(selectId, dataValue) {
                $('#' + selectId + ' option').each(function() {
                    // Compare both values in lowercase to ensure case-insensitivity
                    if ($(this).val().toLowerCase() === dataValue.toLowerCase()) {
                        $(this).prop('selected', true);
                    }
                });
            }
            // For each select field, use the setSelectedOption function
            setSelectedOption('type', $(this).data('type'));
            setSelectedOption('sales-channel', $(this).data('sales_channel'));
            setSelectedOption('environmental', $(this).data('environmental'));
            $('#actionModalLabel').text('Edit Packaging');
            $('#savePackagingBtn').text('Update');
            $('#actionModal').modal('show');
            $('.js-example-basic-single').select2({
                width: '100%',
                dropdownParent: $('#actionModal')
            });
        });

        $('#packagingForm').on('submit', function(e) {
            e.preventDefault();
            const $form = $(this);
            const $submitButton = $('#savePackagingBtn');
            $submitButton.prop('disabled', true);
            const packagingId = $('#packaging_id').val();
            const url = packagingId ?
                "{{ route('packaging.update', ':id') }}".replace(':id', packagingId) :
                "{{ route('packaging.store') }}";
            const method = packagingId ? 'POST' : 'POST';
            const formData = new FormData(this);
            if (packagingId) {
                formData.append('_method', 'PUT');
            }
            $.ajax({
                url: url,
                method: method,
                data: formData,
                processData: false,
                contentType: false,
                complete: function() {
                    $submitButton.prop('disabled', false);
                },
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
                    }
                },
                error: function(xhr) {
                    if (xhr.status === 422) {
                        const errors = xhr.responseJSON.errors;
                        let errorList = '';
                        $.each(errors, function(key, value) {
                            $.each(value, function(index, message) {
                                errorList += `<div>${message}</div>`;
                            });
                        });
                        Swal.fire({
                            title: 'Validation Error',
                            html: `${errorList}`,
                            icon: 'warning',
                            confirmButtonText: 'OK',
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

        $(document).on('click', '.delete-row-data', function() {
            const id = $(this).data('id');
            const archive = $(this).data('archive');
            const url = "{{ route('packaging.destroy', ':id') }}".replace(':id', id);
            Swal.fire({
                title: 'Are you sure?',
                text: (archive == 0) ? 'You want to move this record to archive status.': 'You won\'t be able to revert this!',
                icon: 'warning',
                showCancelButton: true,
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
    });

    $(document).ready(function() {
        let typingTimer;
        const doneTypingInterval = 1000;
        $('#name').on('keyup change', function() {
            clearTimeout(typingTimer);
            const packName = $(this).val();
            const packagingId = $('#packaging_id').val();
            if (packName && !packagingId) {
                typingTimer = setTimeout(function() {
                    generatePackSKU(packName, packagingId);
                }, doneTypingInterval);
            }
        });
    });

    function generatePackSKU(packName, packagingId) {
        $.ajax({
            url: '{{ route("packaging.generate_sku") }}',
            type: 'POST',
            data: {
                name: packName,
                id: packagingId,
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                $('#pack_sku').val(response.sku);
            },
            error: function(xhr) {
                console.error('Error generating SKU:', xhr.responseText);
            }
        });
    }

    $(document).on('focusout','#purchase_price,#purchase_units',function(){
        let pur_price = $('#purchase_price').val()
        let pur_unit = $('#purchase_units').val()
        let temp = pur_price/ pur_unit
        $('#price_per_unit').val(temp.toFixed(2))
    })

    $(document).on('click','#packageDefault',function() {
        let selectvalue = $('#packageDefault').is(':checked')
        $('#dtRecordsView1 tbody tr').each(function() {
            $(this).find('td:eq(0) input').prop('checked',selectvalue)
        });
    });

    function delete_selected_package(){
        let packageobj = [];
        $("table#dtRecordsView1 tbody tr").each(function () {
            if($(this).find('td:eq(0) input').prop('checked') == true){
                let id = $(this).find('td:eq(0) input').data('package')
                packageobj.push(id)
            }
        });
        if(packageobj.length == 0){
            Swal.fire({
                icon: 'warning',
                title: 'Warning!',
                text: 'No Package select'
            });
        }else{
            let archiveVal = $('#customFilter').val()
            let html,title,confirmBtn,inputText
            if(archiveVal == "1"){
                title = 'Delete Packages'
                confirmBtn = 'Delete'
                inputText = 'delete'
                html = `<p>The <strong>${packageobj.length}</strong> selected items will be permanently deleted and cannot be retrieved. <strong>Are you sure you want to delete them?</strong></p>
                        <p>To confirm, enter the phrase <strong>delete</strong>.</p>
                        <input id="confirmInput" class="swal2-input" placeholder="Type delete here">`
            }else{
                title = 'Archive Packages'
                confirmBtn = 'Archive'
                inputText = 'archive'
                html = `<p>The <strong>${packageobj.length}</strong> selected items will be archived. <strong>Are you sure you want to archive them?</strong></p>
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
                    var package = JSON.stringify(packageobj);
                    let data = {'archive':archiveVal,'packageobj':package,'_token':$('meta[name="csrf-token"]').attr('content')}
                    $.ajax({
                        type: "POST",
                        url: "{{route('packaging.delete')}}",
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
            let data = {'_token':$('meta[name="csrf-token"]').attr('content'),'selectedCols':selectedCols,'model':'packaging'};	
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
			table.columns(12).search(search_val, true, false).draw();
		}else{ 
			table.columns().search('').draw(); 
		} 
    })

    $(document).on('click', '.unarchive-data', function() {
        const archive = $(this).data('archive');
        const id = $(this).data('id');
        const url = "{{ route('packaging.unarchive', ':id') }}".replace(':id', id);
        Swal.fire({
            title: 'Are you sure?',
            text: 'You want to Unarchive this record',
            icon: 'warning',
            showCancelButton: true,
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


</script>
@endpush