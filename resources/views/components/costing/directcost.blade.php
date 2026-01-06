<div class="card price_card mb-3 p-3 rounded-2 box-shadow">
    <div class="card-body px-0 py-2">
        <h5 class="card-title text-primary-orange">Direct Costs</h5>
        <table class="table costing_table">
            <thead>
                <tr>
                    <td></td>
                    <td class="text-end fw-bold">$ / kg</td>
                    <td class="text-end fw-bold">$ / Unit</td>
                    <td class="text-end fw-bold">$ / Sell Unit</td>
                    <td class="text-end fw-bold">$ / Carton</td>
                    <td class="text-end fw-bold">$ / Pallet</td>
                    <td class="text-end fw-bold">$ / Batch</td>
                </tr>
            </thead>
            <tbody> 
                @foreach(['ingredient', 'packaging', 'machinery', 'labour', 'freight','total', 'contingency', 'total_direct'] as $costType)
                @php 
                    $bold_class="";
                    if(in_array($costType,['total','total_direct'])){
                        $bold_class = "fw-bold";
                    }
                @endphp
                <tr class="{{ $costType === 'freight' ? 'labour-row' : ($costType === 'contingency' ? 'contingency-row' : '') }}">
                    <td class="{{$bold_class}}">
                        {{ 
                            $costType === 'total_direct' ? 'Direct Cost' : 
                            ($costType === 'total' ? 'Total' : ucfirst($costType)) 
                        }}
                    </td>

                    <td class="text-end {{$bold_class}} ">{{ number_format($costingData[$costType]['per_kg'], 2) }}</td>
                    <td class="text-end {{$bold_class}} ">{{ number_format($costingData[$costType]['per_unit'], 2) }}</td>
                    <td class="text-end {{$bold_class}} ">{{ number_format($costingData[$costType]['per_sell_unit'], 2) }}</td>
                    <td class="text-end {{$bold_class}} ">{{ number_format($costingData[$costType]['per_carton'], 2) }}</td>
                    <td class="text-end {{$bold_class}} ">{{ $costingData[$costType]['per_pallet'] ? number_format($costingData[$costType]['per_pallet'], 2) : 'null' }}</td>
                    <td class="text-end {{$bold_class}} ">{{ number_format($costingData[$costType]['per_batch'], 2) }}</td>
                </tr>
                @endforeach
                <tr style="height: 40px;">
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td>Weight (nett g)</td>
                    <td class="text-end">1,000.0</td>
                    <td class="text-end">{{ number_format(old('weight_ind_unit_g', $product->weight_ind_unit_g), 1) }}</td>
                    <td class="text-end">{{ number_format(old('weight_retail_unit_g', $product->weight_retail_unit_g), 1) }}</td>
                    <td class="text-end">{{ number_format(old('weight_carton_g', $product->weight_carton_g), 1) }}</td>
                    <td class="text-end">{{ number_format(old('weight_pallet_g', $product->weight_pallet_g), 1) }}</td>
                    <td class="text-end">{{ number_format($weightTotal, 1) }}</td>
                </tr>
                <tr>
                    <td>Units</td>
                    <td class="text-end"></td>
                    <td class="text-end">1</td>
                    <td class="text-end">{{ number_format(old('count_ind_units_per_retail', $product->count_ind_units_per_retail), 1) }}</td>
                    <td class="text-end">{{ number_format(old('count_retail_units_per_carton', $product->count_retail_units_per_carton), 1) }}</td>
                    <td class="text-end">{{ number_format(old('count_cartons_per_pallet', $product->count_cartons_per_pallet), 1) }}</td>
                    <td class="text-end"></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>


