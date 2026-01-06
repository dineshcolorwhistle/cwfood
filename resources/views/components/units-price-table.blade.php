<div class="card nutrition_card mb-3 p-3 rounded-2 box-shadow">
    <div class="card-body px-0 py-2">
        <h5 class="text-primary-orange">Units and Price</h5>
        <table class="table table-borderless nutrition_table">
            <!-- Weight Section -->
            <tr>
                <th width="15%"></th>
                <th class="th-top-align text-end fw-bold" width="15%">Ind Unit</th>
                <th class="th-top-align text-end fw-bold" width="15%">Sell Unit</th>
                <th class="th-top-align text-end fw-bold" width="15%">Carton</th>
                <th class="th-top-align text-end fw-bold" width="15%">Pallet</th>
            </tr>
            <tr>
                <td class="align-middle">Weight (g)</td>
                <td>
                    <div class="input-group input-group-dynamic table-input-readonly">
                        <input type="text" name="weight_ind_unit_g" id="weight_ind_unit_g" step="0.1" min="0" class="form-control ph-blue text-primary-dark-mud text-end" value="{{ old('weight_ind_unit_g', number_format($product->weight_ind_unit_g, 1)) }}" placeholder="000.0" readonly>
                    </div>
                </td>
                <td>
                    <div class="input-group input-group-dynamic table-input-readonly">
                        <input type="text" name="weight_retail_unit_g" id="weight_retail_unit_g" step="0.1" min="0" class="form-control ph-blue text-primary-dark-mud text-end" value="{{ old('weight_retail_unit_g', number_format($product->weight_retail_unit_g, 1)) }}" placeholder="000.0" readonly>
                    </div>
                </td>
                <td>
                    <div class="input-group input-group-dynamic table-input-readonly">
                        <input type="text" name="weight_carton_g" id="weight_carton_g" step="0.1" min="0" class="form-control ph-blue text-primary-dark-mud text-end" value="{{ old('weight_carton_g', number_format($product->weight_carton_g, 1)) }}" placeholder="000.0" readonly>
                    </div>
                </td>
                <td>
                    <div class="input-group input-group-dynamic table-input-readonly">
                        <input type="text" name="weight_pallet_g" id="weight_pallet_g" class="form-control ph-blue text-primary-dark-mud text-end" value="{{ old('weight_pallet_g', number_format($product->weight_pallet_g, 1)) }}" placeholder="000.0" readonly>
                    </div>
                </td>
            </tr>

            <!-- Units Section -->
            <tr>
                <td class="align-middle">Unit(#)</td>
                <td>&nbsp;</td>
                <td>
                    <div class="input-group input-group-dynamic table-input-readonly">
                        <input type="number" name="count_ind_units_per_retail" id="count_ind_units_per_retail" step="1" min="0" class="form-control ph-blue text-primary-dark-mud" value="{{ old('count_ind_units_per_retail', $product->count_ind_units_per_retail) }}" placeholder="000" readonly>
                    </div>
                </td>
                <td>
                    <div class="input-group input-group-dynamic table-input-readonly">
                        <input type="number" name="count_retail_units_per_carton" id="count_retail_units_per_carton" step="1" min="0" class="form-control ph-blue text-primary-dark-mud" value="{{ old('count_retail_units_per_carton', $product->count_retail_units_per_carton) }}" placeholder="000" readonly>
                    </div>
                </td>
                <td>
                    <div class="input-group input-group-dynamic table-input-readonly">
                        <input type="number" name="count_cartons_per_pallet" id="count_cartons_per_pallet" step="1" min="0" class="form-control ph-blue text-primary-dark-mud" value="{{ old('count_cartons_per_pallet', $product->count_cartons_per_pallet) }}" placeholder="000" readonly>
                    </div>
                </td>
            </tr>

            <!-- Price Section -->
            <tr>
                <td class="align-middle">RRP (AUD)</td>
                <td>
                    <div class="input-group input-group-dynamic table-input-readonly">
                        <input type="text" name="price_ind_unit" id="price_ind_unit" class="form-control ph-blue text-primary-dark-mud text-end" value="{{ old('price_ind_unit', number_format($product->price_ind_unit, 2)) }}" placeholder="0.00" readonly>
                    </div>
                </td>
                <td>
                    <div class="input-group input-group-dynamic table-input-readonly">
                        <input type="text" name="price_retail_unit" id="price_retail_unit" step="0.01" min="0" class="form-control ph-blue text-primary-dark-mud text-end" value="{{ old('price_retail_unit', number_format($product->price_retail_unit, 2)) }}" placeholder="0.00" readonly>
                    </div>
                </td>
                <td>
                    <div class="input-group input-group-dynamic table-input-readonly">
                        <input type="text" name="price_carton" id="price_carton" class="form-control ph-blue text-primary-dark-mud text-end" value="{{ old('price_carton', number_format($product->price_carton, 2)) }}" placeholder="0.00" readonly>
                    </div>
                </td>
                <td>
                    <div class="input-group input-group-dynamic table-input-readonly">
                        <input type="text" name="price_pallet" id="price_pallet" class="form-control ph-blue text-primary-dark-mud text-end" value="{{ old('price_pallet', number_format($product->price_pallet, 2)) }}" placeholder="0.00" readonly>
                    </div>
                </td>
            </tr>
        </table>
    </div>
</div>