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
    ::placeholder {color: #c7ccd0 !important;opacity: 1;}
    .label-right {text-align: right;display: block;}
    #pdf-viewer {
        width: 100%;
        height: 100vh;
        border: none;
    }
    .audit-section{display: flex; column-gap: 8px; border: 1px solid var(--Menu-Back-Ground); padding: 1rem 0rem 0rem 1rem;}
    .audit-html-section h1{font-size:20px;}
    .audit-html-section h2{font-size:18px;}
    .audit-html-section p{font-size:14px;}
    .audit-html-section ul, .audit-html-section ol{color: var(--Primary-Dark-Mud) !important; font-size: 14px;}
    .overview-card{border: 1px solid var(--Menu-Back-Ground);padding: 20px;}
    .overview-wrapper{border: 1px solid var(--Menu-Back-Ground);padding: 13px;}
    .arrow-box span {font-size: 24px;font-weight: 500;color: #444;}
    #Re_extractionModal .bg-primary{background-color:var(--primary-color) !important;color:white !important;}
    .toggle-wrap {
    display: flex;
    align-items: center;
    gap: 12px;
}

.toggle {
    display: inline-flex;
    border: 2px solid #007bff;
    border-radius: 30px;
    overflow: hidden;
}

.toggle input {
    display: none;
}

.toggle-option {
    padding: 8px 20px;
    cursor: pointer;
    user-select: none;
    transition: 0.3s;
    color: #007bff;
    background: #fff;
    font-weight: 500;
}

.toggle input:checked + .toggle-option {
    background: #007bff;
    color: white;
}

</style>
@endpush

@section('content')
<div class="container-fluid product-search dataTable-wrapper">
    <div class="card-header">
        <div class="title-add">
            <h1 class="page-title">Edit Specifications</h1>
            <div class="right-side content d-flex flex-row-reverse align-items-center">
                <div class="Export-btn me-2">
                    <div class="btn-group click-dropdown">
                        <div class="dropdown ms-2">
                            <button class="btn btn-link text-dark p-0" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <span class="material-symbols-outlined">more_vert</span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <a class="dropdown-item re-run" href="javascript:void(0);">Re-run AI Extraction</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card-body">
        
        <!-- Tabs -->
        <!-- <ul class="nav nav-tabs mb-4" id="specTabs" role="tablist">
            <li class="nav-item" role="presentation">
            <button class="nav-link active" id="overview-tab" data-bs-toggle="tab" data-bs-target="#overview" type="button" role="tab">Overview</button>
            </li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#nutrition" type="button">Nutrition</button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#cool" type="button">Cool</button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#quality" type="button">Quality</button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#packaging" type="button">Packaging</button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#compliance" type="button">Compliance</button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#audit" type="button">Audit</button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#document" type="button">Document</button></li>
        </ul> -->

        <!-- Overview Tab -->
        <form id="UpdateSpecification">
            <!-- Tabs and Action Buttons -->
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
                    <a href="{{route('batchbase_agent.specifications')}}" class="btn btn-secondary-white">
                        Cancel
                    </a>
                    <button type="submit" id="saveSpecificationBtn" class="btn btn-secondary-blue">
                        <i class="material-symbols-outlined me-1" style="font-size: 18px;">save</i>
                        Save
                    </button>
                </div>
            </div>

            <!-- Tab Content -->
            @csrf
            <div class="tab-content">
                <div class="tab-pane fade show active" id="overview" role="tabpanel">
                    <div class="card p-4">
                        <h5 class="mb-3 fw-semibold">Basic Information</h5>
                        <div class="row g-3">
                            <div class="col-md-4">
                            <label for="spec_name" class="form-label">Spec Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="spec_name" id="spec_name" placeholder="Enter spec name" value="{{ old('spec_name', $specification->spec_name ?? '') }}">
                            </div>
                            <div class="col-md-4">
                            <label for="spec_sku" class="form-label">Spec Sku <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="spec_sku" id="spec_sku" placeholder="Enter SKU" value="{{ old('spec_sku', $specification->spec_sku ?? '') }}">
                            </div>
                            <div class="col-md-4">
                            <label for="spec_type" class="form-label">Specification Type <span class="text-danger">*</span></label>
                            <select class="form-select" name="spec_type" id="spec_type">
                                <option disabled>Select type</option>
                                <option value="raw_material" {{ old('spec_type', $specification->spec_type ?? '') == 'raw_material' ? 'selected' : '' }}>Raw Material</option>
                                <option value="product" {{ old('spec_type', $specification->spec_type ?? '') == 'product' ? 'selected' : '' }}>Finished Product</option>
                                <option value="package_material" {{ old('spec_type', $specification->spec_type ?? '') == 'package_material' ? 'selected' : '' }}>Packaging Material</option>
                            </select>
                            </div>
                            @php
                                $imgCount = 0;
                                if($specification->spec_image){
                                    $imageArray = get_images('specification',$specification->id);
                                    $imgCount = sizeof($imageArray);
                                }
                            @endphp
                            <div class="col-12">
                                <label class="form-label">Specification Image</label>
                                <div class="dropzone" id="dzProduct" data-preview="fileList">
                                    <span class="material-symbols-outlined upload-icon">upload</span>
                                    <p class="mt-1">Drag & drop files here or <span class="uploan-span">click to upload</span></p>
                                    <input type="file" id="fileInput" accept=".png,.jpg,.jpeg" multiple hidden>
                                    <span class="mt-1">Accepted file formats: "png, jpg, jpeg"</span>
                                </div>
                                @if($specification->spec_image)
                                <ul class="list-group mt-2" id="fileList" style="width: 100%;">
                                    @foreach($imageArray as $key => $img)
                                    @php
                                        $img_url = "/assets/{$specification->client_id}/{$specification->workspace_id}/specification_images/{$specification->id}/{$img['image_name']}";
                                        $df_image = (int)$specification->spec_image - 1;
                                    @endphp
                                    <li class="list-group-item mb-2">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="d-flex align-items-center">
                                                <img src="{{ asset($img_url) }}" class="thumb me-3">
                                                <div>
                                                    <strong>{{$img['image_name']}}</strong><br>
                                                    <small>{{$img['file_size']}}</small> |
                                                    <small>{{$img['file_format']}}</small>
                                                </div>
                                            </div>
                                            <div class="d-flex align-items-center gap-4">
                                                <div class="form-check m-0">
                                                    <input class="form-check-input" type="radio" name="productDefault"  id="spec_img_{{ $key }}" {{ $key == $df_image ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="spec_img_{{ $key }}">Make as Default</label>
                                                </div>
                                                <button type="button" class="btn p-0"data-id="{{ $img['id'] }}" onclick="remove_images(this)">
                                                    <span class="material-symbols-outlined text-danger">delete</span>
                                                </button>
                                            </div>
                                        </div>
                                    </li>
                                    @endforeach
                                </ul>
                                @else
                                <ul class="list-group mt-2" id="fileList" style="width: 100%;"></ul>
                                @endif
                            </div>

                            <div class="col-md-4">
                            <label for="spec_status" class="form-label">Status</label>
                            <select class="form-select" name="spec_status" id="spec_status">
                                <option disabled>Select status</option>
                                <option value="draft" {{ old('spec_status', $specification->spec_status ?? '') == 'draft' ? 'selected' : '' }}>Draft</option>
                                <option value="active" {{ old('spec_status', $specification->spec_status ?? '') == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ old('spec_status', $specification->spec_status ?? '') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                <option value="pending_review" {{ old('spec_status', $specification->spec_status ?? '') == 'pending_review' ? 'selected' : '' }}>Pending Review</option>
                                <option value="approved" {{ old('spec_status', $specification->spec_status ?? '') == 'approved' ? 'selected' : '' }}>Approved</option>
                            </select>
                            </div>
                            <div class="col-md-4">
                                <label for="aus_regulatory_status" class="form-label">Australian Regulatory Status</label>
                                <input type="text" class="form-control" name="aus_regulatory_status" id="aus_regulatory_status" placeholder="Enter status" value="{{ old('aus_regulatory_status', $specification->aus_regulatory_status ?? '') }}">
                            </div>

                            <div class="col-md-4">
                                <label for="supplier_name" class="form-label">Supplier Name</label>
                                <input type="text" class="form-control" name="supplier_name" id="supplier_name" placeholder="Enter supplier name" value="{{ old('supplier_name', $specification->supplier_name ?? '') }}">
                            </div>
                            <div class="col-12">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3" placeholder="Enter product description">{{ old('description', $specification->description ?? '') }}</textarea>
                            </div>
                             <div class="col-12">
                                <label for="spec_url" class="form-label">Specification URL</label>
                                <input type="text" class="form-control" name="spec_url" id="spec_url" placeholder="Enter specification URL" value="{{ old('spec_url', $specification->spec_url ?? '') }}">
                            </div>
                            <input type="hidden" name="spec_upload_type" value="{{ old('spec_upload_type', $specification->spec_upload_type ?? '') }}">
                            <input type="hidden" name="file_name" value="{{ old('file_name', $specification->file_name ?? '') }}">
                            <input name="default_image" id="default_image" type="hidden" value="{{ old('default_image', $specification->default_image ?? '') }}" />
                            
                        </div>
                    </div>

                    <div class="card p-4">
                        <h5 class="mb-3 fw-semibold">Manufacturer & Supply Chain</h5>
                        <div class="row g-3">
                            
                            <div class="col-md-6">
                                <label for="manufacturer_name" class="form-label">Manufacturer Name</label>
                                <input type="text" class="form-control" name="manufacturer_name" id="manufacturer_name" placeholder="Enter name" value="{{ old('manufacturer_name', $specification->manufacturer_name ?? '') }}">
                            </div>
                            <div class="col-md-6">
                                <label for="manufacturer_contact" class="form-label">Manufacturer Contact</label>
                                <input type="text" class="form-control" name="manufacturer_contact" id="manufacturer_contact" placeholder="Enter contact" value="{{ old('manufacturer_contact', $specification->manufacturer_contact ?? '') }}">
                            </div>
                            <div class="col-12">
                                <label for="manufacturer_address" class="form-label">Manufacturer Address</label>
                                <textarea class="form-control" id="manufacturer_address" name="manufacturer_address" rows="3" placeholder="Enter address">{{ old('manufacturer_address', $specification->manufacturer_address ?? '') }}</textarea>
                            </div>
                            <div class="col-md-6">
                                <label for="distributor_name" class="form-label">Distributor Name</label>
                                <input type="text" class="form-control" name="distributor_name" id="distributor_name" placeholder="Enter Name" value="{{ old('distributor_name', $specification->distributor_name ?? '') }}">
                            </div>
                            <div class="col-md-6">
                                <label for="distributor_contact" class="form-label">Distributor Contact</label>
                                <input type="text" class="form-control" name="distributor_contact" id="distributor_contact" placeholder="Enter Contact" value="{{ old('distributor_contact', $specification->distributor_contact ?? '') }}">
                            </div>
                            <div class="col-md-4">
                                <label for="compliance_officer" class="form-label">Compliance Officer Name</label>
                                <input type="text" class="form-control" name="compliance_officer" id="compliance_officer" placeholder="Compliance Officer" value="{{ old('compliance_officer', $specification->compliance_officer ?? '') }}">
                            </div>

                            <div class="col-md-4">
                                <label for="trace_gln" class="form-label">GLN</label>
                                <input type="text" class="form-control" name="trace_gln" placeholder="Enter 13-digit GLN" value="{{ old('trace_gln', $specification->trace_gln ?? '') }}">
                            </div>

                            <div class="col-md-4">
                                <label for="trace_system" class="form-label">Traceability System</label>
                                <input type="text" class="form-control" name="trace_system" placeholder="Specify system" value="{{ old('trace_system', $specification->trace_system ?? '') }}">
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
                                <input type="number" step="0.01" class="form-control" name="shelf_life_value" placeholder="Enter number" value="{{ old('shelf_life_value', $specification->shelf_life_value ?? '') }}">
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
                                <input type="number" step="0.01" class="form-control" name="storage_temp_min_c" placeholder="Enter °C" value="{{ old('storage_temp_min_c', $specification->storage_temp_min_c ?? '') }}">
                            </div>
                            <div class="col-md-3">
                                <label for="storage_temp_max_c" class="form-label label-right">Storage Temp Max (°C)</label>
                                <input type="number" step="0.01" class="form-control" name="storage_temp_max_c" placeholder="Enter °C" value="{{ old('storage_temp_max_c', $specification->storage_temp_max_c ?? '') }}">
                            </div>
                            <div class="col-md-3">
                                <label for="storage_humidity_min_percent" class="form-label label-right">Storage Humidity Min (%)</label>
                                <input type="number" step="0.01" class="form-control" name="storage_humidity_min_percent" placeholder="Enter %" value="{{ old('storage_humidity_min_percent', $specification->storage_humidity_min_percent ?? '') }}">
                            </div>
                            <div class="col-md-3">
                                <label for="storage_humidity_max_percent" class="form-label label-right">Storage Humidity Max (%)</label>
                                <input type="number" step="0.01" class="form-control" name="storage_humidity_max_percent" placeholder="Enter %" value="{{ old('storage_humidity_max_percent', $specification->storage_humidity_max_percent ?? '') }}">
                            </div>
                            
                            <div class="col-12">
                                <label for="storage_conditions" class="form-label">Storage Conditions</label>
                                <textarea class="form-control" name="storage_conditions" rows="3" placeholder="Describe storage requirements">{{ old('storage_conditions', $specification->storage_conditions ?? '') }}</textarea>
                            </div>
                            <div class="col-12">
                                <label for="handling_instructions" class="form-label">Handling Instructions</label>
                                <textarea class="form-control" name="handling_instructions" rows="3" placeholder="Enter instructions">{{ old('handling_instructions', $specification->handling_instructions ?? '') }}</textarea>
                            </div>
                            <div class="col-12">
                                <label for="disposal_instructions" class="form-label">Disposal Instructions</label>
                                <textarea class="form-control" name="disposal_instructions" rows="3" placeholder="Enter disposal instructions">{{ old('disposal_instructions', $specification->disposal_instructions ?? '') }}</textarea>
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
                                <input type="number" step="0.01" class="form-control" name="nutr_serving_size_g" id="nutr_serving_size_g" placeholder="e.g., 100g" value="{{ old('nutr_serving_size_g', $specification->nutr_serving_size_g ?? '') }}">
                            </div>
                            <div class="col-md-4">
                                <label for="nutr_servings_per_container" class="form-label label-right">Servings Per Container</label>
                                <input type="number" step="0.01" class="form-control" name="nutr_servings_per_container" id="nutr_servings_per_container" placeholder="e.g., 4 servings" value="{{ old('nutr_servings_per_container', $specification->nutr_servings_per_container ?? '') }}">
                            </div>
                            <div class="col-md-4">
                                <label for="nutritional_basis" class="form-label">Nutritional Basis</label>
                                <select class="form-select" name="nutritional_basis" id="nutritional_basis">
                                    <option value="g" {{ old('nutritional_basis', $specification->nutritional_basis ?? '') == 'g' ? 'selected' : '' }}>Per 100g</option>
                                    <option value="ml" {{ old('nutritional_basis', $specification->nutritional_basis ?? '') == 'ml' ? 'selected' : '' }}>Per 100ml</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="card p-4">
                        <h5 class="mb-3 fw-semibold">Macronutrients (per 100g/100ml)</h5>
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label for="nutr_energy_kj" class="form-label label-right">Energy (kJ)</label>
                                <input type="number" step="0.01" class="form-control" id="nutr_energy_kj" name="nutr_energy_kj" placeholder="Enter kJ value" value="{{ old('nutr_energy_kj', $specification->nutr_energy_kj ?? '') }}">
                            </div>
                            <div class="col-md-3">
                                <label for="nutr_protein_g" class="form-label label-right">Protein (g)</label>
                                <input type="number" step="0.01" class="form-control" name="nutr_protein_g" id="nutr_protein_g" placeholder="Enter grams" value="{{ old('nutr_protein_g', $specification->nutr_protein_g ?? '') }}">
                            </div>
                            <div class="col-md-3">
                                <label for="nutr_fat_total_g" class="form-label label-right">Total Fat (g)</label>
                                <input type="number" step="0.01" class="form-control" name="nutr_fat_total_g" id="nutr_fat_total_g" placeholder="Enter grams" value="{{ old('nutr_fat_total_g', $specification->nutr_fat_total_g ?? '') }}">
                            </div>
                            <div class="col-md-3">
                                <label for="nutr_fat_saturated_g" class="form-label label-right">Saturated Fat (g)</label>
                                <input type="number" step="0.01" class="form-control" name="nutr_fat_saturated_g" id="nutr_fat_saturated_g" placeholder="Enter grams" value="{{ old('nutr_fat_saturated_g', $specification->nutr_fat_saturated_g ?? '') }}">
                            </div>
                            <div class="col-md-3">
                                <label for="nutr_carbohydrate_g" class="form-label label-right label-right">Carbohydrate (g)</label>
                                <input type="number" step="0.01" class="form-control" name="nutr_carbohydrate_g" id="nutr_carbohydrate_g" placeholder="Enter grams" value="{{ old('nutr_carbohydrate_g', $specification->nutr_carbohydrate_g ?? '') }}">
                            </div>
                            <div class="col-md-3">
                                <label for="nutr_sugars_g" class="form-label label-right">Sugars (g)</label>
                                <input type="number" step="0.01" class="form-control" name="nutr_sugars_g" id="nutr_sugars_g" placeholder="Enter grams" value="{{ old('nutr_sugars_g', $specification->nutr_sugars_g ?? '') }}">
                            </div>
                            <div class="col-md-3">
                                <label for="nutr_added_sugars_g" class="form-label label-right">Added Sugars (g)</label>
                                <input type="number" step="0.01" class="form-control" name="nutr_added_sugars_g" id="nutr_added_sugars_g" placeholder="Enter grams" value="{{ old('nutr_added_sugars_g', $specification->nutr_added_sugars_g ?? '') }}">
                            </div>
                            <div class="col-md-3">
                                <label for="nutr_fat_trans_g" class="form-label label-right">Trans Fat (g)</label>
                                <input type="number" step="0.01" class="form-control" name="nutr_fat_trans_g" id="nutr_fat_trans_g" placeholder="Enter grams" value="{{ old('nutr_fat_trans_g', $specification->nutr_fat_trans_g ?? '') }}">
                            </div>
                            <div class="col-md-3">
                                <label for="nutr_dietary_fiber_g" class="form-label label-right">Dietary Fiber (g)</label>
                                <input type="number" step="0.01" class="form-control" name="nutr_dietary_fiber_g" id="nutr_dietary_fiber_g" placeholder="Enter grams" value="{{ old('nutr_dietary_fiber_g', $specification->nutr_dietary_fiber_g ?? '') }}">
                            </div>
                            <div class="col-md-3">
                                <label for="nutr_sodium_mg" class="form-label label-right">Sodium (mg)</label>
                                <input type="number" step="0.01" class="form-control" name="nutr_sodium_mg" id="nutr_sodium_mg" placeholder="Enter mg" value="{{ old('nutr_sodium_mg', $specification->nutr_sodium_mg ?? '') }}">
                            </div>
                            <div class="col-md-3">
                                <label for="nutr_cholesterol_mg" class="form-label label-right">Cholesterol (mg)</label>
                                <input type="number" step="0.01" class="form-control" name="nutr_cholesterol_mg" id="nutr_cholesterol_mg" placeholder="Enter mg" value="{{ old('nutr_cholesterol_mg', $specification->nutr_cholesterol_mg ?? '') }}">
                            </div>
                            <div class="col-md-3">
                                <label for="nutr_gluten_content" class="form-label">Gluten Content</label>
                                <input type="text" class="form-control" name="nutr_gluten_content" id="nutr_gluten_content" placeholder="Specify gluten status" value="{{ old('nutr_gluten_content', $specification->nutr_gluten_content ?? '') }}">
                            </div>
                        </div>
                    </div>
                    <div class="card p-4">
                        <h5 class="mb-3 fw-semibold">Micronutrients (Optional)</h5>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label for="nutr_vitamin_d_mcg" class="form-label label-right">Vitamin D (mcg)</label>
                                <input type="number" step="0.01" class="form-control" name="nutr_vitamin_d_mcg" id="nutr_vitamin_d_mcg" placeholder="Enter mcg" value="{{ old('nutr_vitamin_d_mcg', $specification->nutr_vitamin_d_mcg ?? '') }}">
                            </div>
                            <div class="col-md-4">
                                <label for="nutr_calcium_mg" class="form-label label-right">Calcium (mg)</label>
                                <input type="number" step="0.01" class="form-control" name="nutr_calcium_mg" id="nutr_calcium_mg" placeholder="Enter mg" value="{{ old('nutr_calcium_mg', $specification->nutr_calcium_mg ?? '') }}">
                            </div>
                            <div class="col-md-4">
                                <label for="nutr_iron_mg" class="form-label label-right">Iron (mg)</label>
                                <input type="number" step="0.01" class="form-control" name="nutr_iron_mg" id="nutr_iron_mg" placeholder="Enter mg" value="{{ old('nutr_iron_mg', $specification->nutr_iron_mg ?? '') }}">
                            </div>
                            <div class="col-md-4">
                                <label for="nutr_potassium_mg" class="form-label label-right">Potassium (mg)</label>
                                <input type="number" step="0.01" class="form-control" name="nutr_potassium_mg" id="nutr_potassium_mg" placeholder="Enter mg" value="{{ old('nutr_potassium_mg', $specification->nutr_potassium_mg ?? '') }}">
                            </div>
                            <div class="col-md-4">
                                <label for="phys_specific_gravity" class="form-label label-right">Specific Gravity</label>
                                <input type="number" step="0.01" class="form-control" name="phys_specific_gravity" placeholder="Enter value" value="{{ old('phys_specific_gravity', $specification->phys_specific_gravity ?? '') }}">
                            </div>
                        </div>
                    </div>
                    <div class="card p-4">
                        <h5 class="mb-3 fw-semibold">Ingredients & Allergens</h5>
                        <div class="row g-3">
                            <div class="col-12">
                                <label for="ing_ingredient_list" class="form-label">Ingredient List</label>
                                <textarea class="form-control" name="ing_ingredient_list" rows="3" placeholder="List all ingredients">{{ old('ing_ingredient_list', $specification->ing_ingredient_list ?? '') }}</textarea>
                            </div>
                            <div class="col-12">
                                <label for="allergen_statement" class="form-label">Allergen Statement</label>
                                <textarea class="form-control" name="allergen_statement" rows="3" placeholder="Enter allergen statement">{{ old('allergen_statement', $specification->allergen_statement ?? '') }}</textarea>
                            </div>
                            <div class="col-12">
                                <label for="allergen_fsanz_declaration" class="form-label">FSANZ Allergen Declaration</label>
                                <textarea class="form-control" name="allergen_fsanz_declaration" rows="3" placeholder="FSANZ format declaration">{{ old('allergen_fsanz_declaration', $specification->allergen_fsanz_declaration ?? '') }}</textarea>
                            </div>
                            <div class="col-12">
                                <label for="ing_percentage_labelling" class="form-label">Percentage Labelling</label>
                                <textarea class="form-control" name="ing_percentage_labelling" rows="3" placeholder="E.g., Contains 70% Beef">{{ old('ing_percentage_labelling', $specification->ing_percentage_labelling ?? '') }}</textarea>
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
                                <input type="text" class="form-control" name="cool_primary_country" placeholder="Enter country" value="{{ old('cool_primary_country', $specification->cool_primary_country ?? '') }}">
                            </div>                      
                            <div class="col-md-4">
                                <label for="cool_label_type" class="form-label">Label Type</label>
                                <input type="text" class="form-control" name="cool_label_type" placeholder="Specify label type" value="{{ old('cool_label_type', $specification->cool_label_type ?? '') }}">
                            </div>
                            <div class="col-md-4">
                                <label for="cool_fsanz_standard_ref" class="form-label">FSANZ Standard Reference</label>
                                <input type="text" class="form-control" name="cool_fsanz_standard_ref" placeholder="e.g., Standard 1.2.11" value="{{ old('cool_fsanz_standard_ref', $specification->cool_fsanz_standard_ref ?? '') }}">
                            </div>
                            <div class="col-12">
                                <label for="cool_origin_declaration" class="form-label">Origin Declaration</label>
                                <textarea class="form-control" name="cool_origin_declaration" rows="3" placeholder="Enter declaration">{{ old('origin_declaration', $specification->origin_declaration ?? '') }}</textarea>
                            </div>
                            <div class="col-12">
                                <label for="cool_date_marking_requirement" class="form-label">Date Marking Requirements</label>
                                <textarea class="form-control" name="cool_date_marking_requirement" rows="3" placeholder="Describe requirements">{{ old('cool_date_marking_requirement', $specification->cool_date_marking_requirement ?? '') }}</textarea>
                            </div>
                        </div>
                    </div>
                    <div class="card p-4">
                        <h5 class="mb-3 fw-semibold">Australian Claims & Content</h5>
                        <div class="row mb-1 gy-2 mt-2">
                            <div class="col-md-4 col-6">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="cool_aus_made_claim"  {{ old('cool_aus_made_claim', $specification->cool_aus_made_claim ?? false) ? 'checked' : '' }}>
                                <label class="form-check-label ms-1" for="cool_aus_made_claim">Australian Made</label>
                            </div>
                            </div>
                            <div class="col-md-4 col-6">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="cool_aus_grown_claim" {{ old('cool_aus_grown_claim', $specification->cool_aus_grown_claim ?? false) ? 'checked' : '' }}>
                                <label class="form-check-label ms-1" for="cool_aus_grown_claim">Australian Grown</label>
                            </div>
                            </div>
                            <div class="col-md-4 col-6">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="cool_aus_owned_claim" {{ old('cool_aus_owned_claim', $specification->cool_aus_owned_claim ?? false) ? 'checked' : '' }}>
                                <label class="form-check-label ms-1" for="cool_aus_owned_claim">Australian Owned</label>
                            </div>
                            </div>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="cool_percentage_australia" class="form-label">Australian Content %</label>
                                <input type="text" class="form-control" name="cool_percentage_australia" placeholder="E.g., 0.85 for 85%" value="{{ old('cool_percentage_australia', $specification->cool_percentage_australia ?? '') }}">
                            </div>
                            <div class="col-md-6">
                                <label for="cool_calculation_method" class="form-label">Calculation Method</label>
                                <input type="text" class="form-control" name="cool_calculation_method" placeholder="Describe method" value="{{ old('cool_calculation_method', $specification->cool_calculation_method ?? '') }}">
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
                                <textarea class="form-control" name="phys_appearance" rows="3" placeholder="Describe appearance">{{ old('phys_appearance', $specification->phys_appearance ?? '') }}</textarea>
                            </div>
                            <div class="col-6">
                                <label for="phys_color" class="form-label">Color</label>
                                <textarea class="form-control" name="phys_color" rows="3" placeholder="Describe color">{{ old('phys_color', $specification->phys_color ?? '') }}</textarea>
                            </div>
                            <div class="col-6">
                                <label for="phys_odor" class="form-label">Odor</label>
                                <textarea class="form-control" name="phys_odor" rows="3" placeholder="Describe odor">{{ old('phys_odor', $specification->phys_odor ?? '') }}</textarea>
                            </div>
                            <div class="col-6">
                                <label for="phys_texture" class="form-label">Texture</label>
                                <textarea class="form-control" name="phys_texture" rows="3" placeholder="Describe texture">{{ old('phys_texture', $specification->phys_texture ?? '') }}</textarea>
                            </div>
                            <div class="col-md-3">
                                <label for="phys_ph_level" class="form-label label-right">pH Level</label>
                                <input type="number" step="0.01" class="form-control" name="phys_ph_level" placeholder="6.5, 4.0-4.5, 7.2" value="{{ old('phys_ph_level', $specification->phys_ph_level ?? '') }}">
                            </div>
                            <div class="col-md-3">
                                <label for="phys_moisture_percent" class="form-label label-right">Moisture Content (%)</label>
                                <input type="number" step="0.01" class="form-control" name="phys_moisture_percent" placeholder="12.5, <10, 14-16" value="{{ old('phys_moisture_percent', $specification->phys_moisture_percent ?? '') }}">
                            </div>
                            <div class="col-md-3">
                                <label for="phys_water_activity" class="form-label label-right">Water Activity</label>
                                <input type="number" step="0.01" class="form-control" name="phys_water_activity" placeholder="0.65, <0.60, 0.70-0.75" value="{{ old('phys_water_activity', $specification->phys_water_activity ?? '') }}">
                            </div>
                            <div class="col-md-3">
                                <label for="phys_density_g_ml" class="form-label label-right">Density</label>
                                <input type="number" step="0.01" class="form-control" name="phys_density_g_ml" placeholder="0.85, 1.05, 0.95-1.00" value="{{ old('phys_density_g_ml', $specification->phys_density_g_ml ?? '') }}">
                            </div>
                            <div class="col-md-6">
                                <label for="phys_specific_gravity" class="form-label label-right">Specific Gravity</label>
                                <input type="number" step="0.01" class="form-control" name="phys_specific_gravity" placeholder="1.05, 0.98, 1.10-1.15" value="{{ old('phys_specific_gravity', $specification->phys_specific_gravity ?? '') }}">
                            </div>
                            <div class="col-6">
                                <label for="phys_viscosity_cps" class="form-label">Viscosity</label>
                                <textarea class="form-control" name="phys_viscosity_cps" rows="3" placeholder="2500-3500 cP, Pourable, Thick">{{ old('phys_viscosity_cps', $specification->phys_viscosity_cps ?? '') }}</textarea>
                            </div>
                        </div>
                    </div>

                    <div class="card p-4">
                        <h5 class="mb-3 fw-semibold">Microbiological Specifications</h5>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label for="micro_total_plate_count_cfu_g_max" class="form-label">Total Plate Count</label>
                                <input type="text" class="form-control" name="micro_total_plate_count_cfu_g_max" placeholder="e.g., <10,000 cfu/g" value="{{ old('micro_total_plate_count_cfu_g_max', $specification->micro_total_plate_count_cfu_g_max ?? '') }}">
                            </div>
                            <div class="col-md-4">
                                <label for="micro_yeast_mold_cfu_g_max" class="form-label">Yeast & Mold</label>
                                <input type="text" class="form-control" name="micro_yeast_mold_cfu_g_max" placeholder="e.g., <100 cfu/g" value="{{ old('micro_yeast_mold_cfu_g_max', $specification->micro_yeast_mold_cfu_g_max ?? '') }}">
                            </div>
                            <div class="col-md-4">
                                <label for="micro_coliforms_cfu_g_max" class="form-label">Coliforms</label>
                                <input type="text" class="form-control" name="micro_coliforms_cfu_g_max" placeholder="e.g., <10 cfu/g" value="{{ old('micro_coliforms_cfu_g_max', $specification->micro_coliforms_cfu_g_max ?? '') }}">
                            </div>
                            <div class="col-md-3">
                                <label for="micro_e_coli_cfu_g_max" class="form-label">E. Coli</label>
                                <input type="text" class="form-control" name="micro_e_coli_cfu_g_max" placeholder="e.g., Absent in 25g" value="{{ old('micro_e_coli_cfu_g_max', $specification->micro_e_coli_cfu_g_max ?? '') }}">
                            </div>
                            <div class="col-md-3">
                                <label for="micro_salmonella_absent_in_g" class="form-label">Salmonella</label>
                                <input type="text" class="form-control" name="micro_salmonella_absent_in_g" placeholder="e.g., Absent in 25g" value="{{ old('micro_salmonella_absent_in_g', $specification->micro_salmonella_absent_in_g ?? '') }}">
                            </div>
                            <div class="col-md-3">
                                <label for="micro_listeria_absent_in_g" class="form-label">Listeria</label>
                                <input type="text" class="form-control" name="micro_listeria_absent_in_g" placeholder="e.g., Absent in 25g" value="{{ old('micro_listeria_absent_in_g', $specification->micro_listeria_absent_in_g ?? '') }}">
                            </div>
                            <div class="col-md-3">
                                <label for="micro_staphylococcus_cfu_g_max" class="form-label">Staphylococcus</label>
                                <input type="text" class="form-control" name="micro_staphylococcus_cfu_g_max" placeholder="e.g., <100 cfu/g" value="{{ old('micro_staphylococcus_cfu_g_max', $specification->micro_staphylococcus_cfu_g_max ?? '') }}">
                            </div>
                        </div>
                    </div>
                    <div class="card p-4">
                        <h5 class="mb-3 fw-semibold">Heavy Metals (mg/kg)</h5>
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label for="chem_metal_lead" class="form-label">Lead (Pb)</label>
                                <input type="text" class="form-control" name="chem_metal_lead" placeholder="e.g., <0.5 mg/kg" value="{{ old('chem_metal_lead', $specification->chem_metal_lead ?? '') }}">
                            </div>
                            <div class="col-md-3">
                                <label for="chem_metal_cadmium" class="form-label">Cadmium (Cd)</label>
                                <input type="text" class="form-control" name="chem_metal_cadmium" placeholder="e.g., <0.1 mg/kg" value="{{ old('chem_metal_cadmium', $specification->chem_metal_cadmium ?? '') }}">
                            </div>
                            <div class="col-md-3">
                                <label for="chem_metal_mercury" class="form-label">Mercury (Hg)</label>
                                <input type="text" class="form-control" name="chem_metal_mercury" placeholder="e.g., <0.05 mg/kg" value="{{ old('chem_metal_mercury', $specification->chem_metal_mercury ?? '') }}">
                            </div>
                            <div class="col-md-3">
                                <label for="chem_metal_arsenic" class="form-label">Arsenic (As)</label>
                                <input type="text" class="form-control" name="chem_metal_arsenic" placeholder="e.g., <0.01 mg/kg" value="{{ old('chem_metal_arsenic', $specification->chem_metal_arsenic ?? '') }}">
                            </div>
                            <div class="col-md-3">
                                <label for="chem_metal_tin" class="form-label">Tin (Sn)</label>
                                <input type="text" class="form-control" name="chem_metal_tin" placeholder="e.g., <250 mg/kg" value="{{ old('chem_metal_tin', $specification->chem_metal_tin ?? '') }}">
                            </div>
                        </div>
                    </div>

                    <div class="card p-4">
                        <h5 class="mb-3 fw-semibold">Pesticide Residues (mg/kg)</h5>
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label for="chem_pest_glyphosate" class="form-label">Glyphosate</label>
                                <input type="text" class="form-control" name="chem_pest_glyphosate" placeholder="e.g., <0.1 mg/kg" value="{{ old('chem_pest_glyphosate', $specification->chem_pest_glyphosate ?? '') }}">
                            </div>
                            <div class="col-md-3">
                                <label for="chem_pest_chlorpyrifos" class="form-label">Chlorpyrifos</label>
                                <input type="text" class="form-control" name="chem_pest_chlorpyrifos" placeholder="e.g., <0.5 mg/kg" value="{{ old('chem_pest_chlorpyrifos', $specification->chem_pest_chlorpyrifos ?? '') }}">
                            </div>
                            <div class="col-md-3">
                                <label for="chem_pest_malathion" class="form-label">Malathion</label>
                                <input type="text" class="form-control" name="chem_pest_malathion" placeholder="e.g., <0.05 mg/kg" value="{{ old('chem_pest_malathion', $specification->chem_pest_malathion ?? '') }}">
                            </div>
                            <div class="col-md-3">
                                <label for="chem_pest_permethrin" class="form-label">Permethrin</label>
                                <input type="text" class="form-control" name="chem_pest_permethrin" placeholder="e.g., <0.1 mg/kg" value="{{ old('chem_pest_permethrin', $specification->chem_pest_permethrin ?? '') }}">
                            </div>
                            <div class="col-md-3">
                                <label for="chem_pest_imazalil" class="form-label">Imazalil</label>
                                <input type="text" class="form-control" name="chem_pest_imazalil" placeholder="e.g., <0.1 mg/kg" value="{{ old('chem_pest_imazalil', $specification->chem_pest_imazalil ?? '') }}">
                            </div>
                            <div class="col-md-3">
                                <label for="chem_pesticide_residues" class="form-label">Other Residues</label>
                                <input type="text" class="form-control" name="chem_pesticide_residues" placeholder="e.g., <0.1 mg/kg" value="{{ old('chem_pesticide_residues', $specification->chem_pesticide_residues ?? '') }}">
                            </div>
                        </div>
                    </div>

                    <div class="card p-4">
                        <h5 class="mb-3 fw-semibold">Mycotoxins (μg/kg)</h5>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label for="chem_mycotoxin_aflatoxin_b1" class="form-label">Aflatoxin B1</label>
                                <input type="text" class="form-control" name="chem_mycotoxin_aflatoxin_b1"  placeholder="e.g., <2 µg/kg" value="{{ old('chem_mycotoxin_aflatoxin_b1', $specification->chem_mycotoxin_aflatoxin_b1 ?? '') }}">
                            </div>
                            <div class="col-md-4">
                                <label for="chem_mycotoxin_aflatoxin_total" class="form-label">Aflatoxin Total</label>
                                <input type="text" class="form-control" name="chem_mycotoxin_aflatoxin_total"  placeholder="e.g., <2 µg/kg" value="{{ old('chem_mycotoxin_aflatoxin_total', $specification->chem_mycotoxin_aflatoxin_total ?? '') }}">
                            </div>
                            <div class="col-md-4">
                                <label for="chem_mycotoxin_ochratoxin_a" class="form-label">Ochratoxin A</label>
                                <input type="text" class="form-control" name="chem_mycotoxin_ochratoxin_a"  placeholder="e.g., <4 µg/kg" value="{{ old('chem_mycotoxin_ochratoxin_a', $specification->chem_mycotoxin_ochratoxin_a ?? '') }}">
                            </div>
                            <div class="col-md-4">
                                <label for="chem_mycotoxin_deoxynivalenol" class="form-label">Deoxynivalenol (DON)</label>
                                <input type="text" class="form-control" name="chem_mycotoxin_deoxynivalenol"  placeholder="e.g., <2 µg/kg" value="{{ old('chem_mycotoxin_deoxynivalenol', $specification->chem_mycotoxin_deoxynivalenol ?? '') }}">
                            </div>
                            <div class="col-md-4">
                                <label for="chem_mycotoxin_zearalenone" class="form-label">Zearalenone</label>
                                <input type="text" class="form-control" name="chem_mycotoxin_zearalenone"  placeholder="e.g., <5 µg/kg" value="{{ old('chem_mycotoxin_zearalenone', $specification->chem_mycotoxin_zearalenone ?? '') }}">
                            </div>
                            <div class="col-md-4">
                                <label for="chem_mycotoxin_patulin" class="form-label">Patulin</label>
                                <input type="text" class="form-control" name="chem_mycotoxin_patulin"  placeholder="e.g., <2 µg/kg" value="{{ old('chem_mycotoxin_patulin', $specification->chem_mycotoxin_patulin ?? '') }}">
                            </div>
                        </div>
                    </div>

                    <div class="card p-4">
                        <h5 class="mb-3 fw-semibold">Additives (mg/kg)</h5>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label for="chem_add_tartrazine" class="form-label">Tartrazine</label>
                                <input type="text" class="form-control" name="chem_add_tartrazine" placeholder="Specify content" value="{{ old('chem_add_tartrazine', $specification->chem_add_tartrazine ?? '') }}">
                            </div>
                            <div class="col-md-4">
                                <label for="chem_add_cochineal" class="form-label">Cochineal</label>
                                <input type="text" class="form-control" name="chem_add_cochineal" placeholder="Specify content" value="{{ old('chem_add_cochineal', $specification->chem_add_cochineal ?? '') }}">
                            </div>
                            <div class="col-md-4">
                                <label for="chem_add_sunset_yellow" class="form-label">Sunset Yellow</label>
                                <input type="text" class="form-control" name="chem_add_sunset_yellow" placeholder="Specify content" value="{{ old('chem_add_sunset_yellow', $specification->chem_add_sunset_yellow ?? '') }}">
                            </div>
                            <div class="col-md-4">
                                <label for="chem_add_citric_acid" class="form-label">Citric Acid</label>
                                <input type="text" class="form-control" name="chem_add_citric_acid" placeholder="Specify content" value="{{ old('chem_add_citric_acid', $specification->chem_add_citric_acid ?? '') }}">
                            </div>
                            <div class="col-md-4">
                                <label for="chem_add_ascorbic_acid" class="form-label">Ascorbic Acid</label>
                                <input type="text" class="form-control" name="chem_add_ascorbic_acid" placeholder="Specify content" value="{{ old('chem_add_ascorbic_acid', $specification->chem_add_ascorbic_acid ?? '') }}">
                            </div>
                            <div class="col-md-4">
                                <label for="chem_add_monosodium_glutamate" class="form-label">Monosodium Glutamate</label>
                                <input type="text" class="form-control" name="chem_add_monosodium_glutamate" placeholder="Specify content" value="{{ old('chem_add_monosodium_glutamate', $specification->chem_add_monosodium_glutamate ?? '') }}">
                            </div>
                        </div>
                    </div>

                    <div class="card p-4">
                        <h5 class="mb-3 fw-semibold">Preservatives (mg/kg)</h5>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label for="chem_pres_sodium_benzoate" class="form-label">Sodium Benzoate</label>
                                <input type="text" class="form-control" name="chem_pres_sodium_benzoate" placeholder="Specify content" value="{{ old('chem_pres_sodium_benzoate', $specification->chem_pres_sodium_benzoate ?? '') }}">
                            </div>
                            <div class="col-md-4">
                                <label for="chem_pres_potassium_sorbate" class="form-label">Potassium Sorbate</label>
                                <input type="text" class="form-control" name="chem_pres_potassium_sorbate" placeholder="Specify content" value="{{ old('chem_pres_potassium_sorbate', $specification->chem_pres_potassium_sorbate ?? '') }}">
                            </div>
                            <div class="col-md-4">
                                <label for="chem_pres_calcium_propionate" class="form-label">Calcium Propionate</label>
                                <input type="text" class="form-control" name="chem_pres_calcium_propionate" placeholder="Specify content" value="{{ old('chem_pres_calcium_propionate', $specification->chem_pres_calcium_propionate ?? '') }}">
                            </div>
                            <div class="col-md-4">
                                <label for="chem_pres_sulfur_dioxide" class="form-label">Sulfur Dioxide</label>
                                <input type="text" class="form-control" name="chem_pres_sulfur_dioxide" placeholder="Specify content" value="{{ old('chem_pres_sulfur_dioxide', $specification->chem_pres_sulfur_dioxide ?? '') }}">
                            </div>
                            <div class="col-md-4">
                                <label for="chem_pres_sodium_nitrite" class="form-label">Sodium Nitrite</label>
                                <input type="text" class="form-control" name="chem_pres_sodium_nitrite" placeholder="Specify content" value="{{ old('chem_pres_sodium_nitrite', $specification->chem_pres_sodium_nitrite ?? '') }}">
                            </div>
                            <div class="col-md-4">
                                <label for="chem_pres_sodium_metabisulfite" class="form-label">Sodium Metabisulfite</label>
                                <input type="text" class="form-control" name="chem_pres_sodium_metabisulfite" placeholder="Specify content" value="{{ old('chem_pres_sodium_metabisulfite', $specification->chem_pres_sodium_metabisulfite ?? '') }}">
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
                                <input type="text" class="form-control" name="pack_primary_type" placeholder="e.g., Bottle" value="{{ old('pack_primary_type', $specification->pack_primary_type ?? '') }}">
                            </div>
                            <div class="col-md-3">
                                <label for="pack_primary_material" class="form-label">Material</label>
                                <input type="text" class="form-control" name="pack_primary_material" placeholder="e.g., PET" value="{{ old('pack_primary_material', $specification->pack_primary_material ?? '') }}">
                            </div>
                            <div class="col-md-3">
                                <label for="pack_primary_dimensions_mm" class="form-label">Dimensions</label>
                                <input type="text" class="form-control" name="pack_primary_dimensions_mm" placeholder="Enter dimensions" value="{{ old('pack_primary_dimensions_mm', $specification->pack_primary_dimensions_mm ?? '') }}">
                            </div>
                            <div class="col-md-3">
                                <label for="pack_primary_weight_g" class="form-label label-right">Weight</label>
                                <input type="number" step="0.01" class="form-control" name="pack_primary_weight_g" placeholder="Enter weight in grams" value="{{ old('pack_primary_weight_g', $specification->pack_primary_weight_g ?? '') }}">
                            </div>
                        </div>
                    </div>
                    <div class="card p-4">
                        <h5 class="mb-3 fw-semibold">Secondary Packaging & Case</h5>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label for="pack_secondary_type" class="form-label">Secondary Type</label>
                                <input type="text" class="form-control" name="pack_secondary_type" placeholder="e.g., Shrink wrap" value="{{ old('pack_secondary_type', $specification->pack_secondary_type ?? '') }}">
                            </div>
                            <div class="col-md-4">
                                <label for="pack_secondary_material" class="form-label">Secondary Material</label>
                                <input type="text" class="form-control" name="pack_secondary_material" placeholder="e.g., LDPE" value="{{ old('pack_secondary_material', $specification->pack_secondary_material ?? '') }}">
                            </div>
                            <div class="col-md-4">
                                <label for="pack_secondary_dimensions_mm" class="form-label">Secondary Dimensions</label>
                                <input type="text" class="form-control" name="pack_secondary_dimensions_mm" placeholder="Enter dimensions" value="{{ old('pack_secondary_dimensions_mm', $specification->pack_secondary_dimensions_mm ?? '') }}">
                            </div>
                            <div class="col-md-3">
                                <label for="pack_units_per_secondary" class="form-label label-right">Units/Secondary</label>
                                <input type="number" step="0.01" class="form-control" name="pack_units_per_secondary" placeholder="Enter count" value="{{ old('pack_units_per_secondary', $specification->pack_units_per_secondary ?? '') }}">
                            </div>
                            <div class="col-md-3">
                                <label for="pack_units_per_case" class="form-label label-right">Units/Case</label>
                                <input type="number" step="0.01" class="form-control" name="pack_units_per_case" placeholder="Enter count" value="{{ old('pack_units_per_case', $specification->pack_units_per_case ?? '') }}">
                            </div>
                            <div class="col-md-3">
                                <label for="pack_case_dimensions_mm" class="form-label">Case Dimensions</label>
                                <input type="text" class="form-control" name="pack_case_dimensions_mm" placeholder="Enter L×W×H	" value="{{ old('pack_case_dimensions_mm', $specification->pack_case_dimensions_mm ?? '') }}">
                            </div>
                            <div class="col-md-3">
                                <label for="pack_case_weight_g" class="form-label label-right">Case Weight</label>
                                <input type="number" step="0.01" class="form-control" name="pack_case_weight_g" placeholder="Enter kg" value="{{ old('pack_case_weight_g', $specification->pack_case_weight_g ?? '') }}">
                            </div>
                        </div>
                    </div>
                    <div class="card p-4">
                        <h5 class="mb-3 fw-semibold">Barcodes & Identification</h5>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label for="id_gtin_13" class="form-label">GTIN-13 (Consumer Unit)</label>
                                <input type="text" class="form-control" name="id_gtin_13" placeholder="Enter 13-digit GTIN" value="{{ old('id_gtin_13', $specification->id_gtin_13 ?? '') }}">
                            </div>
                            <div class="col-md-4">
                                <label for="id_gtin_14" class="form-label">GTIN-14 (Case)</label>
                                <input type="text" class="form-control" name="id_gtin_14" placeholder="Enter 14-digit GTIN" value="{{ old('id_gtin_14', $specification->id_gtin_14 ?? '') }}">
                            </div>
                            <div class="col-md-4">
                                <label for="id_sscc" class="form-label">SSCC (Pallet)</label>
                                <input type="text" class="form-control" name="id_sscc" placeholder="Enter 18-digit SSCC" value="{{ old('id_sscc', $specification->id_sscc ?? '') }}">
                            </div>
                            <div class="col-md-4">
                                <label for="id_barcode_type" class="form-label">Barcode Type</label>
                                <select class="form-select" name="id_barcode_type">
                                    <option disabled>Select type</option>
                                    <option value="1d" {{ old('id_barcode_type', $specification->id_barcode_type ?? '') == '1d' ? 'selected' : '' }}>1D</option>
                                    <option value="2d" {{ old('id_barcode_type', $specification->id_barcode_type ?? '') == '2d' ? 'selected' : '' }}>2D</option>
                                    <option value="qr" {{ old('id_barcode_type', $specification->id_barcode_type ?? '') == 'qr' ? 'selected' : '' }}>QR Type</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="id_batch_code_format" class="form-label">Batch Code Format</label>
                                <input type="text" class="form-control" name="id_batch_code_format" placeholder="Describe format" value="{{ old('id_batch_code_format', $specification->id_batch_code_format ?? '') }}">
                            </div>
                            <div class="col-md-4">
                                <label for="lot_number_format" class="form-label">Lot Number Format</label>
                                <input type="text" class="form-control" name="lot_number_format" id="lot_number_format" placeholder="Describe Format" value="{{ old('lot_number_format', $specification->lot_number_format ?? '') }}">
                            </div>
                        </div>
                    </div>
                    <div class="card p-4">
                        <h5 class="mb-3 fw-semibold">Pallet Configuration</h5>
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label for="pack_pallet_type" class="form-label">Pallet Type</label>
                                <input type="text" class="form-control" name="pack_pallet_type" placeholder="e.g., Australian standard" value="{{ old('pack_pallet_type', $specification->pack_pallet_type ?? '') }}">
                            </div>
                            <div class="col-md-3">
                                <label for="pack_cases_per_layer" class="form-label label-right">Cases/Layer</label>
                                <input type="number" step="0.01" class="form-control" name="pack_cases_per_layer" placeholder="Enter count" value="{{ old('pack_cases_per_layer', $specification->pack_cases_per_layer ?? '') }}">
                            </div>
                            <div class="col-md-3">
                                <label for="pack_layers_per_pallet" class="form-label label-right">Layers/Pallet</label>
                                <input type="number" step="0.01" class="form-control" name="pack_layers_per_pallet" placeholder="Enter count" value="{{ old('pack_layers_per_pallet', $specification->pack_layers_per_pallet ?? '') }}">
                            </div>
                            <div class="col-md-3">
                                <label for="pack_total_cases_per_pallet" class="form-label label-right">Total Cases/Pallet</label>
                                <input type="number" step="0.01" class="form-control" name="pack_total_cases_per_pallet" placeholder="Enter count" value="{{ old('pack_total_cases_per_pallet', $specification->pack_total_cases_per_pallet ?? '') }}">
                            </div>
                            <div class="col-md-3">
                                <label for="pack_pallet_dimensions_mm" class="form-label">Pallet Dimensions</label>
                                <input type="text" class="form-control" name="pack_pallet_dimensions_mm" placeholder="Enter dimensions" value="{{ old('pack_pallet_dimensions_mm', $specification->pack_pallet_dimensions_mm ?? '') }}">
                            </div>
                            <div class="col-md-3">
                                <label for="pack_pallet_height_mm" class="form-label label-right">Pallet Height</label>
                                <input type="number" step="0.01" class="form-control" name="pack_pallet_height_mm" placeholder="Enter mm" value="{{ old('pack_pallet_height_mm', $specification->pack_pallet_height_mm ?? '') }}">
                            </div>
                            <div class="col-md-3">
                                <label for="pack_pallet_weight_kg" class="form-label label-right">Pallet Weight</label>
                                <input type="number" step="0.01" class="form-control" name="pack_pallet_weight_kg" placeholder="Enter kg" value="{{ old('pack_pallet_weight_kg', $specification->pack_pallet_weight_kg ?? '') }}">
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
                                <input class="form-check-input" type="checkbox" name="cert_is_organic" {{ old('cert_is_organic', $specification->cert_is_organic ?? false) ? 'checked' : '' }}>
                                <label class="form-check-label ms-1" for="cert_is_organic">Organic Certified</label>
                            </div>
                            </div>
                            <div class="col-md-4 col-6">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="cert_is_halal" {{ old('cert_is_halal', $specification->cert_is_halal ?? false) ? 'checked' : '' }}>
                                <label class="form-check-label ms-1" for="cert_is_halal">Halal Certified</label>
                            </div>
                            </div>
                            <div class="col-md-4 col-6">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="cert_is_kosher" {{ old('cert_is_kosher', $specification->cert_is_kosher ?? false) ? 'checked' : '' }}>
                                <label class="form-check-label ms-1" for="cert_is_kosher">Kosher Certified</label>
                            </div>
                            </div>
                            <div class="col-md-4 col-6">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="cert_is_gluten_free" {{ old('cert_is_gluten_free', $specification->cert_is_gluten_free ?? false) ? 'checked' : '' }}>
                                <label class="form-check-label ms-1" for="cert_is_gluten_free">Gluten Free</label>
                            </div>
                            </div>
                            <div class="col-md-4 col-6">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="cert_is_non_gmo" {{ old('cert_is_non_gmo', $specification->cert_is_non_gmo ?? false) ? 'checked' : '' }}>
                                <label class="form-check-label ms-1" for="cert_is_non_gmo">Non-GMO</label>
                            </div>
                            </div>
                            <div class="col-md-4 col-6">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="cert_is_fair_trade" {{ old('cert_is_fair_trade', $specification->cert_is_fair_trade ?? false) ? 'checked' : '' }}>
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
                                    <input class="form-check-input" type="checkbox" name="trace_document_required" {{ old('trace_document_required', $specification->trace_document_required ?? false) ? 'checked' : '' }}>
                                    <label class="form-check-label ms-1" for="trace_document_required">Trace Documentation Required</label>
                                </div>
                            </div>
                        </div>
                        <div class="row g-3 mt-1">
                            <div class="col-12">
                                <label for="trace_recall_procedure" class="form-label">Recall Procedure</label>
                                <textarea class="form-control" name="trace_recall_procedure" rows="3" placeholder="Reference procedure">{{ old('trace_recall_procedure', $specification->trace_recall_procedure ?? '') }}</textarea>
                            </div>
                        </div>
                    </div>
                    <div class="card p-4">
                        <h5 class="mb-3 fw-semibold">Compliance Sign-Off</h5>
                        <div class="row g-3 mt-1">
                            <div class="col-md-6">
                                <label for="best_before_days" class="form-label label-right">Best Before Days</label>
                                <input type="number" step="0.01" class="form-control" name="best_before_days" placeholder="Enter days" value="{{ old('best_before_days', $specification->best_before_days ?? '') }}">
                            </div>
                            <div class="col-md-6">
                                <label for="use_by_days" class="form-label label-right">Use By Days</label>
                                <input type="number" step="0.01" class="form-control" name="use_by_days" placeholder="Enter days" value="{{ old('use_by_days', $specification->use_by_days ?? '') }}">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="tab-pane fade" id="audit" role="tabpanel">

                    <div class="audit-section">
                        <span class="material-symbols-outlined">stars_2</span>
                        <p>AI-powered audit commentary from a senior Australian food standards auditor perspective. This analysis reviews your specification against FSANZ requirements and provides actionable recommendations.</p>
                    </div>

                    <div class="card p-4 mt-3">
                        <h5 class="mb-3 fw-semibold">Generate Audit Commentary</h5>
                        <p>Click below to generate a professional audit of this specification</p>
                        <div class="row g-2 mt-1">
                            <div class="col-md-6">
                                <button type="button" id="Auditsummary" class="btn btn-primary">Generate Audit Commentary</button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card p-4 mt-3">
                        <h5 class="mb-3 fw-semibold">Audit Commentary</h5>
                        <div class="row g-2 mt-1">
                            <div class="col-12 audit-html-section">
                                @if($specification->audit_response) {!! json_decode($specification->audit_response) !!} @endif
                            </div>
                        </div>
                    </div>
                    
                </div>
                
                <div class="tab-pane fade" id="document" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center border rounded p-2">
                        @if($specification->spec_upload_type == 1)
                        {{-- File Name / Editable Field --}}
                        <div class="file-name-container flex-grow-1">
                            <span class="file-name-text">{{ $specification->file_name }}</span>
                            <input type="text" class="form-control file-name-input d-none" value="{{ $specification->file_name }}">
                        </div>
                        @endif

                        {{-- 3-dot dropdown --}}
                        <div class="dropdown ms-2">
                            @if($specification->spec_upload_type == 1)
                            <button class="btn btn-link text-dark p-0" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <span class="material-symbols-outlined">more_vert</span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <a class="dropdown-item edit-file" href="#">Edit</a>
                                </li>
                                <li>
                                    <a class="dropdown-item delete-file" href="#">Delete</a>
                                </li>
                            </ul>
                            @endif
                            <button type="button" class="btn btn-primary-orange plus-icon" title="Add Specification Document">
                                <span class="material-symbols-outlined">add</span>
                            </button>
                        </div>
                    </div>

                    <div class="file-add-section card p-4 mt-3 d-none">
                            <div class="upload-section ">
                                <h3 class="pb-1"><span class="material-symbols-outlined">quick_reference_all</span>Upload Document</h3>
                                <p class="pt-2">Supports PDF only. AI will extract nutritional info, allergens, and FSANZ compliance data.</p>
                                <!-- <input name="uploadimage" id="uploadimage" type="file" accept=".pdf"/> -->
                                 <div class="dropzone" id="dzSpec" data-preview="fileListSpec">
                                    <span class="material-symbols-outlined upload-icon">upload</span>
                                    <p class="mt-1">Drag & drop files here or <span class="uploan-span">click to upload</span></p>
                                    <input type="file" id="fileInput" accept=".pdf" hidden>
                                    <span class="mt-1">Accepted file formats: "pdf"</span>
                                </div>
                                <ul id="fileListSpec" style="width: 50%;"></ul>
                            </div>

                            <!-- Footer section -->
                            <div class="specification-footer text-end mt-3">
                                <button type="button" class="btn btn-secondary-blue" id="Processcancel">Cancel</button>
                                <button type="button" id="processBtn" class="btn btn-secondary-blue">Save</button>
                            </div>
                    </div>
                    


                    @if($specification->spec_upload_type == 1)
                        @php 
                            $filepath = "assets/{$specification->client_id}/{$specification->workspace_id}/specification/{$specification->file_name}";
                            $pdfUrl = asset($filepath);
                        @endphp
                        <div class="mt-5">
                            <iframe id="pdf-viewer" src="{{ asset('assets/js/pdfjs/web/viewer.html') }}?file={{ urlencode($pdfUrl) }}" allowfullscreen></iframe>
                        </div>
                    @endif
                </div>
                
            </div>

            <!-- <div class="text-end mt-3">
                <a href="{{route('batchbase_agent.specifications')}}"><button type="button" class="btn btn-secondary-blue">Back</button></a>
                <button type="submit" id="saveSpecificationBtn" class="btn btn-primary">Update Specification</button>
            </div> -->
        </form>

    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="Re_extractionModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">

            <!-- Modal Header -->
            <div class="modal-header">
                <div>
                    <h5 class="modal-title">Review Extraction Changes</h5>
                    <p></p>
                </div>
                <div style="display: flex; align-items: center; gap: 6px;">
                    <div class="toggle-wrap">
                        <div class="toggle">
                            <!-- LEFT OPTION = ALL RECORDS -->
                            <input type="radio" id="allRecords" name="filter" value="all" >
                            <label for="allRecords" class="toggle-option left">All</label>

                            <!-- RIGHT OPTION = SELECTED RECORDS -->
                            <input type="radio" id="selectedRecords" name="filter" value="selected" checked>
                            <label for="selectedRecords" class="toggle-option right">Changed</label>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                
            </div>

            <!-- Modal Form -->
            <form id="Re_extractionForm" enctype="multipart/form-data">
                @csrf

                <!-- Modal Body (scrollable) -->
                <div class="modal-body" style="max-height: 60vh; overflow-y: auto;">
                    
                    <!-- Your dynamic content goes here -->
                    <div class="d-none" id="AllSpecification"></div>
                    <div class="" id="ChangedSpecification"></div>
                </div>

                <!-- Modal Footer -->
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary-white" data-bs-dismiss="modal">
                        Close
                    </button>
                    <button type="submit" class="btn btn-secondary-blue" id="updateRe_extraction">
                        Save Changes
                    </button>
                </div>

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
    let id = @json($specification->id);
    $(document).ready(function() {
        // Prevent Enter key from submitting the form while editing filename
        $(document).on('keypress', '.file-name-input', function(e) {
            if (e.which === 13) {
                e.preventDefault(); // Stop form submission
            }
        });


        // Edit filename
        $('.edit-file').on('click', function(e) {
            e.preventDefault();
            const container = $(this).closest('.d-flex');
            const textEl = container.find('.file-name-text');
            const inputEl = container.find('.file-name-input');

            // Toggle edit mode
            textEl.addClass('d-none');
            inputEl.removeClass('d-none').focus();

            // When user presses Enter or blurs
            inputEl.on('keypress blur', function(e) {
                if (e.type === 'blur' || e.which === 13) {
                    const newName = $(this).val().trim();
                    const specId = "{{ $specification->id }}"; // pass dynamically

                    if (newName && newName !== textEl.text()) {
                        $.ajax({
                            url: "{{ route('specification.updateFileName') }}",
                            method: "POST",
                            data: {
                                _token: "{{ csrf_token() }}",
                                id: specId,
                                file_name: newName
                            },
                            success: function(res) {
                                if (res.success) {
                                    textEl.text(newName);
                                    Swal.fire({
                                        icon: 'success',
                                        text: res.message,
                                        timer: 2000
                                    });

                                } else {
                                    Swal.fire({
                                        icon: 'warning',
                                        title: 'Warning!',
                                        text: res.message
                                    }); 
                                }
                            },
                            error: function() {
                            },
                            complete: function() {
                                inputEl.addClass('d-none');
                                textEl.removeClass('d-none');
                            }
                        });
                    } else {
                        inputEl.addClass('d-none');
                        textEl.removeClass('d-none');
                    }
                }
            });
        });

        $('.delete-file').on('click', function(e) {
            const specId = "{{ $specification->id }}"; // pass dynamically
            Swal.fire({
                title: 'Are you sure?',
                text: 'You won\'t be able to revert this!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText:'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {

                    $.ajax({
                        url: "{{ route('specification.deleteFile') }}",
                        method: "POST",
                        data: {
                            _token: "{{ csrf_token() }}",
                            id: specId
                        },
                        success: function(res) {
                            if (response.status) {
                                Swal.fire({
                                    icon: 'success',
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
                        }
                    });
                }
            });
        });    

        $('.plus-icon').on('click', function(e) {
            const container = $('.file-add-section')
            container.removeClass('d-none');
        });

        $('#Processcancel').on('click', function(e) {
            const container = $('.file-add-section')
            container.addClass('d-none');
        });

        $(document).on('click', '#processBtn', function (e) {
            e.preventDefault();

            const btn = $('#processBtn');
            const cText = btn.text();

            // Get the file input element
            // const fileInput = $('#fileInput')[0];

            // Check if a file is selected
            if (fileBuckets["dzSpec"].length === 0) {
                Swal.fire({
                    icon: 'warning',
                    text: 'Please select a file before uploading.'
                });
                return;
            }

            // Create a FormData object
            const data = new FormData();

            if (fileBuckets["dzSpec"] && fileBuckets["dzSpec"].length > 0) {
                fileBuckets["dzSpec"].forEach((item) => {
                    data.append("image_file", item.file); // Append each file to FormData
                });
            }
            data.append('_token', '{{ csrf_token() }}'); // include CSRF token if needed
            $.ajax({
                type: "POST",
                url: "{{ route('specification.fileUpload', ':id') }}".replace(':id', id),
                data: data,
                processData: false,
                contentType: false,
                dataType: 'json',

                beforeSend: function () {
                    btn.text('Processing...');
                    btn.prop('disabled', true);
                },

                success: function (response) {
                    if (response.status) {
                        Swal.fire({
                            icon: 'success',
                            text: response.message,
                            timer: 2000
                        }).then(() => {
                            window.location.href="{{ route('batchbase_agent.specifications') }}"
                        });
                    } else {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Warning!',
                            text: response.message
                        });
                    }
                },

                error: function (xhr, status, error) {
                    console.error('Upload error:', error);
                    Swal.fire({
                        icon: 'error',
                        text: 'Something went wrong while uploading the file.'
                    });
                },

                complete: function () {
                    btn.text(cText);
                    btn.prop('disabled', false);
                }
            });
        });
       
    });
    
    $(document).on('submit', 'form#UpdateSpecification', function (e) {
        e.preventDefault(); // prevent normal form submission

        const btn = $('#saveSpecificationBtn');
        const cText = btn.text();

        // ✅ Create FormData object (this collects all form inputs automatically)
        const form = document.getElementById('UpdateSpecification');
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
                data.append("image_file[]", item.file); // Append each file to FormData
            });
        }
        
        $.ajax({
            type: "POST",
            url: "{{ route('specifications.update', ':id') }}".replace(':id', id),
            data: data,
            processData: false, // prevent jQuery from processing data
            contentType: false, // prevent jQuery from setting incorrect headers
            dataType: 'json',
            
            beforeSend: function () {
                if (btn.length) {
                    btn.text('Processing...');
                    btn.prop('disabled', true);
                }
            },
            success: function (response) {
                if (response.status) {
                    Swal.fire({
                        icon: 'success',
                        text: response.message,
                        timer: 2000
                    }).then(() => {
                        window.location.href="{{ route('batchbase_agent.specifications') }}"
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
                if (btn.length) {
                    btn.text(cText);
                    btn.prop('disabled', false);
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

    $(document).on('click', '#Auditsummary',function(e){
        const btn = $('#Auditsummary');
        const cText = btn.text();
        $.ajax({
            type: "POST",
            url: "{{ route('specifications.audit', ':id') }}".replace(':id', id),
            processData: false, // prevent jQuery from processing data
            contentType: false, // prevent jQuery from setting incorrect headers
            dataType: 'json',
            beforeSend: function () {
                progressLocked = false;
                if (btn.length) {
                    btn.text('Processing...');
                    btn.prop('disabled', true);
                }
                $('#AIloader').removeClass('d-none');
            },
            success: function (response) {
                $('#AIloader').addClass('d-none');
                if (response.status) {
                    $('.audit-html-section').html($.parseHTML(response.audit));
                    Swal.fire({ 
                        icon: 'success',
                        text: response.message,
                        timer: 2000
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

    $(document).on('click','.re-run',function(){
        $.ajax({
            type: "POST",
            url: "{{ route('specification.rerun', ':id') }}".replace(':id', id),
            dataType: 'json',
            beforeSend: function () {
                $('#AIloader').removeClass('d-none');
            },
            success: function (response) {
                $('#AIloader').addClass('d-none');
                if (response.status) {
                    if(response.type == 0){
                        Swal.fire({
                            icon: 'warning',
                            title: 'Warning!',
                            text: 'Specification does not have File.'
                        });
                        return;
                    }
                    $('#Re_extractionModal .modal-header p').html(`<span class="badge bg-primary ms-1">${response.count}</span> fields have changed from the AI extraction. Review the changes below and decide whether to accept or reject them.`);
                    $('#Re_extractionModal .modal-body #ChangedSpecification').html(response.message);
                    $('#Re_extractionModal .modal-body #AllSpecification').html(response.all_html);
                    $('#Re_extractionModal').modal('show');
                } else {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Warning!',
                        text: response.message
                    });
                }
            },
            complete: function () {
               $('#AIloader').addClass('d-none');
            }
        });
    });

    $('#Re_extractionForm').on('submit', function (e) {
        e.preventDefault();
        let formData = new FormData();

        // loop through all wrappers (across all tabs)
        $(this).find('.reextract-overview-wrapper').each(function () {
            const $row = $(this);

            // locate the checkbox in this wrapper (adjust selector if your checkbox is elsewhere)
            const $checkbox = $row.find('input[type="checkbox"]');

            // only process rows where checkbox is checked
            if (!$checkbox.is(':checked')) return;

            // collect all re_ fields inside this wrapper (inputs, selects, textareas)
            $row.find('input[name^="re_"], select[name^="re_"], textarea[name^="re_"]').each(function () {
                const rawName = $(this).attr('name') || '';
                const key = rawName.replace(/^re_/, '');
                const value = $(this).val();

                // ensure key exists before appending
                if (key) {
                    // append empty string for null/undefined to keep consistent form fields
                    formData.append(key, value == null ? '' : value);
                }
            });
        });
        
        // append CSRF
        formData.append('_token', $('meta[name="csrf-token"]').attr('content'));
        // Send AJAX
         $.ajax({
            type: "POST",
            url: "{{ route('specification.rerun_update', ':id') }}".replace(':id', id),
            data:formData,
            processData: false,
            contentType: false,
            beforeSend: function () {
                $('#AIloader').removeClass('d-none');
            },
            success: function (response) {
                $('#AIloader').addClass('d-none');
                if (response.status) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: response.message
                    });
                } else {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Warning!',
                        text: response.message
                    });
                }
            },
            complete: function () { 
                $('#AIloader').addClass('d-none');
                $('#Re_extractionModal').modal('hide');
            }
        });
    });


    $(document).on('change', 'input[name="filter"]', function() {
        let value = $(this).val();  // get selected value
        if (value === "all") {
            $('#AllSpecification').removeClass('d-none');
            $('#ChangedSpecification').addClass('d-none');
        } else {
            $('#AllSpecification').addClass('d-none');
            $('#ChangedSpecification').removeClass('d-none');
        }
    });

 
    let backdropClicked = false;
    // Detect if user clicked on the backdrop
    $(document).on('mousedown', function (e) {
        const modal = $('#Re_extractionModal');

        if (
            modal.hasClass('show') &&
            $(e.target).hasClass('modal') // Backdrop area
        ) {
            backdropClicked = true;
        } else {
            backdropClicked = false;
        }
    });

    // Trigger SweetAlert ONLY if backdrop was clicked
    $('#Re_extractionModal').on('hide.bs.modal', function (e) {
        if (!backdropClicked) {
            return true; // allow normal close (button, save, etc.)
        }

        e.preventDefault(); // stop closing

        Swal.fire({
            title: "Are you sure?",
            text: "You have unsaved changes. Do you really want to close?",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Yes, close it",
            cancelButtonText: "No, keep it open"
        }).then((result) => {
            if (result.isConfirmed) {
                backdropClicked = false; 
                $('#Re_extractionModal').modal('hide'); // force close
            }
        });
    });

    function remove_images(_this) {
    let imgID = $(_this).attr('data-id')
      Swal.fire({
        title: "Are you sure?",
        text: "You won't be able to revert this!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Yes, delete it!"
      }).then((result) => {
        if (result.isConfirmed) {
            let data = {'_token':$('meta[name="csrf-token"]').attr('content')};	
            $.ajax({
                type: "POST",
                url: `/remove/images/${imgID}`,
                dataType: 'json',
                data: data,
                beforeSend: function () {
                },
                success: function (response) {    
                    if(response['status'] == false){
                        if ('message_type' in response) {
                            show_swal(0, response.message, response.message_type);
                        } else {
                            show_swal(0, response.message);
                        }
                    }else{
                        $(_this).closest('li').remove(); // Remove the list item from the UI
                        show_swal(1,"Image has been deleted.")
                    }
                },
                complete: function(){}
            });
        }
      });
}



</script>
@endpush