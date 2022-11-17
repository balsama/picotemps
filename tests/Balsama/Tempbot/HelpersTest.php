<?php

namespace Balsama\Tempbot;

class HelpersTest extends \PHPUnit\Framework\TestCase
{
    public function testGetLoganReading()
    {
        $foo = Helpers::getExternalSensorReading('KBOS');
    }
}
