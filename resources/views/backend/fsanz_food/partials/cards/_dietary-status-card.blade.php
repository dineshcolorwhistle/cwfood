@php
    $dietaryStatus = $food->estimated_dietary_status;
    $confidenceScore = $food->dietary_confidence_score;
    
    // Priority labels
    $priorityLabels = [];
    if ($food->getDietaryStatus('vegan')) $priorityLabels[] = 'Vegan';
    if ($food->getDietaryStatus('vegetarian') && !$food->getDietaryStatus('vegan')) $priorityLabels[] = 'Vegetarian';
    if ($food->getDietaryStatus('halal')) $priorityLabels[] = 'Halal';
    if ($food->getDietaryStatus('kosher')) $priorityLabels[] = 'Kosher';
    
    
    // Other labels
    $excludeKeys = ['vegetarian', 'vegan', 'halal', 'kosher'];
    $otherLabels = [];
    foreach ($dietaryStatus as $key => $value) {
        if (!in_array($key, $excludeKeys) && $food->getDietaryStatus($key)) {
            $otherLabels[] = ucwords(str_replace('_', ' ', $key));
        }
    }
@endphp

<div class="card border-start border-success border-3 h-100">
    <div class="card-header bg-transparent d-flex align-items-center gap-2">
        <i class="bi bi-leaf text-success"></i>
        <h6 class="mb-0">Dietary Status</h6>
        @if($confidenceScore)
            @include('backend.fsanz_food.partials._confidence-badge', ['score' => $confidenceScore, 'showIcon' => true])
        @endif
        <button type="button" class="btn btn-sm btn-link text-muted p-0 ms-1" 
                data-bs-toggle="popover" 
                data-bs-trigger="hover focus"
                data-bs-html="true"
                data-bs-content="<p>AI-estimated dietary and religious compliance.</p><p class='mb-0 small text-warning'><i class='bi bi-exclamation-triangle'></i> Religious certifications require official approval</p>">
            <i class="bi bi-info-circle"></i>
        </button>
    </div>
    <div class="card-body">
        @if(count($priorityLabels) > 0)
            <div class="d-flex flex-wrap gap-2 mb-3">
                @foreach($priorityLabels as $label)
                    <span class="badge bg-success">{{ $label }}</span>
                @endforeach
            </div>
        @endif

        @if(count($otherLabels) > 0)
            <div class="d-flex flex-wrap gap-2">
                @foreach($otherLabels as $label)
                    <span class="badge bg-secondary bg-opacity-25 text-dark">{{ $label }}</span>
                @endforeach
            </div>
        @endif

        @if(count($priorityLabels) === 0 && count($otherLabels) === 0)
            <p class="text-muted small mb-0">No specific dietary attributes identified.</p>
        @endif
    </div>
</div>
