@extends('backend.master', [
'pageTitle' => 'Raw Materials',
'activeMenu' => [
'item' => 'Raw Materials',
'subitem' => 'Raw Materials',
'additional' => '',
],
'breadcrumbItems' => [
['label' => 'Data Entry', 'url' => '#'],
['label' => 'Product']
],
])

@push('styles')
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
            <h1 class="page-title">Raw Materials</h1>
            <div class="right-side content d-flex flex-row-reverse align-items-center">
                @if($user_role == 4)
                    @if($permission['status'] == true && $permission['page']['Resources - Raw Material'] == true)
                    <div class="text-end">
                        <a href="{{ route('add.raw-materials') }}" class="btn btn-primary-orange plus-icon" id="addProductBtn" title="Add Ingredient">
                            <i class="material-symbols-outlined">add</i>
                        </a>
                    </div>
                    <div class="Export-btn me-lg-4 me-3">
                        <div class="btn-group click-dropdown">
                            <a href="{{ route('ingredients.import') }}" class="btn btn-primary-orange plus-icon" title="Upload / Import data">
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
                                    <a class="dropdown-item" href="javascript:void(0)" data-url="{{env('APP_URL')}}/download/csv/ingredient" onclick="export_details(this)">Download as CSV</a>
                                </li>
                                <li>
                                    <a class="dropdown-item" id="download_excel" href="javascript:void(0)" data-url="{{env('APP_URL')}}/download/excel/ingredient" onclick="export_details(this)">Download as Excel</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                    @endif
                @else
                <div class="text-end">
                    <a href="{{ route('add.raw-materials') }}" class="btn btn-primary-orange plus-icon" id="addProductBtn" title="Add Ingredient">
                        <i class="material-symbols-outlined">add</i>
                    </a>
                </div>
                <div class="Export-btn me-lg-4 me-3">
                    <div class="btn-group click-dropdown">
                        <a href="{{ route('ingredients.import') }}" class="btn btn-primary-orange plus-icon" title="Upload / Import data">
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
                                <a class="dropdown-item" href="javascript:void(0)" data-url="{{env('APP_URL')}}/download/csv/ingredient" onclick="export_details(this)">Download as CSV</a>
                            </li>
                            <li>
                                <a class="dropdown-item" id="download_excel" href="javascript:void(0)" data-url="{{env('APP_URL')}}/download/excel/ingredient" onclick="export_details(this)">Download as Excel</a>
                            </li>
                        </ul>
                    </div>
                </div>
                @endif

                <div class="view-toggle me-lg-4 me-3">
                    <button id="listViewBtn" class="icon-primary-orange {{ $viewType == 'list' ? 'active' : '' }} px-3 me-0" title="List View">
                        <i class="material-symbols-outlined">list</i>
                    </button>
                    <button id="gridViewBtn" class="icon-primary-orange{{ $viewType == 'grid' ? 'active' : '' }} me-0 pe-3" title="Grid View">
                        <i class="material-symbols-outlined">grid_view</i>
                    </button>
                </div>
            </div>
        </div>
        <div class="search-filter d-flex justify-content-between align-items-center">
            <div class="d-flex gap-2 mb-3 mt-5">
                <form id="searchForm" style="width: 100%;" class="">
                    <div class="input-group prd-search-btn">
                        <input type="text" name="search" id="searchInput" class="form-control" placeholder="Search" value="{{ request('search') }}">
                        <input type="hidden" id="viewTypeInput" name="view" value="{{ $viewType }}">
                        <input type="hidden" id="clientID" name="view" value="{{ $clientID }}">
                        <input type="hidden" id="workspaceID" name="view" value="{{ $ws_id }}">
                        <button type="submit" class="icon-primary-orange">
                            <i class="material-symbols-outlined hidden">search</i>
                        </button>
                    </div>
                </form>
                @php 
                    use App\Models\{Ing_category,Ing_subcategory};
                    $category = Ing_category::all()->toArray();
                    $sub_category = Ing_subcategory::all()->toArray();
                    $statusArray = get_status_array();
                    $activeArray = get_active_array();
                @endphp
                <select name="cateA" id="cateA" class="form-select-menu p-2" onchange="raw_material_filter(this)">
                    <option value="" selected disabled>Select Category A</option>
                    @foreach($category as $cat)
                    <option value="{{$cat['CID']}}">{{$cat['name']}}</option>
                    @endforeach
                </select>
                <select name="cateB" id="cateB" class="form-select-menu p-2" onchange="raw_material_filter(this)">
                    <option value="" selected disabled>Select Category B</option>
                    @foreach($sub_category as $sub)
                    <option value="{{$sub['SCID']}}">{{$sub['name']}}</option>
                    @endforeach
                </select>
                <select name="rw_status" id="rw_status" class="form-select-menu p-2" onchange="raw_material_filter(this)">
                    <option value="" selected disabled>Select Status</option>
                    @foreach($statusArray as $status)
                    <option value="{{$status}}">{{$status}}</option>
                    @endforeach
                </select>
                <select name="rw_active" id="rw_active" class="form-select-menu p-2" onchange="raw_material_filter(this)">
                    <option value="" selected disabled>Select Active</option>
                    @foreach($activeArray as $key => $active)
                    <option value="{{$key}}">{{$active}}</option>
                    @endforeach
                </select>
            </div>

            <!-- <div>
                
            </div> -->

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
            @include('backend.ingredient.partials.product-view', ['lists' => $lists, 'viewType' => $viewType])
        </div>
        <div class="pagination justify-content-end" id="pagination">
            {{ $lists->appends(['perPage' => $perPage, 'view' => $viewType])->links() }}
        </div>
    </div>


    <!-- Modal -->
    <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <input type="hidden" id="export_url" value="{{route('export.raw-materials')}}">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="exampleModalLabel">Select Column</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="export_column">
                    <div class="form-check-temp p-1">
                        <input class="form-check-input" type="checkbox" value="" id="rawmaterialDefault">
                        <label class="form-check-label" for="rawmaterialDefault">ALL</label>
                    </div>
                    <div class="form-check-temp p-1">
                        <input class="form-check-input raw_material_check" type="checkbox" value="" id="SKU" checked disabled>
                        <label class="form-check-label" for="SKU">Ingredient SKU*</label>
                    </div>
                    <div class="form-check-temp p-1">
                        <input class="form-check-input raw_material_check" type="checkbox" value="" id="name_by_kitchen" checked disabled>
                        <label class="form-check-label" for="name_by_kitchen">Name by Kitchen</label>
                    </div>
                    <div class="form-check-temp p-1">
                        <input class="form-check-input raw_material_check" type="checkbox" value="" id="name_by_supplier">
                        <label class="form-check-label" for="name_by_supplier">Name by Supplier</label>
                    </div>
                    <div class="form-check-temp p-1">
                        <input class="form-check-input raw_material_check" type="checkbox" value="" id="status">
                        <label class="form-check-label" for="status">Status</label>
                    </div>
                    <div class="form-check-temp p-1">
                        <input class="form-check-input raw_material_check" type="checkbox" value="" id="is_active">
                        <label class="form-check-label" for="is_active">Active</label>
                    </div>
                    <div class="form-check-temp p-1">
                        <input class="form-check-input raw_material_check" type="checkbox" value="" id="gtin">
                        <label class="form-check-label" for="gtin">GTIN</label>
                    </div>
                    <div class="form-check-temp p-1">
                        <input class="form-check-input raw_material_check" type="checkbox" value="" id="supplier_code">
                        <label class="form-check-label" for="supplier_code">Supplier Code</label>
                    </div>
                    <div class="form-check-temp p-1">
                        <input class="form-check-input raw_material_check" type="checkbox" value="" id="supplier_name">
                        <label class="form-check-label" for="supplier_name">Supplier Name</label>
                    </div>
                    <div class="form-check-temp p-1">
                        <input class="form-check-input raw_material_check" type="checkbox" value="" id="category">
                        <label class="form-check-label" for="category">Category A</label>
                    </div>
                    <div class="form-check-temp p-1">
                        <input class="form-check-input raw_material_check" type="checkbox" value="" id="SCategory">
                        <label class="form-check-label" for="SCategory">Category B</label>
                    </div>
                    <div class="form-check-temp p-1">
                        <input class="form-check-input raw_material_check" type="checkbox" value="" id="ingr_list">
                        <label class="form-check-label" for="ingr_list">Ingredients List</label>
                    </div>
                    <div class="form-check-temp p-1">
                        <input class="form-check-input raw_material_check" type="checkbox" value="" id="allergens">
                        <label class="form-check-label" for="allergens">Allergens</label>
                    </div>
                    <div class="form-check-temp p-1">
                        <input class="form-check-input raw_material_check" type="checkbox" value="" id="price_per_item">
                        <label class="form-check-label" for="price_per_item">Price Per Item</label>
                    </div>
                    <div class="form-check-temp p-1">
                        <input class="form-check-input raw_material_check" type="checkbox" value="" id="unit_per_item">
                        <label class="form-check-label" for="unit_per_item">Units Per Item</label>
                    </div>
                    <div class="form-check-temp p-1">
                        <input class="form-check-input raw_material_check" type="checkbox" value="" id="ingr_unit">
                        <label class="form-check-label" for="ingr_unit">Ingredient Units</label>
                    </div>
                    <div class="form-check-temp p-1">
                        <input class="form-check-input raw_material_check" type="checkbox" value="" id="purchase_unit">
                        <label class="form-check-label" for="purchase_unit">Is Liquid (Yes/No)</label>
                    </div>

                    <div class="form-check-temp p-1">
                        <input class="form-check-input raw_material_check" type="checkbox" value="" id="price_per_kg">
                        <label class="form-check-label" for="price_per_kg">Price Per KG/L</label>
                    </div>

                    <div class="form-check-temp p-1">
                        <input class="form-check-input raw_material_check" type="checkbox" value="" id="country">
                        <label class="form-check-label" for="country">Country of Origin</label>
                    </div>

                    <div class="form-check-temp p-1">
                        <input class="form-check-input raw_material_check" type="checkbox" value="" id="aus_per">
                        <label class="form-check-label" for="aus_per">Australian %</label>
                    </div>

                    <div class="form-check-temp p-1">
                        <input class="form-check-input raw_material_check" type="checkbox" value="" id="spec_grav">
                        <label class="form-check-label" for="spec_grav">Specific Gravity</label>
                    </div>

                    <div class="form-check-temp p-1">
                        <input class="form-check-input raw_material_check" type="checkbox" value="" id="energy">
                        <label class="form-check-label" for="energy">Energy (kJ)</label>
                    </div>

                    <div class="form-check-temp p-1">
                        <input class="form-check-input raw_material_check" type="checkbox" value="" id="protein">
                        <label class="form-check-label" for="protein">Protein (g)</label>
                    </div>

                    <div class="form-check-temp p-1">
                        <input class="form-check-input raw_material_check" type="checkbox" value="" id="tot_fat">
                        <label class="form-check-label" for="tot_fat">Total Fat (g)</label>
                    </div>

                    <div class="form-check-temp p-1">
                        <input class="form-check-input raw_material_check" type="checkbox" value="" id="sat_fat">
                        <label class="form-check-label" for="sat_fat">Saturated Fat (g)</label>
                    </div>

                    <div class="form-check-temp p-1">
                        <input class="form-check-input raw_material_check" type="checkbox" value="" id="carb">
                        <label class="form-check-label" for="carb">Carbohydrate (g)</label>
                    </div>

                    <div class="form-check-temp p-1">
                        <input class="form-check-input raw_material_check" type="checkbox" value="" id="sugar">
                        <label class="form-check-label" for="sugar">Sugars (g)</label>
                    </div>

                    <div class="form-check-temp p-1">
                        <input class="form-check-input raw_material_check" type="checkbox" value="" id="sodium">
                        <label class="form-check-label" for="sodium">Sodium (mg)</label>
                    </div>

                    <div class="form-check-temp p-1">
                        <input class="form-check-input raw_material_check" type="checkbox" value="" id="shelf">
                        <label class="form-check-label" for="shelf">Shelf Life</label>
                    </div>

                    <div class="form-check-temp p-1">
                        <input class="form-check-input raw_material_check" type="checkbox" value="" id="desc">
                        <label class="form-check-label" for="desc">Description</label>
                    </div>
                    <div class="form-check-temp p-1">
                        <input class="form-check-input raw_material_check" type="checkbox" value="" id="spec_url">
                        <label class="form-check-label" for="spec_url">Supplier Spec URL</label>
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
<script src="{{ asset('assets') }}/js/ingredient.js"></script>
<script>
    $(document).ready(function() {
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
                url: "{{ route('ingredient.search') }}",
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

    function raw_material_filter(_this){
        let type = $(_this).attr('id');
        let selectValue = $(_this).val();

        let all_id = ['cateA','cateB','rw_status','rw_active'];
        
        all_id = $.grep(all_id, function(value) {
            return value !== type;
        });

        $.each(all_id, function(index,value) {
            $(`#${value}`).val('')
        });


        $.ajax({
            url: "{{ route('ingredient.filter') }}",
            method: 'GET',
            data: {
                'type': type,
                'selectValue': selectValue
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
</script>
@endpush