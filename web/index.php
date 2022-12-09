<?php

include_once __DIR__ . '/../vendor/autoload.php';

use Balsama\Tempbot\Helpers;

$graphData1Day = Helpers::getChartData(strtotime('-1 day'));
$graphData7Days = Helpers::getChartData(strtotime('-7 days'));
$graphData1Month = Helpers::getChartData(strtotime('-1 month'));

$sensorIds = Helpers::getSensorIds();
foreach ($sensorIds as $sensorId) {
    if (str_starts_with($sensorId, 'TB')) {
        $sensorId = strtolower($sensorId);
    }
    if (array_key_exists($sensorId, $graphData1Day)) {
        $reading = end($graphData1Day[$sensorId]);
        $currentTemps[$sensorId] = $reading['y'];
    } else {
        $currentTemps[$sensorId] = null;
    }
}
?>
<!DOCTYPE HTML>
<html>
<head>
    <script>
        window.onload = function () {

            var chart7Days = new CanvasJS.Chart("chartContainer7Days", {
                theme: "light2",
                animationEnabled: true,
                zoomEnabled: true,
                title: {
                    text: "Trailing Week"
                },
                data: [
                    {
                        type: "line",
                        showInLegend: true,
                        name: "tb0101",
                        legendText: "First floor",
                        xValueType: "dateTime",
                        xValueFormatString: "DD MMM HH:mm:ss",
                        dataPoints: <?php echo json_encode($graphData7Days['tb0101'], JSON_NUMERIC_CHECK); ?>
                    },
                    {
                        type: "line",
                        showInLegend: true,
                        name: "tb0201",
                        legendText: "Second floor",
                        xValueType: "dateTime",
                        xValueFormatString: "DD MMM HH:mm:ss",
                        dataPoints: <?php echo json_encode($graphData7Days['tb0201'], JSON_NUMERIC_CHECK); ?>
                    },
                    {
                        type: "line",
                        showInLegend: true,
                        name: "tb0301",
                        legendText: "Third floor",
                        xValueType: "dateTime",
                        xValueFormatString: "DD MMM HH:mm:ss",
                        dataPoints: <?php echo @json_encode($graphData7Days['tb0301'], JSON_NUMERIC_CHECK); ?>
                    },
                    {
                        type: "line",
                        showInLegend: true,
                        name: "tb0302",
                        legendText: "Third floor bedroom",
                        xValueType: "dateTime",
                        xValueFormatString: "DD MMM HH:mm:ss",
                        dataPoints: <?php echo json_encode($graphData7Days['tb0302'], JSON_NUMERIC_CHECK); ?>
                    },
                    {
                        type: "line",
                        showInLegend: true,
                        name: "tb0401",
                        legendText: "Fourth floor",
                        xValueType: "dateTime",
                        xValueFormatString: "DD MMM HH:mm:ss",
                        dataPoints: <?php echo json_encode($graphData7Days['tb0401'], JSON_NUMERIC_CHECK); ?>
                    },
                    {
                        type: "line",
                        showInLegend: true,
                        name: "KBOS",
                        legendText: "Logan",
                        xValueType: "dateTime",
                        xValueFormatString: "DD MMM HH:mm:ss",
                        dataPoints: <?php echo json_encode($graphData7Days['KBOS'], JSON_NUMERIC_CHECK); ?>
                    }
                ],
                legend: {
                    cursor: "pointer",
                    itemclick: function (e) {
                        e.dataSeries.visible = !(typeof (e.dataSeries.visible) === "undefined" || e.dataSeries.visible);
                        e.chart.render();
                    }
                }

            });
            chart7Days.render();

            var chart1Day = new CanvasJS.Chart("chartContainer1Day", {
                theme: "light2",
                animationEnabled: true,
                zoomEnabled: true,
                title: {
                    text: "Last 24 Hours"
                },
                data: [
                    {
                        type: "line",
                        showInLegend: true,
                        name: "tb0101",
                        legendText: "First floor",
                        xValueType: "dateTime",
                        xValueFormatString: "DD MMM HH:mm:ss",
                        dataPoints: <?php echo json_encode($graphData1Day['tb0101'], JSON_NUMERIC_CHECK); ?>
                    },
                    {
                        type: "line",
                        showInLegend: true,
                        name: "tb0201",
                        legendText: "Second floor",
                        xValueType: "dateTime",
                        xValueFormatString: "DD MMM HH:mm:ss",
                        dataPoints: <?php echo json_encode($graphData1Day['tb0201'], JSON_NUMERIC_CHECK); ?>
                    },
                    {
                        type: "line",
                        showInLegend: true,
                        name: "tb0301",
                        legendText: "Third floor",
                        xValueType: "dateTime",
                        xValueFormatString: "DD MMM HH:mm:ss",
                        dataPoints: <?php echo @json_encode($graphData1Day['tb0301'], JSON_NUMERIC_CHECK); ?>
                    },
                    {
                        type: "line",
                        showInLegend: true,
                        name: "tb0302",
                        legendText: "Third floor bedroom",
                        xValueType: "dateTime",
                        xValueFormatString: "DD MMM HH:mm:ss",
                        dataPoints: <?php echo json_encode($graphData1Day['tb0302'], JSON_NUMERIC_CHECK); ?>
                    },
                    {
                        type: "line",
                        showInLegend: true,
                        name: "tb0401",
                        legendText: "Fourth floor",
                        xValueType: "dateTime",
                        xValueFormatString: "DD MMM HH:mm:ss",
                        dataPoints: <?php echo json_encode($graphData1Day['tb0401'], JSON_NUMERIC_CHECK); ?>
                    },
                    {
                        type: "line",
                        showInLegend: true,
                        name: "KBOS",
                        legendText: "Logan",
                        xValueType: "dateTime",
                        xValueFormatString: "DD MMM HH:mm:ss",
                        dataPoints: <?php echo json_encode($graphData1Day['KBOS'], JSON_NUMERIC_CHECK); ?>
                    }
                ],
                legend: {
                    cursor: "pointer",
                    itemclick: function (e) {
                        e.dataSeries.visible = !(typeof (e.dataSeries.visible) === "undefined" || e.dataSeries.visible);
                        e.chart.render();
                    }
                }

            });
            chart1Day.render();

            var chart1Month = new CanvasJS.Chart("chartContainer1Month", {
                theme: "light2",
                animationEnabled: true,
                zoomEnabled: true,
                title: {
                    text: "Trailing Month"
                },
                data: [
                    {
                        type: "line",
                        showInLegend: true,
                        name: "tb0101",
                        legendText: "First floor",
                        xValueType: "dateTime",
                        xValueFormatString: "DD MMM HH:mm:ss",
                        dataPoints: <?php echo @json_encode($graphData1Month['tb0101'], JSON_NUMERIC_CHECK); ?>
                    },
                    {
                        type: "line",
                        showInLegend: true,
                        name: "tb0201",
                        legendText: "Second floor",
                        xValueType: "dateTime",
                        xValueFormatString: "DD MMM HH:mm:ss",
                        dataPoints: <?php echo @json_encode($graphData1Month['tb0201'], JSON_NUMERIC_CHECK); ?>
                    },
                    {
                        type: "line",
                        showInLegend: true,
                        name: "tb0301",
                        legendText: "Third floor",
                        xValueType: "dateTime",
                        xValueFormatString: "DD MMM HH:mm:ss",
                        dataPoints: <?php echo @json_encode($graphData1Month['tb0301'], JSON_NUMERIC_CHECK); ?>
                    },
                    {
                        type: "line",
                        showInLegend: true,
                        name: "tb0302",
                        legendText: "Third floor bedroom",
                        xValueType: "dateTime",
                        xValueFormatString: "DD MMM HH:mm:ss",
                        dataPoints: <?php echo @json_encode($graphData1Month['tb0302'], JSON_NUMERIC_CHECK); ?>
                    },
                    {
                        type: "line",
                        showInLegend: true,
                        name: "tb0401",
                        legendText: "Fourth floor",
                        xValueType: "dateTime",
                        xValueFormatString: "DD MMM HH:mm:ss",
                        dataPoints: <?php echo @json_encode($graphData1Month['tb0401'], JSON_NUMERIC_CHECK); ?>
                    },
                    {
                        type: "line",
                        showInLegend: true,
                        name: "KBOS",
                        legendText: "Logan",
                        xValueType: "dateTime",
                        xValueFormatString: "DD MMM HH:mm:ss",
                        dataPoints: <?php echo @json_encode($graphData1Month['KBOS'], JSON_NUMERIC_CHECK); ?>
                    }
                ],
                legend: {
                    cursor: "pointer",
                    itemclick: function (e) {
                        e.dataSeries.visible = !(typeof (e.dataSeries.visible) === "undefined" || e.dataSeries.visible);
                        e.chart.render();
                    }
                }

            });
            chart1Month.render();
        }
    </script>
    <link rel="stylesheet" type="text/css" href="styles/house.css"></link>
    <link rel="icon" type="image/x-icon" href="favicon.ico">
