@if(isset($product) && $product->id)
<div class="card menu-bg grid-detail-view">
    <div class="product-image-wrap">
        @php
        $sequenceNumber = is_numeric($product->prod_image) ? (int)$product->prod_image : null;
        $product_img = getModuleImage('product', $product->id, $sequenceNumber);
        @endphp
        <img src="{{ $product_img }}" alt="Product Image" class="card-img-top product-thumbnail grid">
    </div>
    <div class="card-body right pb-2">
        <div class="product_name text-primary-blue-lgs text-center">{!!truncateDescription($product->prod_name , 40)!!}</div>
        <div class="product_sku text-dark-mud-sm text-center">{{ $product->prod_sku }}</div>
        <div class="product_description text-primary-dark-mud text-center">{!!truncateDescription($product->description_short , 120)!!}</div>
    </div>
</div>
@endif