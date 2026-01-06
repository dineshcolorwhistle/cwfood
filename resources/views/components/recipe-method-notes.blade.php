<div class="card nutrition_card mb-3 p-3 rounded-2 box-shadow">
    <!-- Recipe Method Section -->
    <div class="recipe_method_wrapper mt-2">
        <h5 class="recipe_method_title text-primary-orange">Recipe Method</h5>
        <div class="recipe_method_container text-primary-dark-mud">
            @if($product->recipe_method && strip_tags($product->recipe_method) != '')
                {!! format_content($product->recipe_method) !!}
            @else
                <p>No recipe method available.</p>
            @endif
        </div>
    </div>
</div>