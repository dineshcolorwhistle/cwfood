@php
use Illuminate\Support\Facades\DB;
@endphp
<div class="price_card menu-bg mb-3 p-3 rounded-2 box-shadow">
    <div class="card-body px-0 py-2">
        <h5 class="text-primary-orange">Tags</h5>
        <p class="ps-1 text-primary-dark-mud">
            @if($product->prod_tags)
                @php
                $tags = DB::table('product_tags')->whereIn('id', $product->prod_tags)->pluck('name')->toArray();
                echo implode(', ', $tags);
                @endphp
            @else
                No tags available.
            @endif
        </p>
    </div>
</div>