</head>
<body>

<div class="box">
    <div id="house" class="box-item">
        <h2>Current</h2>
        <div class="floor floor4 box">
            <div class="room room4 room41 box-item" style="background-color: <?php echo Helpers::tempToColor($currentTemps['tb0401'])?>">
                <div class="reading"><?php echo $currentTemps['tb0401']; ?></div>
            </div>
            <div class="room room4 room42 box-item triangle" style="border-left-color: <?php echo Helpers::tempToColor($currentTemps['tb0401'])?>">
            </div>
        </div>
        <div class="floor floor3 box">
            <div class="room box-item room2" style="background-color: <?php echo Helpers::tempToColor($currentTemps['tb0302'])?>">
                <div class="reading"><?php echo $currentTemps['tb0302']; ?></div>
            </div>
            <div class="room box-item room1" style="background-color: <?php echo Helpers::tempToColor($currentTemps['tb0301'])?>">
                <div class="reading"><?php echo $currentTemps['tb0301']; ?></div>
            </div>
        </div>
        <div class="floor floor2">
            <div class="room" style="background-color: <?php echo Helpers::tempToColor($currentTemps['tb0201'])?>">
                <div class="reading"><?php echo $currentTemps['tb0201']; ?></div>
            </div>
        </div>
        <div class="floor floor1">
            <div class="room" style="background-color: <?php echo Helpers::tempToColor($currentTemps['tb0101'])?>">
                <div class="reading"><?php echo $currentTemps['tb0101']; ?></div>
            </div>
        </div>
    </div>
    <div id="chartContainer1Day" style="height: 370px; width: 100%;" class="box-item"></div>
</div>
<div id="chartContainer7Days" style="height: 370px; width: 100%;"></div>
<div id="chartContainer1Month" style="height: 370px; width: 100%;"></div>
<script src="scripts/canvasjs.min.js"></script>
</body>
</html>