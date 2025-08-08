@extends('dashboard.header')
@section('content')

<style>
    .chartdiv {
        width: 100%;
        height: 370px;
        max-width: 100%;
    }

    .text-center {
        text-align: center !important;
    }

    #chartTouch {
        touch-action: auto;
        /* Allow touch events */
        pointer-events: auto;
        /* Ensure the chart is interactive */
    }
</style>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<div>
    <div class="flex justify-between flex-wrap items-center">
        <h4
            class="font-medium lg:text-2xl text-xl capitalize text-slate-900 inline-block ltr:pr-4 rtl:pl-4 sm:mb-0 flex space-x-3 rtl:space-x-reverse">
            Historical Data</h4>
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
                <select name="chart_view" id="chart_view" class="form-control w-full mt-2">
                    <option value="1" class="py-1 inline-block font-Inter font-normal text-sm text-slate-600" {{Auth::user()->chart_view == 1 ? 'selected' : ''}}>Occupied Data</option>
                    <option value="2" class="py-1 inline-block font-Inter font-normal text-sm text-slate-600" {{Auth::user()->chart_view == 2 ? 'selected' : ''}}>Available Data</option>
                    <option value="3" class="py-1 inline-block font-Inter font-normal text-sm text-slate-600" {{Auth::user()->chart_view == 3 ? 'selected' : ''}}>In-Out Data</option>
                </select>
            </div>
        </div>
    </div>

    <div class="flex justify-between flex-wrap items-center mb-4">
        <div class="font-medium capitalize text-slate-900 inline-block ltr:pr-4 rtl:pl-4 sm:mb-0 flex space-x-3 rtl:space-x-reverse dark:text-white" id="lastUpdate">
            <form method="POST" action="{{ route('dashboard/historical-data') }}" id="dateForm">
                @csrf
                <div class="grid xl:grid-cols-3 grid-cols-3 gap-6">
                    <div class="card">
                        <div class="card-body flex flex-col p-1">
                            <div class="card-text h-full ">
                                From<input type="text" onchange="submitForm1()" id="startDate" value="{{$startDate}}" placeholder="Select Date" name="startDate" class="form-control themecolor" required>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-body flex flex-col p-1">
                            <div class="card-text h-full ">
                                To<input type="text" onchange="submitForm()" id="endDate" value="{{$endDate}}" placeholder="Select Date" name="endDate" class="form-control themecolor" required>
                            </div>
                        </div>
                    </div>


                </div>
            </form>
        </div>

    </div>


    <div class="grid grid-cols-12 text-center">
        <div class="2xl:col-span-12 lg:col-span-12 col-span-12" id="detailedData">

        </div>
    </div>

</div>

<div id="data-container"
    data-floorWiseData='@json($floorWiseData)'
    data-count="{{ $count }}"
    data-startDate="{{ $startDate }}"
    data-active="{{ $active }}">
</div>
<script src="https://cdn.amcharts.com/lib/5/index.js"></script>
<script src="https://cdn.amcharts.com/lib/5/xy.js"></script>
<script src="https://cdn.amcharts.com/lib/5/themes/Animated.js"></script>

