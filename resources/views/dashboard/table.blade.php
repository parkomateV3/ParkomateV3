@extends('dashboard.header')
@section('content')

<style>
    #highchart {
        width: 100%;
        height: 370px;
        max-width: 100%;
    }
</style>

<div class="flex justify-between flex-wrap items-center mb-6">
    <h4
        class="font-medium lg:text-2xl text-xl capitalize text-slate-900 inline-block ltr:pr-4 rtl:pl-4 mb-4 sm:mb-0 flex space-x-3 rtl:space-x-reverse">
        Dashboard</h4>
    <!-- <div class="flex sm:space-x-4 space-x-2 sm:justify-end items-center rtl:space-x-reverse">
        <button
            class="btn leading-0 inline-flex justify-center bg-white text-slate-700 dark:bg-slate-800 dark:text-slate-300 !font-normal">
            <span class="flex items-center">
                <iconify-icon class="text-xl ltr:mr-2 rtl:ml-2 font-light"
                    icon="heroicons-outline:calendar"></iconify-icon>
                <span>Weekly</span>
            </span>
        </button>
        <button
            class="btn leading-0 inline-flex justify-center bg-white text-slate-700 dark:bg-slate-800 dark:text-slate-300 !font-normal">
            <span class="flex items-center">
                <iconify-icon class="text-xl ltr:mr-2 rtl:ml-2 font-light"
                    icon="heroicons-outline:filter"></iconify-icon>
                <span>Select Date</span>
            </span>
        </button>
    </div> -->
</div>

<div class="grid grid-cols-12">
    <div class="2xl:col-span-12 lg:col-span-12 col-span-12">
        <div class="card mb-3">
            <header class="card-header">
                <h4 class="card-title">Statistic</h4>
            </header>
            <div class="card-body p-2">
                <!-- <div class="grid md:grid-cols-2 grid-cols-1 gap-3"> -->
                <div class="grid grid-cols-12 gap-3">
                    <div class="lg:col-span-3 col-span-12">
                        <div class="card">
                            <div class="card-body p-2">
                                <div id="progressChart"></div>
                                <div class="bg-slate-50 dark:bg-slate-900 rounded p-4 mt-8 flex justify-between flex-wrap">
                                    <div class="space-y-1">
                                        <h4 class="text-slate-600 dark:text-slate-200 text-xs font-normal">Available</h4>
                                        <div class="text-sm font-medium text-slate-900 dark:text-white">
                                            <h5 class="text-success-500">999</h5>
                                        </div>
                                    </div>
                                    <div class="space-y-1">
                                        <h4 class="text-slate-600 dark:text-slate-200 text-xs font-normal">Occupied</h4>
                                        <div class="text-sm font-medium text-slate-900 dark:text-white">
                                            <h5 class="text-danger-500">999</h5>
                                        </div>
                                    </div>
                                    <div class="space-y-1">
                                        <h4 class="text-slate-600 dark:text-slate-200 text-xs font-normal">Total</h4>
                                        <div class="text-sm font-medium text-slate-900 dark:text-white">
                                            <h5 class="text-dark-500">999</h5>
                                        </div>
                                    </div>
                                    <div class="space-y-1 m-auto">

                                        <button class="btn btn-sm btn-secondary shadow-base2 mx-2">View Map</button>
                                        <button class="btn btn-sm btn-secondary shadow-base2 mx-2">View Stats</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="lg:col-span-9 col-span-12">
                        <div class="card">
                            <div class="card-body">
                                <div id="highchart"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
    </div>

</div>
<br>

<div>
    <div class="grid grid-cols-12 gap-5 apex-charts" id="radarData">

        <div class="lg:col-span-12 col-span-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Parkomate</h4>
                </div>
                <div class="card-body">
                    <div id="progressChart"></div>
                </div>
            </div>
        </div>

    </div>
</div>
<br>
<div id="highchart"></div>

<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<!-- dashboard init -->
<script src="{{ asset('dashboard/assets/js/dashboard.init.js') }}"></script>
<script src="https://cdn.amcharts.com/lib/5/index.js"></script>
<script src="https://cdn.amcharts.com/lib/5/xy.js"></script>
<script src="https://cdn.amcharts.com/lib/5/themes/Animated.js"></script>

