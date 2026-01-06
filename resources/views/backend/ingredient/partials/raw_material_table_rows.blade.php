@foreach($lists as $list)
                    <tr class="search_table_row">
                        <td class="text-primary-dark-mud">
                            <div class="form-check-temp p-1">
                                <input class="form-check-input raw_check" data-rawmaterial="{{$list->id}}" type="checkbox" id="raw_{{$list->id}}">
                            </div>
                        </td>
                        <td class="align-middle">
                            <!-- Product image with thumbnail styling -->
                            @php
                            $imgUrl = '';
                            if($list->ing_image){
                            $imgUrl = get_default_image_url('raw_material',$list->ing_image,$list->id);
                            }else{
                            $imgUrl = env('APP_URL')."/assets/img/ing_default.png";
                            }
                            @endphp
                            <img src="{{ $imgUrl }}" alt="Product Image" class="product-thumbnail list">
                        </td>
                        <td class="align-middle">
                            <div class="product_name text-primary-dark-mud mb-1">{{ $list->name_by_kitchen }}</div>
                            <div class="product_sku text-primary-dark-mud-sm">{{ $list->ing_sku }}</div>
                        </td>
                        <td class="align-middle">
                            <div class="product_des text-primary-dark-mud">{!!truncateDescription($list->raw_material_description , 120)!!}</div>
                        </td>
                        <td class="align-middle">
                            <div class="product_des text-primary-dark-mud">@if($list->category) {{ get_category_name($list->category) }} @endif</div>
                        </td>
                        <td class="align-middle">
                            <div class="product_des text-primary-dark-mud">@if($list->sub_category) {{ get_sub_category_name($list->sub_category) }} @endif</div>
                        </td>
                        <td class="align-middle">
                            <div class="product_des text-primary-dark-mud">@if($list->status){{ $list->status }}@endif</div>
                        </td>
                        <td class="align-middle">
                            <div class="product_des text-primary-dark-mud">@if($list->is_active){{ active_name($list->is_active) }}@endif</div>
                        </td>

                        <td class="align-middle">{{ $list->gtin }}</td>
                        <td class="align-middle">{{ $list->supplier_code }}</td>
                        <td class="text-primary-dark-mud">{{ $list->supplier ? $list->supplier->company_name : '-' }}</td>
                        <td class="align-middle">{{ $list->ingredients_list_supplier }}</td>
                        <td class="align-middle">{{ $list->allergens }}</td>
                        <td class="align-middle text-end">{{ $list->price_per_item }}</td>
                        <td class="align-middle text-end">{{ $list->units_per_item }}</td>
                        <td class="align-middle">{{ $list->ingredient_units }}</td>
                        <td class="align-middle">{{ $list->purchase_units }}</td>
                        <td class="align-middle text-end">{{ $list->price_per_kg_l }}</td>
                        <td class="align-middle">{{$list->country_of_origin}}</td>
                        <td class="align-middle">{{ $list->australian_percent }}</td>
                        <td class="align-middle text-end">{{ $list->specific_gravity }}</td>
                        <td class="align-middle text-end">{{ $list->energy_kj }}</td>
                        <td class="align-middle text-end">{{ $list->protein_g }}</td>
                        <td class="align-middle text-end">{{ $list->fat_total_g }}</td>
                        <td class="align-middle text-end">{{ $list->fat_saturated_g }}</td>
                        <td class="align-middle text-end">{{ $list->carbohydrate_g }}</td>
                        <td class="align-middle text-end">{{ $list->sugars_g }}</td>
                        <td class="align-middle text-end">{{ $list->sodium_mg }}</td>
                        <td class="align-middle">{{ $list->shelf_life }}</td>
                        <td class="align-middle">@if($list->supplier_spec_url) <a href="{{ $list->supplier_spec_url }}" target="_blank">URL</a>@endif</td>
                        <td class="align-middle icon-section">

                            @if($user_role == 4)
                                @if($permission['status'] == true && $permission['page']['Resources - Raw Material'] == true)
                                <div class="d-flex justify-content-end">
                                    <button type="button" class="icon-primary-orange me-2" title="Favorite">
                                        <span class="material-symbols-outlined" onclick="make_favorite(this)" id="makefavorite_{{$list->id}}" data-favor="{{$list->favorite}}" data-url="{{route('favorite.raw-materials',['id'=>$list->id])}}">favorite</span>
                                    </button>
                                    <!-- 3-Dot Icon for Menu -->
                                    <div class="dropdown d-inline">
                                        <button class="icon-primary-orange me-0" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                                            <span class="material-symbols-outlined">more_vert</span>
                                        </button>
                                        <ul class="dropdown-menu prod" aria-labelledby="dropdownMenuButton">
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
                            <!-- Edit and Delete Actions (inline) -->
                            <div class="d-flex justify-content-end">
                                <button type="button" class="icon-primary-orange me-2" title="Favorite">
                                    <span class="material-symbols-outlined" onclick="make_favorite(this)" id="makefavorite_{{$list->id}}" data-favor="{{$list->favorite}}" data-url="{{route('favorite.raw-materials',['id'=>$list->id])}}">favorite</span>
                                </button>
                                <!-- 3-Dot Icon for Menu -->
                                <div class="dropdown d-inline">
                                    <button class="icon-primary-orange me-0" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                                        <span class="material-symbols-outlined">more_vert</span>
                                    </button>
                                    <ul class="dropdown-menu prod" aria-labelledby="dropdownMenuButton">
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
                        </td>
                    </tr>
                @endforeach