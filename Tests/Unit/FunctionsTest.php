<?php
declare(strict_types = 1);

/*
 * This file is part of the web-tp3/cal.
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace TYPO3\CMS\Cal\Tests\Unit;

use TYPO3\TestingFramework\Core\Unit\UnitTestCase;
use TYPO3\CMS\Cal\Utility\Functions;

/**
 * Class FunctionsTest
 */
class FunctionsTest extends UnitTestCase
{

    /**
     * @return array
     */
    public function getDayByWeekDataProvider(): array
    {
        return [
            '2012-12-31' => [
                'parameter' => [
                    'year' => 2013,
                    'week' => 1,
                    'dayOfTheWeek' => 1
                ],
                'expectedDate' => '20121231'
            ],
            '2013-01-01' => [
                'parameter' => [
                    'year' => 2013,
                    'week' => 1,
                    'dayOfTheWeek' => 2
                ],
                'expectedDate' => '20130101'
            ],
            '2013-01-06' => [
                'parameter' => [
                    'year' => 2013,
                    'week' => 1,
                    'dayOfTheWeek' => 0
                ],
                'expectedDate' => '20130106'
            ],
        ];
    }

    /**
     * @param array $parameter
     * @param string $expectedDate
     * @dataProvider getDayByWeekDataProvider
     */
    public function testGetDayByWeek(array $parameter, string $expectedDate)
    {
        $this->assertEquals(
            $expectedDate,
            Functions::getDayByWeek($parameter['year'], $parameter['week'], $parameter['dayOfTheWeek'])
        );
    }
}
