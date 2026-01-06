@extends('backend.master', [
'pageTitle' => 'Specifications',
'activeMenu' => [
'item' => 'Specifications',
'subitem' => 'Specifications',
'additional' => '',
],
'features' => [
'datatables' => '1',
],
'breadcrumbItems' => [
['label' => 'Batchbase Agent', 'url' => '#'],
['label' => 'Product']
]
])

@push('styles')
<style>
    .btn-hidden {display: none !important;}

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
    
/* Table appearance */
#compareAccordion table {
    border-collapse: separate;
    border-spacing: 0;
}

#compareAccordion table thead th {
    background: #f8f9fa;
    font-size: 13px;
    font-weight: 600;
    color: #444;
    text-transform: uppercase;
}

#compareAccordion table tbody tr:nth-child(even) {
    background-color: #fafafa;
}

#compareAccordion table tbody td {
    font-size: 14px;
    padding: 8px 10px;
}

/* Inline diff enhancements */
.diff-added {
    background-color: #e4f8e9;
    font-weight: 600;
    color: #007b30;
    border-radius: 4px;
    padding: 0 3px;
}

.diff-removed {
    background-color: #ffe8e8;
    text-decoration: line-through;
    color: #b30000;
    border-radius: 4px;
    padding: 0 3px;
}

.text-empty {
    color: #999 !important;
    font-style: italic;
}

/* Accordion header improvements */
.accordion-button {
    font-weight: 700;
    color: #363d47;
    text-transform: uppercase;
    background: #eef1f5;
}

.accordion-button:not(.collapsed) {
    background: #cfd7e3;
    color: #0b2239;
    box-shadow: none;
}

.accordion-item {
    margin-bottom: 6px;
    border-radius: 8px;
    overflow: hidden;
    border: 1px solid #e1e6eb;
}

</style>
@endpush

