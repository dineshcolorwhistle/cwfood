@extends('backend.master', ['activeItem' => 'tenants', 'activeSubitem' => 'tenants'])

@section('title', 'Tenants')

@section('content')
<div class="tenants">
    <div class="">
        <div class="card-header">
            <h5>Tenants</h5>
            <a href="{{ route('tenants.create') }}" class="btn btn-primary">Add Tenant</a>
        </div>
        <div class="card-body">
            <table class="table table-responsive">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($tenants as $tenant)
                    <tr>
                        <td>{{ $tenant->name }}</td>
                        <td>{{ $tenant->description }}</td>
                        <td>{{ $tenant->status ? 'Active' : 'Inactive' }}</td>
                        <td>
                            <a href="{{ route('tenants.edit', $tenant->id) }}" class="btn btn-warning">Edit</a>
                            {{--<form action="{{ route('tenants.destroy', $tenant->id) }}" method="POST" style="display:inline-block;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">Delete</button>
                            </form> --}}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('script')
<script>
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!confirm('Are you sure you want to delete this tenant?')) {
                e.preventDefault();
            }
        });
    });
</script>
@endpush