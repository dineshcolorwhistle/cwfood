<div>
@if($status === 'completed')
    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25">
        <span class="material-symbols-outlined">check</span>AI Analyzed
    </span>
@else
    <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25">
        <span class="material-symbols-outlined">pending_actions</span>Pending Analysis
    </span>
@endif
</div>
