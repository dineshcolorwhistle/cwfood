<!-- Form for creating or updating a product (step 1) -->
<form id="form_step_1" action="{{ 
        (isset($product) && $product->id) 
            ? route('products.update', ['product' => $product->id]) 
            : route('products.store') 
    }}" method="POST" enctype="multipart/form-data">
    @csrf
    <!-- If product exists, set the method to PUT for updating -->
    @if(isset($product) && $product->id)
    @method('PUT')
    @endif
    <div class="row mt-3">
        <div class="col-lg-5 col-md-5 col-sm-12 col-12 mt-4 mt-sm-0">
            <div class="input-group input-group-dynamic mb-4">
                <label class="form-label">Product Name <span class="text-danger">*</span></label>
                <input type="text" name="prod_name" class="form-control @error('prod_name') is-invalid @enderror" value="{{ old('prod_name', $product->prod_name ?? '') }}" placeholder="Type product name here">
                @error('prod_name')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="input-group input-group-dynamic mb-4 gap-4">
                <label class="form-label">Is this a Sub Recipe?</label>
                <div class="d-flex">
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="sub_receipe" id="sub_reciepe1" value="1" onclick="confirm_popup()" @if(isset($product) && $product->sub_receipe == 1) checked @endif>
                        <label class="form-check-label" for="sub_reciepe1">Yes</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="sub_receipe" id="sub_reciepe2" value="0" onclick="remove_rm_popup()" @if(isset($product) && $product->sub_receipe == 0) checked @endif>
                        <label class="form-check-label" for="sub_reciepe2">No</label>
                    </div>
                </div>
            </div>

            <div class="mt-4 mb-4">
                <label class="form-label mt-2">Product Image</label>
                @php
                $imgCount = 0;
                if($product->prod_image) {
                $imageArray = get_images('product', $product->id);
                $imgCount = sizeof($imageArray);
                }
                @endphp
                <input name="default_image" id="default_image" type="hidden" value="{{ $product->prod_image }}" />
                @if ($imgCount< 9)
                    <div class="dropzone" id="dropzone">
                        <span class="material-symbols-outlined upload-icon">upload</span>
                        <p class="mt-1">Drag & drop files here or <span class="uploan-span">click to upload</span></p>
                        <input type="file" id="fileInput" accept=".png,.jpg,.jpeg" multiple hidden>
                        <span class="mt-1">Accepted file formats: "png, jpg, jpeg"</span>
                    </div>
                @endif

                @if($product->prod_image)
                <ul class="list-group mt-2" id="fileList" style="width: 100%;">
                    @foreach($imageArray as $key => $img)
                    @php
                    $client_id =(!empty($product->client_id)) ? $product->client_id : 1;
                    $workspace =(!empty($product->workspace_id)) ? $product->workspace_id : 1;
                    $img_url = "/assets/{$client_id}/{$workspace}/product/{$product->id}/{$img['image_name']}";
                    $df_image = (int)$product->prod_image - 1;
                    @endphp
                    <li class="list-group-item mb-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                                <img src="{{ asset($img_url) }}" class="thumb me-3">
                                <div>
                                    <strong>{{$img['image_name']}}</strong><br>
                                    <small>{{$img['file_size']}}</small> |
                                    <small>{{$img['file_format']}}</small>
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-4">
                                <div class="form-check m-0">
                                    <input class="form-check-input" type="radio" name="productDefault"  id="product_img_{{ $key }}" {{ $key == $df_image ? 'checked' : '' }}>
                                    <label class="form-check-label" for="product_img_{{ $key }}">Make as Default</label>
                                </div>
                                <button type="button" class="btn p-0"data-id="{{ $img['id'] }}" onclick="remove_images(this)">
                                    <span class="material-symbols-outlined text-danger">delete</span>
                                </button>
                            </div>
                        </div>
                    </li>
                    @endforeach
                </ul>
                @else
                <ul id="fileList" class="list-group my-3"></ul>
                @endif
            </div>


            

            <div class="quill-editor-wrapper">
                <label class="form-label">Description</label>
                <div class="quill-editor" data-input="description_short"></div>
                <input type="hidden" name="description_short" value="{{ old('description_short', $product->description_short ?? '') }}">
                @error('description_short')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="quill-editor-wrapper">
                <label class="form-label">Long Description</label>
                <div class="quill-editor" data-input="description_long"></div>
                <input type="hidden" name="description_long" value="{{ old('description_long', $product->description_long ?? '') }}">
                @error('description_long')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="row">
                <div class="col-lg-6 col-md-6 col-12 col-sm-12 mt-4 mt-sm-0">
                    <div class="input-group input-group-dynamic flex-column mb-4">
                        <label class="form-label">Category</label>
                        <select name="prod_category" id="prod_category" class="form-control-select js-example-basic-single">
                            <option disabled {{ old('prod_category', optional($product)->prod_category) ? '' : 'selected' }}>Select Category</option>
                            @foreach($categories as $category)
                                <option value="{{ $category['id'] }}"
                                    {{ old('prod_category', optional($product)->prod_category) == $category['id'] ? 'selected' : '' }}>
                                    {{ $category['name'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-lg-6 col-md-6 col-12 col-sm-12 mt-4 mt-sm-0">
                    <div class="input-group input-group-dynamic flex-column mb-4">
                        <label class="form-label">Tags</label>
                        <select name="prod_tags[]" class="form-control select2-tags" multiple></select>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-lg-6 col-md-6 col-12 col-sm-12 mt-4 mt-sm-0">
                    <div class="input-group input-group-dynamic flex-column mb-4">
                        <label class="form-label">Status</label>
                        <select name="product_status" id="product_status" class="form-control-select js-example-basic-single">
                            <option disabled {{ old('product_status', optional($product)->product_status) ? '' : 'selected' }}>Select Status</option>
                            @foreach($prod_status as $status)
                                <option value="{{ $status }}"
                                    {{ old('product_status', optional($product)->product_status) == $status ? 'selected' : '' }}>
                                    {{ $status }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-lg-6 col-md-6 col-12 col-sm-12 mt-4 mt-sm-0">
                    <div class="input-group input-group-dynamic flex-column mb-4">
                        <label class="form-label">Ranging</label>
                        <select name="product_ranging" id="product_ranging" class="form-control-select js-example-basic-single">
                            <option disabled {{ old('product_ranging', optional($product)->product_ranging) ? '' : 'selected' }}>Select Ranging</option>
                            @foreach($prod_ranging as $range)
                                <option value="{{ $range }}"
                                    {{ old('product_ranging', optional($product)->product_ranging) == $range ? 'selected' : '' }}>
                                    {{ $range }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <label class="form-label mt-2">Units and Price </label>
            <table class="table table-borderless units-price">
                <tr>
                    <th width="15%"></th>
                    <th class="text-dark-mud-ss text-center" width="15%">Ind Unit</th>
                    <th class="text-dark-mud-ss text-center" width="15%">Sell Unit</th>
                    <th class="text-dark-mud-ss text-center" width="15%">Carton</th>
                    <th class="text-dark-mud-ss text-center" width="15%">Pallet</th>
                </tr>

                <!-- Weight (g) Section -->
                <tr>
                    <td class="align-middle text-dark-mud-ss">Weight - nett (g)</td>
                    <td>
                        <div class="input-group input-group-dynamic table-active-input">
                            <input type="text" name="weight_ind_unit_g" id="weight_ind_unit_g" step="0.1" min="0" class="form-control unit_weight_input ph-blue @error('weight_ind_unit_g') is-invalid @enderror" value="{{ old('weight_ind_unit_g', ($product->weight_ind_unit_g) ? round($product->weight_ind_unit_g,1): 100) }}" placeholder="0">
                            @error('weight_ind_unit_g')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </td>
                    <td>
                        <div class="input-group input-group-dynamic table-input-readonly">
                            <input type="text" name="weight_retail_unit_g" id="weight_retail_unit_g" step="0.1" min="0" class="form-control ph-blue readonly-field" placeholder="" readonly>
                        </div>
                    </td>
                    <td>
                        <div class="input-group input-group-dynamic table-input-readonly">
                            <input type="text" name="weight_carton_g" id="weight_carton_g" step="0.1" min="0" class="form-control ph-blue readonly-field" placeholder="" readonly>
                        </div>
                    </td>
                    <td>
                        <div class="input-group input-group-dynamic table-input-readonly">
                            <input type="text" name="weight_pallet_g" id="weight_pallet_g" step="0.1" min="0" class="form-control ph-blue readonly-field" placeholder="" readonly>
                        </div>
                    </td>
                </tr>

                <!-- Units Section -->
                <tr>
                    <td class="align-middle text-dark-mud-ss">Unit(#)</td>
                    <td>&nbsp;</td>
                    <td>
                        <div class="input-group input-group-dynamic table-active-input">
                            <input type="text" name="count_ind_units_per_retail" id="count_ind_units_per_retail" step="1" min="0" class="form-control unit_weight_input ph-blue @error('count_ind_units_per_retail') is-invalid @enderror" value="{{ old('count_ind_units_per_retail', ($product->count_ind_units_per_retail) ? $product->count_ind_units_per_retail : 1) }}" placeholder="">
                            @error('count_ind_units_per_retail')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </td>
                    <td>
                        <div class="input-group input-group-dynamic table-active-input">
                            <input type="text" name="count_retail_units_per_carton" id="count_retail_units_per_carton" step="1" min="0" class="form-control unit_weight_input ph-blue @error('count_retail_units_per_carton') is-invalid @enderror" value="{{ old('count_retail_units_per_carton', ($product->count_retail_units_per_carton)? $product->count_retail_units_per_carton : 0) }}" placeholder="">
                            @error('count_retail_units_per_carton')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </td>
                    <td>
                        <div class="input-group input-group-dynamic table-active-input">
                            <input type="text" name="count_cartons_per_pallet" id="count_cartons_per_pallet" step="1" min="0" class="form-control unit_weight_input ph-blue @error('count_cartons_per_pallet') is-invalid @enderror" value="{{ old('count_cartons_per_pallet', ($product->count_cartons_per_pallet)? $product->count_cartons_per_pallet: 0 ) }}" placeholder="">
                            @error('count_cartons_per_pallet')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </td>
                </tr>
            </table>
        </div>
        
        <div class="col-lg-5 col-md-5 col-sm-12 col-12 mt-4 mt-sm-0">
            <div class="input-group input-group-dynamic mb-4">
                <label class="form-label">SKU <span class="text-danger">*</span></label>
                <input type="text" name="prod_sku" class="form-control @error('prod_sku') is-invalid @enderror" value="{{ old('prod_sku', $product->prod_sku ?? '') }}" placeholder="Type product SKU here">
                @error('prod_sku')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            @php
                $rm_sku = "";
                $style = "none";
                if(isset($product) && $product->sub_receipe == 1){
                    $recipe = get_subreceipe_details($product->sub_receipe_id);
                    if(count($recipe) > 0){
                        $rm_sku = $recipe[0];  
                    }else{
                        $rm_sku = "RM_".$product->prod_sku;
                    }
                    $style = "block";
                }
            @endphp
            <div class="input-group input-group-dynamic mb-4" id="RM_SKU" style="display:{{$style}}">  
                <label class="form-label">Raw Material SKU</label>
                <input name="raw_material_sku" value="{{$rm_sku}}" class="form-control" placeholder="Raw material SKU" readonly>
            </div>
            <div class="input-group input-group-dynamic mb-4">
                <label class="form-label">GTIN - 13 (Barcode)</label>
                <input name="barcode_gs1" value="{{ old('barcode_gs1', $product->barcode_gs1 ?? '') }}" class="form-control" placeholder="Type GTIN - 13 (Barcode) here">
            </div>
            <div class="input-group input-group-dynamic mb-4">
                <label class="form-label">GTIN - 14 (Carton Code)</label>
                <input name="barcode_gtin14" value="{{ old('barcode_gtin14', $product->barcode_gtin14 ?? '') }}" class="form-control" placeholder="Type GTIN - 14 (Carton Code) here">
            </div>
        </div>
        <div class="col-lg-2 col-md-2 col-sm-12 col-12 mt-4 mt-sm-0">
            <div class="button-row d-flex flex-column gap-2">
                <button class="btn btn-secondary-blue mb-0 js-btn-save" type="button" title="Save"><i class="material-symbols-outlined me-1" style="font-size: 18px;">save</i>Save</button>
                <button class="btn btn-secondary-blue mb-0 js-btn-next" type="button" title="Next"><i class="material-symbols-outlined me-1" style="font-size: 18px;">arrow_forward</i>Next</button>
            </div>
            <div class="mt-4">
                <x-product-gridcard :product="$product" />
                <x-recipe-details :product="$product" />
                <x-tags-card :product="$product" />
                <x-product-weight :product="$product" />
            </div>
        </div>
    </div>
</form>