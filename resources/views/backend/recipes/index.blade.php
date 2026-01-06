@extends('backend.master', [
'activeItem' => 'recipes',
'activeSubitem' => 'recipes'
])

@section('title', 'Dashboard')

@section('content')
<div class="container-fluid recipes">
    <div class="row">
        <div class="col-12 col-lg-8 mx-auto my-5">
            <div class="">
                <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                    <div class="bg-gradient-primary shadow-primary border-radius-lg pt-4 pb-3">
                        <div class="multisteps-form__progress">
                            <button class="multisteps-form__progress-btn js-active" type="button" title="User Info">
                                <span>DESCRIPTION</span>
                            </button>
                            <button class="multisteps-form__progress-btn" type="button" title="Address">
                                <span>UNITS</span>
                            </button>
                            <button class="multisteps-form__progress-btn" type="button" title="Order Info">
                                <span>RECIPE</span>
                            </button>
                            <button class="multisteps-form__progress-btn" type="button" title="Order Info">
                                <span>AUDIT</span>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <form class="multisteps-form__form" style="height: 368px;" action="{{ route('recipes.store') }}" method="POST">
                        @csrf
                        <div class="multisteps-form__panel js-active" data-animation="FadeIn">
                            <div class="multisteps-form__content">
                                <div class="row mt-3">
                                    <div class="col-8 col-sm-8 mt-4 mt-sm-0">
                                        <div class="input-group input-group-dynamic mb-4">
                                            <label class="form-label">Product Name</label>
                                            <input type="text" name="prod_name" class="form-control multisteps-form__input" onfocus="focused(this)" onfocusout="defocused(this)">
                                        </div>
                                        <div class="input-group input-group-dynamic mb-4">
                                            <label class="form-label">Product Description [Short]</label>
                                            <textarea name="description_short" class="form-control multisteps-form__input" onfocus="focused(this)" onfocusout="defocused(this)"></textarea>
                                        </div>
                                        <div class="input-group input-group-dynamic">
                                            <label class="form-label">Product Description [Long]</label>
                                            <textarea name="description_long" class="form-control multisteps-form__input" onfocus="focused(this)" onfocusout="defocused(this)"></textarea>
                                        </div>
                                    </div>
                                    <div class="col-4 col-sm-4 mt-4 mt-sm-0">
                                        <div class="input-group input-group-dynamic mb-4">
                                            <label class="form-label">SKU</label>
                                            <input type="text" name="prod_sku" class="form-control multisteps-form__input" onfocus="focused(this)" onfocusout="defocused(this)">
                                        </div>
                                    </div>
                                </div>
                                <div class="button-row d-flex mt-4">
                                    <button class="btn bg-gradient-dark ms-auto mb-0 js-btn-next" type="submit" title="Next">Next</button>
                                </div>
                            </div>
                        </div>
                    </form>
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