<!-- <script src="{{ asset('dashboard/assets/js/detailedView.js') }}"></script> -->
<script>
    flatpickr("#startDate", {
        dateFormat: "Y-m-d",
        maxDate: new Date() // Disables future dates
    });
    flatpickr("#endDate", {
        dateFormat: "Y-m-d",
        maxDate: new Date() // Disables future dates
    });

    function submitForm1() {
        var startdate = document.getElementById('startDate').value;
        var enddate = document.getElementById('endDate').value;

        if (startdate == enddate) {
            document.getElementById('dateForm').submit();
        }

    }

    function submitForm() {
        var startdate = document.getElementById('startDate').value;
        var enddate = document.getElementById('endDate').value;

        if (startdate === '' || startdate === null) {
            alert('Please select a start date');
            return false;
        }

        if (enddate === '' || enddate === null) {
            alert('Please select an end date');
            return false;
        }

        // console.log(start, end);

        // if (startdate != enddate) {
        //     alert('Start date and end date must be same.');
        //     return false;

        // }

        if (enddate < startdate) {
            alert('End date cannot be earlier than the start date.');
            return false;
        }

        document.getElementById('dateForm').submit();
    }

    window.chartview = "{{ Auth::user()->chart_view }}";

    var theme = localStorage.getItem('theme');
    if (theme == 'light' || theme == null) {
        var color1 = '#5156be';
        var color2 = '#000000';
        var colColor1 = '#FFFFFF';
        var colColor2 = 'black';
    } else {
        var color1 = '#ffffff';
        var color2 = '#ffffff';
        var colColor1 = '#0F172A';
        var colColor2 = '#FFFFFF';
    }
    var chartview = window.chartview;

    var chartInstances = {};
    var chartReferences = {};
    var columnInstances = {};

    $(document).ready(function() {
        if (theme == 'dark') {

            $(".themecolor").attr("style", "background-color: #334155 !important;color: white !important;");
        }
        var container = document.getElementById('data-container');
        var floorWiseData = JSON.parse(container.dataset.floorwisedata);
        var count = container.dataset.count;

        // console.log(floorWiseData);


        // console.log(floorWiseData[0]);


        $('#chart_view').on('change', function() {
            const selectedValue = $(this).val();

            $.ajax({
                url: 'change-data', // Replace with your route
                type: 'GET',
                data: {
                    value: selectedValue,
                },
                success: function(response) {
                    if (response.status === 'success') {
                        location.reload(); // Reload the entire page
                    }
                }
            });
        });

        var parentElement = document.querySelector('#detailedData');
        fetchDataAndRender(parentElement, floorWiseData, count);
    });

    function fetchDataAndRender(parentElement, floorWiseData, count) {

        if (count <= 2) {
            const item = floorWiseData[1];
            var name1 = item.name.replace(/\s+/g, "");
            var htmlContent = `
                    <div class="card mb-3">
                        <header class="card-header">
                            <h4 class="card-title">${item.name}</h4>
                        </header>
                        <div class="card-body p-2">
                            <div class="grid grid-cols-12 gap-3">
                                <div class="lg:col-span-3 col-span-12">
                                    <div class="card">
                                        <div class="card-body p-2">
                                            <table class="min-w-full divide-y divide-slate-100 table-fixed dark:divide-slate-700">
                                                <thead class="bg-slate-200 dark:bg-slate-700">
                                                    <tr>

                                                        <th scope="col" class="text-center table-th ">
                                                            Check-In Count
                                                        </th>

                                                        <th scope="col" class="text-center table-th ">
                                                            Check-Out Count
                                                        </th>
                                                    </tr>
                                                </thead>
                                                <tbody class="bg-white divide-y divide-slate-100 dark:bg-slate-800 dark:divide-slate-700">
                                                    <tr>

                                                        <td scope="col" class=" table-td ">
                                                            ${item.check_in_count}
                                                        </td>

                                                        <td scope="col" class=" table-td ">
                                                            ${item.check_out_count}
                                                        </td>
                                                    </tr>
                                                </tbody>
                                                <thead class="bg-slate-200 dark:bg-slate-700">
                                                    <tr>

                                                        <th scope="col" class="text-center table-th ">
                                                            Min Count
                                                        </th>
                                                        <th scope="col" class="text-center table-th ">
                                                            Max Count
                                                        </th>
                                                    </tr>
                                                </thead>
                                                <tbody class="bg-white divide-y divide-slate-100 dark:bg-slate-800 dark:divide-slate-700">
                                                    <tr>

                                                        <td scope="col" class=" table-td ">
                                                            ${item.min_count}
                                                        </td>
                                                        <td scope="col" class=" table-td ">
                                                            ${item.max_count}
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                            <table class="min-w-full divide-y divide-slate-100 table-fixed dark:divide-slate-700">
                                                <thead class="bg-slate-200 dark:bg-slate-700">
                                                    <tr>

                                                        <th scope="col" class="text-center table-th ">
                                                            Min Time<br>(Min)
                                                        </th>
                                                        <th scope="col" class="text-center table-th ">
                                                            Max Time<br>(Min)
                                                        </th>
                                                        <th scope="col" class="text-center table-th ">
                                                            Avg Time<br>(Min)
                                                        </th>
                                                    </tr>
                                                </thead>
                                                <tbody class="bg-white divide-y divide-slate-100 dark:bg-slate-800 dark:divide-slate-700">
                                                    <tr>

                                                        <td scope="col" class=" table-td ">
                                                            ${item.min_time}
                                                        </td>
                                                        <td scope="col" class=" table-td ">
                                                            ${item.max_time}
                                                        </td>
                                                        <td scope="col" class=" table-td ">
                                                            ${item.avg_time}
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <div class="lg:col-span-9 col-span-12">
                                    <div class="card">
                                        <div class="card-body">
                                            <div id="chart${name1}" class="chartdiv"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                `;

            parentElement.insertAdjacentHTML('beforeend', htmlContent);

            var chartId = "chart" + name1;
            if (chartview == 1 || chartview == 2) {
                perMinuteChart(chartId, item.chart);

            }
            if (chartview == 3) {
                columnChart(chartId, item.columndata);

            }

            // var idData = item.name + item.name;
            // var chartElement = document.querySelector(`#${idData}`);

            // chartInstances[idData] = new ApexCharts(chartElement, options);
            // chartInstances[idData].render();
            // chartInstances[idData].updateSeries([parseFloat(item.percentage)]);
        } else {

            floorWiseData.forEach(item => {
                var name1 = item.name.replace(/\s+/g, "");
                var htmlContent = `
                    <div class="card mb-3">
                        <header class="card-header">
                            <h4 class="card-title">${item.name}</h4>
                        </header>
                        <div class="card-body p-2">
                            <div class="grid grid-cols-12 gap-3">
                                <div class="lg:col-span-3 col-span-12">
                                    <div class="card">
                                        <div class="card-body p-2">
                                            <table class="min-w-full divide-y divide-slate-100 table-fixed dark:divide-slate-700">
                                                <thead class="bg-slate-200 dark:bg-slate-700">
                                                    <tr>

                                                        <th scope="col" class="text-center table-th ">
                                                            Check-In Count
                                                        </th>

                                                        <th scope="col" class="text-center table-th ">
                                                            Check-Out Count
                                                        </th>
                                                    </tr>
                                                </thead>
                                                <tbody class="bg-white divide-y divide-slate-100 dark:bg-slate-800 dark:divide-slate-700">
                                                    <tr>

                                                        <td scope="col" class=" table-td ">
                                                            ${item.check_in_count}
                                                        </td>

                                                        <td scope="col" class=" table-td ">
                                                            ${item.check_out_count}
                                                        </td>
                                                    </tr>
                                                </tbody>
                                                <thead class="bg-slate-200 dark:bg-slate-700">
                                                    <tr>

                                                        <th scope="col" class="text-center table-th ">
                                                            Min Count
                                                        </th>
                                                        <th scope="col" class="text-center table-th ">
                                                            Max Count
                                                        </th>
                                                    </tr>
                                                </thead>
                                                <tbody class="bg-white divide-y divide-slate-100 dark:bg-slate-800 dark:divide-slate-700">
                                                    <tr>

                                                        <td scope="col" class=" table-td ">
                                                            ${item.min_count}
                                                        </td>
                                                        <td scope="col" class=" table-td ">
                                                            ${item.max_count}
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                            <table class="min-w-full divide-y divide-slate-100 table-fixed dark:divide-slate-700">
                                                <thead class="bg-slate-200 dark:bg-slate-700">
                                                    <tr>

                                                        <th scope="col" class="text-center table-th ">
                                                            Min Time<br>(Min)
                                                        </th>
                                                        <th scope="col" class="text-center table-th ">
                                                            Max Time<br>(Min)
                                                        </th>
                                                        <th scope="col" class="text-center table-th ">
                                                            Avg Time<br>(Min)
                                                        </th>
                                                    </tr>
                                                </thead>
                                                <tbody class="bg-white divide-y divide-slate-100 dark:bg-slate-800 dark:divide-slate-700">
                                                    <tr>

                                                        <td scope="col" class=" table-td ">
                                                            ${item.min_time}
                                                        </td>
                                                        <td scope="col" class=" table-td ">
                                                            ${item.max_time}
                                                        </td>
                                                        <td scope="col" class=" table-td ">
                                                            ${item.avg_time}
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <div class="lg:col-span-9 col-span-12">
                                    <div class="card">
                                        <div class="card-body">
                                            <div id="chart${name1}" class="chartdiv"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                `;

                parentElement.insertAdjacentHTML('beforeend', htmlContent);

                var chartId = "chart" + name1;
                if (chartview == 1 || chartview == 2) {
                    perMinuteChart(chartId, item.chart);

                }
                if (chartview == 3) {
                    columnChart(chartId, item.columndata);

                }

                // var idData = item.name + item.name;
                // var chartElement = document.querySelector(`#${idData}`);

                // chartInstances[idData] = new ApexCharts(chartElement, options);
                // chartInstances[idData].render();
                // chartInstances[idData].updateSeries([parseFloat(item.percentage)]);
            });
        }
    }

    function columnChart(id, data) {
        // console.log(data);

        // If the chart instance already exists, update its data.
        if (columnInstances[id]) {
            columnInstances[id].series[0].setData(data.InData, false);
            columnInstances[id].series[1].setData(data.OutData, false);
            columnInstances[id].redraw();
        } else {
            // Create a new chart instance and store it in columnInstances.
            // columnInstances[id] = Highcharts.chart(id, {
            //     chart: {
            //         type: 'column'
            //     },
            //     title: {
            //         text: null
            //     },
            //     xAxis: {
            //         categories: data.hoursArray12H,
            //     },
            //     yAxis: {
            //         title: {
            //             text: 'In-Out Count'
            //         }
            //     },
            //     series: [{
            //         name: 'In',
            //         data: data.InData,
            //         color: '#90ed7d'
            //     }, {
            //         name: 'Out',
            //         data: data.OutData,
            //         color: '#f45b5b'
            //     }]
            // });

            columnInstances[id] = Highcharts.chart(id, {
                chart: {
                    type: 'column',
                    backgroundColor: colColor1, // Dark background for the chart
                },
                title: {
                    text: null
                },
                xAxis: {
                    categories: data.hoursArray12H,
                    labels: {
                        style: {
                            color: colColor2 // Light text for x-axis labels
                        }
                    },
                    lineColor: colColor2, // Light gray line for x-axis
                    tickColor: colColor2 // Light gray ticks for x-axis
                },
                yAxis: {
                    title: {
                        text: 'In-Out Count',
                        style: {
                            color: colColor2 // Light text for y-axis title
                        }
                    },
                    labels: {
                        style: {
                            color: colColor2 // Light text for y-axis labels
                        }
                    },
                    gridLineColor: colColor2 // Light gray grid lines
                },
                series: [{
                    name: 'In',
                    data: data.InData,
                    color: '#90ed7d' // Green color for "In" series
                }, {
                    name: 'Out',
                    data: data.OutData,
                    color: '#f45b5b' // Red color for "Out" series
                }],
                legend: {
                    itemStyle: {
                        color: colColor2 // White text for legend items
                    }
                }
            });
        }
    }

    function perMinuteChart(id, data) {
        var perminutechart = am5.Root.new(id);

        perminutechart.setThemes([
            am5themes_Animated.new(perminutechart)
        ]);

        perminutechart.dateFormatter.setAll({
            dateFormat: "dd MMM, yyyy hh:mm a",
            dateFields: ["valueX"]
        });

        var easing = am5.ease.linear;

        window.last_hour_data_chart = perminutechart.container.children.push(am5xy.XYChart.new(perminutechart, {
            focusable: true,
            panX: true,
            panY: false,
            wheelX: "none",
            wheelY: "none",
            pinchZoomX: false,
            pinchZoomY: false
        }));

        chartReferences[id] = window.last_hour_data_chart;

        var xAxis = window.last_hour_data_chart.xAxes.push(am5xy.DateAxis.new(perminutechart, {
            maxDeviation: 0.5,
            groupData: false,
            extraMax: 0.1,
            extraMin: -0.1,
            baseInterval: {
                timeUnit: "minute",
                count: 1
            },
            renderer: am5xy.AxisRendererX.new(perminutechart, {
                minGridDistance: 50
            }),
            tooltip: am5.Tooltip.new(perminutechart, {})
        }));

        var yAxis = window.last_hour_data_chart.yAxes.push(am5xy.ValueAxis.new(perminutechart, {
            renderer: am5xy.AxisRendererY.new(perminutechart, {})
        }));

        if (theme == 'dark') {
            // Set X-Axis label color to white
            xAxis.get("renderer").labels.template.setAll({
                fill: am5.color(0xFFFFFF) // White color
            });

            // Set Y-Axis label color to white
            yAxis.get("renderer").labels.template.setAll({
                fill: am5.color(0xFFFFFF) // White color
            });
        }

        var series = window.last_hour_data_chart.series.push(am5xy.LineSeries.new(perminutechart, {
            name: "Occupancy",
            xAxis: xAxis,
            yAxis: yAxis,
            valueYField: "value",
            valueXField: "date",
            tooltip: am5.Tooltip.new(perminutechart, {
                pointerOrientation: "horizontal",
                labelText: "{valueY}"
            })
        }));

        if (data.length > 0) {
            data[data.length - 1].bullet = true;
        }
        series.data.setAll(data);

        series.bullets.push(function(perminutechart, series, dataItem) {
            if (dataItem.dataContext.bullet) {
                var container = am5.Container.new(perminutechart, {});
                var circle0 = container.children.push(am5.Circle.new(perminutechart, {
                    radius: 5,
                    fill: am5.color(0xff0000)
                }));
                var circle1 = container.children.push(am5.Circle.new(perminutechart, {
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

                return am5.Bullet.new(perminutechart, {
                    locationX: undefined,
                    sprite: container
                })
            }
        });

        var cursor = window.last_hour_data_chart.set("cursor", am5xy.XYCursor.new(perminutechart, {
            xAxis: xAxis
        }));
        cursor.lineY.set("visible", false);
        window.last_hour_data_chart.appear(1000, 100);
    }
</script>
@endsection