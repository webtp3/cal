<?php
declare(strict_types = 1);

/*
 * This file is part of the web-tp3/cal.
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace TYPO3\CMS\Cal\Tests\Unit\Services;

use DateTime;
use TYPO3\CMS\Cal\Service\CalculateDateTimeService;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * Class CalculateDateTimeServiceTest
 */
class CalculateDateTimeServiceTest extends UnitTestCase
{

    /**
     * @return array
     */
    public function calculateStartOfDayDataProvider(): array
    {
        return [
            [
                'dateTime' => new DateTime('2019-03-15 16:46:25'),
                'expectedDateTime' => new DateTime('2019-03-15 00:00:00')
            ]
        ];
    }

    /**
     * @param DateTime $dateTime
     * @param DateTime $expectedDateTime
     * @dataProvider calculateStartOfDayDataProvider
     */
    public function testCalculateStartOfDay(DateTime $dateTime, DateTime $expectedDateTime)
    {
        $this->assertEquals(
            $expectedDateTime,
            CalculateDateTimeService::calculateStartOfDay($dateTime)
        );
    }

    /**
     * @return array
     */
    public function calculateEndOfDayDataProvider(): array
    {
        return [
            [
                'dateTime' => new DateTime('2019-03-15 16:46:25'),
                'expectedDateTime' => new DateTime('2019-03-15 23:59:59')
            ]
        ];
    }

    /**
     * @param DateTime $dateTime
     * @param DateTime $expectedDateTime
     * @dataProvider calculateEndOfDayDataProvider
     */
    public function testCalculateEndOfDay(DateTime $dateTime, DateTime $expectedDateTime)
    {
        $this->assertEquals(
            $expectedDateTime,
            CalculateDateTimeService::calculateEndOfDay($dateTime)
        );
    }

    /**
     * @return array
     */
    public function calculateStartOfWeekDataProvider(): array
    {
        return [
            [
                'dateTime' => new DateTime('2019-03-15 16:46:25'),
                'expectedDateTime' => new DateTime('2019-03-11 00:00:00')
            ],
            'today_is_monday' => [
                'dateTime' => new DateTime('2019-03-18 16:46:25'),
                'expectedDateTime' => new DateTime('2019-03-18 00:00:00')
            ],
            'before_month_switch' => [
                'dateTime' => new DateTime('2019-07-31 16:46:25'),
                'expectedDateTime' => new DateTime('2019-07-29 00:00:00')
            ],
            'after_month_switch' => [
                'dateTime' => new DateTime('2019-08-01 16:46:25'),
                'expectedDateTime' => new DateTime('2019-07-29 00:00:00')
            ],
            'leap_year' => [
                'dateTime' => new DateTime('2020-03-01 16:46:25'),
                'expectedDateTime' => new DateTime('2020-02-24 00:00:00')
            ]
        ];
    }

    /**
     * @param DateTime $dateTime
     * @param DateTime $expectedDateTime
     * @dataProvider calculateStartOfWeekDataProvider
     */
    public function testCalculateStartOfWeek(DateTime $dateTime, DateTime $expectedDateTime)
    {
        $this->assertEquals(
            $expectedDateTime,
            CalculateDateTimeService::calculateStartOfWeek($dateTime)
        );
    }

    /**
     * @return array
     */
    public function calculateEndOfWeekDataProvider(): array
    {
        return [
            [
                'dateTime' => new DateTime('2019-03-15 16:46:25'),
                'expectedDateTime' => new DateTime('2019-03-17 23:59:59')
            ],
            'today_is_sunday' => [
                'dateTime' => new DateTime('2019-03-24 16:46:25'),
                'expectedDateTime' => new DateTime('2019-03-24 23:59:59')
            ],
            'before_month_switch' => [
                'dateTime' => new DateTime('2019-07-31 16:46:25'),
                'expectedDateTime' => new DateTime('2019-08-04 23:59:59')
            ],
            'after_month_switch' => [
                'dateTime' => new DateTime('2019-08-01 16:46:25'),
                'expectedDateTime' => new DateTime('2019-08-04 23:59:59')
            ],
            'leap_year' => [
                'dateTime' => new DateTime('2020-02-27 16:46:25'),
                'expectedDateTime' => new DateTime('2020-03-01 23:59:59')
            ]
        ];
    }

