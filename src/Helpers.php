<?php

namespace Balsama\Tempbot;

use Symfony\Component\Yaml\Yaml;

class Helpers
{
    public static function getConfig(array $parts = []): array | string
    {
        $config = Yaml::parseFile(__DIR__ . '/../config/config.yml');
        if (!$parts) {
            return $config;
        }
        foreach ($parts as $part) {
            $config = self::getSubArray($config, $part);
        }
        return $config;
    }

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
        return self::getConfig(['sensors']);
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

    /**
     * Takes an array of SensorReading objects and writes the measurements to the InfluxDB instance.
     *
     * @param SensorReading[] $sensorReadings
     * @return void
     */
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

    private static function getSubArray($array, string $subKey)
    {
        return $array[$subKey];
    }

    public static function celsiusToFahrenheit(float $celsius): float
    {
        return ($celsius * 9 / 5) + 32;
    }

}
