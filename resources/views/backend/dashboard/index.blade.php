@extends('backend.master', [
'activeItem' => 'dashboard',
'activeSubitem' => 'dashboard'
])

@section('title', 'Dashboard')

@section('content')
<div class="dashboard">
    Dashboard
</div>
@endsection

@push('scripts')
<script>
    // JavaScript will gohere
</script>
@endpush