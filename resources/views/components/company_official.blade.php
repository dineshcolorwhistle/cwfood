<div class="recipe_details_wrapper menu-bg p-3 mb-3 rounded-2 box-shadow pt-4">
    <h5 class="recipe_details_title text-primary-orange">Official Details</h5>
    <div class="recipe_details_container">
        <table class="table table-sm recipe_details_table">
            <tr>
                <th>Manufacturing Location</th>
                <td>{{ $product->factoryAddress ? $product->factoryAddress : 'N/A' }}</td>
            </tr>
            <tr>
                <th>Compliance Officer</th>
                <td>{{ $product->keyPerson ? $product->keyPerson : 'N/A' }}</td>
            </tr>
            <tr>
                <th>Date</th>
                <td>{{ date('d M Y') }}</td>
            </tr>
        </table>
    </div>
</div>