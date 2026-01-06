@extends('backend.master', [
'pageTitle' => 'Freight Management',
'activeMenu' => [
'item' => 'Freight',
'subitem' => 'Freights',
'additional' => '',
],
'features' => [
'datatables' => '1',
],
'breadcrumbItems' => [
['label' => 'Nutriflow Admin', 'url' => '#'],
['label' => 'Freight Management']
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
<div class="container-fluid labours my-4">
    <div class="">
        <div class="card-header d-flex justify-content-between">
            <h1 class="page-title">Freight</h1>
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
                    <a href="{{ route('freight.import') }}" class="btn btn-primary-orange plus-icon" title="Upload / Import data">
                        <span class="material-symbols-outlined">upload</span>
                    </a>
                </div>
                <div class="btn-group click-dropdown me-2">
                    <button type="button" class="btn btn-primary-orange plus-icon" title="Download Freight">
                        <span class="material-symbols-outlined">download</span>
                    </button>
                    <ul class="dropdown-menu">
                        <li>
                            <a class="dropdown-item export-csv" href="javascript:void(0);" data-url="/download/csv/freight">
                                Download as CSV
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item export-excel" href="javascript:void(0);" data-url="/download/excel/freight">
                                Download as Excel
                            </a>
                        </li>
                    </ul>
                </div>
                @endif
                @if($user_role == 1 || $user_role == 2 || $user_role == 3)
                    <button type="button" class="btn btn-primary-orange plus-icon me-2" onclick="delete_selected_freight()" title="Delete Freight">
                        <span class="material-symbols-outlined">delete</span>
                    </button>
                @endif
                <input type="hidden" name="freight_number" id="freight_number" value="">
                @if($user_role != 4)
                <button type="button" class="btn btn-primary-orange plus-icon me-2" id="addLabourBtn" title="Add Freight">
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
                                <input class="form-check-input" type="checkbox" id="freightDefault">
                            </div>
                        </th>
                        <th class="text-primary-blue">Name</th>
                        <th class="text-primary-blue">Description</th>
                        <th class="text-primary-blue">Freight ID</th>
                        <th class="text-primary-blue">Supplier</th>
                        <th class="text-primary-blue text-end">Price <br>($)</th>
                        <th class="text-primary-blue">Unit</th>
                        <th class="text-primary-blue text-end">Parcel Weight <br>(g)</th>
                        <th class="text-primary-blue hide"></th>
                        <th class="text-primary-blue"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($freights as $freight)
                        @php $freight = (object) $freight; @endphp
                        <tr data-id="{{ $freight->id }}">
                            <td class="text-primary-dark-mud">
                                <div class="form-check-temp p-1">
                                    <input class="form-check-input freight_check" data-freight="{{$freight->id}}" type="checkbox" id="freight_{{$freight->id}}">
                                </div>
                            </td> 
                            <td class="text-primary-dark-mud">{{ $freight->name }}</td>
                            <td class="text-primary-dark-mud">{{ ucfirst($freight->description) }}</td>
                            <td class="text-primary-dark-mud">{{ $freight->freight_id }}</td>
                            <td class="text-primary-dark-mud">{{ $freight->supplier ? $freight->supplier['company_name'] : "" }}</td>
                            <td class="text-primary-dark-mud text-end">{{ $freight->freight_price }}</td>                          
                            <td class="text-primary-dark-mud text-end">{{ $freight->freight_unit }}</td>                          
                            <td class="text-primary-dark-mud text-end">{{ $freight->parcel_weight ?? '-' }}</td> 
                            <td class="text-primary-dark-mud hide">{{ $freight->archive }}</td>                         
                            <td class="actions-menu-area">
                                <div class="">
                                    <!-- 3-Dot Icon Menu for Grid View -->
                                    <div class="dropdown d-flex justify-content-end">
                                        @if($user_role != 4)
                                        <button type="button" class="icon-primary-orange me-2" title="Favourite">
                                            <span class="material-symbols-outlined" onclick="make_favorite(this)" id="makefavorite_{{$freight->id}}" data-favor="{{$freight->favorite}}" data-url="{{route('freight.favorite',['id'=>$freight->id])}}">favorite</span>
                                        </button>
                                        <button class="icon-primary-orange me-0" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                                            <span class="material-symbols-outlined">more_vert</span>
                                        </button>
                                        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                            <li>
                                                <span class="dropdown-item text-primary-dark-mud me-2 edit-row-data"
                                                    data-id="{{ $freight->id }}"
                                                    data-name="{{ $freight->name }}"
                                                    data-description="{{ $freight->description }}"
                                                    data-freight-id="{{ $freight->freight_id }}"
                                                    data-freight-supplier="{{ $freight->freight_supplier }}"
                                                    data-freight-price="{{ $freight->freight_price }}"
                                                    data-freight-unit="{{ $freight->freight_unit }}"
                                                    data-parcel-weight="{{ $freight->parcel_weight }}">
                                                    <span class="sidenav-normal ms-2 ps-1">Edit</span>
                                                </span>
                                            </li>
                                            <li>
                                                <span class="dropdown-item text-primary-dark-mud delete-row-data" data-archive="{{ $freight->archive }}" data-id="{{ $freight->id }}">
                                                    <span class="sidenav-normal ms-2 ps-1">@if($freight->archive == 1) Delete @else Archive @endif</span>
                                                </span>
                                            </li>
                                            @if($freight->archive == 1)
                                            <li>
                                                <span class="dropdown-item text-primary-dark-mud unarchive-data" data-archive="{{ $freight->archive }}" data-id="{{ $freight->id }}">
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

    <!-- Labour Modal -->
    <div class="modal fade" id="actionModal" tabindex="-1" role="dialog" aria-labelledby="actionModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title text-primary-orange" id="actionModalLabel">Add/Edit Freight</h2>
                </div>
                <form id="labourForm" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="labour_id" id="labour_id">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 form-group d-flex flex-column">
                                <label class="text-primary-orange" for="name">Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" id="name" class="form-control">
                            </div>

                            <div class="col-md-6 form-group d-flex flex-column">
                                <label class="text-primary-orange" for="freight_id">Freight ID</label>
                                <input type="text" name="freight_id" id="freight_id" class="form-control">
                            </div>

                            <div class="col-md-6 form-group d-flex flex-column">
                                <label class="text-primary-orange" for="freight_supplier">Freight Supplier</label>
                                <select name="freight_supplier" id="freight_supplier" class="form-control-select js-example-basic-single">
                                    <option selected disabled>Select Supplier</option>
                                    @foreach($suppliers as $supplier)
                                    <option value="{{$supplier->id}}">{{$supplier->company_name}}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6 form-group">
                                <label class="text-primary-orange" for="freight_price">Freight Price ($) <span class="text-danger">*</span></label>
                                <input type="number" name="freight_price" id="freight_price" class="form-control" step="0.01">
                            </div>

                            <div class="col-md-6 form-group d-flex flex-column">
                                <label class="text-primary-orange" for="freight_unit">Freight Unit <span class="text-danger">*</span></label>
                                <select name="freight_unit" id="freight_unit" class="form-control-select" >
                                    <option value="Ind Unit">Ind Unit</option>
                                    <option value="Sell Unit">Sell Unit</option>
                                    <option value="Carton">Carton</option>
                                    <option value="Pallet">Pallet</option>
                                    <option value="Parcel">Parcel</option>
                                    <option value="Kg">Kg</option>
                                </select>
                            </div>

                            <div class="col-md-6 form-group">
                                <label class="text-primary-orange" for="parcel_weight">Parcel Weight (g)</label>
                                <input type="number" name="parcel_weight" id="parcel_weight" class="form-control" step="0.01" disabled>
                            </div>

                            <div class="col-md-12 form-group">
                                <label class="text-primary-orange" for="description">Description</label>
                                <textarea name="description" id="description" class="form-control" rows="3"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary-white" data-dismiss="modal" id="closeActionModal">Close</button>
                        <button type="submit" class="btn btn-secondary-blue" id="saveLabourBtn">Save</button>
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
        var tableArray = @json($freights);
        var user_role = "{{$user_role}}"
        const table = $('#dtRecordsView1').DataTable({
            "order": [],
            responsive: true,
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
                }
            ],

            columnDefs: [
                {
                    targets: -1,
                    className: 'noVis always-visible',
                    orderable: false
                },
                {
                    targets: [6,7], // Specify columns that should be hidden initially
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
                table.columns(8).search(0, true, false).draw(); 
                $('#dtRecordsView1').css('visibility', 'visible');
                if (tableArray.length < 7) {
                    $('.table-scroll').removeClass('table-scroll');
                }
            }
        });


        $('#labourForm').on('change input', 'input, select, textarea', function () {
            isFormChanged = true;
        });

        $('#labourForm').on('submit', function () {
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
                            window.location.href = "{{route('freight.index')}}";    
                        }, 1000);
                    } else if (result.isDenied) {
                        isFormChanged = false;
                        ignorePopState = true;
                        window.location.href = "{{route('freight.index')}}";    
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


    $(document).on('change','#freight_unit',function(){
        let value = $(this).val()
        if(value == "Parcel" || value == "Kg"){
            if(value == "Kg"){
                $('#parcel_weight').val(1000);
            }else{
                $('#parcel_weight').val(0)
            }
            $('#parcel_weight').prop('disabled',false)
        }else{
            $('#parcel_weight').prop('disabled',true)
            $('#parcel_weight').val(0)
        }
    })


    // Add Labour Modal Handling
    $(document).on('click','#addLabourBtn', function() {
        // Reset the form
        $('#labourForm')[0].reset();
        $('#labour_id').val($('#labour_number').val());
        $('#actionModalLabel').text('Add Freight');
        $('#saveLabourBtn').text('Create');
        // Show the modal
        $('#actionModal').modal('show');
        $('.js-example-basic-single').select2({
            width: '100%',
            dropdownParent: $('#actionModal')
        });
    });


     // Form Submission
    $(document).on('submit','#labourForm',function(e) {
        e.preventDefault();
        const $form = $(this);
        const $submitButton = $('#saveLabourBtn');
        // Disable submit button to prevent multiple submissions
        $submitButton.prop('disabled', true);
        const freightId = $('#freight_number').val();
        const url = freightId ?
            "{{ route('freight.update', ':id') }}".replace(':id', freightId) :
            "{{ route('freight.store') }}";
        const method = freightId ? 'POST' : 'POST';

        const formData = new FormData(this);
        if (freightId) {
            formData.append('_method', 'PUT');
        }

        $.ajax({
            url: url,
            method: method,
            data: formData,
            processData: false,
            contentType: false,
            complete: function() {
                // Re-enable submit button
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

    // Edit Labour Modal Handling
    $(document).on('click', '.edit-row-data', function() {
        // Populate form with existing data
        $('#freight_number').val($(this).data('id'));
        $('#name').val($(this).data('name'));
        $('#description').val($(this).data('description'));
        $('#freight_id').val($(this).data('freight-id'));
        $('#freight_supplier').val($(this).data('freight-supplier')).trigger('change');
        $('#freight_price').val($(this).data('freight-price'));
        $('#freight_unit').val($(this).data('freight-unit'));
        $('#parcel_weight').val($(this).data('parcel-weight'));
        if($(this).data('freight-unit') == "Parcel" || $(this).data('freight-unit') == "Kg"){
            $('#parcel_weight').prop('disabled',false)
        }else{
            $('#parcel_weight').prop('disabled',true)
        }
        $('#actionModalLabel').text('Edit Freight');
        $('#saveLabourBtn').text('Update');
        $('#actionModal').modal('show');
        $('.js-example-basic-single').select2({
            width: '100%',
            dropdownParent: $('#actionModal')
        });
    });


    // Delete Labour Handling
    $(document).on('click', '.delete-row-data', function() {
        const id = $(this).data('id');
        const archive = $(this).data('archive');
        const url = "{{ route('freight.destroy', ':id') }}".replace(':id', id);
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
                                title: (archive == 0)? 'Archived': 'Deleted!',
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

    $(document).on('click','#freightDefault',function() {
        let selectvalue = $('#freightDefault').is(':checked')
        $('#dtRecordsView1 tbody tr').each(function() {
            $(this).find('td:eq(0) input').prop('checked',selectvalue)
        });
    });

    function delete_selected_freight(){
        let freightobj = [];
        $("table#dtRecordsView1 tbody tr").each(function () {
            if($(this).find('td:eq(0) input').prop('checked') == true){
                let id = $(this).find('td:eq(0) input').data('freight')
                freightobj.push(id)
            }
        });
        if(freightobj.length == 0){
            Swal.fire({
                icon: 'warning',
                title: 'Warning!',
                text: 'No Freight select'
            });
        }else{
            
            let archiveVal = $('#customFilter').val()
            let html,title,confirmBtn,inputText
            if(archiveVal == "1"){
                title = 'Delete Freights'
                confirmBtn = 'Delete'
                inputText = 'delete'
                html = `<p>The <strong>${freightobj.length}</strong> selected items will be permanently deleted and cannot be retrieved. <strong>Are you sure you want to delete them?</strong></p>
                        <p>To confirm, enter the phrase <strong>delete</strong>.</p>
                        <input id="confirmInput" class="swal2-input" placeholder="Type delete here">`
            }else{
                title = 'Archive Freights'
                confirmBtn = 'Archive'
                inputText = 'archive'
                html = `<p>The <strong>${freightobj.length}</strong> selected items will be archived. <strong>Are you sure you want to archive them?</strong></p>
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
                    var freight = JSON.stringify(freightobj);
                    let data = {'archive':archiveVal,'freightobj':freight,'_token':$('meta[name="csrf-token"]').attr('content')}
                    $.ajax({
                        type: "POST",
                        url: "{{route('freight.delete')}}",
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
            let data = {'_token':$('meta[name="csrf-token"]').attr('content'),'selectedCols':selectedCols,'model':'freight'};	
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
			table.columns(8).search(search_val, true, false).draw();
		}else{ 
			table.columns().search('').draw(); 
		} 
    })

    $(document).on('click', '.unarchive-data', function() {
        const archive = $(this).data('archive');
        const id = $(this).data('id');
        const url = "{{ route('freight.unarchive', ':id') }}".replace(':id', id);
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

</script>
@endpush