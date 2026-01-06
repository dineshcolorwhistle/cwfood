<div class="oven_table_wrapper card p-3 rounded-2 box-shadow pt-4 mb-3">
    <h5 class="oven_table_title text-primary-orange">Allergens Summary</h5>
    <div class="oven_table_container">
        <table class="table ingredient_nutri_table">
            <thead>
                <tr>
                    <th width="30%" class="align-bottom fw-bold">Ingredient Name</th>
                    <th width="10%" class="align-bottom fw-bold text-end" >Weight<br>(g)</th>
                    <th width="10%" class="align-bottom fw-bold text-end">Mix<br>(%)</th>
                    <th width="10%" class="align-bottom fw-bold">Component</th>
                    <th width="10%" class="align-bottom fw-bold">Allergens</th>
                    <th width="30%" class="align-bottom fw-bold">Ingredients</th>
                </tr>
            </thead>
            <tbody>
                @php 
                    $weight_total = 0;
                    if(is_array($prodIngs)){
                    }else{
                        $prodIngs = $prodIngs->toArray();
                    }
                    usort($prodIngs, function ($a, $b) {
                        return $b['quantity_weight'] <=> $a['quantity_weight'];
                    });
                @endphp
                @foreach($prodIngs as $index => $prod_ing)
                @php 
                    if (in_array($prod_ing['units_g_ml'], ['kg', 'L', 'l'])) {
                        $temp = $prod_ing['quantity_weight'] * 1000;
                        $prod_ing['quantity_weight'] = round((float) $temp, 2);;   
                    }
                    $weight_total += $prod_ing['quantity_weight'];
                @endphp
                <tr @if($loop->last) class="total-row" @endif>
                    <td class="align-middle text-primary-dark-mud">
                        <div class="ingredient_nutri_table_image">
                            <div class="ing_info_area align-middle">
                                <div class="ing_name_area">
                                    {{ $prod_ing['ing_name'] }}
                                </div>
                            </div>
                        </div>
                    </td>
                    <td class="align-middle text-primary-dark-mud text-end quantity-weight">
                        {{ number_format($prod_ing['quantity_weight'], 2) }}
                    </td>
                    <td class="align-middle text-primary-dark-mud text-end quantity-weight">
                        {{ number_format($prod_ing['mix_percent'], 2) }}
                    </td>
                    <td class="align-middle text-primary-dark-mud">
                        {{ $prod_ing['component'] }}
                    </td>
                    <td class="align-middle text-primary-dark-mud">
                        {{ $prod_ing['allergens'] }}
                    </td>
                    <td class="align-middle text-primary-dark-mud">
                        {{ $prod_ing['peel_name'] }}
                    </td>
                </tr>
                @endforeach

                <tr class="total-row">
                    <td class="fw-bold">Total</td>
                    <td class="text-end fw-bold">{{number_format($weight_total, 0)}}</td>
                    <td class="text-end fw-bold">100</td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
            </tbody>

        </table>
    </div>
</div>