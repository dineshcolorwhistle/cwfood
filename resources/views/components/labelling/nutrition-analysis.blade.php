<div class="price_card card mb-3 p-3 rounded-2 box-shadow">
    <div class="card-body px-0 py-2">
        <h5 class="text-primary-orange">Nutritional Analysis</h5>
        <table class="table table-borderless nutrition_table nutritional-analysis">
            <thead>
                <tr>
                    <th width="20%">Ingredient Name</th>
                    <th width="10%" class="text-end">Mix<br>(%)</th>
                    <th width="10%" class="text-end">Energy<br>(kJ)</th>
                    <th width="10%" class="text-end">Protein<br>(g)</th>
                    <th width="10%" class="text-end">Total Fat<br>(g)</th>
                    <th width="10%" class="text-end">Saturated Fat<br>(g)</th>
                    <th width="10%" class="text-end">Carbohydrate<br>(g)</th>
                    <th width="10%" class="text-end">Total Sugar<br>(g)</th>
                    <th width="10%" class="text-end">Sodium<br>(mg)</th>  
                    <th width="5%" class="text-end">Australia<br>(%)</th>              
                </tr>
            </thead>
            <tbody>
                @php $mix_total = 0; @endphp
                @foreach($prodIngredient['nutrition'] as $prod)
                @php $mix_total += number_format($prod['mix_percent'], 2); @endphp
                <tr @if ($loop->last) class="last-row" style="border-bottom: 1px solid #000;" @endif>
                    <td>{{$prod['name']}}</td>
                    <td class="text-end">{{number_format( round($prod['mix_percent']), 0) }}</td>
                    <td class="text-end">{{number_format($prod['energy_kj'], 0) }}</td>
                    <td class="text-end">{{number_format($prod['protein_g'], 1) }}</td>
                    <td class="text-end">{{number_format($prod['fat_total_g'], 1) }}</td>
                    <td class="text-end">{{number_format($prod['fat_saturated_g'], 1) }}</td>
                    <td class="text-end">{{number_format($prod['carbohydrate_g'], 1) }}</td>
                    <td class="text-end">{{number_format($prod['sugars_g'], 1) }}</td>
                    <td class="text-end">{{number_format($prod['sodium_mg'], 0) }}</td>
                    <td class="text-end">{{number_format(($prod['australian_percent'] *100), 0) }}</td>
                </tr>
                @endforeach

                <tr class="total-row" style="border-bottom: 1px solid #000;">
                    <td class="fw-bold">Nutrition per 100g</td>
                    <td class="text-end fw-bold">{{ number_format(round($mix_total), 2) }}</td>
                    <td class="text-end fw-bold">{{ number_format($prodIngredient['totals']['energy_kj'], 2) }}</td>
                    <td class="text-end fw-bold">{{ number_format($prodIngredient['totals']['protein_g'], 2) }}</td>
                    <td class="text-end fw-bold">{{ number_format($prodIngredient['totals']['fat_total_g'], 2) }}</td>
                    <td class="text-end fw-bold">{{ number_format($prodIngredient['totals']['fat_saturated_g'], 2) }}</td>
                    <td class="text-end fw-bold">{{ number_format($prodIngredient['totals']['carbohydrate_g'], 2) }}</td>
                    <td class="text-end fw-bold">{{ number_format($prodIngredient['totals']['sugars_g'], 2) }}</td>
                    <td class="text-end fw-bold">{{ number_format($prodIngredient['totals']['sodium_mg'], 2) }}</td>
                    <td class="text-end fw-bold">{{ number_format($prodIngredient['totals']['australian_percent'], 2) }}</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>