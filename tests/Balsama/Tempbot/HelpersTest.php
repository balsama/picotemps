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
        $foo = Helpers::getExternalSensorReading('KBOS');
        $this->assertTrue(true);
    }

    public function testGetDbRecords()
    {
        $foo = Helpers::getDbRecordsByStationId('KBOS');
        $bar = Helpers::getDbRecordsByStationId('KBOS', null, 1668657853);
        $baz = Helpers::getDbRecordsByStationId('KBOS', 1668657831, null);
        $bat = Helpers::getDbRecordsByStationId('KBOS', 1668657811, 1668659363);
        $bee = Helpers::getDbRecordsByStationId('tb0201');
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
        $this->assertNull(Helpers::getCurrentBostonObservations($client));
        $response = Fetch::fetch('example.com', 1, $client);
        $this->assertNull($response);
    }
}
