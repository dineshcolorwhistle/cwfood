<div class="row">
        <div class="col-lg-10 col-md-10 col-12 col-sm-12 mt-4 mt-sm-0">
            <div class="row mt-3">
                <div class="col-lg-6 col-md-6 col-12 col-sm-12 mt-4 mt-sm-0">
                    <div class="input-group input-group-dynamic flex-column">
                        <label class="form-label">Country of Origin</label>
                        <select name="ing_country" id="ing_country" class="form-control-select mb-4">
                            <option disabled {{ !isset($details['country_of_origin']) || $details['country_of_origin'] == null ? 'selected' : '' }}>Select Country of Origin</option>
                            @foreach($country as $coun)
                            <option value="{{$coun['COID']}}" 
                                    {{ (isset($details['country_of_origin']) && $details['country_of_origin'] == $coun['COID']) ? 'selected' : '' }}>
                                {{$coun['full_name']}}
                            </option>
                            @endforeach
                        </select>

                        <div class="mb-4">
                            <label class="form-label">Australian Percent</label>
                            <div class="row">
                                <div class="col-lg-11 col-md-11 col-sm-11 col-11">
                                    <input type="text" name="ing_aus_per" id="ing_aus_per" step="0.1" min="0" 
                                           class="form-control ph-blue text-end" placeholder="0" 
                                           value="{{ isset($details['australian_percent']) ? ($details['australian_percent'] * 100) : '' }}">
                                </div>
                                <div class="col-lg-1 col-md-1 col-sm-1 col-1 p-0">
                                    <p class="mt-2">%</p>
                                </div>
                            </div>
                        </div>

                        <div class="input-group input-group-dynamic flex-column mb-4">
                            <label class="form-label">Is liquid</label>
                            <select name="ing_spec_unit" id="ing_spec_unit" class="form-control-select">
                                <option disabled {{ !isset($details['purchase_units']) || $details['purchase_units'] == null ? 'selected' : '' }}>Select Options</option>
                                <option value="Yes" {{ (isset($details['purchase_units']) && $details['purchase_units'] == 'Yes') ? 'selected' : '' }}>Yes</option>
                                <option value="No" {{ (isset($details['purchase_units']) && $details['purchase_units'] == 'No') ? 'selected' : '' }}>No</option>
                            </select>
                        </div>
                        
                        <div class="input-group input-group-dynamic mb-4">
                            <label class="form-label">Ingredient List</label>
                            <textarea class="form-control ph-blue" name="ing_ing_list" id="ing_ing_list">{{ isset($details['ingredients_list_supplier']) ? $details['ingredients_list_supplier'] : '' }}</textarea>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-6 col-md-6 col-12 col-sm-12 mt-4 mt-sm-0">
                    <div class="input-group input-group-dynamic flex-column mb-4">
                        <label class="form-label">Allergens</label>
                        @php
                            $allrArray = [];
                            if (isset($details['allergens']) && $details['allergens']) {
                                $allrArray = explode(',', $details['allergens']);
                                $allrArray = array_map(fn($v) => ucfirst(trim($v)), $allrArray);
                            }
                            $selectedAllergens = array_intersect($allrArray, array_column($allergen, 'name'));
                            $remainingAllergens = array_diff(array_column($allergen, 'name'), $selectedAllergens);
                            $orderedAllergens = array_merge($selectedAllergens, $remainingAllergens);
                        @endphp
                        <select name="ing_allergen[]" id="ing_allergen" class="form-control-select fa-basic-multiple" multiple>
                            @foreach($orderedAllergens as $allerName)
                                @php
                                    $selected = in_array($allerName, $selectedAllergens);
                                @endphp
                                <option value="{{$allerName}}" {{ $selected ? 'selected' : '' }}>{{$allerName}}</option>
                            @endforeach
                            @foreach($allergen as $aller)
                                @if(!in_array($aller['name'], $orderedAllergens))
                                    <option value="{{$aller['name']}}">{{$aller['name']}}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            
            <label class="form-label mt-2">Raw Material Purchase Price</label>
            <table class="table table-borderless responsiveness input-table">
                <tr>
                    <th class="primary-text-dark fw-bold text-end" width="20%">Purchase Price</th>
                    <th class="primary-text-dark fw-bold text-end" width="20%">Purchase Volume</th>
                    <th class="primary-text-dark fw-bold" width="20%">Purchase Quantity</th>
                    <th class="primary-text-dark fw-bold text-end" width="20%">Specific Gravity</th>
                    <th class="primary-text-dark fw-bold text-end" width="20%">Price per kg</th>
                </tr>
                <tr>
                    <td>
                        <div class="input-group input-group-dynamic">
                            <input type="text" name="ing_total_price" id="ing_total_price" step="0.1" min="0" 
                                   class="form-control ph-blue text-end" placeholder="0"
                                   value="{{ isset($details['price_per_item']) ? $details['price_per_item'] : '' }}">
                        </div>
                    </td>
                    <td>
                        <div class="input-group input-group-dynamic table-active-input">
                            <input type="text" name="ing_quantity" id="ing_quantity" step="0.1" min="0" 
                                   class="form-control unit_weight_input text-end" placeholder="0"
                                   value="{{ isset($details['units_per_item']) ? $details['units_per_item'] : '' }}">
                        </div>
                    </td>
                    <td>
                        <div class="input-group input-group-dynamic">
                            @php
                                $unit = strtolower($details['ingredient_units'] ?? '');
                            @endphp

                            <select name="ing_quantity_unit" id="ing_quantity_unit" class="form-select">
                                <option disabled selected>Select Unit</option>
                                <option value="g"  {{ $unit === 'g' ? 'selected' : '' }}>g</option>
                                <option value="kg" {{ $unit === 'kg' ? 'selected' : '' }}>kg</option>
                                <option value="ml" {{ $unit === 'ml' ? 'selected' : '' }}>mL</option>
                                <option value="l"  {{ $unit === 'l' ? 'selected' : '' }}>L</option>
                            </select>

                        </div>
                    </td>
                    <td>
                        <div class="input-group input-group-dynamic table-active-readonly">
                            <input type="text" name="ing_spec_gravity" id="ing_spec_gravity" step="0.1" min="0" 
                                   class="form-control unit_weight_input text-end" placeholder="0" readonly
                                   value="{{ isset($details['specific_gravity']) ? $details['specific_gravity'] : '' }}">
                        </div>
                    </td>
                    <td>
                        <div class="input-group input-group-dynamic table-active-readonly">
                            <input type="hidden" name="ing_unit_price" id="ing_unit_price" step="0.1" min="0" 
                                   class="form-control unit_weight_input text-end" placeholder="0">
                            <input type="text" name="ing_unit_kg_price" id="ing_unit_kg_price" step="0.1" min="0" 
                                   class="form-control unit_weight_input text-end" placeholder="0" readonly
                                   value="{{ isset($details['price_per_kg_l']) ? $details['price_per_kg_l'] : '' }}">
                        </div>
                    </td>
                </tr>
            </table>
            
            <label class="form-label mt-2">Nutritional Specification per 100g</label>
            <table class="table table-borderless responsiveness input-table">
                <tr>
                    <th class="primary-text-dark fw-bold text-end" width="14%">Energy (kJ)</th>
                    <th class="primary-text-dark fw-bold text-end" width="14%">Protein (g)</th>
                    <th class="primary-text-dark fw-bold text-end" width="14%">Total Fat (g)</th>
                    <th class="primary-text-dark fw-bold text-end" width="14%">Saturated Fat (g)</th>
                    <th class="primary-text-dark fw-bold text-end" width="14%">Available Carb (g)</th>
                    <th class="primary-text-dark fw-bold text-end" width="15%">Total Sugar (g)</th>
                    <th class="primary-text-dark fw-bold text-end" width="15%">Sodium (mg)</th>
                </tr>
                <tr>
                    <td>
                        <div class="input-group input-group-dynamic table-active-input">
                            <input type="text" name="ing_energy" id="ing_energy" step="0.1" min="0" 
                                   class="form-control unit_weight_input text-end" placeholder="0"
                                   value="{{ isset($details['energy_kj']) ? $details['energy_kj'] : '' }}">
                        </div>
                    </td>
                    <td>
                        <div class="input-group input-group-dynamic table-active-input">
                            <input type="text" name="ing_protein" id="ing_protein" step="0.1" min="0" 
                                   class="form-control ph-blue text-end" placeholder="0"
                                   value="{{ isset($details['protein_g']) ? $details['protein_g'] : '' }}">
                        </div>
                    </td>
                    <td>
                        <div class="input-group input-group-dynamic table-active-input">
                            <input type="text" name="ing_total_fat" id="ing_total_fat" step="0.1" min="0" 
                                   class="form-control ph-blue text-end" placeholder="0"
                                   value="{{ isset($details['fat_total_g']) ? $details['fat_total_g'] : '' }}">
                        </div>
                    </td>
                    <td>
                        <div class="input-group input-group-dynamic table-active-input">
                            <input type="text" name="ing_saturated_fat" id="ing_saturated_fat" step="0.1" min="0" 
                                   class="form-control ph-blue text-end" placeholder="0"
                                   value="{{ isset($details['fat_saturated_g']) ? $details['fat_saturated_g'] : '' }}">
                        </div>
                    </td>
                    <td>
                        <div class="input-group input-group-dynamic table-active-input">
                            <input type="text" name="ing_avail_corb" id="ing_avail_corb" step="0.1" min="0" 
                                   class="form-control ph-blue text-end" placeholder="0"
                                   value="{{ isset($details['carbohydrate_g']) ? $details['carbohydrate_g'] : '' }}">
                        </div>
                    </td>
                    <td>
                        <div class="input-group input-group-dynamic table-active-input">
                            <input type="text" name="ing_total_sugar" id="ing_total_sugar" step="0.1" min="0" 
                                   class="form-control ph-blue text-end" placeholder="0"
                                   value="{{ isset($details['sugars_g']) ? $details['sugars_g'] : '' }}">
                        </div>
                    </td>
                    <td>
                        <div class="input-group input-group-dynamic table-active-input">
                            <input type="text" name="ing_sodium" id="ing_sodium" step="0.1" min="0" 
                                   class="form-control ph-blue text-end" placeholder="0"
                                   value="{{ isset($details['sodium_mg']) ? $details['sodium_mg'] : '' }}">
                        </div>
                    </td>
                </tr>
            </table>

            <div class="input-group input-group-dynamic mb-4">
                <label class="form-label">Shelf Life and storage instructions</label>
                <textarea class="form-control ph-blue" name="ing_shelf" id="ing_shelf" 
                          placeholder="Type shelf life and storage as you want it to appear on label">{{ isset($details['shelf_life']) ? $details['shelf_life'] : '' }}</textarea>
            </div>

            <div class="row mt-5">
                <label class="form-label">Suitability to make certain claims</label>
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
                                $rm = "rm_{$pr_label}_yn";
                                $rm1 = "rm_{$pr_label}_validated";
                                $rm2 = "rm_{$pr_label}_certification_yn";
                            @endphp
                            <tr>
                                <td class="primary-text-dark">{{$suits}}</td>
                                <td>
                                    <select class="suit_section form-select suitable_select" name="rm_{{$pr_label}}_yn">
                                        <option value="Yes" {{ ($prod_labels && $prod_labels->$rm == "Yes") ? 'selected' : '' }}>Yes</option>
                                        <option value="No" {{ ($prod_labels && $prod_labels->$rm == "No") ? 'selected' : '' }}>No</option>
                                        <option value="Unknown" {{ ($prod_labels && $prod_labels->$rm == "Unknown") ? 'selected' : '' }}>Unknown</option>
                                    </select>
                                </td>
                                <td>
                                    <select class="suit_section form-select" name="rm_{{$pr_label}}_validated">
                                        <option value="Based on ingredients" {{ ($prod_labels && $prod_labels->$rm1 == "Based on ingredients") ? 'selected' : '' }}>Based on ingredients</option>
                                        <option value="Through certification" {{ ($prod_labels && $prod_labels->$rm1 == "Through certification") ? 'selected' : '' }}>Through certification</option>
                                        <option value="na" {{ ($prod_labels && $prod_labels->$rm1 == "na") ? 'selected' : '' }}>na</option>
                                    </select>
                                </td>
                                <td>
                                    <select class="suit_section form-select" name="rm_{{$pr_label}}_certification_yn">
                                        <option value="Yes" {{ ($prod_labels && $prod_labels->$rm2 == "Yes") ? 'selected' : '' }}>Yes</option>
                                        <option value="No" {{ ($prod_labels && $prod_labels->$rm2 == "No") ? 'selected' : '' }}>No</option>
                                        <option value="Unknown" {{ ($prod_labels && $prod_labels->$rm2 == "Unknown") ? 'selected' : '' }}>Unknown</option>
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
                                <input type="text" name="rm_supplied_shelf_life_num" id="rm_supplied_shelf_life_num" class="form-control text-right numeric-input" value="{{ $prod_labels ? $prod_labels->rm_supplied_shelf_life_num : '' }}" />
                            </td>
                            <td>
                                <select class="suit_section form-select" name="rm_supplied_shelf_life_units">
                                    <option value="Days" {{ ($prod_labels && $prod_labels->rm_supplied_shelf_life_units == "Days") ? 'selected' : '' }}>Days</option>
                                    <option value="Weeks" {{ ($prod_labels && $prod_labels->rm_supplied_shelf_life_units == "Weeks") ? 'selected' : '' }}>Weeks</option>
                                    <option value="Months" {{ ($prod_labels && $prod_labels->rm_supplied_shelf_life_units == "Months") ? 'selected' : '' }}>Months</option>
                                    <option value="Years" {{ ($prod_labels && $prod_labels->rm_supplied_shelf_life_units == "Years") ? 'selected' : '' }}>Years</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td class="primary-text-dark">Temperature controlled during storage:</td>
                            <td>
                                <select class="suit_section form-select" name="rm_suppied_temp_control_storage_num">
                                    <option value="Yes" {{ ($prod_labels && $prod_labels->rm_suppied_temp_control_storage_num == "Yes") ? 'selected' : '' }}>Yes</option>
                                    <option value="No" {{ ($prod_labels && $prod_labels->rm_suppied_temp_control_storage_num == "No") ? 'selected' : '' }}>No</option>
                                    <option value="Unknown" {{ ($prod_labels && $prod_labels->rm_suppied_temp_control_storage_num == "Unknown") ? 'selected' : '' }}>Unknown</option>
                                </select>
                            </td>
                            <td>
                                <select class="suit_section form-select" name="rm_suppied_temp_control_storage_degrees">
                                    <option value="≤ 20°C (Ambient)" {{ ($prod_labels && $prod_labels->rm_suppied_temp_control_storage_degrees == "≤ 20°C (Ambient)") ? 'selected' : '' }}>≤ 20°C (Ambient)</option>
                                    <option value="≤ 5°C (Refridgerated)" {{ ($prod_labels && $prod_labels->rm_suppied_temp_control_storage_degrees == "≤ 5°C (Refridgerated)") ? 'selected' : '' }}>≤ 5°C (Refridgerated)</option>
                                    <option value="≤ -15 (Frozen)" {{ ($prod_labels && $prod_labels->rm_suppied_temp_control_storage_degrees == "≤ -15 (Frozen)") ? 'selected' : '' }}>≤ -15 (Frozen)</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td class="primary-text-dark">Temperature controlled during transport:</td>
                            <td>
                                <select class="suit_section form-select" name="rm_supplied_temp_control_transport_yn">
                                    <option value="Yes" {{ ($prod_labels && $prod_labels->rm_supplied_temp_control_transport_yn == "Yes") ? 'selected' : '' }}>Yes</option>
                                    <option value="No" {{ ($prod_labels && $prod_labels->rm_supplied_temp_control_transport_yn == "No") ? 'selected' : '' }}>No</option>
                                    <option value="Unknown" {{ ($prod_labels && $prod_labels->rm_supplied_temp_control_transport_yn == "Unknown") ? 'selected' : '' }}>Unknown</option>
                                </select>
                            </td>
                            <td>
                                <select class="suit_section form-select" name="rm_supplied_temp_control_transport_degrees">
                                    <option value="≤ 20°C (Ambient)" {{ ($prod_labels && $prod_labels->rm_supplied_temp_control_transport_degrees == "≤ 20°C (Ambient)") ? 'selected' : '' }}>≤ 20°C (Ambient)</option>
                                    <option value="≤ 5°C (Refridgerated)" {{ ($prod_labels && $prod_labels->rm_supplied_temp_control_transport_degrees == "≤ 5°C (Refridgerated)") ? 'selected' : '' }}>≤ 5°C (Refridgerated)</option>
                                    <option value="≤ -15 (Frozen)" {{ ($prod_labels && $prod_labels->rm_supplied_temp_control_transport_degrees == "≤ -15 (Frozen)") ? 'selected' : '' }}>≤ -15 (Frozen)</option>
                                </select>
                            </td>
                        </tr>
                        <tr style="height: 50px;">
                            <td class="primary-text-dark fw-bold">Product - Once in Use (resealable pack or bulk container)</td><td></td><td></td>
                        </tr>
                        <tr>
                            <td class="primary-text-dark">Shelf Life:</td>
                            <td>
                                <input type="text" name="rm_inuse_shelf_life_num" id="rm_inuse_shelf_life_num" class="form-control text-right numeric-input" value="{{ $prod_labels ? $prod_labels->rm_inuse_shelf_life_num : '' }}" />
                            </td>
                            <td>
                                <select class="suit_section form-select" name="rm_inuse_shelf_life_units">
                                    <option value="Days" {{ ($prod_labels && $prod_labels->rm_inuse_shelf_life_units == "Days") ? 'selected' : '' }}>Days</option>
                                    <option value="Weeks" {{ ($prod_labels && $prod_labels->rm_inuse_shelf_life_units == "Weeks") ? 'selected' : '' }}>Weeks</option>
                                    <option value="Months" {{ ($prod_labels && $prod_labels->rm_inuse_shelf_life_units == "Months") ? 'selected' : '' }}>Months</option>
                                    <option value="Years" {{ ($prod_labels && $prod_labels->rm_inuse_shelf_life_units == "Years") ? 'selected' : '' }}>Years</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td class="primary-text-dark">Temperature controlled during storage:</td>
                            <td>
                                <select class="suit_section form-select" name="rm_inuse_temp_control_storage_num">
                                    <option value="Yes" {{ ($prod_labels && $prod_labels->rm_inuse_temp_control_storage_num == "Yes") ? 'selected' : '' }}>Yes</option>
                                    <option value="No" {{ ($prod_labels && $prod_labels->rm_inuse_temp_control_storage_num == "No") ? 'selected' : '' }}>No</option>
                                    <option value="Unknown" {{ ($prod_labels && $prod_labels->rm_inuse_temp_control_storage_num == "Unknown") ? 'selected' : '' }}>Unknown</option>
                                </select>
                            </td>
                            <td>
                                <select class="suit_section form-select" name="rm_inuse_temp_control_storage_degrees">
                                    <option value="≤ 20°C (Ambient)" {{ ($prod_labels && $prod_labels->rm_inuse_temp_control_storage_degrees == "≤ 20°C (Ambient)") ? 'selected' : '' }}>≤ 20°C (Ambient)</option>
                                    <option value="≤ 5°C (Refridgerated)" {{ ($prod_labels && $prod_labels->rm_inuse_temp_control_storage_degrees == "≤ 5°C (Refridgerated)") ? 'selected' : '' }}>≤ 5°C (Refridgerated)</option>
                                    <option value="≤ -15 (Frozen)" {{ ($prod_labels && $prod_labels->rm_inuse_temp_control_storage_degrees == "≤ -15 (Frozen)") ? 'selected' : '' }}>≤ -15 (Frozen)</option>
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
                            <td class="primary-text-dark">Specify any other storage requirements:</td>
                            <td>
                                <select class="suit_section form-select" name="rm_storage_requirement">
                                    <option value="Once opened, store in an airtight container in a cool, dry place (≤ 20°C)." {{ ($prod_labels && $prod_labels->rm_storage_requirement == "Once opened, store in an airtight container in a cool, dry place (≤ 20°C).") ? 'selected' : '' }}>Once opened, store in an airtight container in a cool, dry place (≤ 20°C).</option>
                                    <option value="Once opened, store in an airtight container and keep refrigerated (≤ 5°C)." {{ ($prod_labels && $prod_labels->rm_storage_requirement == "Once opened, store in an airtight container and keep refrigerated (≤ 5°C).") ? 'selected' : '' }}>Once opened, store in an airtight container and keep refrigerated (≤ 5°C).</option>
                                    <option value="Once opened, store in an airtight container and keep frozen (≤ -15°C)." {{ ($prod_labels && $prod_labels->rm_storage_requirement == "Once opened, store in an airtight container and keep frozen (≤ -15°C).") ? 'selected' : '' }}>Once opened, store in an airtight container and keep frozen (≤ -15°C).</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td class="primary-text-dark">Intended use:</td>
                            <td>
                                <select class="suit_section form-select" name="rm_indended_use">
                                    <option value="This product is intended for general consumption" {{ ($prod_labels && $prod_labels->rm_indended_use == "This product is intended for general consumption") ? 'selected' : '' }}>This product is intended for general consumption</option>
                                    <option value="For general consumption" {{ ($prod_labels && $prod_labels->rm_indended_use == "For general consumption") ? 'selected' : '' }}>For general consumption</option>
                                    <option value="Other" {{ ($prod_labels && $prod_labels->rm_indended_use == "Other") ? 'selected' : '' }}>Other</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td class="primary-text-dark">Specify type of date mark to be used:</td>
                            <td>
                                <select class="suit_section form-select" name="rm_date_mark">
                                    <option value="Use Before" {{ ($prod_labels && $prod_labels->rm_date_mark == "Use Before") ? 'selected' : '' }}>Use Before</option>
                                    <option value="Best Before" {{ ($prod_labels && $prod_labels->rm_date_mark == "Best Before") ? 'selected' : '' }}>Best Before</option>
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
                                    <option value="Yes" {{ ($prod_labels && $prod_labels->rm_hazard_yn == "Yes") ? 'selected' : '' }}>Yes</option>
                                    <option value="No" {{ ($prod_labels && $prod_labels->rm_hazard_yn == "No") ? 'selected' : '' }}>No</option>
                                    <option value="Unknown" {{ ($prod_labels && $prod_labels->rm_hazard_yn == "Unknown") ? 'selected' : '' }}>Unknown</option>
                                </select>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <div id="custom-text">
                    <p>Potentially hazardous food means food that has to be kept at certain temperatures to minimise the growth of any pathogenic microorganisms that may be present in the food or to prevent the formation of toxins in the food. (Source: STANDARD 3.2.2 FOOD SAFETY PRACTICES AND GENERAL REQUIREMENTS)</p>
                </div>
            </div>
        </div>
        
        <div class="col-lg-2 col-md-2 col-12 col-sm-12 mt-sm-0">
            <div class="mt-4">
                <x-raw-material-details :ingredient="$details" />
                @if($hasIngredient)
                    <x-raw-material-info/>
                @endif
            </div>
        </div>
    </div>

