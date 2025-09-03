@extends('dashboard.header')
@section('content')

<style>
    .chartdiv {
        width: 100%;
        height: 430px;
        max-width: 100%;
    }
</style>

<div>
    <div class="flex justify-between flex-wrap items-center mb-2">
        <h4 class="font-medium lg:text-2xl text-xl capitalize text-slate-900 inline-block ltr:pr-4 rtl:pl-4 mb-4 sm:mb-0 flex space-x-3 rtl:space-x-reverse">Detailed View</h4>
        <div class="flex sm:space-x-4 space-x-2 sm:justify-end items-center rtl:space-x-reverse">
            <!-- <button
                class="btn leading-0 inline-flex justify-center bg-white text-slate-700 dark:bg-slate-800 dark:text-slate-300 !font-normal">
                <span class="flex items-center">
                    <iconify-icon class="text-xl ltr:mr-2 rtl:ml-2 font-light"
                        icon="heroicons-outline:calendar"></iconify-icon>
                    <span>Weekly</span>
                </span>
            </button> -->
            <div>
                <select name="chart_view" id="chart_view" class="form-control w-full">
                    <option value="1" class="py-1 inline-block font-Inter font-normal text-sm text-slate-600" {{Auth::user()->chart_view == 1 ? 'selected' : ''}}>Occupied Data</option>
                    <option value="2" class="py-1 inline-block font-Inter font-normal text-sm text-slate-600" {{Auth::user()->chart_view == 2 ? 'selected' : ''}}>Available Data</option>
                    <option value="3" class="py-1 inline-block font-Inter font-normal text-sm text-slate-600" {{Auth::user()->chart_view == 3 ? 'selected' : ''}}>In-Out Data</option>
                </select>
            </div>
        </div>
    </div>

    <div class="flex justify-between flex-wrap items-center mb-4">
        <p class="font-medium capitalize text-slate-900 inline-block ltr:pr-4 rtl:pl-4 mb-4 sm:mb-0 flex space-x-3 rtl:space-x-reverse dark:text-white" id="lastUpdate">
            Last Update: Just now</p>
        <div class="flex sm:space-x-4 space-x-2 sm:justify-end items-center rtl:space-x-reverse mobileview">
            <button class="btn inline-flex justify-center btn-light shadow-base2" id="live-status-refresh" style="display: none;">
                <span class="flex items-center">
                    <i class="bx bx-loader bx-spin font-size-16 align-middle"></i>
                    <span>Refreshing</span>
                </span>
            </button>
            <button class="btn inline-flex justify-center btn-success shadow-base2 mobilebtn" id="live">
                <span class="flex items-center">
                    <i class="bx bx-bullseye bx-burst font-size-16 align-middle mx-1"></i>
                    <span>Live</span>
                </span>
            </button>
            <button class="btn inline-flex justify-center btn-danger shadow-base2 mobilebtn" id="offline" style="display: none;">
                <span class="flex items-center">
                    <i class="bx bx-bullseye bx-burst font-size-16 align-middle mx-1"></i>
                    <span>Offline</span>
                </span>
            </button>
            <!-- <button id="live-status-refresh" type="button" class="btn btn-light waves-effect" >
            <i class="bx bx-loader bx-spin font-size-16 align-middle me-2"></i>Refreshing
        </button>
        <button id="app-status-online" type="button" class="btn btn-success waves-effect">
            <i class="bx bx-bullseye bx-burst font-size-16 align-middle me-2"></i>Live
        </button>
        <button id="app-status-offline" type="button" class="btn btn-danger waves-effect">
            <i class="bx bx-bullseye bx-burst font-size-16 align-middle me-2"></i>Offline
        </button> -->
        </div>
    </div>

    <div class="grid grid-cols-12 text-center">
        <div class="2xl:col-span-12 lg:col-span-12 col-span-12" id="detailedData">

        </div>
    </div>

</div>


<script src="https://cdn.amcharts.com/lib/5/index.js"></script>
<script src="https://cdn.amcharts.com/lib/5/xy.js"></script>
<script src="https://cdn.amcharts.com/lib/5/themes/Animated.js"></script>

