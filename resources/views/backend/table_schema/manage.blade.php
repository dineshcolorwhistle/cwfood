@extends('backend.master', [
'pageTitle' => 'Table Schema',
'activeMenu' => [
'item' => 'Table Schema',
'subitem' => 'Table Schema',
'additional' => '',
],
'features' => [
'datatables' => '1',
],
'breadcrumbItems' => [
['label' => 'Prompts', 'url' => '#'],
['label' => 'Prompts']
]
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

    /* #tableFilter {
        padding: 9px !important;font-size: 14px;
        border-radius: 4px;
        color: var(--Primary-Dark-Mud);
    } */

    .dt-table-filter-wrap{width: 350px;}
    .select2-container--default .select2-selection--single .select2-selection__placeholder {float: left;}
    .tooltipContent p {font-size:16px !important;}
   
</style>
@endpush

@section('content')
<div class="container-fluid product-search dataTable-wrapper">
    <div class="card-header">
        <div class="title-add">
            <h1 class="page-title">Table Schema</h1>
            <div class="right-side content d-flex flex-row-reverse align-items-center">
                <div class="btn-group click-dropdown me-2">
                    <button type="button" class="btn btn-primary-orange plus-icon" title="Download Table Schema">
                        <span class="material-symbols-outlined">download</span>
                    </button>
                    <ul class="dropdown-menu">
                        <li>
                            <a class="dropdown-item export-csv" href="javascript:void(0);" data-url="/admin/table_schema/csv">
                                Download as CSV
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item export-excel" href="javascript:void(0);" data-url="/admin/table_schema/excel">
                                Download as Excel
                            </a>
                        </li>
                    </ul>
                </div> 
            </div>
        </div>
    </div>


    <div class="card-body">
        <x-shimmer />

        <table class="table responsiveness" id="dtRecordsView1" style="display:none;">
            <thead>
                <tr>
                    @foreach($keys as $key => $value)
                        <th class="text-primary-blue @if($key > 7) hide @endif">
                            {{ strtoupper($value) }}
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                 @foreach($schema_response as $schema_key => $schema_value)
                    <tr>
                        @foreach($keys as $key => $value)
                            <td class="text-primary-dark-mud @if($key > 7) hide @endif">
                                {{ $schema_value->$value }}
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>

    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="glossaryModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <!-- Modal Header -->
            <div class="modal-header">
                <div>
                    <h5 class="modal-title">Database Glossary</h5>
                </div>                
            </div>

            <!-- Modal Body (scrollable) -->
            <div class="modal-body" style="max-height: 60vh; overflow-y: auto;">
                
               <div class='tooltipContent'><p><strong>TABLE NAME:</strong> The name of the table that holds a specific group of data.</p>
                <p><strong>COLUMN NAME:</strong> The name of the table that holds a specific group of data.</p>
                <p><strong>ORDINAL_POSITION:</strong> The order number of the column in the table (1st, 2nd, etc.).</p>
                <p><strong>DATA_TYPE:</strong> The type of data stored (e.g., number, text, date).</p>
                <p><strong>COLUMN_TYPE:</strong> The full technical definition of the column, including size (e.g., varchar(255), decimal(10,2)).</p>
                <p><strong>CHARACTER_MAXIMUM_LENGTH:</strong> For text columns, this is the maximum number of characters allowed.</p>
                <p><strong>NUMERIC_PRECISION:</strong> For numeric columns, the total number of digits allowed.</p>
                <p><strong>NUMERIC_SCALE:</strong> For numeric columns, how many digits appear after the decimal point.</p>
                <p><strong>DATETIME_PRECISION:</strong> How precise a timestamp or date field is (e.g., includes microseconds).</p>
                <p><strong>CHARACTER_SET_NAME:</strong> The character encoding used (e.g., UTF-8).</p>
                <p><strong>COLLATION_NAME:</strong> The sorting and comparison rule used for text.</p>
                <p><strong>TABLE_COLLATION:</strong> The default collation applied to the entire table.</p>
                <p><strong>IS_NULLABLE:</strong> Whether a column can be empty (NULL) or must always have a value.</p>
                <p><strong>COLUMN_DEFAULT:</strong> The value automatically filled in when nothing is provided.</p>
                <p><strong>COLUMN_KEY:</strong> Whether the column is used to uniquely identify rows (e.g., Primary Key).</p>
                <p><strong>EXTRA:</strong> Special options (e.g., auto_increment, meaning it increases automatically).</p>
                <p><strong>GENERATION_EXPRESSION:</strong> Formula for computed fields (if the column is generated automatically).</p>
                <p><strong>ENGINE:</strong> The storage system the table uses (e.g., InnoDB, MyISAM).</p>
                <p><strong>TABLE_ROWS:</strong> Approximate number of rows in the table.</p>
                <p><strong>CREATE_TIME:</strong> The date/time when the table was created.</p>
                <p><strong>UPDATE_TIME:</strong> The last time the table’s structure or data was updated.</p>
                <p><strong>COLUMN_COMMENT:</strong> Developer or business notes about what the column means.</p></div>
            </div>

            <!-- Modal Footer -->
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary-white" data-bs-dismiss="modal">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {   
    var tableArray = @json($tableNames);
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
                targets: [8,9,10,11,12,13,14,15,16,17,18], // Specify columns that should be hidden initially
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
                
             var api = this.api();

            // Move the search box to the left and entries dropdown to the right
            const tableWrapper = $(this).closest('.dataTables_wrapper');
            const lengthDropdown = tableWrapper.find('.dataTables_length');
            const colvisButton = tableWrapper.find('.buttons-colvis');
            colvisButton.insertBefore(lengthDropdown); // Move the colvis button before the length dropdown (right side)

            // Create info icon with tooltip
            var infoIcon = $(`
                <span class="ms-2"
                    data-bs-toggle="modal" data-bs-target="#glossaryModal">
                   <span class="material-symbols-outlined" style="font-size:30px; cursor:pointer;">info</span> 
                </span>
            `);

            // Build options safely
            var optionsHtml = '<option value="">All Tables</option>';
            for (var i = 0; i < tableArray.length; i++) {
                // escape simple values
                var val = $('<div>').text(tableArray[i]).html();
                optionsHtml += '<option value="' + val + '">' + val + '</option>';
            }

            var dropdownWrapper = $('<div class="dt-table-filter-wrap ms-2"></div>');
            var selectEl = $('<select id="tableFilter" class="tableFilter-padding fa-basic-multiple" multiple></select>').html(optionsHtml);
            dropdownWrapper.append(selectEl);

            selectEl.select2({
                width: '300px;',
                placeholder: "Select table",
                allowClear: true
            });

            // Insert after existing filter input (search) without changing layout
            var filterBox = tableWrapper.find('.dataTables_filter');
            if (filterBox.length) {
                // ensure filter box uses flex so appended element aligns well
                filterBox.css('display', 'flex').css('align-items', 'center');
                filterBox.append(dropdownWrapper);
            } else {
                // fallback: put into the custom-dropdown area if search not found
                tableWrapper.find('.custom-dropdown').append(dropdownWrapper);
            }

            

            // Assuming TABLE_NAME is in column index 0 - CHANGE INDEX if needed
            var tableNameColumnIndex = 0;

            $('#tableFilter').on('change', function () {
                var selected = $(this).val(); // Array of selected values

                if (!selected || selected.length === 0) {
                    api.column(tableNameColumnIndex)
                    .search('', false, false)
                    .draw();
                    return;
                }

                // Build OR regex:  ^(A|B|C)$
                var escaped = selected.map(v =>
                    v.replace(/[-\/\\^$*+?.()|[\]{}]/g, '\\$&')
                );

                var regex = '^(' + escaped.join('|') + ')$';

                api.column(tableNameColumnIndex)
                .search(regex, true, false)
                .draw();
            });

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

            // append info icon then dropdown
            $('.custom-dropdown').prepend(infoIcon);


            // Enable tooltip
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
            
        }
    });
});

function createColVisDropdown(dt) {
    let dropdownHtml = '<div class="colvis-dropdown">'; 
    let initiallyCheckedColumns = [0,1,2,3,4,5,6,7]; // Define which columns should be checked by default   
    dt.columns().every(function(idx) {
        let column = this;
        let columnTitle = column.header().textContent;
        if (columnTitle !== "") {
        dropdownHtml += `<label class="colvis-item">
                    <span>${columnTitle}</span>
                    <input type="checkbox" class="colvis-checkbox form-check-input" data-column="${idx}" 
                        ${initiallyCheckedColumns.includes(idx) ? 'checked' : ''}>
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


$(document).on('click','.export-csv, .export-excel', function(){
    let url = $(this).attr('data-url')
    var selected = $('#tableFilter').val(); // Array of selected values
    if (!selected || selected.length === 0) {
         Swal.fire({
            icon: 'warning',
            title: 'Warning!',
            text: 'Can you select any Table'
        }); 
    }else{
        let tables = selected.join(",");
        url += `/${tables}`;
        window.open(url,'_blank');
       
    }
})
</script>
@endpush