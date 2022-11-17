<?php

namespace Balsama\Tempbot;

class HelpersTest extends \PHPUnit\Framework\TestCase
{
    public function testGetLoganReading()
    {
        $foo = Helpers::getExternalSensorReading('KBOS');
        $this->assertTrue(true);
    }

    public function testGetDbRecords()
    {
        $foo = Helpers::getDbRecords('KBOS');
        $bar = Helpers::getDbRecords('KBOS', null, 1668657853);
        $baz = Helpers::getDbRecords('KBOS', 1668657831, null);
        $bat = Helpers::getDbRecords('KBOS', 1668657811, 1668659363);
        $this->assertTrue(true);
    }
}
