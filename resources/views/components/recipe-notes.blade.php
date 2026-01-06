<div class="card nutrition_card mb-3 p-3 rounded-2 box-shadow">
    <!-- Notes Section -->
    <div class="notes_wrapper">
        <h5 class="notes_title text-primary-orange mt-2">Recipe Notes</h5>
        <div class="notes_data mb-2">
            <div class="text-primary-dark-mud">
                @if($product->recipe_notes && strip_tags($product->recipe_notes) != '')
                    {!! format_content($product->recipe_notes) !!}
                @else
                    <p>No notes available.</p>
                @endif
            </div>
        </div>
    </div>
</div>