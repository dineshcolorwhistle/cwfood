@extends('backend.master', [
  'pageTitle' => 'Analytics Dashboard',
  'activeMenu' => ['item'=>'Analytics','sub'=>'Dashboard'],
])

@push('styles')
 <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet">
<style>
        :root {
            --primary-color: #28a745; /* Green for profit */
            --secondary-color: #6c757d;
            --secondary-primary-color: #007bff; /* Blue for revenue/price */
            --danger-color: #dc3545; /* Red for costs */
            --primary-dark-color: #0e112a;
            --dark-gray-font: #666666;
            --light-border-color: #d2d2d2;
            --white-color: #ffffff;
            --light-bg: #f8f9fa;
        }

        body {
            background-color: var(--light-bg);
            font-family: 'Inter', sans-serif;
        }
        
        .chart-component-subtitle {
            color: var(--dark-gray-font);
        }
        
        .widget {
            border: 1px solid var(--light-border-color);
            border-radius: 8px;
            padding: 24px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            margin-bottom: 20px;
            background-color: var(--white-color);
        }
        
        .kpi-card {
            background-color: var(--white-color);
            border: 1px solid var(--light-border-color);
            border-radius: 8px;
            padding: 15px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            margin-bottom: 5px;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        
        .kpi-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        
        .kpi-value {
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--primary-dark-color);
        }
        
        .kpi-label {
            font-size: 0.9rem;
            color: var(--dark-gray-font);
            margin-top: -5px;
        }
        
        .kpi-icon {
            font-size: 2.5rem;
        }
        
        .dashboard-toggle-btn {
            padding: 0.25rem 0.5rem;
            border: none;
            background-color: #fff;
            color: var(--secondary-primary-color);
            cursor: pointer;
            font-weight: 600;
            transition: background-color 0.2s, color 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.1rem;
            font-size: 0.75rem;
            border-radius: 0;
            border: 1px solid var(--secondary-primary-color);
        }
        
        .dashboard-toggle-btn:first-child {
            border-top-left-radius: 4px;
            border-bottom-left-radius: 4px;
        }
        
        .dashboard-toggle-btn:last-child {
            border-top-right-radius: 4px;
            border-bottom-right-radius: 4px;
        }
        
        .dashboard-toggle-btn:not(:last-child) {
            border-right: none;
        }
        
        .dashboard-toggle-btn.active {
            background-color: var(--secondary-primary-color);
            color: #fff;
        }
        
        .dashboard-toggle-btn .material-icons-outlined {
            font-size: 1rem;
        }
        
        #dashboard-loader {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 400px;
            font-size: 1.2rem;
            color: var(--dark-gray-font);
        }
        
        .table-responsive {
            background-color: var(--white-color);
            border-radius: 8px;
            overflow-x: auto;
            border: 1px solid var(--light-border-color);
        }
        
        .table thead th {
            background-color: var(--light-bg);
            color: var(--dark-gray-font);
            font-weight: 600;
            border-bottom: 2px solid var(--light-border-color);
            cursor: pointer;
            user-select: none;
        }

        .table thead th:hover {
            color: var(--primary-dark-color);
        }

        .table thead th .sort-icon {
            font-size: 1rem;
            vertical-align: middle;
            margin-left: 4px;
            opacity: 0.5;
        }
        
        .table tfoot tr.grandtotal {
            font-weight: bold;
            background-color: var(--light-bg);
            border-top: 1px solid var(--light-border-color);
        }
        
        .controls-bar {
            background-color: var(--white-color);
            border: 1px solid var(--light-border-color);
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1.5rem;
        }
        #controls-row .form-control {border: 1px solid var(--bs-dark-snow);height: 42px;}
        .form-switch{display: flex;align-items: center;gap: 5px;}
    </style>

@endpush