    /**
     * @param DateTime $dateTime
     * @param DateTime $expectedDateTime
     * @dataProvider calculateEndOfWeekDataProvider
     */
    public function testCalculateEndOfWeek(DateTime $dateTime, DateTime $expectedDateTime)
    {
        $this->assertEquals(
            $expectedDateTime,
            CalculateDateTimeService::calculateEndOfWeek($dateTime)
        );
    }

    /**
     * @return array
     */
    public function calculateStartOfNextWeekDataProvider(): array
    {
        return [
            [
                'dateTime' => new DateTime('2019-03-15 16:46:25'),
                'expectedDateTime' => new DateTime('2019-03-18 00:00:00')
            ],
            'today_is_monday' => [
                'dateTime' => new DateTime('2019-03-11 16:46:25'),
                'expectedDateTime' => new DateTime('2019-03-18 00:00:00')
            ],
            'before_month_switch' => [
                'dateTime' => new DateTime('2019-07-31 16:46:25'),
                'expectedDateTime' => new DateTime('2019-08-05 00:00:00')
            ],
            'after_month_switch' => [
                'dateTime' => new DateTime('2019-08-01 16:46:25'),
                'expectedDateTime' => new DateTime('2019-08-05 00:00:00')
            ],
            'leap_year' => [
                'dateTime' => new DateTime('2020-03-01 16:46:25'),
                'expectedDateTime' => new DateTime('2020-03-02 00:00:00')
            ]
        ];
    }

    /**
     * @param DateTime $dateTime
     * @param DateTime $expectedDateTime
     * @dataProvider calculateStartOfNextWeekDataProvider
     */
    public function testCalculateStartOfNextWeek(DateTime $dateTime, DateTime $expectedDateTime)
    {
        $this->assertEquals(
            $expectedDateTime,
            CalculateDateTimeService::calculateStartOfNextWeek($dateTime)
        );
    }

    /**
     * @return array
     */
    public function calculateEndOfNextWeekDataProvider(): array
    {
        return [
            [
                'dateTime' => new DateTime('2019-03-15 16:46:25'),
                'expectedDateTime' => new DateTime('2019-03-24 23:59:59')
            ],
            'today_is_sunday' => [
                'dateTime' => new DateTime('2019-03-17 16:46:25'),
                'expectedDateTime' => new DateTime('2019-03-24 23:59:59')
            ],
            'before_month_switch' => [
                'dateTime' => new DateTime('2019-07-31 16:46:25'),
                'expectedDateTime' => new DateTime('2019-08-11 23:59:59')
            ],
            'after_month_switch' => [
                'dateTime' => new DateTime('2019-08-01 16:46:25'),
                'expectedDateTime' => new DateTime('2019-08-11 23:59:59')
            ],
            'leap_year' => [
                'dateTime' => new DateTime('2020-03-01 16:46:25'),
                'expectedDateTime' => new DateTime('2020-03-08 23:59:59')
            ]
        ];
    }

    /**
     * @param DateTime $dateTime
     * @param DateTime $expectedDateTime
     * @dataProvider calculateEndOfNextWeekDataProvider
     */
    public function testCalculateEndOfNextWeek(DateTime $dateTime, DateTime $expectedDateTime)
    {
        $this->assertEquals(
            $expectedDateTime,
            CalculateDateTimeService::calculateEndOfNextWeek($dateTime)
        );
    }

    /**
     * @return array
     */
    public function calculateStartOfLastWeekDataProvider(): array
    {
        return [
            [
                'dateTime' => new DateTime('2019-03-15 16:46:25'),
                'expectedDateTime' => new DateTime('2019-03-04 00:00:00')
            ],
            'before_month_switch' => [
                'dateTime' => new DateTime('2019-07-31 16:46:25'),
                'expectedDateTime' => new DateTime('2019-07-22 00:00:00')
            ],
            'after_month_switch' => [
                'dateTime' => new DateTime('2019-08-01 16:46:25'),
                'expectedDateTime' => new DateTime('2019-07-22 00:00:00')
            ],
            'leap_year' => [
                'dateTime' => new DateTime('2020-03-01 16:46:25'),
                'expectedDateTime' => new DateTime('2020-02-17 00:00:00')
            ]
        ];
    }

    /**
     * @param DateTime $dateTime
     * @param DateTime $expectedDateTime
     * @dataProvider calculateStartOfLastWeekDataProvider
     */
    public function testCalculateStartOfLastWeek(DateTime $dateTime, DateTime $expectedDateTime)
    {
        $this->assertEquals(
            $expectedDateTime,
            CalculateDateTimeService::calculateStartOfLastWeek($dateTime)
        );
    }

