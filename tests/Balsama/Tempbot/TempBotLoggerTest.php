<?php

namespace Balsama\Tempbot;

use PHPUnit\Framework\TestCase;

class TempBotLoggerTest extends TestCase
{
    public function testLog()
    {
        TempBotLogger::logNotice('foo');
        TempBotLogger::logError('bar');
        TempBotLogger::logDebug('baz');
        $this->assertTrue(true);
    }
}
