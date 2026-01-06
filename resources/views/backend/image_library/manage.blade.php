{{-- manage.blade.php --}}
@extends('backend.master', [
'pageTitle' => 'Image Libraries',
'activeMenu' => [
'item' => 'Image Libraries',
'subitem' => 'Image Libraries',
'additional' => '',
],
'features' => [
'datatables' => '1',
],
'breadcrumbItems' => [
['label' => 'Admin', 'url' => '#'],
['label' => 'Product']
],
])

@push('styles')
<style>
    .dropdown-item.active {
        background-color: #f8f9fa !important;
        color: #ea580c !important;
    }
</style>

<style>
    .filter-toggle {
        border: 2px solid var(--secondary-color);
        border-radius: 30px;
        display: flex;
    }

    .filter-toggle .filter-button {
        display: flex;
        align-items: center;
        gap: 8px;
        background: transparent;
        border: none;
        padding: 6px 16px;
        color: var(--secondary-color);
        border-radius: 30px;
    }

    .filter-toggle .filter-button:hover {
        /* background-color: rgba(234, 88, 12, 0.1); */
    }

    .filter-toggle .filter-text {
        font-size: 14px;
        font-weight: 500;
    }

    .filter-toggle .material-symbols-outlined {
        font-size: 20px;
    }

    .dropdown-item.active {
        background-color: #f8f9fa;
        color: var(--secondary-color);
    }

    /* Update the filterModule function to update the button text */
</style>
@endpush

