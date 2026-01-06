@extends('backend.master', [
'activeItem' => 'recipes',
'activeSubitem' => 'recipes'
])

@section('title', 'Dashboard')

@section('content')
<style>

</style>
<div class="recipes2">
    <div class="row">
        <div class="col-12 col-lg-8 mx-auto my-5">
            <div class="">
                <div class="card-body">
                    <h2 class="success-head text-success">Success</h2>
                    <h5 class="success-msg text-success">The Data Saved Successfully.</h5>
                    <a class="btn btn-primary back-btn" href="{{ route('recipes.index') }}">Back</a>
                    <a class="btn btn-secondary  vierw-btn" href="{{ route('product') }}">View All</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // JavaScript will go here
</script>
@endpush