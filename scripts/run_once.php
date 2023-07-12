<?php
/**
 * Main entry point.
 *
 * This file should be called on a schedule by cron.
 */

include_once __DIR__ . '/../vendor/autoload.php';

use Balsama\Tempbot\Helpers;

$sensorIds = Helpers::getSensorIds();
$sensors = Helpers::getSensorReadings($sensorIds);
//Helpers::writeSensorReadingsDb($sensors);
Helpers::writeInfluxDb($sensors);

exit(0);