@section('content')
<div class="container-fluid product-search dataTable-wrapper">
    <div class="card-header">
        <div class="title-add">
            <h1 class="page-title">Specifications</h1>
            <input type="hidden" class="selectedCols" id="selectedCols">
            <div class="right-side content d-flex flex-row-reverse align-items-center">
                
                <div class="btn-group click-dropdown me-2">
                    <button type="button" class="btn btn-primary-orange plus-icon" title="Add Specification">
                        <span class="material-symbols-outlined">add</span>
                    </button>
                    <ul class="dropdown-menu">
                        <li>
                            <a class="dropdown-item" href="{{ route('specifications.manual') }}">
                                Manual
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="{{ route('specifications.add') }}">
                                Upload (PDF or paste text)
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="javascript:void(0)" onclick="openFSANZModal()">
                                FSANZ
                            </a>
                        </li>
                    </ul>
                </div>


                <button type="button" class="btn btn-primary-orange plus-icon me-2" onclick="delete_selected_specification()" title="Delete Specification">
                    <span class="material-symbols-outlined">delete</span>
                </button>
                   
                <div class="btn-group click-dropdown me-2">
                    <button type="button" class="btn btn-primary-orange plus-icon" title="Download Specification">
                        <span class="material-symbols-outlined">download</span>
                    </button>
                    <ul class="dropdown-menu">
                        <li>
                            <a class="dropdown-item export-csv" href="javascript:void(0);" data-url="/download/csv/specifications">
                                Download as CSV
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item export-excel" href="javascript:void(0);" data-url="/download/excel/specifications">
                                Download as Excel
                            </a>
                        </li>
                    </ul>
                </div> 
                <div class="btn-group click-dropdown me-2">
                    <a href="{{ route('specifications.import') }}" class="btn btn-primary-orange plus-icon" title="Upload / Import data">
                        <span class="material-symbols-outlined">upload</span>
                    </a>
                </div>    

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
                            <input class="form-check-input" type="checkbox" id="specificationDefault">
                        </div>
                    </th>
                    <th class="text-primary-blue">Photo</th>
                    @php 
                    $headers = [
                        'Spec Name','Spec Sku','Source','Spec Type', 'Status', 'Australian Regulatory Status', 'Description', 'Supplier Name', 'Manufacturer Name', 'Manufacturer Address', 'Manufacturer Contact', 'Distributor Name', 'Distributor Contact', 'Compliance Officer', 'Lot Number Format', 'Serving Size (g)', 'Servings per Container', 'Nutrition Basis', 'Energy (kJ)', 'Protein (g)', 'Carbohydrate (g)', 'Sodium (mg)', 'Total Fat (g)', 'Saturated Fat (g)', 'Trans Fat (g)', 'Sugars (g)', 'Added Sugars (g)', 'Dietary Fibre (g)', 'Cholesterol (mg)', 'Calcium (mg)', 'Iron (mg)', 'Potassium (mg)', 'Vitamin D (mcg)', 'Gluten Content', 'Primary Country of Origin', 'Origin Declaration', 'Australian Content (%)', 'FSANZ Standard Reference', 'Date Marking Requirement', 'Label Type','Calculation Method','Australian Made', 'Australian Owned', 'Australian Grown','Ingredient List', 'Allergen Statement', 'FSANZ Allergen Declaration', 'Percentage Labelling', 'Appearance', 'Colour', 'Odour', 'Texture', 'Density (g/mL)', 'Specific Gravity', 'Moisture (%)', 'pH Level', 'Water Activity (aw)', 'Viscosity (cP)', 'Aerobic Plate Count Max', 'Yeast & Mould Max', 'Coliforms Max', 'E. coli Max', 'Salmonella Absent in (g)', 'Listeria Absent in (g)', 'Staphylococcus Max', 'Primary Pack Type', 'Primary Pack Material', 'Primary Pack Dimensions (mm)', 'Primary Pack Weight (g)', 'Secondary Pack Type', 'Secondary Pack Material', 'Secondary Pack Dimensions (mm)', 'Units per Secondary', 'Case Dimensions (mm)', 'Case Weight (g)', 'Units per Case', 'Pallet Type', 'Pallet Dimensions (mm)', 'Pallet Height (mm)', 'Pallet Weight (kg)', 'Cases per Layer (Ti)', 'Layers per Pallet (Hi)', 'Total Cases per Pallet', 'GTIN-13 (Retail)', 'GTIN-14 (Case)', 'SSCC (Logistics)', 'Batch Code Format', 'Barcode Type', 'Advisory Statements', 'Warning Statements', 'Health Claims', 'Nutrition Content Claims', 'Organic (Certified)', 'Halal (Certified)', 'Kosher (Certified)', 'Gluten Free (Certified)', 'Non-GMO', 'Fair Trade', 'Certificate Details', 'Storage Temp Min (C)', 'Storage Temp Max (C)', 'Storage RH Min (%)', 'Storage RH Max (%)', 'Storage Conditions', 'Shelf Life Type', 'Shelf Life Value', 'Shelf Life Unit', 'Best Before Days','Use By Days','Handling Instructions', 'Disposal Instructions', 'GLN', 'Traceability System', 'Recall Procedure', 'Trace Documents Required','Lead (Pb)', 'Cadmium (Cd)','Mercury (Hg)',  'Arsenic (As)', 'Tin (Sn)', 'Chlorpyrifos', 'Glyphosate','Malathion','Permethrin','Imazalil','Residues', 'Aflatoxin B1','Aflatoxin Total','Ochratoxin A','Deoxynivalenol (DON)','Zearalenone','Patulin','Tartrazine', 'Cochineal','Sunset Yellow','Citric Acid','Ascorbic Acid','Monosodium Glutamate (MSG)','Sodium Benzoate', 'Potassium Sorbate','Calcium Propionate','Sulfur Dioxide','Sodium Nitrite','Sodium Metabisulfite'
                        ];
                    @endphp 
                    @foreach($headers as $header)
                    <th class="text-primary-blue">{{$header}}</th>
                    @endforeach
                    <th class="text-primary-blue"></th>
                    <th class="text-primary-blue"></th>
                </tr>
            </thead>
            <tbody>

                @php 
                $specValues = ["spec_name","spec_sku",'fsanz_source_id',"spec_type","spec_status","aus_regulatory_status","description","supplier_name","manufacturer_name","manufacturer_address","manufacturer_contact","distributor_name","distributor_contact","compliance_officer","lot_number_format","nutr_serving_size_g","nutr_servings_per_container","nutritional_basis","nutr_energy_kj","nutr_protein_g","nutr_carbohydrate_g","nutr_sodium_mg","nutr_fat_total_g","nutr_fat_saturated_g","nutr_fat_trans_g","nutr_sugars_g","nutr_added_sugars_g","nutr_dietary_fiber_g","nutr_cholesterol_mg","nutr_calcium_mg","nutr_iron_mg","nutr_potassium_mg","nutr_vitamin_d_mcg","nutr_gluten_content","cool_primary_country","cool_origin_declaration","cool_percentage_australia","cool_fsanz_standard_ref","cool_date_marking_requirement","cool_label_type","cool_calculation_method","cool_aus_made_claim","cool_aus_owned_claim","cool_aus_grown_claim","ing_ingredient_list","allergen_statement","allergen_fsanz_declaration","ing_percentage_labelling","phys_appearance","phys_color","phys_odor","phys_texture","phys_density_g_ml","phys_specific_gravity","phys_moisture_percent","phys_ph_level","phys_water_activity","phys_viscosity_cps","micro_total_plate_count_cfu_g_max","micro_yeast_mold_cfu_g_max","micro_coliforms_cfu_g_max","micro_e_coli_cfu_g_max","micro_salmonella_absent_in_g","micro_listeria_absent_in_g","micro_staphylococcus_cfu_g_max","pack_primary_type","pack_primary_material","pack_primary_dimensions_mm","pack_primary_weight_g","pack_secondary_type","pack_secondary_material","pack_secondary_dimensions_mm","pack_units_per_secondary","pack_case_dimensions_mm","pack_case_weight_g","pack_units_per_case","pack_pallet_type","pack_pallet_dimensions_mm","pack_pallet_height_mm","pack_pallet_weight_kg","pack_cases_per_layer","pack_layers_per_pallet","pack_total_cases_per_pallet","id_gtin_13","id_gtin_14","id_sscc","id_batch_code_format","id_barcode_type","aus_advisory_statements","aus_warning_statements","aus_health_claims","aus_nutrition_content_claims","cert_is_organic","cert_is_halal","cert_is_kosher","cert_is_gluten_free","cert_is_non_gmo","cert_is_fair_trade","cert_certificate_details","storage_temp_min_c","storage_temp_max_c","storage_humidity_min_percent","storage_humidity_max_percent","storage_conditions","shelf_life_type","shelf_life_value","shelf_life_unit","best_before_days","use_by_days","handling_instructions","disposal_instructions","trace_gln","trace_system","trace_recall_procedure","trace_document_required","chem_metal_lead","chem_metal_cadmium","chem_metal_mercury","chem_metal_arsenic","chem_metal_tin","chem_pest_glyphosate","chem_pest_chlorpyrifos","chem_pest_malathion","chem_pest_permethrin","chem_pest_imazalil","chem_pesticide_residues","chem_mycotoxin_aflatoxin_b1","chem_mycotoxin_aflatoxin_total","chem_mycotoxin_ochratoxin_a","chem_mycotoxin_deoxynivalenol","chem_mycotoxin_zearalenone","chem_mycotoxin_patulin","chem_add_tartrazine","chem_add_cochineal","chem_add_sunset_yellow","chem_add_citric_acid","chem_add_ascorbic_acid","chem_add_monosodium_glutamate","chem_pres_sodium_benzoate","chem_pres_potassium_sorbate","chem_pres_calcium_propionate","chem_pres_sulfur_dioxide","chem_pres_sodium_nitrite","chem_pres_sodium_metabisulfite"];
                @endphp
                @foreach($specifications as $specification)
                    <tr>
                        <td class="text-primary-dark-mud">
                            <div class="form-check-temp p-1">
                                <input class="form-check-input specification_check" data-labour="{{$specification->id}}" type="checkbox" id="specification_{{$specification->id}}">
                            </div>
                        </td>
                        <td class="align-middle">
                            @php
                                $imgUrl = '';
                                if($specification->spec_image){
                                    $imgUrl = get_default_image_url('specification_images',$specification->spec_image,$specification->id);
                                }else{
                                    $imgUrl = env('APP_URL')."/assets/img/ing_default.png";
                                }
                            @endphp
                            <img src="{{ $imgUrl }}" alt="specification Image" class="product-thumbnail list">
                        </td>
                        @foreach($specValues as $Values)
                            <td class="text-primary-dark-mud">@if($Values == "fsanz_source_id") {{$specification->$Values ? 'FSANZ': 'Batchbase'}} @else {{$specification->$Values ?? ''}} @endif</td>
                        @endforeach
                        <td class="text-primary-dark-mud hide">{{ ucfirst($specification->archive) ?? '' }}</td>                        
                        <td class="actions-menu-area">
                            <div class="">
                                <!-- 3-Dot Icon Menu for Grid View -->
                                <div class="dropdown d-flex justify-content-end">
                                    <button class="icon-primary-orange me-0" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                                        <span class="material-symbols-outlined">more_vert</span>
                                    </button>
                                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                        <a href="{{route('specifications.edit', ['specification' => $specification->id])}}"><li>
                                            <span class="dropdown-item text-primary-dark-mud me-2 edit-row-data">
                                                 <span class="sidenav-normal ms-2 ps-1">Edit</span>
                                            </span>
                                        </li></a>

                                        <li>
                                            <span class="dropdown-item text-primary-dark-mud delete-row-data"  data-archive="{{ $specification->archive }}" data-id="{{ $specification->id }}">
                                                <span class="sidenav-normal ms-2 ps-1"> @if($specification->archive == 1) Delete @else Archive @endif</span>
                                            </span>
                                        </li>
                                        @if($specification->archive == 1)
                                        <li>
                                            <span class="dropdown-item text-primary-dark-mud unarchive-data" data-archive="{{ $specification->archive }}" data-id="{{ $specification->id }}">
                                                <span class="sidenav-normal ms-2 ps-1">Unarchive</span>
                                            </span>
                                        </li>
                                        @endif
                                        @if($specification->version->isNotEmpty())
                                        <li>
                                            <span class="dropdown-item text-primary-dark-mud version-compare"
                                                data-version='@json($specification->version)'
                                                data-spec-id="{{ $specification->id }}">
                                                <span class="sidenav-normal ms-2 ps-1">Compare</span>
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
</div>


