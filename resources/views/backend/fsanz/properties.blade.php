@extends('backend.master', [
'activeItem' => 'dashboard',
'activeSubitem' => 'fsanz_properties'
])

@section('title', 'FSANZ Food Properties Data')

@section('content')
<div class="fsanz_properties">
    <div class="">
        <div class="card-header pb-0">
            <h5 class="mb-0">FSANZ Food Properties Data</h5>
        </div>
        <div class="card-body px-0 pb-0">
            <!-- Add overflow-auto class to enable scrolling if table overflows -->
            <div class="">
                <table class="table responsiveness" id="properties-list">
                    <thead class="thead-light">
                        <tr>
                            <th>Food ID</th>
                            <th>Food Name</th>
                            <th>Description</th>
                            <th>Specific Gravity</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($properties as $property)
                        <tr>
                            <td>{{ $property->food_id }}</td>
                            <td>{{ $property->food_name }}</td>
                            <td>{{ $property->description }}</td>
                            <td>{{ number_format($property->specific_gravity, 2) }}</td>
                            <td>
                                <a href="{{ route('fsanz.details', $property->food_id) }}" class="btn btn-primary btn-sm">View Details</a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection


@push('scripts')
<script src="{{ asset('assets') }}/js/plugins/perfect-scrollbar.min.js"></script>

<!-- Datatables JS -->
<script src="{{ asset('assets') }}/js/plugins/datatables.js"></script>

<script>
    // Initialize DataTable for the properties table
    if (document.getElementById('properties-list')) {
        const dataTableSearch = new simpleDatatables.DataTable("#properties-list", {
            searchable: true,
            fixedHeight: false,
            perPage: 7
        });
    }
</script>

@endpush