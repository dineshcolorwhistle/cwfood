@extends('backend.master', [
    'pageTitle' => 'Raw Materials v2',
    'activeMenu' => [
        'item' => 'Rawmaterial v2',
        'subitem' => 'Rawmaterial v2',
        'additional' => '',
    ],
    'breadcrumbItems' => [
        ['label' => 'Data Entry', 'url' => '#'],
        ['label' => 'Raw Materials v2']
    ],
])

@section('content')
<div class="container-fluid product-search dataTable-wrapper">
    <div class="card-header">
        <div class="title-add d-flex justify-content-between align-items-center">
            <h1 class="page-title mb-0">Raw Materials (v2)</h1>
            <div class="d-flex gap-2">
                @if($user_role == 4)
                    @if(isset($permission['status']) && $permission['status'] == true && isset($permission['page']['Resources - Raw Material']) && $permission['page']['Resources - Raw Material'] == true)
                    <a href="{{ route('rawmaterial_v2.create') }}" class="btn btn-primary-orange plus-icon" title="Add Raw Material">
                        <i class="material-symbols-outlined">add</i>
                    </a>
                    @endif
                @else
                <a href="{{ route('rawmaterial_v2.create') }}" class="btn btn-primary-orange plus-icon" title="Add Raw Material">
                    <i class="material-symbols-outlined">add</i>
                </a>
                @endif
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
                        @forelse($lists as $ingredient)
                        <tr>
                            <td>{{ $ingredient->name_by_kitchen }}</td>
                            <td>{{ $ingredient->ing_sku }}</td>
                            <td>{{ $ingredient->raw_material_status ?? '—' }}</td>
                            <td>{{ $ingredient->updated_at->format('Y-m-d H:i') }}</td>
                            <td class="text-end">
                                <a href="{{ route('rawmaterial_v2.edit', $ingredient->id) }}" class="btn btn-sm btn-secondary-blue">
                                    Edit
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-4">No raw materials found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-3">
                {{ $lists->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

