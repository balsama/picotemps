<?php

namespace Balsama\Tempbot;

use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Client;

class SensorReading
{
    private string $ip;
    private string $id;
    private ?Response $response;
    public ?\stdClass $responseBody;
    private Client $client;
    private string $responseType;

    public function __construct(string $sensorId, Client $client = new Client())
    {
        $this->client = $client;
        $this->ip = Helpers::getSensorIpById($sensorId);
        $this->id = $sensorId;
        $this->response = $this->getReading();
        $this->responseBody = ($this->response) ? json_decode($this->response->getBody()) : null;
        $this->responseType = $this->getType();
        // @todo Sensor Reading should provide its own timestamp.
    }

    public function getTemp(): ?float
    {
        if (!$this->response) {
            return null;
        }
        $rawTemp = $this->getRawTemp();
        if (!$rawTemp) {
            return null;
        }
        if ($this->isOutSideRange($rawTemp)) {
            return null;
        }
        return Helpers::celsiusToFahrenheit($rawTemp);
    }

    public function getHumidity(): ?float
    {
        if (!$this->response) {
            return null;
        }
        if ($this->responseType === 'weather.gov') {
            return $this->responseBody->properties->relativeHumidity->value;
        }
        return $this->responseBody->humidity;
    }

    public function getTbId(): string
    {
        if (!$this->response) {
            return $this->id;
        }
        if ($this->responseType === 'weather.gov') {
            $stationParts = explode('/', $this->responseBody->properties->station);
            return end($stationParts);
        }
        return $this->responseBody->id;
    }

    private function getReading(): ?Response
    {
        return Fetch::fetch($this->ip, 1, $this->client);
    }

    private function getType(): string
    {
        if (!$this->responseBody) {
            return '';
        }
        if (!property_exists($this->responseBody, 'properties')) {
            return 'lan';
        }
        return 'weather.gov';
    }

    private function isOutSideRange(int $value, int $low = -25, int $high = 45): bool
    {
        if ($value < $low) {
            return true;
        }
        if ($value > $high) {
            return true;
        }
        return false;
    }

    private function getRawTemp(): ?float
    {
        if ($this->responseType === 'weather.gov') {
            if ($this->responseBody->properties->temperature->value) {
                return $this->responseBody->properties->temperature->value;
            }
            return null;
        }
        return $this->responseBody->temperature;
    }
}
