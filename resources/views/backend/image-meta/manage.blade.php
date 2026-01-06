{{-- manage.blade.php --}}
@extends('backend.master', [
'pageTitle' => 'Image Metadata',
'activeMenu' => [
'item' => 'Image Metadata',
'subitem' => 'Image Metadata',
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
            <h1 class="page-title">Image Metadata</h1>
            </div>
        </div>
    </div>
    <div class="card-body">
       

        <div id="resultsContainer" class="scrollable-results">
             <!-- Loader -->
        <div id="tableSkeleton" class="skeleton-wrapper">
            @for($i=0;$i<6;$i++)
            <div class="skeleton-row"></div>
            @endfor
        </div>
          
            <table class="table responsiveness" id="dtRecordsView" style="display:none;">
                <thead>
                    <th class="text-primary-blue">ImageID</th>
                    <th class="text-primary-blue">SKU</th>
                    <th class="text-primary-blue text-end">Image Number</th>
                    <th class="text-primary-blue">Default Image</th>
                    <th class="text-primary-blue">File Format</th>
                    <th class="text-primary-blue">File Size</th>
                    <th class="text-primary-blue">Upload Timestamp</th>
                    <th class="text-primary-blue">Uploaded By</th>
                    <th class="text-primary-blue">Folder Path</th>
                </thead>
                <tbody id="resultsBody">
                @foreach($all as $key => $lists)
                    @php 
                    $formattedTime = isset($lists['created_at'])
                            ? Carbon::parse($lists['created_at'])->format('D F d,Y H:i')
                            : Carbon::now()->format('D F d,Y H:i');
                            $lastModifiedBy = isset($lists['uploaded_by'])
                            ? get_user_name($lists['uploaded_by'])
                            : 'System';
                            $default_image = ($lists['default_image'] == 1) ? "True": "False";
                            $image_number = finding_numbers($lists['id']);
                            $exp = explode('/',$lists['folder_path']);
                            $folder_path = "/$exp[3]/$exp[4]";

                    @endphp
                    <tr class="search_table_row">
                        <td class="text-primary-dark-mud">IMG{{$image_number}}</td>    
                        <td class="text-primary-dark-mud">{{$lists['SKU']}}</td>
                       <td class="text-primary-dark-mud text-end">{{$lists['image_number']}}</td>
                       <td class="text-primary-dark-mud text-start">{{$default_image}}</td>
                       <td class="text-primary-dark-mud">{{$lists['file_format']}}</td>
                       <td class="text-primary-dark-mud">{{$lists['file_size']}}</td>
                       <td class="text-primary-dark-mud">{{$formattedTime}}</td>
                       <td class="text-primary-dark-mud">{{$lastModifiedBy}}</td>
                       <td class="text-primary-dark-mud">{{$folder_path}}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