    /**
     * @return array
     */
    public function calculateEndOfLastWeekDataProvider(): array
    {
        return [
            [
                'dateTime' => new DateTime('2019-03-15 16:46:25'),
                'expectedDateTime' => new DateTime('2019-03-10 23:59:59')
            ],
            'before_month_switch' => [
                'dateTime' => new DateTime('2019-07-31 16:46:25'),
                'expectedDateTime' => new DateTime('2019-07-28 23:59:59')
            ],
            'after_month_switch' => [
                'dateTime' => new DateTime('2019-08-01 16:46:25'),
                'expectedDateTime' => new DateTime('2019-07-28 23:59:59')
            ],
            'leap_year' => [
                'dateTime' => new DateTime('2020-03-01 16:46:25'),
                'expectedDateTime' => new DateTime('2020-02-23 23:59:59')
            ]
        ];
    }

    /**
     * @param DateTime $dateTime
     * @param DateTime $expectedDateTime
     * @dataProvider calculateEndOfLastWeekDataProvider
     */
    public function testCalculateEndOfLastWeek(DateTime $dateTime, DateTime $expectedDateTime)
    {
        $this->assertEquals(
            $expectedDateTime,
            CalculateDateTimeService::calculateEndOfLastWeek($dateTime)
        );
    }

    /**
     * @return array
     */
    public function calculateStartOfMonthDataProvider(): array
    {
        return [
            [
                'dateTime' => new DateTime('2019-03-15 16:46:25'),
                'expectedDateTime' => new DateTime('2019-03-01 00:00:00')
            ]
        ];
    }

    /**
     * @param DateTime $dateTime
     * @param DateTime $expectedDateTime
     * @dataProvider calculateStartOfMonthDataProvider
     */
    public function testCalculateStartOfMonth(DateTime $dateTime, DateTime $expectedDateTime)
    {
        $this->assertEquals(
            $expectedDateTime,
            CalculateDateTimeService::calculateStartOfMonth($dateTime)
        );
    }

    /**
     * @return array
     */
    public function calculateEndOfMonthDataProvider(): array
    {
        return [
            [
                'dateTime' => new DateTime('2019-03-15 16:46:25'),
                'expectedDateTime' => new DateTime('2019-03-31 23:59:59')
            ],
            'leap_year' => [
                'dateTime' => new DateTime('20200215 16:46:25'),
                'expectedDateTime' => new DateTime('2020-02-29 23:59:59')
            ]
        ];
    }

    /**
     * @param DateTime $dateTime
     * @param DateTime $expectedDateTime
     * @dataProvider calculateEndOfMonthDataProvider
     */
    public function testCalculateEndOfMonth(DateTime $dateTime, DateTime $expectedDateTime)
    {
        $this->assertEquals(
            $expectedDateTime,
            CalculateDateTimeService::calculateEndOfMonth($dateTime)
        );
    }

    /**
     * @return array
     */
    public function calculateStartOfNextMonthDataProvider(): array
    {
        return [
            [
                'dateTime' => new DateTime('2019-03-15 16:46:25'),
                'expectedDateTime' => new DateTime('2019-04-01 00:00:00')
            ]
        ];
    }

    /**
     * @param DateTime $dateTime
     * @param DateTime $expectedDateTime
     * @dataProvider calculateStartOfNextMonthDataProvider
     */
    public function testCalculateStartOfNextMonth(DateTime $dateTime, DateTime $expectedDateTime)
    {
        $this->assertEquals(
            $expectedDateTime,
            CalculateDateTimeService::calculateStartOfNextMonth($dateTime)
        );
    }

    /**
     * @return array
     */
    public function calculateEndOfNextMonthDataProvider(): array
    {
        return [
            [
                'dateTime' => new DateTime('2019-03-15 16:46:25'),
                'expectedDateTime' => new DateTime('2019-04-30 23:59:59')
            ],
            'leap_year' => [
                'dateTime' => new DateTime('2020-02-15 16:46:25'),
                'expectedDateTime' => new DateTime('2020-03-31 23:59:59')
            ]
        ];
    }

