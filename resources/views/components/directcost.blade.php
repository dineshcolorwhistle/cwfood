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
                    <td class="text-end {{$bold_class}} ">{{ $costingData[$costType]['per_kg'] ? number_format($costingData[$costType]['per_kg'], 2) : 'null' }}</td>
                    <td class="text-end {{$bold_class}} ">{{ $costingData[$costType]['per_unit'] ? number_format($costingData[$costType]['per_unit'], 2) : 'null' }}</td>
                    <td class="text-end {{$bold_class}} ">{{ $costingData[$costType]['per_sell_unit'] ? number_format($costingData[$costType]['per_sell_unit'], 2) : 'null' }}</td>
                    <td class="text-end {{$bold_class}} ">{{ $costingData[$costType]['per_carton'] ? number_format($costingData[$costType]['per_carton'], 2) : 'null' }}</td>
                    <td class="text-end {{$bold_class}} ">{{ $costingData[$costType]['per_pallet'] ? number_format($costingData[$costType]['per_pallet'], 2) : 'null' }}</td>
                    <td class="text-end {{$bold_class}} ">{{ $costingData[$costType]['per_batch'] ? number_format($costingData[$costType]['per_batch'], 2) : 'null' }}</td>
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
