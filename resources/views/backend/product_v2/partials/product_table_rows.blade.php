@foreach($products as $product)
                    <tr class="search_table_row">
                        <td class="text-primary-dark-mud">
                            <div class="form-check-temp p-1">
                                <input class="form-check-input pro_check" data-product="{{$product->id}}" type="checkbox" id="product_{{$product->id}}">
                            </div>
                        </td>
                        <td class="align-middle">
                            <!-- Product image with thumbnail styling -->
                            @php
                                $sequenceNumber = is_numeric($product->prod_image) ? (int)$product->prod_image : null;
                                $product_img = getModuleImage('product', $product->id, $sequenceNumber);
                                $tooltip_content = get_product_tooltip($product);
                            @endphp
                            <img src="{{ $product_img  }}" alt="Product Image" class="product-thumbnail list">
                        </td>
                        <td class="align-middle">
                            <div class="product_name text-primary-dark-mud mb-1">{!!truncateDescription($product->prod_name , 50)!!}</div>
                            <div class="product_sku text-primary-dark-mud-sm">{{ $product->prod_sku }}</div>
                        </td>
                        <td class="align-middle">
                            <div class="product_des text-primary-dark-mud">{!!truncateDescription($product->description_short , 120)!!}</div>
                        </td>
                        <td class="align-middle">{{$product->barcode_gs1}}</td>
                        <td class="align-middle">
                            <div class="product_des text-primary-dark-mud">{!!truncateDescription($product->description_long , 120)!!}</div>
                        </td>
                        <td class="align-middle">@if($product->prod_tags){{ implode(',',$product->prod_tags) }}@endif</td>
                        <td class="align-middle text-end">{{$product->contingency}}</td>
                        <td class="align-middle text-end">{{$product->weight_ind_unit_g}}</td>
                        <td class="align-middle text-end"> {{$product->weight_retail_unit_g}}</td>
                        <td class="align-middle text-end">{{$product->weight_carton_g}}</td>
                        <td class="align-middle text-end">{{$product->weight_pallet_g}}</td>
                        <td class="align-middle text-end">{{$product->count_ind_units_per_retail}}</td>
                        <td class="align-middle text-end">{{$product->count_retail_units_per_carton}}</td>
                        <td class="align-middle text-end">{{$product->count_cartons_per_pallet}}</td>
                        <td class="align-middle text-end">{{$product->price_ind_unit}}</td>
                        <td class="align-middle text-end">{{$product->price_retail_unit}}</td>
                        <td class="align-middle text-end">{{$product->price_carton}}</td>
                        <td class="align-middle text-end">{{$product->price_pallet}}</td>
                        <td class="align-middle">{{$product->recipe_method}}</td>
                        <td class="align-middle">{{$product->recipe_notes}}</td>
                        <td class="align-middle text-end">{{$product->recipe_oven_time}}</td>
                        <td class="align-middle text-end">{{$product->recipe_oven_temp}}</td>
                        <td class="align-middle">{{$product->recipe_oven_temp_unit}}</td>
                        <td class="align-middle text-end">{{$product->batch_baking_loss_percent}}</td>
                        <td class="align-middle text-end">{{$product->serv_per_package}}</td>
                        <td class="align-middle text-end">{{$product->serv_size_g}}</td>

                        <td class="align-middle icon-section">

                            <!-- Edit and Delete Actions (inline) -->
                            <div class="d-flex justify-content-end">

                                <!-- Alert Icon -->
                                @if($tooltip_content != "")
                                <button type="button" class="icon-primary-orange me-2" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-custom-class="custom-tooltip"data-bs-title="{{$tooltip_content}}" title="Warning">
                                    <span class="material-symbols-outlined">warning</span>
                                </button>
                                @endif

                                <!-- Favorite Icon -->
                                @if($user_role == 4)
                                    @if($permission['status'] == true && $permission['page']['Products'] == true)
                                        <button type="button" class="icon-primary-orange me-2" title="Favorite">
                                            <span class="material-symbols-outlined" onclick="make_favorite(this)" id="makefavorite_{{$product->id}}" data-favor="{{$product->favorite}}" data-url="{{route('products.favorite',['id'=>$product->id])}}">favorite</span>
                                        </button>
                                    @endif
                                @else
                                <button type="button" class="icon-primary-orange me-2" title="Favorite">
                                    <span class="material-symbols-outlined" onclick="make_favorite(this)" id="makefavorite_{{$product->id}}" data-favor="{{$product->favorite}}" data-url="{{route('products.favorite',['id'=>$product->id])}}">favorite</span>
                                </button>
                                @endif
                                

                                <!-- 3-Dot Icon for Menu -->
                                <div class="btn-group dropup d-inline">
                                    <button class="icon-primary-orange me-0" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                                        <span class="material-symbols-outlined">more_vert</span>
                                    </button>
                                    <ul class="dropdown-menu prod" aria-labelledby="dropdownMenuButton">
                                        @if($user_role == 4)
                                            @if($permission['status'] == true && $permission['page']['Products'] == true)
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
                                                <li>
                                                    <a href="javascript:void(0)" data-type="product" data-url="{{ route('products.duplicate', ['id'=>$product->id]) }}" onclick="make_duplicate(this)" class="dropdown-item text-primary-dark-mud me-2">
                                                        <span class="sidenav-normal ms-2 ps-1">Duplicate</span>
                                                    </a>
                                                </li>
                                                <hr>
                                            @endif
                                        @else
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
                                        <li>
                                            <a href="javascript:void(0)" data-type="product" data-url="{{ route('products.duplicate', ['id'=>$product->id]) }}" onclick="make_duplicate(this)" class="dropdown-item text-primary-dark-mud me-2">
                                                <span class="sidenav-normal ms-2 ps-1">Duplicate</span>
                                            </a>
                                        </li>
                                        <hr>
                                        @endif
                                        
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
                                        <li>
                                            <a class="dropdown-item text-primary-dark-mud" href="{{ route('products.costing', $product->id) }}">
                                                <span class="sidenav-normal ms-2 ps-1">Costing</span>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @endforeach