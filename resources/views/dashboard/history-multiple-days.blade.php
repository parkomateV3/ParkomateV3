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
                    <option value="1" class="py-1 inline-block font-Inter font-normal text-sm text-slate-600" {{Auth::user()->chart_view == 1 ? 'selected' : ''}}>Check In-Out Data</option>
                    <option value="2" class="py-1 inline-block font-Inter font-normal text-sm text-slate-600" {{Auth::user()->chart_view == 2 ? 'selected' : ''}}>Min-Max Count Data</option>
                    <option value="3" class="py-1 inline-block font-Inter font-normal text-sm text-slate-600" {{Auth::user()->chart_view == 3 ? 'selected' : ''}}>Financial-Model</option>
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
    data-endDate="{{ $endDate }}">
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
                                            ${chartview != 3 ? `
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
                                                </tbody>` : `
                                                <thead class="bg-slate-200 dark:bg-slate-700">
                                                    <tr>
                                                        <th scope="col" class="text-center table-th ">
                                                            Max Amount
                                                        </th>

                                                        <th scope="col" class="text-center table-th ">
                                                            Min Amount
                                                        </th>
                                                    </tr>
                                                </thead>
                                                <tbody class="bg-white divide-y divide-slate-100 dark:bg-slate-800 dark:divide-slate-700">
                                                    <tr>
                                                        <td scope="col" class=" table-td ">
                                                            ${item.max_amount}
                                                        </td>

                                                        <td scope="col" class=" table-td ">
                                                            ${item.min_amount}
                                                        </td>
                                                    </tr>
                                                </tbody>
                                                `}
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <div class="lg:col-span-9 col-span-12">
                                    <div class="card">
                                        <div class="card-body">
                                            <div id="chart${item.name}" class="chartdiv"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                `;

            parentElement.insertAdjacentHTML('beforeend', htmlContent);

            var chartId = "chart" + item.name;
            if (chartview == 1) {
                inOutChart(chartId, item.chart);
            }
            if (chartview == 2) {
                minMaxChart(chartId, item.chart);
            }
            if (chartview == 3) {
                financialModelChart(chartId, item.chart);
            }

            // var idData = item.name + item.name;
            // var chartElement = document.querySelector(`#${idData}`);

            // chartInstances[idData] = new ApexCharts(chartElement, options);
            // chartInstances[idData].render();
            // chartInstances[idData].updateSeries([parseFloat(item.percentage)]);
        } else {

            floorWiseData.forEach(item => {
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
                                            ${chartview != 3 ? `
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
                                                </tbody>` : `
                                                <thead class="bg-slate-200 dark:bg-slate-700">
                                                    <tr>
                                                        <th scope="col" class="text-center table-th ">
                                                            Max Amount
                                                        </th>

                                                        <th scope="col" class="text-center table-th ">
                                                            Min Amount
                                                        </th>
                                                    </tr>
                                                </thead>
                                                <tbody class="bg-white divide-y divide-slate-100 dark:bg-slate-800 dark:divide-slate-700">
                                                    <tr>
                                                        <td scope="col" class=" table-td ">
                                                            ${item.max_amount}
                                                        </td>

                                                        <td scope="col" class=" table-td ">
                                                            ${item.min_amount}
                                                        </td>
                                                    </tr>
                                                </tbody>
                                                `}
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <div class="lg:col-span-9 col-span-12">
                                    <div class="card">
                                        <div class="card-body">
                                            <div id="chart${item.name}" class="chartdiv"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                `;

                parentElement.insertAdjacentHTML('beforeend', htmlContent);

                var chartId = "chart" + item.name;
                if (chartview == 1) {
                    inOutChart(chartId, item.chart);
                }
                if (chartview == 2) {
                    minMaxChart(chartId, item.chart);
                }
                if (chartview == 3) {
                    financialModelChart(chartId, item.chart);
                }
                // var idData = item.name + item.name;
                // var chartElement = document.querySelector(`#${idData}`);

                // chartInstances[idData] = new ApexCharts(chartElement, options);
                // chartInstances[idData].render();
                // chartInstances[idData].updateSeries([parseFloat(item.percentage)]);
            });
        }
    }

    function financialModelChart(id, data) {
        am5.ready(function() {
            var root = am5.Root.new(id);
            root.setThemes([am5themes_Animated.new(root)]);

            var chart = root.container.children.push(am5xy.XYChart.new(root, {
                focusable: true,
                panX: true,
                panY: false,
                wheelX: "none",
                wheelY: "none",
                pinchZoomX: false,
                pinchZoomY: false
            }));

            var cursor = chart.set("cursor", am5xy.XYCursor.new(root, {
                behavior: "none"
            }));
            cursor.lineY.set("visible", false);

            var xAxis = chart.xAxes.push(am5xy.DateAxis.new(root, {
                maxDeviation: 0,
                baseInterval: {
                    timeUnit: "day",
                    count: 1
                },
                renderer: am5xy.AxisRendererX.new(root, {
                    minorGridEnabled: true,
                    minGridDistance: 60
                }),
                tooltip: am5.Tooltip.new(root, {})
            }));

            var yAxis = chart.yAxes.push(am5xy.ValueAxis.new(root, {
                renderer: am5xy.AxisRendererY.new(root, {})
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

            var checkInSeries = chart.series.push(am5xy.LineSeries.new(root, {
                name: "Amount",
                xAxis: xAxis,
                yAxis: yAxis,
                valueYField: "checkIn",
                valueXField: "date",
                stroke: am5.color(0x28a745), // Green
                fill: am5.color(0x28a745),
                tooltip: am5.Tooltip.new(root, {
                    labelText: "{valueY}₹"
                })
            }));

            checkInSeries.bullets.push(function() {
                return am5.Bullet.new(root, {
                    sprite: am5.Circle.new(root, {
                        radius: 5,
                        fill: am5.color(0x28a745)
                    })
                });
            });

            // var checkOutSeries = chart.series.push(am5xy.LineSeries.new(root, {
            //     name: "Max Count",
            //     xAxis: xAxis,
            //     yAxis: yAxis,
            //     valueYField: "checkOut",
            //     valueXField: "date",
            //     stroke: am5.color(0xdc3545), // Red
            //     fill: am5.color(0xdc3545),
            //     tooltip: am5.Tooltip.new(root, {
            //         labelText: "{valueY}"
            //     })
            // }));

            // checkOutSeries.bullets.push(function() {
            //     return am5.Bullet.new(root, {
            //         sprite: am5.Circle.new(root, {
            //             radius: 5,
            //             fill: am5.color(0xdc3545)
            //         })
            //     });
            // });

            checkInSeries.data.setAll(data);
            // checkOutSeries.data.setAll(data);

            var legend = chart.children.push(am5.Legend.new(root, {
                centerX: am5.p50,
                x: am5.p50,
                marginTop: 20,
                layout: root.horizontalLayout
            }));

            if (theme == 'dark') {
                // Set legend label color to white
                legend.labels.template.setAll({
                    fill: am5.color(0xFFFFFF) // White color
                });
            }

            // legend.data.setAll([checkInSeries, checkOutSeries]);
            legend.data.setAll([checkInSeries]);

            checkInSeries.appear(1000);
            // checkOutSeries.appear(1000);
            chart.appear(1000, 100);
        });
    }

    function minMaxChart(id, data) {
        am5.ready(function() {
            var root = am5.Root.new(id);
            root.setThemes([am5themes_Animated.new(root)]);

            var chart = root.container.children.push(am5xy.XYChart.new(root, {
                focusable: true,
                panX: true,
                panY: false,
                wheelX: "none",
                wheelY: "none",
                pinchZoomX: false,
                pinchZoomY: false
            }));

            var cursor = chart.set("cursor", am5xy.XYCursor.new(root, {
                behavior: "none"
            }));
            cursor.lineY.set("visible", false);

            var xAxis = chart.xAxes.push(am5xy.DateAxis.new(root, {
                maxDeviation: 0,
                baseInterval: {
                    timeUnit: "day",
                    count: 1
                },
                renderer: am5xy.AxisRendererX.new(root, {
                    minorGridEnabled: true,
                    minGridDistance: 60
                }),
                tooltip: am5.Tooltip.new(root, {})
            }));

            var yAxis = chart.yAxes.push(am5xy.ValueAxis.new(root, {
                renderer: am5xy.AxisRendererY.new(root, {})
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

            var checkInSeries = chart.series.push(am5xy.LineSeries.new(root, {
                name: "Min Count",
                xAxis: xAxis,
                yAxis: yAxis,
                valueYField: "checkIn",
                valueXField: "date",
                stroke: am5.color(0x28a745), // Green
                fill: am5.color(0x28a745),
                tooltip: am5.Tooltip.new(root, {
                    labelText: "{valueY}"
                })
            }));

            checkInSeries.bullets.push(function() {
                return am5.Bullet.new(root, {
                    sprite: am5.Circle.new(root, {
                        radius: 5,
                        fill: am5.color(0x28a745)
                    })
                });
            });

            var checkOutSeries = chart.series.push(am5xy.LineSeries.new(root, {
                name: "Max Count",
                xAxis: xAxis,
                yAxis: yAxis,
                valueYField: "checkOut",
                valueXField: "date",
                stroke: am5.color(0xdc3545), // Red
                fill: am5.color(0xdc3545),
                tooltip: am5.Tooltip.new(root, {
                    labelText: "{valueY}"
                })
            }));

            checkOutSeries.bullets.push(function() {
                return am5.Bullet.new(root, {
                    sprite: am5.Circle.new(root, {
                        radius: 5,
                        fill: am5.color(0xdc3545)
                    })
                });
            });

            checkInSeries.data.setAll(data);
            checkOutSeries.data.setAll(data);

            var legend = chart.children.push(am5.Legend.new(root, {
                centerX: am5.p50,
                x: am5.p50,
                marginTop: 20,
                layout: root.horizontalLayout
            }));

            if (theme == 'dark') {
                // Set legend label color to white
                legend.labels.template.setAll({
                    fill: am5.color(0xFFFFFF) // White color
                });
            }

            legend.data.setAll([checkInSeries, checkOutSeries]);

            checkInSeries.appear(1000);
            checkOutSeries.appear(1000);
            chart.appear(1000, 100);
        });
    }

    function inOutChart(id, data) {
        am5.ready(function() {
            var root = am5.Root.new(id);
            root.setThemes([am5themes_Animated.new(root)]);

            var chart = root.container.children.push(am5xy.XYChart.new(root, {
                focusable: true,
                panX: true,
                panY: false,
                wheelX: "none",
                wheelY: "none",
                pinchZoomX: false,
                pinchZoomY: false
            }));

            var cursor = chart.set("cursor", am5xy.XYCursor.new(root, {
                behavior: "none"
            }));
            cursor.lineY.set("visible", false);

            var xAxis = chart.xAxes.push(am5xy.DateAxis.new(root, {
                maxDeviation: 0,
                baseInterval: {
                    timeUnit: "day",
                    count: 1
                },
                renderer: am5xy.AxisRendererX.new(root, {
                    minorGridEnabled: true,
                    minGridDistance: 60
                }),
                tooltip: am5.Tooltip.new(root, {})
            }));

            var yAxis = chart.yAxes.push(am5xy.ValueAxis.new(root, {
                renderer: am5xy.AxisRendererY.new(root, {})
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

            var checkInSeries = chart.series.push(am5xy.LineSeries.new(root, {
                name: "Check-In",
                xAxis: xAxis,
                yAxis: yAxis,
                valueYField: "checkIn",
                valueXField: "date",
                stroke: am5.color(0x28a745), // Green
                fill: am5.color(0x28a745),
                tooltip: am5.Tooltip.new(root, {
                    labelText: "{valueY}"
                })
            }));

            checkInSeries.bullets.push(function() {
                return am5.Bullet.new(root, {
                    sprite: am5.Circle.new(root, {
                        radius: 5,
                        fill: am5.color(0x28a745)
                    })
                });
            });

            var checkOutSeries = chart.series.push(am5xy.LineSeries.new(root, {
                name: "Check-Out",
                xAxis: xAxis,
                yAxis: yAxis,
                valueYField: "checkOut",
                valueXField: "date",
                stroke: am5.color(0xdc3545), // Red
                fill: am5.color(0xdc3545),
                tooltip: am5.Tooltip.new(root, {
                    labelText: "{valueY}"
                })
            }));

            checkOutSeries.bullets.push(function() {
                return am5.Bullet.new(root, {
                    sprite: am5.Circle.new(root, {
                        radius: 5,
                        fill: am5.color(0xdc3545)
                    })
                });
            });

            checkInSeries.data.setAll(data);
            checkOutSeries.data.setAll(data);

            var legend = chart.children.push(am5.Legend.new(root, {
                centerX: am5.p50,
                x: am5.p50,
                marginTop: 20,
                layout: root.horizontalLayout
            }));

            if (theme == 'dark') {
                // Set legend label color to white
                legend.labels.template.setAll({
                    fill: am5.color(0xFFFFFF) // White color
                });
            }

            legend.data.setAll([checkInSeries, checkOutSeries]);

            checkInSeries.appear(1000);
            checkOutSeries.appear(1000);
            chart.appear(1000, 100);
        });
    }
</script>
@endsection