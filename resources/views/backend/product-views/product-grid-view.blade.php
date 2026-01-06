@extends('backend.master', [
'pageTitle' => 'Product Views',
'activeMenu' => [
'item' => 'Product Views',
'subitem' => 'Product Views',
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

    button.custom-search-style {
        color: var(--bs-white) !important;
        background: var(--primary-color);
        background-color: var(--primary-color) !important;
        border: 1px solid var(--primary-color) !important;
    }
    .hidden {display: none;}
    .visible {display: block;}
    /* #foot_note{font-size: 19px; color: #403e3e7d !important;padding-left: 8px;} */
    .product_des span{background-color:rgb(214 236 255) !important;}
</style>
@endpush

@section('content')
<div class="container-fluid product-search dataTable-wrapper">
    <div class="card-header">
        <div class="title-add">
            <h1 class="page-title">Product Views</h1>
            <div class="right-side content d-flex flex-row-reverse align-items-center">
                <input type="hidden" id="clientID" name="view" value="{{ $clientID }}">
                <input type="hidden" id="workspaceID" name="view" value="{{ $ws_id }}">
                <div class="Export-btn me-2">
                    <div class="btn-group click-dropdown">
                        <button type="button" class="btn btn-primary-orange plus-icon" title="Custom Search">
                            <span class="material-symbols-outlined" id="custom_search">filter_list</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="search-filter d-flex justify-content-between align-items-center">
            <form id="searchForm" class="mb-5 mt-4">
                <div class="input-group prd-search-btn">
                    <input type="text" name="search" id="searchInput" class="form-control" placeholder="Search" value="{{ request('search') }}">
                    <input type="hidden" id="viewTypeInput" name="view" value="{{ $viewType }}">
                    <button type="submit" class="icon-primary-orange">
                        <i class="material-symbols-outlined hidden">search</i>
                    </button>
                </div>
            </form>

            <div class="dataTable-dropdown mb-5 mt-4 d-flex" style="float: right;">
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
                    @foreach($pr_tags as $tag)
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
            @include('backend.product-views.partials.product-view', ['products' => $products])
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
        var searchTerm = $('#searchInput').val();
        performSearch(searchTerm);
    });

    $(document).on('click', '#custom_search', function () {
        const $searchBtn = $(this);
        const $customFilter = $('.custom-filter');

        $searchBtn.toggleClass('custom-search-style');
        $customFilter.toggleClass('hidden visible');
    });

    // Function to perform search and handle AJAX request
    function performSearch(searchTerm) {
        let category = $('#prod_category').val()
        let status = $('#product_status').val()
        let ranging = $('#product_ranging').val()
        let tags = $('#product_tags').val()

        $.ajax({
            url: "{{ route('products-view.search') }}",
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
                $('#foot_note').html(response.foot_note);

                // Remove any lingering tooltip elements appended to body
                document.querySelectorAll('.tooltip.show').forEach(function(tt) {
                    tt.remove();
                });
                setTimeout(() => {
                    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function (el) {
                        new bootstrap.Tooltip(el);
                    });
                }, 500);
            },
            error: function(xhr) {
                console.error('Error:', xhr);
            }
        });
    }

    function custom_search(_this) {
        var searchTerm = $('#searchInput').val();
        performSearch(searchTerm);
    }

    $(document).on('click','#reset_filters',function(){
        // Reset single-selects
        $('#prod_category, #product_status, #product_ranging').val(null).trigger('change');
        // Reset multi-select (tags)
        $('#product_tags').val([]).trigger('change');
        var searchTerm = $('#searchInput').val();
        performSearch(searchTerm);
    })
</script>
@endpush