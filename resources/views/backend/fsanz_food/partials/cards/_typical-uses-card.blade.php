@php
    $typicalUses = $food->estimated_typical_uses;
    $primaryApplications = $typicalUses['primary_applications'] ?? [];
    $commonRecipes = $typicalUses['common_recipes'] ?? [];
    $functionalProperties = $typicalUses['functional_properties'] ?? [];
    $compatibleIngredients = $typicalUses['compatible_ingredients'] ?? [];
@endphp

<div class="card border-start border-3 h-100" style="border-left-color: #3776EC !important;">
    <div class="card-header bg-transparent d-flex align-items-center gap-2">
        <i class="bi bi-list-check text-info"></i>
        <h6 class="mb-0">Typical Uses</h6>
    </div>
    <div class="card-body">
        @if(count($primaryApplications) > 0)
            <h6 class="small fw-semibold mb-2">Primary Applications</h6>
            <ul class="list-unstyled mb-3">
                @foreach($primaryApplications as $item)
                    <li class="mb-1"><i class="bi bi-dot"></i> {{ $item }}</li>
                @endforeach
            </ul>
        @endif

        @if(count($commonRecipes) > 0)
            <h6 class="small fw-semibold mb-2">Common Recipes</h6>
            <ul class="list-unstyled mb-3">
                @foreach($commonRecipes as $item)
                    <li class="mb-1"><i class="bi bi-dot"></i> {{ $item }}</li>
                @endforeach
            </ul>
        @endif

        @if(count($functionalProperties) > 0)
            <h6 class="small fw-semibold mb-2">Functional Properties</h6>
            <div class="d-flex flex-wrap gap-1 mb-3">
                @foreach($functionalProperties as $item)
                    <span class="badge bg-info bg-opacity-10 text-info">{{ $item }}</span>
                @endforeach
            </div>
        @endif

        @if(count($compatibleIngredients) > 0)
            <h6 class="small fw-semibold mb-2">Compatible Ingredients</h6>
            <div class="d-flex flex-wrap gap-1">
                @foreach($compatibleIngredients as $item)
                    <span class="badge bg-secondary bg-opacity-25 text-dark">{{ $item }}</span>
                @endforeach
            </div>
        @endif
    </div>
</div>
