<?php

namespace Balsama\Tempbot;

use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\HandlerStack;

/**
 * @note Currently requires access to LAN.
 */
class SensorReadingTest extends TestCase
{
    private SensorReading $sensor;
    private const SENSORID = 'TB0101';
    private const TEMP = 20.2;
    private const HUMIDITY = 50.1;

    protected function setUp(): void
    {
        parent::setUp();

        $body = [
            'temperature' => self::TEMP,
            'humidity' => self::HUMIDITY,
            'id' => self::SENSORID,
        ];
        $mock = new MockHandler([
            new Response(200, [], json_encode($body)),
        ]);
        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);
        $this->sensor = new SensorReading(self::SENSORID, $client);
    }

    public function testGetTemp()
    {
        $temp = $this->sensor->getTemp();
        $this->assertIsFloat($temp);
        $this->assertEquals(Helpers::c2f(self::TEMP), $temp);
    }

    public function testGetId()
    {
        $id = $this->sensor->getTbId();
        $this->assertIsString($id);
        $this->assertEquals(self::SENSORID, $id);
    }

    public function testGetHumidity()
    {
        $humidity = $this->sensor->getHumidity();
        $this->assertEquals(self::HUMIDITY, $humidity);
    }
}
