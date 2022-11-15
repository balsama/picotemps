<?php

namespace Balsama\Tempbot;

use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Client;

class SensorReading
{
    private string $ip;
    private ?Response $response;
    private ?\stdClass $responseBody;
    private Client $client;

    public function __construct(string $sensorId, Client $client = new Client())
    {
        $this->client = $client;
        $this->ip = Helpers::getSensorIpById($sensorId);
        $this->response = $this->getReading();
        $this->responseBody = ($this->response) ? json_decode($this->response->getBody()) : null;
    }

    public function getTemp()
    {
        if (!$this->response) {
            return null;
        }
        return Helpers::c2f($this->responseBody->temperature);
    }
    public function getHumidity()
    {
        if (!$this->response) {
            return null;
        }
        return $this->responseBody->humidity;
    }
    public function getTbId()
    {
        if (!$this->response) {
            return null;
        }
        return $this->responseBody->id;
    }

    private function getReading(): ?Response
    {
        return Helpers::fetch($this->ip, $this->client);
    }
}
