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
</style>
@endpush

@section('content')
<div class="container-fluid packagings my-4">
    <div class="">
        <div class="card-header d-flex justify-content-between">
            <h1 class="page-title">Packaging</h1>
            <div class="Export-btn">
                <div class="btn-group click-dropdown me-4">
                    <button type="button" class="btn btn-primary-orange plus-icon" title="Download Packaging">
                        <span class="material-symbols-outlined">download</span>
                    </button>
                    <ul class="dropdown-menu">
                        <li>
                            <a class="dropdown-item export-csv" href="javascript:void(0);">
                                Download as CSV
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item export-excel" href="javascript:void(0);">
                                Download as Excel
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="btn-group click-dropdown me-4">
                    <button type="button" class="btn btn-primary-orange plus-icon" title="Uplad / Import data">
                        <span class="material-symbols-outlined">upload</span>
                    </button>
                    <ul class="dropdown-menu">
                        <li>
                            <a class="dropdown-item" href="{{ route('packaging.import') }}">Upload / Import Data</a>
                        </li>
                    </ul>
                </div>
                <button type="button" class="btn btn-primary-orange plus-icon" id="addPackagingBtn" title="Add Packaging">
                    <span class="material-symbols-outlined">add</span>
                </button>
            </div>

        </div>
        <div class="card-body">
            <table class="table responsiveness" id="dtRecordsView1">
                <thead>
                    <tr>
                        <th class="text-primary-blue">Pack SKU</th>
                        <th class="text-primary-blue">Name</th>
                        <th class="text-primary-blue">Type</th>
                        <th class="text-primary-blue">Channel</th>
                        <th class="text-primary-blue">Stage</th>
                        <th class="text-primary-blue text-end">Price per Order</th>
                        <th class="text-primary-blue text-end">Units per Order</th>
                        <th class="text-primary-blue">Status</th>
                        <th class="text-primary-blue">Supplier</th>
                        <th class="text-primary-blue hide">Unit Type</th>
                        <th class="text-primary-blue"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($packagings as $packaging)
                    @php $packaging = (object) $packaging; @endphp
                    <tr data-id="{{ $packaging->id }}">
                        <td class="text-primary-dark-mud">{{ $packaging->pack_sku }}</td>
                        <td class="text-primary-dark-mud">{{ $packaging->name }}</td>
                        <td class="text-primary-dark-mud">{{ $packaging->type }}</td>
                        <td class="text-primary-dark-mud">{{ $packaging->channel }}</td>
                        <td class="text-primary-dark-mud">{{ $packaging->stage }}</td>
                        <td class="text-primary-dark-mud text-end">${{ number_format($packaging->price_per_order, 2) }}</td>
                        <td class="text-primary-dark-mud text-end">{{ $packaging->units_per_order }}</td>
                        <td class="text-primary-dark-mud">{{ $packaging->status }}</td>
                        <td class="text-primary-dark-mud">{{ $packaging->supplier ? $packaging->supplier->company_name : '-' }}</td>
                        <td class="text-primary-dark-mud hide">{{ $packaging->unit_type}}</td>
                        <td class="actions-menu-area">
                            <div class="">
                                <div class="dropdown d-flex justify-content-end">
                                    <button class="icon-primary-orange me-0" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                                        <span class="material-symbols-outlined">more_vert</span>
                                    </button>
                                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                        <li>
                                            <span class="dropdown-item text-primary-dark-mud me-2 edit-row-data"
                                                data-id="{{ $packaging->id }}"
                                                data-pack-sku="{{ $packaging->pack_sku }}"
                                                data-status="{{ $packaging->status }}"
                                                data-supplier-id="{{ $packaging->supplier_id }}"
                                                data-type="{{ $packaging->type }}"
                                                data-channel="{{ $packaging->channel }}"
                                                data-stage="{{ $packaging->stage }}"
                                                data-name="{{ $packaging->name }}"
                                                data-description="{{ $packaging->description }}"
                                                data-price-per-order="{{ $packaging->price_per_order }}"
                                                data-units-per-order="{{ $packaging->units_per_order }}"
                                                data-unit-type="{{ $packaging->unit_type }}"
                                                data-sell-units-per-carton="{{ $packaging->sell_units_per_carton }}"
                                                data-machinery-usage="{{ $packaging->machinery_usage }}"
                                                data-disposal="{{ $packaging->disposal }}"
                                                data-cost-per-packing-unit="{{ $packaging->cost_per_packing_unit }}"
                                                data-cost-per-sell-unit="{{ $packaging->cost_per_sell_unit }}">
                                                <span class="sidenav-normal ms-2 ps-1">Edit</span>
                                            </span>
                                        </li>
                                        <li>
                                            <span class="dropdown-item text-primary-dark-mud delete-row-data" data-id="{{ $packaging->id }}">
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

    <!-- Packaging Modal -->
    <div class="modal fade" id="actionModal" tabindex="-1" role="dialog" aria-labelledby="actionModalLabel" aria-hidden="true">
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
                                <label class="text-primary-orange" for="pack_sku">Pack SKU <span class="text-danger">*</span></label>
                                <input type="text" name="pack_sku" id="pack_sku" class="form-control">
                            </div>

                            <div class="col-md-6 form-group d-flex flex-column">
                                <label class="text-primary-orange" for="status">Status <span class="text-danger">*</span></label>
                                <select name="status" id="status" class="form-control-select">
                                    <option value="Not started">Not started</option>
                                    <option value="Complete">Complete</option>
                                    <option value="Needs checking">Needs checking</option>
                                    <option value="Awaiting supplier">Awaiting supplier</option>
                                    <option value="Request supplier">Request supplier</option>
                                    <option value="Out of date">Out of date</option>
                                    <option value="Inactive">Inactive</option>
                                </select>
                            </div>

                            <div class="col-md-6 form-group d-flex flex-column">
                                <label class="text-primary-orange" for="supplier_id">Supplier</label>
                                <select name="supplier_id" id="supplier_id" class="form-control-select">
                                    <option value="">Select Supplier</option>
                                    @foreach($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}">{{ $supplier->company_name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6 form-group d-flex flex-column">
                                <label class="text-primary-orange" for="type">Packaging Type <span class="text-danger">*</span></label>
                                <select name="type" id="type" class="form-control-select">
                                    <option value="Bag">Bag</option>
                                    <option value="Cake BakeInBox">Cake BakeInBox</option>
                                    <option value="Retail Sell Box">Retail Sell Box</option>
                                    <option value="Cake Insert">Cake Insert</option>
                                    <option value="Pudd foil">Pudd foil</option>
                                    <option value="Cake Foil">Cake Foil</option>
                                    <option value="Carton">Carton</option>
                                    <option value="Flowrap">Flowrap</option>
                                    <option value="Tray">Tray</option>
                                    <option value="Jar">Jar</option>
                                    <option value="Label">Label</option>
                                    <option value="Mould">Mould</option>
                                    <option value="Silicon - Baking Paper">Silicon - Baking Paper</option>
                                    <option value="PET z_container">PET z_container</option>
                                    <option value="Tin">Tin</option>
                                    <option value="Lid">Lid</option>
                                    <option value="Store Ready Tray">Store Ready Tray</option>
                                    <option value="Disposable">Disposable</option>
                                    <option value="PET Tamperproof">PET Tamperproof</option>
                                    <option value="Hamper">Hamper</option>
                                </select>
                            </div>

                            <div class="col-md-6 form-group d-flex flex-column">
                                <label class="text-primary-orange" for="channel">Channel <span class="text-danger">*</span></label>
                                <select name="channel" id="channel" class="form-control-select">
                                    <option value="Retail (grocery)">Retail (grocery)</option>
                                    <option value="Wholesale (cafes)">Wholesale (cafes)</option>
                                    <option value="D2M - Both (shopify)">D2M - Both (shopify)</option>
                                    <option value="D2M - CSB (shopify)">D2M - CSB (shopify)</option>
                                    <option value="D2M - CSH (shopify)">D2M - CSH (shopify)</option>
                                    <option value="Retail & Wholesale">Retail & Wholesale</option>
                                    <option value="All">All</option>
                                </select>
                            </div>

                            <div class="col-md-6 form-group d-flex flex-column">
                                <label class="text-primary-orange" for="stage">Stage <span class="text-danger">*</span></label>
                                <select name="stage" id="stage" class="form-control-select">
                                    <option value="Bac - Disposable">Bac - Disposable</option>
                                    <option value="Bac - Final">Bac - Final</option>
                                    <option value="Pac - flow wrap">Pac - flow wrap</option>
                                    <option value="Pac - boxes">Pac - boxes</option>
                                    <option value="Pac - trays/moulds">Pac - trays/moulds</option>
                                    <option value="Pac - carton">Pac - carton</option>
                                    <option value="Pac - jar">Pac - jar</option>
                                    <option value="Pac - Individual">Pac - Individual</option>
                                    <option value="Pac - retail">Pac - retail</option>
                                    <option value="Who- cafe">Who- cafe</option>
                                    <option value="Who - cartons">Who - cartons</option>
                                    <option value="Lab - product">Lab - product</option>
                                    <option value="Lab - logistics">Lab - logistics</option>
                                    <option value="Pal - wrapping">Pal - wrapping</option>
                                    <option value="Pal - insulation">Pal - insulation</option>
                                    <option value="Pal - sealing">Pal - sealing</option>
                                    <option value="Pac - other">Pac - other</option>
                                </select>
                            </div>

                            <div class="col-md-12 form-group">
                                <label class="text-primary-orange" for="description">Description</label>
                                <textarea name="description" id="description" class="form-control" rows="3"></textarea>
                            </div>

                            <div class="col-md-6 form-group">
                                <label class="text-primary-orange" for="price_per_order">Price per Order ($) <span class="text-danger">*</span></label>
                                <input type="number" name="price_per_order" id="price_per_order" class="form-control" step="0.01">
                            </div>

                            <div class="col-md-6 form-group">
                                <label class="text-primary-orange" for="units_per_order">Units per Order <span class="text-danger">*</span></label>
                                <input type="number" name="units_per_order" id="units_per_order" class="form-control">
                            </div>

                            <div class="col-md-6 form-group d-flex flex-column">
                                <label class="text-primary-orange" for="unit_type">Unit Type <span class="text-danger">*</span></label>
                                <select name="unit_type" id="unit_type" class="form-control-select">
                                    <option value="Ind Unit">Ind Unit</option>
                                    <option value="Sell Unit">Sell Unit</option>
                                    <option value="Carton">Carton</option>
                                    <option value="Pallet">Pallet</option>
                                </select>
                            </div>

                            <div class="col-md-6 form-group">
                                <label class="text-primary-orange" for="sell_units_per_carton">Sell Units per Carton</label>
                                <input type="number" name="sell_units_per_carton" id="sell_units_per_carton" class="form-control">
                            </div>

                            <div class="col-md-6 form-group d-flex flex-column">
                                <label class="text-primary-orange" for="machinery_usage">Packing Usage</label>
                                <select name="machinery_usage" id="machinery_usage" class="form-control-select">
                                    <option value="">Select Packing Usage</option>
                                    <option value="Flowrap usage">Flowrap usage</option>
                                    <option value="Oven usage">Oven usage</option>
                                    <option value="Packing room usage">Packing room usage</option>
                                    <option value="Ultrasonic">Ultrasonic</option>
                                </select>
                            </div>

                            <div class="col-md-6 form-group d-flex flex-column">
                                <label class="text-primary-orange" for="disposal">Disposal/Recycling</label>
                                <select name="disposal" id="disposal" class="form-control-select">
                                    <option value="">Select Disposal Type</option>
                                    <option value="1 - PET (Polyethylene Terephthalate)">1 - PET (Polyethylene Terephthalate)</option>
                                    <option value="2 - HDPE (High-Density Polyethylene)">2 - HDPE (High-Density Polyethylene)</option>
                                    <option value="3 - PVC (Polyvinyl Chloride)">3 - PVC (Polyvinyl Chloride)</option>
                                    <option value="4 - LDPE (Low-Density Polyethylene)">4 - LDPE (Low-Density Polyethylene)</option>
                                    <option value="Recycle -cardboard">Recycle -cardboard</option>
                                    <option value="5 - PP (Polypropylene)">5 - PP (Polypropylene)</option>
                                    <option value="6 - PS (Polystyrene)">6 - PS (Polystyrene)</option>
                                    <option value="7 - Other">7 - Other</option>
                                    <option value="Compostable">Compostable</option>
                                    <option value="Recycle Glass">Recycle Glass</option>
                                </select>
                            </div>

                            <div class="col-md-6 form-group">
                                <label class="text-primary-orange" for="cost_per_packing_unit">Cost per Packing Unit ($)</label>
                                <input type="number" name="cost_per_packing_unit" id="cost_per_packing_unit" class="form-control" step="0.0001">
                            </div>

                            <div class="col-md-6 form-group">
                                <label class="text-primary-orange" for="cost_per_sell_unit">Cost per Sell Unit ($)</label>
                                <input type="number" name="cost_per_sell_unit" id="cost_per_sell_unit" class="form-control" step="0.0001">
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
    $(document).ready(function() {
        const table = $('#dtRecordsView1').DataTable({
            responsive: true,
            dom: "<'row mb-4'<'col-md-6 col-sm-6'fB><'col-md-6 col-sm-6'l>>" +
                "<'row'<'col-sm-12'tr>>" +
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
                            return idx < totalColumns - 1; 
                        }
                    },
                    title: ""
                },
                {
                    extend: 'colvis',
                    columns: ':not(:last, :first)',
                    text: 'Show/Hide Columns',
                    action: function (e, dt, button, config) {
                        // Override default action to prevent default dropdown
                        if ($('.colvis-dropdown').length === 0) {
                            createColVisDropdown(dt);
                        }
                    }
                }
            ],

            columnDefs: [{
                targets: -1,
                className: 'noVis always-visible',
                orderable: false
            }],

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
                // Move the search box to the left and entries dropdown to the right
                const tableWrapper = $(this).closest('.dataTables_wrapper');
                const searchBox = tableWrapper.find('.dataTables_filter');
                const lengthDropdown = tableWrapper.find('.dataTables_length');

                searchBox.css({
                    'float': 'left',
                    'margin-top': '0',
                    'margin-right': '20px'
                });
                lengthDropdown.css('float', 'right');
            }
        });

        // Export button handlers for dropdown
        $('.export-csv').on('click', function() {
            table.button('.buttons-csv').trigger();
        });

        $('.export-excel').on('click', function() {
            table.button('.buttons-excel').trigger();
        });
    });


    function createColVisDropdown(dt) {
        let dropdownHtml = '<div class="colvis-dropdown">';        
        dt.columns().every(function (idx) {
            let column = this;
            let columnTitle = column.header().textContent;
            if(columnTitle != "" || columnTitle != ''){
                dropdownHtml += `<label class="colvis-item">
                                <span>${columnTitle}</span>
                                <input type="checkbox" class="colvis-checkbox form-check-input" data-column="${idx}" 
                                    ${column.visible() ? 'checked' : ''} ${(idx == 8 )? '':'disabled'}>
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
    }


    $(document).ready(function() {
        $('#addPackagingBtn').on('click', function() {
            $('#packagingForm')[0].reset();
            $('#packaging_id').val('');
            $('#actionModalLabel').text('Add Packaging');
            $('#savePackagingBtn').text('Create');
            $('#actionModal').modal('show');
        });

        $(document).on('click', '.edit-row-data', function() {
            $('#packaging_id').val($(this).data('id'));
            $('#pack_sku').val($(this).data('pack-sku'));
            //  $('#status').val($(this).data('status'));
            $('#supplier_id').val($(this).data('supplier-id'));
            //   $('#type').val($(this).data('type'));
            //     $('#channel').val($(this).data('channel'));
            //    $('#stage').val($(this).data('stage'));
            $('#name').val($(this).data('name'));
            $('#description').val($(this).data('description'));
            $('#price_per_order').val($(this).data('price-per-order'));
            $('#units_per_order').val($(this).data('units-per-order'));
            //  $('#unit_type').val($(this).data('unit-type'));
            $('#sell_units_per_carton').val($(this).data('sell-units-per-carton'));
            //     $('#machinery_usage').val($(this).data('machinery-usage'));
            //    $('#disposal').val($(this).data('disposal'));
            $('#cost_per_packing_unit').val($(this).data('cost-per-packing-unit'));
            $('#cost_per_sell_unit').val($(this).data('cost-per-sell-unit'));

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
            setSelectedOption('status', $(this).data('status'));
            setSelectedOption('type', $(this).data('type'));
            setSelectedOption('channel', $(this).data('channel'));
            setSelectedOption('stage', $(this).data('stage'));
            setSelectedOption('unit_type', $(this).data('unit-type'));
            setSelectedOption('machinery_usage', $(this).data('machinery-usage'));
            setSelectedOption('disposal', $(this).data('disposal'));

            $('#actionModalLabel').text('Edit Packaging');
            $('#savePackagingBtn').text('Update');
            $('#actionModal').modal('show');
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
            const url = "{{ route('packaging.destroy', ':id') }}".replace(':id', id);
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
</script>
@endpush