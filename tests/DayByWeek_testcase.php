<?php
/**
 * This file is part of the TYPO3 extension Calendar Base (cal).
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 extension Calendar Base (cal) project - inspiring people to share!
 */
use TYPO3\CMS\Cal\Utility\Functions;

/**
 * Test case for WEC Map
 *
 * WARNING: Never ever run a unit test like this on a live site!
 */
class DayByWeek_testcase extends tx_phpunit_testcase
{
    public function testFirstDayOfWeek1OfYear2013_is_20121231()
    {
        $this->assertEquals('20121231', Functions::getDayByWeek(2013, 1, 1));
    }

    public function testSecondDayOfWeek1OfYear2013_is_20130101()
    {
        $this->assertEquals('20130101', Functions::getDayByWeek(2013, 1, 2));
    }

    public function testThirdDayOfWeek1OfYear2013_is_20130102()
    {
        $this->assertEquals('20130102', Functions::getDayByWeek(2013, 1, 3));
    }

    public function testFourthDayOfWeek1OfYear2013_is_20130103()
    {
        $this->assertEquals('20130103', Functions::getDayByWeek(2013, 1, 4));
    }

    public function testFifthDayOfWeek1OfYear2013_is_20130104()
    {
        $this->assertEquals('20130104', Functions::getDayByWeek(2013, 1, 5));
    }

    public function testSixthDayOfWeek1OfYear2013_is_20130105()
    {
        $this->assertEquals('20130105', Functions::getDayByWeek(2013, 1, 6));
    }

    public function testSeventhDayOfWeek1OfYear2013_is_20130106()
    {
        $this->assertEquals('20130106', Functions::getDayByWeek(2013, 1, 0));
    }

    public function testFirstDayOfWeek1OfYear2013WeekstartSunday_is_20130304()
    {
        $this->assertEquals('20130304', Functions::getDayByWeek(2013, 10, 1));
    }
}
