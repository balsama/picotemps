<?php

namespace Balsama\Tempbot;

use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Client;
use PHPUnit\Framework\TestCase;

class HelpersTest extends TestCase
{
    public function testGetLoganReading()
    {
        $foo = Helpers::getExternalSensorReading('KBOS');
        $this->assertTrue(true);
    }

    public function testGetDbRecords()
    {
        $foo = Helpers::getDbRecordsByStationId('KBOS');
        $bar = Helpers::getDbRecordsByStationId('KBOS', null, 1668657853);
        $baz = Helpers::getDbRecordsByStationId('KBOS', 1668657831, null);
        $bat = Helpers::getDbRecordsByStationId('KBOS', 1668657811, 1668659363);
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
        $response = Helpers::fetch('example.com', 1, $client);
        $this->assertNull($response);
    }
}
