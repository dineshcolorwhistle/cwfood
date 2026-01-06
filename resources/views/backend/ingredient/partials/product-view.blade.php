<div class="row" id="gridView">
    @foreach($lists as $list)
    <div class="col-md-3 mb-4">
        <div class="card grid-view">
            <!-- Adjust image size for card view -->
            @php
            $imgUrl = '';
            if($list->ing_image){
            $imgUrl = get_default_image_url('raw_material',$list->ing_image,$list->id);
            }else{
            $imgUrl = env('APP_URL')."/assets/img/ing_default.png";
            }

            $category = ($list->raw_category) ? $list->raw_category->name : "N/A";
            $tooltipHtml = "<h4>{$list->name_by_kitchen}</h4>
                            <p>{$list->ing_sku}</p>
                            <p>{$list->raw_material_description}</p> 
                            <p><strong>Status:</strong> {$list->raw_material_status}</p>
                            <p><strong>Ranging:</strong> {$list->raw_material_ranging}</p>
                            <p><strong>Category:</strong> {$category}</p>
                            <p><strong>Tags:</strong> {$list->ing_tags}</p>";
            @endphp
            <div class="product-image-wrap" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-custom-class="custom-tooltip" data-bs-html="true" data-bs-title="<?= htmlspecialchars($tooltipHtml) ?>">
                <img src="{{ $imgUrl }}" alt="Product Image tdgf" class="card-img-top product-thumbnail grid">
            </div>
            <div class="card-body w-100 pb-2">
                <div class="product_name text-center">
                    {{ $list->name_by_kitchen }}
                </div>
                <div class="product_sku text-center">{{ $list->ing_sku}}</div>
                <div class="prod-des-wrap">
                    <div class="product_des text-center">{{ strip_tags($list->raw_material_description) }}</div>
                </div>
                <div class="product_status">
                    <div class="finalised">{{ $list->raw_material_status }}</div>
                    <div class="ranged">{{ $list->raw_material_ranging }}</div>
                </div>
                <div class="grid-footer">
                    <div class="cat-tag">
                        <div class="cat-tag-line">
                            <span class="prod-label">Category:</span> <span class="prod-status">{{ $list->raw_category ?  $list->raw_category->name : ""}}</span>
                        </div>
                        <div class="cat-tag-line">
                            <span class="prod-label">Tags:</span> <span class="prod-status">{{ $list->ing_tags}}</span>
                        </div>
                    </div>
                    <div class="icon-wrap d-flex justify-content-end pb-3">
                    <!-- Edit and Delete Actions (inline) -->
                        @if($user_role == 4)
                        @if($permission['status'] == true && $permission['page']['Resources - Raw Material'] == true)
                        <div class="d-flex justify-content-end mt-3">
                            <button type="button" class="icon-primary-orange me-2 heart" title="Favorite">
                                <span class="material-symbols-outlined" onclick="make_favorite(this)" id="make_favorite" data-favor="0" data-url="{{route('favorite.raw-materials',['id'=>$list->id])}}">favorite</span>
                            </button>
                            <!-- 3-Dot Icon Menu for Grid View -->
                            <div class="dropdown">
                                <button class="icon-primary-orange me-0" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                                    <span class="material-symbols-outlined">more_vert</span>
                                </button>
                                <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                    <li>
                                        <a href="{{ route('edit.raw-materials', ['id'=>$list->id]) }}" class="dropdown-item text-primary-dark-mud me-2">
                                            <span class="sidenav-normal ms-2 ps-1">Edit</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="javascript:void(0)" data-url="{{ route('destroy.raw-materials', ['id'=>$list->id]) }}" onclick="commonDelete(this)" class="dropdown-item text-primary-dark-mud me-2">
                                            <span class="sidenav-normal ms-2 ps-1">Delete</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="javascript:void(0)" data-type="ingr" data-url="{{ route('duplicate.raw-materials', ['id'=>$list->id]) }}" onclick="make_duplicate(this)" class="dropdown-item text-primary-dark-mud me-2">
                                            <span class="sidenav-normal ms-2 ps-1">Duplicate</span>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        @endif
                        @else
                        <div class="d-flex justify-content-end mt-3">
                            <button type="button" class="icon-primary-orange me-2 heart" title="Favorite">
                                <span class="material-symbols-outlined" onclick="make_favorite(this)" id="make_favorite" data-favor="0" data-url="{{route('favorite.raw-materials',['id'=>$list->id])}}">favorite</span>
                            </button>
                            <!-- 3-Dot Icon Menu for Grid View -->
                            <div class="dropdown">
                                <button class="icon-primary-orange me-0" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                                    <span class="material-symbols-outlined">more_vert</span>
                                </button>
                                <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                    <li>
                                        <a href="{{ route('edit.raw-materials', ['id'=>$list->id]) }}" class="dropdown-item text-primary-dark-mud me-2">
                                            <span class="sidenav-normal ms-2 ps-1">Edit</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="javascript:void(0)" data-url="{{ route('destroy.raw-materials', ['id'=>$list->id]) }}" onclick="commonDelete(this)" class="dropdown-item text-primary-dark-mud me-2">
                                            <span class="sidenav-normal ms-2 ps-1">Delete</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="javascript:void(0)" data-type="ingr" data-url="{{ route('duplicate.raw-materials', ['id'=>$list->id]) }}" onclick="make_duplicate(this)" class="dropdown-item text-primary-dark-mud me-2">
                                            <span class="sidenav-normal ms-2 ps-1">Duplicate</span>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>