@extends('backend.master', [
'pageTitle' => 'FSANZ Food',
'activeMenu' => [
'item' => 'FSANZ Food',
'subitem' => 'FSANZ Food',
'additional' => '',
],
'features' => [
'datatables' => '1',
],
'breadcrumbItems' => [
['label' => 'Food', 'url' => '#'],
['label' => 'Food']
]
])

@push('styles')
<style>
/* Hide DataTables controls until table is fully loaded */
#dtRecordsView1_wrapper {
    visibility: hidden;
    opacity: 0;
}
#dtRecordsView1_wrapper.dt-ready {
    visibility: visible;
    opacity: 1;
    transition: opacity 0.25s ease-in;
}

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

    .select2-selection--single {
        height: 37px !important;
        padding: 5px 10px !important;
        display: flex !important;
        align-items: center !important;
    }

    .select2-selection__arrow {
        height: 35px !important;
    }

   
</style>
@endpush

@section('content')
<div class="container-fluid product-search dataTable-wrapper">
    <div class="card-header">
        <div class="title-add">
            <div><h1 class="page-title">FSANZ Food</h1>
            <p>Australian Food Composition Database with AI-estimated compliance data</p></div>
            <div class="right-side content d-flex flex-row-reverse align-items-center">
                <!-- <div class="btn-group click-dropdown me-2">
                    <button type="button" class="btn btn-primary-orange plus-icon" title="Download FSANZ Food">
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
                </div>  -->
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

        <table class="table responsiveness" id="dtRecordsView1" style="display:none;">
            <thead>
                <tr>
                    <th class="text-primary-blue">
                        <div class="form-check-temp p-1">
                            <input class="form-check-input" type="checkbox" id="fsanzDefault">
                        </div>
                    </th>
                    <th class="text-primary-blue">Name</th>
                    <th class="text-primary-blue">FSANZ Key</th>
                    <th class="text-primary-blue">Food Group</th>
                    <th class="text-primary-blue">Energy</th>
                    <th class="text-primary-blue">Basis</th>
                    <th class="text-primary-blue">Origin</th>
                    <th class="text-primary-blue">AI Status</th>
                    <th class="text-primary-blue"></th>                
                </tr>
            </thead>
            <tbody>
                {{-- Data loaded via AJAX server-side processing --}}
            </tbody>
        </table>
    </div>

    @include('backend.fsanz_food._specmodal')
</div>

@endsection

@push('scripts')


