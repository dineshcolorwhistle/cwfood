@php
    $allergens = $food->estimated_allergens;
    $contains = $allergens['contains'] ?? [];
    $mayContain = $allergens['may_contain'] ?? [];
    $fsanzStatement = $allergens['fsanz_statement'] ?? null;
@endphp

<div class="card border-start border-danger border-3 h-100" style="border-left-color: #328678 !important;">
    <div class="card-header bg-transparent d-flex align-items-center gap-2">
        <i class="bi bi-exclamation-triangle text-danger"></i>
        <h6 class="mb-0">Allergen Information</h6>
        <button type="button" class="btn btn-sm btn-link text-muted p-0 ms-1" 
                data-bs-toggle="popover" 
                data-bs-trigger="hover focus"
                data-bs-html="true"
                data-bs-content="<p>FSANZ Standard 1.2.3 allergen assessment.</p><ul class='mb-0'><li><strong>Contains</strong> = allergen present</li><li><strong>May contain</strong> = cross-contamination risk</li></ul>">
            <i class="bi bi-info-circle"></i>
        </button>
    </div>
    <div class="card-body">
        @if(count($contains) > 0)
            <p class="fw-semibold text-danger small mb-2">Contains:</p>
            <div class="d-flex flex-wrap gap-1 mb-3">
                @foreach($contains as $item)
                    @php
                        $allergenName = is_string($item) ? $item : ($item['allergen'] ?? '');
                    @endphp
                    <span class="badge bg-danger">{{ $allergenName }}</span>
                @endforeach
            </div>
        @endif

        @if(count($mayContain) > 0)
            <p class="fw-semibold small mb-2">May Contain:</p>
            <div class="d-flex flex-wrap gap-1 mb-3">
                @foreach($mayContain as $item)
                    @php
                        $allergenName = is_string($item) ? $item : ($item['allergen'] ?? '');
                    @endphp
                    <span class="badge bg-light text-dark border">{{ $allergenName }}</span>
                @endforeach
            </div>
        @endif

        @if($fsanzStatement)
            <p class="text-muted small fst-italic mb-0">{{ $fsanzStatement }}</p>
        @endif
    </div>
</div>
