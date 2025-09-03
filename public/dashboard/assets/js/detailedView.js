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
var chartInstances = {};
var chartview = window.chartview;

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
        },
        events: {
            touchStart: function (e) {
                e.preventDefault(); // Block touch events
            },
            touchMove: function (e) {
                e.preventDefault(); // Block touch events
            },
            touchEnd: function (e) {
                e.preventDefault(); // Block touch events
            }
        },
        touchEnabled: true // Ensure touch is enabled
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
                    formatter: function (val) {
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

var chartInstances = {};
var chartReferences = {};
var columnInstances = {};
$(document).ready(function () {

    $('#chart_view').on('change', function () {
        const selectedValue = $(this).val();

        $.ajax({
            url: 'change-data',  // Replace with your route
            type: 'GET',
            data: {
                value: selectedValue,
            },
            success: function (response) {
                if (response.status === 'success') {
                    location.reload(); // Reload the entire page
                }
            }
        });
    });

    var parentElement = document.querySelector('#detailedData');
    fetchDataAndRender(parentElement);
    fetchDataAndRenderCharts();

    setInterval(function () {
        refreshDashboard();
    }, 5000);

    setInterval(function () {
        updateChartData();
    }, 60000);
});

function fetchDataAndRenderCharts() {
    $.get("get-detailed-chart-data", function (data, status) {
        var count = data.count;
        if (count <= 2) {
            const item = data.data[1];
            var name1 = item.name.replace(/\s+/g, "");
            var chartId = "chart" + name1;
            if (chartview == 1 || chartview == 2) {
                perMinuteChart(chartId, item.chart);
            }
            if (chartview == 3) {
                columnChart(chartId, item.columndata);
            }
        } else {

            data.data.forEach(item => {

                var name1 = item.name.replace(/\s+/g, "");
                var chartId = "chart" + name1;
                if (chartview == 1 || chartview == 2) {
                    perMinuteChart(chartId, item.chart);
                }
                if (chartview == 3) {
                    columnChart(chartId, item.columndata);
                }
            });
        }
    });
}

function updateChartData() {
    $.get("get-detailed-chart-data", function (data, status) {
        var count = data.count;
        if (count <= 2) {
            const item = data.data[1];
            var name1 = item.name.replace(/\s+/g, "");
            var chartId = "chart" + name1;
            if (chartview == 1 || chartview == 2) {
                updatePerMinuteChart(chartId, item.chart);
            }
            if (chartview == 3) {
                columnChart(chartId, item.columndata);
            }
        } else {

            data.data.forEach(item => {

                var name1 = item.name.replace(/\s+/g, "");
                var chartId = "chart" + name1;
                if (chartview == 1 || chartview == 2) {
                    updatePerMinuteChart(chartId, item.chart);
                }
                if (chartview == 3) {
                    columnChart(chartId, item.columndata);
                }
            });
        }
    });
}

function fetchDataAndRender(parentElement) {
    $("#live-status-refresh").show();
    $.get("get-detailed-data", function (data, status) {

        if (data.minutes > 15) {
            $('#offline').show();
            $('#live').hide();
        } else {
            $('#offline').hide();
            $('#live').show();
        }
        $('#lastUpdate').html(data.lastUpdated);
        var count = data.count;
        if (count <= 2) {
            const item = data.data[1];
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
                                            <div id="${name1}${name1}"></div>
                                            <div class="bg-slate-50 dark:bg-slate-900 rounded p-4 mt-4 flex justify-between flex-wrap">
                                                <div class="space-y-1 m-auto">
                                                    <h4 class="text-slate-600 dark:text-slate-200 text-xs font-normal">Available</h4>
                                                    <div class="text-sm font-medium text-slate-900 dark:text-white">
                                                        <h5 class="text-success-500">${item.available}</h5>
                                                    </div>
                                                </div>
                                                <div class="space-y-1 m-auto">
                                                    <h4 class="text-slate-600 dark:text-slate-200 text-xs font-normal">Occupied</h4>
                                                    <div class="text-sm font-medium text-slate-900 dark:text-white">
                                                        <h5 class="text-danger-500">${item.occupied}</h5>
                                                    </div>
                                                </div>
                                                <div class="space-y-1 m-auto">
                                                    <h4 class="text-slate-600 dark:text-slate-200 text-xs font-normal">Total</h4>
                                                    <div class="text-sm font-medium text-slate-900 dark:text-white">
                                                        <h5 class="text-dark-500">${item.total}</h5>
                                                    </div>
                                                </div>
                                                <div class="space-y-1 m-auto" >
                                                    <a href="
                                                    ${item.btn == 1 ? `floormap/${item.id}` : "#"}
                                                    " class="btn btn-sm btn-secondary shadow-base2 mx-2">View Map</a>

                                                    <a href="
                                                    ${data.summaryFlag == 1 ? `summary-report-stats/${item.name}` : "#"}
                                                    " class="btn btn-sm btn-secondary shadow-base2 mx-2">View Stats</a>
                                                </div>
                                            </div>
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

            // var chartId = "chart" + item.name;
            // if (chartview == 1 || chartview == 2) {
            //     perMinuteChart(chartId, item.chart);

            // }
            // if (chartview == 3) {
            //     columnChart(chartId, item.columndata);

            // }
            var name1 = item.name.replace(/\s+/g, "");
            var idData = name1 + name1;
            var chartElement = document.querySelector(`#${idData}`);

            chartInstances[idData] = new ApexCharts(chartElement, options);
            chartInstances[idData].render();
            chartInstances[idData].updateSeries([parseFloat(item.percentage)]);
        } else {

            data.data.forEach(item => {
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
                                            <div id="${name1}${name1}"></div>
                                            <div class="bg-slate-50 dark:bg-slate-900 rounded p-4 mt-4 flex justify-between flex-wrap">
                                                <div class="space-y-1 m-auto">
                                                    <h4 class="text-slate-600 dark:text-slate-200 text-xs font-normal">Available</h4>
                                                    <div class="text-sm font-medium text-slate-900 dark:text-white">
                                                        <h5 class="text-success-500">${item.available}</h5>
                                                    </div>
                                                </div>
                                                <div class="space-y-1 m-auto">
                                                    <h4 class="text-slate-600 dark:text-slate-200 text-xs font-normal">Occupied</h4>
                                                    <div class="text-sm font-medium text-slate-900 dark:text-white">
                                                        <h5 class="text-danger-500">${item.occupied}</h5>
                                                    </div>
                                                </div>
                                                <div class="space-y-1 m-auto">
                                                    <h4 class="text-slate-600 dark:text-slate-200 text-xs font-normal">Total</h4>
                                                    <div class="text-sm font-medium text-slate-900 dark:text-white">
                                                        <h5 class="text-dark-500">${item.total}</h5>
                                                    </div>
                                                </div>
                                                <div class="space-y-1 m-auto">
                                                    <a href="
                                                    ${data.floorFlag == 1 ? `${item.btn == 1 ? `floormap/${item.id}` : "#"}` : "#"}
                                                    " class="btn btn-sm btn-secondary shadow-base2 mx-2">View Map</a>
                                                    <a href="
                                                    ${data.summaryFlag == 1 ? `summary-report-stats/${item.name}` : "#"}
                                                    " class="btn btn-sm btn-secondary shadow-base2 mx-2">View Stats</a>
                                                </div>
                                            </div>
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

                // var chartId = "chart" + item.name;
                // if (chartview == 1 || chartview == 2) {
                //     perMinuteChart(chartId, item.chart);

                // }
                // if (chartview == 3) {
                //     columnChart(chartId, item.columndata);

                // }

                var idData = name1 + name1;
                var chartElement = document.querySelector(`#${idData}`);

                chartInstances[idData] = new ApexCharts(chartElement, options);
                chartInstances[idData].render();
                chartInstances[idData].updateSeries([parseFloat(item.percentage)]);
            });
        }
        setTimeout(() => { $("#live-status-refresh").hide(); }, 500);
    });
}

function refreshDashboard() {
    $("#live-status-refresh").show();
    $.get("get-detailed-data", function (data, status) {
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
            const item = data.data[1];
            var name1 = item.name.replace(/\s+/g, "");
            var idData = name1 + name1;
            var chartElement = document.querySelector(`#${idData}`);

            if (chartInstances[idData]) {
                chartInstances[idData].updateSeries([parseFloat(item.percentage)]);
            }

            // var chartId = "chart" + item.name;
            // // updatePerMinuteChart(chartId, item.chart);
            // if (chartview == 1 || chartview == 2) {
            //     updatePerMinuteChart(chartId, item.chart);
            // }
            // if (chartview == 3) {
            //     columnChart(chartId, item.columndata);
            // }

            // Corrected selectors for sibling elements
            var bgSlateDiv = document.querySelector(`#${idData} + .bg-slate-50`);
            if (bgSlateDiv) {
                var availableElement = bgSlateDiv.querySelector('.text-success-500');
                var occupiedElement = bgSlateDiv.querySelector('.text-danger-500');
                var totalElement = bgSlateDiv.querySelector('.text-dark-500');

                if (availableElement) availableElement.textContent = item.available;
                if (occupiedElement) occupiedElement.textContent = item.occupied;
                if (totalElement) totalElement.textContent = item.total;
            }
        } else {

            data.data.forEach(item => {
                var name1 = item.name.replace(/\s+/g, "");
                var idData = name1 + name1;
                var chartElement = document.querySelector(`#${idData}`);

                if (chartInstances[idData]) {
                    chartInstances[idData].updateSeries([parseFloat(item.percentage)]);
                }

                // var chartId = "chart" + item.name;
                // // updatePerMinuteChart(chartId, item.chart);
                // if (chartview == 1 || chartview == 2) {
                //     updatePerMinuteChart(chartId, item.chart);
                // }
                // if (chartview == 3) {
                //     columnChart(chartId, item.columndata);
                // }

                // Corrected selectors for sibling elements
                var bgSlateDiv = document.querySelector(`#${idData} + .bg-slate-50`);
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
        setTimeout(() => { $("#live-status-refresh").hide(); }, 500);
    });
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

function updatePerMinuteChart(id, data) {
    var chart = chartReferences[id]; // Get the correct chart reference
    if (chart) {
        if (data.length > 0) {
            data[data.length - 1].bullet = true;
        }
        var series = chart.series.values[0]; // Get the first series
        series.data.setAll(data); // Update the data
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

    xAxis.children.push(am5.Label.new(perminutechart, {
        text: "Time",
        x: am5.p50,
        centerX: am5.p50,
        paddingTop: 5
    }));

    var yAxis = window.last_hour_data_chart.yAxes.push(am5xy.ValueAxis.new(perminutechart, {
        renderer: am5xy.AxisRendererY.new(perminutechart, {})
    }));

    // Add Y-axis label
    yAxis.children.unshift(am5.Label.new(perminutechart, {
        text: chartview == 1 ? "Occupancy" : "Available",       // <-- change as needed
        rotation: -90,
        y: am5.p50,
        centerY: am5.p50,
        // x: am5.p50,
        centerX: am5.p50,
        paddingLeft: 5
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

    series.bullets.push(function (perminutechart, series, dataItem) {
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