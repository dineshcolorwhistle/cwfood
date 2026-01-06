@php
    $regulatoryInfo = $food->estimated_regulatory_info;
@endphp

<div class="card border-start border-3 h-100" style="border-left-color: #328678 !important;">
    <div class="card-header bg-transparent d-flex align-items-center gap-2">
        <i class="bi bi-exclamation-triangle text-warning"></i>
        <h6 class="mb-0">Regulatory Information</h6>
        <button type="button" class="btn btn-sm btn-link text-muted p-0 ms-1" 
                data-bs-toggle="popover" 
                data-bs-trigger="hover focus"
                data-bs-html="true"
                data-bs-content="<p>AI-estimated regulatory compliance status under FSANZ Food Standards Code.</p><p class='mb-0 small text-warning'><i class='bi bi-exclamation-triangle'></i> Verify compliance with food regulator</p>">
            <i class="bi bi-info-circle"></i>
        </button>
    </div>
    <div class="card-body">
        <dl class="row mb-0">
            @foreach($regulatoryInfo as $key => $value)
                @php
                    if (is_bool($value)) {
                        $display = $value ? 'Yes' : 'No';
                    } elseif (is_array($value)) {
                        $display = !empty($value) ? implode(', ', $value) : 'None';
                    } else {
                        $display = $value ?? '—';
                    }
                @endphp
                <dt class="col-sm-5 text-muted">{{ ucwords(str_replace('_', ' ', $key)) }}</dt>
                <dd class="col-sm-7 fw-medium">{{ $display }}</dd>
            @endforeach
        </dl>
    </div>
</div>
