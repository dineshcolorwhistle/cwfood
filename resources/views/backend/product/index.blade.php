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
</style>
@endpush

@section('content')
<div class="container-fluid product-search dataTable-wrapper">
    <div class="card-header">
        <div class="title-add">
            <h1 class="page-title">Products</h1>
            <div class="right-side content d-flex flex-row-reverse align-items-center">
                @if($user_role == 4)
                    @if($permission['status'] == true && $permission['page']['Products'] == true)
                        <div class="text-end">
                            <a href="{{ route('products.create') }}" class="btn btn-primary-orange plus-icon" id="addProductBtn" title="Add Product">
                                <i class="material-symbols-outlined">add</i>
                            </a>
                        </div>
                        <div class="Export-btn me-lg-4 me-3">
                            <div class="btn-group click-dropdown">
                                <a href="{{ route('product.import.form') }}" class="btn btn-primary-orange plus-icon" title="Upload / Import data">
                                    <span class="material-symbols-outlined">upload</span>
                                </a>
                            </div>
                        </div>
                        <div class="Export-btn me-lg-4 me-3">
                            <div class="btn-group click-dropdown">
                                <button type="button" class="btn btn-primary-orange plus-icon" title="Download Raw Material">
                                    <span class="material-symbols-outlined">download</span>
                                </button>
                                <ul class="dropdown-menu">
                                    <li>
                                        <a class="dropdown-item" href="javascript:void(0)" data-url="{{env('APP_URL')}}/download/csv/product" onclick="export_details(this)">Download as CSV</a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" id="download_excel" href="javascript:void(0)" data-url="{{env('APP_URL')}}/download/excel/product" onclick="export_details(this)">Download as Excel</a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    @endif
                @else
                    <div class="text-end">
                        <a href="{{ route('products.create') }}" class="btn btn-primary-orange plus-icon" id="addProductBtn" title="Add Product">
                            <i class="material-symbols-outlined">add</i>
                        </a>
                    </div>
                    <div class="Export-btn me-lg-4 me-3">
                        <div class="btn-group click-dropdown">
                            <a href="{{ route('product.import.form') }}" class="btn btn-primary-orange plus-icon" title="Upload / Import data">
                                <span class="material-symbols-outlined">upload</span>
                            </a>
                        </div>
                    </div>
                    <div class="Export-btn me-lg-4 me-3">
                        <div class="btn-group click-dropdown">
                            <button type="button" class="btn btn-primary-orange plus-icon" title="Download Raw Material">
                                <span class="material-symbols-outlined">download</span>
                            </button>
                            <ul class="dropdown-menu">
                                <li>
                                    <a class="dropdown-item" href="javascript:void(0)" data-url="{{env('APP_URL')}}/download/csv/product" onclick="export_details(this)">Download as CSV</a>
                                </li>
                                <li>
                                    <a class="dropdown-item" id="download_excel" href="javascript:void(0)" data-url="{{env('APP_URL')}}/download/excel/product" onclick="export_details(this)">Download as Excel</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                @endif
                <input type="hidden" id="clientID" name="view" value="{{ $clientID }}">
                <input type="hidden" id="workspaceID" name="view" value="{{ $ws_id }}">
                <div class="view-toggle me-lg-4 me-3">
                    <button id="listViewBtn" class="icon-primary-orange {{ $viewType == 'list' ? 'active' : '' }} px-3 me-0" title="List View">
                        <i class="material-symbols-outlined">list</i>
                    </button>
                    <button id="gridViewBtn" class="icon-primary-orange {{ $viewType == 'grid' ? 'active' : '' }} me-0 pe-3" title="Grid View">
                        <i class="material-symbols-outlined">grid_view</i>
                    </button>
                </div>
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
            <div class="dataTable-dropdown mb-3 mt-5" style="float: right;">
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
    </div>

    <div class="card-body">
        <div id="resultsContainer" class="background-bg">
            @include('backend.product.partials.product-view', ['products' => $products, 'viewType' => $viewType,'clientID' =>$clientID, 'ws_id'=>$ws_id])
        </div>
        <div class="pagination justify-content-end" id="pagination">
            {{ $products->appends(['perPage' => $perPage, 'view' => $viewType,'clientID' =>$clientID, 'ws_id'=>$ws_id])->links() }}
        </div>
    </div>



    <!-- Modal -->
    <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <input type="hidden" id="export_url" value="{{route('export.product_column')}}">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="exampleModalLabel">Select Column</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="export_column">
                    <div class="form-check-temp p-1">
                        <input class="form-check-input" type="checkbox" value="" id="productDefault">
                        <label class="form-check-label" for="productDefault">ALL</label>
                    </div>
                    <div class="form-check-temp p-1">
                        <input class="form-check-input product_check" type="checkbox" value="" id="pr_name" checked disabled>
                        <label class="form-check-label" for="pr_name">Product Name</label>
                    </div>
                    <div class="form-check-temp p-1">
                        <input class="form-check-input product_check" type="checkbox" value="" id="sku" checked disabled>
                        <label class="form-check-label" for="sku">SKU</label>
                    </div>
                    <div class="form-check-temp p-1">
                        <input class="form-check-input product_check" type="checkbox" value="" id="gs_code">
                        <label class="form-check-label" for="gs_code">GS1 Barcode</label>
                    </div>
                    <div class="form-check-temp p-1">
                        <input class="form-check-input product_check" type="checkbox" value="" id="desc">
                        <label class="form-check-label" for="desc">Description</label>
                    </div>
                    <div class="form-check-temp p-1">
                        <input class="form-check-input product_check" type="checkbox" value="" id="long_desc">
                        <label class="form-check-label" for="long_desc">Long Description</label>
                    </div>
                    <div class="form-check-temp p-1">
                        <input class="form-check-input product_check" type="checkbox" value="" id="pr_tags">
                        <label class="form-check-label" for="pr_tags">Product Tags</label>
                    </div>
                    <div class="form-check-temp p-1">
                        <input class="form-check-input product_check" type="checkbox" value="" id="contingency">
                        <label class="form-check-label" for="contingency">Contingency</label>
                    </div>
                    <div class="form-check-temp p-1">
                        <input class="form-check-input product_check" type="checkbox" value="" id="we_int">
                        <label class="form-check-label" for="we_int">Weight-Ind Unit</label>
                    </div>

                    <div class="form-check-temp p-1">
                        <input class="form-check-input product_check" type="checkbox" value="" id="we_sell">
                        <label class="form-check-label" for="we_sell">Weight-Sell Unit</label>
                    </div>
                    <div class="form-check-temp p-1">
                        <input class="form-check-input product_check" type="checkbox" value="" id="we_cart">
                        <label class="form-check-label" for="we_cart">Weight-Carton</label>
                    </div>
                    <div class="form-check-temp p-1">
                        <input class="form-check-input product_check" type="checkbox" value="" id="we_pallet">
                        <label class="form-check-label" for="we_pallet">Weight-Pallet</label>
                    </div>
                    <div class="form-check-temp p-1">
                        <input class="form-check-input product_check" type="checkbox" value="" id="unit_sell">
                        <label class="form-check-label" for="unit_sell">Unit-Sell Unit</label>
                    </div>
                    <div class="form-check-temp p-1">
                        <input class="form-check-input product_check" type="checkbox" value="" id="unit_cart">
                        <label class="form-check-label" for="unit_cart">Unit-Carton</label>
                    </div>
                    <div class="form-check-temp p-1">
                        <input class="form-check-input product_check" type="checkbox" value="" id="unit_pallet">
                        <label class="form-check-label" for="unit_pallet">Unit-Pallet</label>
                    </div>
                    <div class="form-check-temp p-1">
                        <input class="form-check-input product_check" type="checkbox" value="" id="rro_ind">
                        <label class="form-check-label" for="rro_ind">RRP-Ind Unit</label>
                    </div>
                    <div class="form-check-temp p-1">
                        <input class="form-check-input product_check" type="checkbox" value="" id="rro_sell">
                        <label class="form-check-label" for="rro_sell">RRP-Sell Unit</label>
                    </div>
                    <div class="form-check-temp p-1">
                        <input class="form-check-input product_check" type="checkbox" value="" id="rro_cart">
                        <label class="form-check-label" for="rro_cart">RRP-Carton</label>
                    </div>
                    <div class="form-check-temp p-1">
                        <input class="form-check-input product_check" type="checkbox" value="" id="rro_pallet">
                        <label class="form-check-label" for="rro_pallet">RRP-Pallet</label>
                    </div>
                </div>
                <input type="hidden" id="exportable_url">
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary-blue" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-secondary-blue" onclick="save_export_column();">Save</button>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.0/dist/sweetalert2.min.js"></script>
<script>
    $(document).ready(function() {

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

        // Function to perform search and handle AJAX request
        function performSearch(searchTerm) {
            $.ajax({
                url: "{{ route('products.search') }}",
                method: 'GET',
                data: {
                    search: searchTerm,
                    perPage: $('#perPage').val(),
                    view: $('#viewTypeInput').val(),
                    client:$(`#clientID`).val(),
                    ws:$(`#workspaceID`).val()
                },
                success: function(response) {
                    // Replace the results container with the returned HTML
                    $('#resultsContainer').html(response.data);
                    $('#pagination').html(response.pagination); // Update pagination links
                },
                error: function(xhr) {
                    console.error('Error:', xhr);
                }
            });
        }

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
            });
        });

        // Initial load to ensure view is set correctly
        performSearch($('#searchInput').val());
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
</script>
@endpush