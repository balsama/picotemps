<?php

include_once __DIR__ . '/../vendor/autoload.php';

use Balsama\Tempbot\Helpers;

$sensors = Helpers::sensorIpMap();
$results = [];
foreach ($sensors as $sensorName => $ip) {
    $results[$sensorName] = (Helpers::availableSensor($ip)) ? 'up' : 'down';
}
print_r($results);
