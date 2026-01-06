@extends('backend.master', ['activeItem' => 'tenants', 'activeSubitem' => 'tenants'])

@section('title', 'Add Tenant')

@section('content')
<div class="card">
    <div class="card-header">
        <h5>Add Tenant</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('tenants.store') }}" method="POST">
            @csrf
            <div class="form-group">
                <label for="name">Name</label>
                <input type="text" name="name" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="description">Description</label>
                <textarea name="description" class="form-control" required></textarea>
            </div>
            <div class="form-group">
                <label for="status">Status</label>
                <select name="status" class="form-control">
                    <option value="1">Active</option>
                    <option value="0">Inactive</option>
                </select>
            </div>
            <button type="submit" class="btn btn-success">Save</button>
        </form>
    </div>
</div>
@endsection