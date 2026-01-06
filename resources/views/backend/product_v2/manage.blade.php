@extends('backend.master', [
    'pageTitle' => 'product_v2',
    'activeMenu' => [
        'item' => 'product_v2',
        'subitem' => 'product_v2',
        'additional' => '',
    ],
    'breadcrumbItems' => [
        ['label' => 'Data Entry', 'url' => '#'],
        ['label' => 'product_v2']
    ],
])

@section('content')
<div class="container-fluid product-search dataTable-wrapper">
    <div class="card-header">
        <div class="title-add d-flex justify-content-between align-items-center">
            <h1 class="page-title mb-0">Products (v2)</h1>
            <div class="d-flex gap-2">
                <a href="{{ route('product_v2.create') }}" class="btn btn-primary-orange plus-icon" title="Add Product">
                    <i class="material-symbols-outlined">add</i>
                </a>
            </div>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>SKU</th>
                            <th>Status</th>
                            <th>Updated</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($products as $product)
                        <tr>
                            <td>{{ $product->prod_name }}</td>
                            <td>{{ $product->prod_sku }}</td>
                            <td>{{ $product->product_status ?? '—' }}</td>
                            <td>{{ $product->updated_at }}</td>
                            <td class="text-end">
                                <a href="{{ route('product_v2.edit', $product->id) }}" class="btn btn-sm btn-secondary-blue">
                                    Edit
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-4">No products found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-3">
                {{ $products->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

