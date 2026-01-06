@extends('backend.master', [
'pageTitle' => 'Products Import',
'activeMenu' => [
'item' => 'Products',
'subitem' => 'Products',
'additional' => '',
],
'breadcrumbItems' => [
['label' => 'Database', 'url' => '#'],
['label' => 'Products']
],])

@section('content')
<div class="container-fluid products form-wizard">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="">
                    <div class="card-header">
                        <h4>Import Results</h4>
                    </div>
                    <div class="card-body">
                        <!-- Successfully Imported Products -->
                        <div class="mb-4">
                            <h5 class="text-success">Successfully Imported Products ({{ count($results['success']) }})</h5>
                            @if(count($results['success']) > 0)
                            <ul class="list-group">
                                @foreach($results['success'] as $sku)
                                <li class="list-group-item">{{ $sku }}</li>
                                @endforeach
                            </ul>
                            @else
                            <p>No products were successfully imported.</p>
                            @endif
                        </div>

                        <!-- Products Missing Ingredients -->
                        @if(count($results['errors']['missing_ingredients']) > 0)
                        <div class="mb-4">
                            <h5 class="text-danger">Products With No Ingredients Found</h5>
                            <ul class="list-group">
                                @foreach($results['errors']['missing_ingredients'] as $sku)
                                <li class="list-group-item">{{ $sku }}</li>
                                @endforeach
                            </ul>
                        </div>
                        @endif

                        <!-- Products with Invalid Ingredient SKUs -->
                        @if(count($results['errors']['invalid_ingredient_skus']) > 0)
                        <div class="mb-4">
                            <h5 class="text-danger">Products With Invalid Ingredient SKUs</h5>
                            <ul class="list-group">
                                @foreach($results['errors']['invalid_ingredient_skus'] as $sku)
                                <li class="list-group-item">{{ $sku }}</li>
                                @endforeach
                            </ul>
                        </div>
                        @endif

                        <!-- Products with Missing Master Ingredients -->
                        @if(count($results['errors']['missing_master_ingredients']) > 0)
                        <div class="mb-4">
                            <h5 class="text-danger">Products With Missing Master Ingredients</h5>
                            <ul class="list-group">
                                @foreach($results['errors']['missing_master_ingredients'] as $productSku => $ingredientSkus)
                                <li class="list-group-item">
                                    Product SKU: {{ $productSku }}<br>
                                    Missing Ingredients: {{ implode(', ', $ingredientSkus) }}
                                </li>
                                @endforeach
                            </ul>
                        </div>
                        @endif

                        <a href="{{ route('product.import.form') }}" class="btn btn-primary">Back to Import Form</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')

@endpush