<div class="modal fade" id="compareModal" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Compare Versions</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <div class="row mb-3">
          <div class="col">
            <label class="form-label">Source A</label>
            <select id="sourceA" class="form-select"></select>
          </div>
          <div class="col">
            <label class="form-label">Source B</label>
            <select id="sourceB" class="form-select"></select>
          </div>
        </div>

        <button id="compareBtn" class="btn btn-success w-100 mb-3">COMPARE</button>

        {{-- Expand / Collapse All --}}
        <div class="d-flex justify-content-end gap-2 mb-3 mt-3 d-none" id="compareActions">
            <button class="btn btn-outline-primary btn-sm" id="expandAllBtn">Expand All</button>
            <button class="btn btn-outline-secondary btn-sm" id="collapseAllBtn">Collapse All</button>
        </div>

        <div id="compareResult">
            <div class="accordion" id="compareAccordion"></div>
        </div>

        <button id="restoreVersionBtn" class="btn btn-primary w-100 mt-3" style="display:none;">
            Restore This Version as Current
        </button>
      </div>
    </div>
  </div>
</div>

{{-- FSANZ Food Search Modal --}}
<div class="modal fade" id="fsanzSearchModal" tabindex="-1" aria-labelledby="fsanzSearchModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="fsanzSearchModalLabel">Search FSANZ Food Database</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                {{-- Info Box --}}
                <div class="alert alert-info d-flex align-items-start gap-2 mb-3">
                    <i class="bi bi-database fs-5"></i>
                    <div>
                        <strong>FSANZ Food Database</strong>
                        <p class="mb-0 small">Search and select an FSANZ food to create a specification from. All mandatory fields will be automatically populated.</p>
                    </div>
                </div>

                {{-- Search Input --}}
                <div class="position-relative mb-3">
                    <i class="bi bi-search position-absolute top-50 translate-middle-y ms-3 text-muted"></i>
                    <input type="text" 
                           id="fsanz-search-input" 
                           class="form-control ps-5" 
                           placeholder="Search by name, FSANZ key, or food group...">
                </div>

                {{-- Results Count --}}
                <div id="fsanz-results-count" class="small text-muted mb-2 d-none">
                    Showing <span id="fsanz-count">0</span> food(s)
                </div>

                {{-- Results Container --}}
                <div id="fsanz-search-results" class="border rounded" style="max-height: 500px; overflow-y: auto;">
                    {{-- Loading State --}}
                    <div id="fsanz-loading" class="d-flex justify-content-center align-items-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>

                    {{-- Empty State --}}
                    <div id="fsanz-empty" class="text-center py-5 d-none">
                        <i class="bi bi-search fs-1 text-muted"></i>
                        <p class="text-muted mt-2">Loading FSANZ foods...</p>
                    </div>

                    {{-- No Results --}}
                    <div id="fsanz-no-results" class="text-center py-5 d-none">
                        <i class="bi bi-inbox fs-1 text-muted"></i>
                        <p class="text-muted mt-2">No foods found matching your search</p>
                    </div>

                    {{-- Results List --}}
                    <div id="fsanz-list" class="p-3"></div>
                </div>

                {{-- Data Source Info --}}
                <div class="small text-muted mt-3">
                    <strong>Data Source:</strong> FSANZ Food Database<br>
                    Foods from the Food Standards Australia New Zealand database with verified nutritional data.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<style>
