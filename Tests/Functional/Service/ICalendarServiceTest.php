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
use TYPO3\CMS\Cal\Service\ICalendarService;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class ICalendarServiceTest
 */
class ICalendarServiceTest extends \CAG\CagTests\Core\Functional\FunctionalTestCase
{

    /** @var \TYPO3\CMS\Extbase\Object\ObjectManagerInterface The object manager */
    protected $objectManager;

    /** @var  ICalendarService */
    protected $calService;

    protected $testExtensionsToLoad = ['typo3conf/ext/cal'];

    public function setUp()
    {
        parent::setUp();
        $success = true;
        $this->objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
        $this->importDataSet(__DIR__ . '/../Fixtures/tx_cal_calendar.xml');
        $this->calService = $this->objectManager->get(ICalendarService::class);
    }

    /**
     * Test if tests work fine
     * @test
     */
    public function dummyMethod()
    {
        $this->assertTrue(true);
    }

    /**
     * Test find external calendar uid or pid-list
     * @test
     *
     */
    public function canFindByUidTest()
    {
        /*
     * @param int $uid
     *            to search for
     * @param string $pidList
     *            to search in
     * @return array array ($row)
    */

        $c =  $this->calService->find(1);
        $this->assertEquals(1, $c['uid']);
    }
    /**
     * Test find external calendar uid or pid-list
     *
     * @test
     */
    public function canFindByPidTest()
    {
        /*
      * @param int $uid
      *            to search for
      * @param string $pidList
      *            to search in
      * @return array array ($row)
     */

        $c =  $this->calService->find('', [1]);
        $this->assertEquals($c['uid'], 1);
    }

    /**
     * Test find external calendar uid or pid-list
     *
     * @test
     */
    public function canFindAll()
    {
        /**
         * Looks for all external calendars on a certain pid-list
         *
         * @param string $pidList
         *            to search in
         * @return array array of array (array of $rows)
         */
        $c = $this->calService->findAll([1]);
        $this->assertEquals($c['uid'], 1);
    }

    /**
     * Test Updates an existing calendar events
     *
     * @test
     */
    public function camUpdateEventsTest()
    {
        /**
//     * Updates an existing calendar
//     *
//     * @param int $uid
//     *            The calendar record uid
//     * @param int $pid
//     *            The page id
//     * @param string $urlString
//     *            The url to get the ics content from
//     * @param string $md5
//     *            The md5 hash of the current content
//     * @param int $cruser_id
//     *            The create user id
//     * @return string|bool False or the new md5 hash
//     */
        $urlString = 'https://calendar.google.com/calendar/ical/9tv213eho9k41t3knn1fmpoobo%40group.calendar.google.com/public/basic.ics';
        $md5 = '53d700544a7c4e38674d90329d140278';
        $cmd5 = $this->calService->updateEvents(1, 0, $urlString, $md5, 1);
//        $urls = GeneralUtility::trimExplode("\n", $urlString, 1);
//        $mD5Array = [];
//        $contentArray = [];
//
//        foreach ($urls as $key => $url) {
//            /* If the calendar has a URL, get a checksum on the contents */
//            if ($url != '') {
//                $contents = GeneralUtility::getUrl($url);
//
//                $mD5Array[$key] = md5($contents);
//            }
//        }
//
//        $newMD5 = md5(implode('', $mD5Array));
        //#todo real check md5 -> check return needed or can me array
        $this->assertEquals(1, preg_match('/^[a-f0-9]{32}$/', $cmd5));
    }

    /**
     * Test ScheduleUpdates an existing calendar events
     *
     * @test
     *
     */
    public function canScheduleUpdatesTest()
    {
        if (ExtensionManagementUtility::isLoaded('scheduler')) {
            /**
             * Schedules future updates using the scheduling engine.
             *
             * @param int $refreshInterval
             *            Frequency (in minutes) between calendar updates.
             * @param int $uid
             *            UID of the calendar to be updated.
             */
            $refreshInterval = 120;
            $uid = 1;
            $this->calService->scheduleUpdates($refreshInterval, $uid);
        } else {
            // ignore Test
            $this->assertTrue(true);
        }
    }
    /**
     * Test CreateSchedulerTask an existing calendar events
     *
     * @test
     *
     */
    public function canCreateSchedulerTaskTest()
    {
        /**
         * @param $scheduler
         * @param $offset
         * @param $calendarUid
         * @throws RuntimeException
         */
        if (ExtensionManagementUtility::isLoaded('scheduler')) {
            $offset = 1;

            $calendarUid = 1;

            $this->assertEquals(1, $this->calService->createSchedulerTask($scheduler, $offset, $calendarUid));
        } else {
            // ignore Test
            $this->assertTrue(true);
        }
    }

    /**
     * Test DeleteSchedulerTask an existing calendar events
     *
     * @test
     *
     */
    public function canDeleteSchedulerTaskTest()
    {
        if (ExtensionManagementUtility::isLoaded('scheduler')) {
            $calendarUid = 1;
            $this->assertEquals(1, $this->calService->deleteSchedulerTask($calendarUid));
        } else {
            // ignore Test
            $this->assertTrue(true);
        }
    }
    /**
     * Test deleteScheduledUpdatesFromCalendar an existing calendar events
     *
     * @test
     *
     */
    public function canDeleteScheduledUpdatesFromCalendarTest()
    {
        /**
         * @param int $uid
         *            The calendar uid
         */
        if (ExtensionManagementUtility::isLoaded('scheduler')) {
            $calendarUid = 1;

            $this->assertEquals(1, $this->calService->deleteScheduledUpdatesFromCalendar($calendarUid));
        } else {
            // ignore Test
            $this->assertTrue(true);
        }
    }
    //#todo further functional Tests if work

//    /**
//     * Test insertCalEventsIntoDB an existing calendar events
//     *
//     * @test
//     *
//     */
//    public function canInsertCalEventsIntoDBTest() {
//        /**
//         * @param array $iCalendarComponentArray
//         *            component array
//         * @param int $calId
//         *            The calendar uid to add the events/todos to
//         * @param string $pid
//         *            The save page id
//         * @param string $cruserId
//         *            The create user id
//         * @param number $isTemp
//         *            are the records only temporary (1 == true, 0 == false)
//         * @param string $deleteNotUsedCategories
//         *            Should not assigned categories be deleted
//         * @return array The inserted or updated event uids
//         * @throws RuntimeException
//         */
//        $calId = 2;
//        $venvent = new \TYPO3\CMS\Cal\Model\ICalendar\vevent();
//        $venvent->setAttribute('UID',2);
//        $c = $this->calService->insertCalEventsIntoDB(
//            $iCalendarComponentArray = [
//                $venvent,
//            ],
//            $calId,
//            $pid = '1',
//            $cruserId = '1',
//            $isTemp = 1,
//            $deleteNotUsedCategories = true
//        );
//        if (is_array($c)) {
//            $c = array_sum($c);
//        }
//        $this->assertGreaterThan($calId, $c);
//    }

//    /**
//     * Test if calendar can be updated
//     * (like cron would)
//     *
//     * @test
//     */
//    public function canupdateICalServiceTest()
//    {
//        $calid = 1;
//        $c = $this->calService->getServiceInfo();
//        $this->assertEquals($calid, $c);
//    }
}