@section('content')
@php
use Carbon\Carbon;
@endphp
<div class="container-fluid product-search dataTable-wrapper">
    <div class="card-header">
        <div class="title-add">
            <h1 class="page-title">Image Libraries</h1>

            <div class="right-side content d-flex flex-row-reverse align-items-center">
                <div class="Export-btn">
                    @if(sizeof($lists) > 0)
                    <button type="button" class="btn btn-primary-orange plus-icon" onclick="download_images(this)" title="Download as ZIP">
                        <span class="material-symbols-outlined">download</span>
                    </button>
                    @endif
                </div>

                {{-- Replace the existing filter div with this code --}}
                <div class="filter-toggle me-lg-4 me-3">
                    <div class="btn-group">
                        <button type="button" class="filter-button" data-bs-toggle="dropdown" aria-expanded="false">
                            <span class="filter-text">
                                {{ ucfirst($selectedModule === 'all' ? 'All' : $selectedModule) }}
                            </span>
                            <span class="material-symbols-outlined">filter_alt</span>
                        </button>
                        <ul class="dropdown-menu">
                            <li>
                                <a class="dropdown-item {{ $selectedModule == 'all' ? 'active' : '' }}"
                                    href="javascript:void(0);"
                                    onclick="filterModule('all')">
                                    All
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item {{ $selectedModule == 'product' ? 'active' : '' }}"
                                    href="javascript:void(0);"
                                    onclick="filterModule('product')">
                                    Products
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item {{ $selectedModule == 'raw_material' ? 'active' : '' }}"
                                    href="javascript:void(0);"
                                    onclick="filterModule('raw_material')">
                                    Raw Material
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>

            </div>
        </div>
    </div>
    <div class="card-body">
        <div id="resultsContainer" class="scrollable-results">
            <table class="table no-sort responsiveness" id="dtRecordsView">
                <thead>
                    <th style="width:3%;"></th>
                    <th style="width:7%;"></th>
                    <th style="width:90%;"></th>
                </thead>
                <tbody id="resultsBody">
                    
                    @foreach($lists as $key => $list)
                    <tr class="search_table_row" id="img_lib">
                        <td class="align-middle">
                            <input class="form-check-input" name="img_ckeck" type="checkbox" value=""
                                data-module="{{ $list['type'] }}"
                                data-moduleid="{{ $list['id'] }}"
                                id="img_ckeck{{$key}}">
                        </td>
                        <td class="align-middle">
                            @php
                            $imgUrl = '';
                            $imageField = $list['type'] === 'product' ? 'prod_image' : 'ing_image';
                                if($list[$imageField]){
                                    $imgUrl = get_default_image_url($list['type'], $list[$imageField], $list['id']);
                                } else {
                                    if($list['type'] === 'product'){
                                        $imgUrl = env('APP_URL')."/assets/img/prod_default.png";
                                    }else{
                                        $imgUrl = env('APP_URL')."/assets/img/ing_default.png";
                                    }
                                }
                            @endphp
                            <img src="{{$imgUrl}}" alt="Image" class="product-thumbnail image-lib" style="max-width: 65%;">
                        </td>
                        {{-- manage.blade.php (relevant section) --}}
                        <td class="align-middle">
                            @if($list[$imageField])
                            @php
                            if ($list['type'] === 'product') {
                            $img_details = $list['image_details'] ?? [];
                            $formattedTime = isset($img_details['updated_at'])
                            ? Carbon::parse($img_details['updated_at'])->format('D F d,Y H:i')
                            : Carbon::now()->format('D F d,Y H:i');
                            $fileSize = $img_details['file_size'] ?? 'N/A';
                            $lastModifiedBy = isset($img_details['last_modified_by'])
                            ? get_user_name($img_details['last_modified_by'])
                            : 'System';
                            $imageName = $img_details['image_name'] ?? basename($list['prod_image']);
                            } else {
                            $img_details = get_default_image_details('raw_material', $list[$imageField], $list['id']);
                            $formattedTime = isset($img_details['updated_at'])
                            ? Carbon::parse($img_details['updated_at'])->format('D F d,Y H:i')
                            : '';
                            $fileSize = $img_details['file_size'] ?? 'N/A';
                            $lastModifiedBy = isset($img_details['last_modified_by'])
                            ? get_user_name($img_details['last_modified_by'])
                            : 'System';
                            $imageName = $img_details['image_name'] ?? '';
                            }
                            @endphp
                            <div class="image_details_wrapper">
                                <a href="{{route('show.images', ['id' => $list['id']])}}?module={{$list['type']}}">
                                    <h5 class="mb-1 text-dark-mud" style="font-weight: 600;">
                                        {{strtoupper($list['type'])}}_{{$imageName}}
                                    </h5>
                                </a>
                                <p class="text-primary-dark-mud">
                                    {{$formattedTime}}, {{$fileSize}}, {{$lastModifiedBy}}
                                </p>
                            </div>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function filterModule(module) {
        window.location.href = "{{ route('manage.image_library') }}?module=" + module;
    }

    // Update the existing download_images function to handle both modules
    function download_images(element) {
        var checked = $('input[name="img_ckeck"]:checked');
        if (checked.length == 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Nothing Selected',
                text: 'Please select at least one checkbox to proceed with the download.',
                showConfirmButton: false,
                timer: 4000
            });
            return false;
        }

        var details = [];
        checked.each(function() {
            details.push({
                module: $(this).data('module'),
                moduleid: $(this).data('moduleid')
            });
        });

        $.ajax({
            url: "{{ url('admin/download/images') }}",
            type: "POST",
            data: {
                details: JSON.stringify(details),
                _token: "{{ csrf_token() }}"
            },
            xhrFields: {
                responseType: 'blob'
            },
            success: function(response) {
                var blob = new Blob([response]);
                var link = document.createElement('a');
                link.href = window.URL.createObjectURL(blob);
                link.download = "images.zip";
                link.click();
            },
            error: function(xhr, status, error) {
                console.error("Error downloading images:", error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error downloading images',
                    text: 'Please try again later',
                    showConfirmButton: false,
                    timer: 1500
                });
            }
        });
    }
</script>


<script>
    function filterModule(module) {
        // Update the button text before navigating
        const filterText = document.querySelector('.filter-text');
        filterText.textContent = module.charAt(0).toUpperCase() + module.slice(1);

        // Navigate to the filtered view
        window.location.href = "{{ route('manage.image_library') }}?module=" + module;
    }
</script>
@endpush