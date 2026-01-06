<div class="batch_table_wrapper card p-3 rounded-2 box-shadow pt-4 mb-3">
    <h5 class="batch_table_title text-primary-orange">Batch Size and Losses (g)</h5>
    <div class="batch_table_container">
        <table class="table table-sm batch_table">
            <tr>
                <th>Batch (g)</th>
                <td align="right">{{ number_format($batchTotal, 0) }}</td>
            </tr>
            <tr>
                <th>Batch (after baking loss)</th>
                <td align="right">{{ number_format($product->batch_after_waste_g, 0) }}</td>
            </tr>
            <!-- <tr>
                <th>Batch (after waste)</th>
                <td align="right">{{ number_format($product->batch_after_waste_g, 0) }}</td>
            </tr> -->
        </table>
    </div>
</div>