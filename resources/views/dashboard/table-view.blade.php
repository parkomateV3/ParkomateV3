@extends('dashboard.header')
@section('content')

<style>
    #highchart {
        width: 100%;
        height: 600px;
    }
</style>

<div class="flex justify-between flex-wrap items-center mb-6" style="margin-bottom: 0px !important;">
    <h4 class="font-medium lg:text-2xl text-xl capitalize text-slate-900 inline-block ltr:pr-4 rtl:pl-4 mb-4 sm:mb-0 flex space-x-3 rtl:space-x-reverse">Table View</h4>
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

<div>
    <div class="grid grid-cols-12 gap-5 apex-charts" id="radarData">



    </div>
</div>
<div class="grid xl:grid-cols-1 grid-cols-1 gap-5 mt-4">
    <!-- BEGIN: Basic Table -->
    <div class="card">
        <div class="card-body px-6 pb-6">
            <div class="overflow-x-auto -mx-6">
                <div class="inline-block min-w-full align-middle">
                    <div class="overflow-hidden ">
                        <table class="min-w-full divide-y divide-slate-100 table-fixed dark:divide-slate-700">
                            <thead class=" border-t border-slate-100 dark:border-slate-800">
                                <tr>
                                    <th colspan="2" scope="col" class="table-th border-slate-900 dark:bg-slate-800 dark:border-slate-700">
                                        <h6 class="text-center">{{$title}}</h6>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-slate-100 dark:bg-slate-800 dark:divide-slate-700" id="tableData">

                                <tr>
                                    <th colspan="2" scope="col" class="table-th border-slate-900 dark:bg-slate-800 dark:border-slate-700">
                                        <h6 class="text-center">No Data</h6>
                                    </th>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<br>

<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>

