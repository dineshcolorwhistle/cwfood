<div class="oven_table_wrapper card p-3 rounded-2 box-shadow pt-4 mb-3">
    <h5 class="oven_table_title text-primary-orange">Oven Temperature and Time</h5>
    <div class="oven_table_container">
        <table class="table table-sm oven_table">
            <tr>
                <th>Oven Temperature (°C)</th>
                <td align="right">{{ $product->recipe_oven_temp }}</td>
            </tr>
            <tr>
                <th>Oven Time (hh:mm:ss)</th>
                <td align="right">{{ $product->formatted_oven_time  }}</td>
            </tr>
            <!-- <tr>
                <th>Mould Usage</th>
                <td align="right">{{ $product->recipe_mould_type }}</td>
            </tr> -->
        </table>
    </div>
</div>