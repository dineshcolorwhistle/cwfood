<div class="price_card menu-bg mb-3 p-3 rounded-2 box-shadow">
    <div class="card-body px-0 py-2">
        <h5 class="text-primary-orange">Nett Weight (g)</h5>
        <table class="table table-borderless">
        <tbody>
            <tbody>
                @php
                    $weights = [
                        'Batch' => $product->batch_after_waste_g ?? 0,
                        'Unit' => $product->weight_ind_unit_g ?? 0,
                        'Sell Unit' => $product->weight_retail_unit_g ?? 0,
                        'Carton' => $product->weight_carton_g ?? 0,
                        'Pallet' => $product->weight_pallet_g ?? 0,
                    ];
                @endphp

                @foreach ($weights as $key => $value)
                    <tr>
                        <td>{{ $key }}</td>
                        <td class="text-end">{{ number_format($value, 1) }} g</td>
                    </tr>
                @endforeach
            </tbody>
    </table>
    </div>
</div>
