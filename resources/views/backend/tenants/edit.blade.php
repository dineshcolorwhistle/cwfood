@extends('backend.master', ['activeItem' => 'tenants', 'activeSubitem' => 'tenants'])

@section('title', 'Edit Tenant')

@section('content')
<div class="card">
    <div class="card-header">
        <h5>Edit Tenant</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('tenants.update', $tenant->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label for="name">Name</label>
                <input type="text" name="name" class="form-control" value="{{ $tenant->name }}" required>
            </div>
            <div class="form-group">
                <label for="description">Description</label>
                <textarea name="description" class="form-control" required>{{ $tenant->description }}</textarea>
            </div>
            <div class="form-group">
                <label for="status">Status</label>
                <select name="status" class="form-control">
                    <option value="1" {{ $tenant->status == 1 ? 'selected' : '' }}>Active</option>
                    <option value="0" {{ $tenant->status == 0 ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
            <button type="submit" class="btn btn-success">Update</button>
        </form>
    </div>
</div>
@endsection