<script>
    /**
     * FSANZ Create Specification Modal Handler
     */
    class FSANZCreateSpecificationModal {
        constructor() {
            this.modal = null;
            this.successModal = null;
            this.currentFood = null;
            this.isSubmitting = false;
            
            this.init();
        }
        
        init() {
            // Initialize Bootstrap modals
            const modalEl = document.getElementById('createSpecificationModal');
            const successModalEl = document.getElementById('successModal');
            
            if (modalEl) {
                this.modal = new bootstrap.Modal(modalEl);
            }
            if (successModalEl) {
                this.successModal = new bootstrap.Modal(successModalEl);
            }
            
            // Bind create button
            const createBtn = document.getElementById('createSpecificationBtn');
            if (createBtn) {
                createBtn.addEventListener('click', () => this.handleCreate());
            }
            
            // Reset form when modal is hidden
            modalEl?.addEventListener('hidden.bs.modal', () => this.resetForm());
        }
        
        /**
         * Open modal with FSANZ food data
         * @param {Object} food - FSANZ food object
         */
        open(food) {
            if (!food) {
                console.error('No food data provided');
                return;
            }
            
            this.currentFood = food;
            this.populateModal(food);
            this.validateFood(food);
            this.modal?.show();
        }
        
        /**
         * Populate modal with FSANZ food data
         */
        populateModal(food) {
            // Hidden fields
            document.getElementById('fsanz_food_id').value = food.id;
            
            // Preview section
            document.getElementById('preview-food-name').textContent = food.name || '-';
            document.getElementById('preview-food-description').textContent = food.description || '';
            document.getElementById('preview-fsanz-key').textContent = food.fsanz_key || '-';
            document.getElementById('preview-measurement-basis').textContent = food.measurement_basis || 'per 100g';
            
            // Result preview
            document.getElementById('result-name').textContent = food.name || '-';
            document.getElementById('result-sku').textContent = `FSANZ-${food.fsanz_key}`;
        }
        
        /**
         * Validate FSANZ food data and show errors/warnings
         */
        validateFood(food) {
            const errors = [];
            const warnings = [];
            
            // Critical validations
            if (!food.name || food.name.trim() === '') {
                errors.push('Food name is required');
            }
            
            if (!food.id) {
                errors.push('FSANZ food ID is missing');
            }
            
            // Check nutritional data
            const nutritionalValues = [food.energy_kj, food.protein_g, food.fat_total_g, food.carbohydrate_g];
            const hasNutritionalData = nutritionalValues.some(val => val !== null && val !== undefined);
            
            if (!hasNutritionalData) {
                errors.push('At least one nutritional value (energy, protein, fat, or carbohydrate) is required');
            }
            
            // Quality warnings
            if (!food.estimated_allergens) {
                warnings.push('No allergen data available - allergen fields will be empty');
            }
            
            if (!food.estimated_dietary_status) {
                warnings.push('No dietary status data available - certification fields will be false');
            }
            
            if (!food.primary_origin_country && !food.estimated_australia_percent) {
                warnings.push('No country of origin data available');
            }
            
            if (!food.estimated_ingredients) {
                warnings.push('No ingredient list data available');
            }
            
            // Display errors
            const errorsContainer = document.getElementById('validation-errors');
            const errorList = document.getElementById('error-list');
            if (errors.length > 0) {
                errorList.innerHTML = errors.map(e => `<li>${e}</li>`).join('');
                errorsContainer.classList.remove('d-none');
                document.getElementById('createSpecificationBtn').disabled = true;
            } else {
                errorsContainer.classList.add('d-none');
                document.getElementById('createSpecificationBtn').disabled = false;
            }
            
            // Display warnings
            const warningsContainer = document.getElementById('validation-warnings');
            const warningList = document.getElementById('warning-list');
            if (warnings.length > 0) {
                warningList.innerHTML = warnings.map(w => `<li>${w}</li>`).join('');
                warningsContainer.classList.remove('d-none');
            } else {
                warningsContainer.classList.add('d-none');
            }
        }
        
        /**
         * Handle form submission
         */
        async handleCreate() {
            if (this.isSubmitting || !this.currentFood) return;
            
            this.isSubmitting = true;
            this.setLoading(true);
            
            const formData = new FormData(document.getElementById('createSpecificationForm'));
            
            try {
                const response = await fetch('/specifications/create-from-fsanz', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: formData
                });
                
                const data = await response.json();
                
                if (!response.ok) {
                    throw new Error(data.message || 'Failed to create specification');
                }
                
                // Success - show success modal
                this.modal?.hide();
                this.showSuccess(data.specification);
                
            } catch (error) {
                console.error('Error creating specification:', error);
                this.showError(error.message);
            } finally {
                this.isSubmitting = false;
                this.setLoading(false);
            }
        }
        
        /**
         * Show success modal
         */
        showSuccess(specification) {
            Swal.fire({
                title: "Success!",
                text: "Specification created successfully.",
                icon: "success",
                confirmButtonText: "Go to Specification",
                confirmButtonColor: "#3085d6",
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = `/specifications/edit/${specification.id}`; // Your redirect URL
                }
            });

            // document.getElementById('success-spec-name').textContent = specification.name;
            // document.getElementById('viewSpecificationLink').href = `/specifications/${specification.id}`;
            // this.successModal?.show();
        }
        
        /**
         * Show error toast/alert
         */
        showError(message) {
            // You can use Bootstrap toast or any notification library
            alert('Error: ' + message);
        }
        
        /**
         * Set loading state
         */
        setLoading(loading) {
            const btn = document.getElementById('createSpecificationBtn');
            const spinner = document.getElementById('loadingSpinner');
            const cancelBtn = document.getElementById('cancelBtn');
            
            if (loading) {
                btn.disabled = true;
                cancelBtn.disabled = true;
                spinner.classList.remove('d-none');
            } else {
                btn.disabled = false;
                cancelBtn.disabled = false;
                spinner.classList.add('d-none');
            }
        }
        
        /**
         * Reset form to initial state
         */
        resetForm() {
            document.getElementById('createSpecificationForm').reset();
            document.getElementById('include_classification').checked = true; // Default checked
            document.getElementById('validation-errors').classList.add('d-none');
            document.getElementById('validation-warnings').classList.add('d-none');
            this.currentFood = null;
            this.isSubmitting = false;
        }
    }

    // Initialize when DOM is ready
    document.addEventListener('DOMContentLoaded', function() {
        window.fsanzSpecModal = new FSANZCreateSpecificationModal();
    });

    document.addEventListener('click', function (e) {
        const btn = e.target.closest('.create-spec-btn');
        
        if (!btn) return;

        // Parse food data from attribute
        let food = btn.dataset.food ? JSON.parse(btn.dataset.food) : null;

        if (food && window.fsanzSpecModal) {
            window.fsanzSpecModal.open(food);
        }
    });
