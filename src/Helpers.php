<?php

namespace Balsama\Tempbot;
use Symfony\Component\Yaml\Yaml;

class Helpers
{
    public static function getSensorIds(): array
    {
        return array_keys(self::sensorIpMap());
    }

    public static function getSensorIpById(string $sensorId): string
    {
        $map = self::sensorIpMap();
        return $map[$sensorId];
    }

    public static function sensorIpMap(): array
    {
        return Yaml::parseFile(__DIR__ . '/../config/sensors.yml');
    }

    /**
     * @return SensorReading[]
     */
    public static function getSensorReadings(array $sensorIds): array
    {
        $sensors = [];
        foreach ($sensorIds as $sensorId) {
            $sensors[$sensorId] = new SensorReading($sensorId);
        }
        return $sensors;
    }

    public static function writeInfluxDb(array $sensorReadings)
    {
        $readings = array_filter($sensorReadings, function ($obj) {
            if (isset($obj->responseBody)) {
                return true;
            }
            return false;
        });

        $influx = new InfluxDb();
        foreach ($readings as $reading) {
            $influx->write(
                $reading->getTbId(),
                $reading->getTemp(),
                $reading->getHumidity(),
            );
        }
    }

    public static function c2f(float $c): float
    {
        return ($c * 9 / 5) + 32;
    }
}
