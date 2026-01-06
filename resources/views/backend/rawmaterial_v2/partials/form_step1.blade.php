<div class="row mt-3">
        <div class="col-lg-5 col-md-5 col-sm-12 col-12 mt-4 mt-sm-0">
            <div class="input-group input-group-dynamic mb-4">
                <label class="form-label">Ingredient Name <span class="text-danger">*</span></label>
                <input type="text" name="ing_name" id="ing_name" class="form-control" 
                       value="{{ $hasIngredient ? ($details['name_by_kitchen'] ?? '') : '' }}" 
                       placeholder="Type Ingredient name here">
            </div>
            
            @php
                $imgCount = 0;
                $imageArray = [];
                if($hasIngredient && isset($details['ing_image']) && $details['ing_image']) {
                    $imageArray = get_images('raw_material', $details['id']);
                    $imgCount = sizeof($imageArray);
                }
            @endphp
            <input name="default_image" id="default_image" type="hidden" 
                   value="{{ $hasIngredient ? ($details['ing_image'] ?? '') : '' }}" />
            <div class="input-group input-group-dynamic flex-column mb-4">
                <label class="form-label">Ingredient Image</label>
                @if($imgCount < 10)
                <div class="dropzone" id="dropzone">
                    <span class="material-symbols-outlined upload-icon">upload</span>
                    <p class="mt-1">Drag & drop files here or <span class="uploan-span">click to upload</span></p>
                    <input type="file" id="fileInput" accept=".png,.jpg,.jpeg" multiple hidden>
                    <span class="mt-1">Accepted file formats: "png, jpg, jpeg"</span>
                </div>
                @endif
                @if($hasIngredient && isset($details['ing_image']) && $details['ing_image'] && $imgCount > 0)
                    <ul class="list-group mt-2" id="fileList" style="width: 100%;">
                        @foreach($imageArray as $key => $img)
                            @php
                                $img_url = "/assets/{$details['client_id']}/{$details['workspace_id']}/raw_material/{$details['id']}/{$img['image_name']}";
                                $df_image = (int)$details['ing_image'] - 1;
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
                                            <input class="form-check-input" type="radio" name="productDefault" id="ingredient_img_{{ $key }}" value="{{ $key }}" {{ $key == $df_image ? 'checked' : '' }}>
                                            <label class="form-check-label" for="ingredient_img_{{ $key }}">Make as Default</label>
                                        </div>
                                        <button type="button" class="btn p-0" data-id="{{ $img['id'] }}" onclick="remove_images(this)">
                                            <span class="material-symbols-outlined text-danger">delete</span>
                                        </button>
                                    </div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <ul class="list-group mt-2" id="fileList" style="width: 100%;"></ul>
                @endif
            </div>

            <div class="input-group input-group-dynamic mb-4">
                <label class="form-label">Supplier Spec (URL)</label>
                <input type="text" name="supplier_spec_url" class="form-control" 
                       value="{{ $hasIngredient ? ($details['supplier_spec_url'] ?? '') : '' }}" 
                       placeholder="Insert shareable link (URL) to supplier spec sheet">
            </div>

            <div class="input-group input-group-dynamic mb-4">
                <label class="form-label">Ingredient Description</label>
                <input type="text" name="ing_description" class="form-control" 
                       value="{{ $hasIngredient ? ($details['raw_material_description'] ?? '') : '' }}" 
                       placeholder="Type Ingredient description here">
            </div>
            
            <div class="row">
                <div class="col-lg-6 col-md-6 col-12 col-sm-12 mt-4 mt-sm-0">
                    <div class="input-group input-group-dynamic flex-column mb-4">
                        <label class="form-label">Category</label>
                        <select name="ing_category" id="ing_category" class="form-control-select js-example-basic-single">
                            <option disabled selected>Select Category</option>
                            @foreach($categories as $cat)
                            <option value="{{$cat['id']}}" 
                                    {{ ($hasIngredient && isset($details['category']) && $details['category'] == $cat['id']) ? 'selected' : '' }}>
                                {{$cat['name']}}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-lg-6 col-md-6 col-12 col-sm-12 mt-4 mt-sm-0">
                    <div class="input-group input-group-dynamic flex-column mb-4">
                        <label class="form-label">Tags</label>
                        <select name="ing_tags[]" id="ing_tags" class="form-control-select select2-tags" multiple>
                            @foreach($Tags as $tag)
                            <option value="{{$tag['id']}}"
                                    {{ ($hasIngredient && isset($details['ing_tags']) && in_array($tag['id'], json_decode($details['ing_tags'] ?? '[]', true))) ? 'selected' : '' }}>
                                {{$tag['name']}}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-lg-12 col-md-12 col-12 col-sm-12 mt-4 mt-sm-0">
                    <div class="input-group input-group-dynamic mb-4">
                        <label class="form-label">Name Provided by Supplier</label>
                        <input type="text" name="ing_supplier_name" class="form-control" 
                               value="{{ $hasIngredient ? ($details['name_by_supplier'] ?? '') : '' }}" 
                               placeholder="Type Ingredient Supplier name here">
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-lg-5 col-md-5 col-12 col-sm-12 mt-4 mt-sm-0">
                    <div class="input-group input-group-dynamic flex-column mb-4">
                        <label class="form-label">Supplier Name</label>
                        <select name="ing_supplier" id="ing_supplier" class="form-control-select js-example-basic-single">
                            <option disabled selected>Select Supplier</option>
                            @foreach($supplier as $sup)
                            <option value="{{$sup['id']}}"
                                    {{ ($hasIngredient && isset($details['supplier_name']) && $details['supplier_name'] == $sup['id']) ? 'selected' : '' }}>
                                {{$sup['company_name']}}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-lg-4 col-md-4 col-12 col-sm-12 mt-4 mt-sm-0">
                    <div class="input-group input-group-dynamic mb-4">
                        <label class="form-label">Supplier Code</label>
                        <input type="text" name="ing_supplier_code" class="form-control" 
                               value="{{ $hasIngredient ? ($details['supplier_code'] ?? '') : '' }}" 
                               placeholder="Type Supplier code">
                    </div>
                </div>
                <div class="col-lg-3 col-md-3 col-12 col-sm-12 mt-4 mt-sm-0">
                    <div class="input-group input-group-dynamic mb-4">
                        <label class="form-label">GTIN</label>
                        <input type="text" name="ing_gtin" class="form-control" 
                               value="{{ $hasIngredient ? ($details['gtin'] ?? '') : '' }}" 
                               placeholder="Type GTIN here">
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-lg-6 col-md-6 col-12 col-sm-12 mt-4 mt-sm-0">
                    <div class="input-group input-group-dynamic flex-column mb-4">
                        <label class="form-label">Status</label>
                        <select name="raw_material_status" id="raw_material_status" class="form-control-select js-example-basic-single">
                            <option disabled selected>Select Status</option>
                            @foreach($statusArray as $status)
                            <option value="{{$status}}"
                                    {{ ($hasIngredient && isset($details['raw_material_status']) && $details['raw_material_status'] == $status) ? 'selected' : '' }}>
                                {{$status}}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-lg-6 col-md-6 col-12 col-sm-12 mt-4 mt-sm-0">
                    <div class="input-group input-group-dynamic flex-column mb-4">
                        <label class="form-label">Ranging</label>
                        <select name="raw_material_ranging" id="raw_material_ranging" class="form-control-select js-example-basic-single">
                            <option disabled selected>Select Range</option>
                            @foreach($rangeArray as $range)
                            <option value="{{$range}}"
                                    {{ ($hasIngredient && isset($details['raw_material_ranging']) && $details['raw_material_ranging'] == $range) ? 'selected' : '' }}>
                                {{$range}}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-5 col-md-5 col-sm-12 col-12 mt-4 mt-sm-0">
            <div class="input-group input-group-dynamic mb-4">
                <label class="form-label">Ingredient SKU <span class="text-danger">*</span></label>
                <input type="text" name="ing_sku" id="ing_sku" class="form-control" 
                       value="{{ $hasIngredient ? ($details['ing_sku'] ?? '') : '' }}" 
                       placeholder="Type Ingredient sku here">
            </div>
        </div>
        
        <div class="col-lg-2 col-md-2 col-sm-12 col-12 mt-sm-0">
            <div class="mt-4">
                <x-raw-material-details :ingredient="$details" />
            </div>
        </div>
    </div>