@section('content')
<div class="container-fluid">
   <!-- Dashboard Header -->
    
        <div class="card-header d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
            <div>
                <h1 class="page-title mb-0">Unit Economics Dashboard</h1>
                <p class="chart-component-subtitle mb-2 mb-md-0">
                    SKU Profitability Analysis
                </p>
            </div>
             <div class="d-flex align-items-center">
                <div class="form-check form-switch me-3">
                    <input class="form-check-input" type="checkbox" role="switch" id="unit-toggle-switch">
                    <label class="form-check-label" for="unit-toggle-switch" id="unit-toggle-label">$/kg</label>
                </div>
                <div class="d-flex">
                    <button type="button" class="dashboard-toggle-btn active" id="product-view-btn">
                        <span class="material-icons-outlined">inventory_2</span> SKU Breakdown
                    </button>
                    <button type="button" class="dashboard-toggle-btn" id="comparison-view-btn">
                        <span class="material-icons-outlined">compare_arrows</span> SKU Comparison
                    </button>
                </div>
                <button type="button" class="dashboard-toggle-btn ms-2" id="filter-toggle-btn" style="border-radius: 4px;">
                    <span class="material-icons-outlined">filter_list</span>
                </button>
            </div>
        </div>
    
        <div class="card-body">
            <!-- Loading/Error State -->
            <div id="dashboard-loader">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <span class="ms-3">Loading Dashboard Data...</span>
            </div>
            <div id="dashboard-error" class="alert alert-danger d-none" role="alert">
                <strong>Error!</strong> Could not load data. Please ensure 'data.csv' is in the same folder and try again.
            </div>
        
            <!-- Main Dashboard Content -->
            <div id="dashboard-content" class="d-none">
                <!-- Row 1: Controls -->
                <div class="row d-none" id="controls-row">
                    <div class="col-12">
                        <div class="controls-bar">
                            <div class="row g-3 align-items-end">
                                <div class="col-md-3">
                                    <label for="product-filter" class="form-label">Product Name</label>
                                    <input class="form-control" list="product-datalist" id="product-filter" placeholder="Type to search...">
                                    <datalist id="product-datalist"></datalist>
                                </div>
                                <div class="col-md-2">
                                    <label for="sku-filter" class="form-label">SKU</label>
                                    <input class="form-control" list="sku-datalist" id="sku-filter" placeholder="Type to search...">
                                    <datalist id="sku-datalist"></datalist>
                                </div>
                                <div class="col-md-3">
                                    <label for="category-filter" class="form-label">Category</label>
                                    <select class="form-select" id="category-filter">
                                        <option value="" selected>All Categories</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label for="tags-filter" class="form-label">Tags</label>
                                    <select class="form-select" id="tags-filter">
                                        <option value="" selected>All Tags</option>
                                    </select>
                                </div>
                                <div class="col-md-2 d-none">
                                    <label for="tag-filter" class="form-label">Tags</label>
                                    <input class="form-control" list="tag-datalist" id="tag-filter" placeholder="e.g. vegan, spicy...">
                                    <datalist id="tag-datalist"></datalist>
                                </div>
                                <div class="col-md-2">
                                    <button class="btn btn-secondary w-100" id="clear-filters-btn" title="Clear Filters">
                                        <span class="material-icons-outlined" style="font-size: 1.25rem; vertical-align: middle;">clear</span> Clear
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
        
                <!-- Row 2: KPIs -->
                <div class="row row-cols-1 row-cols-md-2 row-cols-xl-5 g-4">
                    <div class="col">
                        <div class="kpi-card h-100">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0"><span class="material-icons-outlined kpi-icon" style="color: var(--secondary-primary-color);">local_offer</span></div>
                                <div class="flex-grow-1 ms-3">
                                    <div id="avg-wholesale-price-kpi" class="kpi-value">$0.00</div>
                                    <div class="kpi-label">Avg. Wholesale Price <span class="unit-label-text">/ kg</span></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="kpi-card h-100">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0"><span class="material-icons-outlined kpi-icon" style="color: var(--danger-color);">payment</span></div>
                                <div class="flex-grow-1 ms-3">
                                    <div id="avg-cost-kpi" class="kpi-value">$0.00</div>
                                    <div class="kpi-label">Avg. Direct Cost <span class="unit-label-text">/ kg</span></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="kpi-card h-100">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0"><span class="material-icons-outlined kpi-icon" style="color: var(--primary-color);">account_balance_wallet</span></div>
                                <div class="flex-grow-1 ms-3">
                                    <div id="avg-mfr-margin-kpi" class="kpi-value">$0.00</div>
                                    <div class="kpi-label">Avg. Margin¹ <span class="unit-label-text">/ kg</span></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="kpi-card h-100">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0"><span class="material-icons-outlined kpi-icon" style="color: var(--primary-color);">pie_chart</span></div>
                                <div class="flex-grow-1 ms-3">
                                    <div id="avg-mfr-margin-percent-vs-whsl-kpi" class="kpi-value">0%</div>
                                    <div class="kpi-label">Avg. Margin %²</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="kpi-card h-100">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0"><span class="material-icons-outlined kpi-icon" style="color: var(--primary-color);">donut_small</span></div>
                                <div class="flex-grow-1 ms-3">
                                    <div id="avg-mfr-margin-percent-vs-rrp-kpi" class="kpi-value">0%</div>
                                    <div class="kpi-label">Avg. Margin % (vs RRP)³</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
        
                <!-- Row 3: Dynamic Content -->
                <div class="row mt-3">
                    <div class="col-12">
                        <!-- Product View -->
                        <div id="product-view" class="view-container">
                            <div class="widget">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h4 class="chart-component-subtitle mb-0">SKU Economics Breakdown</h4>
                                    <div class="d-flex align-items-center">
                                        <label for="items-per-page-select" class="form-label me-2 mb-0 text-nowrap">Show:</label>
                                        <select class="form-select form-select-sm" id="items-per-page-select" style="width: auto;">
                                            <option value="10">Top 10</option>
                                            <option value="20">Top 20</option>
                                            <option value="50">Top 50</option>
                                            <option value="100">Top 100</option>
                                            <option value="9999">All</option>
                                        </select>
                                    </div>
                                </div>
                                <div id="product-table-container">
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th data-sort="name" style="width:30%;">Product Name <span class="material-icons-outlined sort-icon"></span></th>
                                                    <th data-sort="sku">SKU <span class="material-icons-outlined sort-icon"></span></th>
                                                    <th class="text-end" data-sort="WholesalePrice">Wholesale Price <span class="unit-label-text-th">($/kg)</span> <span class="material-icons-outlined sort-icon"></span></th>
                                                    <th class="text-end" data-sort="Cost">Cost <span class="unit-label-text-th">($/kg)</span> <span class="material-icons-outlined sort-icon"></span></th>
                                                    <th class="text-end" data-sort="ManufacturerMargin">Margin¹ <span class="unit-label-text-th">($/kg)</span> <span class="material-icons-outlined sort-icon"></span></th>
                                                    <th class="text-end" data-sort="ManufacturerMarginPercentVsWholesale">Margin %² <span class="material-icons-outlined sort-icon"></span></th>
                                                    <th class="text-end" data-sort="RRP">RRP <span class="unit-label-text-th">($/kg)</span> <span class="material-icons-outlined sort-icon"></span></th>
                                                    <th class="text-end" data-sort="ManufacturerMarginPercentVsRRP">Margin % (vs RRP)³ <span class="material-icons-outlined sort-icon"></span></th>
                                                </tr>
                                            </thead>
                                            <tbody id="product-table-body"></tbody>
                                        </table>
                                    </div>
                                    <div class="mt-3">
                                        <small class="text-muted">
                                            <strong>Footnotes:</strong><br>
                                            ¹ Margin = Wholesale Price - Direct Cost<br>
                                            ² Margin % = Margin / Wholesale Price<br>
                                            ³ Margin % (vs RRP) = Margin / RRP<br>
                                            <em>All prices are pre-GST. Table can be toggled between per kg and per unit values.</em>
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
        
                        <!-- Comparison View -->
                        <div id="comparison-view" class="view-container d-none">
                            <div class="widget">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h4 class="chart-component-subtitle mb-0">Graphical SKU Ranking</h4>

                                    <div class="d-flex" style="gap: 20px;">
                                        <div>
                                            <label for="items-per-chart-select" class="me-2 form-label">Show:</label>
                                            <select id="items-per-chart-select" class="form-select-sm">
                                                <option value="10" selected>10</option>
                                                <option value="20">20</option>
                                                <option value="50">50</option>
                                                <option value="100">100</option>
                                                <option value="9999">All</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label for="rank-by-select" class="form-label me-2">Rank by:</label>
                                            <select class="form-select-sm" id="rank-by-select">
                                                <option value="ManufacturerMargin" selected>Margin ($)</option>
                                                <option value="WholesalePrice">Wholesale Price ($)</option>
                                                <option value="RRP">RRP ($)</option>
                                                <option value="Cost">Cost ($)</option>
                                                <option value="ManufacturerMarginPercentVsWholesale">Margin %</option>
                                                <option value="ManufacturerMarginPercentVsRRP">Margin % (vs RRP)</option>
                                            </select>
                                        </div>

                                    </div>
                                    
                                    
                                </div>
                                <div id="sku-ranking-chart"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
