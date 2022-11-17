<?php

namespace Balsama\Tempbot;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Medoo\Medoo;
use stdClass;

class Helpers
{
    private const BOSTON_STATION_URL = 'api.weather.gov/stations/KBOS/observations/latest';

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
            'TB0101' => '192.168.7.154',
            'TB0201' => '192.168.7.155',
            'TB0301' => '192.168.7.152',
            'TB0302' => '192.168.7.156',
            'TB0401' => '192.168.7.157',
            'KBOS' => 'api.weather.gov/stations/KBOS/observations/latest',
        ];
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
        $sensors['KBOS'] = self::getExternalSensorReading();
        return $sensors;
    }

    public static function getExternalSensorReading(string $sensorId = 'KBOS'): SensorReading
    {
        return new SensorReading($sensorId);
    }

    /**
     * @param SensorReading[] $sensorReadings
     * @return void
     * @throws Exception
     */
    public static function writeSensorsCsvLine(array $sensorReadings): void
    {
        $date = new \DateTimeImmutable('now', new \DateTimeZone('America/New_York'));
        $datetime = $date->format('Y-m-d H:i:s');

        $outside = Helpers::getCurrentBostonObservations();
        if (!$outside->properties->relativeHumidity->value) {
            // Sometimes the API returns this as null, in which case we can't pass it off to the converter.
            $outsideTemp = null;
        } else {
            $outsideTemp = Helpers::c2f((float) $outside->properties->temperature->value);
        }
        if (!$outside->properties->relativeHumidity->value) {
            $outsideHumidity = null;
        } else {
            $outsideHumidity = $outside->properties->relativeHumidity->value;
        }

        $recordLine = new RecordEntry(
            $datetime,
            $outsideTemp,
            $outsideHumidity,
            $sensorReadings['TB0101']->getTemp(),
            $sensorReadings['TB0201']->getTemp(),
            $sensorReadings['TB0301']->getTemp(),
            $sensorReadings['TB0302']->getTemp(),
            $sensorReadings['TB0401']->getTemp(),
            $sensorReadings['TB0101']->getHumidity(),
            $sensorReadings['TB0201']->getHumidity(),
            $sensorReadings['TB0301']->getHumidity(),
            $sensorReadings['TB0302']->getHumidity(),
            $sensorReadings['TB0401']->getHumidity(),
        );

        $csvLine = $recordLine->getArray();

        self::csv([], [$csvLine], 'temps.csv', true, __DIR__ . '/../data/');
    }

    public static function writeSensorReadingsDb(array $sensorReadings)
    {
        foreach ($sensorReadings as $sensorReading) {
            self::writeSensorReadingDb($sensorReading);
        }
    }

    public static function writeSensorReadingDb(SensorReading $sensorReading)
    {
        $database = Helpers::initializeDatabase();

        $database->insert(
            'sensor_record',
            [
                'sensor_id' => $sensorReading->getTbId(),
                'timestamp' => time(),
                'temperature' => $sensorReading->getTemp(),
                'humidity' => $sensorReading->getHumidity(),
            ]
        );
    }

    /**
     * Writes an array of arrays to a CSV file.
     *
     * @param string[] $headers
     *   The names of the table columns. Pass an empty array if you don't want any headers (e.g. if you're appending to
     *   an existing file.
     * @param array[] $data
     *   Data to write. Each top-level array should contain an array the same length as the $header array.
     * @param string $filename
     * @param bool $append
     *   Whether to append to the file if it exist or overwrite from the beginning of the file.
     * @param string $path
     */
    public static function csv(array $headers, array $data, string $filename, $append = false, $path = 'data/'): void
    {
        if ($headers && $data) {
            if (count($headers) !== count(reset($data))) {
                throw new \InvalidArgumentException(
                    'The length of the `$header` array must equal the length of each of the arrays in `$data`'
                );
            }
        }

        $mode = ($append) ? 'a' : 'w';

        $fp = fopen($path . $filename, $mode);
        if ($headers) {
            fputcsv($fp, $headers);
        }
        foreach ($data as $datum) {
            fputcsv($fp, $datum);
        }
        fclose($fp);
    }

    public static function getCurrentBostonObservations(): stdClass
    {
        $url = self::BOSTON_STATION_URL;
        return json_decode(self::fetch($url)->getBody());
    }

    public static function fetch($url, Client $client = new Client()): ?Response
    {
        try {
            return $client->get($url, [
                'timeout' => 6,
                'connect_timeout' => 6,
            ]);
        } catch (\Throwable $e) {
            if (in_array($url, self::getSensorIps())) {
                $tbid = self::getSensorIdByIp($url);
                echo $e->getMessage() . PHP_EOL;
                echo "Sensor '$tbid' appears to be unreachable at $url." . PHP_EOL;
            } elseif ($url === self::BOSTON_STATION_URL) {
                echo $e->getMessage() . PHP_EOL;
                echo "Boston weather station at appears to be unreachable at $url." . PHP_EOL;
            } else {
                echo $e->getMessage() . PHP_EOL;
            }
            return null;
        }
    }

    public static function c2f(float $c): float
    {
        return ($c * 9 / 5) + 32;
    }

    public static function initializeDatabase(): Medoo
    {

        $database = new Medoo([
            'type' => 'sqlite',
            'database' => __DIR__ . '/../databases/picotemps.db'
        ]);


        $database->create('sensor_record', [
            'sensor_id' => ['TEXT'],
            'timestamp' => ['INTEGER'],
            'temperature' => ['FLOAT'],
            'humidity' => ['FLOAT'],
        ]);

        return $database;
    }
}
