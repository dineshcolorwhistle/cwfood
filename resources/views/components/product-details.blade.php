<div class="card nutrition_card mb-3 p-3 rounded-2 box-shadow">
    <!-- Product Details -->
    <div class="prod_desc_card">
        <div class="card-body px-0 py-2">
            <h5 class="text-primary-orange">Product information</h5>
        </div>
    </div>

   
    <!-- Long Description -->
    @if($product->description_long && strip_tags($product->description_long) != '')
        <div class="prod_desc_card">
            <div class="card-body px-0 py-2">
                <h5 class="text-primary-blue-ss">Long Form</h5>
                <p class="text-primary-dark-mud">{!! format_content($product->description_long) !!}</p>
            </div>
        </div>
    @endif

    <!-- GS1 Barcode -->
    @if($product->barcode_gs1 && strip_tags($product->barcode_gs1) != '')
        <div class="prod_barcode_card">
            <div class="card-body px-0 py-2">
                <h5 class="text-primary-blue-ss">GS1 Barcode</h5>
                <p class="text-primary-dark-mud">{{ $product->barcode_gs1 }}</p>
            </div>
        </div>
    @endif

    <!-- Product Labels -->

    @if($product->prodLabels)
        <div class="prod_barcode_card">
            <div class="card-body px-0 py-2">
                <h5 class="text-primary-blue-ss">Australia %</h5>
                <p class="text-primary-dark-mud">@if($product->australian_percent){{number_format($product->australian_percent, 2)}}% @endif</p>
            </div>
            <div class="card-body px-0 py-2">
                <h4 style="font-size:15px;">As supplied (unopened pack or bulk)</h4>
                <h5 class="text-primary-blue-ss">Shelf Life</h5>
                <p class="text-primary-dark-mud">@if($product->prodLabels->rm_supplied_shelf_life_num) {{$product->prodLabels->rm_supplied_shelf_life_num}} @else 0 @endif {{$product->prodLabels->rm_supplied_shelf_life_units}}</p>
            </div>
            <div class="card-body px-0 py-2">
                <h5 class="text-primary-blue-ss">Temperature controlled during storage</h5>
                <p class="text-primary-dark-mud">@if($product->prodLabels) @if($product->prodLabels->rm_suppied_temp_control_storage_num == "Yes"){{$product->prodLabels->rm_suppied_temp_control_storage_degrees}} @else N/A @endif @endif</p>
            </div>
            <div class="card-body px-0 py-2">
                <h5 class="text-primary-blue-ss">Temperature controlled during transport</h5>
                <p class="text-primary-dark-mud">@if($product->prodLabels) @if($product->prodLabels->rm_supplied_temp_control_transport_yn == "Yes"){{$product->prodLabels->rm_supplied_temp_control_transport_degrees}} @else N/A @endif @endif</p>
            </div>
            <div class="card-body px-0 py-2">
                <h4 style="font-size:15px;">Product - Once in Use (resealable pack or bulk container)</h4>
                <h5 class="text-primary-blue-ss">Shelf Life</h5>
                <p class="text-primary-dark-mud">@if($product->prodLabels->rm_inuse_shelf_life_num) {{$product->prodLabels->rm_inuse_shelf_life_num}} @else 0 @endif {{$product->prodLabels->rm_inuse_shelf_life_units}}</p>
            </div>
            <div class="card-body px-0 py-2">
                <h5 class="text-primary-blue-ss">Temperature controlled during storage</h5>
                <p class="text-primary-dark-mud">@if($product->prodLabels) @if($product->prodLabels->rm_inuse_temp_control_storage_num == "Yes") {{$product->prodLabels->rm_inuse_temp_control_storage_degrees}} @else N/A @endif @endif</p>
            </div>
            
            <div class="card-body px-0 py-2">
                <h4 style="font-size:15px;">Other</h4>
                <h5 class="text-primary-blue-ss">Specifiy any other storage requirements</h5>
                <p class="text-primary-dark-mud">{{$product->prodLabels->rm_storage_requirement}}</p>
            </div>
            <div class="card-body px-0 py-2">
                <h5 class="text-primary-blue-ss">Intended Use</h5>
                <p class="text-primary-dark-mud">{{$product->prodLabels->rm_indended_use}}</p>
            </div>
            <div class="card-body px-0 py-2">
                <h5 class="text-primary-blue-ss">Specifiy type of date mark to be used</h5>
                <p class="text-primary-dark-mud">{{$product->prodLabels->rm_date_mark}}</p>
            </div>
        </div>
    @endif


    

    <!-- Shelf Life and Storage -->
    <!-- <div class="recipe_card">
        <div class="card-body px-0 py-2">
            <h5 class="text-primary-blue-ss">Shelf Life and Storage</h5>
            <p class="text-primary-dark-mud">
                Shelf life: {{ $product->supplied_shelf_life_num }} {{ $product->supplied_shelf_life_units }}
                @if($product->supplied_temp_control_storage_yn === 'Yes')
                    Once opened, store in an airtight container in a cool, dry place (
                    {{ $product->supplied_temp_control_storage_degrees ?? '= 20�C' }}).
                @endif
                <br>
                Intended use: {{ $product->ing_intended_use ?? 'General consumption' }}<br>
                Date mark to be used: {{ $product->ing_date_mark ?? 'Best Before' }}
            </p>
        </div>
    </div> -->

    <!-- Australian Made -->
    @if(!empty($product->country_of_origin))
        <div class="prod_barcode_card">
            <div class="card-body px-0 py-2">
                <h5 class="text-primary-blue-ss">Australian Made</h5>
                <p class="text-primary-dark-mud">Made in Australia from <span>at least {{ $product->country_of_origin }}</span></p>
            </div>
        </div>
    @endif

    <!-- Packaging -->
    @if($product->pack_packaging1 && strip_tags($product->pack_packaging1) != '')
        <div class="recipe_card">
            <div class="card-body px-0 py-2">
                <h5 class="text-primary-blue-ss">Packaging</h5>
                <p class="text-primary-dark-mud">{{ $product->pack_packaging1 }}</p>
            </div>
        </div>
    @endif




</div>