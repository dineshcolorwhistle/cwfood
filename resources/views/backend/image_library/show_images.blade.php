{{-- show_images.blade.php --}}
@extends('backend.master', [
'pageTitle' => 'Image Libraries',
'activeMenu' => [
'item' => 'Image Libraries',
'subitem' => 'Image Libraries',
'additional' => '',
],
'breadcrumbItems' => [
['label' => 'Admin', 'url' => '#'],
['label' => 'Image Libraries', 'url' => route('manage.image_library')],
['label' => ucfirst($module) . ' Images']
],
])

@section('content')
<div class="container-fluid product-search">
    <div class="row">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h1 class="page-title mb-0">
                <span class="hidden">{{ ucfirst($module) }} Images</span>
                <span>Image Libraries</span>
            </h1>
            <a href="{{ route('manage.image_library') }}" class="btn btn-primary-orange plus-icon hidden">
                <span class="material-symbols-outlined">arrow_back</span>
            </a>
        </div>
        <div class="card-body">
            @if(count($lists) > 0)
            <div class="row">
                @foreach($lists as $list)
                <div class="col-12 col-sm-6 col-md-4 col-lg-3 mb-4">
                    <div class="shadow-sm">
                        <div class="card-img-top position-relative overflow-hidden" style="border-radius: 10px;">
                            <img src="{{ $list['image_url'] }}"
                                class="img-fluid"
                                alt="{{ $list['image_name'] }}"
                                style="width: 100%; height: 200px; object-fit: cover;">
                        </div>
                        <div class="card-body hidden">
                            <h6 class="card-title text-truncate" title="{{ $list['image_name'] }}">
                                {{ $list['image_name'] }}
                            </h6>
                            <p class="card-text small text-muted mb-0">
                                Size: {{ $list['file_size'] ?? 'N/A' }}
                            </p>
                            <p class="card-text small text-muted">
                                Updated: {{ isset($list['updated_at']) ? 
                                            \Carbon\Carbon::parse($list['updated_at'])->format('M d, Y H:i') : 
                                            'N/A' 
                                        }}
                            </p>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <div class="alert alert-info">
                No images found for this {{ $module }}.
            </div>
            @endif
        </div>
    </div>
</div>
@endsection