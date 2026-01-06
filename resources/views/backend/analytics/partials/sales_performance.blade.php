@extends('backend.master', [
  'pageTitle' => 'Xero Sales Performance',
  'activeMenu' => ['item'=>'Analytics','sub'=>'Xero']
])

@section('content')
<div class="card-header">
        <div class="title-add">
            <h1 class="page-title">Sales Performance</h1>
        </div>


    <div id="sales-perf">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    @push('styles')
    <style>
        :root{
        --bb-border:    var(--bs-border-color, #d2d2d2);
        --bb-surface:   var(--bs-body-bg, #fff);
        --bb-muted:     var(--bs-secondary-color, #666);
        --bb-surface-2: var(--bs-tertiary-bg, #f8f9fa);
        }
        #sales-perf .chart-component-subtitle{ color: var(--bb-muted); }
        #sales-perf .widget, #sales-perf .kpi-card, #sales-perf .table-responsive, #sales-perf .item-card{
        background: var(--bb-surface); border:1px solid var(--bb-border); border-radius:8px; box-shadow:0 1px 3px rgba(0,0,0,.05);
        }
        #sales-perf .widget{ padding:24px; margin-bottom:20px; }
        #sales-perf .kpi-card{ padding:24px; margin-bottom:20px; }
        #sales-perf .kpi-value{ font-size:2rem; font-weight:700; color:#0e112a; }
        #sales-perf .kpi-label{ color: var(--bb-muted); }
        #sales-perf .kpi-icon{ font-size:2rem; color:#ec5934; }
        #sales-perf .kpi-chart{ height:50px; margin-top:1rem; }
        #sales-perf .neg{ color:#c53030; }

        /* Status pill (top-right) */
        #sales-perf .status-pill{
        font-size:.75rem; padding:.25rem .5rem; border-radius:999px; border:1px solid var(--bb-border);
        background: var(--bb-surface-2); color:#444;
        }
        #sales-perf .status-pill.loading{ color:#0b5ed7; border-color:#0b5ed7; }
        #sales-perf .status-pill.ready{ color:#198754; border-color:#198754; }

        /* Cards */
        #sales-perf .item-card{ padding:1rem; box-sizing:border-box; }
        #sales-perf .item-name{ font-weight:700; padding-bottom:.25rem; overflow-wrap:anywhere; word-break:break-word; line-height:1.15; }
        #sales-perf .mini{ font-size:.85rem; color:var(--bb-muted); }
        #sales-perf .badge-credit{ display:inline-block; padding:.1rem .4rem; font-size:.7rem; border:1px solid #c53030; color:#c53030; border-radius:999px; margin-left:.35rem; }

        /* Layout helpers */
        #sales-perf .section-toolbar{ display:flex; gap:.5rem; align-items:center; margin-bottom:12px; }
        #sales-perf .card-grid{ display:grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap:12px; }

        /* Filters */
        #sales-perf #controls-row .form-control,
        #sales-perf #controls-row .form-control-sm,
        #sales-perf #controls-row .form-select-sm{
        border:1px solid var(--bb-border) !important;
        box-shadow:none;
        }
        @media (min-width: 992px){
        #sales-perf .period-inline{ display:flex; align-items:center; gap:.5rem; }
        #sales-perf .period-inline .form-control-sm{ width:auto; }
        }

        /* View-specific KPI rows toggling */
        #kpi-trend, #kpi-customers, #kpi-skus { display:none; }
        .show-kpi-trend     #kpi-trend { display:block; }
        .show-kpi-customers #kpi-customers { display:block; }
        .show-kpi-skus      #kpi-skus { display:block; }

        /* Trend "Show" toolbar alignment */
        .trend-toolbar{ display:flex; justify-content:flex-end; align-items:center; gap:.5rem; margin-bottom:.5rem; }
        .trend-toolbar label{ margin:0; }
    </style>
    @endpush

    <div class="container-fluid p-0">
        {{-- Header --}}
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
        <p class="chart-component-subtitle mb-2 mb-md-0">
            <span id="dashboard-date-range" class="fw-bold"></span>
        </p>

        <div class="d-flex align-items-center gap-2 flex-wrap justify-content-end" style="row-gap:.4rem">
            {{-- STATUS BADGE --}}
            <span id="status-pill" class="status-pill loading">Loading…</span>

            {{-- Tenant (no “All Tenants”) --}}
            <div id="tenant-filter-container" class="me-2 d-none">
            <select id="tenant-filter" class="form-select form-select-sm"></select>
            </div>

            {{-- View buttons (Trend first) --}}
            <div class="d-flex gap-2">
            <button type="button" class="btn btn-secondary btn-sm" id="trend-view-btn">
                <span class="material-icons-outlined" style="font-size:16px">show_chart</span> Trend
            </button>
            <button type="button" class="btn btn-secondary btn-sm" id="customer-view-btn">
                <span class="material-icons-outlined" style="font-size:16px">people</span> Customers
            </button>
            <button type="button" class="btn btn-secondary btn-sm" id="sku-view-btn">
                <span class="material-icons-outlined" style="font-size:16px">inventory_2</span> SKUs
            </button>
            </div>

            {{-- Filters toggle --}}
            <button type="button" class="btn btn-secondary btn-sm" id="filter-toggle-btn" aria-expanded="false" title="Show/Hide filters">
            <span class="material-icons-outlined" style="font-size:16px">filter_list</span>
            </button>
        </div>
        </div>

        {{-- Error --}}
        <div id="dashboard-error" class="alert alert-danger d-none" role="alert">
        <strong>Error!</strong> Could not load Xero Invoices data. Please try again later.
        </div>

        {{-- Content --}}
        <div id="dashboard-content" class="d-none">
        {{-- FILTERS --}}
        <div class="row d-none" id="controls-row">
            <div class="col-12">
            <div class="widget" style="padding:12px">
                <div class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">Customer</label>
                    <input class="form-control form-control-sm" list="customer-datalist" id="customer-filter" placeholder="Type to search…">
                    <datalist id="customer-datalist"></datalist>
                </div>

                <div class="col-md-3">
                    <label class="form-label">SKU (Item Code)</label>
                    <input class="form-control form-control-sm" list="sku-datalist" id="sku-filter" placeholder="Type to search…">
                    <datalist id="sku-datalist"></datalist>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Period</label>
                    <div class="period-inline">
                    <select id="period-select" class="form-select form-select-sm" style="width:auto">
                        <option value="last_3_months" selected>Last 3 Months</option>
                        <option value="last_month">Last Month</option>
                        <option value="current_month">Current Month</option>
                        <option value="last_7_days">Last 7 Days</option>
                        <option value="custom">Custom</option>
                    </select>
                    <input type="date" id="from-date" class="form-control form-control-sm d-none">
                    <input type="date" id="to-date" class="form-control form-control-sm d-none">
                    <button class="btn btn-secondary btn-sm ms-auto" id="clear-filters-btn" title="Clear Filters">
                        <span class="material-icons-outlined" style="font-size:16px">clear</span>
                    </button>
                    </div>
                </div>
                </div> <!-- /row -->
            </div>
            </div>
        </div>

        {{-- ===== KPI ROWS ===== --}}

        {{-- KPI: TREND (area sparklines) --}}
        <div id="kpi-trend">
            <div class="row row-cols-1 row-cols-md-3 row-cols-xl-6 g-3">
            <div class="col"><div class="kpi-card h-100"><div class="d-flex align-items-center"><div class="flex-shrink-0"><span class="material-icons-outlined kpi-icon">attach_money</span></div><div class="flex-grow-1 ms-3"><div id="total-sales-kpi" class="kpi-value">$0</div><div class="kpi-label">Total Sales (Net)</div></div></div><div id="total-sales-chart" class="kpi-chart"></div></div></div>
            <div class="col"><div class="kpi-card h-100"><div class="d-flex align-items-center"><div class="flex-shrink-0"><span class="material-icons-outlined kpi-icon">receipt_long</span></div><div class="flex-grow-1 ms-3"><div id="total-invoices-kpi" class="kpi-value">0</div><div class="kpi-label">Total Invoices</div></div></div><div id="total-invoices-chart" class="kpi-chart"></div></div></div>
            <div class="col"><div class="kpi-card h-100"><div class="d-flex align-items-center"><div class="flex-shrink-0"><span class="material-icons-outlined kpi-icon">assignment_return</span></div><div class="flex-grow-1 ms-3"><div id="total-credits-kpi" class="kpi-value">0</div><div class="kpi-label">Total Credit Notes</div></div></div><div id="total-credits-chart" class="kpi-chart"></div></div></div>
            <div class="col"><div class="kpi-card h-100"><div class="d-flex align-items-center"><div class="flex-shrink-0"><span class="material-icons-outlined kpi-icon">monetization_on</span></div><div class="ms-3 flex-grow-1"><div id="avg-sales-kpi" class="kpi-value">$0</div><div class="kpi-label">Avg. Sales / Invoice</div></div></div><div id="avg-sales-chart" class="kpi-chart"></div></div></div>
            <div class="col"><div class="kpi-card h-100"><div class="d-flex align-items-center"><div class="flex-shrink-0"><span class="material-icons-outlined kpi-icon">groups</span></div><div class="ms-3 flex-grow-1"><div id="unique-customers-kpi" class="kpi-value">0</div><div class="kpi-label">Active Customers</div></div></div><div id="unique-customers-chart" class="kpi-chart"></div></div></div>
            <div class="col"><div class="kpi-card h-100"><div class="d-flex align-items-center"><div class="flex-shrink-0"><span class="material-icons-outlined kpi-icon">style</span></div><div class="ms-3 flex-grow-1"><div id="unique-skus-kpi" class="kpi-value">0</div><div class="kpi-label">Active SKUs</div></div></div><div id="unique-skus-chart" class="kpi-chart"></div></div></div>
            </div>
        </div>

        {{-- KPI: CUSTOMERS (icons + numbers only) --}}
        <div id="kpi-customers">
            <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-4 g-3">
            <div class="col"><div class="kpi-card h-100 d-flex"><div class="flex-shrink-0"><span class="material-icons-outlined kpi-icon">supervisor_account</span></div><div class="ms-3"><div id="cust-total-kpi" class="kpi-value">0</div><div class="kpi-label">Total Customers</div></div></div></div>
            <div class="col"><div class="kpi-card h-100 d-flex"><div class="flex-shrink-0"><span class="material-icons-outlined kpi-icon">groups</span></div><div class="ms-3"><div id="cust-active-kpi" class="kpi-value">0</div><div class="kpi-label">Active</div></div></div></div>
            <div class="col"><div class="kpi-card h-100 d-flex"><div class="flex-shrink-0"><span class="material-icons-outlined kpi-icon">person_add_alt</span></div><div class="ms-3"><div id="cust-new-kpi" class="kpi-value">0</div><div class="kpi-label">New</div></div></div></div>
            <div class="col"><div class="kpi-card h-100 d-flex"><div class="flex-shrink-0"><span class="material-icons-outlined kpi-icon">history</span></div><div class="ms-3"><div id="cust-repeat-kpi" class="kpi-value">0</div><div class="kpi-label">Repeat</div></div></div></div>
            </div>
        </div>

        {{-- KPI: SKUs (icons + numbers only) --}}
        <div id="kpi-skus">
            <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-4 g-3">
            <div class="col"><div class="kpi-card h-100 d-flex"><div class="flex-shrink-0"><span class="material-icons-outlined kpi-icon">style</span></div><div class="ms-3"><div id="sku-total-kpi" class="kpi-value">0</div><div class="kpi-label">Total SKUs</div></div></div></div>
            <div class="col"><div class="kpi-card h-100 d-flex"><div class="flex-shrink-0"><span class="material-icons-outlined kpi-icon">inventory_2</span></div><div class="ms-3"><div id="sku-active-kpi" class="kpi-value">0</div><div class="kpi-label">Active</div></div></div></div>
            <div class="col"><div class="kpi-card h-100 d-flex"><div class="flex-shrink-0"><span class="material-icons-outlined kpi-icon">add_shopping_cart</span></div><div class="ms-3"><div id="sku-new-kpi" class="kpi-value">0</div><div class="kpi-label">New</div></div></div></div>
            <div class="col"><div class="kpi-card h-100 d-flex"><div class="flex-shrink-0"><span class="material-icons-outlined kpi-icon">repeat</span></div><div class="ms-3"><div id="sku-repeat-kpi" class="kpi-value">0</div><div class="kpi-label">Repeat</div></div></div></div>
            </div>
        </div>

        {{-- ===== VIEWS ===== --}}
        <div class="row mt-3">
            <div class="col-12">

            {{-- Customers --}}
            <div id="customer-view" class="view-container d-none">
                <div class="widget">
                <div class="d-flex justify-content-between align-items-center">
                    <h4 class="chart-component-subtitle mb-3">Top Customers by Sales</h4>
                    <div class="section-toolbar">
                    <label class="me-1 mini">Show</label>
                    <select id="cust-show-select" class="form-select form-select-sm" style="width:auto">
                        <option value="20" selected>Top 20</option>
                        <option value="50">Top 50</option>
                        <option value="100">Top 100</option>
                        <option value="all">All</option>
                    </select>
                    <button class="btn btn-secondary btn-sm active" id="cust-table-btn"><span class="material-icons-outlined" style="font-size:16px">table_chart</span> Table</button>
                    <button class="btn btn-secondary btn-sm" id="cust-card-btn"><span class="material-icons-outlined" style="font-size:16px">grid_view</span> Cards</button>
                    </div>
                </div>

                <div id="customer-table-container" class="table-responsive">
                    <table class="table table-hover">
                    <thead>
                        <tr>
                        <th>Customer Name</th>
                        <th class="text-end">Invoices</th>
                        <th class="text-end">Credit Notes</th>
                        <th class="text-end">Avg. Invoice Value</th>
                        <th class="text-end">Total Sales</th>
                        <th class="text-end">% of Total</th>
                        </tr>
                    </thead>
                    <tbody id="top-customers-table-body"></tbody>
                    <tfoot id="top-customers-table-foot"></tfoot>
                    </table>
                </div>

                <div id="customer-card-container" class="card-grid d-none"></div>
                </div>
            </div>

            {{-- SKUs --}}
            <div id="sku-view" class="view-container d-none">
                <div class="widget">
                <div class="d-flex justify-content-between align-items-center">
                    <h4 class="chart-component-subtitle mb-3">Top SKUs by Sales</h4>
                    <div class="section-toolbar">
                    <label class="me-1 mini">Show</label>
                    <select id="sku-show-select" class="form-select form-select-sm" style="width:auto">
                        <option value="20" selected>Top 20</option>
                        <option value="50">Top 50</option>
                        <option value="100">Top 100</option>
                        <option value="all">All</option>
                    </select>
                    <button class="btn btn-secondary btn-sm active" id="sku-table-btn"><span class="material-icons-outlined" style="font-size:16px">table_chart</span> Table</button>
                    <button class="btn btn-secondary btn-sm" id="sku-card-btn"><span class="material-icons-outlined" style="font-size:16px">grid_view</span> Cards</button>
                    </div>
                </div>

                <div id="sku-table-container" class="table-responsive">
                    <table class="table table-hover">
                    <thead>
                        <tr>
                        <th>SKU / Item Code</th>
                        <th>Description</th>
                        <th class="text-end">Qty Sold</th>
                        <th class="text-end">Total Sales</th>
                        <th class="text-end">% of Total</th>
                        </tr>
                    </thead>
                    <tbody id="top-skus-table-body"></tbody>
                    <tfoot id="top-skus-table-foot"></tfoot>
                    </table>
                </div>

                <div id="sku-card-container" class="card-grid d-none"></div>
                </div>
            </div>

            {{-- TREND --}}
            <div id="trend-view" class="view-container">
                <div class="d-flex justify-content-between flex-wrap mb-2">
                <div></div>
                <div class="btn-group" role="group" aria-label="Trend Granularity">
                    <button class="btn btn-secondary btn-sm active" id="trend-daily-btn">Daily</button>
                    <button class="btn btn-secondary btn-sm" id="trend-monthly-btn">Monthly</button>
                </div>
                </div>

                {{-- 2 line charts side-by-side --}}
                <div class="row">
                <div class="col-12 col-lg-6 mb-4">
                    <div class="widget h-100">
                    <h4 class="chart-component-subtitle mb-3">Total Sales</h4>
                    <div id="trend-sales-chart"></div>
                    </div>
                </div>
                <div class="col-12 col-lg-6 mb-4">
                    <div class="widget h-100">
                    <h4 class="chart-component-subtitle mb-3">Invoices & Credits</h4>
                    <div id="trend-docs-chart"></div>
                    </div>
                </div>
                </div>

                {{-- Shared Top bars --}}
                <div class="trend-toolbar">
                <label class="mini">Show</label>
                <select id="trend-top-show" class="form-select form-select-sm" style="width:auto">
                    <option value="20" selected>Top 20</option>
                    <option value="50">Top 50</option>
                    <option value="100">Top 100</option>
                    <option value="all">All</option>
                </select>
                </div>

                <div class="row">
                <div class="col-12 col-lg-6 mb-4">
                    <div class="widget h-100">
                    <h4 class="chart-component-subtitle mb-3">Top Customers by Sales</h4>
                    <div id="trend-top-customers"></div>
                    </div>
                </div>
                <div class="col-12 col-lg-6 mb-4">
                    <div class="widget h-100">
                    <h4 class="chart-component-subtitle mb-3">Top SKUs by Sales</h4>
                    <div id="trend-top-skus"></div>
                    </div>
                </div>
                </div>

                {{-- A/R Ageing + hover drill-in --}}
                <div class="row">
                <div class="col-12 mb-4">
                    <div class="widget h-100" id="trend-ar-widget">
                    <h4 class="chart-component-subtitle mb-3">A/R Ageing</h4>
                    <div id="trend-ar-chart"></div>
                    <div class="mt-3">
                        <h6 class="chart-component-subtitle mb-2" id="trend-ar-detail-title" style="display:none"></h6>
                        <div id="trend-ar-detail-chart" style="display:none"></div>
                    </div>
                    </div>
                </div>
                </div>

                {{-- Repeat vs New Customers --}}
                <div class="row">
                <div class="col-12 mb-4">
                    <div class="widget h-100">
                    <h4 class="chart-component-subtitle mb-3">Repeat vs New Customers</h4>
                    <div id="trend-repeat-chart"></div>
                    </div>
                </div>
                </div>
            </div>
            {{-- /TREND --}}
            </div>
        </div>
        </div>
    </div>

    @push('scripts')
    <script>
    document.addEventListener("DOMContentLoaded", async function () {
        /* ================= STATE ================= */
        const state = {
        initializing: true,
        tenantId: null,
        from: null, to: null,
        period: 'last_3_months', // default
        view: 'trend',
        trendGranularity: 'daily',
        allRows: [],
        monthlyRows: [],
        dailyRows: [],
        allTimeRows: [],
        _allTimeKey: null,
        filters: { customer:'', sku:'' },
        charts: {},
        display: { customers:'table', skus:'table' },
        showTop: { customers: 20, skus: 20 },
        trendShowBoth: 20,
        tenantsMeta: []
        };

        /* ================= ELs ================= */
        const root = document.getElementById('sales-perf');

        const els = {
        err:         document.getElementById('dashboard-error'),
        content:     document.getElementById('dashboard-content'),
        dateLabel:   document.getElementById('dashboard-date-range'),
        statusPill:  document.getElementById('status-pill'),

        tenantWrap:  document.getElementById('tenant-filter-container'),
        tenantSelect:document.getElementById('tenant-filter'),

        // Filters
        controlsRow: document.getElementById('controls-row'),
        filterBtn:   document.getElementById('filter-toggle-btn'),
        clearBtn:    document.getElementById('clear-filters-btn'),

        fCustomer:   document.getElementById('customer-filter'),
        fSku:        document.getElementById('sku-filter'),
        dlCustomer:  document.getElementById('customer-datalist'),
        dlSku:       document.getElementById('sku-datalist'),

        periodSelect: document.getElementById('period-select'),
        fromDate:     document.getElementById('from-date'),
        toDate:       document.getElementById('to-date'),

        // KPI groups
        kpiTrend:    document.getElementById('kpi-trend'),
        kpiCust:     document.getElementById('kpi-customers'),
        kpiSkus:     document.getElementById('kpi-skus'),

        // KPI numbers (Trend)
        kpiTotalSales:    document.getElementById('total-sales-kpi'),
        kpiTotalInvoices: document.getElementById('total-invoices-kpi'),
        kpiTotalCredits:  document.getElementById('total-credits-kpi'),
        kpiAvgSales:      document.getElementById('avg-sales-kpi'),
        kpiUniqueCust:    document.getElementById('unique-customers-kpi'),
        kpiUniqueSkus:    document.getElementById('unique-skus-kpi'),

        // Sparklines (Trend)
        sparkSales:    document.getElementById('total-sales-chart'),
        sparkInvoices: document.getElementById('total-invoices-chart'),
        sparkCredits:  document.getElementById('total-credits-chart'),
        sparkAvg:      document.getElementById('avg-sales-chart'),
        sparkCust:     document.getElementById('unique-customers-chart'),
        sparkSkus:     document.getElementById('unique-skus-chart'),

        // Customer/SKU summary KPIs
        custTotal:  document.getElementById('cust-total-kpi'),
        custActive: document.getElementById('cust-active-kpi'),
        custNew:    document.getElementById('cust-new-kpi'),
        custRepeat: document.getElementById('cust-repeat-kpi'),

        skuTotal:  document.getElementById('sku-total-kpi'),
        skuActive: document.getElementById('sku-active-kpi'),
        skuNew:    document.getElementById('sku-new-kpi'),
        skuRepeat: document.getElementById('sku-repeat-kpi'),

        // Views
        vCustomer:   document.getElementById('customer-view'),
        vSku:        document.getElementById('sku-view'),
        vTrend:      document.getElementById('trend-view'),

        // Table/Card containers & toggles
        custCardWrap: document.getElementById('customer-card-container'),
        skuCardWrap:  document.getElementById('sku-card-container'),
        custTableWrap:document.getElementById('customer-table-container'),
        skuTableWrap: document.getElementById('sku-table-container'),

        custBody:    document.getElementById('top-customers-table-body'),
        custFoot:    document.getElementById('top-customers-table-foot'),
        skuBody:     document.getElementById('top-skus-table-body'),
        skuFoot:     document.getElementById('top-skus-table-foot'),

        custTableBtn: document.getElementById('cust-table-btn'),
        custCardBtn:  document.getElementById('cust-card-btn'),
        skuTableBtn:  document.getElementById('sku-table-btn'),
        skuCardBtn:   document.getElementById('sku-card-btn'),

        custShowSelect: document.getElementById('cust-show-select'),
        skuShowSelect:  document.getElementById('sku-show-select'),

        // View buttons
        custBtn:     document.getElementById('customer-view-btn'),
        skuBtn:      document.getElementById('sku-view-btn'),
        trendBtn:    document.getElementById('trend-view-btn'),

        // Trend controls & charts
        trendDailyBtn:   document.getElementById('trend-daily-btn'),
        trendMonthlyBtn: document.getElementById('trend-monthly-btn'),
        tSales:          document.getElementById('trend-sales-chart'),
        tDocs:           document.getElementById('trend-docs-chart'),

        trendTopShow:        document.getElementById('trend-top-show'),
        trendTopCustomers:   document.getElementById('trend-top-customers'),
        trendTopSkus:        document.getElementById('trend-top-skus'),
        trendARWidget:       document.getElementById('trend-ar-widget'),
        trendAR:             document.getElementById('trend-ar-chart'),
        trendARDetailTitle:  document.getElementById('trend-ar-detail-title'),
        trendARDetailChart:  document.getElementById('trend-ar-detail-chart'),
        trendRepeat:         document.getElementById('trend-repeat-chart'),
        };

        const ROUTES = {
        options: @json(route('ana.xero.options')),
        data:    @json(route('ana.xero.sales_performance.data')),
        customer: @json(route('ana.xero.sales_performance.customer')),
        };

        /* ================= HELPERS ================= */
        function setStatus(loading){
        if(loading){
            els.statusPill.textContent = 'Loading…';
            els.statusPill.classList.add('loading');
            els.statusPill.classList.remove('ready');
        }else{
            els.statusPill.textContent = 'Ready';
            els.statusPill.classList.remove('loading');
            els.statusPill.classList.add('ready');
        }
        }

        function fmtLocal(d){
        const y=d.getFullYear(), m=String(d.getMonth()+1).padStart(2,'0'), day=String(d.getDate()).padStart(2,'0');
        return `${y}-${m}-${day}`;
        }
        const monthNames = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
        function labelMonthYYYYFromStr(isoYYYYMM){ const [y,m]=isoYYYYMM.split('-').map(n=>parseInt(n,10)); return `${monthNames[m-1]} ${y}`; }
        function labelDDMMYY(d){ const dd=String(d.getDate()).padStart(2,'0'); const mm=String(d.getMonth()+1).padStart(2,'0'); const yy=String(d.getFullYear()).slice(-2); return `${dd}-${mm}-${yy}`; }
        function moneyShort(n){ const v=Number(n||0), abs=Math.abs(v); if(abs>=1_000_000) return '$'+(v/1_000_000).toFixed(1).replace(/\.0$/,'')+'m'; if(abs>=1_000) return '$'+(v/1_000).toFixed(1).replace(/\.0$/,'')+'k'; return '$'+Math.round(v).toLocaleString(); }
        function intFmt(n){ return Number(n||0).toLocaleString(undefined,{maximumFractionDigits:0}); }
        function escapeHtml(s){ return (s??'').toString().replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }

        //function priorMonthRange(){ const today=new Date(); today.setHours(0,0,0,0); const end=today; const start=new Date(today.getFullYear(), today.getMonth()-1, 1); return {start,end}; }
        function priorMonthRange() {const today = new Date();today.setHours(0, 0, 0, 0);const start = new Date(today.getFullYear(), today.getMonth() - 1, 1);const end = new Date(today.getFullYear(), today.getMonth(), 0);return { start, end };}
        function currentMonthToDate(){ const today=new Date(); today.setHours(0,0,0,0); const start=new Date(today.getFullYear(), today.getMonth(), 1); return {start, end:today}; }
        function last3Months(){ const today=new Date(); today.setHours(0,0,0,0); const end=today; const start=new Date(today.getFullYear(), today.getMonth()-2, 1); return {start, end}; }
        function last7Days(){ const today=new Date(); today.setHours(0,0,0,0); const end=today; const start=new Date(today); start.setDate(start.getDate()-6); return {start, end}; }

        function computePeriodRange(value){
        if(value==='last_3_months') return last3Months();
        if(value==='last_month')    return priorMonthRange();
        if(value==='current_month') return currentMonthToDate();
        if(value==='last_7_days')   return last7Days();
        // custom
        const f = els.fromDate.value ? new Date(els.fromDate.value) : new Date();
        const t = els.toDate.value   ? new Date(els.toDate.value)   : new Date();
        f.setHours(0,0,0,0); t.setHours(0,0,0,0);
        return { start:f, end:t };
        }

        function calcBarHeight(n){ if(n==='all') return 480; const k=parseInt(n,10)||20; return Math.min(900, Math.max(260, 24*k + 80)); }

        async function getOptions(){ const res=await fetch(ROUTES.options,{headers:{'Accept':'application/json'}}); if(!res.ok) throw new Error('options HTTP '+res.status); return res.json(); }
        function populateTenantSelect(tenants){
        state.tenantsMeta = tenants || [];
        const sel=els.tenantSelect; sel.innerHTML='';
        tenants.forEach((t,i)=>{ const opt=document.createElement('option'); opt.value=t.id; opt.textContent=t.name; sel.appendChild(opt); if(i===0){ state.tenantId=t.id; sel.value=t.id; }});
        els.tenantWrap.classList.toggle('d-none', tenants.length<=1);
        }

        async function fetchRows(from,to){
        const q=new URLSearchParams();
        if(state.tenantId) q.append('tenant_id', state.tenantId);
        if(from) q.append('from',from); if(to) q.append('to',to);
        const res=await fetch(`${ROUTES.data}?${q.toString()}`, { headers:{'Accept':'application/json'} });
        if(!res.ok) throw new Error('data HTTP '+res.status);
        const json=await res.json();
        return Array.isArray(json) ? json : (json.data||[]);
        }

        async function fetchCustomer(from,to){
        const q=new URLSearchParams();
        if(state.tenantId) q.append('tenant_id', state.tenantId);
        if(from) q.append('from',from); if(to) q.append('to',to);
        const res=await fetch(`${ROUTES.customer}?${q.toString()}`, { headers:{'Accept':'application/json'} });
        if(!res.ok) throw new Error('data HTTP '+res.status);
        const json=await res.json();
        state.totalCustomers = json.totalContact ?? 0;
        state.totalInvoices  = json.totalInvoice ?? 0;
        return json;
        }

        // All-time fetch (per tenant) for Total Customers / Total SKUs
        async function ensureAllTimeRows(){
        const today = new Date(); today.setHours(0,0,0,0);
        const key = `${state.tenantId}|all|1970-01-01|${fmtLocal(today)}`;
        if(state._allTimeKey===key && state.allTimeRows.length) return;
        state.allTimeRows = await fetchCustomer(state.from, state.to);
        // state.allTimeRows = await fetchCustomer('1970-01-01', fmtLocal(today));
        state._allTimeKey = key;
        }

        /* ================= DATA PREP ================= */
        function buildDatalists(rows){
        const customers=[...new Set(rows.map(r=>(r.ContactName||'Uncategorized Customer')))].sort();
        const items=[...new Set(rows.map(r=>(r.LineItem_ItemCode||'—')))].sort();
        els.dlCustomer.innerHTML=customers.map(c=>`<option value="${escapeHtml(c)}">`).join('');
        els.dlSku.innerHTML     =items.map(c=>`<option value="${escapeHtml(c)}">`).join('');
        }

        function applyFilters(rows){
        let out=rows;
        if(state.filters.customer){ const q=state.filters.customer.toLowerCase(); out=out.filter(r=>(r.ContactName||'Uncategorized Customer').toLowerCase().includes(q)); }
        if(state.filters.sku){      const q=state.filters.sku.toLowerCase();      out=out.filter(r=>(r.LineItem_ItemCode||'').toLowerCase().includes(q)); }
        return out;
        }

        function computeAggregates(rows){
        const sales=rows.reduce((a,r)=>a+Number(r.LineItem_LineAmount||0),0);
        const invoiceKeys=new Set(rows.filter(r=>r.Types==='INVOICE').map(r=>String(r.InvoiceNumber)));
        const creditKeys =new Set(rows.filter(r=>r.Types==='CREDIT' ).map(r=>String(r.InvoiceNumber)));
        const invCount=invoiceKeys.size, crCount=creditKeys.size;
        const avg=invCount ? (sales/invCount) : 0;
        const uniqCustomers=new Set(rows.map(r=>(r.ContactName||'Uncategorized Customer'))).size;
        const uniqSkus=new Set(rows.map(r=>(r.LineItem_ItemCode||'—'))).size;
        return { sales, invCount, crCount, avg, uniqCustomers, uniqSkus };
        }

        function sliceByShow(arr, showVal){ if(showVal==='all') return arr; const n=parseInt(showVal,10); return arr.slice(0, isNaN(n)?20:n); }

        function calcTopCustomers(rows, showVal){
        const by=new Map();
        rows.forEach(r=>{
            const name=r.ContactName||'Uncategorized Customer';
            const invNo=String(r.InvoiceNumber);
            const amt=Number(r.LineItem_LineAmount||0);
            if(!by.has(name)) by.set(name,{ sales:0, invoices:new Set(), credits:new Set(), perInv:new Map() });
            const o=by.get(name);
            o.sales+=amt;
            if(r.Types==='INVOICE'){ o.invoices.add(invNo); o.perInv.set(invNo,(o.perInv.get(invNo)||0)+amt); }
            else if(r.Types==='CREDIT'){ o.credits.add(invNo); }
        });

        const arr=[...by.entries()].map(([name,o])=>{
            const invCount=o.invoices.size;
            const sumPerInv=[...o.perInv.values()].reduce((a,b)=>a+b,0);
            const avgInv=invCount ? (sumPerInv/invCount) : 0;
            return { name, total:o.sales, invoices:invCount, credits:o.credits.size, avgInvoice:avgInv };
        }).sort((a,b)=>b.total-a.total);

        const totalAll=arr.reduce((a,b)=>a+b.total,0)||1;
        const sliced=sliceByShow(arr, showVal);
        const withPct=sliced.map(r=>({ ...r, pct:(r.total/totalAll)*100 }));
        return { top:withPct, totalAll };
        }

        function calcTopSkus(rows, showVal){
        const by=new Map();
        rows.forEach(r=>{
            const code=r.LineItem_ItemCode||'—';
            const desc=r.LineItem_Description||'';
            const amt =Number(r.LineItem_LineAmount||0);
            const qty =Number(r.LineItem_Quantity||0);
            if(!by.has(code)) by.set(code,{ sales:0, qty:0, desc:'' });
            const o=by.get(code);
            o.sales+=amt; o.qty+=qty;
            if(desc && !o.desc) o.desc=desc;
        });

        const arr=[...by.entries()].map(([code,o])=>({
            code, desc:o.desc, sales:o.sales, qty:o.qty
        })).sort((a,b)=>b.sales-a.sales);

        const totalAll=arr.reduce((a,b)=>a+b.sales,0)||1;
        const sliced=sliceByShow(arr, showVal);
        const withPct=sliced.map(r=>({ ...r, pct:(r.sales/totalAll)*100 }));
        return { top:withPct, totalAll };
        }

        /* ===== Customers/SKUs summary ===== */
        function customerSummary(rows){
        const activeSet = new Map(); // name -> {invoices:Set}
        rows.forEach(r=>{
            const name = r.ContactName || 'Uncategorized Customer';
            const inv  = String(r.InvoiceNumber||'');
            if(!activeSet.has(name)) activeSet.set(name,{invoices:new Set()});
            if(r.Types==='INVOICE') activeSet.get(name).invoices.add(inv);
        });
        const active = activeSet.size;
        let repeat=0, newly=0;
        activeSet.forEach(v => { const c=v.invoices.size; if(c>1) repeat++; else if(c===1) newly++; });
        return { active, newly, repeat };
        }

        function skuSummary(rows){
        const activeMap = new Map(); // code -> {invoices:Set}
        rows.forEach(r=>{
            const code = r.LineItem_ItemCode || '—';
            const inv  = String(r.InvoiceNumber||'');
            if(!activeMap.has(code)) activeMap.set(code,{invoices:new Set()});
            if(r.Types==='INVOICE') activeMap.get(code).invoices.add(inv);
        });
        const active = activeMap.size;
        let repeat=0, newly=0;
        activeMap.forEach(v => { const c=v.invoices.size; if(c>1) repeat++; else if(c===1) newly++; });
        return { active, newly, repeat };
        }

        /* ================= CHART RENDERERS ================= */
        function renderAreaSpark(el, series, yfmt, categories=null){
        const id=el.id;
        const options={
            chart:{ type:'area', height:50, sparkline:{enabled:true}},
            stroke:{ width:2, curve:'smooth' },
            fill:{ type:'gradient', gradient:{ shadeIntensity:.3, opacityFrom:.4, opacityTo:.1 }},
            series: Array.isArray(series) ? series : [series],
            xaxis: categories ? { categories, type:'category' } : undefined,
            tooltip:{
            enabled:true,
            x:{ formatter:(val,opts)=> (opts?.w?.globals?.categoryLabels?.[opts.dataPointIndex] ?? '') },
            y:{ formatter:(v)=> (yfmt==='money'? moneyShort(v) : intFmt(v)) }
            }
        };
        if(state.charts[id]) state.charts[id].updateOptions(options,true,true);
        else { state.charts[id]=new ApexCharts(el, options); state.charts[id].render(); }
        }

        function renderSeries(container, key, type, series, categories, isMoney=false, datetime=false, yMin=null){
        const allVals = series.flatMap(s => s.data).filter(v => typeof v==='number' && !isNaN(v));
        const minVal = (yMin!==null ? yMin : (allVals.length ? Math.min(...allVals) : 0));

        const options={
            chart:{ type, height:300, toolbar:{show:false}},
            series,
            dataLabels:{enabled:false},
            stroke:(type==='line'||type==='area')?{curve:'smooth',width:2}:undefined,
            fill: type==='area'?{type:'gradient',gradient:{shadeIntensity:.3,opacityFrom:.5,opacityTo:.15}}:undefined,
            xaxis: datetime
            ? { type:'datetime', categories: categories.map(d=>d.toISOString()), tickAmount: Math.max(4, Math.floor(categories.length/7)), labels:{ formatter:(val)=>labelDDMMYY(new Date(val)) } }
            : { type:'category', categories, labels:{ rotate:0, formatter:(v)=>v }, tickPlacement:'between' },
            tooltip:{
            shared: series.length>1, intersect:false,
            x:{ formatter:(val,o)=> datetime ? labelDDMMYY(new Date(val)) : (o?.w?.globals?.categoryLabels?.[o.dataPointIndex] ?? val) },
            y:{ formatter:(v)=> (isMoney?moneyShort(v):intFmt(v)) }
            },
            yaxis:{ labels:{ formatter:(v)=> (isMoney?moneyShort(v):intFmt(v)) }, min: (isFinite(minVal) ? minVal : undefined) },
            legend:{ show: series.length>1 }
        };
        if(state.charts[key]) state.charts[key].updateOptions(options,true,true);
        else { state.charts[key]=new ApexCharts(container, options); state.charts[key].render(); }
        }

        function renderHorizontalBar(container, key, categories, data, isMoney=false, height=320){
        const options={
            chart:{ type:'bar', height, toolbar:{show:false}},
            series:[{ name:'Total Sales', data }],
            plotOptions:{ bar:{ horizontal:true, barHeight:'70%' }},
            dataLabels:{enabled:false},
            xaxis:{ categories, labels:{ formatter:(v)=> (isMoney?moneyShort(v):intFmt(v)) }},
            tooltip:{ y:{ formatter:(v)=> (isMoney?moneyShort(v):intFmt(v)) } }
        };
        if(state.charts[key]) state.charts[key].updateOptions(options,true,true);
        else { state.charts[key]=new ApexCharts(container, options); state.charts[key].render(); }
        }

        function renderBarVertical(container, key, categories, data, isMoney=false, onHover=null){
        const options={
            chart:{ type:'bar', height:320, toolbar:{show:false},
            events: onHover ? {
                dataPointMouseEnter: function(event, chartContext, config){ const idx=config.dataPointIndex; onHover(idx); }
            } : {}
            },
            series:[{ name:'Total', data }],
            plotOptions:{ bar:{ horizontal:false, columnWidth:'45%' }},
            dataLabels:{enabled:false},
            xaxis:{ categories },
            yaxis:{ labels:{ formatter:(v)=> (isMoney?moneyShort(v):intFmt(v)) }},
            tooltip:{ y:{ formatter:(v)=> (isMoney?moneyShort(v):intFmt(v)) } }
        };
        if(state.charts[key]) state.charts[key].updateOptions(options,true,true);
        else { state.charts[key]=new ApexCharts(container, options); state.charts[key].render(); }
        }

        /* ================= BUILDERS (daily/monthly) ================= */
        function buildRepeatNewDaily(rows, startDate, endDate){
        const start=new Date(startDate); start.setHours(0,0,0,0);
        const end=new Date(endDate);     end.setHours(0,0,0,0);

        const firstSeen = new Map(); // name -> 'YYYY-MM-DD'
        const present   = new Map(); // dateStr -> Set(names)
        rows.forEach(r=>{
            if(r.Types!=='INVOICE') return;
            const name = r.ContactName || 'Uncategorized Customer';
            const dStr = String(r.Date).slice(0,10);
            if(!present.has(dStr)) present.set(dStr, new Set());
            present.get(dStr).add(name);
            const prev = firstSeen.get(name);
            if(!prev || dStr < prev) firstSeen.set(name, dStr);
        });

        const labels=[];
        for(let d=new Date(start); d<=end; d.setDate(d.getDate()+1)) labels.push(new Date(d));
        const dayKeys = labels.map(d=>d.toISOString().slice(0,10));

        const seriesNew=[], seriesRepeat=[];
        dayKeys.forEach(k=>{
            const todays = present.get(k) || new Set();
            let nNew=0, nRep=0;
            todays.forEach(name=>{
            const first=firstSeen.get(name);
            if(first===k) nNew++; else if(first && first<k) nRep++;
            });
            seriesNew.push(nNew);
            seriesRepeat.push(nRep);
        });
        return { labels, seriesNew, seriesRepeat };
        }

        function buildRepeatNewMonthly(rows, startDate, endDate){
        const startFirst=new Date(startDate.getFullYear(), startDate.getMonth(), 1);
        const endFirst  =new Date(endDate.getFullYear(),   endDate.getMonth(),   1);

        const months=[];
        for(let d=new Date(startFirst); d<=endFirst; d.setMonth(d.getMonth()+1)){
            months.push(`${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}`);
        }

        const firstSeenM = new Map(); // name -> 'YYYY-MM'
        const presentM   = new Map(); // 'YYYY-MM' -> Set(names)
        rows.forEach(r=>{
            if(r.Types!=='INVOICE') return;
            const name = r.ContactName || 'Uncategorized Customer';
            const mStr = String(r.Date).slice(0,7);
            if(!presentM.has(mStr)) presentM.set(mStr, new Set());
            presentM.get(mStr).add(name);
            const prev = firstSeenM.get(name);
            if(!prev || mStr < prev) firstSeenM.set(name, mStr);
        });

        const categories = months.map(labelMonthYYYYFromStr);
        const seriesNew=[], seriesRepeat=[];
        months.forEach(m=>{
            const todays = presentM.get(m) || new Set();
            let nNew=0, nRep=0;
            todays.forEach(name=>{
            const first=firstSeenM.get(name);
            if(first===m) nNew++; else if(first && first<m) nRep++;
            });
            seriesNew.push(nNew);
            seriesRepeat.push(nRep);
        });

        return { months, categories, seriesNew, seriesRepeat };
        }

        function buildMonthlyContinuous(rows, startDate, endDate){
        const startFirst=new Date(startDate.getFullYear(), startDate.getMonth(), 1);
        const endFirst  =new Date(endDate.getFullYear(),   endDate.getMonth(),   1);
        const buckets=new Map();
        for(let d=new Date(startFirst); d<=endFirst; d.setMonth(d.getMonth()+1)){
            const key = `${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}`;
            buckets.set(key,{ sales:0, perInv:new Map(), inv:new Set(), cr:new Set(), cust:new Set(), sku:new Set() });
        }
        rows.forEach(r=>{
            const key=String(r.Date).slice(0,7);
            if(!buckets.has(key)) return;
            const b=buckets.get(key);
            const amt=Number(r.LineItem_LineAmount||0);
            const invNo=String(r.InvoiceNumber);
            const cust=(r.ContactName||'Uncategorized Customer');
            const sku =(r.LineItem_ItemCode||'—');
            b.sales+=amt; b.cust.add(cust); b.sku.add(sku);
            if(r.Types==='INVOICE'){ b.inv.add(invNo); b.perInv.set(invNo,(b.perInv.get(invNo)||0)+amt); }
            else if(r.Types==='CREDIT'){ b.cr.add(invNo); }
        });
        const keys=[...buckets.keys()].sort();
        const categories=keys.map(k=>labelMonthYYYYFromStr(k));
        const sales=keys.map(k=>Number(buckets.get(k).sales||0));
        const invoices=keys.map(k=>buckets.get(k).inv.size);
        const credits =keys.map(k=>buckets.get(k).cr.size);
        const customers=keys.map(k=>buckets.get(k).cust.size);
        const skus     =keys.map(k=>buckets.get(k).sku.size);
        const avg=keys.map(k=>{
            const invCount=buckets.get(k).inv.size;
            if(!invCount) return 0;
            let sum=0; buckets.get(k).perInv.forEach(v=>sum+=v);
            return sum/invCount;
        });
        return { keys, categories, sales, invoices, credits, avg, customers, skus };
        }

        function buildDailyContinuous(rows, startDate, endDate){
        const start=new Date(startDate); start.setHours(0,0,0,0);
        const end  =new Date(endDate);   end.setHours(0,0,0,0);
        const buckets=new Map();
        for(let d=new Date(start); d<=end; d.setDate(d.getDate()+1)){
            const key=d.toISOString().slice(0,10);
            buckets.set(key,{ sales:0, perInv:new Map(), inv:new Set(), cr:new Set() });
        }
        rows.forEach(r=>{
            const key=String(r.Date).slice(0,10);
            const b=buckets.get(key); if(!b) return;
            const amt=Number(r.LineItem_LineAmount||0);
            const invNo=String(r.InvoiceNumber);
            b.sales+=amt;
            if(r.Types==='INVOICE'){ b.inv.add(invNo); b.perInv.set(invNo,(b.perInv.get(invNo)||0)+amt); }
            else if(r.Types==='CREDIT'){ b.cr.add(invNo); }
        });
        const keys=[...buckets.keys()].sort();
        const labels=keys.map(k=>new Date(k));
        const sales=keys.map(k=>Number(buckets.get(k).sales||0));
        const invoices=keys.map(k=>buckets.get(k).inv.size);
        const credits =keys.map(k=>buckets.get(k).cr.size);
        const avg=keys.map(k=>{
            const invCount=buckets.get(k).inv.size; if(!invCount) return 0;
            let sum=0; buckets.get(k).perInv.forEach(v=>sum+=v);
            return sum/invCount;
        });
        return { labels, sales, invoices, credits, avg };
        }

        /* ====== TRIMMING HELPERS (start charts at first actual value) ====== */
        function firstNonZeroIndex(seriesList){
        const len = seriesList[0]?.length || 0;
        for(let i=0;i<len;i++){
            for(const s of seriesList){
            if((s[i]||0) !== 0) return i;
            }
        }
        return 0;
        }
        function trimSeries(seriesList, labels){
        const startIdx = firstNonZeroIndex(seriesList);
        const trimmedSeries = seriesList.map(s => s.slice(startIdx));
        const trimmedLabels = labels.slice(startIdx);
        return { trimmedSeries, trimmedLabels, startIdx };
        }

        /* ================= TREND RENDER ================= */
        function renderTrend(granularity, daily, monthly){
        if(granularity==='daily'){
            // Trim by first actual value across sales/invoices/credits
            const t = trimSeries([daily.sales, daily.invoices, daily.credits], daily.labels);
            const [salesT, invT, crT] = t.trimmedSeries;
            const labelsT = t.trimmedLabels;

            renderSeries(els.tSales, 'tSales', 'area',
            [{ name:'Total Sales', data: salesT }],
            labelsT, true, true
            );
            renderSeries(els.tDocs, 'tDocs', 'area',
            [{ name:'Invoices', data: invT }, { name:'Credits', data: crT }],
            labelsT, false, true
            );
        } else {
            // Monthly—use keys to align months precisely, then trim leading zeros
            const t = trimSeries([monthly.sales, monthly.invoices, monthly.credits], monthly.categories);
            const [salesT, invT, crT] = t.trimmedSeries;
            const catsT = t.trimmedLabels;

            renderSeries(els.tSales, 'tSales', 'area',
            [{ name:'Total Sales', data: salesT.map(Number) }],
            catsT, true, false
            );
            renderSeries(els.tDocs, 'tDocs', 'area',
            [{ name:'Invoices', data: invT.map(Number) }, { name:'Credits', data: crT.map(Number) }],
            catsT, false, false
            );
        }
        }

        function renderTrendBars(granularity, dailyRows, monthlyRows){
        const start=new Date(state.from), end=new Date(state.to);
        const src = (granularity==='daily')
            ? dailyRows.filter(r => String(r.Date).slice(0,10) >= fmtLocal(start) && String(r.Date).slice(0,10) <= fmtLocal(end))
            : monthlyRows.filter(r => r.Date && String(r.Date).slice(0,10) >= fmtLocal(new Date(start.getFullYear(), start.getMonth(), 1)) && String(r.Date).slice(0,10) <= fmtLocal(new Date(end.getFullYear(), end.getMonth()+1, 0)));
        const filtered = applyFilters(src);
        const showVal = state.trendShowBoth;
        const dynamicH = calcBarHeight(showVal);

        const custAgg = calcTopCustomers(filtered, showVal).top;
        renderHorizontalBar(els.trendTopCustomers, 'trendTopCustomers', custAgg.map(r=>r.name), custAgg.map(r=>r.total), true, dynamicH);

        const skuAgg = calcTopSkus(filtered, showVal).top;
        renderHorizontalBar(els.trendTopSkus, 'trendTopSkus', skuAgg.map(r=>r.code), skuAgg.map(r=>r.sales), true, dynamicH);
        }

        function renderRepeatChart(granularity, dailyRows, monthlyRows, start, end){
        if(granularity==='daily'){
            const d=buildRepeatNewDaily(dailyRows, start, end);
            // Trim leading zeros across both series
            const t = trimSeries([d.seriesNew, d.seriesRepeat], d.labels);
            const [newT, repT] = t.trimmedSeries;
            const labelsT = t.trimmedLabels;

            const options = {
            chart:{ type:'area', height:300, toolbar:{show:false}},
            series: [{ name:'New', data: newT }, { name:'Repeat', data: repT }],
            dataLabels:{enabled:false},
            stroke:{curve:'smooth',width:2},
            fill:{type:'gradient',gradient:{shadeIntensity:.3,opacityFrom:.5,opacityTo:.15}},
            xaxis:{ type:'datetime', categories: labelsT.map(x=>x.toISOString()),
                    labels:{ formatter:(val)=>labelDDMMYY(new Date(val)) } },
            tooltip:{ shared:true, intersect:false, x:{ formatter:(val)=> labelDDMMYY(new Date(val)) }, y:{ formatter:(v)=> intFmt(v) } },
            yaxis:{ labels:{ formatter:(v)=> intFmt(v) } },
            legend:{ show:true }
            };
            const key='trendRepeat';
            if(state.charts[key]) state.charts[key].updateOptions(options,true,true);
            else { state.charts[key]=new ApexCharts(els.trendRepeat, options); state.charts[key].render(); }
        } else {
            const m=buildRepeatNewMonthly(monthlyRows, start, end);
            // Trim leading zeros across both series (use the month sequence)
            const t = trimSeries([m.seriesNew, m.seriesRepeat], m.categories);
            const [newT, repT] = t.trimmedSeries;
            const catsT = t.trimmedLabels;

            const options = {
            chart:{ type:'area', height:300, toolbar:{show:false}},
            series: [{ name:'New', data: newT }, { name:'Repeat', data: repT }],
            dataLabels:{enabled:false},
            stroke:{curve:'smooth',width:2},
            fill:{type:'gradient',gradient:{shadeIntensity:.3,opacityFrom:.5,opacityTo:.15}},
            xaxis:{ type:'category', categories: catsT, tickPlacement:'between' },
            tooltip:{ shared:true, intersect:false, y:{ formatter:(v)=> intFmt(v) } },
            yaxis:{ labels:{ formatter:(v)=> intFmt(v) } },
            legend:{ show:true }
            };
            const key='trendRepeat';
            if(state.charts[key]) state.charts[key].updateOptions(options,true,true);
            else { state.charts[key]=new ApexCharts(els.trendRepeat, options); state.charts[key].render(); }
        }
        }

        function computeARBuckets(rows, refDate){
        const ref = new Date(refDate.getFullYear(), refDate.getMonth(), refDate.getDate());
        const seen = new Map(); // invoiceNo -> {due, amount, customer}
        rows.forEach(r=>{
            if(r.Types!=='INVOICE') return;
            const inv = String(r.InvoiceNumber);
            if(seen.has(inv)) return;
            const amt = Number(r.AmountDue||0);
            if(!(amt>0)) return;
            const due = r.DueDate ? new Date(r.DueDate) : null;
            const customer = r.ContactName || 'Uncategorized Customer';
            seen.set(inv, { due, amount: amt, customer });
        });

        const buckets = { notDue:0, d1_30:0, d31_60:0, d61_90:0, d90p:0 };
        const bucketCustomers = { notDue:new Map(), d1_30:new Map(), d31_60:new Map(), d61_90:new Map(), d90p:new Map() };

        seen.forEach(({due, amount, customer})=>{
            let key='d90p';
            if(!due){ key='d90p'; }
            else {
            const days = Math.floor((ref - new Date(due.getFullYear(),due.getMonth(),due.getDate()))/86400000);
            if(days <= 0) key='notDue';
            else if(days <= 30) key='d1_30';
            else if(days <= 60) key='d31_60';
            else if(days <= 90) key='d61_90';
            else key='d90p';
            }
            buckets[key] += amount;
            const m = bucketCustomers[key];
            m.set(customer, (m.get(customer)||0) + amount);
        });
        return { buckets, bucketCustomers };
        }

        function renderARWithDrill(granularity, dailyRows, monthlyRows){
        const start=new Date(state.from), end=new Date(state.to);
        const src = (granularity==='daily')
            ? dailyRows.filter(r => String(r.Date).slice(0,10) >= fmtLocal(start) && String(r.Date).slice(0,10) <= fmtLocal(end))
            : monthlyRows.filter(r => r.Date && String(r.Date).slice(0,10) >= fmtLocal(new Date(start.getFullYear(), start.getMonth(), 1)) && String(r.Date).slice(0,10) <= fmtLocal(new Date(end.getFullYear(), end.getMonth()+1, 0)));
        const filtered = applyFilters(src);
        const { buckets, bucketCustomers } = computeARBuckets(filtered, end);

        const arCats = ['Not Due','1–30','31–60','61–90','>90'];
        const arKeys = ['notDue','d1_30','d31_60','d61_90','d90p'];
        const arData = [buckets.notDue, buckets.d1_30, buckets.d31_60, buckets.d61_90, buckets.d90p];

        function hideArDetail(){
            els.trendARDetailTitle.style.display='none';
            els.trendARDetailChart.style.display='none';
        }

        function onHover(idx){
            const key = arKeys[idx];
            const titleMap = { notDue:'Not Due', d1_30:'1–30 Days', d31_60:'31–60 Days', d61_90:'61–90 Days', d90p:'> 90 Days' };
            const map = bucketCustomers[key];
            const entries = [...map.entries()].sort((a,b)=>b[1]-a[1]);
            if(entries.length===0){ hideArDetail(); return; }
            const names = entries.map(([c])=>c);
            const amounts = entries.map(([,v])=>v);
            const h = Math.min(900, 22*names.length + 100); // dynamic height based on rows
            els.trendARDetailTitle.textContent = `Customers in ${titleMap[key]} bucket`;
            els.trendARDetailTitle.style.display='block';
            els.trendARDetailChart.style.display='block';
            renderHorizontalBar(els.trendARDetailChart, 'trendARDetail', names, amounts, true, h);
        }

        // Hide on mouse leave of the whole widget area
        els.trendARWidget.addEventListener('mouseleave', hideArDetail);

        renderBarVertical(els.trendAR, 'trendAR', arCats, arData, true, onHover);
        hideArDetail();
        }

        /* ================= KPI RENDERERS ================= */
        function renderKPIsTrend(agg, monthlyAgg){
        els.kpiTotalSales.textContent    = moneyShort(agg.sales);
        els.kpiTotalInvoices.textContent = intFmt(agg.invCount);
        els.kpiTotalCredits.textContent  = intFmt(agg.crCount);
        els.kpiAvgSales.textContent      = moneyShort(agg.avg);
        els.kpiUniqueCust.textContent    = intFmt(agg.uniqCustomers);
        els.kpiUniqueSkus.textContent    = intFmt(agg.uniqSkus);

        const cats=(monthlyAgg?.categories||[]);
        renderAreaSpark(els.sparkSales,    [{ name:'Total Sales', data:(monthlyAgg?.sales||[]) }], 'money', cats);
        renderAreaSpark(els.sparkInvoices, [{ name:'Invoices',    data:(monthlyAgg?.invoices||[]) }], 'int',   cats);
        renderAreaSpark(els.sparkCredits,  [{ name:'Credits',     data:(monthlyAgg?.credits||[]) }], 'int',   cats);
        renderAreaSpark(els.sparkAvg,      [{ name:'Avg/Invoice', data:(monthlyAgg?.avg||[]) }],     'money', cats);
        renderAreaSpark(els.sparkCust,     [{ name:'Customers',   data:(monthlyAgg?.customers||[])}], 'int',   cats);
        renderAreaSpark(els.sparkSkus,     [{ name:'SKUs',        data:(monthlyAgg?.skus||[])}],      'int',   cats);
        }

        function renderCustomerSummary(filtered){
        // const totalSet = new Set(state.allTimeRows.map(r => r.ContactName || 'Uncategorized Customer'));
        const s = customerSummary(filtered);
        // els.custTotal.textContent  = intFmt(totalSet.size);
        els.custTotal.textContent = intFmt(state.totalCustomers || 0);
        els.custActive.textContent = intFmt(s.active);
        els.custNew.textContent    = intFmt(s.newly);
        els.custRepeat.textContent = intFmt(s.repeat);
        }

        function renderSkuSummary(filtered){
        // const totalSet = new Set(state.allTimeRows.map(r => r.LineItem_ItemCode || '—'));
        const s = skuSummary(filtered);
        // els.skuTotal.textContent  = intFmt(totalSet.size);
        els.skuTotal.textContent = intFmt(state.totalInvoices || 0);
        els.skuActive.textContent = intFmt(s.active);
        els.skuNew.textContent    = intFmt(s.newly);
        els.skuRepeat.textContent = intFmt(s.repeat);
        }

        function renderTopCustomers(tbl){
        const { top, totalAll } = tbl;
        els.custBody.innerHTML=''; els.custFoot.innerHTML='';

        top.forEach(r=>{
            const isNeg=r.total<0;
            const tr=document.createElement('tr');
            tr.innerHTML=
            `<td><strong>${escapeHtml(r.name)}</strong>${r.credits>0 ? ' <span class="badge-credit">Credit x'+intFmt(r.credits)+'</span>' : ''}</td>
            <td class="text-end">${intFmt(r.invoices)}</td>
            <td class="text-end">${intFmt(r.credits)}</td>
            <td class="text-end">${moneyShort(r.avgInvoice)}</td>
            <td class="text-end ${isNeg?'neg':''}">${moneyShort(r.total)}</td>
            <td class="text-end">${(r.pct).toFixed(1)}%</td>`;
            els.custBody.appendChild(tr);
        });

        const foot=document.createElement('tr');
        foot.className='grandtotal';
        foot.innerHTML=
            `<td><strong>Total</strong></td>
            <td></td><td></td><td></td>
            <td class="text-end"><strong>${moneyShort(totalAll)}</strong></td>
            <td></td>`;
        els.custFoot.appendChild(foot);

        els.custCardWrap.innerHTML='';
        top.forEach(r=>{
            const isNeg=r.total<0;
            const card=document.createElement('div');
            card.className='item-card';
            card.innerHTML=
            `<div class="item-name"><strong>${escapeHtml(r.name)}</strong>${r.credits>0 ? ' <span class="badge-credit">Credit x'+intFmt(r.credits)+'</span>' : ''}</div>
            <div class="mini">Invoices: <strong>${intFmt(r.invoices)}</strong></div>
            <div class="mini">Share: <strong>${(r.pct).toFixed(1)}%</strong></div>
            <div class="mt-2">Total: <strong class="${isNeg?'neg':''}">${moneyShort(r.total)}</strong></div>
            <div class="mini">Avg/Invoice: <strong>${moneyShort(r.avgInvoice)}</strong></div>`;
            els.custCardWrap.appendChild(card);
        });

        const useTable=state.display.customers==='table';
        els.custTableWrap.classList.toggle('d-none', !useTable);
        els.custCardWrap.classList.toggle('d-none',  useTable);
        els.custTableBtn.classList.toggle('active',  useTable);
        els.custCardBtn.classList.toggle('active',  !useTable);
        }

        function renderTopSkus(tbl){
        const { top, totalAll } = tbl;
        els.skuBody.innerHTML=''; els.skuFoot.innerHTML='';
        let totalQty=0;

        top.forEach(r=>{
            const isNeg=r.sales<0; totalQty += r.qty;
            const tr=document.createElement('tr');
            tr.innerHTML=
            `<td><strong>${escapeHtml(r.code)}</strong></td>
            <td>${escapeHtml(r.desc||'')}</td>
            <td class="text-end">${intFmt(r.qty)}</td>
            <td class="text-end ${isNeg?'neg':''}">${moneyShort(r.sales)}</td>
            <td class="text-end">${(r.pct).toFixed(1)}%</td>`;
            els.skuBody.appendChild(tr);
        });

        const foot=document.createElement('tr');
        foot.className='grandtotal';
        foot.innerHTML=
            `<td><strong>Total</strong></td>
            <td></td>
            <td class="text-end"><strong>${intFmt(totalQty)}</strong></td>
            <td class="text-end"><strong>${moneyShort(totalAll)}</strong></td>
            <td></td>`;
        els.skuFoot.appendChild(foot);

        els.skuCardWrap.innerHTML='';
        top.forEach(r=>{
            const isNeg=r.sales<0;
            const card=document.createElement('div');
            card.className='item-card';
            card.innerHTML=
            `<div class="item-name"><strong>${escapeHtml(r.code)}</strong></div>
            <div class="mini">${escapeHtml(r.desc||'')}</div>
            <div class="mini mt-1">Qty: <strong>${intFmt(r.qty)}</strong></div>
            <div class="mt-2">Total: <strong class="${isNeg?'neg':''}">${moneyShort(r.sales)}</strong></div>`;
            els.skuCardWrap.appendChild(card);
        });

        const useTable=state.display.skus==='table';
        els.skuTableWrap.classList.toggle('d-none', !useTable);
        els.skuCardWrap.classList.toggle('d-none',  useTable);
        els.skuTableBtn.classList.toggle('active',  useTable);
        els.skuCardBtn.classList.toggle('active',  !useTable);
        }

        function renderAll(rows){
        buildDatalists(rows);
        const filtered=applyFilters(rows);
        const agg=computeAggregates(filtered);

        const start=new Date(state.from), end=new Date(state.to);
        const monthlyAgg = buildMonthlyContinuous(rows, start, end);

        root.classList.remove('show-kpi-trend','show-kpi-customers','show-kpi-skus');
        if(state.view==='trend'){
            root.classList.add('show-kpi-trend');
            renderKPIsTrend(agg, monthlyAgg);
        } else if(state.view==='customers'){
            root.classList.add('show-kpi-customers');
            renderCustomerSummary(filtered);
        } else if(state.view==='sku'){
            root.classList.add('show-kpi-skus');
            renderSkuSummary(filtered);
        }

        const topC=calcTopCustomers(filtered, state.showTop.customers);
        const topS=calcTopSkus(filtered,      state.showTop.skus);
        if(state.view==='customers'){ renderTopCustomers(topC); }
        if(state.view==='sku'){ renderTopSkus(topS); }

        els.err.classList.add('d-none');
        els.content.classList.remove('d-none');
        }

        /* ================= UI EVENTS ================= */
        function toggleFilters(){
        const willShow = els.filterBtn.getAttribute('aria-expanded') !== 'true';
        els.controlsRow.classList.toggle('d-none', !willShow);
        els.filterBtn.classList.toggle('active', willShow);
        els.filterBtn.setAttribute('aria-expanded', willShow ? 'true' : 'false');
        }
        els.filterBtn.addEventListener('click', toggleFilters);

        function updatePeriodUI(){
        const isCustom = state.period === 'custom';
        els.fromDate.classList.toggle('d-none', !isCustom);
        els.toDate.classList.toggle('d-none',   !isCustom);
        }

        async function applyPeriodAndReload(){
        const rng = computePeriodRange(state.period);
        state.from = fmtLocal(rng.start);
        state.to   = fmtLocal(rng.end);
        els.dateLabel.textContent=`${state.from} → ${state.to}`;
        await reload();
        }

        els.periodSelect.addEventListener('change', async (e)=>{
        state.period = e.target.value;
        updatePeriodUI();
        if(state.period!=='custom'){ await applyPeriodAndReload(); }
        });
        els.fromDate.addEventListener('change', async ()=>{
        if(state.period!=='custom') return;
        if(!els.fromDate.value || !els.toDate.value) return;
        await applyPeriodAndReload();
        });
        els.toDate.addEventListener('change', async ()=>{
        if(state.period!=='custom') return;
        if(!els.fromDate.value || !els.toDate.value) return;
        await applyPeriodAndReload();
        });

        // Clear → reset defaults (incl. period)
        els.clearBtn.addEventListener('click', async ()=>{
        state.filters={ customer:'', sku:'' };
        els.fCustomer.value=''; els.fSku.value='';
        state.period='last_3_months';
        els.periodSelect.value='last_3_months';
        updatePeriodUI();
        state.trendGranularity='daily';
        els.trendDailyBtn.classList.add('active'); els.trendMonthlyBtn.classList.remove('active');

        state.display.customers='table'; state.display.skus='table';
        els.custTableBtn.classList.add('active'); els.custCardBtn.classList.remove('active');
        els.skuTableBtn.classList.add('active');  els.skuCardBtn.classList.remove('active');

        state.showTop.customers=20; state.showTop.skus=20;
        els.custShowSelect.value='20'; els.skuShowSelect.value='20';
        state.trendShowBoth=20; els.trendTopShow.value='20';

        await applyPeriodAndReload();
        });

        // Text filters (debounced)
        let tId;
        function onFilter(){ clearTimeout(tId); tId=setTimeout(()=>{ renderAll(state.allRows); if(state.view==='trend'){ renderTrendNow(); } },200); }
        els.fCustomer.addEventListener('input',(e)=>{ state.filters.customer=e.target.value||''; onFilter(); });
        els.fSku     .addEventListener('input',(e)=>{ state.filters.sku     =e.target.value||''; onFilter(); });

        // Table/Cards toggles
        els.custTableBtn.addEventListener('click', (e)=>{
        e.preventDefault();
        state.display.customers='table';
        els.custTableBtn.classList.add('active');
        els.custCardBtn.classList.remove('active');
        renderAll(state.allRows);
        });
        els.custCardBtn.addEventListener('click', (e)=>{
        e.preventDefault();
        state.display.customers='card';
        els.custCardBtn.classList.add('active');
        els.custTableBtn.classList.remove('active');
        renderAll(state.allRows);
        });
        els.skuTableBtn.addEventListener('click', (e)=>{
        e.preventDefault();
        state.display.skus='table';
        els.skuTableBtn.classList.add('active');
        els.skuCardBtn.classList.remove('active');
        renderAll(state.allRows);
        });
        els.skuCardBtn.addEventListener('click', (e)=>{
        e.preventDefault();
        state.display.skus='card';
        els.skuCardBtn.classList.add('active');
        els.skuTableBtn.classList.remove('active');
        renderAll(state.allRows);
        });

        // Show dropdown handlers
        els.custShowSelect.addEventListener('change', (e)=>{ state.showTop.customers=e.target.value; renderAll(state.allRows); });
        els.skuShowSelect .addEventListener('change', (e)=>{ state.showTop.skus     =e.target.value; renderAll(state.allRows); });

        // View switchers
        function setView(v){
        state.view=v;
        els.custBtn.classList.toggle('active', v==='customers');
        els.skuBtn.classList.toggle('active', v==='sku');
        els.trendBtn.classList.toggle('active', v==='trend');

        els.vCustomer.classList.toggle('d-none', v!=='customers');
        els.vSku.classList.toggle('d-none', v!=='sku');
        els.vTrend.classList.toggle('d-none', v!=='trend');

        if(!state.initializing){
            renderAll(state.allRows);
            if(v==='trend'){ renderTrendNow(); }
        }
        }
        els.custBtn.addEventListener('click', () => setView('customers'));
        els.skuBtn .addEventListener('click', () => setView('sku'));
        els.trendBtn.addEventListener('click', () => setView('trend'));

        // Trend controls
        els.trendDailyBtn.addEventListener('click', async ()=>{
        state.trendGranularity='daily';
        els.trendDailyBtn.classList.add('active');
        els.trendMonthlyBtn.classList.remove('active');
        await renderTrendNow();
        });
        els.trendMonthlyBtn.addEventListener('click', async ()=>{
        state.trendGranularity='monthly';
        els.trendMonthlyBtn.classList.add('active');
        els.trendDailyBtn.classList.remove('active');
        await renderTrendNow();
        });
        els.trendTopShow.addEventListener('change', async (e)=>{
        state.trendShowBoth = e.target.value;
        await renderTrendNow();
        });

        // Tenant change → invalidate caches and reload
        els.tenantSelect?.addEventListener('change', async (e)=>{
        state.tenantId=e.target.value||null;
        state._dailyKey = state._monthlyKey = state._allTimeKey = null;
        state.dailyRows = []; state.monthlyRows = []; state.allTimeRows = [];
        state.totalCustomers = 0; state.totalInvoices  = 0;
        await reload();
        });

        /* ================= FETCH & RENDER ================= */
        async function ensureMonthlyRows(){
        const start=new Date(state.from), end=new Date(state.to);
        const startFirst=new Date(start.getFullYear(), start.getMonth(), 1);
        const endLast  =new Date(end.getFullYear(),   end.getMonth()+1, 0);
        const f=fmtLocal(startFirst), t=fmtLocal(endLast);
        const key=`${state.tenantId}|m|${f}|${t}`;
        if(state._monthlyKey===key && state.monthlyRows.length) return;
        state.monthlyRows=await fetchRows(f,t);
        state._monthlyKey=key;
        state._lastMonthly=buildMonthlyContinuous(state.monthlyRows, start, end);
        }

        async function ensureDailyRows(){
        const f=state.from, t=state.to;
        const key=`${state.tenantId}|d|${f}|${t}`;
        if(state._dailyKey===key && state.dailyRows.length) return;
        state.dailyRows=await fetchRows(f,t);
        state._dailyKey=key;
        }

        async function renderTrendNow(){
        const start=new Date(state.from), end=new Date(state.to);
        await ensureMonthlyRows();
        await ensureDailyRows();
        const daily=buildDailyContinuous(state.dailyRows, start, end);
        const monthly=state._lastMonthly;

        renderTrend(state.trendGranularity, daily, monthly);
        renderTrendBars(state.trendGranularity, state.dailyRows, state.monthlyRows);
        renderARWithDrill(state.trendGranularity, state.dailyRows, state.monthlyRows);
        renderRepeatChart(state.trendGranularity, state.dailyRows, state.monthlyRows, start, end);
        }

        async function reload(){
        setStatus(true);
        els.content.classList.add('d-none');
        els.err.classList.add('d-none');
  
        try{
            await ensureAllTimeRows();
            state.allRows = await fetchRows(state.from, state.to);
            await ensureMonthlyRows();
            await ensureDailyRows();

            if(state.initializing){ state.initializing=false; }
            renderAll(state.allRows);
            if(state.view==='trend'){ await renderTrendNow(); }
            setStatus(false);
        }catch(e){
            console.error(e);
            setStatus(false);
            els.err.classList.remove('d-none');
        }
        }

        // Init → default period Last 3 Months
        try{
        state.period = 'last_3_months';
        els.periodSelect.value='last_3_months';
        const rng = computePeriodRange(state.period);
        state.from=fmtLocal(rng.start); state.to=fmtLocal(rng.end);
        els.dateLabel.textContent=`${state.from} → ${state.to}`;

        setView('trend');

        const opt=await getOptions();
        populateTenantSelect(opt.tenants||[]);
        await reload();
        }catch(e){
        console.error(e);
        setStatus(false);
        els.err.classList.remove('d-none');
        }
    });
    </script>
    @endpush
    </div>
</div>
@endsection