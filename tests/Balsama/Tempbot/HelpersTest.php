<?php

namespace Balsama\Tempbot;

use PHPUnit\Framework\TestCase;

class HelpersTest extends TestCase
{

    public function testc2f()
    {
        $c = -40;
        $f = Helpers::c2f($c);
        $this->assertEquals(-40, $f);

        $c = (float) null;
        $f = Helpers::c2f($c);
        $this->assertEquals(32, $f);

        $c = 100;
        $f = Helpers::c2f($c);
        $this->assertEquals(212, $f);
    }

}