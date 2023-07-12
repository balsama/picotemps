<?php

namespace Balsama\Tempbot;

use InfluxDB2\Client;
use InfluxDB2\Model\WritePrecision;
use InfluxDB2\Point;

class InfluxDb
{
    private const INFLUX_TOKEN = '_dNPcvw2SRpRsGJDHtc6_WiAb1WVf4zXHEU7Ng7WIQjmRwMvEtVdL5Qq_lNNbVm86OsYXxzbIQMwKGBjMm8wYQ==';

    public Client $client;

    public function __construct()
    {}

    public function getClient()
    {
        $token = self::INFLUX_TOKEN;

        $client = new Client([
            "url" => "http://192.168.7.162:8086",
            "token" => $token,
        ]);

        return $client;
    }


    public function write($sensorId, $temperature, $humidity, $time = null)
    {
        $org = 'tempbot';
        $bucket = 'picotemps';
        $client = $this->getClient();

        if (!$time) {
            $time = time();
        }

        $writeApi = $client->createWriteApi();

        $point = new Point("sensor reading");
        $point->addTag("location", $sensorId)
            ->addField("temperature", $temperature)
            ->addField("humidity", $humidity)
            ->time($time);

        $writeApi->write($point, WritePrecision::S, $bucket, $org);
        $writeApi->close();
    }
}