<div class="card nutrition_card mb-3 p-3 rounded-2 box-shadow">
    <div class="card-body px-0 py-2">
        <h5 class="text-primary-orange">Margin Analysis</h5>
        <table class="table table-borderless tb-cost nutrition_table">
            <!-- Weight Section -->
            <tr>
                <th width="15%"></th>
                <th class="th-top-align text-end fw-bold text-dark-mud-sm" width="15%">$ / kg</th>
                <th class="th-top-align text-end fw-bold text-dark-mud-sm" width="15%">$ / Unit</th>
                <th class="th-top-align text-end fw-bold text-dark-mud-sm" width="15%">$ / Sell Unit</th>
                <th class="th-top-align text-end fw-bold text-dark-mud-sm" width="15%">$ / Carton</th>
                <th class="th-top-align text-end fw-bold text-dark-mud-sm" width="15%">$ / Pallet</th>
                <th class="th-top-align text-end fw-bold text-dark-mud-sm" width="15%">$ / Batch</th>
            </tr>
            <tr>
                <td class="align-middle">Direct Cost</td>
                <td class="align-middle">
                    <div class="input-group input-group-dynamic table-input-readonly">
                        <input type="text" name="weight_" id="weight_" step="0.1" min="0" class="text-end form-control ph-blue text-primary-dark-mud" value="{{ number_format($costingData[$costType]['per_kg'] ?? 0, 2) }}" placeholder="000.0" readonly>
                    </div>
                </td>
                <td>
                    <div class="input-group input-group-dynamic table-input-readonly">
                        <input type="text" name="weight_ind_unit_g" id="weight_ind_unit_g" step="0.1" min="0" class="text-end form-control ph-blue text-primary-dark-mud" value="{{ number_format($costingData[$costType]['per_unit'] ?? 0, 2) }}" placeholder="000.0" readonly>
                    </div>
                </td>
                <td>
                    <div class="input-group input-group-dynamic table-input-readonly">
                        <input type="text" name="weight_retail_unit_g" id="weight_retail_unit_g" step="0.1" min="0" class="text-end form-control ph-blue text-primary-dark-mud" value="{{ number_format($costingData[$costType]['per_sell_unit'] ?? 0, 2) }}" placeholder="000.0" readonly>
                    </div>
                </td>
                <td>
                    <div class="input-group input-group-dynamic table-input-readonly">
                        <input type="text" name="weight_carton_g" id="weight_carton_g" step="0.1" min="0" class="text-end form-control ph-blue text-primary-dark-mud" value="{{ number_format($costingData[$costType]['per_carton'] ?? 0, 2) }}" placeholder="000.0" readonly>
                    </div>
                </td>
                <td>
                    <div class="input-group input-group-dynamic table-input-readonly">
                        <input type="text" name="weight_pallet_g" id="weight_pallet_g" step="0.1" min="0" class="text-end form-control ph-blue text-primary-dark-mud" value="{{ number_format($costingData[$costType]['per_pallet'] ?? 0, 2) }}" placeholder="000.0" readonly>
                    </div>
                </td>
                <td class="align-middle">
                    <div class="input-group input-group-dynamic table-input-readonly">
                        <input type="text" name="weight_" id="weight_" step="0.1" min="0" class="text-end form-control ph-blue text-primary-dark-mud" value="{{ number_format($costingData[$costType]['per_batch'] ?? 0, 2) }}" placeholder="000.0" readonly>
                    </div>
                </td>
            </tr>

            <!-- Units Section -->
            <tr>
                <td class="align-middle">Margin</td>
                <td class="align-middle">
                    <div class="input-group input-group-dynamic table-input-readonly">
                        <input type="text" name="weight_" id="weight_" step="0.1" min="0" class="text-end form-control ph-blue text-primary-dark-mud" value="{{ number_format($costingData['margin']['per_kg'] ?? 0, 0) }}%" placeholder="000.0" readonly>
                    </div>
                </td>
                <td>
                    <div class="input-group input-group-dynamic table-input-readonly">
                        <input type="text" name="count_ind_units_per_retail" id="count_ind_units_per_retail" step="1" min="0" class="text-end form-control ph-blue text-primary-dark-mud" value="{{ number_format($costingData['margin']['per_unit'] ?? 0, 0) }}%" placeholder="000" readonly>
                    </div>
                </td>
                <td>
                    <div class="input-group input-group-dynamic table-input-readonly">
                        <input type="text" name="count_retail_units_per_carton" id="count_retail_units_per_carton" step="1" min="0" class="text-end form-control ph-blue text-primary-dark-mud" value="{{ number_format($costingData['margin']['per_sell_unit'] ?? 0, 0) }}%" placeholder="000" readonly>
                    </div>
                </td>
                <td>
                    <div class="input-group input-group-dynamic table-input-readonly">
                        <input type="text" name="count_cartons_per_pallet" id="count_cartons_per_pallet" step="1" min="0" class="text-end form-control ph-blue text-primary-dark-mud" value="{{ number_format($costingData['margin']['per_carton'] ?? 0, 0) }}%" placeholder="000" readonly>
                    </div>
                </td>
                <td class="align-middle">
                    <div class="input-group input-group-dynamic table-input-readonly">   
                        <input type="text" name="weight_" id="weight_" step="0.1" min="0" class="text-end form-control ph-blue text-primary-dark-mud" value="{{ number_format($costingData['margin']['per_pallet'] ?? 0, 0) }}%" placeholder="000.0" readonly>
                    </div>
                </td>
                <td class="align-middle">
                    <div class="input-group input-group-dynamic table-input-readonly">
                        <input type="text" name="weight_" id="weight_" step="0.1" min="0" class="text-end form-control ph-blue text-primary-dark-mud" value="{{ number_format($costingData['margin']['per_batch'] ?? 0, 0) }}%" placeholder="000.0" readonly>
                    </div>
                </td>
            </tr>

            <!-- Price Section -->
            <tr>
                <td class="align-middle">RRP</td>
                <td class="align-middle">
                    <div class="input-group input-group-dynamic table-input-readonly">
                        <input type="text" name="weight_" id="weight_" step="0.1" min="0" class="text-end form-control ph-blue text-primary-dark-mud" value="{{ number_format($costingData['rrp']['per_kg'] ?? 0, 2) }}" placeholder="000.0" readonly>
                    </div>
                </td>
                <td>
                    <div class="input-group input-group-dynamic table-input-readonly">
                        <input type="text" name="price_ind_unit" id="price_ind_unit" class="text-end form-control ph-blue text-primary-dark-mud" value="{{ old('price_ind_unit', $product->price_ind_unit) }}" placeholder="0.00" readonly>
                    </div>
                </td>
                <td>
                    <div class="input-group input-group-dynamic table-input-readonly">
                        <input type="text" name="price_retail_unit" id="price_retail_unit" step="0.01" min="0" class="text-end form-control ph-blue text-primary-dark-mud" value="{{ old('price_retail_unit', $product->price_retail_unit) }}" placeholder="0.00" readonly>
                    </div>
                </td>
                <td>
                    <div class="input-group input-group-dynamic table-input-readonly">
                        <input type="text" name="price_carton" id="price_carton" class="form-control ph-blue text-primary-dark-mud text-end" value="{{ old('price_carton', $product->price_carton) }}" placeholder="0.00" readonly>
                    </div>
                </td>
                <td>
                    <div class="input-group input-group-dynamic table-input-readonly">
                        <input type="text" name="price_pallet" id="price_pallet" class="form-control ph-blue text-primary-dark-mud text-end" value="{{ old('price_pallet', $product->price_pallet) }}" placeholder="0.00" readonly>
                    </div>
                </td>
                <td class="align-middle">
                    <div class="input-group input-group-dynamic table-input-readonly">
                        <input type="text" name="weight_" id="weight_" step="0.1" min="0" class="text-end form-control ph-blue text-primary-dark-mud" value="{{ number_format($costingData['rrp']['per_batch'] ?? 0, 2) }}" placeholder="000.0" readonly>
                    </div>
                </td>
            </tr>
        </table>
    </div>
</div>