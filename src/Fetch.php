<?php

namespace Balsama\Tempbot;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;

class Fetch
{
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
                $retry--;
                return self::fetch($url, $retry, $client);
            }
            return null;
        }
    }
}
