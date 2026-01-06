@php
    $imgCount = 0;
    $imageArray = [];

    if ($rawMaterial['ing_image']) {
        $imageArray = get_images('raw_material', $rawMaterial['id']);
        $imgCount = sizeof($imageArray);
    }

    // Set default image or fallback image
    $img_url = $imgCount > 0 
        ? "/assets/{$rawMaterial['client_id']}/{$rawMaterial['workspace_id']}/raw_material/{$rawMaterial['id']}/{$imageArray[0]['image_name']}" 
        : "/assets/img/ing_default.png";

    $df_image = (int) $rawMaterial['ing_image'] - 1;
@endphp

<div class="card menu-bg grid-detail-view">       
    <div class="product-image-wrap">
        <img src="{{ env('APP_URL') . $img_url }}" alt="{{ $rawMaterial['name_by_kitchen']}}" class="card-img-top product-thumbnail grid">
    </div>
    <div class="card-body right pb-2">
        <h2 class="product_name text-primary-blue-lgs text-center">{{ $rawMaterial['name_by_kitchen'] ?? '-' }}</h2>
        <p class="product_sku text-dark-mud-sm text-center">{{ $rawMaterial['ing_sku'] ?? '-' }}</p>
        @if($rawMaterial['raw_material_description']) 
            <p class="product_description text-primary-dark-mud text-center">{{ $rawMaterial['raw_material_description'] }}</p>
        @endif
        <h5 id="raw-material-head" class="text-primary-orange text-center"></h5>
        <p id="updated-price-unit" class="ps-1 text-primary-dark-mud text-center"></p>
    </div>
</div>
