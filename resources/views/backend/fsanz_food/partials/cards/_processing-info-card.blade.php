@php
    $processingInfo = $food->estimated_processing_info;
@endphp

<div class="card border-start border-purple border-3 h-100" style="border-left-color: #AF92FF !important;">
    <div class="card-header bg-transparent d-flex align-items-center gap-2">
        <i class="bi bi-funnel" style="color: #AF92FF;"></i>
        <h6 class="mb-0">Processing Characteristics</h6>
        <button type="button" class="btn btn-sm btn-link text-muted p-0 ms-1" 
                data-bs-toggle="popover" 
                data-bs-trigger="hover focus"
                data-bs-html="true"
                data-bs-content="<p>AI-estimated processing methods and preservation characteristics.</p><p class='mb-0 small text-warning'><i class='bi bi-exclamation-triangle'></i> Verify processing details with supplier</p>">
            <i class="bi bi-info-circle"></i>
        </button>
    </div>
    <div class="card-body">
        <dl class="row mb-0">
            @foreach($processingInfo as $key => $value)
                <dt class="col-sm-5 text-muted">{{ ucwords(str_replace('_', ' ', $key)) }}</dt>
                <dd class="col-sm-7 fw-medium">{{ is_bool($value) ? ($value ? 'Yes' : 'No') : $value }}</dd>
            @endforeach
        </dl>
    </div>
</div>
