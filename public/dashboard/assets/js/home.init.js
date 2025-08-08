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
    refreshDashboard();
    GraphInitialData();
    // getLast4HourGraphInitialData();


    setInterval(function () {
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

function GraphInitialData() {
    // Select the parent element where you want to append the new HTML
    const parentElement = document.querySelector('#radarData');

    $.get("get-dashboard-data", function (data, status) {
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
            const item = data.data[1];
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
        } else {
            data.data.forEach(item => {
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

    $.get("get-dashboard-data", function (data, status) {
        if (data.flag == 1) {
            $('#financial').show();
        } else {
            $('#financial').hide();
        }
        $('#lastUpdate').html(data.lastUpdated);
        if (data.minutes > 15) {
            $('#offline').show();
            $('#live').hide();
        } else {
            $('#offline').hide();
            $('#live').show();
        }
        // console.log(item);
        if (data.count <= 2) {
            const item = data.data[1];
            // var idData = "#" + item.name + item.name;
            var namee = item.name.replace(/\s+/g, "");
            const chartId = namee + namee; // Ensure it matches the chart container ID

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

            const inOutData = data.inOutData[1];
            tableContent += `
            <tr>
                <td class="text-center table-td"><h5 class="text-dark-500">${inOutData.name}</h5></td>
                <td class="text-center table-td"><h4 class="text-dark-500">${inOutData.in}</h4></td>
                <td class="text-center table-td"><h4 class="text-dark-500">${inOutData.out}</h4></td>
                ${data.flag == 1 ? `<td class="text-center table-td"><h4 class="text-dark-500">${inOutData.price}₹</h4></td>` : ''}
                ${inOutData.btn == 1 ? `<td class="text-center table-td">${data.floorFlag == 1 ? `<a href="floormap/${inOutData.id}" class="btn btn-secondary shadow-base2">Layout</a>` : `<a href="#" class="btn btn-secondary shadow-base2">Layout</a>`}</td>` : ''}
            </tr>
            `;
        } else {
            data.data.forEach(item => {

                // var idData = "#" + item.name + item.name;

                var namee = item.name.replace(/\s+/g, "");
                const chartId = namee + namee; // Ensure it matches the chart container ID

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

            data.inOutData.forEach(item => {
                tableContent += `
                <tr>
                    <td class="text-center table-td"><h5 class="text-dark-500">${item.name}</h5></td>
                    <td class="text-center table-td"><h4 class="text-dark-500">${item.in}</h4></td>
                    <td class="text-center table-td"><h4 class="text-dark-500">${item.out}</h4></td>
                    ${data.flag == 1 ? `<td class="text-center table-td"><h4 class="text-dark-500">${item.price}₹</h4></td>` : ''}
                    ${item.btn == 1 ? `<td class="text-center table-td">${data.floorFlag == 1 ? `<a href="floormap/${item.id}" class="btn btn-secondary shadow-base2">Layout</a>` : `<a href="#" class="toolTip onTop onClickTooltip btn btn-secondary shadow-base2" data-tippy-content="No Access" data-tippy-theme="danger">Layout</a>`}</td>` : ''}
                </tr>
                `;
            });
        }


        // console.log(tableContent);

        $("#tableData").html(tableContent);
        // $("#live-floors-data").html(data.floorHtml);
        setTimeout(() => { $("#live-status-refresh").hide(); }, 500);

    });
}

// var radialchartColors = ["#5156be", "#34c38f"];
// var options = {
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
//                     formatter: function (val) {
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
//     legend: {
//         show: false
//     },
//     series: [0],
//     labels: ['Occupancy'],
// }

// window.liveStatusChart = new ApexCharts(
//     document.querySelector("#progressChart"),
//     options
// );

// window.liveStatusChart.render();