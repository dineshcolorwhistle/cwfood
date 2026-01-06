@extends('backend.master', [
'pageTitle' => 'Products',
'activeMenu' => [
'item' => 'Products',
'subitem' => 'Products',
'additional' => '',
],
'breadcrumbItems' => [
['label' => 'Data Entry', 'url' => '#'],
['label' => 'Product']
]
])

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.0/dist/sweetalert2.min.css" rel="stylesheet">
<style>
    .fixed-bottom-right {
        position: fixed;
        bottom: 20px;
        right: 20px;
        z-index: 1000;
    }

    .scrollable-results {
        overflow-y: auto;
        max-height: 600px;
    }

    .list-view-item:hover {
        background-color: #f8f9fa;
    }
    button.custom-search-style {color: var(--bs-white) !important;background: var(--primary-color);background-color: var(--primary-color) !important;border: 1px solid var(--primary-color) !important;}
    .hidden {display: none;}
    .visible {display: block;}

</style>
@endpush

@section('content')
<div class="container-fluid product-search dataTable-wrapper">
    <div class="card-header">
        <div class="title-add">
            <h1 class="page-title">Products</h1>
            <div class="right-side content d-flex flex-row-reverse align-items-center">
                <input type="hidden" id="clientID" name="view" value="{{ $clientID }}">
                <input type="hidden" id="workspaceID" name="view" value="{{ $ws_id }}">
                <div class="view-toggle me-lg-4 me-3">
                    <button id="listViewBtn" class="icon-primary-orange {{ $viewType == 'list' ? 'active' : '' }} px-3 me-0" title="List View">
                        <a href="{{route('products.index')}}"><i class="material-symbols-outlined">list</i></a>
                    </button>
                    <button id="gridViewBtn" class="icon-primary-orange {{ $viewType == 'grid' ? 'active' : '' }} me-0 pe-3" title="Grid View">
                        <a href="{{route('products.grid')}}"><i class="material-symbols-outlined">grid_view</i></a>
                    </button>
                </div>
                <div class="Export-btn me-2">
                    <div class="btn-group click-dropdown">
                        <button type="button" class="btn btn-primary-orange plus-icon" title="Custom Search">
                            <span class="material-symbols-outlined" id="custom_search">filter_list</span>
                        </button>
                    </div>
                </div>
                <!-- <span class="material-symbols-outlined me-2" id="custom_search" style="font-size: 40px; cursor: pointer;">filter_list</span> -->
            </div>
        </div>
        <div class="search-filter d-flex justify-content-between align-items-center">
            <form id="searchForm" class="mb-3 mt-5">
                <div class="input-group prd-search-btn">
                    <input type="text" name="search" id="searchInput" class="form-control" placeholder="Search" value="{{ request('search') }}">
                    <input type="hidden" id="viewTypeInput" name="view" value="{{ $viewType }}">
                    <button type="submit" class="icon-primary-orange">
                        <i class="material-symbols-outlined hidden">search</i>
                    </button>
                </div>
            </form>
            <div class="dataTable-dropdown mb-3 mt-5 d-flex" style="float: right;">                
                <form method="GET" action="{{ url()->current() }}" id="entries-form">
                    <label for="perPage">
                        <select name="perPage" id="perPage" class="p-2">
                            <option value="10" {{ request('perPage') == 10 ? 'selected' : '' }}>10</option>
                            <option value="25" {{ request('perPage') == 25 ? 'selected' : '' }}>25</option>
                            <option value="50" {{ request('perPage') == 50 ? 'selected' : '' }}>50</option>
                            <option value="100" {{ request('perPage') == 100 ? 'selected' : '' }}>100</option>
                        </select>
                        per page</label>
                </form>
            </div>
        </div>

        <div class="custom-filter hidden">
            <div class="col-lg-3">
                <label class="form-label">Category</label>
                <select name="prod_category" id="prod_category" class="form-control-select js-example-basic-single" data-module="category" onchange="custom_search(this)">
                    <option disabled selected>Select Category</option>
                    @foreach($categories as $category)
                        <option value="{{ $category['id'] }}">{{ $category['name'] }} </option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-3">
                <label class="form-label">Tags</label>
                <select name="prod_tags[]" id="product_tags" class="form-control select2-tags" data-module="tags" onchange="custom_search(this)"  multiple >
                    @foreach($tags as $tag)
                        <option value="{{ $tag['id'] }}">{{ $tag['name'] }} </option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-3">
                <label class="form-label">Status</label>
                <select name="product_status" id="product_status" class="form-control-select js-example-basic-single" data-module="status" onchange="custom_search(this)">
                    <option disabled selected>Select Status</option>
                    @foreach($prod_status as $status)
                        <option value="{{ $status }}">{{ $status }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-3">
                <label class="form-label">Ranging</label>
                <select name="product_ranging" id="product_ranging" class="form-control-select js-example-basic-single" data-module="ranging" onchange="custom_search(this)">
                    <option disabled selected>Select Ranging</option>
                    @foreach($prod_ranging as $range)
                        <option value="{{ $range }}">{{ $range }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-3">
                <div class="Export-btn mt-2 text-end">
                    <div class="btn-group click-dropdown">
                        <button type="button" class="btn btn-primary-orange plus-icon" title="Reset" id="reset_filters">
                            <span class="material-symbols-outlined">refresh</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <div class="card-body">
        <div id="resultsContainer" class="background-bg">
        <div class="row" id="gridView">
    @foreach($products as $product)
    <div class="col-lg-3 col-md-4 col-sm-4 mb-4">
        <div class="card grid-view">
            <!-- Adjust image size for card view -->
            <div class="product-image-wrap">
                @php
                $sequenceNumber = is_numeric($product->prod_image) ? (int)$product->prod_image : null;
                $product_img = getModuleImage('product', $product->id, $sequenceNumber);
                @endphp
                <img src="{{ $product_img }}" alt="Product Image" class="card-img-top product-thumbnail grid">
            </div>
            <div class="card-body w-100 pb-2">
                <div class="product_name text-primary-blue-lgs text-center">{!!truncateDescription($product->prod_name , 40)!!}</div>
                <div class="product_sku text-dark-mud-sm text-center">{{ $product->prod_sku }}</div>
                <div class="product_des text-primary-dark-mud text-center">{!!truncateDescription($product->description_short , 120)!!}</div>
            </div>
            <div class="card-footer w-100">
                <div class="icon-wrap d-flex justify-content-end pb-3">
                    <!-- Edit and Delete Actions (inline) -->
                    <div class="d-flex justify-content-end mt-3">

                        <!-- Favorite Icon -->
                        <button type="button" class="icon-primary-orange me-2" title="Favourite">
                            <span class="material-symbols-outlined" onclick="make_favorite(this)" id="makefavorite_{{$product->id}}" data-favor="{{$product->favorite}}" data-url="{{route('products.favorite',['id'=>$product->id])}}">favorite</span>
                        </button>

                        <!-- 3-Dot Icon Menu for Grid View -->
                        <div class="dropdown">
                            <button class="icon-primary-orange me-0" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                                <span class="material-symbols-outlined">more_vert</span>
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                @if($user_role == 4)
                                    @if($permission['status'] == true && $permission['page']['Products'] == true)
                                        <li>
                                            <a href="{{ route('products.edit', $product->id) }}" class="dropdown-item text-primary-dark-mud me-2">
                                                <span class="sidenav-normal ms-2 ps-1">Edit</span>
                                            </a>
                                        </li>
                                        <li>
                                            <button type="button" class="dropdown-item text-primary-dark-mud me-0 delete-product text-capitalize" data-id="{{ $product->id }}">
                                                <span class="sidenav-normal ms-2 ps-1">Delete</span>
                                            </button>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0)" data-type="product" data-url="{{ route('products.duplicate', ['id'=>$product->id]) }}" onclick="make_duplicate(this)" class="dropdown-item text-primary-dark-mud me-2">
                                                <span class="sidenav-normal ms-2 ps-1">Duplicate</span>
                                            </a>
                                        </li>
                                        <hr>
                                    @endif
                                @else
                                    <li>
                                        <a href="{{ route('products.edit', $product->id) }}" class="dropdown-item text-primary-dark-mud me-2">
                                            <span class="sidenav-normal ms-2 ps-1">Edit</span>
                                        </a>
                                    </li>
                                    <li>
                                        <button type="button" class="dropdown-item text-primary-dark-mud me-0 delete-product text-capitalize" data-id="{{ $product->id }}">
                                            <span class="sidenav-normal ms-2 ps-1">Delete</span>
                                        </button>
                                    </li>
                                    <li>
                                        <a href="javascript:void(0)" data-type="product" data-url="{{ route('products.duplicate', ['id'=>$product->id]) }}" onclick="make_duplicate(this)" class="dropdown-item text-primary-dark-mud me-2">
                                            <span class="sidenav-normal ms-2 ps-1">Duplicate</span>
                                        </a>
                                    </li>
                                    <hr>
                                @endif
                                <li>
                                    <a class="dropdown-item text-primary-dark-mud" href="{{ route('products.spec', $product->id) }}">
                                        <span class="sidenav-normal ms-2 ps-1">Products Specs</span>
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item text-primary-dark-mud" href="{{ route('products.recipe', $product->id) }}">
                                        <span class="sidenav-normal ms-2 ps-1">Recipes</span>
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item text-primary-dark-mud" href="{{ route('products.labelling', $product->id) }}">
                                        <span class="sidenav-normal ms-2 ps-1">Labelling</span>
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item text-primary-dark-mud" href="{{ route('products.costing', $product->id) }}">
                                        <span class="sidenav-normal ms-2 ps-1">Costing</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>
        </div>
        <div class="d-flex justify-content-between">
            <div id="foot_note"></div>
            <div class="pagination justify-content-end" id="pagination"></div>
        </div>
    </div>


</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.0/dist/sweetalert2.min.js"></script>
<script>
    $(document).ready(function() {

        $('.js-example-basic-single, .select2-tags').select2({
            width: '100%'
        });

        $("body").tooltip({ selector: '[data-bs-toggle=tooltip]' });

        let typingTimer;
        const typingInterval = 300;

        // View toggle functionality
        $('#listViewBtn, #gridViewBtn').on('click', function() {
            // Remove active class from all buttons
            $('#listViewBtn, #gridViewBtn').removeClass('active');

            // Add active class to clicked button
            $(this).addClass('active');

            // Set the view type input
            $('#viewTypeInput').val(
                $(this).attr('id') === 'listViewBtn' ? 'list' : 'grid'
            );

            // Trigger search with current search term
            performSearch($('#searchInput').val());
        });

        // Search input handling
        $('#searchInput').on('keyup', function() {
            clearTimeout(typingTimer);
            const searchTerm = $(this).val().trim();

            if (searchTerm) {
                typingTimer = setTimeout(function() {
                    performSearch(searchTerm);
                }, typingInterval);
            } else {
                // If search is empty, show all products
                performSearch('');
            }
        });

        // Form submit
        $('#searchForm').on('submit', function(e) {
            e.preventDefault();
            var searchTerm = $('#searchInput').val();
            performSearch(searchTerm);
        });

        var searchTerm = $('#searchInput').val();
        performSearch(searchTerm);

        // Handle perPage change
        $(document).on('change', '#perPage', function() {
            performSearch($('#searchInput').val()); // Trigger search after perPage change
        });

        // Handle pagination click
        $(document).on('click', '.pagination a', function(e) {
            e.preventDefault();
            let url = $(this).attr('href');
            $.get(url, function(response) {
                $('#resultsContainer').html(response.data);
                $('#pagination').html(response.pagination); // Update pagination links
                $('#foot_note').html(response.foot_note)
            });
        });
    });

    $(document).on('click', '.delete-product', function(e) {
        e.preventDefault();
        const button = $(this);
        const id = button.data('id');
        if (!id) {
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: 'Product ID not found'
            });
            return;
        }

        const baseUrl = "{{ config('app.url') }}";
        const url = `${baseUrl}/products/${id}`;
        // Alternative way using Laravel route:
        // const url = "{{ route('products.destroy', '') }}/" + id;

        Swal.fire({
            title: 'Are you sure?',
            text: 'This will delete the product and all related data. You won\'t be able to revert this!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: url,
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Deleted!',
                                text: response.message,
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => {
                                window.location.href = "{{ route('products.index') }}";
                            });
                        }
                    },
                    error: function(xhr) {
                        console.error('Delete Error:', xhr);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: xhr.responseJSON?.message || 'An error occurred while deleting the product'
                        });
                    }
                });
            }
        });
    });

    $(document).on('click','#productDefault',function() {
        var _this = this;
        $('input.product_check').each(function() {
        if ($(_this).is(':checked')) {
            $(this).prop('checked', true);
        } else {
            if($(this).attr('id') == "sku" || $(this).attr('id') == "pr_name"){
            }else{
                $(this).prop('checked', false);
            }
        }
        });
    });

    function export_details(_this) {
        let url = $(_this).attr('data-url')
        $('#exampleModal').modal('show')
        $('#exportable_url').val(url)         
    }

    function save_export_column(params) {
        let url = $('#exportable_url').val()         
        let selectedLabels = [];
        $('#export_column').find('div.form-check-temp input').each(function() {
            if ($(this).is(':checked')) {
                let label = $(this).next('label').text().trim(); // Get the label text
                if (label) {
                    selectedLabels.push(label);
                }
            }
        });
        let data = {'_token':$('meta[name="csrf-token"]').attr('content'),'ex_xol':selectedLabels};	
        $.ajax({
            type: "POST",
            url: $(`#export_url`).val(),
            dataType: 'json',
            data: data,
            beforeSend: function () {
            },
            success: function (response) {    
                if(response['status'] == true){
                    $('#exampleModal').modal('hide')
                }
            },
            complete: function(){
                window.open(url,'_blank');
            }
        });
    }


     // Function to perform search and handle AJAX request
    function performSearch(searchTerm) {
        let category = $('#prod_category').val()
        let status = $('#product_status').val()
        let ranging = $('#product_ranging').val()
        let tags = $('#product_tags').val()

        $.ajax({
            url: "{{ route('products.search') }}",
            method: 'GET',
            data: {
                search: searchTerm,
                perPage: $('#perPage').val(),
                view: $('#viewTypeInput').val(),
                category:category,
                status:status,  
                ranging:ranging,
                tags:tags
            },
            success: function(response) {
                // Replace the results container with the returned HTML
                $('#resultsContainer').html(response.data);
                $('#pagination').html(response.pagination); // Update pagination links
                $('#foot_note').html(response.foot_note)
            },
            error: function(xhr) {
                console.error('Error:', xhr);
            }
        });
    }

    $(document).on('click', '#custom_search', function () {
        const $searchBtn = $(this);
        const $customFilter = $('.custom-filter');

        $searchBtn.toggleClass('custom-search-style');
        $customFilter.toggleClass('hidden visible');
    });

    function custom_search(_this) {
        var searchTerm = $('#searchInput').val();
        performSearch(searchTerm);
    }

    $(document).on('click','#reset_filters',function(){
        $('#prod_category, #product_status, #product_ranging').val(null).trigger('change');
        $('#product_tags').val([]).trigger('change');
        var searchTerm = $('#searchInput').val();
        performSearch(searchTerm);
    })
</script>
@endpush