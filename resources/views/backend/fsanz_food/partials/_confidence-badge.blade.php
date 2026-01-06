@php
    $percentage = round($score * 100);
    if ($percentage >= 80) {
        $badgeClass = 'bg-success bg-opacity-10 text-success border-success';
    } elseif ($percentage >= 60) {
        $badgeClass = 'bg-warning bg-opacity-10 text-warning border-warning';
    } else {
        $badgeClass = 'bg-secondary bg-opacity-10 text-secondary border-secondary';
    }
@endphp

<span class="badge {{ $badgeClass }} border border-opacity-25">
    @if(isset($showIcon) && $showIcon)
        <i class="bi bi-cpu me-1"></i>
    @endif
    {{ $percentage }}%
</span>
