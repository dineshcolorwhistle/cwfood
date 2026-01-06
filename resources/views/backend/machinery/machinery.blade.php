@extends('backend.master', [
'pageTitle' => 'Machinery Management',
'activeMenu' => [
'item' => 'Machinery',
'subitem' => 'Machineries',
'additional' => '',
],
'features' => [
'datatables' => '1',
],
'breadcrumbItems' => [
['label' => 'Nutriflow Admin', 'url' => '#'],
['label' => 'Machinery Management']
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
<div class="container-fluid machinery my-4">
    <div class="">
        <div class="card-header d-flex justify-content-between">
            <h1 class="page-title">Machinery</h1>
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
                    <a href="{{ route('machinery.import') }}" class="btn btn-primary-orange plus-icon" title="Upload / Import data">
                        <span class="material-symbols-outlined">upload</span>
                    </a>
                </div>
                <div class="btn-group click-dropdown me-2">
                    <button type="button" class="btn btn-primary-orange plus-icon" title="Download Machinery">
                        <span class="material-symbols-outlined">download</span>
                    </button>
                    <ul class="dropdown-menu">
                        <li>
                            <a class="dropdown-item export-csv" href="javascript:void(0);" data-url="/download/csv/machinery">
                                Download as CSV
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item export-excel" href="javascript:void(0);" data-url="/download/excel/machinery">
                                Download as Excel
                            </a>
                        </li>
                    </ul>
                </div>
                @endif
                
                @if($user_role == 1 || $user_role == 2 || $user_role == 3)
                    <button type="button" class="btn btn-primary-orange plus-icon me-2" onclick="delete_selected_machine()" title="Delete Machinery">
                        <span class="material-symbols-outlined">delete</span>
                    </button>
                @endif

                @if($user_role != 4)
                <button type="button" class="btn btn-primary-orange plus-icon me-2" id="addMachineryBtn" title="Add Machinery">
                    <span class="material-symbols-outlined">add</span>
                </button>
                @endif
            </div>
            
        </div>
        <input type="hidden" name="machinery_primary" id="machinery_primary" value="">
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
                        <th class="text-primary-blue hide">Machinery ID</th>
                        <th class="text-primary-blue">
                            <div class="form-check-temp p-1">
                                <input class="form-check-input" type="checkbox" id="machineDefault">
                            </div>
                        </th>
                        <th class="text-primary-blue">Machine Name</th>
                        <th class="text-primary-blue text-end">Cost per Hour <br>($)</th>
                        <th class="text-primary-blue">Serial Number</th>
                        <th class="text-primary-blue">Model Number</th>
                        <th class="text-primary-blue">Manufacturer</th>
                        <th class="text-primary-blue">Energy Efficiency</th>
                        <th class="text-primary-blue text-end">Power Consumption <br>(kW)</th>
                        <th class="text-primary-blue ">Machine Condition</th>
                        <th class="text-primary-blue ">Location</th>
                        <th class="text-primary-blue ">Year of Manufacture</th>
                        <th class="text-primary-blue ">Maintenance Frequency</th>
                        <th class="text-primary-blue ">Production Rate (Units/Hour)</th>
                        <th class="text-primary-blue ">Setup Time (Minutes)</th>
                        <th class="text-primary-blue ">Downtime Impact ($/Hour)</th>
                        <th class="text-primary-blue ">Wear and Tear Factor</th>
                        <th class="text-primary-blue ">Last Maintenance Date</th>
                        <th class="text-primary-blue ">Depreciation Rate (%/Year)</th>
                        <th class="text-primary-blue ">Additional Notes</th>   
                        <th class="text-primary-blue hide"></th>                     
                        <th class="text-primary-blue"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($machinery as $machine)
                    @php $machine = (object) $machine; @endphp 
                    <tr data-id="{{ $machine->id }}">
                        <td class="text-primary-dark-mud hide">{{ $machine->machinery_id }}</td>    
                        <td class="text-primary-dark-mud">
                            <div class="form-check-temp p-1">
                                <input class="form-check-input machinery_check" data-machine="{{$machine->id}}" type="checkbox" id="machine_{{$machine->id}}">
                            </div>
                        </td>
                        <td class="text-primary-dark-mud">{{ $machine->name }}</td>    
                        <td class="text-primary-dark-mud text-end">{{ $machine->cost_per_hour_aud }}</td>
                        <td class="text-primary-dark-mud">{{ $machine->serial_number }}</td>
                        <td class="text-primary-dark-mud">{{ $machine->model_number }}</td>
                        <td class="text-primary-dark-mud">{{ $machine->supplier ? $machine->supplier['company_name'] : "" }}</td>
                        <td class="text-primary-dark-mud">{{ $machine->energy_efficiency }}</td>
                        <td class="text-primary-dark-mud text-end">{{ $machine->power_consumption_kw }}</td>
                        <td class="text-primary-dark-mud ">{{ $machine->condition }}</td> 
                        <td class="text-primary-dark-mud ">{{ $machine->location }}</td>
                        <td class="text-primary-dark-mud ">{{ $machine->year_of_manufacture }}</td>
                        <td class="text-primary-dark-mud ">{{ $machine->maintenance_frequency }}</td>
                        <td class="text-primary-dark-mud ">{{ $machine->production_rate_units_hr }}</td>
                        <td class="text-primary-dark-mud ">{{ $machine->setup_time_minutes }}</td>
                        <td class="text-primary-dark-mud ">{{ $machine->downtime_impact_aud_hr }}</td>
                        <td class="text-primary-dark-mud ">{{ $machine->wear_and_tear_factor }}</td>
                        <td class="text-primary-dark-mud ">{{ $machine->last_maintenance_date }}</td>
                        <td class="text-primary-dark-mud ">{{ $machine->depreciation_rate_percent_yr }}</td>
                        <td class="text-primary-dark-mud ">{{ $machine->notes }}</td>
                        <td class="text-primary-dark-mud hide">{{ $machine->archive }}</td>                          
                        <td class="actions-menu-area">
                            <div class="">
                                <!-- 3-Dot Icon Menu for Grid View -->
                               
                                <div class="dropdown d-flex justify-content-end">
                                    @if($user_role != 4)
                                    <button type="button" class="icon-primary-orange me-2" title="Favourite">
                                        <span class="material-symbols-outlined" onclick="make_favorite(this)" id="makefavorite_{{$machine->id}}" data-favor="{{$machine->favorite}}" data-url="{{route('machinery.favorite',['id'=>$machine->id])}}">favorite</span>
                                    </button>
                                    <button class="icon-primary-orange me-0" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                                        <span class="material-symbols-outlined">more_vert</span>
                                    </button>
                                    
                                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                        <li>
                                            <span class="dropdown-item text-primary-dark-mud me-2 edit-row-data"
                                                data-id="{{ $machine->id }}"
                                                data-machine="{{$machine->machinery_id}}"
                                                data-name="{{ $machine->name }}"
                                                data-model-number="{{ $machine->model_number }}"
                                                data-year-of-manufacture="{{ $machine->year_of_manufacture }}"
                                                data-manufacturer="{{ $machine->manufacturer }}"
                                                data-serial-number="{{ $machine->serial_number }}"
                                                data-energy-efficiency="{{ $machine->energy_efficiency }}"
                                                data-power-consumption-kw="{{ $machine->power_consumption_kw }}"
                                                data-cost-per-hour-aud="{{ $machine->cost_per_hour_aud }}"
                                                data-maintenance-frequency="{{ $machine->maintenance_frequency }}"
                                                data-last-maintenance-date="{{ $machine->last_maintenance_date ? \Carbon\Carbon::parse($machine->last_maintenance_date)->format('Y-m-d') : '' }}"
                                                data-condition="{{ $machine->condition }}"
                                                data-location="{{ $machine->location }}"
                                                data-production-rate-units-hr="{{ $machine->production_rate_units_hr }}"
                                                data-setup-time-minutes="{{ $machine->setup_time_minutes }}"
                                                data-downtime-impact-aud-hr="{{ $machine->downtime_impact_aud_hr }}"
                                                data-wear-and-tear-factor="{{ $machine->wear_and_tear_factor }}"
                                                data-depreciation-rate-percent-yr="{{ $machine->depreciation_rate_percent_yr }}"
                                                data-notes="{{ $machine->notes }}">
                                                <span class="sidenav-normal ms-2 ps-1">Edit</span>
                                            </span>
                                        </li>
                                        <li>
                                            <span class="dropdown-item text-primary-dark-mud delete-row-data" data-archive="{{ $machine->archive }}" data-id="{{ $machine->id }}">
                                                <span class="sidenav-normal ms-2 ps-1">@if($machine->archive == 1) Delete @else Archive @endif</span>
                                            </span>
                                        </li>
                                        @if($machine->archive == 1)
                                        <li>
                                            <span class="dropdown-item text-primary-dark-mud unarchive-data" data-archive="{{ $machine->archive }}" data-id="{{ $machine->id }}">
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

    <!-- Machinery Modal -->
    <div class="modal fade" id="actionModal" tabindex="-1" role="dialog" aria-labelledby="actionModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title text-primary-orange" id="actionModalLabel">Add/Edit Machinery</h2>
                </div>
                <form id="machineryForm" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="machinery_id" id="machinery_id" value="@if(isset($lab_code)){{$lab_code}}@endif">
                    <div class="modal-body">
                        <div class="row">

                            <div class="col-md-6 form-group">
                                <label class="text-primary-orange" for="name">Machine Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" id="name" class="form-control">
                            </div>

                            <div class="col-md-6 form-group">
                                <label class="text-primary-orange" for="cost_per_hour_aud">Cost per Hour ($) <span class="text-danger">*</span></label>
                                <input type="number" name="cost_per_hour_aud" id="cost_per_hour_aud" class="form-control" step="0.01">
                            </div>

                            <div class="col-md-6 form-group">
                                <label class="text-primary-orange" for="model_number">Model Number</label>
                                <input type="text" name="model_number" id="model_number" class="form-control">
                            </div>

                            <div class="col-md-6 form-group">
                                <label class="text-primary-orange" for="year_of_manufacture">Year of Manufacture</label>
                                <input type="number" name="year_of_manufacture" id="year_of_manufacture" class="form-control">
                            </div>

                            <div class="col-md-6 form-group">
                                <label class="text-primary-orange" for="manufacturer">Manufacturer</label>
                                <select name="manufacturer" id="manufacturer" class="form-control-select js-example-basic-single">
                                    <option selected disabled>Select Supplier</option>
                                    @foreach($suppliers as $supplier)
                                    <option value="{{$supplier->id}}">{{$supplier->company_name}}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6 form-group">
                                <label class="text-primary-orange" for="serial_number">Serial Number</label>
                                <input type="text" name="serial_number" id="serial_number" class="form-control">
                            </div>

                            <div class="col-md-6 form-group d-flex flex-column">
                                <label class="text-primary-orange" for="energy_efficiency">Energy Efficiency</label>
                                <select name="energy_efficiency" id="energy_efficiency" class="form-control-select">
                                    <option value="High">High</option>
                                    <option value="Medium">Medium</option>
                                    <option value="Low">Low</option>
                                    <option value="A+">A+</option>
                                    <option value="A">A</option>
                                    <option value="B+">B+</option>
                                    <option value="B">B</option>
                                    <option value="C">C</option>
                                    <option value="D">D</option>
                                </select>
                            </div>

                            <div class="col-md-6 form-group">
                                <label class="text-primary-orange" for="power_consumption_kw">Power Consumption (kW)</label>
                                <input type="number" name="power_consumption_kw" id="power_consumption_kw" class="form-control" step="0.1">
                            </div>

                            <div class="col-md-6 form-group d-flex flex-column">
                                <label class="text-primary-orange" for="maintenance_frequency">Maintenance Frequency</label>
                                <select name="maintenance_frequency" id="maintenance_frequency" class="form-control-select">
                                    <option value="Daily">Daily</option>
                                    <option value="Weekly">Weekly</option>
                                    <option value="Monthly">Monthly</option>
                                    <option value="Quarterly">Quarterly</option>
                                    <option value="Biannually">Biannually</option>
                                    <option value="Annually">Annually</option>
                                </select>
                            </div>

                            <div class="col-md-6 form-group">
                                <label class="text-primary-orange" for="last_maintenance_date">Last Maintenance Date</label>
                                <input type="date" name="last_maintenance_date" id="last_maintenance_date" class="form-control">
                            </div>

                            <div class="col-md-6 form-group d-flex flex-column">
                                <label class="text-primary-orange" for="condition">Machine Condition</label>
                                <select name="condition" id="condition" class="form-control-select">
                                    <option value="Good">Good</option>
                                    <option value="Excellent">Excellent</option>
                                    <option value="Fair">Fair</option>
                                    <option value="New">New</option>
                                    <option value="Operational">Operational</option>
                                    <option value="Under Repair">Under Repair</option>
                                    <option value="Decommissioned">Decommissioned</option>
                                </select>
                            </div>

                            <div class="col-md-6 form-group">
                                <label class="text-primary-orange" for="location">Location</label>
                                <input type="text" name="location" id="location" class="form-control">
                            </div>

                            <div class="col-md-6 form-group">
                                <label class="text-primary-orange" for="production_rate_units_hr">Production Rate (Units/Hour)</label>
                                <input type="number" name="production_rate_units_hr" id="production_rate_units_hr" class="form-control" step="0.01">
                            </div>

                            <div class="col-md-6 form-group">
                                <label class="text-primary-orange" for="setup_time_minutes">Setup Time (Minutes)</label>
                                <input type="number" name="setup_time_minutes" id="setup_time_minutes" class="form-control">
                            </div>

                            <div class="col-md-6 form-group">
                                <label class="text-primary-orange" for="downtime_impact_aud_hr">Downtime Impact ($/Hour)</label>
                                <input type="number" name="downtime_impact_aud_hr" id="downtime_impact_aud_hr" class="form-control" step="0.01">
                            </div>

                            <div class="col-md-6 form-group">
                                <label class="text-primary-orange" for="wear_and_tear_factor">Wear and Tear Factor</label>
                                <input type="number" name="wear_and_tear_factor" id="wear_and_tear_factor" class="form-control" step="0.01" min="0" max="1">
                            </div>

                            <div class="col-md-6 form-group">
                                <label class="text-primary-orange" for="depreciation_rate_percent_yr">Depreciation Rate (%/Year)</label>
                                <input type="number" name="depreciation_rate_percent_yr" id="depreciation_rate_percent_yr" class="form-control" step="0.01">
                            </div>

                            <div class="col-md-12 form-group">
                                <label class="text-primary-orange" for="notes">Additional Notes</label>
                                <textarea name="notes" id="notes" class="form-control" rows="3"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary-white" data-dismiss="modal" id="closeActionModal">Close</button>
                        <button type="submit" class="btn btn-secondary-blue" id="saveMachineryBtn">Save</button>
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
        var tableArray = @json($machinery);
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
                //         delete_selected_machine();
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
                    targets: [8,9,10,11,12,13,14,15,16,17,18,19], // Specify columns that should be hidden initially
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
                table.columns(20).search(0, true, false).draw(); 
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

        $('#machineryForm').on('change input', 'input, select, textarea', function () {
            isFormChanged = true;
        });

        $('#machineryForm').on('submit', function () {
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
                            window.location.href = "{{route('machinery.index')}}";    
                        }, 1000);
                    } else if (result.isDenied) {
                        isFormChanged = false;
                        ignorePopState = true;
                        window.location.href = "{{route('machinery.index')}}";    
                    } else {
                        // Stay on the page
                    }
                });
            }
        });
    });

    function createColVisDropdown(dt) {
        let dropdownHtml = '<div class="colvis-dropdown">';  
        let initiallyCheckedColumns = [2,3,4,5,6,7]; // Define which columns should be checked by default

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

    $(document).ready(function() {
        // Add Machinery Modal Handling
        $('#addMachineryBtn').on('click', function() {
            // Reset the form
            $('#machineryForm')[0].reset();
            $('#machinery_primary').val('');

            // Auto-generate serial number
            // $('#serial_number').val('MCH-' + Math.random().toString(36).substr(2, 6).toUpperCase());

            $('#actionModalLabel').text('Add Machinery');
            $('#saveMachineryBtn').text('Create');

            // Show the modal
            $('#actionModal').modal('show');

            $('.js-example-basic-single').select2({
            width: '100%',
            dropdownParent: $('#actionModal')
        });
        });

        // Edit Machinery Modal Handling
        $(document).on('click', '.edit-row-data', function() {
            // Populate form with existing data
            $('#machinery_primary').val($(this).data('id'));
            $('#machinery_id').val($(this).data('machine'));
            $('#serial_number').val($(this).data('serial-number'));
            $('#name').val($(this).data('name'));
            $('#model_number').val($(this).data('model-number'));
            $('#year_of_manufacture').val($(this).data('year-of-manufacture'));
            $('#manufacturer').val($(this).data('manufacturer'));
            // $('#energy_efficiency').val($(this).data('energy-efficiency'));
            var energyEfficiency = $(this).data('energy-efficiency').toLowerCase(); // Convert to lowercase
            $('#energy_efficiency option').each(function() {
                if ($(this).val().toLowerCase() === energyEfficiency) { // Compare values in lowercase
                    $(this).prop('selected', true); // Set selected option
                }
            });
            $('#power_consumption_kw').val($(this).data('power-consumption-kw'));
            $('#cost_per_hour_aud').val($(this).data('cost-per-hour-aud'));
            //$('#maintenance_frequency').val($(this).data('maintenance-frequency'));
            var maintenanceFrequency = $(this).data('maintenance-frequency').toLowerCase(); // Convert to lowercase
            $('#maintenance_frequency option').each(function() {
                if ($(this).val().toLowerCase() === maintenanceFrequency) { // Compare values in lowercase
                    $(this).prop('selected', true); // Set selected option
                }
            });
            $('#last_maintenance_date').val($(this).data('last-maintenance-date'));
            // $('#condition').val($(this).data('condition'));
            var condition = $(this).data('condition').toLowerCase(); // Convert to lowercase
            $('#condition option').each(function() {
                if ($(this).val().toLowerCase() === condition) { // Compare values in lowercase
                    $(this).prop('selected', true); // Set selected option
                }
            });
            $('#location').val($(this).data('location'));
            $('#production_rate_units_hr').val($(this).data('production-rate-units-hr'));
            $('#setup_time_minutes').val($(this).data('setup-time-minutes'));
            $('#downtime_impact_aud_hr').val($(this).data('downtime-impact-aud-hr'));
            $('#wear_and_tear_factor').val($(this).data('wear-and-tear-factor'));
            $('#depreciation_rate_percent_yr').val($(this).data('depreciation-rate-percent-yr'));
            $('#notes').val($(this).data('notes'));

            $('#actionModalLabel').text('Edit Machinery');
            $('#saveMachineryBtn').text('Update');
            $('#actionModal').modal('show');
            $('.js-example-basic-single').select2({
            width: '100%',
            dropdownParent: $('#actionModal')
        });
        });

        // Form Submission
        $('#machineryForm').on('submit', function(e) {
            e.preventDefault();

            const $form = $(this);
            const $submitButton = $('#saveMachineryBtn');

            // Disable submit button to prevent multiple submissions
            $submitButton.prop('disabled', true);

            const machineryId = $('#machinery_primary').val();
            const url = machineryId ?
                "{{ route('machinery.update', ':id') }}".replace(':id', machineryId) :
                "{{ route('machinery.store') }}";
            const method = machineryId ? 'POST' : 'POST';

            const formData = new FormData(this);
            if (machineryId) {
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

        // Delete Machinery Handling
        $(document).on('click', '.delete-row-data', function() {
            const id = $(this).data('id');
            const archive = $(this).data('archive');
            const url = "{{ route('machinery.destroy', ':id') }}".replace(':id', id);
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

    $(document).on('click','#machineDefault',function() {
        let selectvalue = $('#machineDefault').is(':checked')
        $('#dtRecordsView1 tbody tr').each(function() {
            $(this).find('td:eq(1) input').prop('checked',selectvalue)
        });
    });


    function delete_selected_machine(){
        let machineobj = [];
        // $('table#dtRecordsView1').DataTable().destroy();
        // $('table#dtRecordsView1').dataTable({ paging: false, ordering: false });
        $("table#dtRecordsView1 tbody tr").each(function () {
            if($(this).find('td:eq(1) input').prop('checked') == true){
                let id = $(this).find('td:eq(1) input').data('machine')
                machineobj.push(id)
            }
        });
        if(machineobj.length == 0){
            Swal.fire({
                icon: 'warning',
                title: 'Warning!',
                text: 'No Machine select'
            });
        }else{
            let archiveVal = $('#customFilter').val()
            let html,title,confirmBtn,inputText
            if(archiveVal == "1"){
                title = 'Delete Machines'
                confirmBtn = 'Delete'
                inputText = 'delete'
                html = `<p>The <strong>${machineobj.length}</strong> selected items will be permanently deleted and cannot be retrieved. <strong>Are you sure you want to delete them?</strong></p>
                        <p>To confirm, enter the phrase <strong>delete</strong>.</p>
                        <input id="confirmInput" class="swal2-input" placeholder="Type delete here">`
            }else{
                title = 'Archive Machines'
                confirmBtn = 'Archive'
                inputText = 'archive'
                html = `<p>The <strong>${machineobj.length}</strong> selected items will be archived. <strong>Are you sure you want to archive them?</strong></p>
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
                    var machine = JSON.stringify(machineobj);
                    let data = {'archive':archiveVal,'machineobj':machine,'_token':$('meta[name="csrf-token"]').attr('content')}
                    $.ajax({
                        type: "POST",
                        url: "{{route('machinery.delete')}}",
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
            let data = {'_token':$('meta[name="csrf-token"]').attr('content'),'selectedCols':selectedCols,'model':'machinery'};	
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
			table.columns(20).search(search_val, true, false).draw();
		}else{ 
			table.columns().search('').draw(); 
		} 
    })

    $(document).on('click', '.unarchive-data', function() {
        const archive = $(this).data('archive');
        const id = $(this).data('id');
        const url = "{{ route('machinery.unarchive', ':id') }}".replace(':id', id);
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