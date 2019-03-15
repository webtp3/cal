<?php
declare(strict_types=1);

namespace TYPO3\CMS\Cal\Tests\Unit\Services;


use TYPO3\CMS\Cal\Service\DateCalculationService;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * Class DateCalculationServiceTest
 * @package TYPO3\CMS\Cal\Tests\Unit\Services
 */
class DateCalculationServiceTest extends UnitTestCase
{

    /**
     * @return array
     */
    public function firstOfMonthWeekdayDataProvider(): array
    {
        return [
            '2019-01-01' => [
                'date' => [
                    'month' => 1,
                    'year' => 2019
                ],
                'expected' => 2
            ],
            '2019-02-01' => [
                'date' => [
                    'month' => 2,
                    'year' => 2019
                ],
                'expected' => 5
            ]
        ];
    }

    /**
     * @param array $date
     * @param int $expected
     * @dataProvider firstOfMonthWeekdayDataProvider
     */
    public function testFirstOfMonthWeekday(array $date, int $expected)
    {
        $this->assertEquals(
            $expected,
            DateCalculationService::firstOfMonthWeekday($date['month'], $date['year'])
        );
    }

    /**
     * @return array
     */
    public function isLeapYearDataProvider(): array
    {
        return [
            4 => [
                'year' => 4,
                'expected' => true
            ],
            1582 => [
                'year' => 1582,
                'expected' => false
            ],
            1584 => [
                'year' => 1584,
                'expected' => true
            ],
            1600 => [
                'year' => 1600,
                'expected' => true
            ],
            1999 => [
                'year' => 1999,
                'expected' => false
            ],
            2000 => [
                'year' => 2000,
                'expected' => true
            ],
            2001 => [
                'year' => 2001,
                'expected' => false
            ],
        ];
    }

    /**
     * @param int $year
     * @param bool $expected
     * @dataProvider isLeapYearDataProvider
     */
    public function testIsLeapYear(int $year, bool $expected)
    {
        $this->assertEquals(
            $expected,
            DateCalculationService::isLeapYear($year)
        );
    }

    /**
     * @return array
     */
    public function dateDiffDataProvider(): array
    {
        return [
            '+ 1 day' => [
                'dates' => [
                    'day1' => 15,
                    'month1' => 5,
                    'year1' => 2018,
                    'day2' => 16,
                    'month2' => 5,
                    'year2' => 2018,
                ],
                'expected' => 1
            ],
            '-1 day' => [
                'dates' => [
                    'day1' => 15,
                    'month1' => 5,
                    'year1' => 2018,
                    'day2' => 14,
                    'month2' => 5,
                    'year2' => 2018,
                ],
                'expected' => 1
            ],
            '+ 23 days' => [
                'dates' => [
                    'day1' => 15,
                    'month1' => 5,
                    'year1' => 2018,
                    'day2' => 7,
                    'month2' => 6,
                    'year2' => 2018,
                ],
                'expected' => 23
            ],
            '- 23 days' => [
                'dates' => [
                    'day1' => 15,
                    'month1' => 5,
                    'year1' => 2018,
                    'day2' => 22,
                    'month2' => 4,
                    'year2' => 2018,
                ],
                'expected' => 23
            ],
            '0 days' => [
                'dates' => [
                    'day1' => 15,
                    'month1' => 5,
                    'year1' => 2018,
                    'day2' => 15,
                    'month2' => 5,
                    'year2' => 2018,
                ],
                'expected' => 0
            ],
        ];
    }

    /**
     * @param array $dates
     * @param int $expected
     * @dataProvider dateDiffDataProvider
     */
    public function testDateDiff(array $dates, int $expected)
    {
        $this->assertEquals(
            $expected,
            DateCalculationService::dateDiff($dates['day1'], $dates['month1'], $dates['year1'], $dates['day2'],
                $dates['month2'], $dates['year2'])
        );
    }
}
