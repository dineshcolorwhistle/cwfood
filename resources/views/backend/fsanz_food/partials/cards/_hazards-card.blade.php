@php
    $hazards = $food->estimated_hazards;
    $biological = $hazards['biological'] ?? [];
    $chemical = $hazards['chemical'] ?? [];
    $physical = $hazards['physical'] ?? [];
@endphp

<div class="card border-start border-3 h-100" style="border-left-color: #3776EC !important;">
    <div class="card-header bg-transparent d-flex align-items-center gap-2">
        <i class="bi bi-shield-exclamation text-warning"></i>
        <h6 class="mb-0">Food Safety Hazards</h6>
        <button type="button" class="btn btn-sm btn-link text-muted p-0 ms-1" 
                data-bs-toggle="popover" 
                data-bs-trigger="hover focus"
                data-bs-html="true"
                data-bs-content="<p>Potential hazards requiring HACCP control measures:</p><ul class='mb-0'><li><strong>Biological</strong>: Bacteria, viruses</li><li><strong>Chemical</strong>: Pesticides, mycotoxins</li><li><strong>Physical</strong>: Foreign objects</li></ul>">
            <i class="bi bi-info-circle"></i>
        </button>
    </div>
    <div class="card-body">
        @if(count($biological) > 0)
            <h6 class="small fw-semibold mb-2">Biological</h6>
            <ul class="list-unstyled mb-3">
                @foreach($biological as $item)
                    @php
                        $name = is_string($item) ? $item : ($item['name'] ?? $item['hazard'] ?? '');
                        $controlMeasures = is_array($item) ? ($item['control_measures'] ?? null) : null;
                    @endphp
                    <li class="mb-2">
                        <span class="fw-medium">{{ $name }}</span>
                        @if($controlMeasures)
                            <p class="text-muted small mb-0">{{ $controlMeasures }}</p>
                        @endif
                    </li>
                @endforeach
            </ul>
        @endif

        @if(count($chemical) > 0)
            <h6 class="small fw-semibold mb-2">Chemical</h6>
            <ul class="list-unstyled mb-3">
                @foreach($chemical as $item)
                    @php
                        $name = is_string($item) ? $item : ($item['name'] ?? $item['hazard'] ?? '');
                        $controlMeasures = is_array($item) ? ($item['control_measures'] ?? null) : null;
                    @endphp
                    <li class="mb-2">
                        <span class="fw-medium">{{ $name }}</span>
                        @if($controlMeasures)
                            <p class="text-muted small mb-0">{{ $controlMeasures }}</p>
                        @endif
                    </li>
                @endforeach
            </ul>
        @endif

        @if(count($physical) > 0)
            <h6 class="small fw-semibold mb-2">Physical</h6>
            <ul class="list-unstyled mb-0">
                @foreach($physical as $item)
                    @php
                        $name = is_string($item) ? $item : ($item['name'] ?? $item['hazard'] ?? '');
                        $controlMeasures = is_array($item) ? ($item['control_measures'] ?? null) : null;
                    @endphp
                    <li class="mb-2">
                        <span class="fw-medium">{{ $name }}</span>
                        @if($controlMeasures)
                            <p class="text-muted small mb-0">{{ $controlMeasures }}</p>
                        @endif
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</div>