<script>
    var theme = localStorage.getItem('theme');

    if (theme == 'light' || theme == null) {
        var color1 = '#5156be';
        var color2 = '#000000';
    } else {
        var color1 = '#ffffff';
        var color2 = '#ffffff';
    }
    var chartInstances = {};

    $(document).ready(function() {
        console.log(theme);
        refreshDashboard();
        GraphInitialData();
        // getLast4HourGraphInitialData();


        setInterval(function() {
            refreshDashboard();
            // const randomNumber = Math.floor(Math.random() * 100) + 1;
            // window.liveStatusChart.updateSeries([randomNumber]);
        }, 5000);
    });

    var radialchartColors = ["#5156be", "#34c38f"];
    var options = {
        chart: {
            height: 270,
            type: 'radialBar',
            offsetY: -10,
            zoom: {
                enabled: false
            },
            toolbar: {
                show: false
            }
        },
        plotOptions: {
            radialBar: {
                startAngle: -130,
                endAngle: 130,
                dataLabels: {
                    name: {
                        show: true,
                        color: color1
                    },
                    value: {
                        offsetY: 10,
                        fontSize: '18px',
                        color: color2,
                        formatter: function(val) {
                            return val + "%";
                        }
                    }
                }
            }
        },
        colors: [radialchartColors[0]],
        fill: {
            type: 'gradient',
            gradient: {
                shade: 'dark',
                type: 'horizontal',
                gradientToColors: [radialchartColors[1]],
                shadeIntensity: 0.15,
                inverseColors: false,
                opacityFrom: 1,
                opacityTo: 1,
                stops: [20, 60]
            },
        },
        stroke: {
            dashArray: 4,
        },
        legend: {
            show: false
        },
        series: [0],
        labels: ['Occupancy'],
    }

    function GraphInitialData() {
        // Select the parent element where you want to append the new HTML
        const parentElement = document.querySelector('#radarData');

        $.get("table-view-data", function(data, status) {
            // var obj = JSON.parse(data);
            // console.log(data);
            var column = 0;
            var count = data.count;
            if (count == 4) {
                column = 3;
            } else {
                column = 4;
            }
            if (count <= 2) {
                const item = data.occupancy[1];
                // Define the HTML code as a string
                var name1 = item.name.replace(/\s+/g, "");
                const htmlContent = `
                    <div class="lg:col-span-12 col-span-12">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">${item.name}</h4>
                            </div>
                            <div class="card-body">
                                <div id="${name1}${name1}"></div>
                                <div class="bg-slate-50 dark:bg-slate-900 rounded p-4 mt-2 flex justify-between flex-wrap" style="padding: 15px 50px;">
                                <div class="space-y-1">
                                    <h4 class="text-slate-600 dark:text-slate-200 text-xs font-normal">Available</h4>
                                    <div class="text-sm font-medium text-slate-900 dark:text-white">
                                        <h5 class="text-success-500" style="text-align:center;">${item.available}</h5>
                                    </div>
                                </div>
                                <div class="space-y-1">
                                    <h4 class="text-slate-600 dark:text-slate-200 text-xs font-normal">Occupied</h4>
                                    <div class="text-sm font-medium text-slate-900 dark:text-white">
                                        <h5 class="text-danger-500" style="text-align:center;">${item.occupied}</h5>
                                    </div>
                                </div>
                                <div class="space-y-1">
                                    <h4 class="text-slate-600 dark:text-slate-200 text-xs font-normal">Total</h4>
                                    <div class="text-sm font-medium text-slate-900 dark:text-white">
                                        <h5 class="text-dark-500" style="text-align:center;">${item.total}</h5>
                                    </div>
                                </div>
                            </div>
                            </div>
                        </div>
                    </div>
                    `;

                // var idData = "#" + item.name + item.name;
                var idData = name1 + name1;

                // Append the HTML code to the parent element
                parentElement.insertAdjacentHTML('beforeend', htmlContent);

                // const chartElement = document.querySelector(idData);
                const chartElement = document.querySelector(`#${idData}`);

                // if (chartElement) {
                // Initialize and store the chart instance
                chartInstances[idData] = new ApexCharts(chartElement, options);
                chartInstances[idData].render();
                chartInstances[idData].updateSeries([parseFloat(item.percentage)]);
                // }
            } else {
                data.occupancy.forEach(item => {
                    var name1 = item.name.replace(/\s+/g, "");
                    // Define the HTML code as a string
                    const htmlContent = `
                    <div class="lg:col-span-${column} col-span-12">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">${item.name}</h4>
                            </div>
                            <div class="card-body">
                                <div id="${name1}${name1}"></div>
                                <div class="bg-slate-50 dark:bg-slate-900 rounded p-4 mt-2 flex justify-between flex-wrap" style="padding: 15px 50px;">
                                    <div class="space-y-1">
                                        <h4 class="text-slate-600 dark:text-slate-200 text-xs font-normal">Available</h4>
                                        <div class="text-sm font-medium text-slate-900 dark:text-white">
                                            <h5 class="text-success-500" style="text-align:center;">${item.available}</h5>
                                        </div>
                                    </div>
                                    <div class="space-y-1">
                                        <h4 class="text-slate-600 dark:text-slate-200 text-xs font-normal">Occupied</h4>
                                        <div class="text-sm font-medium text-slate-900 dark:text-white">
                                            <h5 class="text-danger-500" style="text-align:center;">${item.occupied}</h5>
                                        </div>
                                    </div>
                                    <div class="space-y-1">
                                        <h4 class="text-slate-600 dark:text-slate-200 text-xs font-normal">Total</h4>
                                        <div class="text-sm font-medium text-slate-900 dark:text-white">
                                            <h5 class="text-dark-500" style="text-align:center;">${item.total}</h5>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    `;

                    // var idData = "#" + item.name + item.name;
                    var idData = name1 + name1;

                    // Append the HTML code to the parent element
                    parentElement.insertAdjacentHTML('beforeend', htmlContent);

                    // const chartElement = document.querySelector(idData);
                    const chartElement = document.querySelector(`#${idData}`);

                    // if (chartElement) {
                    // Initialize and store the chart instance
                    chartInstances[idData] = new ApexCharts(chartElement, options);
                    chartInstances[idData].render();
                    chartInstances[idData].updateSeries([parseFloat(item.percentage)]);
                    // }
                });
            }


            // $("#live-floors-data").html(data.floorHtml);

        });
    }

    function refreshDashboard() {
        $("#live-status-refresh").show();
        // console.log(chartInstances);

        var tableContent = "";

        $.get("table-view-data", function(data, status) {
            $('#lastUpdate').html(data.lastUpdated);
            if (data.minutes > 15) {
                $('#offline').show();
                $('#live').hide();
            } else {
                $('#offline').hide();
                $('#live').show();
            }
            var count = data.count;
            if (count <= 2) {
                const item = data.occupancy[1];
                var name1 = item.name.replace(/\s+/g, "");
                const chartId = name1 + name1; // Ensure it matches the chart container ID

                // Check if the chart instance already exists
                if (chartInstances[chartId]) {
                    // Update the chart's series
                    chartInstances[chartId].updateSeries([parseFloat(item.percentage)]);
                } else {
                    console.warn(`Chart instance not found for ID: ${chartId}`);
                }

                // Corrected selectors for sibling elements
                var bgSlateDiv = document.querySelector(`#${chartId} + .bg-slate-50`);
                if (bgSlateDiv) {
                    var availableElement = bgSlateDiv.querySelector('.text-success-500');
                    var occupiedElement = bgSlateDiv.querySelector('.text-danger-500');
                    var totalElement = bgSlateDiv.querySelector('.text-dark-500');

                    if (availableElement) availableElement.textContent = item.available;
                    if (occupiedElement) occupiedElement.textContent = item.occupied;
                    if (totalElement) totalElement.textContent = item.total;
                }
            } else {
                data.occupancy.forEach(item => {
                    var name1 = item.name.replace(/\s+/g, "");
                    const chartId = name1 + name1; // Ensure it matches the chart container ID

                    // Check if the chart instance already exists
                    if (chartInstances[chartId]) {
                        // Update the chart's series
                        chartInstances[chartId].updateSeries([parseFloat(item.percentage)]);
                    } else {
                        console.warn(`Chart instance not found for ID: ${chartId}`);
                    }

                    // Corrected selectors for sibling elements
                    var bgSlateDiv = document.querySelector(`#${chartId} + .bg-slate-50`);
                    if (bgSlateDiv) {
                        var availableElement = bgSlateDiv.querySelector('.text-success-500');
                        var occupiedElement = bgSlateDiv.querySelector('.text-danger-500');
                        var totalElement = bgSlateDiv.querySelector('.text-dark-500');

                        if (availableElement) availableElement.textContent = item.available;
                        if (occupiedElement) occupiedElement.textContent = item.occupied;
                        if (totalElement) totalElement.textContent = item.total;
                    }
                });

            }

            data.data.forEach(item => {

                // var idData = "#" + item.name + item.name;



                tableContent += `
                <tr>
                    <td class="table-td" style="width: 90%;"><h5 class="text-dark-500">${item.entry_name}</h5></td>
                    <td class="table-td" style="text-align:center;"><h4 class="text-dark-500">${item.count}</h4></td>
                </tr>
                `;

            });

            $("#tableData").html(tableContent);
            setTimeout(() => {
                $("#live-status-refresh").hide();
            }, 500);

        });
    }
</script>
@endsection