.fsanz-item {
    padding: 1rem;
    border: 1px solid #e5e7eb;
    border-radius: 0.5rem;
    margin-bottom: 0.5rem;
    transition: background-color 0.15s;
    cursor: pointer;
}
.fsanz-item:hover {
    background-color: #f3f4f6;
}
.fsanz-item .nutritional-info {
    font-size: 0.75rem;
    color: #6b7280;
}
</style>

{{-- Include Create Specification Modal --}}
@include('backend.fsanz_food._specmodal')



@endsection

@push('scripts')

<script>

   let compareModal = new bootstrap.Modal(document.getElementById("compareModal"));

/**
 * Open Compare Modal
 * Source A: always "Current Version"
 * Source B: all archived versions
 */
$(".version-compare").on("click", function () {
    const versionData = $(this).data("version") || [];
    const specId = $(this).data("spec-id");

    let dropdownA = $("#sourceA");
    let dropdownB = $("#sourceB");

    // 🔒 Source A = Current Version only
    dropdownA.empty()
        .append(`<option value="current" selected>Current Version</option>`)
        .prop("disabled", true);

    // 🔄 Load only archived versions for Source B
    dropdownB.empty();
    versionData.forEach(v => {
        const label = `${v.archive_name} (v${v.archive_version})`;
        dropdownB.append(`<option value="${v.archive_id}">${label}</option>`);
    });

    // Clear previous accordion content (but keep the wrapper!)
    $("#compareAccordion").empty();
    $("#compareActions").addClass('d-none');
    $("#restoreVersionBtn").hide();

    $("#compareModal").data("spec-id", specId);
    compareModal.show();
});

