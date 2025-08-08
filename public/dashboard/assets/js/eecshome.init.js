var theme = localStorage.getItem('theme');
if (theme == 'light' || theme == null) {
    var color1 = '#5156be';
    var color2 = '#000000';
} else {
    var color1 = '#ffffff';
    var color2 = '#ffffff';
}
var chartInstances = {};

$(document).ready(function () {
    // refreshDashboard();
    GraphInitialData();


    setInterval(function () {
        refreshDashboard();
    }, 5000);
});


function GraphInitialData() {
    // Select the parent element where you want to append the new HTML
    var tableContent = "";
    const parentElement = document.querySelector('#radarData');
    $("#live-status-refresh").show();
    $.get("eecs-dashboard-data", function (data, status) {
        $('#lastUpdate').html(data.lastUpdated);

        if (data.minutes > 15) {
            $('#offline').show();
            $('#live').hide();
        } else {
            $('#offline').hide();
            $('#live').show();
        }
        // var obj = JSON.parse(data);
        // console.log(data);
        // var column = 4;
        var count = data.count;
        // if (count == 4) {
        //     column = 3;
        // } else {
        //     column = 4;
        // }
        if (count <= 2) {
            const floorData = Array.isArray(data.floorWiseData) ? data.floorWiseData : [data.floorWiseData];
            var loopbreak = 0;

            floorData.forEach(item => {
                if (loopbreak == 0) {

                    // in-out
                    const floorGroups = {};
                    item.inOutData.forEach(entry => {
                        if (!floorGroups[entry.floor_name]) {
                            floorGroups[entry.floor_name] = [];
                        }
                        floorGroups[entry.floor_name].push(entry);
                    });

                    // Loop and render
                    Object.keys(floorGroups).forEach(floor => {
                        const group = floorGroups[floor];

                        group.forEach((item1, index) => {
                            tableContent += `<tr>`;

                            if (index === 0) {
                                tableContent += `
                <td class="text-center table-td" rowspan="${group.length}">
                    <h5 class="text-dark-500">${item.floor_name}</h5>
                </td>`;
                            }

                            tableContent += `
                        <td class="text-center table-td"><h5 class="text-dark-500">${item1.type}</h5></td>
                        <td class="text-center table-td"><h4 class="text-dark-500">${item1.in}</h4></td>
                        <td class="text-center table-td"><h4 class="text-dark-500">${item1.out}</h4></td>
                    </tr>`;
                        });
                    });
                    // end

                    var nameKey = item.floor_name.replace(/\s+/g, "");
                    var chartId = nameKey + nameKey;
                    var column = 12;

                    const detectionHeader = `
                <div class="grid grid-cols-3 gap-2 px-3 py-2 bg-gray-100 text-xs font-bold text-center">
                    <div>Type</div><div>Occupied</div><div>Total</div>
                </div>
                `;

                    const detectionHtml1 = detectionHeader + item.detection_details.map(detail => `
                <div class="grid grid-cols-3 gap-2 px-3 py-2 bg-gray-50 dark:bg-slate-800 border-t text-xs font-semibold text-center">
                    <div class="truncate text-gray-700 dark:text-white">${detail.name}</div>
                    <div class="text-success-500">${detail.occupied}</div>
                    <div class="text-dark-500 dark:text-white">${detail.total}</div>
                </div>
                `).join('');

                    const detectionHtml = detectionHtml1 + `<div class="grid grid-cols-3 gap-2 px-3 py-2 bg-gray-50 dark:bg-slate-800 border-t text-xs font-semibold text-center total-row" data-floor="${chartId}">
                    <div class="truncate text-gray-700 dark:text-white"><b>Total</b></div>
                    <div class="text-success-500"><b>${item.occupied}</b></div>
                    <div class="text-dark-500 dark:text-white"><b>${item.total}</b></div>
                </div>`

                    const htmlContent = `
        <div class="lg:col-span-${column} col-span-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title text-xl font-semibold">${item.floor_name}</h4>
                </div>
                <div class="card-body">
                    <div id="${nameKey}${nameKey}"></div>
                    <div class="mb-1 text-center"><b>Occupancy</b></div>
                    <div class="rounded overflow-hidden shadow-sm border border-gray-200 dark:border-gray-700">
                        ${detectionHtml}
                    </div>
                </div>
            </div>
        </div>
    `;

                    parentElement.insertAdjacentHTML('beforeend', htmlContent);

                    const chartElement = document.querySelector(`#${nameKey}${nameKey}`);
                    const series = item.detection_details.map(d => d.percentage);
                    const labels = item.detection_details.map(d => d.name);

                    chartInstances[nameKey + nameKey] = new ApexCharts(chartElement, {
                        series: series,
                        chart: {
                            type: 'radialBar',
                            height: 250,
                        },
                        tooltip: {
                            enabled: false // 👈 disables hover tooltip
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
                                    formatter: function (seriesName, opts) {
                                        return seriesName + ":  " + opts.w.globals.series[opts.seriesIndex] + "%"
                                    },
                                },
                            }
                        },
                        labels: labels,
                        // colors: ['#00BFFF', '#0084ff', '#39539E', '#FF5722'],
                    });

                    chartInstances[nameKey + nameKey].render();
                    loopbreak++;
                }
            });

        } else {
            data.floorWiseData.forEach(item => {

                // in-out
                const floorGroups = {};
                item.inOutData.forEach(entry => {
                    if (!floorGroups[entry.floor_name]) {
                        floorGroups[entry.floor_name] = [];
                    }
                    floorGroups[entry.floor_name].push(entry);
                });

                // Loop and render
                Object.keys(floorGroups).forEach(floor => {
                    const group = floorGroups[floor];

                    group.forEach((item1, index) => {
                        tableContent += `<tr>`;

                        if (index === 0) {
                            tableContent += `
                <td class="text-center table-td" rowspan="${group.length}">
                    <h5 class="text-dark-500">${item.floor_name}</h5>
                </td>`;
                        }

                        tableContent += `
                        <td class="text-center table-td"><h5 class="text-dark-500">${item1.type}</h5></td>
                        <td class="text-center table-td"><h4 class="text-dark-500">${item1.in}</h4></td>
                        <td class="text-center table-td"><h4 class="text-dark-500">${item1.out}</h4></td>
                    </tr>`;
                    });
                });
                // end

                var nameKey = item.floor_name.replace(/\s+/g, "");
                var chartId = nameKey + nameKey;
                var column = 4;

                const detectionHeader = `
                <div class="grid grid-cols-3 gap-2 px-3 py-2 bg-gray-100 text-xs font-bold text-center">
                    <div>Type</div><div>Occupied</div><div>Total</div>
                </div>
                `;

                const detectionHtml1 = detectionHeader + item.detection_details.map(detail => `
                <div class="grid grid-cols-3 gap-2 px-3 py-2 bg-gray-50 dark:bg-slate-800 border-t text-xs font-semibold text-center">
                    <div class="truncate text-gray-700 dark:text-white">${detail.name}</div>
                    <div class="text-success-500">${detail.occupied}</div>
                    <div class="text-dark-500 dark:text-white">${detail.total}</div>
                </div>
                `).join('');

                const detectionHtml = detectionHtml1 + `<div class="grid grid-cols-3 gap-2 px-3 py-2 bg-gray-50 dark:bg-slate-800 border-t text-xs font-semibold text-center total-row" data-floor="${chartId}">
                    <div class="truncate text-gray-700 dark:text-white"><b>Total</b></div>
                    <div class="text-success-500"><b>${item.occupied}</b></div>
                    <div class="text-dark-500 dark:text-white"><b>${item.total}</b></div>
                </div>`

                const htmlContent = `
        <div class="lg:col-span-${column} col-span-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title text-xl font-semibold">${item.floor_name}</h4>
                </div>
                <div class="card-body">
                    <div id="${nameKey}${nameKey}"></div>
                    <div class="mb-1 text-center"><b>Occupancy</b></div>

                    <div class="rounded overflow-hidden shadow-sm border border-gray-200 dark:border-gray-700">
                        ${detectionHtml}
                    </div>
                </div>
            </div>
        </div>
    `;

                parentElement.insertAdjacentHTML('beforeend', htmlContent);

                // Build chart data
                const chartElement = document.querySelector(`#${nameKey}${nameKey}`);
                const series = item.detection_details.map(d => d.percentage);

                const labels = item.detection_details.map(d => d.name);

                chartInstances[nameKey + nameKey] = new ApexCharts(chartElement, {
                    series: series,
                    chart: {
                        type: 'radialBar',
                        height: 250,
                    },
                    tooltip: {
                        enabled: false // 👈 disables hover tooltip
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
                                formatter: function (seriesName, opts) {
                                    return seriesName + ":  " + opts.w.globals.series[opts.seriesIndex] + "%"
                                },
                            },
                        }
                    },
                    labels: labels,
                    // colors: ['#00BFFF', '#0084ff', '#39539E', '#FF5722'],
                });

                chartInstances[nameKey + nameKey].render();
            });


        }

        $("#tableData").html(tableContent);
        // $("#live-floors-data").html(data.floorHtml);
        setTimeout(() => {
            $("#live-status-refresh").hide();
        }, 500);

    });
}


