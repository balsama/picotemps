<?php

namespace Balsama\Tempbot;

use InfluxDB2\Client;
use InfluxDB2\Model\WritePrecision;
use InfluxDB2\Point;

class InfluxDb
{
    private string $influx_token;

    public Client $client;

    public function __construct()
    {
        $this->influx_token = $this->getInfluxToken();
    }

    private function getInfluxToken() :string
    {
        if (file_exists(__DIR__ . '/../../../keys/TEMPBOT_INFLUX_TOKEN')) {
            return trim(file_get_contents(__DIR__ . '/../../../keys/TEMPBOT_INFLUX_TOKEN'));
        }
        return trim(file_get_contents(__DIR__ . '/../../keys/TEMPBOT_INFLUX_TOKEN'));
    }

    public function getClient()
    {
        $token = $this->influx_token;

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