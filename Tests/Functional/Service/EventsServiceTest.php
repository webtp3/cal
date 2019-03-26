<?php

/*
 * This file is part of the web-tp3/cal.
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace TYPO3\CMS\Cal\Tests\Functional\Service;

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

use TYPO3\CMS\Cal\Service\EventsService;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class EventsServiceTest
 */
class EventsServiceTest extends \CAG\CagTests\Core\Functional\FunctionalTestCase
{


    /** @var \TYPO3\CMS\Extbase\Object\ObjectManagerInterface The object manager */
    protected $objectManager;

    /** @var  EventsService */
    protected $calService;

    protected $testExtensionsToLoad = ['typo3conf/ext/cal'];

    public function setUp()
    {
        parent::setUp();
        $success = true;
        $this->objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
        $this->importDataSet(__DIR__ . '/../Fixtures/tx_cal_calendar.xml');
        $this->calService = $this->objectManager->get(EventsService::class);

    }

    /**
     * Test if tests work fine
     * @test
     */
    public function dummyMethod() {
        $this->assertTrue(true);
    }

    /**
     * Test find external calendar uid or pid-list
     * @test
     *
     */
    public function canGetCalNumberTest()
    {

       $c =  $this->calService->getCalNumber();
        $this->assertEquals(1, $c);
    }
    /**
     * Test find external calendar uid or pid-list
     *
     * @test
     */
    public function canGetEventsFromTableTest()
    {

        /*getEventsFromTable(
                &$categories,
                $includeRecurring = false,
                $additionalWhere = '',
                $serviceKey = '',
                $addCategoryWhere = false,
                $onlyMeetingsWithoutStatus = false,
                $eventType = '0,1,2,3'
            )*/
        $c =  $this->calService->getEventsFromTable();
        $this->assertEquals(1, $c);
    }

    /**
     * Test find external calendar uid or pid-list
     *
     * @test
     */

    public function canFindAllTest()
    {
    /**
     * Looks for all external calendars on a certain pid-list
     *
     * @param string $pidList
     *            to search in
     * @return array array of array (array of $rows)
     */
        $c = $this->calService->findAll([1]);
        $this->assertEquals($c, 1);

    }

    /**
     * Test Updates an existing calendar events
     *
     * @test
     */
    public function camCreateEventTest()
    {

        $c = $this->calService->createEvent(1, 0);
        $this->assertEquals(1, $c);

    }




    /**
     * Test ScheduleUpdates an existing calendar events
     *
     * @test
     *
     */
    public function canGetTimeParsedTest()
    {


            $this->calService->getTimeParsed(time());

            $this->assertTrue(true);


    }
    /**
     * Test CreateSchedulerTask an existing calendar events
     *
     * @test
     *
     */

    public function canFindTest()
    {
        /**
         * @param $scheduler
         * @param $offset
         * @param $calendarUid
         * @throws RuntimeException
         */

        $c = $this->calService->find(1, 1);
        $this->assertEquals(1, $c);

    }


}