</script>


<script>
$(document).ready(function() {
    var foodgroups = @json($foodGroups);
    
    // Initialize DataTable with Server-Side Processing
    const table = $('#dtRecordsView1').DataTable({
        // ========================================
        // SERVER-SIDE PROCESSING CONFIGURATION
        // ========================================
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('fsanz_food.data') }}",
            type: "GET",
            data: function(d) {
                // Pass custom filter values to server
                d.measurement_basis = $('#filter1').val() || 'all';
                d.food_group = $('#filter2').val() || 'all';
                d.origin = $('#filter3').val() || 'all';
                d.ai_status = $('#filter4').val() || 'all';
            },
            error: function(xhr, error, thrown) {
                console.error('DataTables AJAX error:', error, thrown);
                $("#tableSkeleton").hide();
                $("#dtRecordsView1").show();
            }
        },
        
        // Column definitions - must match server response
        columns: [
            { data: 'checkbox', name: 'checkbox', orderable: false, searchable: false },
            { data: 'name', name: 'name' },
            { data: 'fsanz_key', name: 'fsanz_key' },
            { data: 'food_group', name: 'food_group' },
            { data: 'energy_kj', name: 'energy_kj' },
            { data: 'measurement_basis', name: 'measurement_basis' },
            { data: 'primary_origin_country', name: 'primary_origin_country' },
            { data: 'ai_estimation_status', name: 'ai_estimation_status' },
            { data: 'actions', name: 'actions', orderable: false, searchable: false }
        ],
        
        // Default sorting
        order: [[1, 'asc']],
        
        // Other options
        responsive: true,
        pageLength: 25,
        
        dom: "<'row mb-4'<'col-md-6 col-sm-6'fB><'col-md-6 col-sm-6 custom-dropdown'l>>" +
            "<'row table-scroll'<'col-sm-12 overflow-container'tr>>" +
            "<'row'<'col-md-5'i><'col-md-7'p>>",

        buttons: [{
                extend: 'csvHtml5',
                text: 'CSV',
                className: 'btn-hidden buttons-csv',
                exportOptions: {
                    columns: ':not(:first-child):not(:last-child)'
                },
                title: ""
            },
            {
                extend: 'excelHtml5',
                text: 'Excel',
                className: 'btn-hidden buttons-excel',
                exportOptions: {
                    columns: ':not(:first-child):not(:last-child)'
                },
                title: ""
            },
            {
                extend: 'colvis',
                columns: ':not(:last, :first)',
                text: '<span class="material-symbols-outlined" style="font-size: 30px; margin-top: -6px;"> view_column </span>',
                action: function (e, dt, button, config) {
                    if ($('.colvis-dropdown').length === 0) {
                        createColVisDropdown(dt);
                    }
                }
            },
        ],

        columnDefs: [
            {
                targets: 0,
                orderable: false,
                searchable: false
            },
            {
                targets: -1,
                className: 'noVis always-visible actions-menu-area',
                orderable: false,
                searchable: false
            }
        ],

        language: {
            search: "",
            searchPlaceholder: "Search",
            lengthMenu: "_MENU_ per page",
            processing: '<div class="d-flex align-items-center justify-content-center"><div class="spinner-border text-primary me-2" role="status"></div><span>Loading data...</span></div>',
            paginate: {
                previous: "<i class='material-symbols-outlined'>chevron_left</i>",
                next: "<i class='material-symbols-outlined'>chevron_right</i>"
            },
            info: "Showing _START_ to _END_ of _TOTAL_ entries",
            infoFiltered: "(filtered from _MAX_ total entries)"
        },
        
        // Debounce search to reduce AJAX calls
        searchDelay: 400,
        
        initComplete: function() {
            const tableWrapper = $(this).closest('.dataTables_wrapper');
            
            // Hide skeleton and show table with controls
            $("#tableSkeleton").fadeOut(200, () => {
                $("#dtRecordsView1").show();
                tableWrapper.addClass('dt-ready'); // Show all DataTables controls
            });
            
            // Move the search box to the left and entries dropdown to the right
            const lengthDropdown = tableWrapper.find('.dataTables_length');
            const colvisButton = tableWrapper.find('.buttons-colvis');
            colvisButton.insertBefore(lengthDropdown);
            const searchBox = tableWrapper.find('.dataTables_filter');

            // INSERT CUSTOM FILTER CONTAINER right after the search
            searchBox.after('<div id="customFilterContainer" class="d-flex gap-2 ms-3"></div>');

            // Now add 4 dropdowns inside it
            const customFilters = `
                <select id="filter1" class="form-select form-select-sm fa-basic-multiple"><option value="all">All Measurements</option><option value="per_100g">Per 100g</option><option value="per_100ml">Per 100ml</option></select>
                <select id="filter2" class="form-select form-select-sm fa-basic-multiple"><option value="all">All Food Groups</option></select>
                <select id="filter3" class="form-select form-select-sm fa-basic-multiple"><option value="all">All Origins</option><option value="australia">Australia Only</option><option value="imported">Imported</option></select>
                <select id="filter4" class="form-select form-select-sm fa-basic-multiple"><option value="all">All AI Status</option><option value="completed">With AI Analysis</option><option value="incomplete">Without AI Analysis</option></select>
            `;
            $('#customFilterContainer').html(customFilters);

            // Populate Food Groups dynamically
            let filter2 = document.getElementById("filter2");
            foodgroups.forEach(group => {
                if (group) {
                    let opt = document.createElement("option");
                    opt.value = group;
                    opt.textContent = group;
                    filter2.appendChild(opt);
                }
            });

            // Enable Select2 on all filters
            $('#filter1, #filter2, #filter3, #filter4').select2({
                width: '150px',
                height: '35px',
                minimumResultsForSearch: Infinity
            });

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
        }
    });

    // ========================================
    // CUSTOM FILTER HANDLERS - Reload table with server-side filtering
    // ========================================
    $(document).on('change', '#filter1, #filter2, #filter3, #filter4', function() {
        // Reload table with new filter values (sent to server via ajax.data)
        table.ajax.reload();
    });
});

function createColVisDropdown(dt) {
    let dropdownHtml = '<div class="colvis-dropdown">';  
    let initiallyCheckedColumns = [1,2,3,4,5,6,7]; // Define which columns should be checked by default
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


// Select All checkbox handler - works with server-side rendered rows
$(document).on('click', '#fsanzDefault', function() {
    let selectvalue = $(this).is(':checked');
    // Only select visible rows on current page
    $('#dtRecordsView1 tbody tr').each(function() {
        $(this).find('td:eq(0) input.food_check').prop('checked', selectvalue);
    });
});

// Reset "Select All" checkbox when page changes
$('#dtRecordsView1').on('draw.dt', function() {
    $('#fsanzDefault').prop('checked', false);
});


</script>
@endpush