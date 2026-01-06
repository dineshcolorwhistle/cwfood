{{-- manage.blade.php --}}
@extends('backend.master', [
'pageTitle' => 'Admin Preferences',
'activeMenu' => [
'item' => 'Admin Preferences',
'subitem' => 'Admin Preferences',
'additional' => '',
],
'features' => [
'datatables' => '1',
],
'breadcrumbItems' => [
['label' => 'Admin', 'url' => '#'],
['label' => 'Preferences']
],
])

@push('styles')
<style>
    .dropdown-item.active {
        background-color: #f8f9fa !important;
        color: #ea580c !important;
    }
    .accordion-button {
        background-color: #f8f9fa;
        color: #333;
        font-weight: bold;
    }
    .btn-action {
        margin-left: 10px;
    }
</style>
@endpush

@section('content')
@php
use Carbon\Carbon;
@endphp
<div class="container-fluid product-search dataTable-wrapper">

    <!-- Loader Overlay -->
<div id="pageOverlay" style="
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(255,255,255,0.7);
    z-index: 9999;
    text-align: center;
">
    <div class="spinner-border text-primary" role="status" style="margin-top: 20%;">
        <span class="visually-hidden">Loading...</span>
    </div>
</div>

    <div class="card-header">
        <div class="title-add">
            <h1 class="page-title">Tags</h1>
        </div>
    </div>
    <div class="card-body">
        <!-- Nav tabs -->
        <ul class="nav nav-tabs" id="myTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="product-cat" data-bs-toggle="tab" data-bs-target="#product-cat-pane" type="button" role="tab" aria-controls="product-cat-pane" aria-selected="true">
                Product Category
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="product-tag" data-bs-toggle="tab" data-bs-target="#product-tag-pane" type="button" role="tab" aria-controls="product-tag-pane" aria-selected="false">
                Product Tags
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="raw-cat" data-bs-toggle="tab" data-bs-target="#raw-cat-pane" type="button" role="tab" aria-controls="raw-cat-pane" aria-selected="false">
                Rawmaterial Category
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="raw-tag" data-bs-toggle="tab" data-bs-target="#raw-tag-pane" type="button" role="tab" aria-controls="raw-tag-pane" aria-selected="false">
                Rawmaterial Tags
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="recipe-comp" data-bs-toggle="tab" data-bs-target="#recipe-comp-pane" type="button" role="tab" aria-controls="recipe-comp-pane" aria-selected="false">
                Recipe Component
                </button>
            </li>
        </ul>

        <!-- Tab panes -->
            <div class="tab-content p-3 border border-top-0" id="myTabContent">
                <div class="tab-pane fade show active" id="product-cat-pane" role="tabpanel" aria-labelledby="product-cat" tabindex="0">
                    <div class="right-side mb-5">
                        <div class="text-end">
                            <button type="button" class="btn btn-primary-orange plus-icon" data-toggle="modal" data-target="#actionModal" id="addCompanyBtn" title="Add Product Category" data-module="Product Category" onclick="create_preference(this)">
                                <span class="material-symbols-outlined">add</span>
                            </button>
                        </div>
                    </div>
                     <table class="table responsiveness custom-wrap" id="dtRecordsView">
                        <thead>
                            <tr>
                                <th class="text-primary-orange">Name</th>
                                <th class="text-primary-orange">Created By</th>
                                <th class="text-primary-orange">Usage Count</th>
                                <th class="text-primary-orange"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($product_categories as $tag)
                            <tr>
                                <td>{{$tag['name']}}</td>
                                <td>{{$tag['creator']['name'] ?? 'Admin'}}</td>
                                <td>
                                    @if($tag['products_count'] > 0)
                                        @php
                                            $associatedItems = array_column($tag['products'],'prod_name');
                                            $tooltipHtml = '<ol>';
                                            foreach ($associatedItems as $item) {
                                                $tooltipHtml .= '<li>' . htmlspecialchars($item) . '</li>';
                                            }
                                            $tooltipHtml .= '</ol>';
                                        @endphp
                                        <span data-bs-toggle="tooltip" data-bs-placement="top" data-bs-custom-class="custom-tooltip admin-preference" data-bs-html="true" data-bs-title="<?= htmlspecialchars($tooltipHtml) ?>">{{$tag['products_count']}}</span>
                                    @else
                                        {{$tag['products_count']}}
                                    @endif
                                </td>
                                <td class="actions-menu-area">
                                    <div class="">
                                        <!-- 3-Dot Icon Menu for Grid View -->
                                        <div class="dropdown d-flex justify-content-end">
                                            <button class="icon-primary-orange me-0" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                                                <span class="material-symbols-outlined">more_vert</span>
                                            </button>
                                            <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                                <li>
                                                    <span class="dropdown-item text-primary-dark-mud me-2 edit-tag"
                                                        data-id="{{ $tag['id'] }}"
                                                        data-module="Product Category"
                                                        data-name="{{ $tag['name'] }}">
                                                        <span class="sidenav-normal ms-2 ps-1">Edit</span>
                                                    </span>
                                                </li>
                                                <li>
                                                    <span class="dropdown-item text-primary-dark-mud delete-tag" data-module="Product Category" data-id="{{ $tag['id'] }}" data-count="{{$tag['products_count']}}">
                                                        <span class="sidenav-normal ms-2 ps-1">Delete</span>
                                                    </span>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                   
                </div>
                <div class="tab-pane fade" id="product-tag-pane" role="tabpanel" aria-labelledby="product-tag" tabindex="0">
                    <div class="right-side mb-5">
                        <div class="text-end">
                            <button type="button" class="btn btn-primary-orange plus-icon" data-toggle="modal" data-target="#actionModal" title="Add Product Tags" data-module="Product Tags" onclick="create_preference(this)">
                                <span class="material-symbols-outlined">add</span>
                            </button>
                        </div>
                    </div>
                    <table class="table responsiveness custom-wrap init-datatable" id="dtRecordsView1">
                        <thead>
                            <tr>
                                <th class="text-primary-orange">Name</th>
                                <th class="text-primary-orange">Created By</th>
                                <th class="text-primary-orange">Usage Count</th>
                                <th class="text-primary-orange"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($produc_tags as $tag)
                            <tr>
                                <td>{{$tag['name']}}</td>
                                <td>{{$tag['creator']['name'] ?? 'Admin'}}</td>
                                <td>
                                    @if($tag['product_count'] > 0)
                                       <span data-bs-toggle="tooltip" data-bs-placement="top" data-bs-custom-class="custom-tooltip admin-preference" data-bs-html="true" data-bs-title="<?= htmlspecialchars($tag['products_list']) ?>">{{$tag['product_count']}}</span>
                                    @else
                                        {{$tag['product_count']}}
                                    @endif
                                </td>
                                <td class="actions-menu-area">
                                    <div class="">
                                        <!-- 3-Dot Icon Menu for Grid View -->
                                        <div class="dropdown d-flex justify-content-end">
                                            <button class="icon-primary-orange me-0" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                                                <span class="material-symbols-outlined">more_vert</span>
                                            </button>
                                            <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                                <li>
                                                    <span class="dropdown-item text-primary-dark-mud me-2 edit-tag"
                                                        data-id="{{ $tag['id'] }}"
                                                        data-module="Product Tags"
                                                        data-name="{{ $tag['name'] }}">
                                                        <span class="sidenav-normal ms-2 ps-1">Edit</span>
                                                    </span>
                                                </li>
                                                <li>
                                                    <span class="dropdown-item text-primary-dark-mud delete-tag" data-module="Product Tags" data-id="{{ $tag['id'] }}" data-count="{{$tag['product_count']}}">
                                                        <span class="sidenav-normal ms-2 ps-1">Delete</span>
                                                    </span>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="tab-pane fade" id="raw-cat-pane" role="tabpanel" aria-labelledby="raw-cat" tabindex="0">
                    <div class="right-side mb-5">
                        <div class="text-end">
                            <button type="button" class="btn btn-primary-orange plus-icon" data-toggle="modal" data-target="#actionModal" title="Add Rawmaterial Category" data-module="Rawmaterial Category" onclick="create_preference(this)">
                                <span class="material-symbols-outlined">add</span>
                            </button>
                        </div>
                    </div>
                    <table class="table responsiveness custom-wrap init-datatable" id="dtRecordsView1">
                        <thead>
                            <tr>
                                <th class="text-primary-orange">Name</th>
                                <th class="text-primary-orange">Created By</th>
                                <th class="text-primary-orange">Usage Count</th>
                                <th class="text-primary-orange"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($rawmaterial_categories as $tag)
                            <tr>
                                <td>{{$tag['name']}}</td>
                                <td>{{$tag['creator']['name'] ?? 'Admin'}}</td>
                                <td>
                                    @if($tag['raw_materials_count'] > 0)
                                        @php
                                            $associatedItems = array_column($tag['raw_materials'],'name_by_kitchen');
                                            $tooltipHtml = '<ol>';
                                            foreach ($associatedItems as $item) {
                                                $tooltipHtml .= '<li>' . htmlspecialchars($item) . '</li>';
                                            }
                                            $tooltipHtml .= '</ol>';
                                        @endphp
                                       <span data-bs-toggle="tooltip" data-bs-placement="top" data-bs-custom-class="custom-tooltip admin-preference" data-bs-html="true" data-bs-title="<?= htmlspecialchars($tooltipHtml) ?>">{{$tag['raw_materials_count']}}</span>
                                    @else
                                        {{$tag['raw_materials_count']}}
                                    @endif
                                </td>
                                <td class="actions-menu-area">
                                    <div class="">
                                        <!-- 3-Dot Icon Menu for Grid View -->
                                        <div class="dropdown d-flex justify-content-end">
                                            <button class="icon-primary-orange me-0" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                                                <span class="material-symbols-outlined">more_vert</span>
                                            </button>
                                            <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                                <li>
                                                    <span class="dropdown-item text-primary-dark-mud me-2 edit-tag"
                                                        data-id="{{ $tag['id'] }}"
                                                        data-module="Rawmaterial Category"
                                                        data-name="{{ $tag['name'] }}">
                                                        <span class="sidenav-normal ms-2 ps-1">Edit</span>
                                                    </span>
                                                </li>
                                                <li>
                                                    <span class="dropdown-item text-primary-dark-mud delete-tag" data-module="Rawmaterial Category" data-id="{{ $tag['id'] }}" data-count="{{$tag['raw_materials_count']}}">
                                                        <span class="sidenav-normal ms-2 ps-1">Delete</span>
                                                    </span>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="tab-pane fade" id="raw-tag-pane" role="tabpanel" aria-labelledby="raw-tag" tabindex="0">
                    <div class="right-side mb-5">
                        <div class="text-end">
                            <button type="button" class="btn btn-primary-orange plus-icon" data-toggle="modal" data-target="#actionModal" id="addCompanyTagBtn" title="Add Rawmaterial Tags" data-module="Rawmaterial Tags" onclick="create_preference(this)">
                                <span class="material-symbols-outlined">add</span>
                            </button>
                        </div>
                    </div>
                    <table class="table responsiveness custom-wrap init-datatable" id="dtRecordsView1">
                        <thead>
                            <tr>
                                <th class="text-primary-orange">Name</th>
                                <th class="text-primary-orange">Created By</th>
                                <th class="text-primary-orange">Usage Count</th>
                                <th class="text-primary-orange"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($rawmaterial_tags as $tag)
                            <tr>
                                <td>{{$tag['name']}}</td>
                                <td>{{$tag['creator']['name'] ?? 'Admin'}}</td>
                                <td>
                                    @if($tag['raw_materials_count'] > 0)
                                       <span data-bs-toggle="tooltip" data-bs-placement="top" data-bs-custom-class="custom-tooltip admin-preference" data-bs-html="true" data-bs-title="<?= htmlspecialchars($tag['raw_material_list']) ?>">{{$tag['raw_materials_count']}}</span>
                                    @else
                                        {{$tag['raw_materials_count']}}
                                    @endif
                                </td>
                                <td class="actions-menu-area">
                                    <div class="">
                                        <!-- 3-Dot Icon Menu for Grid View -->
                                        <div class="dropdown d-flex justify-content-end">
                                            <button class="icon-primary-orange me-0" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                                                <span class="material-symbols-outlined">more_vert</span>
                                            </button>
                                            <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                                <li>
                                                    <span class="dropdown-item text-primary-dark-mud me-2 edit-tag"
                                                        data-id="{{ $tag['id'] }}"
                                                        data-module="Rawmaterial Tags"
                                                        data-name="{{ $tag['name'] }}">
                                                        <span class="sidenav-normal ms-2 ps-1">Edit</span>
                                                    </span>
                                                </li>
                                                <li>
                                                    <span class="dropdown-item text-primary-dark-mud delete-tag" data-module="Rawmaterial Tags" data-id="{{ $tag['id'] }}" data-count="{{$tag['raw_materials_count']}}">
                                                        <span class="sidenav-normal ms-2 ps-1">Delete</span>
                                                    </span>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="tab-pane fade" id="recipe-comp-pane" role="tabpanel" aria-labelledby="recipe-comp" tabindex="0">
                    <div class="right-side mb-5">
                        <div class="text-end">
                            <button type="button" class="btn btn-primary-orange plus-icon" data-toggle="modal" data-target="#actionModal" id="addCompanyTagBtn" title="Add Recipe Component" data-module="Recipe Component" onclick="create_preference(this)">
                                <span class="material-symbols-outlined">add</span>
                            </button>
                        </div>
                    </div>
                    <table class="table responsiveness custom-wrap init-datatable" id="dtRecordsView1">
                        <thead>
                            <tr>
                                <th class="text-primary-orange">Name</th>
                                <th class="text-primary-orange">Created By</th>
                                <th class="text-primary-orange">Usage Count</th>
                                <th class="text-primary-orange">Default</th>
                                <th class="text-primary-orange"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recipe_components as $component)
                            <tr>
                                <td>{{$component['name']}}</td>
                                <td>{{$component['creator']['name'] ?? 'Admin'}}</td>
                                <td>{{$component['component_count']}}</td>
                                <td>
                                    <input class="form-check-input" type="checkbox"  data-id="{{$component['id']}}" name="default" @if($component['default'] == 1) checked  disabled @endif>
                                </td>
                                <td class="actions-menu-area">
                                    <div class="">
                                        <!-- 3-Dot Icon Menu for Grid View -->
                                        <div class="dropdown d-flex justify-content-end">
                                            <button class="icon-primary-orange me-0" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                                                <span class="material-symbols-outlined">more_vert</span>
                                            </button>
                                            <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                                <li>
                                                    <span class="dropdown-item text-primary-dark-mud me-2 edit-tag"
                                                        data-id="{{ $component['id'] }}"
                                                        data-module="Recipe Component"
                                                        data-name="{{ $component['name'] }}">
                                                        <span class="sidenav-normal ms-2 ps-1">Edit</span>
                                                    </span>
                                                </li>
                                                <li>
                                                    <span class="dropdown-item text-primary-dark-mud delete-tag" data-module="Recipe Component" data-id="{{ $component['id'] }}" data-count="{{$component['component_count']}}">
                                                        <span class="sidenav-normal ms-2 ps-1">Delete</span>
                                                    </span>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        
    </div>

    <!-- Modal -->
    <div class="modal fade" id="prefModal" tabindex="-1" aria-labelledby="prefModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="prefModalLabel"></h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="export_column">
                    <div class="form-group d-flex flex-column">
                        <label class="text-primary-orange col-form-label" for="labour_type">Name</label>
                        <input type="text"  class="form-control" name="pref_create_val" id="pref_create_val" >
                    </div>
                </div>
                <input type="hidden" id="pref_cmodule">
                <input type="hidden" id="pref_cid">
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary-blue" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-secondary-blue" onclick="new_preference();">Save</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('.init-datatable').DataTable({
            responsive: true,
            dom: "<'row mb-4'<'col-md-6 col-6 col-sm-6'f><'col-md-6 col-6 col-sm-6'l>>" + // Search box (f) and entries dropdown (l)
                "<'row table-responsiveness'<'col-sm-12'tr>>" + // Table rows
                "<'row'<'col-md-5'i><'col-md-7'p>>", // Info text (i) and pagination (p)
            language: {
                search: "", // Removes "Search:" label
                searchPlaceholder: "Search", // Adds placeholder to the search box
                lengthMenu: "_MENU_ per page", // Customizes "entries per page" text
                paginate: {
                    previous: "<i class='material-symbols-outlined'>chevron_left</i>", // Replace "Previous" text with '<'
                    next: "<i class='material-symbols-outlined'>chevron_right</i>" // Replace "Next" text with '>'
                }
            },
            pageLength: 25,
            initComplete: function() {
                // Move the search box to the left and entries dropdown to the right
                const tableWrapper = $(this).closest('.dataTables_wrapper');
                const searchBox = tableWrapper.find('.dataTables_filter');
                const lengthDropdown = tableWrapper.find('.dataTables_length');

                searchBox.css({
                    'float': 'left',
                    'margin-top': '0'
                });
                lengthDropdown.css('float', 'right');
            }
        }); 
    });    

    function create_preference(_this){
        let module = $(_this).attr('data-module')    
        $('#prefModalLabel').html(`Add ${module}`)
        $('#pref_cmodule').val(module)
        $('#prefModal').modal('show')
    }

    function new_preference(_this){
        let val = $('#pref_create_val').val()
        let module = $('#pref_cmodule').val()
        const tagtId = $('#pref_cid').val();
        const url = tagtId ? "{{ route('preference.update')}}" : "{{ route('preference.store') }}";
        let data = {'_token':$('meta[name="csrf-token"]').attr('content'),'module':module,'name':val,'id':tagtId};	
        $.ajax({
            type: "POST",
            url: url,
            dataType: 'json',
            data: data,
            beforeSend: function () {
            },
            success: function (response) {
                let val = $('#pref_create_val').val('')             
                $('#prefModal').modal('hide')
                if(response.success){
                    show_swal(1, response.message);
                    setTimeout(() => {
                        window.location.href="{{route('preference.manage')}}"
                    }, 1000); 
                }else{
                    show_swal(0, response.message);
                }
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    let errorMessage = '';

                    for (const [field, messages] of Object.entries(errors)) {
                        errorMessage += `${field}: ${messages.join(', ')}\n`;
                    }

                    Swal.fire({
                        icon: 'warning',
                        title: 'Validation Errors',
                        text: errorMessage
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: xhr.responseJSON.message || 'An error occurred'
                    });
                }
            }
        });
    }

    $(document).on('click', '.edit-tag', function() {
        const id = $(this).data('id');
        const name = $(this).data('name');
        const module = $(this).data('module');
        $('#prefModalLabel').html(`Update ${module}`)
        $('#pref_cmodule').val(module)
        $('#pref_cid').val(id)
        $('#pref_create_val').val(name)
        $('#prefModal').modal('show')
    });

     $(document).on('click', '.delete-tag', function() { 
        const id = $(this).data('id');
        const module = $(this).data('module');
        const count = $(this).data('count');
        console.log(count);
        
        // title = 'Delete Raw Materials' 
  
        // if(count != 0){
        //     Swal.fire({
        //         icon: 'warning',
        //         title: 'Warning!',
        //         text: title
        //     })
        //     return;
        // }

        const url = "{{ route('preference.delete') }}";
        Swal.fire({
            title: 'Are you sure?',
            text: 'You won\'t be able to revert this!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: url,
                    method: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}',
                        id:id,
                        module:module
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Deleted!',
                                text: response.message,
                                timer: 2000
                            }).then(() => {
                                location.reload();
                            });
                        }else{
                            Swal.fire({
                                icon: 'warning',
                                title: 'Warning!',
                                text: response.message
                            })
                        }
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: xhr.responseJSON.message || 'An error occurred'
                        });
                    }
                });
            }
        });
    });


    $(document).on('click','input[name="default"]',function(){
        let id = $(this).data('id');
        const url = "{{ route('preference.default', ':id') }}".replace(':id', id);
        // Show overlay before request
        $('#pageOverlay').fadeIn(200);

        $.ajax({
            url: url,
            method: 'POST',
            processData: false,
            contentType: false,
            complete: function() {
                // Disable overlay
                $('#pageOverlay').fadeOut(200);
            },
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: response.message,
                        timer: 2000
                    }).then(() => {
                        location.reload();
                    });
                }else{
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: response.message
                    });
                }
            }
        });
        
    });

</script>
@endpush