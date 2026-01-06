@php
    $ingredients = $food->estimated_ingredients;
    $primaryIngredients = $ingredients['primary_ingredients'] ?? [];
    $possibleAdditives = $ingredients['possible_additives'] ?? [];
    
    // Handle if ingredients is a flat array
    if (empty($primaryIngredients) && !isset($ingredients['primary_ingredients'])) {
        $primaryIngredients = is_array($ingredients) ? $ingredients : [];
    }
@endphp

@if(count($primaryIngredients) > 0 || count($possibleAdditives) > 0)
<div class="card border-start border-primary border-3" style="border-left-color: #FFB1A0 !important;">
    <div class="card-header bg-transparent d-flex align-items-center gap-2">
        <i class="bi bi-box-seam text-primary"></i>
        <h6 class="mb-0">Estimated Ingredients</h6>
        <button type="button" class="btn btn-sm btn-link text-muted p-0 ms-1" 
                data-bs-toggle="popover"    
                data-bs-trigger="hover focus"
                data-bs-html="true"
                data-bs-content="<p>AI-analyzed ingredient breakdown with estimated percentages.</p><p class='mb-0 small text-warning'><i class='bi bi-exclamation-triangle'></i> Always verify with supplier specifications</p>">
            <i class="bi bi-info-circle"></i>
        </button>
    </div>
    <div class="card-body">
        @if(count($primaryIngredients) > 0)
            <h6 class="text-muted small mb-2">Primary Ingredients:</h6>
            <ul class="list-group list-group-flush mb-3">
                @foreach($primaryIngredients as $ing)
                    @php
                        $name = $ing['name'] ?? $ing['ingredient'] ?? '';
                        $confidence = $ing['confidence'] ?? null;
                        $percentage = $ing['estimated_percentage'] ?? null;
                        $reasoning = $ing['reasoning'] ?? null;
                    @endphp
                    <li class="list-group-item px-0 d-flex justify-content-between align-items-start">
                        <div>
                            <span class="fw-medium">{{ $name }}</span>
                            @if($reasoning)
                                <p class="text-muted small mb-0">{{ $reasoning }}</p>
                            @endif
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            @if($percentage)
                                <small class="text-muted">{{ $percentage }}</small>
                            @endif
                            @if($confidence)
                                @include('backend.fsanz_food.partials._confidence-badge', ['score' => $confidence])
                            @endif
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif

        @if(count($possibleAdditives) > 0)
            <h6 class="text-muted small mb-2">Possible Additives:</h6>
            <ul class="list-group list-group-flush">
                @foreach($possibleAdditives as $ing)
                    @php
                        $name = $ing['name'] ?? $ing['ingredient'] ?? '';
                        $confidence = $ing['confidence'] ?? null;
                        $reasoning = $ing['reasoning'] ?? null;
                    @endphp
                    <li class="list-group-item px-0 d-flex justify-content-between align-items-start">
                        <div>
                            <span class="fw-medium">{{ $name }}</span>
                            @if($reasoning)
                                <p class="text-muted small mb-0">{{ $reasoning }}</p>
                            @endif
                        </div>
                        @if($confidence)
                            @include('backend.fsanz_food.partials._confidence-badge', ['score' => $confidence])
                        @endif
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</div>
@endif