<script>
    // Example data output
    var data = @json($data);
    // console.log(data);

    var last_hour_data_chart_root = am5.Root.new("highchart");

    last_hour_data_chart_root.setThemes([
        am5themes_Animated.new(last_hour_data_chart_root)
    ]);

    last_hour_data_chart_root.dateFormatter.setAll({
        dateFormat: "dd MMM, yyyy hh:mm a",
        dateFields: ["valueX"]
    });

    var easing = am5.ease.linear;

    window.last_hour_data_chart = last_hour_data_chart_root.container.children.push(am5xy.XYChart.new(last_hour_data_chart_root, {
        focusable: false,
        panX: true, // Disable panning on X-axis
        panY: false, // Disable panning on Y-axis
        wheelX: "none", // Disable zooming on X-axis
        wheelY: "none", // Disable zooming on Y-axis
        pinchZoomX: false, // Disable pinch zoom on X-axis
        pinchZoomY: false // Disable pinch zoom on Y-axis

        // panX: true,
        // panY: true,
        // wheelX: "panX",
        // wheelY: "zoomX",
        // pinchZoomX: true
    }));

    var xAxis = window.last_hour_data_chart.xAxes.push(am5xy.DateAxis.new(last_hour_data_chart_root, {
        maxDeviation: 0.5,
        groupData: false,
        extraMax: 0.1, // this adds some space in front
        extraMin: -0.1, // this removes some space form th beginning so that the line would not be cut off
        baseInterval: {
            timeUnit: "minute",
            count: 1
        },
        renderer: am5xy.AxisRendererX.new(last_hour_data_chart_root, {
            minGridDistance: 50
        }),
        tooltip: am5.Tooltip.new(last_hour_data_chart_root, {})
    }));

    var yAxis = window.last_hour_data_chart.yAxes.push(am5xy.ValueAxis.new(last_hour_data_chart_root, {
        renderer: am5xy.AxisRendererY.new(last_hour_data_chart_root, {})
    }));

    var series = window.last_hour_data_chart.series.push(am5xy.LineSeries.new(last_hour_data_chart_root, {
        name: "Occupancy",
        xAxis: xAxis,
        yAxis: yAxis,
        valueYField: "value",
        valueXField: "date",
        tooltip: am5.Tooltip.new(last_hour_data_chart_root, {
            pointerOrientation: "horizontal",
            labelText: "{valueY}"
        })
    }));

    if (data.length > 0) {
        data[data.length - 1].bullet = true;
    }
    series.data.setAll(data);
    // Create animating bullet by adding two circles in a bullet container and
    // animating radius and opacity of one of them.
    series.bullets.push(function(last_hour_data_chart_root, series, dataItem) {
        // only create sprite if bullet == true in data context
        if (dataItem.dataContext.bullet) {
            var container = am5.Container.new(last_hour_data_chart_root, {});
            var circle0 = container.children.push(am5.Circle.new(last_hour_data_chart_root, {
                radius: 5,
                fill: am5.color(0xff0000)
            }));
            var circle1 = container.children.push(am5.Circle.new(last_hour_data_chart_root, {
                radius: 5,
                fill: am5.color(0xff0000)
            }));

            circle1.animate({
                key: "radius",
                to: 20,
                duration: 1000,
                easing: am5.ease.out(am5.ease.cubic),
                loops: Infinity
            });
            circle1.animate({
                key: "opacity",
                to: 0,
                from: 1,
                duration: 1000,
                easing: am5.ease.out(am5.ease.cubic),
                loops: Infinity
            });

            return am5.Bullet.new(last_hour_data_chart_root, {
                locationX: undefined,
                sprite: container
            })
        }
    });

    // Add cursor
    // https://www.amcharts.com/docs/v5/charts/xy-chart/cursor/
    var cursor = window.last_hour_data_chart.set("cursor", am5xy.XYCursor.new(last_hour_data_chart_root, {
        xAxis: xAxis
    }));
    cursor.lineY.set("visible", false);
    window.last_hour_data_chart.appear(1000, 100);
    setInterval(function() {
        appendLast4HourGraphInitialData();
    }, 60000);
</script>
@endsection