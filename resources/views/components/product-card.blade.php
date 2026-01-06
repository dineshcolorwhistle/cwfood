<div class="card nutrition_card mb-3 p-3 rounded-2 box-shadow">
    <div class="card-body mobile-res row px-0 py-2">
        @php
        $sequenceNumber = is_numeric($product->prod_image) ? (int)$product->prod_image : null;
        $product_img = getModuleImage('product', $product->id, $sequenceNumber);
        @endphp
        <div class="col-lg-4 col-md-4 col-sm-12 text-center">
            <img src="{{ $product_img }}" alt="Recipe Image" class="product_image img-fluid rounded">
        </div>
        <div class="col-lg-8 col-md-8 col-sm-12 title_sku_container">
            <h2 class="prod_title text-primary-orange">{{ $product->prod_name }}</h2>
            <p class="prod_sku text-primary-dark-snow mt-2">{{ $product->prod_sku }}</p>
            @if($product->description_short && strip_tags($product->description_short)!='')
            <p class="text-primary-dark-mud">{!! format_content($product->description_short) !!}</p>
            @endif
        </div>
    </div>
</div>
