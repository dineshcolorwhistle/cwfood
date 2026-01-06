@php
    $origin = $food->estimated_origin;
    $primaryCountry = $food->primary_origin_country ?? ($origin['primary_origin_country'] ?? null);
    $australiaPercent = $food->estimated_australia_percent ?? ($origin['australia_percent'] ?? null);
    $alternativeSources = $food->alternative_origin_sources ?? ($origin['alternative_origin_sources'] ?? []);
    $isVariable = $food->origin_is_variable ?? ($origin['origin_is_variable'] ?? false);
    $likelySources = $origin['likely_sources'] ?? [];
@endphp

<div class="card border-start border-info border-3">
    <div class="card-header bg-transparent d-flex align-items-center gap-2">
        <i class="bi bi-globe text-info"></i>
        <h6 class="mb-0">Country of Origin</h6>
        @if($food->origin_confidence_score)
            @include('backend.fsanz_food.partials._confidence-badge', ['score' => $food->origin_confidence_score, 'showIcon' => true])
        @endif
    </div>
    <div class="card-body">
        <div class="row">
            @if($primaryCountry)
                <div class="col-md-4 mb-3">
                    <p class="text-muted small mb-1">Primary Origin</p>
                    <p class="fw-semibold mb-0">{{ $primaryCountry }}</p>
                </div>
            @endif

            @if($australiaPercent !== null)
                <div class="col-md-4 mb-3">
                    <p class="text-muted small mb-1">Australian Content</p>
                    <p class="fw-semibold mb-0">{{ $australiaPercent }}%</p>
                </div>
            @endif

            @if($isVariable)
                <div class="col-md-4 mb-3">
                    <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25">
                        <i class="bi bi-arrow-repeat me-1"></i>Variable Origin
                    </span>
                </div>
            @endif
        </div>

        @if(count($likelySources) > 0)
            <h6 class="small fw-semibold mb-2 mt-2">Likely Sources</h6>
            <div class="table-responsive">
                <table class="table table-sm table-borderless">
                    <thead>
                        <tr class="text-muted small">
                            <th>Country</th>
                            <th>Est. %</th>
                            <th>Reasoning</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($likelySources as $source)
                            <tr>
                                <td class="fw-medium">{{ $source['country'] ?? 'Unknown' }}</td>
                                <td>{{ $source['estimated_percentage'] ?? '-' }}%</td>
                                <td class="text-muted small">{{ $source['reasoning'] ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        @if(count($alternativeSources) > 0)
            <h6 class="small fw-semibold mb-2 mt-2">Alternative Sources</h6>
            <div class="d-flex flex-wrap gap-1">
                @foreach($alternativeSources as $source)
                    <span class="badge bg-light text-dark border">{{ $source }}</span>
                @endforeach
            </div>
        @endif
    </div>
</div>
