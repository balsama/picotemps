<?php

namespace Balsama\Tempbot;

use PHPUnit\Framework\TestCase;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Client;

class HelpersTest extends TestCase
{
    public function testGetLoganReading()
    {
        $this->assertTrue(true);
    }

    public function testFetch()
    {
        $mock = new MockHandler([
            new \Exception('Mock exception'),
            new \Exception('Mock exception'),
        ]);
        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);
        $response = Fetch::fetch('example.com', 1, $client);
        $this->assertNull($response);
    }

    public function testGetConfig()
    {
        $allConfig = Helpers::getConfig();
        $this->assertIsArray($allConfig);
        $this->assertCount(2, $allConfig);

        $sensorsConfig = Helpers::getConfig(['sensors']);
        $this->assertIsArray($sensorsConfig);

        $influxUrlConfig = Helpers::getConfig(['influx', 'url']);
        $this->assertIsArray($influxUrlConfig);
        $this->assertCount(3, $influxUrlConfig);

        $influxOrg = Helpers::getConfig(['influx', 'org']);
        $this->assertIsString($influxOrg);
    }
}