/**
 * Compare button click
 */
$("#compareBtn").on("click", function () {
    let specId = $("#compareModal").data("spec-id");
    let sourceA = "current"; // always
    let sourceB = $("#sourceB").val();

    if (!sourceB) {
        alert("Please select a version to compare!");
        return;
    }

    $.ajax({
        url: "{{ route('specification.compare') }}",
        method: "POST",
        data: {
            spec_id: specId,
            sourceA: sourceA,
            sourceB: sourceB,
            _token: $('meta[name="csrf-token"]').attr("content")
        },
        success: function (response) {
            renderComparison(response.groups || []);

            if ((response.groups || []).length) {
                $("#compareActions").removeClass('d-none');
                $("#restoreVersionBtn")
                    .data("archive-id", sourceB)
                    .show();

                // 🔽 Scroll to first changed section automatically
                setTimeout(() => {
                    const firstItem = document.querySelector("#compareAccordion .accordion-item");
                    if (firstItem) {
                        firstItem.scrollIntoView({
                            behavior: "smooth",
                            block: "start"
                        });
                    }
                }, 150);
            }
        },
        error: function () {
            alert("Failed to compare versions. Please try again.");
        }
    });
});

/**
 * Restore selected version as current
 */
$("#restoreVersionBtn").on("click", function () {
    let archiveId = $(this).data("archive-id");
    let specId = $("#compareModal").data("spec-id");

    Swal.fire({
            title: 'Are you sure?',
            text: 'you want to restore this version as the current version?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "{{ route('specification.restore') }}",
                    method: "POST",
                    data: {
                        spec_id: specId,
                        archive_id: archiveId,
                        _token: $('meta[name="csrf-token"]').attr("content")
                    },
                    success: function () {
                        Swal.fire({
                            icon: 'success',
                            text: 'Version restored successfully!',
                            timer: 2000
                        }).then(() => {
                            location.reload();
                        });
                    },
                    error: function () {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Warning!',
                            text: 'Failed to restore version.'
                        }); 
                    }
                });
            }
        });


    
});

/**
 * Render comparison groups + rows into accordion
 */
function renderComparison(groups) {
    const container = $("#compareAccordion");
    container.empty();

    if (!groups.length) {
        container.html('<p class="text-center text-muted mb-0 p-3">No differences found between the selected versions.</p>');
        return;
    }

    groups.forEach((group, index) => {
        let rowsHtml = "";

        group.rows.forEach(row => {
            rowsHtml += `
                <tr>
                    <td class="fw-semibold">${row.label}</td>
                    <td>${row.htmlA || ""}</td>
                    <td class="text-success">${row.htmlB || ""}</td>
                </tr>
            `;
        });

        const itemHtml = `
            <div class="accordion-item">
                <h2 class="accordion-header" id="heading-${index}">
                    <button class="accordion-button ${index !== 0 ? "collapsed" : ""}" 
                            type="button" 
                            data-bs-toggle="collapse" 
                            data-bs-target="#collapse-${index}" 
                            aria-expanded="${index === 0 ? "true" : "false"}" 
                            aria-controls="collapse-${index}">
                        ${group.label}
                    </button>
                </h2>
                <div id="collapse-${index}" 
                    class="accordion-collapse collapse ${index === 0 ? "show" : ""}"
                    data-bs-parent="#compareAccordion">
                    <div class="accordion-body p-0">
                        <table class="table table-sm mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 30%;">Field</th>
                                    <th style="width: 35%;">Source A</th>
                                    <th style="width: 35%;">Source B</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${rowsHtml}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        `;

        container.append(itemHtml);
    });
}

/**
 * Expand / Collapse All buttons
 */
$("#expandAllBtn").on("click", function () {
    $("#compareAccordion .accordion-collapse").each(function () {
        let collapse = new bootstrap.Collapse(this, { toggle: false });
        collapse.show();
    });
});

