@extends('backend.master', [
'pageTitle' => 'Products',
'activeMenu' => [
'item' => 'Products',
'subitem' => 'Products',
'additional' => '',
],
'breadcrumbItems' => [
['label' => 'Reports', 'url' => '#'],
['label' => 'Products']
],])
@section('content')
<div class="container product-list-container my-4">
    <div class="table-responsive overflow-auto">
        <table class="table" id="product-list">
            <thead class="thead-light">
                <tr>
                    <th class="text-primary-orange">Product Name/SKU</th>
                    <th class="text-primary-orange">Description</th>
                    <th class="text-primary-orange">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($products as $product)
                <tr>
                    <td>
                        <div class="text-primary-dark-mud">{{ $product->prod_name }}</div>
                        <div class="text-primary-dark-mud-sm">{{ $product->prod_sku }}</div>
                    </td>
                    <td class="text-primary-dark-mud">{!!$product->description_short !!}</td>

                    {{--
                    <td class="text-primary-dark-mud">
                        Individual Unit: {{ round($product->weight_ind_unit_g,1) }}g<br>
                    Retail Unit: {{ round($product->weight_retail_unit_g,1) }}g
                    </td>
                    <td class="text-primary-dark-mud">
                        @if(isset($product->creator))
                        Created: {{ $product->creator->name }} ({{ $product->created_at->format('Y-m-d') }})<br>
                        @if($product->updater && $product->created_at != $product->updated_at)
                        Updated: {{ $product->updater->name }} ({{ $product->updated_at->format('Y-m-d') }})
                        @endif
                        @endif
                    </td>--}}

                    <td>
                        <a href="{{ route('products.recipe', $product->id) }}" class="icon-primary-orange">
                            <span class="material-symbols-outlined">
                                content_paste
                            </span>
                        </a>
                        <a href="{{ route('products.spec', $product->id) }}" class="icon-primary-orange">
                            <span class="material-symbols-outlined">
                                apps
                            </span>
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')


<!-- Datatables JS -->
<script src="{{ asset('assets') }}/js/plugins/datatables.js"></script>

<script>
    // Initialize DataTable with correct table ID
    if (document.getElementById('product-list')) {
        const dataTableSearch = new simpleDatatables.DataTable("#product-list", {
            searchable: true,
            fixedHeight: false,
            perPage: 5
        });
    }
</script>

@endpush