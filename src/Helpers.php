<?php

namespace Balsama\Tempbot;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Medoo\Medoo;
use stdClass;

class Helpers
{
    public const BOSTON_STATION_URL = 'api.weather.gov/stations/0258W/observations/latest';

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
            'TB0201' => '192.168.7.160',
            'TB0301' => '192.168.7.152',
            'TB0302' => '192.168.7.156',
            'TB0401' => '192.168.7.159',
            'KBOS' => 'api.weather.gov/stations/KBOS/observations/latest',
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
        $time = time();
        $groupReadId = md5($time);
        $database = Helpers::initializeDatabase();
        $database->insert(
            'group_read',
            [
                'groupReadId' => $groupReadId,
                'timestamp' => $time,
            ]
        );
        foreach ($sensorReadings as $sensorReading) {
            self::writeSensorReadingDb($sensorReading, $groupReadId);
        }
        TempBotLogger::logNotice('Wrote DB record. Group Read ID: ' . $groupReadId . '.');
    }

    public static function writeSensorReadingDb(SensorReading $sensorReading, string $groupReadId)
    {
        $database = Helpers::initializeDatabase();

        $database->insert(
            'sensor_record',
            [
                'groupReadId' => $groupReadId,
                'sensor_id' => $sensorReading->getTbId(),
                'timestamp' => time(),
                'temperature' => $sensorReading->getTemp(),
                'humidity' => $sensorReading->getHumidity(),
            ]
        );
    }

    public static function writeInfluxDb(array $sensorReadings)
    {
        $readings = array_filter($sensorReadings, function($obj){
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

    public static function getCurrentBostonObservations(Client $client = new Client()): ?stdClass
    {
        $url = self::BOSTON_STATION_URL;
        if ($response = Fetch::fetch($url, 1, $client)) {
            return json_decode($response->getBody());
        }
        return null;
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


        $database->create('group_read', [
            'groupReadId' => ['TEXT'],
            'timestamp' => ['INTEGER'],
        ]);

        $database->create('sensor_record', [
            'groupReadId' => ['TEXT'],
            'sensor_id' => ['TEXT'],
            'timestamp' => ['INTEGER'],
            'temperature' => ['FLOAT'],
            'humidity' => ['FLOAT'],
        ]);

        return $database;
    }

    public static function getDbRecordsByStationId(
        string $stationId,
        int $after = null,
        int $before = null
    ): array {
        $database = Helpers::initializeDatabase();
        $join = [
            'sensor_id',
            'timestamp',
            'temperature',
            'humidity',
        ];
        $columns['sensor_id'] = $stationId;
        if ($after) {
            $columns['timestamp[>]'] = $after;
        }
        if ($before) {
            $columns['timestamp[<]'] = $before;
        }
        $records = $database->select('sensor_record', $join, $columns);

        return $records;
    }

    public static function getChartData(
        int $after = null,
        int $before = null
    ) {
        $sensorReadings = [];
        $sensorIds = self::getSensorIds();
        $sensorGraphData = [];
        foreach ($sensorIds as $sensorId) {
            if (str_starts_with($sensorId, 'TB')) {
                $sensorId = strtolower($sensorId);
            }
            $sensorReadings[$sensorId] = self::getDbRecordsByStationId($sensorId, $after);
            foreach ($sensorReadings[$sensorId] as $reading) {
                $sensorGraphData[$sensorId][] = ['x' => (1000 * $reading['timestamp']), 'y' => $reading['temperature']];
            }
        }
        return $sensorGraphData;
    }

    /**
     * This is copy pasta crap. Should be combined with the above function. Which is also crap.
     */
    public static function tempToColor(float $value, int $min = 58, int $max = 72)
    {
        $gradientColors = array_reverse(self::gradient());
        // Ensure value is in range
        if ($value < $min) {
            $value = $min;
        }
        if ($value > $max) {
            $value = $max;
        }

        // Normalize min-max range to [0, positive_value]
        $max -= $min;
        $value -= $min;
        $min = 0;

        // Calculate distance from min to max in [0,1]
        $distFromMin = $value / $max;

        // Define start and end color
        if (count($gradientColors) == 0) {
            return self::colorFromRange($value, $min, $max, ['#CC0000', '#EEEE00', '#00FF00']);
        } elseif (count($gradientColors) == 2) {
            $startColor = $gradientColors[0];
            $endColor = $gradientColors[1];
        } elseif (count($gradientColors) > 2) {
            $startColor = $gradientColors[floor($distFromMin * (count($gradientColors) - 1))];
            $endColor = $gradientColors[ceil($distFromMin * (count($gradientColors) - 1))];

            $distFromMin *= count($gradientColors) - 1;
            while ($distFromMin > 1) {
                $distFromMin--;
            }
        } else {
            die("Please pass more than one color or null to use default red-green colors.");
        }

        // Remove hex from string
        if ($startColor[0] === '#') {
            $startColor = substr($startColor, 1);
        }
        if ($endColor[0] === '#') {
            $endColor = substr($endColor, 1);
        }

        // Parse hex
        list($ra, $ga, $ba) = sscanf("#$startColor", "#%02x%02x%02x");
        list($rz, $gz, $bz) = sscanf("#$endColor", "#%02x%02x%02x");

        // Get rgb based on
        $distDiff = 1 - $distFromMin;
        $r = intval(($rz * $distFromMin) + ($ra * $distDiff));
        $r = min(max(0, $r), 255);
        $g = intval(($gz * $distFromMin) + ($ga * $distDiff));
        $g = min(max(0, $g), 255);
        $b = intval(($bz * $distFromMin) + ($ba * $distDiff));
        $b = min(max(0, $b), 255);

        // Convert rgb back to hex
        $rgbColorAsHex = '#' .
            str_pad(dechex($r), 2, "0", STR_PAD_LEFT) .
            str_pad(dechex($g), 2, "0", STR_PAD_LEFT) .
            str_pad(dechex($b), 2, "0", STR_PAD_LEFT);

        return $rgbColorAsHex;
    }

    public static function gradient($HexFrom = 'ff7e00', $HexTo = 'fff3e7', $ColorSteps = 50)
    {
        $FromRGB['r'] = hexdec(substr($HexFrom, 0, 2));
        $FromRGB['g'] = hexdec(substr($HexFrom, 2, 2));
        $FromRGB['b'] = hexdec(substr($HexFrom, 4, 2));

        $ToRGB['r'] = hexdec(substr($HexTo, 0, 2));
        $ToRGB['g'] = hexdec(substr($HexTo, 2, 2));
        $ToRGB['b'] = hexdec(substr($HexTo, 4, 2));

        $StepRGB['r'] = ($FromRGB['r'] - $ToRGB['r']) / ($ColorSteps - 1);
        $StepRGB['g'] = ($FromRGB['g'] - $ToRGB['g']) / ($ColorSteps - 1);
        $StepRGB['b'] = ($FromRGB['b'] - $ToRGB['b']) / ($ColorSteps - 1);

        $GradientColors = array();

        for ($i = 0; $i <= $ColorSteps; $i++) {
            $RGB['r'] = floor($FromRGB['r'] - ($StepRGB['r'] * $i));
            $RGB['g'] = floor($FromRGB['g'] - ($StepRGB['g'] * $i));
            $RGB['b'] = floor($FromRGB['b'] - ($StepRGB['b'] * $i));

            $HexRGB['r'] = sprintf('%02x', ($RGB['r']));
            $HexRGB['g'] = sprintf('%02x', ($RGB['g']));
            $HexRGB['b'] = sprintf('%02x', ($RGB['b']));

            $GradientColors[] = implode('', $HexRGB);
        }
        return array_filter($GradientColors, function ($val) {
            return strlen($val) == 6;
        });
    }
}