    /**
     * @param DateTime $dateTime
     * @param DateTime $expectedDateTime
     * @dataProvider calculateEndOfNextMonthDataProvider
     */
    public function testCalculateEndOfNextMonth(DateTime $dateTime, DateTime $expectedDateTime)
    {
        $this->assertEquals(
            $expectedDateTime,
            CalculateDateTimeService::calculateEndOfNextMonth($dateTime)
        );
    }

    /**
     * @return array
     */
    public function calculateStartOfLastMonthDataProvider(): array
    {
        return [
            [
                'dateTime' => new DateTime('2019-03-15 16:46:25'),
                'expectedDateTime' => new DateTime('2019-02-01 00:00:00')
            ]
        ];
    }

    /**
     * @param DateTime $dateTime
     * @param DateTime $expectedDateTime
     * @dataProvider calculateStartOfLastMonthDataProvider
     */
    public function testCalculateStartOfLastMonth(DateTime $dateTime, DateTime $expectedDateTime)
    {
        $this->assertEquals(
            $expectedDateTime,
            CalculateDateTimeService::calculateStartOfLastMonth($dateTime)
        );
    }

    /**
     * @return array
     */
    public function calculateEndOfLastMonthDataProvider(): array
    {
        return [
            [
                'dateTime' => new DateTime('2019-03-15 16:46:25'),
                'expectedDateTime' => new DateTime('2019-02-28 23:59:59')
            ],
            'leap_year' => [
                'dateTime' => new DateTime('20200215 16:46:25'),
                'expectedDateTime' => new DateTime('2020-01-31 23:59:59')
            ]
        ];
    }

    /**
     * @param DateTime $dateTime
     * @param DateTime $expectedDateTime
     * @dataProvider calculateEndOfLastMonthDataProvider
     */
    public function testCalculateEndOfLastMonth(DateTime $dateTime, DateTime $expectedDateTime)
    {
        $this->assertEquals(
            $expectedDateTime,
            CalculateDateTimeService::calculateEndOfLastMonth($dateTime)
        );
    }

    /**
     * @return array
     */
    public function calculateStartOfYearDataProvider(): array
    {
        return [
            [
                'dateTime' => new DateTime('2019-03-15 16:46:25'),
                'expectedDateTime' => new DateTime('2019-01-01 00:00:00')
            ]
        ];
    }

    /**
     * @param DateTime $dateTime
     * @param DateTime $expectedDateTime
     * @dataProvider calculateStartOfYearDataProvider
     */
    public function testCalculateStartOfYear(DateTime $dateTime, DateTime $expectedDateTime)
    {
        $this->assertEquals(
            $expectedDateTime,
            CalculateDateTimeService::calculateStartOfYear($dateTime)
        );
    }

    /**
     * @return array
     */
    public function calculateEndOfYearDataProvider(): array
    {
        return [
            [
                'dateTime' => new DateTime('2019-03-15 16:46:25'),
                'expectedDateTime' => new DateTime('2019-12-31 23:59:59')
            ]
        ];
    }

    /**
     * @param DateTime $dateTime
     * @param DateTime $expectedDateTime
     * @dataProvider calculateEndOfYearDataProvider
     */
    public function testCalculateEndOfYear(DateTime $dateTime, DateTime $expectedDateTime)
    {
        $this->assertEquals(
            $expectedDateTime,
            CalculateDateTimeService::calculateEndOfYear($dateTime)
        );
    }

    /**
     * @return array
     */
    public function calculateStartOfNextYearDataProvider(): array
    {
        return [
            [
                'dateTime' => new DateTime('2019-03-15 16:46:25'),
                'expectedDateTime' => new DateTime('2020-01-01 00:00:00')
            ]
        ];
    }

    /**
     * @param DateTime $dateTime
     * @param DateTime $expectedDateTime
     * @dataProvider calculateStartOfNextYearDataProvider
     */
    public function testCalculateStartOfNextYear(DateTime $dateTime, DateTime $expectedDateTime)
    {
        $this->assertEquals(
            $expectedDateTime,
            CalculateDateTimeService::calculateStartOfNextYear($dateTime)
        );
    }

    /**
     * @return array
     */
    public function calculateEndOfNextYearDataProvider(): array
    {
        return [
            [
                'dateTime' => new DateTime('2019-03-15 16:46:25'),
                'expectedDateTime' => new DateTime('2020-12-31 23:59:59')
            ]
        ];
    }

