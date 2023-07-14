<?php

namespace Balsama\Tempbot;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Monolog\Level;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class Fetch
{
    private Logger $log;
    private const LOGPATH = __DIR__ . '/../logs/tempbot.log';
    private const BOSTON_STATION_URL = Helpers::BOSTON_STATION_URL;
    private const BC_STATION_URL = Helpers::BC_STATION_URL;
    private const LOGAN_STATION_URL = Helpers::LOGAN_STATION_URL;

    public function __construct()
    {
        $this->log = new Logger('Fetch Log');
        $this->log->pushHandler(new StreamHandler(self::LOGPATH, Level::Warning));
    }

    public static function fetch(
        string $url,
        int $retry = 2,
        Client $client = new Client()
    ): ?Response {
        try {
            return $client->get($url, [
                'timeout' => 6,
                'connect_timeout' => 6,
            ]);
        } catch (\Throwable $e) {
            if ($retry) {
                TempBotLogger::logWarn("Sensor at $url didn't respond. Trying $retry more times.");
                $retry--;
                return self::fetch($url, $retry, $client);
            }
            if (in_array($url, Helpers::getSensorIps())) {
                $tbid = Helpers::getSensorIdByIp($url);
                TempBotLogger::logError("Sensor '$tbid' appears to be unreachable at $url.");
            } elseif ($url === self::BOSTON_STATION_URL) {
                TempBotLogger::logError("Boston weather station appears to be unreachable at $url.");
            } else {
                TempBotLogger::logError($e->getMessage());
            }
            return null;
        }
    }
}
