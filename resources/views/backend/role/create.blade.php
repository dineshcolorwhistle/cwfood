@extends('backend.master', ['activeItem' => 'roles', 'activeSubitem' => 'roles'])

@section('title', 'Create Role')

@section('content')
<div class="card">
    <div class="card-header">
        <h5>Create Role</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('roles.store') }}" method="POST">
            @csrf
            <div class="form-group">
                <label for="name">Role Name</label>
                <input type="text" name="name" class="form-control" placeholder="Role Name" required>
            </div>
            <div class="form-group">
                <label for="description">Description</label>
                <textarea name="description" class="form-control" placeholder="Role Description"></textarea>
            </div>
            <button type="submit" class="btn btn-success">Create</button>
        </form>
    </div>
</div>
@endsection