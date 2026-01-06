@if($food->ai_estimation_status !== 'completed')
    <div class="card">
        <div class="card-body text-center py-5">
            <p class="text-muted mb-0">AI analysis has not been completed for this food item yet.</p>
        </div>
    </div>
@else
    <div class="row g-4">
        {{-- Regulatory Info Card --}}
        @if($food->estimated_regulatory_info)
            <div class="col-lg-6">
                @include('backend.fsanz_food.partials.cards._regulatory-info-card')
            </div>
        @endif

        {{-- Typical Uses Card --}}
        @if($food->estimated_typical_uses)
            <div class="col-lg-6">
                @include('backend.fsanz_food.partials.cards._typical-uses-card')
            </div>
        @endif
    </div>

    {{-- AI Disclaimer --}}
    <div class="border-top mt-4 pt-3">
        <p class="text-muted small mb-0">
            <i class="bi bi-exclamation-triangle text-warning me-1"></i>
            AI-generated estimates require professional verification before use in food safety decisions.
        </p>
    </div>
@endif
