<?php

namespace Balsama\Tempbot;

use InfluxDB2\ApiException;
use InfluxDB2\Client;
use InfluxDB2\Model\WritePrecision;
use InfluxDB2\Point;

class InfluxDb
{
    public Client $client;

    public function getClient()
    {
        $token = $this->getInfluxToken();
        return new Client([
            "url" => self::getInfluxUrl(),
            "token" => $token,
        ]);
    }

    public function write($sensorId, $temperature, $humidity, $time = null)
    {
        $org = Helpers::getConfig(['influx', 'org']);
        $bucket = Helpers::getConfig(['influx', 'bucket']);
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

        try {
            $writeApi->write($point, WritePrecision::S, $bucket, $org);
        } catch (ApiException $e) {
            error_log(
                'Influx write Failed. Humidity a float? `' . $humidity . '` is_float?: ' . is_float((string) $humidity),
                0
            );
            throw $e;
        }

        $writeApi->close();
    }

    private function getInfluxToken(): string
    {
        $config = Helpers::getConfig(['influx', 'token']);
        return trim(file_get_contents($config['path'] . $config['name']));
    }

    public function getInfluxUrl(): string
    {
        $influxConfig = Helpers::getConfig(['influx', 'url']);
        return implode(':', [$influxConfig['host'], $influxConfig['port']]) . $influxConfig['path'];
    }
}
