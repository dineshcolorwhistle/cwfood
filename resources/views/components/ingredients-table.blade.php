<div class="batch_table_wrapper card p-3 rounded-2 box-shadow pt-4 mb-3">
    <h5 class="ingredient_table_title text-primary-orange">Ingredients</h5>
    <div class="ing_table_container">
        <table class="table table-sm ingredient_table">
            <thead>
                <tr>
                    <th class="fw-bold">Component</th>
                    <th class="fw-bold">Name</th>
                    <th class="fw-bold text-end">Quantity&nbsp;(g)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($groupedIngredients as $component => $ingredients)
                    @foreach($ingredients as $ingredient)
                        <tr>
                            <td>{{ ucfirst($component) }}</td>
                            <td>{{ $ingredient->ing_name }}</td>
                            <td align="right">{{ number_format($ingredient->quantity_g, 0) }}</td>
                        </tr>
                    @endforeach
                @endforeach
                <tr>
                    <td colspan="2" style="border-top: 2px solid #050505ff !important;;">Batch Weight <strong>Before</strong> Gain or loss (g)</td>
                    <td align="right" style="border-top: 2px solid #050505ff !important;;">{{ number_format($batchTotal, 0) }}</td>
                </tr>
                <tr>
                    <td colspan="2">Batch Weight <strong>After</strong> Gain or loss (g)</td>
                    <td align="right">{{ number_format($product->batch_after_waste_g, 0) }}</td>
                </tr>
                <tr>
                    <td colspan="2">Weight Gain or Loss % </td>
                    <td align="right">{{ $product->batch_baking_loss_percent }}</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>