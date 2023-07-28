<?php

namespace Balsama\Tempbot;

class Helpers
{
    public const BC_STATION_URL = 'api.weather.gov/stations/0258W/observations/latest';
    public const LOGAN_STATION_URL = 'api.weather.gov/stations/KBOS/observations/latest';

    public static function getSensorIds(): array
    {
        return array_keys(self::sensorIpMap());
    }

    public static function getSensorIps(): array
    {
        return array_values(self::sensorIpMap());
    }

    public static function getSensorIpById(string $sensorId): string
    {
        $map = self::sensorIpMap();
        return $map[$sensorId];
    }

    public static function getSensorIdByIp(string $sensorIp): string
    {
        $map = array_flip(self::sensorIpMap());
        return $map[$sensorIp];
    }

    public static function sensorIpMap(): array
    {
        return [
            'TB0101' => '192.168.7.161',
            'TB0102' => '192.168.7.184',
            'TB0201' => '192.168.7.160',
            'TB0301' => '192.168.7.152',
            'TB0302' => '192.168.7.156',
            'TB0401' => '192.168.7.159',
            'LOGAN' => self::LOGAN_STATION_URL,
            'BC' => self::BC_STATION_URL,
        ];
    }

    public static function availableSensor($host, $port = 80, $timeout = 3)
    {
        $fp = @fSockOpen($host, $port, $errno, $errstr, $timeout);
        return $fp != false;
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
