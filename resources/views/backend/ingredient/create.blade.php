@extends('backend.master', [
'pageTitle' => 'Raw Material',
'activeMenu' => [
'item' => 'Raw Material',
'subitem' => 'Raw Material',
'additional' => '',
],
'breadcrumbItems' => [
['label' => 'Data Entry', 'url' => '#'],
['label' => 'Raw Materials']
],
])

<style>
    .products.form-wizard .input-group select.form-control {
        background-image: none;
        border: 1px solid lightgrey;
        border-radius: 4px !important;
    }
    div#custom-text p {font-size: 11px;color: #808080ab !important;}
    
</style>


@section('content')
<div class="container-fluid products form-wizard">
    <div class="title-add">
        <div class="wizard-card">
            <div class="multisteps-form mb-5">
                <div class="multisteps-form__progress mb-5">
                    <button class="multisteps-form__progress-btn js-active" type="button" title="User Info"><span>DESCRIPTION</span></button>
                    <button class="multisteps-form__progress-btn" type="button" title="Address"><span>SPECIFICATION</span></button>
                </div>
            </div>
        </div>
        <div class="wizard-card-body">
            <form class="multisteps-form__form" id="ingredient_form" data-to="{{route('save.ingredient')}}" data-update-route="{{ route('update.raw-materials', ['id' => ':id']) }}">
                @csrf
                <input type="hidden" name="ing_form" id="ing_form" class="form-control" value="add">
                <input type="hidden" name="ing_form_id" id="ing_form_id" class="form-control" value="">
                <div class="multisteps-form__panel js-active" data-animation="FadeIn">
                    <div class="multisteps-form__content">
                        <div class="row mt-3">
                            <div class="col-lg-5 col-md-5 col-sm-12 col-12 mt-4 mt-sm-0">
                                <div class="input-group input-group-dynamic mb-4">
                                    <label class="form-label">Ingredient Name <span class="text-danger">*</span></label>
                                    <input type="text" name="ing_name" id="ing_name" class="form-control" value="" placeholder="Type Ingredient name here">
                                </div>
                                
                                <input name="default_image" id="default_image" type="hidden" value="" />
                                <div class="input-group input-group-dynamic flex-column mb-4">
                                    <label class="form-label">Ingredient Image</label>
                                    <div class="dropzone" id="dropzone">
                                        <span class="material-symbols-outlined upload-icon">upload</span>
                                        <p class="mt-1">Drag & drop files here or <span class="uploan-span">click to upload</span></p>
                                        <input type="file" id="fileInput" accept=".png,.jpg,.jpeg" multiple hidden>
                                        <span class="mt-1">Accepted file formats: "png, jpg, jpeg"</span>
                                    </div>
                                    <ul class="list-group mt-2" id="fileList" style="width: 100%;"></ul>
                                </div>
                                

                                <div class="input-group input-group-dynamic mb-4">
                                    <label class="form-label">Supplier Spec (URL)</label>
                                    <input type="text" name="supplier_spec_url" class="form-control" value="" placeholder="Insert shareable link (URL) to supplier spec sheet">
                                </div>

                                <div class="input-group input-group-dynamic mb-4">
                                    <label class="form-label">Ingredient Description</label>
                                    <input type="text" name="ing_description" class="form-control" value="" placeholder="Type Ingredient name here">
                                </div>
                                <div class="row">
                                    <div class="col-lg-6 col-md-6 col-12 col-sm-12 mt-4 mt-sm-0">
                                        <div class="input-group input-group-dynamic flex-column mb-4">
                                            <label class="form-label">Category</label>
                                            <select name="ing_category" id="ing_category" class="form-control-select js-example-basic-single">
                                                <option disabled selected>Select Category </option>
                                                @foreach($categories as $cat)
                                                <option value="{{$cat['id']}}">{{$cat['name']}}</option>
                                                @endforeach
                                            </select>
                                            <!-- <div class="mt-3">
                                                <button type="button" class="btn btn-primary-orange plus-icon" onclick="save_ingredient_source(this)" data-source="category"><span class="material-symbols-outlined">add</span></button>
                                            </div> -->
                                        </div>
                                    </div>
                                    <div class="col-lg-6 col-md-6 col-12 col-sm-12 mt-4 mt-sm-0">
                                        <div class="input-group input-group-dynamic flex-column mb-4">
                                            <label class="form-label">Tags</label>
                                            <select name="ing_tags[]" id="ing_tags" class="form-control-select select2-tags" multiple>
                                                @foreach($Tags as $tag)
                                                <option value="{{$tag['id']}}">{{$tag['name']}}</option>
                                                @endforeach
                                            </select>
                                            <!-- <div class="mt-3">
                                                <button type="button" class="btn btn-primary-orange plus-icon" onclick="save_ingredient_source(this)" data-source="sub_category"><span class="material-symbols-outlined">add</span></button>
                                            </div> -->
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-12 col-md-12 col-12 col-sm-12 mt-4 mt-sm-0">
                                        <div class="input-group input-group-dynamic mb-4">
                                            <label class="form-label">Name Provided by Supplier</label>
                                            <input type="text" name="ing_supplier_name" class="form-control" value="" placeholder="Type Ingredient Supplier name here">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-5 col-md-5 col-12 col-sm-12 mt-4 mt-sm-0">
                                        <div class="input-group input-group-dynamic flex-column mb-4">
                                            <label class="form-label">Supplier Name</label>
                                            <select name="ing_supplier" id="ing_supplier" class="form-control-select js-example-basic-single">
                                                <option disabled selected>Select Supplier</option>
                                                @foreach($supplier as $sup)
                                                <option value="{{$sup['id']}}">{{$sup['company_name']}}</option>
                                                @endforeach
                                            </select>
                                            <!-- <div class="mt-3">
                                                <button type="button" class="btn btn-primary-orange plus-icon" onclick="save_ingredient_source(this)" data-source="supplier"><span class="material-symbols-outlined">add</span></button>
                                            </div> -->
                                        </div>
                                    </div>
                                    <div class="col-lg-4 col-md-4 col-12 col-sm-12 mt-4 mt-sm-0">
                                        <div class="input-group input-group-dynamic mb-4">
                                            <label class="form-label">Supplier Code</label>
                                            <input type="text" name="ing_supplier_code" name="ing_supplier_code" class="form-control" value="" placeholder="Type Supplier code">
                                        </div>
                                    </div>
                                    <div class="col-lg-3 col-md-3 col-12 col-sm-12 mt-4 mt-sm-0">
                                        <div class="input-group input-group-dynamic mb-4">
                                            <label class="form-label">GTIN</label>
                                            <input type="text" name="ing_gtin" name="ing_gtin" class="form-control" value="" placeholder="Type GTIN here">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-6 col-md-6 col-12 col-sm-12 mt-4 mt-sm-0">
                                        <div class="input-group input-group-dynamic flex-column mb-4">
                                            <label class="form-label">Status</label>
                                            <select name="raw_material_status" id="raw_material_status" class="form-control-select js-example-basic-single">
                                                <option disabled selected>Select Status</option>
                                                @foreach($statusArray as $status)
                                                <option value="{{$status}}">{{$status}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-lg-6 col-md-6 col-12 col-sm-12 mt-4 mt-sm-0">
                                        <div class="input-group input-group-dynamic flex-column mb-4">
                                            <label class="form-label">Ranging</label>
                                            <select name="raw_material_ranging" id="raw_material_ranging" class="form-control-select js-example-basic-single">
                                                <option disabled selected>Select Range</option>
                                                @foreach($rangeArray as $range)
                                                <option value="{{$range}}">{{$range}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-5 col-md-5 col-sm-12 col-12 mt-4 mt-sm-0">
                                <div class="input-group input-group-dynamic mb-4">
                                    <label class="form-label">Ingredient SKU <span class="text-danger">*</span></label>
                                    <input type="text" name="ing_sku" id="ing_sku" class="form-control" value="" placeholder="Type Ingredient sku here">
                                </div>
                            </div>
                            <div class="col-lg-2 col-md-2 col-sm-12 col-12 mt-sm-0">
                                <div class="button-row d-flex">
                                    <button class="btn btn-secondary-blue" type="button" title="Next" onclick="form_temp_submit(this)">Save</button>
                                    <button class="btn btn-secondary-white mb-0 js-btn-next" type="button" title="Next">Next</button>
                                </div>
                                <div class="mt-4">
                                    <x-raw-material-details :ingredient="[]" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="multisteps-form__panel" data-animation="FadeIn">
                    <div class="multisteps-form__content">
                        <div class="row">
                            <div class="col-lg-10 col-md-10 col-12 col-sm-12 mt-4 mt-sm-0">
                                <div class="row mt-3">
                                    <div class="col-lg-6 col-md-6 col-12 col-sm-12 mt-4 mt-sm-0">
                                        <div class="input-group input-group-dynamic flex-column">
                                            <label class="form-label">Country of Origin</label>
                                            <select name="ing_country" id="ing_country" class="form-control-select mb-4">
                                                <option disabled selected>Select Country of Origin</option>
                                                @foreach($country as $coun)
                                                <option value="{{$coun['COID']}}">{{$coun['full_name']}}</option>
                                                @endforeach
                                            </select>

                                            <div class="mb-4">
                                                <label class="form-label">Australian Percent</label>
                                                <div class="row">
                                                    <div class="col-lg-11 col-md-11 col-sm-11 col-11">
                                                        <input type="text" name="ing_aus_per" id="ing_aus_per" step="0.1" min="0" class="form-control ph-blue text-end" value="" placeholder="0">
                                                    </div>
                                                    <div class="col-lg-1 col-md-1 col-sm-1 col-1 p-0">
                                                        <p class="mt-2">%</p>
                                                    </div>
                                                </div>
                                            </div>
                                            
                           
                                            <div class="input-group input-group-dynamic flex-column mb-4">
                                                <label class="form-label">Is liquid</label>
                                                <select name="ing_spec_unit" id="ing_spec_unit" class="form-control-select">
                                                    <option disabled>Select Options</option>
                                                    <option value="Yes">Yes</option>
                                                    <option value="No" selected>No</option>
                                                </select>
                                            </div>
                                            <div class="input-group input-group-dynamic mb-4">
                                                <label class="form-label">Ingredient List</label>
                                                <textarea class="form-control ph-blue" name="ing_ing_list" id="ing_ing_list"></textarea>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-6 col-md-6 col-12 col-sm-12 mt-4 mt-sm-0">
                                        <div class="input-group input-group-dynamic flex-column mb-4">
                                            <label class="form-label">Allergens</label>
                                            <select name="ing_allergen[]" id="ing_allergen" class="form-control-select fa-basic-multiple" multiple>
                                                @foreach($allergen as $aller)
                                                <option value="{{$aller['name']}}">{{$aller['name']}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <label class="form-label mt-2">Raw Material Purchase Price</label>
                                <table class="table table-borderless responsiveness input-table">
                                    <tr>
                                        <th class="primary-text-dark fw-bold text-end" width="25%">Purchase Price</th>
                                        <th class="primary-text-dark fw-bold text-end" width="25%">Purchase Volume</th>
                                        <th class="primary-text-dark fw-bold" width="25%">Purchase Quantity</th>
                                        <th class="primary-text-dark fw-bold text-end" width="25%">Specific Gravity</th>
                                        <th class="primary-text-dark fw-bold text-end" width="25%">Price per kg</th>
                                    </tr>
                                    <tr>
                                        <td>
                                            <div class="input-group input-group-dynamic">
                                                <input type="text" name="ing_total_price" id="ing_total_price" step="0.1" min="0" class="form-control ph-blue text-end" placeholder="0">
                                            </div>
                                        </td>
                                        
                                        <td>
                                            <div class="input-group input-group-dynamic table-active-input">
                                                <input type="text" name="ing_quantity" id="ing_quantity" step="0.1" min="0" class="form-control unit_weight_input text-end" value="" placeholder="0">
                                            </div>
                                        </td>
                                        <td>
                                            <div class="input-group input-group-dynamic">
                                                <select name="ing_quantity_unit" id="ing_quantity_unit" class="form-select">
                                                    <option disabled selected>Select Unit</option>
                                                    <option value="g">g</option>
                                                    <option value="kg">kg</option>
                                                    <option value="ml">mL</option>
                                                    <option value="l">L</option>
                                                </select>
                                            </div>
                                        </td>
                                            <td>
                                                <div class="input-group input-group-dynamic table-active-readonly">
                                                    <input type="text" name="ing_spec_gravity" id="ing_spec_gravity" step="0.1" min="0" class="form-control unit_weight_input text-end" value="" placeholder="0" readonly>
                                                </div>
                                            </td>
                                        <td>
                                            <div class="input-group input-group-dynamic table-active-readonly">
                                                <input type="hidden" name="ing_unit_price" id="ing_unit_price" step="0.1" min="0" class="form-control unit_weight_input text-end" value="" placeholder="0">
                                                <input type="text" name="ing_unit_kg_price" id="ing_unit_kg_price" step="0.1" min="0" class="form-control unit_weight_input text-end" value="" placeholder="0" readonly>
                                            </div>
                                        </td>
                                    </tr>
                                </table>
                                <label class="form-label mt-2">Nutritional Specification per 100g</label>
                                <table class="table table-borderless responsiveness input-table">
                                    <tr>
                                        <th class="primary-text-dark fw-bold text-end" width="15%">Energy <br>(kJ)</th>
                                        <th class="primary-text-dark fw-bold text-end" width="15%">Protein <br>(g)</th>
                                        <th class="primary-text-dark fw-bold text-end" width="15%">Total Fat <br>(g)</th>
                                        <th class="primary-text-dark fw-bold text-end" width="15%">Saturated Fat <br>(g)</th>
                                        <th class="primary-text-dark fw-bold text-end" width="15%">Available Carb <br>(g)</th>
                                        <th class="primary-text-dark fw-bold text-end" width="15%">Total Sugar <br>(g)</th>
                                        <th class="primary-text-dark fw-bold text-end" width="15%">Sodium <br>(mg)</th>
                                    </tr>
                                    <tr>
                                        <td>
                                            <div class="input-group input-group-dynamic table-active-input">
                                                <input type="text" name="ing_energy" id="ing_energy" step="0.1" min="0" class="form-control unit_weight_input text-end" value="" placeholder="0">
                                            </div>
                                        </td>
                                        <td>
                                            <div class="input-group input-group-dynamic table-active-input">
                                                <input type="text" name="ing_protein" id="ing_protein" step="0.1" min="0" class="form-control ph-blue text-end" value="" placeholder="0">
                                            </div>
                                        </td>
                                        <td>
                                            <div class="input-group input-group-dynamic table-active-input">
                                                <input type="text" name="ing_total_fat" id="ing_total_fat" step="0.1" min="0" class="form-control ph-blue text-end" value="" placeholder="0">
                                            </div>
                                        </td>
                                        <td>
                                            <div class="input-group input-group-dynamic table-active-input">
                                                <input type="text" name="ing_saturated_fat" id="ing_saturated_fat" step="0.1" min="0" class="form-control ph-blue text-end" value="" placeholder="0">
                                            </div>
                                        </td>
                                        <td>
                                            <div class="input-group input-group-dynamic table-active-input">
                                                <input type="text" name="ing_avail_corb" id="ing_avail_corb" step="0.1" min="0" class="form-control ph-blue text-end" value="" placeholder="0">
                                            </div>
                                        </td>
                                        <td>
                                            <div class="input-group input-group-dynamic table-active-input">
                                                <input type="text" name="ing_total_sugar" id="ing_total_sugar" step="0.1" min="0" class="form-control ph-blue text-end" value="" placeholder="0">
                                            </div>
                                        </td>
                                        <td>
                                            <div class="input-group input-group-dynamic table-active-input">
                                                <input type="text" name="ing_sodium" id="ing_sodium" step="0.1" min="0" class="form-control ph-blue text-end" value="" placeholder="0">
                                            </div>
                                        </td>
                                    </tr>
                                </table>

                                <div class="input-group input-group-dynamic mb-4">
                                    <label class="form-label">Shelf Life and storage instructions</label>
                                    <textarea class="form-control ph-blue" name="ing_shelf" id="ing_shelf" placeholder="Type shelf life and storage as you want it to appear on label"></textarea>
                                    <!-- <input type="text" name="ing_shelf" name="ing_shelf" class="form-control" value="" placeholder="Type Shelf Life here"> -->
                                </div>

                                <div class="row mt-5">
                                    <label class="form-label">Sutiability to make certain claims</label>
                                    <p class="primary-text-dark">Specify if the product is suitable for use in product intended for the following consumer uses.</p>
                                    <table class="table responsiveness mx-3" id="dynamicIngredients">
                                        <thead>
                                            <tr>
                                                <th class="primary-text-dark fw-bold" width="40%"></th>
                                                <th class="primary-text-dark fw-bold" width="13%">Specify if suitable for (Y/N)</th>
                                                <th class="primary-text-dark fw-bold" width="30%">How has this been validated?</th>
                                                <th class="primary-text-dark fw-bold" width="17%">Certificate available (Y/N)</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach(['Halal','Kosher','Organic','Biodynamic','Octo-lacto-vegetarian','Lacto-vegetarian','Vegan'] as $index => $suits)
                                            @php
                                                $pr_label = "";
                                                switch ($suits) {
                                                    case 'Biodynamic':
                                                        $pr_label = "bio";
                                                        break;
                                                    case 'Octo-lacto-vegetarian':
                                                        $pr_label = "octo";
                                                        break;
                                                    case 'Lacto-vegetarian':
                                                        $pr_label ="lacto"; 
                                                        break;
                                                    default:
                                                        $pr_label = strtolower($suits);
                                                        break;
                                                }
                                            @endphp
                                            <tr>
                                                <td class="primary-text-dark">{{$suits}}</td>
                                                <td>
                                                    @php $rm = "rm_{$pr_label}_yn"; @endphp
                                                    <select class="suit_section form-select suitable_select" name="rm_{{$pr_label}}_yn">
                                                        <option value="Yes">Yes</option>
                                                        <option value="No">No</option>
                                                        <option value="Unknown">Unknown</option>
                                                    </select>
                                                </td>
                                                <td>
                                                    @php $rm1 = "rm_{$pr_label}_validated"; @endphp
                                                    <select class="suit_section form-select" name="rm_{{$pr_label}}_validated">
                                                        <option value="Based on ingredients">Based on ingredients</option>
                                                        <option value="Through certification">Through certification</option>
                                                        <option value="na">na</option>
                                                    </select>
                                                </td>
                                                <td>
                                                    @php $rm2 = "rm_{$pr_label}_certification_yn"; @endphp
                                                    <select class="suit_section form-select" name="rm_{{$pr_label}}_certification_yn">
                                                        <option value="Yes">Yes</option>
                                                        <option value="No">No</option>
                                                        <option value="Unknown">Unknown</option>
                                                    </select>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>

                                    <label class="form-label mt-4">Durability, Packaging and Supply Chain</label>
                                    <table class="table responsiveness mx-3" id="dynamicIngredients1">
                                        <thead>
                                            <tr>
                                                <th class="text-primary-orange" width="40%"></th>
                                                <th class="text-primary-orange" width="13%"></th>
                                                <th class="text-primary-orange" width="30%"></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr style="height: 50px;">
                                                <td class="primary-text-dark fw-bold">As supplied (unopened pack or bulk)</td><td></td><td></td>
                                            </tr>
                                            <tr>
                                                <td class="primary-text-dark">Shelf Life:</td>
                                                <td>
                                                <input type="text" name="rm_supplied_shelf_life_num" id="rm_supplied_shelf_life_num" class="form-control text-right numeric-input"  value="" />
                                                </td>
                                                <td>
                                                    <select class="suit_section form-select" name="rm_supplied_shelf_life_units">
                                                        <option value="Days">Days</option>
                                                        <option value="Weeks">Weeks</option>
                                                        <option value="Months">Months</option>
                                                        <option value="Years">Years</option>
                                                    </select>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="primary-text-dark">Temperature controlled during storage:</td>
                                                <td>
                                                    <select class="suit_section form-select" name="rm_suppied_temp_control_storage_num">
                                                        <option value="Yes">Yes</option>
                                                        <option value="No">No</option>
                                                        <option value="Unknown">Unknown</option>
                                                    </select>
                                                </td>
                                                <td>
                                                    <select class="suit_section form-select" name="rm_suppied_temp_control_storage_degrees">
                                                        <option value="≤ 20°C (Ambient)">≤ 20°C (Ambient)</option>
                                                        <option value="≤ 5°C (Refridgerated)">≤ 5°C (Refridgerated)</option>
                                                        <option value="≤ -15 (Frozen)">≤ -15 (Frozen)</option>
                                                    </select>
                                                </td>
                                            </tr>

                                            <tr>
                                                <td class="primary-text-dark">Temperature controlled during transport:</td>
                                                <td>
                                                    <select class="suit_section form-select" name="rm_supplied_temp_control_transport_yn">
                                                        <option value="Yes">Yes</option>
                                                        <option value="No">No</option>
                                                        <option value="Unknown">Unknown</option>
                                                    </select>
                                                </td>
                                                <td>
                                                    <select class="suit_section form-select" name="rm_supplied_temp_control_transport_degrees">
                                                        <option value="≤ 20°C (Ambient)">≤ 20°C (Ambient)</option>
                                                        <option value="≤ 5°C (Refridgerated)">≤ 5°C (Refridgerated)</option>
                                                        <option value="≤ -15 (Frozen)">≤ -15 (Frozen)</option>
                                                    </select>
                                                </td>
                                            </tr>

                                            <tr style="height: 50px;">
                                                <td class="primary-text-dark fw-bold">Product - Once in Use (resealable pack or bulk container)</td><td></td><td></td>
                                            </tr>

                                            <tr>
                                                <td class="primary-text-dark">Shelf Life:</td>
                                                <td>
                                                <input type="text" name="rm_inuse_shelf_life_num" id="rm_inuse_shelf_life_num" class="form-control text-right numeric-input" value="" />
                                                </td>
                                                <td>
                                                    <select class="suit_section form-select" name="rm_inuse_shelf_life_units">
                                                        <option value="Days">Days</option>
                                                        <option value="Weeks">Weeks</option>
                                                        <option value="Months">Months</option>
                                                        <option value="Years">Years</option>
                                                    </select>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="primary-text-dark">Temperature controlled during storage:</td>
                                                <td>
                                                    <select class="suit_section form-select" name="rm_inuse_temp_control_storage_num">
                                                        <option value="Yes">Yes</option>
                                                        <option value="No">No</option>
                                                        <option value="Unknown">Unknown</option>
                                                    </select>
                                                </td>
                                                <td>
                                                    <select class="suit_section form-select" name="rm_inuse_temp_control_storage_degrees">
                                                        <option value="≤ 20°C (Ambient)">≤ 20°C (Ambient)</option>
                                                        <option value="≤ 5°C (Refridgerated)">≤ 5°C (Refridgerated)</option>
                                                        <option value="≤ -15 (Frozen)">≤ -15 (Frozen)</option>
                                                    </select>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    
                                    <table class="table responsiveness mx-3 mt-4" id="dynamicIngredients2">
                                        <thead style="display:none;">
                                            <tr>
                                                <th class="text-primary-orange" width="50%"></th>
                                                <th class="text-primary-orange" width="50%"></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr style="height: 50px;">
                                                <td class="primary-text-dark fw-bold">Other</td><td></td>
                                            </tr>

                                            <tr>
                                                <td class="primary-text-dark">Specifiy any other storage requirements::</td>
                                                <td>
                                                <select class="suit_section form-select" name="rm_storage_requirement">
                                                        <option value="Once opened, store in an airtight container in a cool, dry place (≤ 20°C).">Once opened, store in an airtight container in a cool, dry place (≤ 20°C).</option>
                                                        <option value="Once opened, store in an airtight container and keep refrigerated (≤ 5°C).">Once opened, store in an airtight container and keep refrigerated (≤ 5°C).</option>
                                                        <option value="Once opened, store in an airtight container and keep frozen (≤ -15°C).">Once opened, store in an airtight container and keep frozen (≤ -15°C).</option>
                                                    </select>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="primary-text-dark">Indended use:</td>
                                                <td>
                                                    <select class="suit_section form-select" name="rm_indended_use">
                                                        <option value="This product is intended for general consumption">This product is intended for general consumption</option>
                                                        <option value="For general consumption">For general consumption</option>
                                                        <option value="Other">Other</option>
                                                    </select>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="primary-text-dark">Specifiy type of date mark to be used:</td>
                                                <td>
                                                    <select class="suit_section form-select" name="rm_date_mark">
                                                        <option value="Use Before">Use Before</option>
                                                        <option value="Best Before">Best Before</option>
                                                    </select>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>

                                    <label class="form-label mt-4">Hazards</label>
                                    <table class="table responsiveness mx-3" id="dynamicIngredients3">
                                        <thead>
                                            <tr>
                                                <th class="text-primary-orange" width="80%"></th>
                                                <th class="text-primary-orange" width="20%"></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td class="primary-text-dark">Are there any potential hazards associated with the product?</td>
                                                <td>
                                                    <select class="suit_section form-select" name="rm_hazard_yn">
                                                        <option value="Yes">Yes</option>
                                                        <option value="No">No</option>
                                                        <option value="Unknown">Unknown</option>
                                                    </select>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>

                                    <div id="custom-text">
                                        <p>Potentially hazardous food means food that has to be kept at certain temperatures to minimise the growth of any pathogenic microorganisms that may be present in the food or to prevent the formation of toxins in the food. (Source: STANDARD 3.2.2 FOOD SAFETY PRACTICES AND GENERAL REQUIREMENTS </p>
                                    </div>
                                </div>

                            </div>
                            <div class="col-lg-2 col-md-2 col-12 col-sm-12 mt-sm-0">
                                <div class="button-row d-flex text-end">
                                    <button class="btn btn-secondary-white mb-0 js-btn-prev" type="button" title="Prev">Back</button>
                                    <button class="btn btn-secondary-blue mb-0 me-1 js-btn-save" type="button" title="Save"  onclick="form_submit(this)">Save</button>
                                    <button class="btn btn-secondary-blue mb-0 js-btn-save" type="button" title="Finish" onclick="form_submit(this)">Finish</button>
                                </div>
                                <div class="mt-4">
                                    <x-raw-material-details :ingredient="[]" />
                                    <x-raw-material-info/>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
</div>

<div class="modal fade" id="ingredientModal" tabindex="-1" aria-labelledby="ingredientModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="ingredientModalLabel"></h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="ing_source" data-to="{{route('save.source')}}">
                @csrf
                <input type="hidden" name="ingre_sour" id="ingre_sour">
                <div class="modal-body">

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary-white" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-secondary-blue" onclick="form_submit(this)">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection





@push('scripts')
<script src="{{ asset('assets') }}/js/multistep-form.js"></script>
<script src="{{ asset('assets') }}/js/ingredient.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="{{ asset('assets') }}/js/dropzone.js"></script>
<script>
    $(window).on('load',function(){
        $('table#dynamicIngredients tbody tr').each(function(){
            $(this).find('select.suitable_select').val('No').trigger('change')
        })

        $('#ing_supplier').select2({
            width: '100%'
        });

    })
</script>
@endpush