<div class="row mt-3" id="gridView">
@foreach($products as $product)
<div class="col-lg-3 col-md-4 col-sm-4 mb-4">
    <div class="card grid-view">
        <!-- Adjust image size for card view -->
        @php
            $sequenceNumber = is_numeric($product->prod_image) ? (int)$product->prod_image : null;
            $product_img = getModuleImage('product', $product->id, $sequenceNumber);
            $category = ($product->product_category) ? $product->product_category->name : "N/A";
            $tooltipHtml = "<h4>{$product->prod_name}</h4>
                            <p>{$product->prod_sku}</p>
                            <p>{$product->description_short}</p> 
                            <p><strong>Status:</strong> {$product->product_status}</p>
                            <p><strong>Ranging:</strong> {$product->product_ranging}</p>
                            <p><strong>Category:</strong> {$category}</p>
                            <p><strong>Tags:</strong> {$product->prod_tags}</p>";
        @endphp
        <div class="product-image-wrap" >
            <img src="{{ $product_img }}" alt="Product Image" class="card-img-top product-thumbnail grid">
        </div>
        <div class="card-body w-100 pb-2">
            <div class="product_name text-center">{{ $product->prod_name }}</div>
            <div class="product_sku text-center">{{ $product->prod_sku }}</div>
            <div class="prod-des-wrap">
                <div class="product_des text-center">{!! $product->description_short !!}</div>
            </div>
            <div class="product_status">
                <div class="finalised">{{ $product->product_status }}</div>
                <div class="ranged">{{ $product->product_ranging }}</div>
            </div>
            <div class="grid-footer">
                <div class="cat-tag">
                    <div class="cat-tag-line">
                        <span class="prod-label">Category:</span> <span class="prod-status">{{ $product->product_category ?  $product->product_category->name : "N/A"}}</span>
                    </div>
                    <div class="cat-tag-line">
                        <span class="prod-label">Tags:</span> <span class="prod-status">{{ $product->prod_tags}}</span>
                    </div>
                </div>
                <div class="icon-wrap d-flex justify-content-end pb-3">
                    <div class="d-flex justify-content-end mt-3">
                        <!-- Favorite Icon -->
                        <button type="button" class="icon-primary-orange me-2 heart" title="Favourite">
                            <span class="material-symbols-outlined" onclick="make_favorite(this)" id="makefavorite_{{$product->id}}" data-favor="{{$product->favorite}}" data-url="{{route('products.favorite',['id'=>$product->id])}}">favorite</span>
                        </button>
                        <!-- 3-Dot Icon Menu for Grid View -->
                        <div class="dropdown">
                            <button class="icon-primary-orange me-0" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                                <span class="material-symbols-outlined">more_vert</span>
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                <li>
                                    <a class="dropdown-item text-primary-dark-mud" href="{{ route('products.spec', $product->id) }}">
                                        <span class="sidenav-normal ms-2 ps-1">Products Specs</span>
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item text-primary-dark-mud" href="{{ route('products.recipe', $product->id) }}">
                                        <span class="sidenav-normal ms-2 ps-1">Recipes</span>
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item text-primary-dark-mud" href="{{ route('products.labelling', $product->id) }}">
                                        <span class="sidenav-normal ms-2 ps-1">Labelling</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endforeach
</div>