    /**
     * @param DateTime $dateTime
     * @param DateTime $expectedDateTime
     * @dataProvider calculateEndOfNextYearDataProvider
     */
    public function testCalculateEndOfNextYear(DateTime $dateTime, DateTime $expectedDateTime)
    {
        $this->assertEquals(
            $expectedDateTime,
            CalculateDateTimeService::calculateEndOfNextYear($dateTime)
        );
    }

    /**
     * @return array
     */
    public function calculateStartOfLastYearDataProvider(): array
    {
        return [
            [
                'dateTime' => new DateTime('2019-03-15 16:46:25'),
                'expectedDateTime' => new DateTime('2018-01-01 00:00:00')
            ]
        ];
    }

    /**
     * @param DateTime $dateTime
     * @param DateTime $expectedDateTime
     * @dataProvider calculateStartOfLastYearDataProvider
     */
    public function testCalculateStartOfLastYear(DateTime $dateTime, DateTime $expectedDateTime)
    {
        $this->assertEquals(
            $expectedDateTime,
            CalculateDateTimeService::calculateStartOfLastYear($dateTime)
        );
    }

    /**
     * @return array
     */
    public function calculateEndOfLastYearDataProvider(): array
    {
        return [
            [
                'dateTime' => new DateTime('2019-03-15 16:46:25'),
                'expectedDateTime' => new DateTime('2018-12-31 23:59:59')
            ]
        ];
    }

