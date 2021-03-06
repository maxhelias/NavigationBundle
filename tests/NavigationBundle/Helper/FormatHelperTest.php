<?php

namespace DH\NavigationBundle\Tests\Helper;

use DH\NavigationBundle\Helper\FormatHelper;
use PHPUnit\Framework\TestCase;

/**
 * @covers \DH\NavigationBundle\Helper\FormatHelper
 */
class FormatHelperTest extends TestCase
{
    public function testFormatTime(): void
    {
        $this->assertSame('1 sec', FormatHelper::formatTime(1));
        $this->assertSame('17 secs', FormatHelper::formatTime(17));
        $this->assertSame('1 min', FormatHelper::formatTime(60));
        $this->assertSame('5 mins 15 secs', FormatHelper::formatTime(315, 2));
        $this->assertSame('5 mins', FormatHelper::formatTime(315));
        $this->assertSame('1 hr', FormatHelper::formatTime(3600));
        $this->assertSame('3 hrs', FormatHelper::formatTime(10800));
        $this->assertSame('3 hrs 5 secs', FormatHelper::formatTime(10805, 2));
        $this->assertSame('3 hrs', FormatHelper::formatTime(10805));
        $this->assertSame('3 hrs 5 mins', FormatHelper::formatTime(11100, 2));
        $this->assertSame('3 hrs', FormatHelper::formatTime(11100));
        $this->assertSame('1 day', FormatHelper::formatTime(86400));
        $this->assertSame('2 days 7 hrs 33 mins 20 secs', FormatHelper::formatTime(200000, 4));
        $this->assertSame('2 days 7 hrs 33 mins', FormatHelper::formatTime(200000, 3));
        $this->assertSame('2 days 7 hrs', FormatHelper::formatTime(200000, 2));
        $this->assertSame('2 days', FormatHelper::formatTime(200000));
    }

    public function testFormatDistance(): void
    {
        $this->assertSame('700 m', FormatHelper::formatDistance(700));
        $this->assertSame('1.5 km', FormatHelper::formatDistance(1500));
        $this->assertSame('1 km 500 m', FormatHelper::formatDistance(1500, 2));
        $this->assertSame('7 km', FormatHelper::formatDistance(7000));
    }
}
