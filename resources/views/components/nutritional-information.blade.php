<div class="card nutrition_card mb-3 p-3 rounded-2 box-shadow">
    <div class="card-body px-0 py-2">
        <div class="d-flex justify-content-between">
            <h5 class="text-primary-orange">Nutritional Information Panel</h5>
        </div>
        <div class="px-1 pt-4">
            <h5 class="text-dark-mud">Nutritional Information</h5>
            <p>Servings per package: {{ $product->serv_per_package }}<br>
                Serving size: {{ $product->serv_size_g }}g</p>
            <table class="table table-borderless nutrition_table">
                <thead>
                    <tr>
                        <th></th>
                        <th class="text-end">Per Serve</th>
                        <th class="text-end">Per 100g</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <th>Energy</th>
                        <td class="text-end">{{ number_format($product->energy_kJ_per_serve, 0) }} kJ</td>
                        <td class="text-end">{{ number_format($product->energy_kJ_per_100g, 0) }} kJ</td>
                    </tr>
                    <tr>
                        <th>Protein</th>
                        <td class="text-end">{{ number_format($product->protein_g_per_serve, 1) }} g</td>
                        <td class="text-end">{{ number_format($product->protein_g_per_100g, 1) }} g</td>
                    </tr>
                    <tr>
                        <th>Fat, total</th>
                        <td class="text-end">{{ number_format($product->fat_total_g_per_serve, 1) }} g</td>
                        <td class="text-end">{{ number_format($product->fat_total_g_per_100g, 1) }} g</td>
                    </tr>
                    <tr>
                        <th>Saturated</th>
                        <td class="text-end">{{ number_format($product->fat_saturated_g_per_serve, 1) }} g</td>
                        <td class="text-end">{{ number_format($product->fat_saturated_g_per_100g, 1) }} g</td>
                    </tr>
                    <tr>
                        <th>Carbohydrate</th>
                        <td class="text-end">{{ number_format($product->carbohydrate_g_per_serve, 1) }} g</td>
                        <td class="text-end">{{ number_format($product->carbohydrate_g_per_100g, 1) }} g</td>
                    </tr>
                    <tr>
                        <th>Sugars</th>
                        <td class="text-end">{{ number_format($product->sugar_g_per_serve, 1) }} g</td>
                        <td class="text-end">{{ number_format($product->sugar_g_per_100g, 1) }} g</td>
                    </tr>
                    <tr>
                        <th>Sodium</th>
                        <td class="text-end">{{ number_format($product->sodium_mg_per_serve, 0) }} mg</td>
                        <td class="text-end">{{ number_format($product->sodium_mg_per_100g, 0) }} mg</td>
                    </tr>
                </tbody>
            </table>

            @php
                $formattedIngredients = [];
                $ingredientsStr = $product->labelling_ingredients;

                // Step 1: Preserve commas by splitting with regex (captures words and commas separately)
                preg_match_all('/[^,\s]+|,/', $ingredientsStr, $matches);
                $ingredientsArray = $matches[0];

                // Convert allergens to lowercase and trim spaces
                $allergen = array_map('strtolower', array_map('trim', $allergen));

                // Step 2: Process each ingredient while preserving commas
                foreach ($ingredientsArray as $ing) {
                    $cleanIng = strtolower(trim($ing, '()')); // Remove parentheses for checking

                    if ($ing === ',') {
                        $formattedIngredients[] = ','; // Keep commas as they are
                    } elseif (in_array($cleanIng, $allergen)) {
                        $formattedIngredients[] = '<strong>' . $ing . '</strong>';
                    } else {
                        $formattedIngredients[] = $ing;
                    }
                }

                $formattedIngredients = trim(htmlspecialchars_decode(implode(' ', $formattedIngredients)));
                $formattedIngredients = str_replace(" ,", ",", $formattedIngredients);
            @endphp

            <p class="text-primary-dark-mud">
                INGREDIENTS: {!! $formattedIngredients !!}
            </p>
            <p class="text-primary-dark-mud">
                CONTAINS: <span class="fw-bold">{{ $product->labelling_allergens }}</span>
            </p>
            <p class="text-primary-dark-mud">
                MAY BE PRESENT: <span class="fw-bold">{{ $product->labelling_may_contain }}</span>
            </p>
        </div>
    </div>
</div>