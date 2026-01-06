@extends('backend.master', [
'activeItem' => 'dashboard',
'activeSubitem' => 'dashboard'
])

@section('title', '{{ $food->food_name }} Details')

@section('content')
<div class="fsanz_details">
    <div class="">
        <div class="card-header pb-0">
            <h5 class="mb-0">{{ $food->food_name }} Details</h5>
            <a href="{{ $backUrl }}" class="btn btn-secondary">Back</a>
        </div>
        <div class="card-body px-3 pb-3">
            <ul class="list-group">
                <li class="list-group-item">Food ID: {{ $food->food_id }}</li>
                <li class="list-group-item">Food Name: {{ $food->food_name }}</li>
                <li class="list-group-item">Description: {{ $food->description }}</li>
                <li class="list-group-item">Energy (kJ): {{ number_format($food->energy_kj, 0) }}</li>
                <li class="list-group-item">Protein (g): {{ number_format($food->protein_g, 1) }}</li>
                <li class="list-group-item">Fat, total (g): {{ number_format($food->fat_total_g, 1) }}</li>
                <li class="list-group-item">Fat, saturated (g): {{ number_format($food->fat_saturated_g, 1) }}</li>
                <li class="list-group-item">Carbohydrate (g): {{ number_format($food->carbohydrate_g, 1) }}</li>
                <li class="list-group-item">Total Sugars (g): {{ number_format($food->total_sugars_g, 1) }}</li>
                <li class="list-group-item">Sodium (mg): {{ number_format($food->sodium_mg, 0) }} mg</li>
                <li class="list-group-item">Specific Gravity: {{ $food->specific_gravity }}</li>
            </ul>

        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets') }}/js/plugins/perfect-scrollbar.min.js"></script>
@endpush