<?php

include_once __DIR__ . '/../vendor/autoload.php';

use Balsama\Tempbot\Helpers;

use Balsama\Tempbot\InfluxDb;

$influx = new InfluxDb();

$sensorIds = Helpers::getSensorIds();
$readings = Helpers::getSensorReadings($sensorIds);
$readings = array_filter($readings, function($obj){
    if (isset($obj->responseBody)) {
        return true;
    }
    return false;
});

foreach ($readings as $reading) {
    $influx->write(
        $reading->getTbId(),
        $reading->getTemp(),
        $reading->getHumidity(),
    );
}
