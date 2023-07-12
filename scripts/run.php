<?php

include_once __DIR__ . '/../vendor/autoload.php';

use Balsama\Tempbot\Helpers;

const INTERVAL = 30;
$i = 1;

while (true) {
    $sensorIds = Helpers::getSensorIds();
    $sensors = Helpers::getSensorReadings($sensorIds);

    //Helpers::writeSensorsCsvLine($sensors);
    //Helpers::writeSensorReadingsDb($sensors);
    Helpers::writeInfluxDb($sensors);

    sleep(INTERVAL);
    $i++;
}
