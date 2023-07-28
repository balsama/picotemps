<?php

namespace Balsama\Tempbot;

use InfluxDB2\Client;
use InfluxDB2\Model\WritePrecision;
use InfluxDB2\Point;

class InfluxDb
{
    private const INFLUX_URL = 'http://192.168.7.162:8086';
    public Client $client;

    public function getClient()
    {
        $token = $this->getInfluxToken();
        return new Client([
            "url" => self::INFLUX_URL,
            "token" => $token,
        ]);
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

    private function getInfluxToken(): string
    {
        if (file_exists(__DIR__ . '/../../../keys/TEMPBOT_INFLUX_TOKEN')) {
            return trim(file_get_contents(__DIR__ . '/../../../keys/TEMPBOT_INFLUX_TOKEN'));
        }
        return trim(file_get_contents(__DIR__ . '/../../keys/TEMPBOT_INFLUX_TOKEN'));
    }
}