<script src="{{ asset('dashboard/assets/js/eecsdetailedView.js') }}"></script>
<script>
    window.chartview = "{{ Auth::user()->chart_view }}";
    // var data = "";
    // var perminutechart = am5.Root.new("chartdiv");

    // perminutechart.setThemes([
    //     am5themes_Animated.new(perminutechart)
    // ]);

    // perminutechart.dateFormatter.setAll({
    //     dateFormat: "dd MMM, yyyy hh:mm a",
    //     dateFields: ["valueX"]
    // });

    // var easing = am5.ease.linear;

    // window.last_hour_data_chart = perminutechart.container.children.push(am5xy.XYChart.new(perminutechart, {
    //     focusable: true,
    //     panX: true,
    //     panY: true,
    //     wheelX: "panX",
    //     wheelY: "zoomX",
    //     pinchZoomX: true
    // }));

    // var xAxis = window.last_hour_data_chart.xAxes.push(am5xy.DateAxis.new(perminutechart, {
    //     maxDeviation: 0.5,
    //     groupData: false,
    //     extraMax: 0.1, // this adds some space in front
    //     extraMin: -0.1, // this removes some space form th beginning so that the line would not be cut off
    //     baseInterval: {
    //         timeUnit: "minute",
    //         count: 1
    //     },
    //     renderer: am5xy.AxisRendererX.new(perminutechart, {
    //         minGridDistance: 50
    //     }),
    //     tooltip: am5.Tooltip.new(perminutechart, {})
    // }));

    // var yAxis = window.last_hour_data_chart.yAxes.push(am5xy.ValueAxis.new(perminutechart, {
    //     renderer: am5xy.AxisRendererY.new(perminutechart, {})
    // }));

    // var series = window.last_hour_data_chart.series.push(am5xy.LineSeries.new(perminutechart, {
    //     name: "Occupancy",
    //     xAxis: xAxis,
    //     yAxis: yAxis,
    //     valueYField: "value",
    //     valueXField: "date",
    //     tooltip: am5.Tooltip.new(perminutechart, {
    //         pointerOrientation: "horizontal",
    //         labelText: "{valueY}"
    //     })
    // }));

    // if (data.length > 0) {
    //     data[data.length - 1].bullet = true;
    // }
    // series.data.setAll(data);
    // // Create animating bullet by adding two circles in a bullet container and
    // // animating radius and opacity of one of them.
    // series.bullets.push(function(perminutechart, series, dataItem) {
    //     // only create sprite if bullet == true in data context
    //     if (dataItem.dataContext.bullet) {
    //         var container = am5.Container.new(perminutechart, {});
    //         var circle0 = container.children.push(am5.Circle.new(perminutechart, {
    //             radius: 5,
    //             fill: am5.color(0xff0000)
    //         }));
    //         var circle1 = container.children.push(am5.Circle.new(perminutechart, {
    //             radius: 5,
    //             fill: am5.color(0xff0000)
    //         }));

    //         circle1.animate({
    //             key: "radius",
    //             to: 20,
    //             duration: 1000,
    //             easing: am5.ease.out(am5.ease.cubic),
    //             loops: Infinity
    //         });
    //         circle1.animate({
    //             key: "opacity",
    //             to: 0,
    //             from: 1,
    //             duration: 1000,
    //             easing: am5.ease.out(am5.ease.cubic),
    //             loops: Infinity
    //         });

    //         return am5.Bullet.new(perminutechart, {
    //             locationX: undefined,
    //             sprite: container
    //         })
    //     }
    // });

    // // Add cursor
    // // https://www.amcharts.com/docs/v5/charts/xy-chart/cursor/
    // var cursor = window.last_hour_data_chart.set("cursor", am5xy.XYCursor.new(perminutechart, {
    //     xAxis: xAxis
    // }));
    // cursor.lineY.set("visible", false);
    // window.last_hour_data_chart.appear(1000, 100);




    // var radialchartColors = ["#5156be", "#34c38f"];
    // var options = {
    //     series: [20],
    //     chart: {
    //         height: 270,
    //         type: 'radialBar',
    //         offsetY: -10
    //     },
    //     plotOptions: {
    //         radialBar: {
    //             startAngle: -130,
    //             endAngle: 130,
    //             dataLabels: {
    //                 name: {
    //                     show: true
    //                 },
    //                 value: {
    //                     offsetY: 10,
    //                     fontSize: '18px',
    //                     color: undefined,
    //                     formatter: function(val) {
    //                         return val + "%";
    //                     }
    //                 }
    //             }
    //         }
    //     },
    //     colors: [radialchartColors[0]],
    //     fill: {
    //         type: 'gradient',
    //         gradient: {
    //             shade: 'dark',
    //             type: 'horizontal',
    //             gradientToColors: [radialchartColors[1]],
    //             shadeIntensity: 0.15,
    //             inverseColors: false,
    //             opacityFrom: 1,
    //             opacityTo: 1,
    //             stops: [20, 60]
    //         },
    //     },
    //     stroke: {
    //         dashArray: 4,
    //     },
    //     labels: ['Occupancy'],
    // }

    // window.liveStatusChart = new ApexCharts(
    //     document.querySelector("#progressChart"),
    //     options
    // );

    // window.liveStatusChart.render();
</script>
@endsection