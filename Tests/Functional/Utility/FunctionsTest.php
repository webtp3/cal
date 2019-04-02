<?php
declare(strict_types = 1);

namespace TYPO3\CMS\Cal\Tests\Functional\Utility;

use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;
use TYPO3\CMS\Cal\Utility\Functions;

/**
 * Class FunctionsTest
 */
class FunctionsTest extends FunctionalTestCase
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
