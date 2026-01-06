<div class="recipe_details_wrapper menu-bg p-3 mb-3 rounded-2 box-shadow pt-4">
    <h5 class="recipe_details_title text-primary-orange">Official Details</h5>
    <div class="recipe_details_container">
        <table class="table table-sm recipe_details_table">
            <tr>
                <th>Created by</th>
                <td>{{ optional($product->creator)->name }}</td>
            </tr>
            <tr>
                <th>Created on</th>
                <td>{{ $product->created_at ? $product->created_at->format('d F Y') : '-' }}</td>
            </tr>
            <tr>
                <th>Updated by</th>
                <td>{{ optional($product->updater)->name }}</td>
            </tr>
            <tr>
                <th>Updated on</th>
                <td>{{ $product->updated_at ? $product->updated_at->format('d F Y') : '-' }}</td>
            </tr>
            <tr>
                <th>Version</th>
                <td>{{ $product->updated_version ?? 'V1' }}</td>
            </tr>
        </table>
    </div>
</div>