$("#collapseAllBtn").on("click", function () {
    $("#compareAccordion .accordion-collapse.show").each(function () {
        let collapse = new bootstrap.Collapse(this, { toggle: false });
        collapse.hide();
    });
});





    $(document).ready(function() {
        var tableArray = @json($specifications);
        const table = $('#dtRecordsView1').DataTable({
            "order": [],
            autoWidth:false,
            deferRender:true,
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
                    targets: [7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,32,33,34,35,36,37,38,39,40,41,42,43,44,45,46,47,48,49,50,51,52,53,54,55,56,57,58,59,60,61,62,63,64,65,66,67,68,69,70,71,72,73,74,75,76,77,78,79,80,81,82,83,84,85,86,87,88,89,90,91,92,93,94,95,96,97,98,99,100,101,102,103,104,105,106,107,108,109,110,111,112,113,114,115,116,117,118,119,120,121,122,123,124,125,126,127,128,129,130,131,132,133,134,135,136,137,138,139,140,141,142,143,144,145,146], // Specify columns that should be hidden initially
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
                table.columns(146).search(0, true, false).draw(); 
                // $('#dtRecordsView1').css('visibility', 'visible');
                // if (tableArray.length < 7) {
                //     $('.table-scroll').removeClass('table-scroll');
                // }  
            }
        });
    });

    function createColVisDropdown(dt) {
        let dropdownHtml = '<div class="colvis-dropdown">';   
        let initiallyCheckedColumns = [1,2,3,4,5,6]; // Define which columns should be checked by default        
        dt.columns().every(function (idx) {
            let column = this;
            let columnTitle = column.header().textContent;
            if(columnTitle != "" || columnTitle != ''){
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
            overflow: 'scroll',
            height: 'inherit',
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

    // Delete Labour Handling
    $(document).on('click', '.delete-row-data', function() {
        const id = $(this).data('id');
        const archive = $(this).data('archive');
        const url = "{{ route('specifications.delete', ':id') }}".replace(':id', id);
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
                        if (response.status) {
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


    $(document).on('click','#specificationDefault',function() {
        let selectvalue = $('#specificationDefault').is(':checked') 
        $('#dtRecordsView1 tbody tr').each(function() {
            $(this).find('td:eq(0) input').prop('checked',selectvalue)
        });
    });

    function delete_selected_specification(){
        let specificationobj = [];
        
        $("table#dtRecordsView1 tbody tr").each(function () {
            if($(this).find('td:eq(0) input').prop('checked') == true){
                let id = $(this).find('td:eq(0) input').data('labour')
                specificationobj.push(id)
            }
        });
        if(specificationobj.length == 0){
            Swal.fire({
                icon: 'warning',
                title: 'Warning!',
                text: 'No Specification select'
            });
        }else{
            let archiveVal = $('#customFilter').val()
            let html,title,confirmBtn,inputText
            if(archiveVal == "1"){
                title = 'Delete Specification'
                confirmBtn = 'Delete'
                inputText = 'delete'
                html = `<p>The <strong>${specificationobj.length}</strong> selected items will be permanently deleted and cannot be retrieved. <strong>Are you sure you want to delete them?</strong></p>
                        <p>To confirm, enter the phrase <strong>delete</strong>.</p>
                        <input id="confirmInput" class="swal2-input" placeholder="Type delete here">`
            }else{
                title = 'Archive Specification'
                confirmBtn = 'Archive'
                inputText = 'archive'
                html = `<p>The <strong>${specificationobj.length}</strong> selected items will be archived. <strong>Are you sure you want to archive them?</strong></p>
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
                    var specifications = JSON.stringify(specificationobj);
                    let data = {'archive':archiveVal,'specificationobj':specifications,'_token':$('meta[name="csrf-token"]').attr('content')}
                    $.ajax({
                        type: "POST",
                        url: "{{route('specifications.bulk_delete')}}",
                        dataType: 'json',
                        data: data,
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
            let data = {'_token':$('meta[name="csrf-token"]').attr('content'),'selectedCols':selectedCols,'model':'specification'};	
            $.ajax({
                type: "POST",
                url: "{{route('save.download.attr')}}",
                dataType: 'json',
                data: data,
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
			table.columns(146).search(search_val, true, false).draw();
		}else{ 
			table.columns().search('').draw(); 
		} 
    })

    $(document).on('click', '.unarchive-data', function() {
        const archive = $(this).data('archive');
        const id = $(this).data('id');
        const url = "{{ route('specifications.unarchive', ':id') }}".replace(':id', id);
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
                   
                    beforeSend: function () {
                        if (btn.length) {
                            btn.text('Processing...');
                            btn.prop('disabled', true);
                        }
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
            modalEl?.addEventListener('hidden.bs.modal', () => {
                this.resetForm();
                // Reopen FSANZ search modal if it was opened from there
                if (fsanzSearchState.openedFromSearch && fsanzSearchModal && typeof restoreFSANZSearchState === 'function') {
                    // Reset the flag
                    fsanzSearchState.openedFromSearch = false;
                    // Small delay to ensure modal is fully closed
                    setTimeout(() => {
                        restoreFSANZSearchState();
                        fsanzSearchModal.show();
                    }, 300);
                }
            });
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
            
            // Required fields validation
            if (!food.name) {
                errors.push('Food name is required');
            }
            
            if (!food.fsanz_key) {
                errors.push('FSANZ key is required');
            }
            
            if (!food.energy_kj && food.energy_kj !== 0) {
                errors.push('Energy (kJ) is required');
            }
            
            // Warnings for missing optional data
            if (!food.estimated_allergens || (Array.isArray(food.estimated_allergens) && food.estimated_allergens.length === 0)) {
                warnings.push('No allergen information available');
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
                    this.showError(data.message);
                    return;
                }
                
                // Success - reset flag so search modal doesn't reopen
                if (typeof fsanzSearchState !== 'undefined') {
                    fsanzSearchState.openedFromSearch = false;
                }
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
                    window.location.href = `/specifications/${specification.id}/edit`;
                } else {
                    location.reload();
                }
            });
        }
        
        /**
         * Show error toast/alert
         */
        showError(message) {
            Swal.fire({
                title: "Warning!",
                text: message || 'Failed to create specification',
                icon: "warning"
            });
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

    // Initialize FSANZ Create Specification Modal
    let fsanzSpecModal = null;
    $(document).ready(function() {
        if (document.getElementById('createSpecificationModal')) {
            fsanzSpecModal = new FSANZCreateSpecificationModal();
        }
    });

    // FSANZ Search Modal
    let fsanzSearchModal = null;
    let fsanzSearchTimeout = null;
    let fsanzSearchState = {
        searchTerm: '',
        lastResults: null,
        openedFromSearch: false // Track if create modal was opened from search
    };

    /**
     * Open FSANZ Search Modal
     */
    function openFSANZModal() {
        if (!fsanzSearchModal) {
            const modalEl = document.getElementById('fsanzSearchModal');
            if (modalEl) {
                fsanzSearchModal = new bootstrap.Modal(modalEl);
                
                // Setup search input handler
                const searchInput = document.getElementById('fsanz-search-input');
                if (searchInput) {
                    searchInput.addEventListener('input', function(e) {
                        clearTimeout(fsanzSearchTimeout);
                        fsanzSearchTimeout = setTimeout(() => {
                            searchFSANZFoods(e.target.value);
                        }, 300);
                    });
                }
            }
        }

        // Load initial records when modal opens
        loadInitialFSANZFoods();
        fsanzSearchModal?.show();
    }

    /**
     * Load initial 25-50 FSANZ foods
     */
    function loadInitialFSANZFoods() {
        showFSANZLoading();
        document.getElementById('fsanz-results-count').classList.add('d-none');
        
        $.ajax({
            url: "{{ route('fsanz_food.data') }}",
            method: 'GET',
            data: {
                start: 0,
                length: 50, // Load 50 records initially
                draw: 1
            },
            success: function(response) {
                if (response.data && response.data.length > 0) {
                    renderFSANZFoods(response.data);
                } else {
                    showFSANZEmpty();
                }
            },
            error: function() {
                showFSANZError();
            }
        });
    }

    /**
     * Search FSANZ foods
     */
    function searchFSANZFoods(searchTerm) {
        if (!searchTerm || searchTerm.trim() === '') {
            loadInitialFSANZFoods();
            return;
        }

        showFSANZLoading();
        
        $.ajax({
            url: "{{ route('fsanz_food.data') }}",
            method: 'GET',
            data: {
                start: 0,
                length: 50,
                draw: 1,
                'search[value]': searchTerm
            },
            success: function(response) {
                if (response.data && response.data.length > 0) {
                    renderFSANZFoods(response.data);
                    document.getElementById('fsanz-results-count').classList.remove('d-none');
                    document.getElementById('fsanz-count').textContent = response.recordsFiltered || response.data.length;
                } else {
                    showFSANZNoResults();
                }
            },
            error: function() {
                showFSANZError();
            }
        });
    }

    /**
     * Render FSANZ foods in card format
     */
    function renderFSANZFoods(foods) {
        const listContainer = document.getElementById('fsanz-list');
        const loadingEl = document.getElementById('fsanz-loading');
        const emptyEl = document.getElementById('fsanz-empty');
        const noResultsEl = document.getElementById('fsanz-no-results');

        loadingEl.classList.add('d-none');
        emptyEl.classList.add('d-none');
        noResultsEl.classList.add('d-none');

        if (!foods || foods.length === 0) {
            showFSANZNoResults();
            return;
        }

        listContainer.innerHTML = foods.map(food => renderFSANZFoodItem(food)).join('');
    }

    /**
     * Render single FSANZ food item
     */
    function renderFSANZFoodItem(food) {
        // Parse energy value (may be formatted as "398.00 KJ" or just number)
        let energyDisplay = '0.00kJ';
        if (food.energy_kj) {
            const energyStr = food.energy_kj.toString().replace(' KJ', '').replace('kJ', '').trim();
            const energy = parseFloat(energyStr);
            if (!isNaN(energy)) {
                energyDisplay = energy.toFixed(2) + 'kJ';
            }
        }
        
        return `
            <div class="fsanz-item" data-food-id="${food.id}" onclick="selectFSANZFood(${food.id})">
                <div class="d-flex align-items-start justify-content-between">
                    <div class="flex-grow-1">
                        <div class="d-flex align-items-center gap-2 flex-wrap mb-2">
                            <span class="fw-bold" style="color:var(--primary-color);">${escapeHtml(food.name || '-')}</span>
                            ${food.fsanz_key ? `<span class="badge bg-light text-dark border">${escapeHtml(food.fsanz_key)}</span>` : ''}
                            ${food.food_group ? `<span class="badge bg-secondary">${escapeHtml(food.food_group)}</span>` : ''}
                        </div>
                        <div class="nutritional-info d-flex gap-3 mb-2">
                            <span>Energy: ${energyDisplay}</span>
                            ${food.measurement_basis ? `<span>Basis: ${escapeHtml(food.measurement_basis)}</span>` : ''}
                            ${food.primary_origin_country ? `<span>Origin: ${escapeHtml(food.primary_origin_country)}</span>` : ''}
                        </div>
                        ${food.ai_estimation_status ? `<div class="small text-muted">AI Status: ${escapeHtml(food.ai_estimation_status)}</div>` : ''}
                    </div>
                    <button class="btn btn-sm btn-ghost">
                        <i class="bi bi-arrow-right"></i>
                    </button>
                </div>
            </div>
        `;
    }

    /**
     * Escape HTML to prevent XSS
     */
    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    /**
     * Select FSANZ food and open create specification modal
     */
    function selectFSANZFood(foodId) {
        if (!foodId) {
            console.error('Food ID not found');
            return;
        }
        
        // Mark that we're opening from search modal
        fsanzSearchState.openedFromSearch = true;
        
        // Store current search state before closing modal
        const searchInput = document.getElementById('fsanz-search-input');
        fsanzSearchState.searchTerm = searchInput ? searchInput.value : '';
        
        // Store current results
        const listContainer = document.getElementById('fsanz-list');
        if (listContainer && listContainer.innerHTML) {
            fsanzSearchState.lastResults = listContainer.innerHTML;
        }
        
        // Close search modal
        if (fsanzSearchModal) {
            fsanzSearchModal.hide();
        }
        
        // Show loading indicator
        Swal.fire({
            title: 'Loading...',
            text: 'Fetching food data...',
            allowOutsideClick: false,
            allowEscapeKey: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        
        // Fetch full food data from API
        $.ajax({
            url: "{{ route('fsanz_food.api.get', ':id') }}".replace(':id', foodId),
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                Swal.close();
                if (response.success && response.data && fsanzSpecModal) {
                    // Open create specification modal with full food data
                    fsanzSpecModal.open(response.data);
                } else {
                    console.error('Failed to fetch food data');
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to load food data. Please try again.'
                    });
                }
            },
            error: function(xhr, status, error) {
                Swal.close();
                console.error('Error fetching food data:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to load food data. Please try again.'
                });
            }
        });
    }

    /**
     * Restore FSANZ search modal state
     */
    function restoreFSANZSearchState() {
        const searchInput = document.getElementById('fsanz-search-input');
        const listContainer = document.getElementById('fsanz-list');
        
        if (fsanzSearchState.searchTerm && searchInput) {
            // Restore search term
            searchInput.value = fsanzSearchState.searchTerm;
            
            // If we have stored results, restore them
            if (fsanzSearchState.lastResults) {
                listContainer.innerHTML = fsanzSearchState.lastResults;
                document.getElementById('fsanz-loading').classList.add('d-none');
                document.getElementById('fsanz-empty').classList.add('d-none');
                document.getElementById('fsanz-no-results').classList.add('d-none');
                
                // Show results count if it was shown before
                if (fsanzSearchState.searchTerm.trim() !== '') {
                    document.getElementById('fsanz-results-count').classList.remove('d-none');
                }
            } else {
                // Re-run search with stored term
                searchFSANZFoods(fsanzSearchState.searchTerm);
            }
        } else {
            // No search term, load initial foods
            loadInitialFSANZFoods();
        }
    }

    /**
     * Show loading state
     */
    function showFSANZLoading() {
        document.getElementById('fsanz-loading').classList.remove('d-none');
        document.getElementById('fsanz-empty').classList.add('d-none');
        document.getElementById('fsanz-no-results').classList.add('d-none');
        document.getElementById('fsanz-list').innerHTML = '';
    }

    /**
     * Show empty state
     */
    function showFSANZEmpty() {
        document.getElementById('fsanz-loading').classList.add('d-none');
        document.getElementById('fsanz-empty').classList.remove('d-none');
        document.getElementById('fsanz-no-results').classList.add('d-none');
    }

    /**
     * Show no results state
     */
    function showFSANZNoResults() {
        document.getElementById('fsanz-loading').classList.add('d-none');
        document.getElementById('fsanz-empty').classList.add('d-none');
        document.getElementById('fsanz-no-results').classList.remove('d-none');
        document.getElementById('fsanz-list').innerHTML = '';
    }

    /**
     * Show error state
     */
    function showFSANZError() {
        document.getElementById('fsanz-loading').classList.add('d-none');
        document.getElementById('fsanz-list').innerHTML = '<div class="text-center py-5 text-danger">Error loading foods. Please try again.</div>';
    }




</script>
@endpush