function refreshDashboard() {
    $("#live-status-refresh").show();

    $.get("eecs-dashboard-data", function (data, status) {
        $('#lastUpdate').html(data.lastUpdated);

        if (data.minutes > 15) {
            $('#offline').show();
            $('#live').hide();
        } else {
            $('#offline').hide();
            $('#live').show();
        }
        var tableContent = "";
        const floorData = Array.isArray(data.floorWiseData) ? data.floorWiseData : [data.floorWiseData];
        if (data.count <= 2) {
            var loopbreak = 0;
            floorData.forEach(item => {
                if (loopbreak == 0) {
                    const nameKey = item.floor_name.replace(/\s+/g, "");
                    const chartId = nameKey + nameKey;

                    // Update main values
                    // $(`#${chartId}`).closest('.card-body').find('.text-success-500.text-lg').text(item.available);
                    // $(`#${chartId}`).closest('.card-body').find('.text-danger-500.text-lg').text(item.occupied);
                    // $(`#${chartId}`).closest('.card-body').find('.text-dark-500.text-lg').text(item.total);

                    // Update detection details values
                    item.detection_details.forEach(detail => {
                        // Find the grid row that contains the detail name
                        const row = $(`#${chartId}`).closest('.card-body')
                            .find(`.grid.grid-cols-3:has(div:contains("${detail.name}"))`);

                        if (row.length) {
                            // Update available (second cell) and total (third cell)
                            row.children().eq(1).text(detail.occupied);
                            row.children().eq(2).text(detail.total);
                        }
                    });

                    // Now update the Total row
                    const cardBody = $(`#${chartId}`).closest('.card-body');

                    // Use a proper attribute selector targeting data-floor attribute
                    const totalRow = cardBody.find(`.total-row[data-floor="${chartId}"]`);

                    if (totalRow.length) {
                        // The inner columns likely don't have individual classes,
                        // so use .children() by index:
                        totalRow.children().eq(1).find('b').text(item.occupied);
                        totalRow.children().eq(2).find('b').text(item.total);
                    }

                    const floorGroups = {};
                    item.inOutData.forEach(entry => {
                        if (!floorGroups[entry.floor_name]) {
                            floorGroups[entry.floor_name] = [];
                        }
                        floorGroups[entry.floor_name].push(entry);
                    });

                    // Loop and render
                    Object.keys(floorGroups).forEach(floor => {
                        const group = floorGroups[floor];

                        group.forEach((item1, index) => {
                            tableContent += `<tr>`;

                            if (index === 0) {
                                tableContent += `
                <td class="text-center table-td" rowspan="${group.length}">
                    <h5 class="text-dark-500">${item.floor_name}</h5>
                </td>`;
                            }

                            tableContent += `
                        <td class="text-center table-td"><h5 class="text-dark-500">${item1.type}</h5></td>
                        <td class="text-center table-td"><h4 class="text-dark-500">${item1.in}</h4></td>
                        <td class="text-center table-td"><h4 class="text-dark-500">${item1.out}</h4></td>
                    </tr>`;
                        });
                    });

                    // OPTIONAL: update chart values directly without re-render
                    if (chartInstances[chartId]) {
                        const newSeries = item.detection_details.map(d => d.percentage);
                        chartInstances[chartId].updateSeries(newSeries);
                    }

                    loopbreak++;
                }
            });
        } else {
            floorData.forEach(item => {
                const nameKey = item.floor_name.replace(/\s+/g, "");
                const chartId = nameKey + nameKey;

                // Update main values
                // $(`#${chartId}`).closest('.card-body').find('.text-success-500.text-lg').text(item.available);
                // $(`#${chartId}`).closest('.card-body').find('.text-danger-500.text-lg').text(item.occupied);
                // $(`#${chartId}`).closest('.card-body').find('.text-dark-500.text-lg').text(item.total);

                // Update detection details values
                item.detection_details.forEach(detail => {
                    // Find the grid row that contains the detail name
                    const row = $(`#${chartId}`).closest('.card-body')
                        .find(`.grid.grid-cols-3:has(div:contains("${detail.name}"))`);

                    if (row.length) {
                        // Update available (second cell) and total (third cell)
                        row.children().eq(1).text(detail.occupied);
                        row.children().eq(2).text(detail.total);
                    }
                });

                // Now update the Total row
                const cardBody = $(`#${chartId}`).closest('.card-body');

                // Use a proper attribute selector targeting data-floor attribute
                const totalRow = cardBody.find(`.total-row[data-floor="${chartId}"]`);

                if (totalRow.length) {
                    // The inner columns likely don't have individual classes,
                    // so use .children() by index:
                    totalRow.children().eq(1).find('b').text(item.occupied);
                    totalRow.children().eq(2).find('b').text(item.total);
                }

                const floorGroups = {};
                item.inOutData.forEach(entry => {
                    if (!floorGroups[entry.floor_name]) {
                        floorGroups[entry.floor_name] = [];
                    }
                    floorGroups[entry.floor_name].push(entry);
                });

                // Loop and render
                Object.keys(floorGroups).forEach(floor => {
                    const group = floorGroups[floor];

                    group.forEach((item1, index) => {
                        tableContent += `<tr>`;

                        if (index === 0) {
                            tableContent += `
                <td class="text-center table-td" rowspan="${group.length}">
                    <h5 class="text-dark-500">${item.floor_name}</h5>
                </td>`;
                        }

                        tableContent += `
                        <td class="text-center table-td"><h5 class="text-dark-500">${item1.type}</h5></td>
                        <td class="text-center table-td"><h4 class="text-dark-500">${item1.in}</h4></td>
                        <td class="text-center table-td"><h4 class="text-dark-500">${item1.out}</h4></td>
                    </tr>`;
                    });
                });

                // OPTIONAL: update chart values directly without re-render
                if (chartInstances[chartId]) {
                    const newSeries = item.detection_details.map(d => d.percentage);
                    chartInstances[chartId].updateSeries(newSeries);
                }
            });
        }



        $("#tableData").html(tableContent);
        setTimeout(() => {
            $("#live-status-refresh").hide();
        }, 500);
    });
}
