<!-- Form for updating product details in step 2 -->
<style>
    .drag-handle {cursor: move;}
    .row-placeholder {background: var(--secondary-light-primary-color-10);border: 2px dashed var(--secondary-primary-color);height: 50px;}
</style>

<form id="form_step_2" action="{{ route('products.updateStep2', $product) }}" method="POST">
    @csrf
    @method('PUT')
    <div class="row mt-3">
        <div class="col-lg-10 col-md-10 col-sm-12 col-12 mt-4 mt-sm-0">
            <div class="quill-editor-wrapper">
                <label class="form-label">Recipe Method</label>
                <div class="quill-editor" data-input="recipe_method"></div>
                <input type="hidden" name="recipe_method" value="{{ old('recipe_method', $product->recipe_method) }}">
                @error('recipe_method')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="quill-editor-wrapper">
                <label class="form-label">Recipe Notes</label>
                <div class="quill-editor" data-input="recipe_notes"></div>
                <input type="hidden" name="recipe_notes" value="{{ old('recipe_notes', $product->recipe_notes) }}">
                @error('recipe_notes')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="row">
                <div class="col-lg-6 col-md-6 col-sm-12 col-12">
                    <label class="form-label mt-2">Baking</label>
                    <table class="table table-borderless border-none mt-1">
                        <tr>
                            <td class="text-primary-dark-mud align-middle">Oven Time</td>
                            <td>
                                <div class="input-group input-group-dynamic">
                                    <input type="text"
                                        name="recipe_oven_time"
                                        class="form-control recipe_oven_time_input @error('recipe_oven_time') is-invalid @enderror text-end"
                                        placeholder="HH:MM:SS"
                                        value="{{ old('recipe_oven_time', $product->formatted_oven_time) }}"
                                        onkeypress="formatTime(this)"
                                        maxlength="8">
                                    @error('recipe_oven_time')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </td>
                            <td class="text-start"> HH:MM:SS </td>
                        </tr>
                        <tr>
                            <td class="text-primary-dark-mud align-middle">Oven Temperature</td>
                            <td>
                                <div class="input-group input-group-dynamic">
                                    <input type="text"
                                        name="recipe_oven_temp"
                                        id="recipe_oven_temp"
                                        step="1"
                                        min="0"
                                        class="form-control ph-blue @error('recipe_oven_temp') is-invalid @enderror text-end"
                                        value="{{ old('recipe_oven_temp', $product->recipe_oven_temp !== null ? round($product->recipe_oven_temp) : '') }}"
                                        placeholder="">

                                    @error('recipe_oven_temp')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </td>
                            <td>
                                <select class="recipe_oven_temp_unit form-select @error('recipe_oven_temp_unit') is-invalid @enderror text-end" name="recipe_oven_temp_unit">
                                    <option value="C" {{ old('recipe_oven_temp_unit', $product->recipe_oven_temp_unit) == 'C' ? 'selected' : '' }}>&deg;C</option>
                                    <option value="F" {{ old('recipe_oven_temp_unit', $product->recipe_oven_temp_unit) == 'F' ? 'selected' : '' }}>&deg;F</option>
                                </select>
                                @error('recipe_oven_temp_unit')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
            <div class="col-12 mt-3">
                <input type="hidden" name="product_id" value="{{ $product->id }}">
                <input type="hidden" name="default_component" value="{{ $default_component }}">
                <div class="">
                    <table class="table responsiveness" id="dynamicIngredients">
                        <thead>
                            <tr>
                                <th class="text-primary-orange" width="35%">Ingredient Name</th>
                                <th class="text-primary-orange" width="15%">Supplier</th>  
                                <th class="text-primary-orange text-end" width="8%">Quantity</th>
                                <th class="text-primary-orange" width="8%">Unit</th>
                                <th class="text-primary-orange" width="12%">Component</th>
                                <th class="text-primary-orange" width="20%">Kitchen Comments</th>
                                <th class="text-primary-orange text-center" width="10%">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($prod_ings as $index => $prod_ing)
                            @php 
                                $spec =  $prod_ing->spec_grav;
                                $specVal =  (!empty($spec) && $spec != 0) ? $spec : 1;
                                if($prod_ing->units_g_ml == "g"){
                                    $ing_weight = $prod_ing->quantity_weight;
                                }elseif($prod_ing->units_g_ml == "kg"){
                                    $ing_weight = $prod_ing->quantity_weight * 1000;
                                }elseif($prod_ing->units_g_ml == "ml" || $prod_ing->units_g_ml == "mL"){
                                    $ing_weight = $prod_ing->quantity_weight * $specVal;
                                }elseif($prod_ing->units_g_ml == "l" || $prod_ing->units_g_ml == "L"){
                                    $ing_weight = ($prod_ing->quantity_weight * $specVal)*1000;
                                }
                                
                            @endphp
                            <tr class="ingredient-row">
                                <td class="drag-handle" draggable="true">
                                    <input type="hidden" name="IngFields[{{ $index }}][id]" id="id{{ $index }}" value="{{ $prod_ing->id }}" />
                                    <div style="display: flex;flex-direction: column;position: absolute;z-index: 1;top: 20px; left: 10px;cursor: pointer;">   
                                        <span class="material-symbols-outlined">drag_indicator</span>
                                    </div>
                                    
                                    <select name="IngFields[{{ $index }}][ing_id]" class="form-select select2input ingname_selection" required>
                                        <option value="">--Select Ingredient--</option>
                                        @foreach($ingredients as $ingredient)
                                        @php
                                        $sequenceNumber = is_numeric($ingredient->ing_image) ? (int)$ingredient->ing_image : null;                                    
                                        $ing_image = getModuleImage('raw_material', $ingredient->id, $sequenceNumber);
                                        @endphp
                                        {{ $ingredient->ing_image }}
                                        {{ $ing_image }}
                                        <option  
                                            value="{{ $ingredient->id }}"
                                            data-ingredient-name="{{ $ingredient->name_by_kitchen }}"
                                            data-ingredient-sku="{{ $ingredient->ing_sku }}"
                                            data-ingredient-image="{{ $ing_image }}"
                                            data-ingredient-unit="{{ $ingredient->purchase_units}}"
                                            data-specific-gravity="{{ $ingredient->specific_gravity}}"
                                            data-ingredient-supplier = "{{ $ingredient->supplier->company_name ?? '' }}"
                                            {{ $prod_ing->ing_id == $ingredient->id ? 'selected' : '' }}>
                                            {{ $ingredient->name_by_kitchen }} {{$ingredient->ing_sku }}
                                        </option>
                                        @endforeach
                                    </select>
                                </td>
                                
                                <td class="text-right">
                                   <p>{{ $prod_ing->ing_supplier ?? ""}}</p>
                                </td>

                                <td class="text-right">
                                    <input type="text"
                                        name="IngFields[{{ $index }}][quantity_weight]"
                                        class="form-control text-right"
                                        step="1"
                                        min="0"
                                        value="{{ round($prod_ing->quantity_weight,1) }}"
                                        data-ing-value="{{ round($ing_weight,1) }}"
                                        style="text-align: right;" required />
                                </td>
                                
                                <td>
                                    <select class="ingunit_section form-select" id="units_g_ml{{ $index }}" name="IngFields[{{ $index }}][units_g_ml]">
                                        <option value="g" {{ $prod_ing->units_g_ml == 'g' ? 'selected' : '' }}>g</option>
                                        <option value="kg" {{ $prod_ing->units_g_ml == 'kg' ? 'selected' : '' }}>kg</option>
                                        <option value="ml" {{ ($prod_ing->units_g_ml == 'ml' || $prod_ing->units_g_ml == 'mL') ? 'selected' : '' }}>mL</option>
                                        <option value="l" {{ ($prod_ing->units_g_ml == 'l' || $prod_ing->units_g_ml == 'L' ) ? 'selected' : '' }}>L</option>
                                    </select>
                                </td>
                                <td>
                                    <select name="IngFields[{{ $index }}][component]" class="form-select" required>
                                        <option @if($prod_ing->component == null) selected @endif disabled>Select Component</option>
                                        @foreach($recipe_components as $component)
                                            <option value="{{$component->name}}" @if($prod_ing->component == $component->name)selected @endif>{{$component->name}}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <input type="text"
                                        name="IngFields[{{ $index }}][kitchen_comments]"
                                        class="form-control"
                                        value="{{ $prod_ing->kitchen_comments }}" />
                                </td>
                                <td class="text-center">
                                    <div class="moreAction d-flex">
                                        @if(isset($prod_ing->ingredient->raw_material_ranging) && $prod_ing->ingredient->raw_material_ranging == "Not Available")
                                        <div class="icon-primary-orange me-2" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-custom-class="custom-tooltip" data-bs-title="Ingredient is Not Available" data-bs-original-title="Warning"><i class="material-symbols-outlined">warning</i></div>
                                        @endif
                                        <div class="remove-ingredient delete-icon cursor-pointer text-danger"><i class="material-symbols-outlined">delete</i></div>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr id="no_ingredients_row">
                                <td colspan="6" class="text-center">No ingredients added yet</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="text-end mt-3">
                    <button type="button" id="add_ingredient" class="btn btn-sm btn-primary-orange plus-icon" title="Add Ingredient">
                        <span class="material-symbols-outlined">add</span>
                    </button>
                </div>

                <div class="loss_nutrition_area">
                    <div class="row mt-4">
                        <div class="col-6 col-sm-6 col-12 mt-4 mt-sm-0">
                            <div class="">
                                <label class="nutrition_info_title form-label mt-2">Loss Details</label>
                                <div class="loss_entry_area">
                                    <table class="table table-borderless border-none">
                                        <tr>
                                            <td class="text-primary-dark-mud align-middle" width="50%">Weight Gain or Loss (%) </td>
                                            <td width="30%">
                                                <div class="input-group input-group-dynamic mb-2">
                                                    <input type="text" name="batch_baking_loss_percent" id="batch_baking_loss_percent" class="form-control text-end unit_weight_input @error('batch_baking_loss_percent') is-invalid @enderror" value="{{ old('batch_baking_loss_percent', ($product->batch_baking_loss_percent != null) ? $product->batch_baking_loss_percent : '0' ) }}" placeholder="" required>
                                                </div>
                                            </td>
                                            <td class="text-primary-dark-mud align-middle" width="20%">%</td>
                                        </tr>
                                        {{--
                                        <tr class="hidden">
                                            <td class="text-primary-dark-mud align-middle" width="50%">Waste (%)</td>
                                            <td width="30%">
                                                <div class="input-group input-group-dynamic mb-2">
                                                    <input type="number" name="batch_waste_percent" id="batch_waste_percent" step="0.01" min="0" class="form-control unit_weight_input @error('batch_waste_percent') is-invalid @enderror" value="{{ old('batch_waste_percent', $product->batch_waste_percent) }}" placeholder="" required>
                                                </div>
                                            </td>
                                            <td class="text-primary-dark-mud align-middle" width="20%">%</td>
                                        </tr>
                                    --}}
                                </table>
                            </div>
                        </div>
                        <div class="serving_entry_area  mt-4 mt-sm-0">
                            <label class="serving_entry_title form-label mt-2">Servings</label>
                            <table class="table table-borderless border-none">
                                <tr>
                                    <td class="text-primary-dark-mud align-middle" width="50%">Serving Per Package </td>
                                    <td width="30%">
                                        <div class="input-group input-group-dynamic mb-2">
                                            <input type="text"
                                                name="serv_per_package"
                                                step="1"
                                                min="0"
                                                class="form-control ph-blue @error('serv_per_package') is-invalid @enderror text-end"
                                                id="serv_per_package"
                                                value="{{ old('serv_per_package', ($product->serv_per_package !== null && $product->serv_per_package > 0) ? number_format($product->serv_per_package, 0) : '1') }}"
                                                placeholder="">

                                        </div>
                                    </td>
                                    <td class="text-primary-dark-mud align-middle" width="20%">#</td>
                                </tr>
                                <tr>
                                    <td class="text-primary-dark-mud align-middle" width="50%">Serving Size (g) </td>
                                    <td width="30%">
                                        <div class="input-group input-group-dynamic mb-2">
                                            <input type="text" name="serv_size_g" id="serv_size_g" step="0.01" min="0" class="form-control unit_weight_input text-end number-input @error('serv_size_g') is-invalid @enderror" value="{{ old('serv_size_g', ($product->serv_size_g !== null && $product->serv_size_g > 0) ? number_format($product->serv_size_g, 1) : '100') }}" placeholder="" required>
                                        </div>
                                    </td>
                                    <td class="text-primary-dark-mud align-middle" width="20%">g</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <div class="col-6 col-sm-6 col-12 mt-4 mt-sm-0 pe-md-0">
                        <div class="nutrition_area card">
                            <h5 class="text-primary-orange mt-2 pb-2">Nutritional Information</h5>
                            <div class="nutrition_display_area">
                                <input type="hidden" id="labelling_ingredients" name="labelling_ingredients" />
                                <input type="hidden" id="labelling_allergens" name="labelling_allergens" />
                                <input type="hidden" id="labelling_may_contain" name="labelling_may_contain" />


                                <!-- Form fields for nutritional values per 100g -->
                                <input type="hidden" name="energy_kJ_per_100g" id="energy_kJ_per_100g">
                                <input type="hidden" name="protein_g_per_100g" id="protein_g_per_100g">
                                <input type="hidden" name="fat_total_g_per_100g" id="fat_total_g_per_100g">
                                <input type="hidden" name="fat_saturated_g_per_100g" id="fat_saturated_g_per_100g">
                                <input type="hidden" name="carbohydrate_g_per_100g" id="carbohydrate_g_per_100g">
                                <input type="hidden" name="sugar_g_per_100g" id="sugar_g_per_100g">
                                <input type="hidden" name="sodium_mg_per_100g" id="sodium_mg_per_100g">

                                <!-- Form fields for nutritional values per serving -->
                                <input type="hidden" name="energy_kJ_per_serve" id="energy_kJ_per_serve">
                                <input type="hidden" name="protein_g_per_serve" id="protein_g_per_serve">
                                <input type="hidden" name="fat_total_g_per_serve" id="fat_total_g_per_serve">
                                <input type="hidden" name="fat_saturated_g_per_serve" id="fat_saturated_g_per_serve">
                                <input type="hidden" name="carbohydrate_g_per_serve" id="carbohydrate_g_per_serve">
                                <input type="hidden" name="sugar_g_per_serve" id="sugar_g_per_serve">
                                <input type="hidden" name="sodium_mg_per_serve" id="sodium_mg_per_serve">
                                <input type="hidden" name="batch_after_waste_g" id="batch_after_waste_g">

                                <div class="text-dark-mud">
                                    <strong>Servings Per Package:
                                        <span class="serv_per_package_text"> {{ old('serv_per_package', $product->serv_per_package) }} </span>
                                    </strong>
                                </div>
                                <div class="text-dark-mud">
                                    <strong>Serving Size:
                                        <span class="serv_size_g_text"> {{ old('serv_size_g', $product->serv_size_g) }} </span>
                                    </strong>
                                </div>

                                <table class="nutrition_display_table mt-4">
                                    <thead>
                                        <tr>
                                            <th></th>
                                            <th class="text-primary-dark-mud"><strong>Per Serve</strong></th>
                                            <th class="text-primary-dark-mud"><strong>Per 100g</strong></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td class="text-primary-dark-mud">Energy</td>
                                            <td class="text-primary-dark-mud">
                                                <div class="nutrition_display_value">
                                                    <span class="nutri_value" id="energy_per_serving">
                                                        {{ $product->serv_per_package > 0 ? number_format($product->energy_kj / $product->serv_per_package, 1) : '0.0' }}
                                                    </span>
                                                    <span class="nutri_unit">kJ</span>
                                                </div>
                                            </td>
                                            <td class="text-primary-dark-mud">
                                                <div class="nutrition_display_value">
                                                    <span class="nutri_value" id="energy_per_100g">
                                                        {{ $product->total_weight_g > 0 ? number_format($product->energy_kj / $product->total_weight_g * 100, 1) : '0.0' }}
                                                    </span>
                                                    <span class="nutri_unit">kJ</span>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="text-primary-dark-mud">Protein</td>
                                            <td class="text-primary-dark-mud">
                                                <div class="nutrition_display_value">
                                                    <span class="nutri_value" id="protein_per_serving">
                                                        {{ $product->serv_per_package > 0 ? number_format($product->protein_g / $product->serv_per_package, 1) : '0.0' }}
                                                    </span>
                                                    <span class="nutri_unit">g</span>
                                                </div>
                                            </td>
                                            <td class="text-primary-dark-mud">
                                                <div class="nutrition_display_value">
                                                    <span class="nutri_value" id="protein_per_100g">
                                                        {{ $product->total_weight_g > 0 ? number_format($product->protein_g / $product->total_weight_g * 100, 1) : '0.0' }}
                                                    </span>
                                                    <span class="nutri_unit">g</span>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="text-primary-dark-mud">Fat, total</td>
                                            <td class="text-primary-dark-mud">
                                                <div class="nutrition_display_value">
                                                    <span class="nutri_value" id="fat_total_per_serving">
                                                        {{ $product->serv_per_package > 0 ? number_format($product->fat_total_g / $product->serv_per_package, 1) : '0.0' }}
                                                    </span>
                                                    <span class="nutri_unit">g</span>
                                                </div>
                                            </td>
                                            <td class="text-primary-dark-mud">
                                                <div class="nutrition_display_value">
                                                    <span class="nutri_value" id="fat_total_per_100g">
                                                        {{ $product->total_weight_g > 0 ? number_format($product->fat_total_g / $product->total_weight_g * 100, 1) : '0.0' }}
                                                    </span>
                                                    <span class="nutri_unit">g</span>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="text-primary-dark-mud">Saturated</td>
                                            <td class="text-primary-dark-mud">
                                                <div class="nutrition_display_value">
                                                    <span class="nutri_value" id="fat_saturated_per_serving">
                                                        {{ $product->serv_per_package > 0 ? number_format($product->fat_saturated_g / $product->serv_per_package, 1) : '0.0' }}
                                                    </span>
                                                    <span class="nutri_unit">g</span>
                                                </div>
                                            </td>
                                            <td class="text-primary-dark-mud">
                                                <div class="nutrition_display_value">
                                                    <span class="nutri_value" id="fat_saturated_per_100g">
                                                        {{ $product->total_weight_g > 0 ? number_format($product->fat_saturated_g / $product->total_weight_g * 100, 1) : '0.0' }}
                                                    </span>
                                                    <span class="nutri_unit">g</span>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="text-primary-dark-mud">Carbohydrate</td>
                                            <td class="text-primary-dark-mud">
                                                <div class="nutrition_display_value">
                                                    <span class="nutri_value" id="carbohydrate_per_serving">
                                                        {{ $product->serv_per_package > 0 ? number_format($product->carbohydrate_g / $product->serv_per_package, 1) : '0.0' }}
                                                    </span>
                                                    <span class="nutri_unit">g</span>
                                                </div>
                                            </td>
                                            <td class="text-primary-dark-mud">
                                                <div class="nutrition_display_value">
                                                    <span class="nutri_value" id="carbohydrate_per_100g">
                                                        {{ $product->total_weight_g > 0 ? number_format($product->carbohydrate_g / $product->total_weight_g * 100, 1) : '0.0' }}
                                                    </span>
                                                    <span class="nutri_unit">g</span>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="text-primary-dark-mud">Sugars</td>
                                            <td class="text-primary-dark-mud">
                                                <div class="nutrition_display_value">
                                                    <span class="nutri_value" id="sugars_per_serving">
                                                        {{ $product->serv_per_package > 0 ? number_format($product->sugars_g / $product->serv_per_package, 1) : '0.0' }}
                                                    </span>
                                                    <span class="nutri_unit">g</span>
                                                </div>
                                            </td>
                                            <td class="text-primary-dark-mud">
                                                <div class="nutrition_display_value">
                                                    <span class="nutri_value" id="sugars_per_100g">
                                                        {{ $product->total_weight_g > 0 ? number_format($product->sugars_g / $product->total_weight_g * 100, 1) : '0.0' }}
                                                    </span>
                                                    <span class="nutri_unit">g</span>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="text-primary-dark-mud">Sodium</td>
                                            <td class="text-primary-dark-mud">
                                                <div class="nutrition_display_value">
                                                    <span class="nutri_value" id="sodium_per_serving">
                                                        {{ $product->serv_per_package > 0 ? number_format($product->sodium_mg / $product->serv_per_package, 1) : '0.0' }}
                                                    </span>
                                                    <span class="nutri_unit">mg</span>
                                                </div>
                                            </td>
                                            <td class="text-primary-dark-mud">
                                                <div class="nutrition_display_value">
                                                    <span class="nutri_value" id="sodium_per_100g">
                                                        {{ $product->total_weight_g > 0 ? number_format($product->sodium_mg / $product->total_weight_g * 100, 1) : '0.0' }}
                                                    </span>
                                                    <span class="nutri_unit">mg</span>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-2 col-md-2 col-sm-12">
        <div class="button-row grid-btn">
            <a href="{{ route('products.edit', ['product' => $product->id, 'step' => 1]) }}"
                class="btn btn-secondary-blue mb-0 me-1 js-btn-previous" title="Previous">Back</a>
            <button class="btn btn-secondary-blue mb-0 me-1 js-btn-save" type="button" title="Save">Save</button>
            <button class="btn btn-secondary-blue mb-0 js-btn-next" type="button" title="Next">Next</button>
        </div>
        <div class="mt-4">
            <x-product-gridcard :product="$product" />
            <x-recipe-details :product="$product" />
            <x-tags-card :product="$product" />
            <x-product-weight :product="$product" />
        </div>
    </div>
    </div>
</form>
<div class="row mt-3">
    <div class="col-lg-10 col-md-10 col-sm-12 col-12">
        <!-- <div class="nutrition_table_area">
            <div class="nutrition_table_button mt-3 text-left cursor-pointer">
                <label class="me-2 nutrition_analysis_title form-label">Nutritional and Costing Analysis</label>
                <span class="material-symbols-outlined">keyboard_arrow_down</span>
            </div>
            <div class="nutrition_table_section responsiveness mt-4">
            </div>
        </div> -->
        <div id="costing_component_container"></div>
        <div id="analysis_component_container"></div>
    </div>
</div>
