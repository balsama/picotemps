<?php

include_once __DIR__ . '/../vendor/autoload.php';

use Google\Client;
use Revolution\Google\Sheets\Sheets;
use Balsama\Tempbot\Helpers;

const INTERVAL = 18;

$i = 1;
while(true) {
    $sensorIds = Helpers::getSensorIds();
    $sensors = Helpers::getSensors($sensorIds);
    Helpers::writeSensorsCsvLine($sensors);
    echo "Wrote line $i\n";
    sleep(INTERVAL);
    $i++;
}

exit(1);

$client = new Client();
$client->setScopes([Google\Service\Sheets::DRIVE, Google\Service\Sheets::SPREADSHEETS]);
$client->setAccessToken();
$client->refreshToken();


$service = new \Google\Service\Sheets($client);

$sheets = new Sheets();
$sheets->setService($service);

$values = $sheets->spreadsheet('spreadsheetID')->sheet('Sheet 1')->all();