</div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <!-- JavaScript Logic -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {
    
            let allProductData = @json($products_analysis);
            let currentSort = { column: 'ManufacturerMarginPerKG', direction: 'desc' };
            let rankingChart;
            let displayUnit = 'kg'; // 'kg' or 'unit'
            let itemsPerPage = 10;
    
            const processCsvData = (data) => {
                const cleanHeader = (h) => h.trim().replace(/[^a-zA-Z0-9]/g, '');
                const headers = data[0];
                const headerMap = {
                    ProductName: 'name',
                    SKU: 'sku',
                    ProductCatagory: 'category', 
                    ProductTags: 'tags',
                    WholesalePricekg: 'WholesalePricePerKG',
                    Costkg: 'CostPerKG',
                    Margin1: 'ManufacturerMarginPerKG',
                    WholesaleMargin2: 'ManufacturerMarginPercentVsWholesale',
                    RRPexGST: 'RRPPerKG',
                    MarginvsRRP3: 'ManufacturerMarginPercentVsRRP',
                    WholesalePriceunit: 'wholesalePrice',
                    Costunit: 'cost',
                    Marginunit1: 'ManufacturerMarginPerUnit',
                    RRPexGSTunit: 'rrp'
                };
                
                const wholesaleMarginUnitIndex = headers.indexOf('Wholesale Margin 2', headers.indexOf('Wholesale Margin 2') + 1);
                const marginVsRrpUnitIndex = headers.indexOf('Margin % (vs RRP)3', headers.indexOf('Margin % (vs RRP)3') + 1);

                return data.slice(1).map(row => {
                    const p = {};
                    headers.forEach((header, i) => {
                        const cleanH = cleanHeader(header);
                        const key = headerMap[cleanH];
                        if (key) {
                            const isNumeric = !['name', 'sku', 'category', 'tags'].includes(key);
                            p[key] = isNumeric ? parseFloat(row[i] || 0) : row[i];
                        }
                    });
                    p.ManufacturerMarginPercentVsWholesalePerUnit = parseFloat(row[wholesaleMarginUnitIndex] || 0);
                    p.ManufacturerMarginPercentVsRRPPerUnit = parseFloat(row[marginVsRrpUnitIndex] || 0);
                    p.tags = typeof p.tags === 'string' ? p.tags.split(',').map(t => t.trim()) : [];
                    return p;
                }).filter(p => p.name);
            };
    
            // --- DOM ELEMENT REFERENCES ---
            const dashboardLoader = document.getElementById('dashboard-loader');
            const dashboardError = document.getElementById('dashboard-error');
            const dashboardContent = document.getElementById('dashboard-content');
            const controlsRow = document.getElementById('controls-row');
            const productViewBtn = document.getElementById('product-view-btn');
            const comparisonViewBtn = document.getElementById('comparison-view-btn');
            const productView = document.getElementById('product-view');
            const comparisonView = document.getElementById('comparison-view');
            const productFilter = document.getElementById('product-filter');
            const skuFilter = document.getElementById('sku-filter');
            const categoryFilter = document.getElementById('category-filter');
            const tagsFilter = document.getElementById('tags-filter');
            const tagFilter = document.getElementById('tag-filter');
            const clearFiltersBtn = document.getElementById('clear-filters-btn');
            const filterToggleBtn = document.getElementById('filter-toggle-btn');
            const rankBySelect = document.getElementById('rank-by-select');
            const unitToggleSwitch = document.getElementById('unit-toggle-switch');
            const itemsPerPageSelect = document.getElementById('items-per-page-select');
            const itemsPerChartSelect = document.getElementById('items-per-chart-select');

            
            // --- UTILITY FUNCTIONS ---
            const formatCurrency = (amount) => new Intl.NumberFormat('en-AU', { style: 'currency', currency: 'AUD' }).format(amount || 0);
            const formatPercent = (value) => `${((value || 0) * 100).toFixed(1)}%`;

            // --- RENDER FUNCTIONS ---
            const renderProductTable = (productData) => {
                const tableBody = document.getElementById('product-table-body');
                
                const getSortKey = (baseKey) => {
                    if (baseKey.includes('PercentVsWholesale')) return displayUnit === 'kg' ? 'ManufacturerMarginPercentVsWholesale' : 'ManufacturerMarginPercentVsWholesalePerUnit';
                    if (baseKey.includes('PercentVsRRP')) return displayUnit === 'kg' ? 'ManufacturerMarginPercentVsRRP' : 'ManufacturerMarginPercentVsRRPPerUnit';

                    if (displayUnit === 'kg') {
                        const keyMap = { 'WholesalePrice': 'WholesalePricePerKG', 'Cost': 'CostPerKG', 'ManufacturerMargin': 'ManufacturerMarginPerKG', 'RRP': 'RRPPerKG', 'name': 'name', 'sku': 'sku' };
                         return keyMap[baseKey] || baseKey;
                    } else {
                         const keyMap = { 'WholesalePrice': 'wholesalePrice', 'Cost': 'cost', 'ManufacturerMargin': 'ManufacturerMarginPerUnit', 'RRP': 'rrp', 'name': 'name', 'sku': 'sku' };
                        return keyMap[baseKey] || baseKey;
                    }
                };

                const sortKey = getSortKey(currentSort.column);

                productData.sort((a, b) => {
                    const valA = a[sortKey];
                    const valB = b[sortKey];
                    const direction = currentSort.direction === 'asc' ? 1 : -1;
                    if (typeof valA === 'string') return valA.localeCompare(valB) * direction;
                    if (valA < valB) return -1 * direction;
                    if (valA > valB) return 1 * direction;
                    return 0;
                });
                
                document.querySelectorAll('#product-table-container thead th').forEach(th => {
                    const icon = th.querySelector('.sort-icon');
                    if (icon) {
                        if (th.dataset.sort === currentSort.column) {
                            icon.textContent = currentSort.direction === 'asc' ? 'arrow_upward' : 'arrow_downward';
                            icon.style.opacity = '1';
                        } else {
                            icon.textContent = '';
                             icon.style.opacity = '0.5';
                        }
                    }
                });

                const paginatedData = productData.slice(0, itemsPerPage);

                let bodyContent = '';
                if (paginatedData.length === 0) {
                    bodyContent = '<tr><td colspan="8" class="text-center p-5 text-muted">No products found for the selected filters.</td></tr>';
                } else {
                    paginatedData.forEach(p => {
                         const wholesalePrice = displayUnit === 'kg' ? p.WholesalePricePerKG : p.wholesalePrice;
                         const cost = displayUnit === 'kg' ? p.CostPerKG : p.cost;
                         const margin = displayUnit === 'kg' ? p.ManufacturerMarginPerKG : p.ManufacturerMarginPerUnit;
                         const marginPercent = displayUnit === 'kg' ? p.ManufacturerMarginPercentVsWholesale : p.ManufacturerMarginPercentVsWholesalePerUnit;
                         const rrp = displayUnit === 'kg' ? p.RRPPerKG : p.rrp;
                         const marginVsRRP = displayUnit === 'kg' ? p.ManufacturerMarginPercentVsRRP : p.ManufacturerMarginPercentVsRRPPerUnit;
                        
                        bodyContent += `
                            <tr>
                                <td>${p.name}</td>
                                <td>${p.sku}</td>
                                <td class="text-end">${formatCurrency(wholesalePrice)}</td>
                                <td class="text-end">${formatCurrency(cost)}</td>
                                <td class="text-end">${formatCurrency(margin)}</td>
                                <td class="text-end">${formatPercent(marginPercent)}</td>
                                <td class="text-end">${formatCurrency(rrp)}</td>
                                <td class="text-end">${formatPercent(marginVsRRP)}</td>
                            </tr>`;
                    });
                }
                tableBody.innerHTML = bodyContent;
            };
    
            const renderRankingChart = (productData) => {
                let rankBy = rankBySelect.value;
                
                if (displayUnit === 'kg') {
                    if(rankBy === 'WholesalePrice') rankBy = 'WholesalePricePerKG';
                    if(rankBy === 'Cost') rankBy = 'CostPerKG';
                    if(rankBy === 'ManufacturerMargin') rankBy = 'ManufacturerMarginPerKG';
                    if(rankBy === 'RRP') rankBy = 'RRPPerKG';
                } else {
                    if(rankBy === 'WholesalePrice') rankBy = 'wholesalePrice';
                    if(rankBy === 'Cost') rankBy = 'cost';
                    if(rankBy === 'ManufacturerMargin') rankBy = 'ManufacturerMarginPerUnit';
                    if(rankBy === 'ManufacturerMarginPercentVsWholesale') rankBy = 'ManufacturerMarginPercentVsWholesalePerUnit';
                    if(rankBy === 'ManufacturerMarginPercentVsRRP') rankBy = 'ManufacturerMarginPercentVsRRPPerUnit';
                    if(rankBy === 'RRP') rankBy = 'rrp';
                }

                const sortedData = [...productData].sort((a,b) => b[rankBy] - a[rankBy]).slice(0, itemsPerPage);
                const seriesData = sortedData.map(p => {
                    const val = p[rankBy];
                    return rankBy.includes('Percent') ? (val * 100).toFixed(1) : val;
                });

                const options = {
                    series: [{ name: rankBy, data: seriesData }],
                    chart: { type: 'bar', height: Math.max(400, 40 * sortedData.length), toolbar: {show: false} },
                    plotOptions: { bar: { horizontal: true, borderRadius: 4, barHeight: '70%' } },
                    xaxis: { 
                        categories: sortedData.map(p => p.name), 
                        labels: { 
                            formatter: val => (rankBy.includes('Percent') ? `${val}%` : formatCurrency(val)),
                            style: { fontSize: '10px' }
                        }
                    },
                    yaxis: { labels: { maxWidth: 450, style: { fontSize: '12px'} } },
                    tooltip: { 
                        y: { formatter: (val) => (rankBy.includes('Percent') ? `${val}%` : formatCurrency(val)) }
                    },
                    colors: [getComputedStyle(document.documentElement).getPropertyValue('--primary-color').trim()],
                    dataLabels: { 
                        enabled: true,
                        formatter: (val) => (rankBy.includes('Percent') ? `${val}%` : formatCurrency(val)),
                        style: { colors: ['#333'] },
                        offsetX: 25
                    },
                    grid: { borderColor: '#f1f1f1' }
                };

                if (rankingChart) {
                    rankingChart.updateOptions(options);
                } else {
                    rankingChart = new ApexCharts(document.querySelector("#sku-ranking-chart"), options);
                    rankingChart.render();
                }
            };
    
            const updateLabelsAndHeaders = () => {
                const unitText = displayUnit === 'kg' ? '/ kg' : '/ Unit';
                const unitTextTh = displayUnit === 'kg' ? '($/kg)' : '($/Unit)';

                document.querySelectorAll('.unit-label-text').forEach(el => el.textContent = unitText);
                document.querySelectorAll('.unit-label-text-th').forEach(el => el.textContent = unitTextTh);
                document.getElementById('unit-toggle-label').textContent = displayUnit === 'kg' ? '$/kg' : '$/Unit';

                 const rankOptions = rankBySelect.options;
                 for(let i=0; i < rankOptions.length; i++) {
                    if (rankOptions[i].value.includes("Price") || rankOptions[i].value.includes("Cost") || rankOptions[i].value.includes("Margin") && !rankOptions[i].value.includes("Percent") || rankOptions[i].value.includes("RRP")) {
                        rankOptions[i].text = rankOptions[i].text.split(' (')[0] + ` (${unitText.replace('/ ','')})`;
                    }
                 }
            };

            const processAndRender = (products) => {
                updateLabelsAndHeaders();
                const totalProducts = products.length;
                if (totalProducts > 0) {
                     const avgWholesalePrice = products.reduce((sum, p) => sum + (displayUnit === 'kg' ? p.WholesalePricePerKG : p.wholesalePrice), 0) / totalProducts;
                     const avgCost = products.reduce((sum, p) => sum + (displayUnit === 'kg' ? p.CostPerKG : p.cost), 0) / totalProducts;
                     const avgMfrMargin = products.reduce((sum, p) => sum + (displayUnit === 'kg' ? p.ManufacturerMarginPerKG : p.ManufacturerMarginPerUnit), 0) / totalProducts;
                     const avgMfrMarginPercentVsWhsl = products.reduce((sum, p) => sum + (displayUnit === 'kg' ? p.ManufacturerMarginPercentVsWholesale : p.ManufacturerMarginPercentVsWholesalePerUnit), 0) / totalProducts;
                     const avgMfrMarginPercentVsRRP = products.reduce((sum, p) => sum + (displayUnit === 'kg' ? p.ManufacturerMarginPercentVsRRP : p.ManufacturerMarginPercentVsRRPPerUnit), 0) / totalProducts;

                    document.getElementById('avg-wholesale-price-kpi').textContent = formatCurrency(avgWholesalePrice);
                    document.getElementById('avg-cost-kpi').textContent = formatCurrency(avgCost);
                    document.getElementById('avg-mfr-margin-kpi').textContent = formatCurrency(avgMfrMargin);
                    document.getElementById('avg-mfr-margin-percent-vs-whsl-kpi').textContent = formatPercent(avgMfrMarginPercentVsWhsl);
                    document.getElementById('avg-mfr-margin-percent-vs-rrp-kpi').textContent = formatPercent(avgMfrMarginPercentVsRRP);
                } else {
                    document.getElementById('avg-wholesale-price-kpi').textContent = formatCurrency(0);
                    document.getElementById('avg-cost-kpi').textContent = formatCurrency(0);
                    document.getElementById('avg-mfr-margin-kpi').textContent = formatCurrency(0);
                    document.getElementById('avg-mfr-margin-percent-vs-whsl-kpi').textContent = formatPercent(0);
                    document.getElementById('avg-mfr-margin-percent-vs-rrp-kpi').textContent = formatPercent(0);
                }
                
                renderProductTable(products);
                renderRankingChart(products);
            };
            
            const applyFiltersAndRender = () => {
                const productSearchTerm = productFilter.value.toLowerCase();
                const skuSearchTerm = skuFilter.value.toLowerCase();
                const selectedCategory = categoryFilter.value;
                const selectedTags = tagsFilter.value;
                const tagSearchTerm = tagFilter.value.toLowerCase();
                const filteredData = allProductData.filter(p => {                    
                    const productMatch = !productSearchTerm || p.name.toLowerCase().includes(productSearchTerm);
                    const skuMatch = !skuSearchTerm || p.sku.toLowerCase().includes(skuSearchTerm);
                    const categoryMatch = !selectedCategory || p.category === selectedCategory;
                    const tagsMatch = !selectedTags || (Array.isArray(p.tags) && p.tags.some(tag => tag.toLowerCase() === selectedTags.toLowerCase()));
                    const tagMatch = !tagSearchTerm || p.tags.some(tag => tag.toLowerCase().includes(tagSearchTerm));
                    return productMatch && skuMatch && categoryMatch && tagMatch && tagsMatch;
                });
                
                processAndRender(filteredData);
            };
    
            const populateFilters = (products) => {
                const productDatalist = document.getElementById('product-datalist');
                const skuDatalist = document.getElementById('sku-datalist');
                const categoryFilter = document.getElementById('category-filter');
                const tagsFilter = document.getElementById('tags-filter');
                const tagDatalist = document.getElementById('tag-datalist');
                
                productDatalist.innerHTML = '';
                skuDatalist.innerHTML = '';
                tagDatalist.innerHTML = '';
                categoryFilter.innerHTML = '<option value="" selected>All Categories</option>';
                tagsFilter.innerHTML = '<option value="" selected>All Tags</option>';
    
                const productNames = [...new Set(products.map(p => p.name))].sort();
                const skuCodes = [...new Set(products.map(p => p.sku))].sort();
                const categories = [...new Set(products.map(p => p.category).filter(c => c))].sort();
                const tags = [...new Set(products.flatMap(p => p.tags).filter(t => t))].sort();
    
                productNames.forEach(name => productDatalist.innerHTML += `<option value="${name}">`);
                skuCodes.forEach(code => skuDatalist.innerHTML += `<option value="${code}">`);
                categories.forEach(cat => categoryFilter.innerHTML += `<option value="${cat}">${cat}</option>`);
                tags.forEach(tag => tagsFilter.innerHTML += `<option value="${tag}">${tag}</option>`);
                tags.forEach(tag => tagDatalist.innerHTML += `<option value="${tag}">`);
            };
            
            const setMainView = (view) => {
                productView.classList.toggle('d-none', view !== 'product');
                comparisonView.classList.toggle('d-none', view !== 'comparison');
                productViewBtn.classList.toggle('active', view === 'product');
                comparisonViewBtn.classList.toggle('active', view === 'comparison');
            };
    
            const initializeDashboard = async () => {
                try {
                    // const data = await new Promise((resolve, reject) => {
                    //     Papa.parse('data.csv', {
                    //         download: true,
                    //         header: false,
                    //         skipEmptyLines: true,
                    //         complete: (results) => resolve(results.data),
                    //         error: (error) => reject(error),
                    //     });
                    // });

                    // allProductData = processCsvData(data);
                    // if (allProductData.length === 0) {
                    //     throw new Error("No valid data rows found in CSV.");
                    // }
                    
                    dashboardLoader.classList.add('d-none');
                    dashboardContent.classList.remove('d-none');
                    
                    populateFilters(allProductData);
                    applyFiltersAndRender();
        
                    // --- Setup Event Listeners ---
                    productFilter.addEventListener('input', applyFiltersAndRender);
                    skuFilter.addEventListener('input', applyFiltersAndRender);
                    categoryFilter.addEventListener('change', applyFiltersAndRender);
                    tagsFilter.addEventListener('change', applyFiltersAndRender);
                    tagFilter.addEventListener('input', applyFiltersAndRender);
                    clearFiltersBtn.addEventListener('click', () => {
                        productFilter.value = '';
                        skuFilter.value = '';
                        categoryFilter.value = '';
                        tagsFilter.value = '';
                        tagFilter.value = '';
                        applyFiltersAndRender();
                    });
                    filterToggleBtn.addEventListener('click', () => controlsRow.classList.toggle('d-none'));
                    productViewBtn.addEventListener('click', () => setMainView('product'));
                    comparisonViewBtn.addEventListener('click', () => setMainView('comparison'));
                    rankBySelect.addEventListener('change', applyFiltersAndRender);
                    itemsPerPageSelect.addEventListener('change', (e) => {
                        itemsPerPage = parseInt(e.target.value, 10);
                        applyFiltersAndRender();
                    });
                    itemsPerChartSelect.addEventListener('change', (e) => {
                        itemsPerPage = parseInt(e.target.value, 10);
                        applyFiltersAndRender();
                    });

                    unitToggleSwitch.addEventListener('change', (e) => {
                        displayUnit = e.target.checked ? 'unit' : 'kg';
                        applyFiltersAndRender();
                    });
    
                    document.querySelectorAll('#product-table-container thead th').forEach(th => {
                        th.addEventListener('click', () => {
                            const column = th.dataset.sort;
                            if (!column) return;
                            
                            const isAsc = currentSort.column === column && currentSort.direction === 'asc';
                            currentSort = { column: column, direction: isAsc ? 'desc' : 'asc' };
                            applyFiltersAndRender();
                        });
                    });

                } catch (error) {
                    console.error("Dashboard Initialization Error:", error);
                    dashboardLoader.classList.add('d-none');
                    dashboardContent.classList.add('d-none');
                    dashboardError.classList.remove('d-none');
                }
            };
            
            initializeDashboard();
        });



    </script>
@endpush