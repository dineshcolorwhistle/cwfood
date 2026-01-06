<style>
    div#custom-text p {font-size: 11px;color: #808080ab !important; margin:0 auto;}
</style>
<div class="card price_card mb-3 p-3 rounded-2 box-shadow">
    <div class="card-body px-0 py-2">
        <h5 class="text-primary-orange">Raw Material Costing</h5>
        <table class="table costing_table">
            <thead>
                <tr>
                    <td class="fw-bold" width="40%">Ingredient Name</td>
                    <td class="text-end fw-bold" width="15%">Cost <br>($/kg)</td>
                    <td class="text-end fw-bold" width="15%">Batch <br>(g)</td>
                    <td class="text-end fw-bold" width="15%">Mix<br>(%)</td>
                    <td class="text-end fw-bold" width="15%">Batch Cost <br>($)</td>
                </tr>
            </thead>  
            <tbody>
                @php 

                $loss_percent = (float) $nutrition['loss_percent'];
                $mix_total = 0;
                if($nutrition['totals']['amount'] > 0){
                    $costper_recipe = ($nutrition['totals']['amount'] / $nutrition['totals']['quantity']) * 1000;
                }else{
                    $costper_recipe = 0;
                }
                @endphp
                @foreach($nutrition['nutritionData'] as $nutrition_details)
                @php 
                    $mix_number = (float)$nutrition_details['mix_percent'];
                    $mix_total += $mix_number;
                @endphp
                <tr @if ($loop->last) class="last-row" @endif>
                    <td>{{$nutrition_details['name']}}</td>
                    <td class="text-end">{{ number_format($nutrition_details['cost_per_kg'], 2) }}</td>
                    <td class="text-end">{{ number_format($nutrition_details['quantity'], 1) }}</td>
                    <td class="text-end">{{ number_format($nutrition_details['mix_percent'], 2) }}</td>
                    <td class="text-end">{{ number_format($nutrition_details['amount'], 2) }}</td>
                </tr>
                @endforeach
                <tr class="total-row">
                    <td class="fw-bold">Total - Before Weight Gain or Loss</td>
                    <td class="text-end fw-bold">{{ number_format($costper_recipe, 2) }}</td>
                    <td class="text-end fw-bold">{{ number_format($nutrition['totals']['quantity'], 1) }}</td>
                    <td class="text-end fw-bold">{{ number_format(round($mix_total), 2) }}</td>
                    <td class="text-end fw-bold">{{ number_format($nutrition['totals']['amount'], 2) }}</td>
                </tr>
                @php
                if((is_numeric($costper_recipe) &&  floatval($costper_recipe) != 0 )){
                    $loss_mix_percent = 100 + ($loss_percent);
                    $after_weight = ($nutrition['totals']['quantity'] * $loss_mix_percent) / 100;
                    $after_kg = ($nutrition['totals']['amount'] / $after_weight) * 1000;
                }else{
                    $loss_mix_percent = 0.00;
                    $after_weight = 0.0;   
                    $after_kg = 0.00;   
                }    
                @endphp 
                <tr class="total-row">
                    <td class="fw-bold">Total - After Weight Gain or Loss</td>
                    <td class="text-end fw-bold">{{ number_format($after_kg, 2) }}</td>
                    <td class="text-end fw-bold">{{ number_format($after_weight, 1) }}</td>
                    <td class="text-end fw-bold">{{ number_format(round($loss_mix_percent), 2) }}</td>
                    <td class="text-end fw-bold">{{ number_format($nutrition['totals']['amount'], 2) }}</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>