    /**
     * @param DateTime $dateTime
     * @param DateTime $expectedDateTime
     * @dataProvider calculateEndOfLastYearDataProvider
     */
    public function testCalculateEndOfLastYear(DateTime $dateTime, DateTime $expectedDateTime)
    {
        $this->assertEquals(
            $expectedDateTime,
            CalculateDateTimeService::calculateEndOfLastYear($dateTime)
        );
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function calculateQuarterDataProvider(): array
    {
        return [
            'Q1' => [
                'dateTime' => new DateTime('2019-02-15 12:00:00'),
                'expectedQuarter' => 1
            ],
            'Q2' => [
                'dateTime' => new DateTime('2019-05-15 12:00:00'),
                'expectedQuarter' => 2
            ],
            'Q3' => [
                'dateTime' => new DateTime('2019-08-15 12:00:00'),
                'expectedQuarter' => 3
            ],
            'Q4' => [
                'dateTime' => new DateTime('2019-11-15 12:00:00'),
                'expectedQuarter' => 4
            ],
        ];
    }

    /**
     * @param DateTime $dateTime
     * @param int $expectedQuarter
     * @dataProvider calculateQuarterDataProvider
     */
    public function testCalculateQuarter(DateTime $dateTime, int $expectedQuarter)
    {
        $this->assertEquals(
            $expectedQuarter,
            CalculateDateTimeService::calculateQuarter($dateTime)
        );
    }

    /**
     * @return array
     */
    public function calculateStartOfQuarterDataProvider(): array
    {
        return [
            [
                'dateTime' => new DateTime('2019-03-15 16:46:25'),
                'expectedDateTime' => new DateTime('2019-01-01 00:00:00')
            ]
        ];
    }

    /**
     * @param DateTime $dateTime
     * @param DateTime $expectedDateTime
     * @dataProvider calculateStartOfQuarterDataProvider
     */
    public function testCalculateStartOfQuarter(DateTime $dateTime, DateTime $expectedDateTime)
    {
        $this->assertEquals(
            $expectedDateTime,
            CalculateDateTimeService::calculateStartOfQuarter($dateTime)
        );
    }

    /**
     * @return array
     */
    public function calculateEndOfQuarterDataProvider(): array
    {
        return [
            [
                'dateTime' => new DateTime('2019-03-15 16:46:25'),
                'expectedDateTime' => new DateTime('2019-03-31 23:59:59')
            ]
        ];
    }

    /**
     * @param DateTime $dateTime
     * @param DateTime $expectedDateTime
     * @dataProvider calculateEndOfQuarterDataProvider
     */
    public function testCalculateEndOfQuarter(DateTime $dateTime, DateTime $expectedDateTime)
    {
        $this->assertEquals(
            $expectedDateTime,
            CalculateDateTimeService::calculateEndOfQuarter($dateTime)
        );
    }

    /**
     * @return array
     */
    public function generateDateTimeDataProvider(): array
    {
        return [
            'default' => [
                'dateComponents' => [
                    'year' => 2019,
                    'month' => 3,
                    'day' => 16,
                    'hour' => 0,
                    'minute' => 0,
                    'second' => 0,
                ],
                'expectedDateTime' => new DateTime('2019-03-16 0:0:0')
            ],
            'second_overflow' => [
                'dateComponents' => [
                    'year' => 2019,
                    'month' => 3,
                    'day' => 16,
                    'hour' => 23,
                    'minute' => 59,
                    'second' => 60,
                ],
                'expectedDateTime' => new DateTime('2019-03-17 0:0:0')
            ],
            'day_overflow' => [
                'dateComponents' => [
                    'year' => 2019,
                    'month' => 3,
                    'day' => 32
                ],
                'expectedDateTime' => new DateTime('2019-04-01 0:0:0')
            ]
        ];
    }

    /**
     * @param array $dateComponents
     * @param DateTime $expectedDateTime
     * @dataProvider generateDateTimeDataProvider
     */
    public function testGenerateDateTime(array $dateComponents, DateTime $expectedDateTime)
    {
        $this->assertEquals(
            $expectedDateTime,
            CalculateDateTimeService::generateDateTime(
                $dateComponents['year'],
                $dateComponents['month'],
                $dateComponents['day'],
                $dateComponents['hour'] ?? 0,
                $dateComponents['minute'] ?? 0,
                $dateComponents['second'] ?? 0
            )
        );
    }

    /**
     * @return array
     */
    public function parseTcaStringDataProvider(): array
    {
        return [
            '+1 month' => [
                'tcaString' => '+1 month',
                'expectedDateTime' => new DateTime('2019-05-16 22:58:13')
            ],
            '+1 week' => [
                'tcaString' => '+1 week',
                'expectedDateTime' => new DateTime('2019-04-23 22:58:13')
            ],
            '+1 year' => [
                'tcaString' => '+1 year',
                'expectedDateTime' => new DateTime('2020-04-16 22:58:13')
            ],
            'empty' => [
                'tcaString' => '',
                'expectedDateTime' => new DateTime('2019-04-16 22:58:13')
            ],
            'cal:monthend' => [
                'tcaString' => 'cal:monthend',
                'expectedDateTime' => new DateTime('2019-04-30 23:59:59')
            ],
            'cal:monthstart' => [
                'tcaString' => 'cal:monthstart',
                'expectedDateTime' => new DateTime('2019-04-01 00:00:00')
            ],
            'cal:quarterend' => [
                'tcaString' => 'cal:quarterend',
                'expectedDateTime' => new DateTime('2019-06-30 23:59:59')
            ],
            'cal:quarterstart' => [
                'tcaString' => 'cal:quarterstart',
                'expectedDateTime' => new DateTime('2019-04-01 00:00:00')
            ],
            'cal:today' => [
                'tcaString' => 'cal:today',
                'expectedDateTime' => new DateTime('2019-04-16 00:00:00')
            ],
            'cal:tomorrow' => [
                'tcaString' => 'cal:tomorrow',
                'expectedDateTime' => new DateTime('2019-04-17 00:00:00')
            ],
            'cal:weekend' => [
                'tcaString' => 'cal:weekend',
                'expectedDateTime' => new DateTime('2019-04-21 23:59:59')
            ],
            'cal:weekstart' => [
                'tcaString' => 'cal:weekstart',
                'expectedDateTime' => new DateTime('2019-04-15 00:00:00')
            ],
            'cal:yearend' => [
                'tcaString' => 'cal:yearend',
                'expectedDateTime' => new DateTime('2019-12-31 23:59:59')
            ],
            'cal:yearstart' => [
                'tcaString' => 'cal:yearstart',
                'expectedDateTime' => new DateTime('2019-01-01 00:00:00')
            ],
            'cal:yesterday' => [
                'tcaString' => 'cal:yesterday',
                'expectedDateTime' => new DateTime('2019-04-15 00:00:00')
            ],
            'now' => [
                'tcaString' => 'now',
                'expectedDateTime' => new DateTime('2019-04-16 22:58:13')
            ],
        ];
    }

    /**
     * @param string $tcaString
     * @param DateTime $expectedDateTime
     * @dataProvider parseTcaStringDataProvider
     */
    public function testParseTcaString(string $tcaString, DateTime $expectedDateTime)
    {
        $dateTime = new DateTime('2019-04-16 22:58:13');
        $this->assertEquals(
            $expectedDateTime,
            CalculateDateTimeService::parseTcaString($tcaString, $dateTime)
        );
    }
}
