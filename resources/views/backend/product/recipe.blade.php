@extends('backend.master', [
'pageTitle' => 'View Product',
'activeMenu' => [
'item' => 'Product',
'subitem' => 'Product',
'additional' => 'ProductDetails',
],
'breadcrumbItems' => [
['label' => 'Products', 'url' => '#'],
['label' => 'Recipes']
],
'pageActions' => [
'created' => [
'user' => $product->creator ?? null,
'date' => $product->created_at ?? null,
'model' => 'Product'
],
'updated' => $product->created_at != $product->updated_at ? [
'user' => $product->updater ?? null,
'date' => $product->updated_at ?? null,
'model' => 'Product'
] : null,
'version' => $product->version ?? null
],
])


@push('styles')
<style>
    .text-right {
        text-align: right !important;
    }

    .action-container {
        display: none !important;
    }
</style>
@endpush
@section('content')
<div class="container-fluid product_specs">
    <div class="product_specs_content">
        <div class="card-header">
            <div class="top_title_wrapper d-flex justify-content-between align-items-center mb-3 hidden">
                 <div class="title-add">
                    <h1>Recipe Card</h1>
                </div>
                <div class="button-row">
                    <button type="button" class="btn btn-primary-orange plus-icon me-3" id="pdf_download" title="Download Recipe Card">
                        <span class="material-symbols-outlined">download</span>
                    </button>
                    <a href="{{ route('products.index', $product->id) }}" class="btn btn-secondary-white me-3">Back</a>
                    <div class="btn-group d-inline">
                        <button class="icon-primary-orange me-0 dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <span class="material-symbols-outlined fs-1">more_vert</span>
                        </button>
                         @php        
                            $user_roleID = Session::get('role_id');
                        @endphp
                        <ul class="dropdown-menu">
                            @if(!in_array($user_roleID, [4, 7]))
                            <li>
                                <a href="{{ route('products.edit', $product->id) }}" class="dropdown-item text-primary-dark-mud me-2">
                                    <span class="sidenav-normal ms-2 ps-1">Edit</span>
                                </a>
                            </li>
                            <li>
                                <button type="button" class="dropdown-item text-primary-dark-mud me-0 delete-product text-capitalize" data-id="{{ $product->id }}">
                                    <span class="sidenav-normal ms-2 ps-1">Delete</span>
                                </button>
                            </li>
                            <hr>
                            @endif

                            <!-- Check current page and exclude it from the options -->
                            @if(Route::currentRouteName() !== 'products.spec')
                            <li>
                                <a class="dropdown-item text-primary-dark-mud" href="{{ route('products.spec', $product->id) }}">
                                    <span class="sidenav-normal ms-2 ps-1">Products Specs</span>
                                </a>
                            </li>
                            @endif

                            @if(Route::currentRouteName() !== 'products.recipe')
                            <li>
                                <a class="dropdown-item text-primary-dark-mud" href="{{ route('products.recipe', $product->id) }}">
                                    <span class="sidenav-normal ms-2 ps-1">Recipes</span>
                                </a>
                            </li>
                            @endif

                            @if(Route::currentRouteName() !== 'products.labelling')
                            <li>
                                <a class="dropdown-item text-primary-dark-mud" href="{{ route('products.labelling', $product->id) }}">
                                    <span class="sidenav-normal ms-2 ps-1">Labelling</span>
                                </a>
                            </li>
                            @endif

                            @if(Route::currentRouteName() !== 'products.costing')
                                @if(!in_array($user_roleID, [7]))
                                <li>
                                    <a class="dropdown-item text-primary-dark-mud" href="{{ route('products.costing', $product->id) }}">
                                        <span class="sidenav-normal ms-2 ps-1">Costing</span>
                                    </a>
                                </li>
                                @endif
                            @endif
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <div class="row card-body pt-1">
            <!-- Ingredients Section -->
            <div class="col-lg-5 col-md-5 mb-4">
                <x-product-card :product="$product" />
                <x-ingredients-table :groupedIngredients="$groupedIngredients" :batchTotal="$batchTotal" :product="$product"/>
            </div>

            <!-- Batch Info and Oven Temp Section -->
            <div class="col-lg-5 col-md-5 mb-lg-4 mb-md-4">
                <x-oven-table :product="$product" />
                <x-recipe-method-notes :product="$product" />
                <x-recipe-notes :product="$product" />
            </div>

            <!-- Official Details, Tags -->
            <div class="col-lg-2 col-md-2 mb-4">
                <x-company-official :product="$product" />
                <x-tags-card :product="$product" />
            </div>
        </div>
        <div class="product_details_footer card-footer">
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).on('click', '.delete-product', function(e) {
        
        e.preventDefault();
        const button = $(this);
        const id = button.data('id');
        if (!id) {
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: 'Product ID not found'
            });
            return;
        }

        const baseUrl = "{{ config('app.url') }}";
        const url = `${baseUrl}/products/${id}`;
        // Alternative way using Laravel route:
        // const url = "{{ route('products.destroy', '') }}/" + id;

        Swal.fire({
            title: 'Are you sure?',
            text: 'This will delete the product and all related data. You won\'t be able to revert this!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: url,
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Deleted!',
                                text: response.message,
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => {
                                window.location.href = "{{ route('products.index') }}";
                            });
                        }
                    },
                    error: function(xhr) {
                        console.error('Delete Error:', xhr);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: xhr.responseJSON?.message || 'An error occurred while deleting the product'
                        });
                    }
                });
            }
        });
    });

    $(document).on('click','#pdf_download',function(){
        Swal.fire({
            title: 'Download Product Recipe Card',
            html: `
                <h3 style="margin-top:10px; font-size:18px; font-weight:bold;">
                    Which Version Would You Like To Download?
                </h3>
                <p style="font-size:14px; color:#555;">
                    Please note: To change the colours of your ‘company colours’ PDF download, please head over to settings → Advance settings → Branding & Visual Identity
                </p>
            `,
            showCloseButton: true, // this adds the "X" button
            showCancelButton: true, // this will be our 2nd button
            confirmButtonText: 'Black & White',
            cancelButtonText: 'Company Colours',
            customClass: {
                confirmButton: 'exp-black',
                cancelButton: 'exp-custom'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                // Handle Black & White download
                window.location.href = "{{ route('products.download_recipe', [$product->id, 'black']) }}";
            } else if (result.dismiss === Swal.DismissReason.cancel) {
                // Handle Custom Color download
                window.location.href = "{{ route('products.download_recipe', [$product->id, 'custom']) }}";
            }
        });
    });
</script>
@endpush