@extends('backend.master', [
  'pageTitle' => 'Analytics Dashboard',
  'activeMenu' => ['item'=>'Analytics','sub'=>'Dashboard'],
  'features' => [
    'datatables' => '1',
  ],
])

@push('styles')

@endpush

@section('content')
<div class="container-fluid">
   <div id="scatterChart"></div>
</div>
@endsection

@push('scripts')
 <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
 <script>
        const salesData = @json($sales);

        // Group data by sale_type for ApexCharts series
        const seriesMap = {};
        salesData.forEach(item => {
            if(!seriesMap[item.sale_type]) seriesMap[item.sale_type] = [];
            seriesMap[item.sale_type].push({ x: item.x, y: item.y, name: item.name });
        });

        const series = Object.keys(seriesMap).map(type => ({
            name: type,
            data: seriesMap[type]
        }));

        const options = {
            chart: {
                type: 'scatter',
                height: 500,
                zoom: { enabled: true }
            },
            xaxis: { title: { text: 'Cost per KG' } },
            yaxis: { title: { text: 'Sale Price' } },
            series: series,
            tooltip: {
                shared: false,
                custom: ({ seriesIndex, dataPointIndex, w }) => {
                    const point = w.globals.series[seriesIndex][dataPointIndex];
                    const name = w.globals.labels[dataPointIndex] || '';
                    return `<div>Product: ${salesData[dataPointIndex].name}<br>X: ${point.x}<br>Y: ${point.y}</div>`;
                }
            }
        };

        const chart = new ApexCharts(document.querySelector("#scatterChart"), options);
        chart.render();
    </script>
@endpush