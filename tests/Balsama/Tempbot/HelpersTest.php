<?php

namespace Balsama\Tempbot;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;

class HelpersTest extends \PHPUnit\Framework\TestCase
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

    public function testGetCurrentBostonObservations()
    {
        $mock = new MockHandler([
            new \Exception('Mock exception'),
            new \Exception('Mock exception'),
        ]);
        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);
        $this->assertNull(Helpers::getCurrentBostonObservations($client));
    }

    public function testTempToColor()
    {
        for ($temp = 50; $temp < 101; $temp++) {
            $colors[] = Helpers::tempToColor($temp);
        }
        $foo = 11;
    }
}
