@extends('dashboard.header')
@section('content')

<style>
    #chart {
        width: 100%;
        height: 500px;
    }
</style>

<div id="chart"></div>
<!-- Chart code -->
<script>
    var options = {
        series: [176, 167],
        chart: {
            height: 270,
            type: 'radialBar',
        },
        plotOptions: {
            radialBar: {
                offsetY: 0,
                startAngle: 0,
                endAngle: 270,
                hollow: {
                    margin: 5,
                    size: '30%',
                    background: 'transparent',
                    image: undefined,
                },
                dataLabels: {
                    name: {
                        show: false,
                    },
                    value: {
                        show: false,
                    }
                },
                barLabels: {
                    enabled: true,
                    useSeriesColors: true,
                    offsetX: -8,
                    fontSize: '15px',
                    formatter: function(seriesName, opts) {
                        return seriesName + ":  " + opts.w.globals.series[opts.seriesIndex]
                    },
                },
            }
        },
        colors: ['#1ab7ea', '#0084ff', '#39539E', '#0077B5'],
        labels: ['TwoWheeler', 'TwoWheeler', 'Person', 'Dog'],
        responsive: [{
            breakpoint: 480,
            options: {
                legend: {
                    show: false
                }
            }
        }]
    };

    var chart = new ApexCharts(document.querySelector("#chart"), options);
    chart.render();
</script>

@endsection