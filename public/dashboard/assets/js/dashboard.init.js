// get colors array from the string
function getChartColorsArray(chartId) {
    var colors = $(chartId).attr('data-colors');
    var colors = JSON.parse(colors);
    return colors.map(function (value) {
        var newValue = value.replace(' ', '');
        if (newValue.indexOf('--') != -1) {
            var color = getComputedStyle(document.documentElement).getPropertyValue(newValue);
            if (color) return color;
        } else {
            return newValue;
        }
    })
}

$(document).ready(function () {
    // refreshDashboard();
    // getLast4HourGraphInitialData();

    // setInterval(function () {
        refreshDashboard();
        const randomNumber = Math.floor(Math.random() * 100) + 1;
        window.liveStatusChart.updateSeries([randomNumber]);
    // }, 5000);
});

function refreshDashboard() {
    // $("#live-status-refresh").show();

    $.get("/get-dashboard-data", function (data, status) {
        // var obj = JSON.parse(data);
        console.log(data);
        $("#live-floors-data").html(data.floorHtml);

    });
}

var radialchartColors = ["#5156be", "#34c38f"];
var options = {
    chart: {
        height: 270,
        type: 'radialBar',
        offsetY: -10
    },
    plotOptions: {
        radialBar: {
            startAngle: -130,
            endAngle: 130,
            dataLabels: {
                name: {
                    show: true
                },
                value: {
                    offsetY: 10,
                    fontSize: '18px',
                    color: undefined,
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

window.liveStatusChart = new ApexCharts(
    document.querySelector("#progressChart"),
    options
);

window.liveStatusChart.render();