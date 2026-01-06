@extends('backend.master', [
'pageTitle' => 'Specifications',
'activeMenu' => [
'item' => 'Specifications',
'subitem' => 'Specifications',
'additional' => '',
],
'breadcrumbItems' => [
['label' => 'Batchbase Agent', 'url' => '#'],
['label' => 'Product']
]
])

@push('styles')
<style>
.upload-section, .parse-content-section{background: var(--bs-body-bg); padding: 1rem 2rem 2rem 2rem; border-radius: 10px;}
.divider {display: flex;align-items: center;text-align: center;color: #6c757d;}
.divider::before,.divider::after {content: "";flex: 1;border-bottom: 1px solid #dee2e6;}
.divider:not(:empty)::before {margin-right: .75em;}
.divider:not(:empty)::after {margin-left: .75em;}
.nav-tabs .nav-link.active {background-color: #fff;border-color: #dee2e6 #dee2e6 #fff;font-weight: 600;}
::placeholder {color: #c7ccd0 !important;opacity: 1;}
.label-right {text-align: right;display: block;}
.form-action-buttons {
        display: flex;
        gap: 0.5rem;
        align-items: center;
    }
    
    @media (max-width: 768px) {
        .d-flex.justify-content-between.align-items-center {
            flex-direction: column;
            align-items: stretch !important;
        }
        
        .form-action-buttons {
            margin-top: 1rem;
            width: 100%;
            justify-content: flex-end;
        }
        
        #specTabs {
            width: 100%;
            margin-bottom: 1rem;
        }
    }
</style>
@endpush

@section('content')
<div class="container-fluid product-search dataTable-wrapper">
    <div class="card-header">
        <div class="title-add">
            <h1 class="page-title">Add Specifications</h1>
        </div>
        <p class="pt-2">Upload a specification document or paste text. AI will automatically extract and validate against FSANZ standards.</p>
    </div>

    <div class="card-body">
        <div id="step1-section">
            <form id="specificationForm" enctype="multipart/form-data">
                @csrf
                <div class="specification-add-section">
                    <!-- Upload section -->
                    <div class="upload-section">
                        <h3 class="pb-1"><span class="material-symbols-outlined">quick_reference_all</span>Upload Document</h3>
                        <p class="pt-2">Supports PDF. AI will extract nutritional info, allergens, and FSANZ compliance data.</p>
                        <!-- <input name="uploadimage" id="uploadimage" type="file" accept=".pdf"/> -->
                        <div class="dropzone" id="dzSpec" data-preview="fileListSpec">
                            <span class="material-symbols-outlined upload-icon">upload</span>
                            <p class="mt-1">Drag & drop files here or <span class="uploan-span">click to upload</span></p>
                            <input type="file" accept=".pdf" hidden>
                            <span class="mt-1">Accepted file formats: "pdf"</span>
                        </div>
                        <ul id="fileListSpec" class="list-group mt-2" style="width:50%"></ul>
                    </div>

                    <div class="divider mt-2 mb-2"><span>OR</span></div>
                    
                    <!-- Content section -->
                    <div class="parse-content-section">
                        <h3 class="pb-1">Paste Text</h3>
                        <p class="pt-2">Copy and paste specification information directly</p>
                        <textarea class="form-control ph-blue" name="parse_content" id="parse_content" rows="5" placeholder="Paste your spec sheet content here..."></textarea>          
                    </div>
                    
                    <!-- Footer section -->
                    <div class="specification-footer text-end mt-3">
                        <a href="{{route('batchbase_agent.specifications')}}"><button type="button" class="btn btn-secondary-blue">Cancel</button></a>
                        <button type="submit" id="processBtn" class="btn btn-secondary-blue">Process Specification</button>
                    </div>
                </div>  
            </form>
        </div>

        <!-- Step 2: Display parsed specification info -->
        <div id="step2-section" class="mt-4" style="display:none;">
            <form id="Addspecification" enctype="multipart/form-data">
            @csrf
                <h3><span class="material-symbols-outlined">assignment</span> Specification Details</h3>
                <p class="pt-2">Review the extracted data before saving.</p>

                <div id="parsedData" class="mt-3"></div>
                <input type="hidden" name="json_object" id="json_object">
                <!-- <div class="text-end mt-3">
                    <a href="{{route('specifications.add')}}"><button type="button" class="btn btn-secondary-blue">Back</button></a>
                    <button type="submit" id="saveSpecificationBtn" class="btn btn-primary">Save Specification</button>
                </div> -->
            </form>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets') }}/js/dropzone-core.js"></script>
<script src="{{ asset('assets') }}/js/dropzone-templates.js"></script>
<script src="{{ asset('assets') }}/js/dropzone-init.js"></script>
<script>
//     $(document).ready(function() {

//     // file input inside dzSpec
//     const $upload = $('#dzSpec').find('input[type="file"]');
//     const $textarea = $('#parse_content');
//     const $button = $('#processBtn');
//     const $fileList = $('#fileListSpec');

//     function toggleButton() {
//         const hasFiles = $fileList.children('li').length > 0;
//         const hasText  = $.trim($textarea.val()).length > 0;

//         $button.prop('disabled', !(hasFiles || hasText));
//     }

//     $upload.on('change', toggleButton);
//     $textarea.on('input', toggleButton);

//     toggleButton(); // initial state
// });


    $(document).on('submit', 'form#Addspecification', function (e) {
        e.preventDefault(); // prevent normal form submission

        const btn = $('#saveSpecificationBtn');
        const cText = btn.text();

        // ✅ Create FormData object (this collects all form inputs automatically)
        const form = document.getElementById('Addspecification');
        const data = new FormData(form);

        // Handle all checkboxes — convert "on" → "1", and add "0" if unchecked
        form.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
            const name = checkbox.name;
            if (!name) return; // skip unnamed inputs

            if (checkbox.checked) {
                // Checked checkbox → value = "1"
                data.set(name, '1');
            } else {
                // Unchecked → ensure "0" is included
                if (!data.has(name)) {
                    data.set(name, '0');
                }
            }
        });

        if (fileBuckets["dzProduct"] && fileBuckets["dzProduct"].length > 0) {
            fileBuckets["dzProduct"].forEach((item) => {
                data.append("image_file[]", item.file);
            });
        }


        $.ajax({
            type: "POST",
            url: "{{ route('specifications.store') }}",
            data: data,
            processData: false, // prevent jQuery from processing data
            contentType: false, // prevent jQuery from setting incorrect headers
            dataType: 'json',
            
            beforeSend: function () {

                if (btn.length) {
                    btn.text('Processing...');
                    btn.prop('disabled', true);
                }
               $('#AIloader').removeClass('d-none');
            },
            success: function (response) {
                $('#AIloader').addClass('d-none');
                if (response.status) {
                    Swal.fire({
                        icon: 'success',                        
                        text: response.message,
                        timer: 2000
                    }).then(() => {
                        window.location.href="{{ route('batchbase_agent.specifications') }}";
                    });
                }else{
                    Swal.fire({
                        icon: 'warning',
                        title: 'Warning!',
                        text: response.message
                    }); 
                }
            },
            complete: function () {
                $('#AIloader').addClass('d-none');

                if (btn.length) {
                    btn.text(cText);
                    btn.prop('disabled', false);
                }                
            },
            error: function(xhr) {
                $('#AIloader').addClass('d-none');
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


    $(document).on('submit', 'form#specificationForm', function (e) {
        
        e.preventDefault(); // prevent normal form submission

        const form = this;
        const btn = $('#processBtn');
        const cText = btn.text();

        // ✅ Create FormData object (this collects all form inputs automatically)
        const data = new FormData(form);

        if (fileBuckets["dzSpec"] && fileBuckets["dzSpec"].length > 0) {
            fileBuckets["dzSpec"].forEach((item) => {
                data.append("image_file", item.file);
            });
        }

        $.ajax({
            type: "POST",
            url: "{{ route('specifications.preview') }}",
            data: data,
            processData: false, // prevent jQuery from processing data
            contentType: false, // prevent jQuery from setting incorrect headers
            dataType: 'json',
           
            beforeSend: function () {
                if (btn.length) {
                    btn.text('Processing...');
                    btn.prop('disabled', true);
                }
                $('#AIloader').removeClass('d-none');
            },
            success: function (response) {
                $('#AIloader').addClass('d-none');
                if (response.status) {
                    let mainResponse = response.content;
                    document.getElementById('json_object').value = JSON.stringify(mainResponse);

                    // ✅ Step 1 done - enable Step 2
                    $(`#step1-section`).hide();
                    $('#step2-section').show();

                    const allergensArray = mainResponse.ingredients_allergens?.contains_allergens || [];
                    const allergensText = allergensArray.join(', ');

                    const additiveArray = mainResponse.ingredients_allergens?.food_additive_numbers || [];
                    const food_additive = additiveArray.join(', ');

                    const healthArray = mainResponse.aus_compliance?.health_claims || [];
                    const healthText = healthArray.join(', ');

                    const nutArray = mainResponse.aus_compliance?.nutrition_content_claims || [];
                    const nutritionText = nutArray.join(', ');

                    const warningArray = mainResponse.aus_compliance?.warning_statements || [];
                    const warningText = warningArray.join(', ');

                    const adviceArray = mainResponse.aus_compliance?.advisory_statements || [];
                    const adviceText = adviceArray.join(', ');

                    

                    // Example: fill extracted fields dynamically
                    let html = `
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <ul class="nav nav-tabs mb-0" id="specTabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="overview-tab" data-bs-toggle="tab" data-bs-target="#overview" type="button" role="tab">Overview</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#nutrition" type="button" role="tab">Nutrition</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#cool" type="button" role="tab">Cool</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#quality" type="button" role="tab">Quality</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#packaging" type="button" role="tab">Packaging</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#compliance" type="button" role="tab">Compliance</button>
                                </li>
                            </ul>
                            
                            <div class="form-action-buttons">
                                <a href="{{route('specifications.add')}}" class="btn btn-secondary-white">
                                    Cancel
                                </a>
                                <button type="submit" id="saveSpecificationBtn" class="btn btn-secondary-blue">
                                    <i class="material-symbols-outlined me-1" style="font-size: 18px;">save</i>
                                    Save
                                </button>
                            </div>
                        </div>
                        <div class="tab-content">
                            <div class="tab-pane fade show active" id="overview" role="tabpanel">
                                <div class="card p-4">
                                    <h5 class="mb-3 fw-semibold">Basic Information</h5>
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                        <label for="spec_name" class="form-label">Spec Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="spec_name" id="spec_name" placeholder="Enter spec name" value="${mainResponse.spec?.spec_name ?? ''}">
                                        </div>
                                        <div class="col-md-4">
                                        <label for="spec_sku" class="form-label">Spec Sku <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="spec_sku" id="spec_sku" placeholder="Enter SKU" value="${mainResponse.spec?.spec_sku ?? ''}">
                                        </div>
                                        <div class="col-md-4">
                                        <label for="spec_type" class="form-label">Specification Type <span class="text-danger">*</span></label>
                                        <select class="form-select" name="spec_type" id="spec_type">
                                            <option disabled>Select type</option>
                                            <option value="raw_material" selected>Raw Material</option>
                                            <option value="product">Finished Product</option>
                                            <option value="package_material">Packaging Material</option>
                                        </select>
                                        </div>
                                        <div class="col-md-4">
                                        <label for="spec_status" class="form-label">Status</label>
                                        <select class="form-select" name="spec_status" id="spec_status">
                                            <option disabled>Select status</option>
                                            <option value="draft" selected>Draft</option>
                                            <option value="active">Active</option>
                                            <option value="inactive">Inactive</option>
                                        </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label for="aus_regulatory_status" class="form-label">Australian Regulatory Status</label>
                                            <input type="text" class="form-control" name="aus_regulatory_status" id="aus_regulatory_status" placeholder="Enter status" value="${mainResponse.spec?.aus_regulatory_status ?? ''}">
                                        </div>

                                        <div class="col-md-4">
                                            <label for="supplier_name" class="form-label">Supplier Name</label>
                                            <input type="text" class="form-control" name="supplier_name" id="supplier_name" placeholder="Enter supplier name" value="${mainResponse.parties?.supplier_name ?? ''}">
                                        </div>
                                        <div class="col-12">
                                        <label for="description" class="form-label">Description</label>
                                        <textarea class="form-control" id="description" name="description" rows="3" placeholder="Enter product description">${mainResponse.spec?.description ?? ''}</textarea>
                                        </div>
                                        <div class="col-12">
                                            <label for="spec_url" class="form-label">Specification URL</label>
                                            <input type="text" class="form-control" name="spec_url" id="spec_url" placeholder="Enter specification URL">
                                        </div>
                                        <input type="hidden" name="spec_upload_type" value="${response.type}">
                                        <input type="hidden" name="file_name" value="${response.file_name}">
                                        <input name="default_image" id="default_image" type="hidden" value="" />
                                        <div class="col-12">
                                            <label class="form-label">Specification Image</label>
                                            <div class="dropzone" id="dzProduct" data-preview="fileList">
                                                <span class="material-symbols-outlined upload-icon">upload</span>
                                                <p class="mt-1">Drag & drop files here or <span class="uploan-span">click to upload</span></p>
                                                <input type="file"  accept=".png,.jpg,.jpeg" multiple hidden>
                                                <span class="mt-1">Accepted file formats: "png, jpg, jpeg"</span>
                                            </div>
                                            <ul class="list-group mt-2" id="fileList" style="width: 50%;"></ul>
                                        </div>
                                    </div>
                                </div>

                                <div class="card p-4">
                                    <h5 class="mb-3 fw-semibold">Manufacturer & Supply Chain</h5>
                                    <div class="row g-3">
                                        
                                        <div class="col-md-6">
                                            <label for="mfr_name" class="form-label">Manufacturer Name</label>
                                            <input type="text" class="form-control" name="manufacturer_name" id="manufacturer_name" placeholder="Enter name" value="${mainResponse.parties?.manufacturer_name ?? ''}">
                                        </div>
                                        <div class="col-md-6">
                                            <label for="mfr_contact" class="form-label">Manufacturer Contact</label>
                                            <input type="text" class="form-control" name="manufacturer_contact" id="manufacturer_contact" placeholder="Enter contact" value="${mainResponse.parties?.manufacturer_contact ?? ''}">
                                        </div>
                                        <div class="col-12">
                                            <label for="mfr_address" class="form-label">Manufacturer Address</label>
                                            <textarea class="form-control" id="manufacturer_address" name="manufacturer_address" rows="3" placeholder="Enter address">${mainResponse.parties?.manufacturer_address ?? ''}</textarea>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="distributor_name" class="form-label">Distributor Name</label>
                                            <input type="text" class="form-control" name="distributor_name" id="distributor_name" placeholder="Enter Name" value="${mainResponse.parties?.distributor_name ?? ''}">
                                        </div>
                                        <div class="col-md-6">
                                            <label for="distributor_contact" class="form-label">Distributor Contact</label>
                                            <input type="text" class="form-control" name="distributor_contact" id="distributor_contact" placeholder="Enter Contact" value="${mainResponse.parties?.distributor_contact ?? ''}">
                                        </div>
                                        <div class="col-md-4">
                                            <label for="compliance_officer" class="form-label">Compliance Officer Name</label>
                                            <input type="text" class="form-control" name="compliance_officer" id="compliance_officer" placeholder="Compliance Officer" value="${mainResponse.parties?.compliance_officer ?? ''}">
                                        </div>

                                        <div class="col-md-4">
                                            <label for="trace_gln" class="form-label">GLN</label>
                                            <input type="text" class="form-control" name="trace_gln" placeholder="Enter 13-digit GLN" value="${mainResponse.trace?.trace_gln ?? ''}">
                                        </div>

                                        <div class="col-md-4">
                                            <label for="trace_system" class="form-label">Traceability System</label>
                                            <input type="text" class="form-control" name="trace_system" placeholder="Specify system" value="${mainResponse.trace?.trace_system ?? ''}">
                                        </div>
                                    </div>
                                </div>

                                <div class="card p-4">
                                    <h5 class="mb-3 fw-semibold">Storage & Shelf Life</h5>
                                    <div class="row g-3">

                                        <div class="col-md-4">
                                            <label for="shelf_life_type" class="form-label">Shelf Life Type</label>
                                            <select class="form-select" name="shelf_life_type">
                                                <option value="best_before" selected>Best Before</option>
                                                <option value="use_by">Use By</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label for="shelf_life_value" class="form-label label-right">Shelf Life Value</label>
                                            <input type="number" step="0.01" class="form-control" name="shelf_life_value" placeholder="Enter number" value="${mainResponse.shelf_life_value ?? ''}">
                                        </div>
                                        <div class="col-md-4">
                                            <label for="shelf_life_unit" class="form-label">Shelf Life Unit</label>
                                            <select class="form-select" name="shelf_life_unit">
                                                <option value="days" selected>Days</option>
                                                <option value="weeks">Weeks</option>
                                                <option value="months">Months</option>
                                                <option value="years">Years</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label for="storage_temp_min_c" class="form-label label-right">Storage Temp Min (°C)</label>
                                            <input type="number" step="0.01" class="form-control" name="storage_temp_min_c" placeholder="Enter °C" value="${mainResponse.storage?.storage_temp_min_c ?? ''}">
                                        </div>
                                        <div class="col-md-3">
                                            <label for="storage_temp_max_c" class="form-label label-right">Storage Temp Max (°C)</label>
                                            <input type="number" step="0.01" class="form-control" name="storage_temp_max_c" placeholder="Enter °C" value="${mainResponse.storage?.storage_temp_max_c ?? ''}">
                                        </div>
                                        <div class="col-md-3">
                                            <label for="storage_humidity_min_percent" class="form-label label-right">Storage Humidity Min (%)</label>
                                            <input type="number" step="0.01" class="form-control" name="storage_humidity_min_percent" placeholder="Enter %" value="${mainResponse.storage?.storage_humidity_min_percent ?? ''}">
                                        </div>
                                        <div class="col-md-3">
                                            <label for="storage_humidity_max_percent" class="form-label label-right">Storage Humidity Max (%)</label>
                                            <input type="number" step="0.01" class="form-control" name="storage_humidity_max_percent" placeholder="Enter %" value="${mainResponse.storage?.storage_humidity_max_percent ?? ''}">
                                        </div>
                                        
                                        <div class="col-12">
                                            <label for="storage_conditions" class="form-label">Storage Conditions</label>
                                            <textarea class="form-control" name="storage_conditions" rows="3" placeholder="Describe storage requirements">${mainResponse.storage?.storage_conditions ?? ''}</textarea>
                                        </div>
                                        <div class="col-12">
                                            <label for="handling_instructions" class="form-label">Handling Instructions</label>
                                            <textarea class="form-control" name="handling_instructions" rows="3" placeholder="Enter instructions">${mainResponse.handling_instructions ?? ''}</textarea>
                                        </div>
                                        <div class="col-12">
                                            <label for="disposal_instructions" class="form-label">Disposal Instructions</label>
                                            <textarea class="form-control" name="disposal_instructions" rows="3" placeholder="Enter disposal instructions">${mainResponse.disposal_instructions ?? ''}</textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="tab-pane fade" id="nutrition" role="tabpanel">
                                <div class="card p-4">
                                    <h5 class="mb-3 fw-semibold">Serving & Basis Information</h5>
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <label for="nutr_serving_size_g" class="form-label label-right">Serving Size</label>
                                            <input type="number" step="0.01" class="form-control" name="nutr_serving_size_g" id="nutr_serving_size_g" placeholder="e.g., 100g" value="${mainResponse.nutritional_info?.nutr_serving_size_g ?? ''}">
                                        </div>
                                        <div class="col-md-4">
                                            <label for="nutr_servings_per_container" class="form-label label-right">Servings Per Container</label>
                                            <input type="number" step="0.01" class="form-control" name="nutr_servings_per_container" id="nutr_servings_per_container" placeholder="e.g., 4 servings" value="${mainResponse.nutritional_info?.nutr_servings_per_container ?? ''}">
                                        </div>
                                        <div class="col-md-4">
                                            <label for="nutritional_basis" class="form-label">Nutritional Basis</label>
                                            <select class="form-select" name="nutritional_basis" id="nutritional_basis">
                                                <option value="g" selected>Per 100g</option>
                                                <option value="ml">Per 100ml</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="card p-4">
                                    <h5 class="mb-3 fw-semibold">Macronutrients (per 100g/100ml)</h5>
                                    <div class="row g-3">
                                        <div class="col-md-3">
                                            <label for="nutr_energy_kj" class="form-label label-right">Energy (kJ)</label>
                                            <input type="number" step="0.01" class="form-control" id="nutr_energy_kj" name="nutr_energy_kj" placeholder="Enter kJ value" value="${mainResponse.nutritional_info?.nutr_energy_kj ?? ''}">
                                        </div>
                                        <div class="col-md-3">
                                            <label for="nutr_protein_g" class="form-label label-right">Protein (g)</label>
                                            <input type="number" step="0.01" class="form-control" name="nutr_protein_g" id="nutr_protein_g" placeholder="Enter grams" value="${mainResponse.nutritional_info?.nutr_protein_g ?? ''}">
                                        </div>
                                        <div class="col-md-3">
                                            <label for="nutr_fat_total_g" class="form-label label-right">Total Fat (g)</label>
                                            <input type="number" step="0.01" class="form-control" name="nutr_fat_total_g" id="nutr_fat_total_g" placeholder="Enter grams" value="${mainResponse.nutritional_info?.nutr_fat_total_g ?? ''}">
                                        </div>
                                        <div class="col-md-3">
                                            <label for="nutr_fat_saturated_g" class="form-label label-right">Saturated Fat (g)</label>
                                            <input type="number" step="0.01" class="form-control" name="nutr_fat_saturated_g" id="nutr_fat_saturated_g" placeholder="Enter grams" value="${mainResponse.nutritional_info?.nutr_fat_saturated_g ?? ''}">
                                        </div>
                                        <div class="col-md-3">
                                            <label for="nutr_carbohydrate_g" class="form-label label-right label-right">Carbohydrate (g)</label>
                                            <input type="number" step="0.01" class="form-control" name="nutr_carbohydrate_g" id="nutr_carbohydrate_g" placeholder="Enter grams" value="${mainResponse.nutritional_info?.nutr_carbohydrate_g ?? ''}">
                                        </div>
                                        <div class="col-md-3">
                                            <label for="nutr_sugars_g" class="form-label label-right">Sugars (g)</label>
                                            <input type="number" step="0.01" class="form-control" name="nutr_sugars_g" id="nutr_sugars_g" placeholder="Enter grams" value="${mainResponse.nutritional_info?.nutr_sugars_g ?? ''}">
                                        </div>
                                        <div class="col-md-3">
                                            <label for="nutr_added_sugars_g" class="form-label label-right">Added Sugars (g)</label>
                                            <input type="number" step="0.01" class="form-control" name="nutr_added_sugars_g" id="nutr_added_sugars_g" placeholder="Enter grams" value="${mainResponse.nutritional_info?.nutr_added_sugars_g ?? ''}">
                                        </div>
                                        <div class="col-md-3">
                                            <label for="nutr_fat_trans_g" class="form-label label-right">Trans Fat (g)</label>
                                            <input type="number" step="0.01" class="form-control" name="nutr_fat_trans_g" id="nutr_fat_trans_g" placeholder="Enter grams" value="${mainResponse.nutritional_info?.nutr_fat_trans_g ?? ''}">
                                        </div>
                                        <div class="col-md-3">
                                            <label for="nutr_dietary_fiber_g" class="form-label label-right">Dietary Fiber (g)</label>
                                            <input type="number" step="0.01" class="form-control" name="nutr_dietary_fiber_g" id="nutr_dietary_fiber_g" placeholder="Enter grams" value="${mainResponse.nutritional_info?.nutr_dietary_fiber_g ?? ''}">
                                        </div>
                                        <div class="col-md-3">
                                            <label for="nutr_sodium_mg" class="form-label label-right">Sodium (mg)</label>
                                            <input type="number" step="0.01" class="form-control" name="nutr_sodium_mg" id="nutr_sodium_mg" placeholder="Enter mg" value="${mainResponse.nutritional_info?.nutr_sodium_mg ?? ''}">
                                        </div>
                                        <div class="col-md-3">
                                            <label for="nutr_cholesterol_mg" class="form-label label-right">Cholesterol (mg)</label>
                                            <input type="number" step="0.01" class="form-control" name="nutr_cholesterol_mg" id="nutr_cholesterol_mg" placeholder="Enter mg" value="${mainResponse.nutritional_info?.nutr_cholesterol_mg ?? ''}">
                                        </div>
                                        <div class="col-md-3">
                                            <label for="nutr_gluten_content" class="form-label">Gluten Content</label>
                                            <input type="text" class="form-control" name="nutr_gluten_content" id="nutr_gluten_content" placeholder="Specify gluten status" value="${mainResponse.nutritional_info?.nutr_gluten_content ?? ''}">
                                        </div>
                                    </div>
                                </div>
                                <div class="card p-4">
                                    <h5 class="mb-3 fw-semibold">Micronutrients (Optional)</h5>
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <label for="nutr_vitamin_d_mcg" class="form-label label-right">Vitamin D (mcg)</label>
                                            <input type="number" step="0.01" class="form-control" name="nutr_vitamin_d_mcg" id="nutr_vitamin_d_mcg" placeholder="Enter mcg" value="${mainResponse.nutritional_info?.nutr_vitamin_d_mcg ?? ''}">
                                        </div>
                                        <div class="col-md-4">
                                            <label for="nutr_calcium_mg" class="form-label label-right">Calcium (mg)</label>
                                            <input type="number" step="0.01" class="form-control" name="nutr_calcium_mg" id="nutr_calcium_mg" placeholder="Enter mg" value="${mainResponse.nutritional_info?.nutr_calcium_mg ?? ''}">
                                        </div>
                                        <div class="col-md-4">
                                            <label for="nutr_iron_mg" class="form-label label-right">Iron (mg)</label>
                                            <input type="number" step="0.01" class="form-control" name="nutr_iron_mg" id="nutr_iron_mg" placeholder="Enter mg" value="${mainResponse.nutritional_info?.nutr_iron_mg ?? ''}">
                                        </div>
                                        <div class="col-md-4">
                                            <label for="nutr_potassium_mg" class="form-label label-right">Potassium (mg)</label>
                                            <input type="number" step="0.01" class="form-control" name="nutr_potassium_mg" id="nutr_potassium_mg" placeholder="Enter mg" value="${mainResponse.nutritional_info?.nutr_potassium_mg ?? ''}">
                                        </div>
                                        <div class="col-md-4">
                                            <label for="phys_specific_gravity" class="form-label label-right">Specific Gravity</label>
                                            <input type="number" step="0.01" class="form-control" name="phys_specific_gravity" placeholder="Enter value" value="${mainResponse.physical_specs?.phys_specific_gravity ?? ''}">
                                        </div>
                                    </div>
                                </div>
                                <div class="card p-4">
                                    <h5 class="mb-3 fw-semibold">Ingredients & Allergens</h5>
                                    <div class="row g-3">
                                        <div class="col-12">
                                            <label for="ing_ingredient_list" class="form-label">Ingredient List</label>
                                            <textarea class="form-control" name="ing_ingredient_list" rows="3" placeholder="List all ingredients">${mainResponse.ingredients_allergens?.ing_ingredient_list ?? ''}</textarea>
                                        </div>
                                        <div class="col-12">
                                            <label for="allergen_statement" class="form-label">Allergen Statement</label>
                                            <textarea class="form-control" name="allergen_statement" rows="3" placeholder="Enter allergen statement">${allergensText}</textarea>
                                        </div>
                                        <div class="col-12">
                                            <label for="allergen_fsanz_declaration" class="form-label">FSANZ Allergen Declaration</label>
                                            <textarea class="form-control" name="allergen_fsanz_declaration" rows="3" placeholder="FSANZ format declaration">${mainResponse.ingredients_allergens?.allergen_fsanz_declaration ?? ''}</textarea>
                                        </div>
                                        <div class="col-12">
                                            <label for="ing_percentage_labelling" class="form-label">Percentage Labelling</label>
                                            <textarea class="form-control" name="ing_percentage_labelling" rows="3" placeholder="E.g., Contains 70% Beef">${mainResponse.ingredients_allergens?.percentage_labelling ?? ''}</textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="tab-pane fade" id="cool" role="tabpanel">
                                <div class="card p-4">
                                    <h5 class="mb-3 fw-semibold">Country of Origin Labelling (COOL)</h5>
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <label for="cool_primary_country" class="form-label">Primary Country</label>
                                            <input type="text" class="form-control" name="cool_primary_country" placeholder="Enter country" value="${mainResponse.cool?.cool_primary_country ?? ''}">
                                        </div>                      
                                        <div class="col-md-4">
                                            <label for="cool_label_type" class="form-label">Label Type</label>
                                            <input type="text" class="form-control" name="cool_label_type" placeholder="Specify label type" value="${mainResponse.cool?.cool_label_type ?? ''}">
                                        </div>
                                        <div class="col-md-4">
                                            <label for="cool_fsanz_standard_ref" class="form-label">FSANZ Standard Reference</label>
                                            <input type="text" class="form-control" name="cool_fsanz_standard_ref" placeholder="e.g., Standard 1.2.11" value="${mainResponse.cool?.cool_fsanz_standard_ref ?? ''}">
                                        </div>
                                        <div class="col-12">
                                            <label for="cool_origin_declaration" class="form-label">Origin Declaration</label>
                                            <textarea class="form-control" name="cool_origin_declaration" rows="3" placeholder="Enter declaration">${mainResponse.cool?.cool_origin_declaration ?? ''}</textarea>
                                        </div>
                                        <div class="col-12">
                                            <label for="cool_date_marking_requirement" class="form-label">Date Marking Requirements</label>
                                            <textarea class="form-control" name="cool_date_marking_requirement" rows="3" placeholder="Describe requirements">${mainResponse.cool?.cool_date_marking_requirement ?? ''}</textarea>
                                        </div>
                                    </div>
                                </div>
                                <div class="card p-4">
                                    <h5 class="mb-3 fw-semibold">Australian Claims & Content</h5>
                                    <div class="row mb-1 gy-2 mt-2">
                                        <div class="col-md-4 col-6">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="cool_aus_made_claim"  ${mainResponse.cool?.cool_aus_made_claim ? 'checked' : ''}>
                                            <label class="form-check-label ms-1" for="cool_aus_made_claim">Australian Made</label>
                                        </div>
                                        </div>
                                        <div class="col-md-4 col-6">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="cool_aus_grown_claim" ${mainResponse.cool?.cool_aus_grown_claim ? 'checked' : ''}>
                                            <label class="form-check-label ms-1" for="cool_aus_grown_claim">Australian Grown</label>
                                        </div>
                                        </div>
                                        <div class="col-md-4 col-6">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="cool_aus_owned_claim" ${mainResponse.cool?.cool_aus_owned_claim ? 'checked' : ''}>
                                            <label class="form-check-label ms-1" for="cool_aus_owned_claim">Australian Owned</label>
                                        </div>
                                        </div>
                                    </div>
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label for="cool_percentage_australia" class="form-label label-right">Australian Content %</label>
                                            <input type="number" step="0.01" class="form-control" name="cool_percentage_australia" placeholder="E.g., 0.85 for 85%" value="${mainResponse.cool?.cool_percentage_australia ?? ''}">
                                        </div>
                                        <div class="col-md-6">
                                            <label for="cool_calculation_details" class="form-label">Calculation Method</label>
                                            <input type="text" class="form-control" name="cool_calculation_details" placeholder="Describe method" value="${mainResponse.cool?.cool_calculation_details ?? ''}">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="tab-pane fade" id="quality" role="tabpanel">
                                <div class="card p-4">
                                    <h5 class="mb-3 fw-semibold">Physical Specifications</h5>
                                    <div class="row g-3">
                                        <div class="col-6">
                                            <label for="phys_appearance" class="form-label">Appearance</label>
                                            <textarea class="form-control" name="phys_appearance" rows="3" placeholder="Describe appearance">${mainResponse.physical_specs?.phys_appearance ?? ''}</textarea>
                                        </div>
                                        <div class="col-6">
                                            <label for="phys_color" class="form-label">Color</label>
                                            <textarea class="form-control" name="phys_color" rows="3" placeholder="Describe color">${mainResponse.physical_specs?.phys_color ?? ''}</textarea>
                                        </div>
                                        <div class="col-6">
                                            <label for="phys_odor" class="form-label">Odor</label>
                                            <textarea class="form-control" name="phys_odor" rows="3" placeholder="Describe odor">${mainResponse.physical_specs?.phys_odor ?? ''}</textarea>
                                        </div>
                                        <div class="col-6">
                                            <label for="phys_texture" class="form-label">Texture</label>
                                            <textarea class="form-control" name="phys_texture" rows="3" placeholder="Describe texture">${mainResponse.physical_specs?.phys_texture ?? ''}</textarea>
                                        </div>
                                        <div class="col-md-3">
                                            <label for="phys_ph_level" class="form-label label-right">pH Level</label>
                                            <input type="number" step="0.01" class="form-control" name="phys_ph_level" placeholder="6.5, 4.0-4.5, 7.2" value="${mainResponse.physical_specs?.phys_ph_level ?? ''}">
                                        </div>
                                        <div class="col-md-3">
                                            <label for="phys_moisture_percent" class="form-label label-right">Moisture Content (%)</label>
                                            <input type="number" step="0.01" class="form-control" name="phys_moisture_percent" placeholder="12.5, <10, 14-16" value="${mainResponse.physical_specs?.phys_moisture_percent ?? ''}">
                                        </div>
                                        <div class="col-md-3">
                                            <label for="phys_water_activity" class="form-label label-right">Water Activity</label>
                                            <input type="number" step="0.01" class="form-control" name="phys_water_activity" placeholder="0.65, <0.60, 0.70-0.75" value="${mainResponse.physical_specs?.phys_water_activity ?? ''}">
                                        </div>
                                        <div class="col-md-3">
                                            <label for="phys_density_g_ml" class="form-label label-right">Density</label>
                                            <input type="number" step="0.01" class="form-control" name="phys_density_g_ml" placeholder="0.85, 1.05, 0.95-1.00" value="${mainResponse.physical_specs?.phys_density_g_ml ?? ''}">
                                        </div>
                                        <div class="col-md-6">
                                            <label for="phys_specific_gravity" class="form-label label-right">Specific Gravity</label>
                                            <input type="number" step="0.01" class="form-control" name="phys_specific_gravity" placeholder="1.05, 0.98, 1.10-1.15" value="${mainResponse.physical_specs?.specific_gravity ?? ''}">
                                        </div>
                                        <div class="col-6">
                                            <label for="phys_viscosity_cps" class="form-label">Viscosity</label>
                                            <textarea class="form-control" name="phys_viscosity_cps" rows="3" placeholder="2500-3500 cP, Pourable, Thick">${mainResponse.physical_specs?.phys_viscosity_cps ?? ''}</textarea>
                                        </div>
                                    </div>
                                </div>

                                <div class="card p-4">
                                    <h5 class="mb-3 fw-semibold">Microbiological Specifications</h5>
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <label for="micro_total_plate_count_cfu_g_max" class="form-label">Total Plate Count</label>
                                            <input type="text" class="form-control" name="micro_total_plate_count_cfu_g_max" placeholder="e.g., <10,000 cfu/g" value="${mainResponse.microbiological_specs?.micro_total_plate_count_cfu_g_max ?? ''}">
                                        </div>
                                        <div class="col-md-4">
                                            <label for="micro_yeast_mold_cfu_g_max" class="form-label">Yeast & Mold</label>
                                            <input type="text" class="form-control" name="micro_yeast_mold_cfu_g_max" placeholder="e.g., <100 cfu/g" value="${mainResponse.microbiological_specs?.micro_yeast_mold_cfu_g_max ?? ''}">
                                        </div>
                                        <div class="col-md-4">
                                            <label for="micro_coliforms_cfu_g_max" class="form-label">Coliforms</label>
                                            <input type="text" class="form-control" name="micro_coliforms_cfu_g_max" placeholder="e.g., <10 cfu/g" value="${mainResponse.microbiological_specs?.micro_coliforms_cfu_g_max ?? ''}">
                                        </div>
                                        <div class="col-md-3">
                                            <label for="micro_e_coli_cfu_g_max" class="form-label">E. Coli</label>
                                            <input type="text" class="form-control" name="micro_e_coli_cfu_g_max" placeholder="e.g., Absent in 25g" value="${mainResponse.microbiological_specs?.micro_e_coli_cfu_g_max ?? ''}">
                                        </div>
                                        <div class="col-md-3">
                                            <label for="micro_salmonella_absent_in_g" class="form-label">Salmonella</label>
                                            <input type="text" class="form-control" name="micro_salmonella_absent_in_g" placeholder="e.g., Absent in 25g" value="${mainResponse.microbiological_specs?.micro_salmonella_absent_in_g ?? ''}">
                                        </div>
                                        <div class="col-md-3">
                                            <label for="micro_listeria_absent_in_g" class="form-label">Listeria</label>
                                            <input type="text" class="form-control" name="micro_listeria_absent_in_g" placeholder="e.g., Absent in 25g" value="${mainResponse.microbiological_specs?.micro_listeria_absent_in_g ?? ''}">
                                        </div>
                                        <div class="col-md-3">
                                            <label for="micro_staphylococcus_cfu_g_max" class="form-label">Staphylococcus</label>
                                            <input type="text" class="form-control" name="micro_staphylococcus_cfu_g_max" placeholder="e.g., <100 cfu/g" value="${mainResponse.microbiological_specs?.micro_staphylococcus_cfu_g_max ?? ''}">
                                        </div>
                                    </div>
                                </div>
                                <div class="card p-4">
                                    <h5 class="mb-3 fw-semibold">Heavy Metals (mg/kg)</h5>
                                    <div class="row g-3">
                                        <div class="col-md-3">
                                            <label for="chem_metal_lead" class="form-label">Lead (Pb)</label>
                                            <input type="text" class="form-control" name="chem_metal_lead" placeholder="e.g., <0.1 mg/kg" value="${mainResponse.chem_metal_lead ?? ''}">
                                        </div>
                                        <div class="col-md-3">
                                            <label for="chem_metal_cadmium" class="form-label">Cadmium (Cd)</label>
                                            <input type="text" class="form-control" name="chem_metal_cadmium" placeholder="e.g., <0.05 mg/kg" value="${mainResponse.chem_metal_cadmium ?? ''}">
                                        </div>
                                        <div class="col-md-3">
                                            <label for="chem_metal_mercury" class="form-label">Mercury (Hg)</label>
                                            <input type="text" class="form-control" name="chem_metal_mercury" placeholder="e.g., <0.5 mg/kg" value="${mainResponse.chem_metal_mercury ?? ''}">
                                        </div>
                                        <div class="col-md-3">
                                            <label for="chem_metal_arsenic" class="form-label">Arsenic (As)</label>
                                            <input type="text" class="form-control" name="chem_metal_arsenic" placeholder="e.g., <0.1 mg/kg" value="${mainResponse.chem_metal_arsenic ?? ''}">
                                        </div>
                                        <div class="col-md-3">
                                            <label for="chem_metal_tin" class="form-label">Tin (Sn)</label>
                                            <input type="text" class="form-control" name="chem_metal_tin" placeholder="e.g., <250 mg/kg" value="${mainResponse.chem_metal_tin ?? ''}">
                                        </div>
                                    </div>
                                </div>

                                <div class="card p-4">
                                    <h5 class="mb-3 fw-semibold">Pesticide Residues (mg/kg)</h5>
                                    <div class="row g-3">
                                        <div class="col-md-3">
                                            <label for="chem_pest_glyphosate" class="form-label">Glyphosate</label>
                                            <input type="text" class="form-control" name="chem_pest_glyphosate" placeholder="e.g., <0.05 mg/kg" value="${mainResponse.chem_pest_glyphosate ?? ''}">
                                        </div>
                                        <div class="col-md-3">
                                            <label for="chem_pest_chlorpyrifos" class="form-label">Chlorpyrifos</label>
                                            <input type="text" class="form-control" name="chem_pest_chlorpyrifos" placeholder="e.g., <0.5 mg/kg" value="${mainResponse.chem_pest_chlorpyrifos ?? ''}">
                                        </div>
                                        <div class="col-md-3">
                                            <label for="chem_pest_malathion" class="form-label">Malathion</label>
                                            <input type="text" class="form-control" name="chem_pest_malathion" placeholder="e.g., <0.1 mg/kg" value="${mainResponse.chem_pest_malathion ?? ''}">
                                        </div>
                                        <div class="col-md-3">
                                            <label for="chem_pest_permethrin" class="form-label">Permethrin</label>
                                            <input type="text" class="form-control" name="chem_pest_permethrin" placeholder="e.g., <0.1 mg/kg" value="${mainResponse.chem_pest_permethrin ?? ''}">
                                        </div>
                                        <div class="col-md-3">
                                            <label for="chem_pest_imazalil" class="form-label">Imazalil</label>
                                            <input type="text" class="form-control" name="chem_pest_imazalil" placeholder="e.g., <5.0 mg/kg" value="${mainResponse.chem_pest_imazalil ?? ''}">
                                        </div>
                                    </div>
                                </div>

                                <div class="card p-4">
                                    <h5 class="mb-3 fw-semibold">Mycotoxins (μg/kg)</h5>
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <label for="chem_mycotoxin_aflatoxin_b1" class="form-label">Aflatoxin B1</label>
                                            <input type="text" class="form-control" name="chem_mycotoxin_aflatoxin_b1" placeholder="e.g., <2 µg/kg" value="${mainResponse.chem_mycotoxin_aflatoxin_b1 ?? ''}">
                                        </div>
                                        <div class="col-md-4">
                                            <label for="chem_mycotoxin_aflatoxin_total" class="form-label">Aflatoxin Total</label>
                                            <input type="text" class="form-control" name="chem_mycotoxin_aflatoxin_total" placeholder="e.g., <4 µg/kg" value="${mainResponse.chem_mycotoxin_aflatoxin_total ?? ''}">
                                        </div>
                                        <div class="col-md-4">
                                            <label for="chem_mycotoxin_ochratoxin_a" class="form-label">Ochratoxin A</label>
                                            <input type="text" class="form-control" name="chem_mycotoxin_ochratoxin_a" placeholder="e.g., <4 µg/kg" value="${mainResponse.chem_mycotoxin_ochratoxin_a ?? ''}">
                                        </div>
                                        <div class="col-md-4">
                                            <label for="chem_mycotoxin_deoxynivalenol" class="form-label">Deoxynivalenol (DON)</label>
                                            <input type="text" class="form-control" name="chem_mycotoxin_deoxynivalenol" placeholder="e.g., <100 µg/kg" value="${mainResponse.chem_mycotoxin_deoxynivalenol ?? ''}">
                                        </div>
                                        <div class="col-md-4">
                                            <label for="chem_mycotoxin_zearalenone" class="form-label">Zearalenone</label>
                                            <input type="text" class="form-control" name="chem_mycotoxin_zearalenone" placeholder="e.g., <200 µg/kg" value="${mainResponse.chem_mycotoxin_zearalenone ?? ''}">
                                        </div>
                                        <div class="col-md-4">
                                            <label for="chem_mycotoxin_patulin" class="form-label">Patulin</label>
                                            <input type="text" class="form-control" name="chem_mycotoxin_patulin" placeholder="e.g., <50 µg/kg" value="${mainResponse.chem_mycotoxin_patulin ?? ''}">
                                        </div>
                                    </div>
                                </div>

                                <div class="card p-4">
                                    <h5 class="mb-3 fw-semibold">Preservatives (mg/kg)</h5>
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <label for="chem_pres_sodium_benzoate" class="form-label">Sodium Benzoate</label>
                                            <input type="text" class="form-control" name="chem_pres_sodium_benzoate" placeholder="Specify content" value="${mainResponse.chem_pres_sodium_benzoate ?? ''}">
                                        </div>
                                        <div class="col-md-4">
                                            <label for="chem_pres_potassium_sorbate" class="form-label">Potassium Sorbate</label>
                                            <input type="text" class="form-control" name="chem_pres_potassium_sorbate" placeholder="Specify content" value="${mainResponse.chem_pres_potassium_sorbate ?? ''}">
                                        </div>
                                        <div class="col-md-4">
                                            <label for="chem_pres_sodium_metabisulfite" class="form-label">Sodium Metabisulfite</label>
                                            <input type="text" class="form-control" name="chem_pres_sodium_metabisulfite" placeholder="Specify content" value="${mainResponse.chem_pres_sodium_metabisulfite ?? ''}">
                                        </div>
                                        <div class="col-md-4">
                                            <label for="chem_pres_sulfur_dioxide" class="form-label">Sulfur Dioxide</label>
                                            <input type="text" class="form-control" name="chem_pres_sulfur_dioxide" placeholder="Specify content" value="${mainResponse.chem_pres_sulfur_dioxide ?? ''}">
                                        </div>
                                        <div class="col-md-4">
                                            <label for="chem_pres_sodium_nitrite" class="form-label">Sodium Nitrite</label>
                                            <input type="text" class="form-control" name="chem_pres_sodium_nitrite" placeholder="Specify content" value="${mainResponse.chem_pres_sodium_nitrite ?? ''}">
                                        </div>
                                        <div class="col-md-4">
                                            <label for="chem_pres_calcium_propionate" class="form-label">Calcium Propionate</label>
                                            <input type="text" class="form-control" name="chem_pres_calcium_propionate" placeholder="Specify content" value="${mainResponse.chem_pres_calcium_propionate ?? ''}">
                                        </div>
                                    </div>
                                </div>

                                <div class="card p-4">
                                    <h5 class="mb-3 fw-semibold">Additives (mg/kg)</h5>
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <label for="chem_add_tartrazine" class="form-label">Tartrazine</label>
                                            <input type="text" class="form-control" name="chem_add_tartrazine" placeholder="Specify content" value="${mainResponse.chem_add_tartrazine ?? ''}">
                                        </div>
                                        <div class="col-md-4">
                                            <label for="chem_add_sunset_yellow" class="form-label">Sunset Yellow</label>
                                            <input type="text" class="form-control" name="chem_add_sunset_yellow" placeholder="Specify content" value="${mainResponse.chem_add_sunset_yellow ?? ''}">
                                        </div>
                                        <div class="col-md-4">
                                            <label for="chem_add_cochineal" class="form-label">Cochineal</label>
                                            <input type="text" class="form-control" name="chem_add_cochineal" placeholder="Specify content" value="${mainResponse.chem_add_cochineal ?? ''}">
                                        </div>
                                        <div class="col-md-4">
                                            <label for="chem_add_citric_acid" class="form-label">Citric Acid</label>
                                            <input type="text" class="form-control" name="chem_add_citric_acid" placeholder="Specify content" value="${mainResponse.chem_add_citric_acid ?? ''}">
                                        </div>
                                        <div class="col-md-4">
                                            <label for="chem_add_monosodium_glutamate" class="form-label">MSG</label>
                                            <input type="text" class="form-control" name="chem_add_monosodium_glutamate" placeholder="Specify content" value="${mainResponse.chem_add_monosodium_glutamate ?? ''}">
                                        </div>
                                        <div class="col-md-4">
                                            <label for="chem_add_ascorbic_acid" class="form-label">Ascorbic Acid</label>
                                            <input type="text" class="form-control" name="chem_add_ascorbic_acid" placeholder="Specify content" value="${mainResponse.chem_add_ascorbic_acid ?? ''}">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="tab-pane fade" id="packaging" role="tabpanel">
                                <div class="card p-4">
                                    <h5 class="mb-3 fw-semibold">Primary Packaging</h5>
                                    <div class="row g-3">
                                        <div class="col-md-3">
                                            <label for="pack_primary_type" class="form-label">Package Type</label>
                                            <input type="text" class="form-control" name="pack_primary_type" placeholder="e.g., Bottle" value="${mainResponse.packaging?.pack_primary_type ?? ''}">
                                        </div>
                                        <div class="col-md-3">
                                            <label for="pack_primary_material" class="form-label">Material</label>
                                            <input type="text" class="form-control" name="pack_primary_material" placeholder="e.g., PET" value="${mainResponse.packaging?.pack_primary_material ?? ''}">
                                        </div>
                                        <div class="col-md-3">
                                            <label for="pack_primary_dimensions_mm" class="form-label">Dimensions</label>
                                            <input type="text" class="form-control" name="pack_primary_dimensions_mm" placeholder="Enter dimensions" value="${mainResponse.packaging?.pack_primary_dimensions_mm ?? ''}">
                                        </div>
                                        <div class="col-md-3">
                                            <label for="pack_primary_weight_g" class="form-label label-right">Weight</label>
                                            <input type="number" step="0.01" class="form-control" name="pack_primary_weight_g" placeholder="Enter weight in grams" value="${mainResponse.packaging?.pack_primary_weight_g ?? ''}">
                                        </div>
                                    </div>
                                </div>
                                <div class="card p-4">
                                    <h5 class="mb-3 fw-semibold">Secondary Packaging & Case</h5>
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <label for="pack_secondary_type" class="form-label">Secondary Type</label>
                                            <input type="text" class="form-control" name="pack_secondary_type" placeholder="e.g., Shrink wrap" value="${mainResponse.packaging?.pack_secondary_type ?? ''}">
                                        </div>
                                        <div class="col-md-4">
                                            <label for="pack_secondary_material" class="form-label">Secondary Material</label>
                                            <input type="text" class="form-control" name="pack_secondary_material" placeholder="e.g., LDPE" value="${mainResponse.packaging?.pack_secondary_material ?? ''}">
                                        </div>
                                        <div class="col-md-4">
                                            <label for="pack_secondary_dimensions_mm" class="form-label">Secondary Dimensions</label>
                                            <input type="text" class="form-control" name="pack_secondary_dimensions_mm" placeholder="Enter dimensions" value="${mainResponse.packaging?.pack_secondary_dimensions_mm ?? ''}">
                                        </div>
                                        <div class="col-md-3">
                                            <label for="pack_units_per_secondary" class="form-label label-right">Units/Secondary</label>
                                            <input type="number" step="0.01" class="form-control" name="pack_units_per_secondary" placeholder="Enter count" value="${mainResponse.packaging?.pack_units_per_secondary ?? ''}">
                                        </div>
                                        <div class="col-md-3">
                                            <label for="pack_units_per_case" class="form-label label-right">Units/Case</label>
                                            <input type="number" step="0.01" class="form-control" name="pack_units_per_case" placeholder="Enter count" value="${mainResponse.packaging?.pack_units_per_case ?? ''}">
                                        </div>
                                        <div class="col-md-3">
                                            <label for="pack_case_dimensions_mm" class="form-label">Case Dimensions</label>
                                            <input type="text" class="form-control" name="pack_case_dimensions_mm" placeholder="Enter L×W×H" value="${mainResponse.packaging?.pack_case_dimensions_mm ?? ''}">
                                        </div>
                                        <div class="col-md-3">
                                            <label for="pack_case_weight_g" class="form-label label-right">Case Weight</label>
                                            <input type="number" step="0.01" class="form-control" name="pack_case_weight_g" placeholder="Enter kg" value="${mainResponse.packaging?.pack_case_weight_g ?? ''}">
                                        </div>
                                    </div>
                                </div>
                                <div class="card p-4">
                                    <h5 class="mb-3 fw-semibold">Barcodes & Identification</h5>
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <label for="id_gtin_13" class="form-label">GTIN-13 (Consumer Unit)</label>
                                            <input type="text" class="form-control" name="id_gtin_13" placeholder="Enter 13-digit GTIN" value="${mainResponse.identifiers?.id_gtin_13 ?? ''}">
                                        </div>
                                        <div class="col-md-4">
                                            <label for="id_gtin_14" class="form-label">GTIN-14 (Case)</label>
                                            <input type="text" class="form-control" name="id_gtin_14" placeholder="Enter 14-digit GTIN" value="${mainResponse.identifiers?.id_gtin_14 ?? ''}">
                                        </div>
                                        <div class="col-md-4">
                                            <label for="id_sscc" class="form-label">SSCC (Pallet)</label>
                                            <input type="text" class="form-control" name="id_sscc" placeholder="Enter 18-digit SSCC" value="${mainResponse.identifiers?.id_sscc ?? ''}">
                                        </div>
                                        <div class="col-md-4">
                                            <label for="id_barcode_type" class="form-label">Barcode Type</label>
                                            <select class="form-select" name="id_barcode_type">
                                                <option disabled>Select type</option>
                                                <option value="1d" selected>1D</option>
                                                <option value="2d">2D</option>
                                                <option value="qr">QR Type</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label for="id_batch_code_format" class="form-label">Batch Code Format</label>
                                            <input type="text" class="form-control" name="id_batch_code_format" placeholder="Describe Format" value="${mainResponse.identifiers?.id_batch_code_format ?? ''}">
                                        </div>
                                        <div class="col-md-4">
                                            <label for="lot_number_format" class="form-label">Lot Number Format</label>
                                            <input type="text" class="form-control" name="lot_number_format" id="lot_number_format" placeholder="Describe Format" value="${mainResponse.lot_number_format ?? ''}">
                                        </div>
                                    </div>
                                </div>
                                <div class="card p-4">
                                    <h5 class="mb-3 fw-semibold">Pallet Configuration</h5>
                                    <div class="row g-3">
                                        <div class="col-md-3">
                                            <label for="pack_pallet_type" class="form-label">Pallet Type</label>
                                            <input type="text" class="form-control" name="pack_pallet_type" placeholder="e.g., Australian standard" value="${mainResponse.packaging?.pack_pallet_type ?? ''}">
                                        </div>
                                        <div class="col-md-3">
                                            <label for="pack_cases_per_layer" class="form-label label-right">Cases/Layer</label>
                                            <input type="number" step="0.01" class="form-control" name="pack_cases_per_layer" placeholder="Enter count" value="${mainResponse.packaging?.pack_cases_per_layer ?? ''}">
                                        </div>
                                        <div class="col-md-3">
                                            <label for="pack_layers_per_pallet" class="form-label label-right">Layers/Pallet</label>
                                            <input type="number" step="0.01" class="form-control" name="pack_layers_per_pallet" placeholder="Enter count" value="${mainResponse.packaging?.pack_layers_per_pallet ?? ''}">
                                        </div>
                                        <div class="col-md-3">
                                            <label for="pack_total_cases_per_pallet" class="form-label label-right">Total Cases/Pallet</label>
                                            <input type="number" step="0.01" class="form-control" name="pack_total_cases_per_pallet" placeholder="Enter count" value="${mainResponse.packaging?.pack_total_cases_per_pallet ?? ''}">
                                        </div>
                                        <div class="col-md-3">
                                            <label for="pack_pallet_dimensions_mm" class="form-label">Pallet Dimensions</label>
                                            <input type="text" class="form-control" name="pack_pallet_dimensions_mm" placeholder="Enter dimensions" value="${mainResponse.packaging?.pack_pallet_dimensions_mm ?? ''}">
                                        </div>
                                        <div class="col-md-3">
                                            <label for="pack_pallet_height_mm" class="form-label label-right">Pallet Height</label>
                                            <input type="number" step="0.01" class="form-control" name="pack_pallet_height_mm" placeholder="Enter mm" value="${mainResponse.packaging?.pack_pallet_height_mm ?? ''}">
                                        </div>
                                        <div class="col-md-3">
                                            <label for="pack_pallet_weight_kg" class="form-label label-right">Pallet Weight</label>
                                            <input type="number" step="0.01" class="form-control" name="pack_pallet_weight_kg" placeholder="Enter kg" value="${mainResponse.packaging?.pack_pallet_weight_kg ?? ''}">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="tab-pane fade" id="compliance" role="tabpanel">
                                <div class="card p-4">
                                    <h5 class="fw-semibold mb-3">Certifications</h5>
                                    <div class="row mb-3 gy-2">
                                        <div class="col-md-4 col-6">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="cert_is_organic" ${mainResponse.certifications?.cert_is_organic ? 'checked' : ''}>
                                            <label class="form-check-label ms-1" for="cert_is_organic">Organic Certified</label>
                                        </div>
                                        </div>
                                        <div class="col-md-4 col-6">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="cert_is_halal" ${mainResponse.certifications?.cert_is_halal ? 'checked' : ''}>
                                            <label class="form-check-label ms-1" for="cert_is_halal">Halal Certified</label>
                                        </div>
                                        </div>
                                        <div class="col-md-4 col-6">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="cert_is_kosher" ${mainResponse.certifications?.cert_is_kosher ? 'checked' : ''}>
                                            <label class="form-check-label ms-1" for="cert_is_kosher">Kosher Certified</label>
                                        </div>
                                        </div>
                                        <div class="col-md-4 col-6">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="cert_is_gluten_free" ${mainResponse.certifications?.cert_is_gluten_free ? 'checked' : ''}>
                                            <label class="form-check-label ms-1" for="cert_is_gluten_free">Gluten Free</label>
                                        </div>
                                        </div>
                                        <div class="col-md-4 col-6">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="cert_is_non_gmo" ${mainResponse.certifications?.cert_is_non_gmo ? 'checked' : ''}>
                                            <label class="form-check-label ms-1" for="cert_is_non_gmo">Non-GMO</label>
                                        </div>
                                        </div>
                                        <div class="col-md-4 col-6">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="cert_is_fair_trade" ${mainResponse.certifications?.cert_is_fair_trade ? 'checked' : ''}>
                                            <label class="form-check-label ms-1" for="cert_is_fair_trade">Fair Trade</label>
                                        </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card p-4">
                                    <h5 class="mb-3 fw-semibold">Traceability</h5>
                                    <div class="row mb-1 gy-2 mt-2">
                                        <div class="col-md-4 col-6">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" name="trace_document_required" ${mainResponse.trace_documentation_required ? 'checked' : ''}>
                                                <label class="form-check-label ms-1" for="trace_document_required">Trace Documentation Required</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row g-3 mt-1">
                                        <div class="col-12">
                                            <label for="trace_recall_procedure" class="form-label">Recall Procedure</label>
                                            <textarea class="form-control" name="trace_recall_procedure" rows="3" placeholder="Reference procedure">${mainResponse.trace_recall_procedure ?? ''}</textarea>
                                        </div>
                                    </div>
                                </div>
                                <div class="card p-4">
                                    <h5 class="mb-3 fw-semibold">Compliance Sign-Off</h5>
                                    
                                    <div class="row g-3 mt-1">
                                        <div class="col-md-6">
                                            <label for="best_before_days" class="form-label">Best Before Days</label>
                                            <input type="number" step="0.01" class="form-control" name="best_before_days" placeholder="Enter days" value="${mainResponse.best_before_days ?? ''}">
                                        </div>
                                        <div class="col-md-6">
                                            <label for="use_by_days" class="form-label">Use By Days</label>
                                            <input type="number" step="0.01" class="form-control" name="use_by_days" placeholder="Enter days" value="${mainResponse.use_by_days ?? ''}">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;

                    $('#parsedData').html(html);
                    // Optionally scroll to Step 2
                    $('html, body').animate({
                        scrollTop: $("#step2-section").offset().top
                    }, 600);
                } else {
                    console.log("No data found in the uploaded content.");
                }
            },
            complete: function () {
                $('#AIloader').addClass('d-none');
                initDropzone("dzProduct", "product");
                if (btn.length) {
                    btn.text(cText);
                    btn.prop('disabled', false);
                }
            }
        });
    });




</script>
@endpush