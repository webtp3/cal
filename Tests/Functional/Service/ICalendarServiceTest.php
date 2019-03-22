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
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

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
    public function dummyMethod() {
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
        $this->assertEquals(1, $c["uid"]);
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

        $c =  $this->calService->find('',[1]);
        $this->assertEquals($c["uid"], 1);
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
        $this->assertEquals($c["uid"], 1);

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
        $this->assertEquals(1,preg_match('/^[a-f0-9]{32}$/', $cmd5));

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
        }
        else{
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
        $this->calService->createSchedulerTask($scheduler, $offset, $calendarUid);
        } else{
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
            $this->calService->deleteSchedulerTask($calendarUid);
        }
        else{
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
    public function canDeleteScheduledUpdatesFromCalendarTest($uid)
    {
        /**
         * @param int $uid
         *            The calendar uid
         */
        if (ExtensionManagementUtility::isLoaded('scheduler')) {
            $calendarUid = 1;
            $this->calService->deleteScheduledUpdatesFromCalendar($calendarUid);
        }
        else{
            // ignore Test
            $this->assertTrue(true);

        }
    }
    //#todo further functional Tests if work

    //    /**
    //     * Returns a parsed ICalendar object of some ics content
    //     *
    //     * @param string $text
    //     *            The ics content
    //     * @return ICalendar
    //     * @throws RuntimeException
    //     */
    //    public function getiCalendarFromIcsFile($text): ICalendar
    //    {
    //        require_once(ICALENDAR_PATH);
    //        $iCalendar = new ICalendar();
    //        if (!$iCalendar->parsevCalendar($text)) {
    //            throw new RuntimeException('Could not parse vCalendar data ' . $text, 1451245373);
    //        }
    //        return $iCalendar;
    //    }
    //
    //    /**
    //     * @param $component
    //     * @return CalDate|null
    //     */
    //    private function getDtstart($component)
    //    {
    //        return $this->getDateValue($component, 'DTSTART');
    //    }
    //
    //    /**
    //     * @param $component
    //     * @return CalDate|null
    //     */
    //    private function getDtend($component)
    //    {
    //        return $this->getDateValue($component, 'DTEND');
    //    }
    //
    //    /**
    //     * @param $component
    //     * @return CalDate|null
    //     */
    //    private function getTstamp($component)
    //    {
    //        return $this->getDateValue($component, 'TSTAMP');
    //    }
    //
    //    /**
    //     * @param $component
    //     * @param $attribute
    //     * @return CalDate|null
    //     */
    //    private function getDateValue($component, $attribute)
    //    {
    //        if ($component->getAttribute($attribute)) {
    //            $value = $component->getAttribute($attribute);
    //            if (is_array($value)) {
    //                $dateTime = new CalDate($value['year'] . $value['month'] . $value['mday'] . '000000');
    //            } else {
    //                $dateTime = new CalDate($value);
    //            }
    //            $params = $component->getAttributeParameters($attribute);
    //            $timezone = $params['TZID'];
    //            if ($timezone) {
    //                $dateTime->convertTZbyID($timezone);
    //            }
    //            return $dateTime;
    //        }
    //        return null;
    //    }
    //
    //    /**
    //     * @param $component
    //     * @param $insertFields
    //     * @param $pid
    //     * @param $calId
    //     * @return array
    //     */
    //    private function setCategories($component, $insertFields, $pid, $calId): array
    //    {
    //        $categories = [];
    //        $categoryString = $component->getAttribute('CATEGORY');
    //        if ($categoryString == '') {
    //            if (is_array($component->getAttribute('CATEGORIES'))) {
    //                foreach ($component->getAttribute('CATEGORIES') as $cat) {
    //                    $categories[] = $cat;
    //                }
    //            } else {
    //                $categoryString = $component->getAttribute('CATEGORIES');
    //                $categories = GeneralUtility::trimExplode(',', $categoryString, 1);
    //            }
    //        } else {
    //            $categories = GeneralUtility::trimExplode(',', $categoryString, 1);
    //        }
    //
    //        $categoryUids = [];
    //        foreach ($categories as $category) {
    //            $category = trim($category);
    //            $categorySelect = '*';
    //            $categoryTable = 'sys_category';
    //            $categoryWhere = 'calendar_id = ' . intval($calId) . ' AND title =' . $GLOBALS['TYPO3_DB']->fullQuoteStr(
    //                $category,
    //                $categoryTable
    //                );
    //            $foundCategory = false;
    //            $result = $GLOBALS['TYPO3_DB']->exec_SELECTquery($categorySelect, $categoryTable, $categoryWhere);
    //            if ($result) {
    //                while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
    //                    $foundCategory = true;
    //                    $categoryUids[] = $row['uid'];
    //                }
    //                $GLOBALS['TYPO3_DB']->sql_free_result($result);
    //            }
    //
    //            if (!$foundCategory) {
    //                $result = $GLOBALS['TYPO3_DB']->exec_INSERTquery($categoryTable, [
    //                    'tstamp' => $insertFields['crdate'],
    //                    'crdate' => $insertFields['crdate'],
    //                    'pid' => $pid,
    //                    'title' => $category,
    //                    'calendar_id' => $calId
    //                ]);
    //                if (false === $result) {
    //                    throw new RuntimeException(
    //                        'Could not write ' . $categoryTable . ' record to database: ' . $GLOBALS['TYPO3_DB']->sql_error(),
    //                        1431458143
    //                    );
    //                }
    //                $categoryUids[] = $GLOBALS['TYPO3_DB']->sql_insert_id();
    //            }
    //        }
    //        return $categoryUids;
    //    }
    //
    //    /**
    //     * @param $component
    //     * @param $insertFields
    //     */
    //    private function setRecurrence($component, &$insertFields)
    //    {
    //        if ($component->getAttribute('RRULE')) {
    //            $rrule = $component->getAttribute('RRULE');
    //
    //            $this->insertRuleValues($rrule, $insertFields);
    //        }
    //
    //        if ($component->getAttribute('RDATE')) {
    //            $rdate = $component->getAttribute('RDATE');
    //            if (is_array($rdate)) {
    //                $insertFields['rdate'] = implode(',', $rdate);
    //            } else {
    //                $insertFields['rdate'] = $rdate;
    //            }
    //            if ($component->getAttributeParameters('RDATE')) {
    //                $parameterArray = $component->getAttributeParameters('RDATE');
    //                $keys = array_keys($parameterArray);
    //                $insertFields['rdate_type'] = strtolower($keys[0]);
    //            } else {
    //                $insertFields['rdate_type'] = 'date_time';
    //            }
    //        }
    //    }
    //
    //    /**
    //     * @param $component
    //     * @param $eventUid
    //     * @param $insertFields
    //     */
    //    private function setRecurrenceId($component, $eventUid, &$insertFields)
    //    {
    //        $recurrenceIdStart = new CalDate($component->getAttribute('RECURRENCE-ID'));
    //        $params = $component->getAttributeParameters('RECURRENCE-ID');
    //        $timezone = $params['TZID'];
    //        if ($timezone) {
    //            $recurrenceIdStart->convertTZbyID($timezone);
    //        }
    //
    //        $indexEntry = BackendUtilityReplacementUtility::getRawRecord(
    //            'tx_cal_index',
    //            'event_uid="' . $eventUid . '" AND start_datetime="' . $recurrenceIdStart->format('%Y%m%d%H%M%S') . '"'
    //        );
    //
    //        if ($indexEntry) {
    //            $table = 'tx_cal_event_deviation';
    //            $insertFields['parentid'] = $eventUid;
    //            $insertFields['orig_start_time'] = $recurrenceIdStart->getHour() * 3600 + $recurrenceIdStart->getMinute() * 60;
    //            $recurrenceIdStart->setHour(0);
    //            $recurrenceIdStart->setMinute(0);
    //            $recurrenceIdStart->setSecond(0);
    //            $insertFields['orig_start_date'] = $recurrenceIdStart->getTime();
    //            unset($insertFields['calendar_id']);
    //
    //            if ($indexEntry['event_deviation_uid'] > 0) {
    //                $GLOBALS['TYPO3_DB']->exec_UPDATEquery(
    //                    $table,
    //                    'uid=' . $indexEntry['event_deviation_uid'],
    //                    $insertFields
    //                );
    //                $eventDeviationUid = $indexEntry['event_deviation_uid'];
    //            } else {
    //                $result = $GLOBALS['TYPO3_DB']->exec_INSERTquery($table, $insertFields);
    //                if (false === $result) {
    //                    throw new RuntimeException(
    //                        'Could not write ' . $table . ' record to database: ' . $GLOBALS['TYPO3_DB']->sql_error(),
    //                        1431458145
    //                    );
    //                }
    //                $eventDeviationUid = $GLOBALS['TYPO3_DB']->sql_insert_id();
    //            }
    //            $GLOBALS['TYPO3_DB']->exec_UPDATEquery('tx_cal_index', 'uid=' . $indexEntry['uid'], [
    //                'event_deviation_uid' => $eventDeviationUid
    //            ]);
    //        }
    //    }
    //
    //    /**
    //     * @param $component
    //     * @param $eventUid
    //     * @param $pid
    //     * @param $cruserId
    //     */
    //    private function setExceptions($component, $eventUid, $pid, $cruserId)
    //    {
    //        /* Delete the old exception relations */
    //        $exceptionEventUidsToBeDeleted = [];
    //        $result = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
    //            'tx_cal_exception_event.uid',
    //            'tx_cal_exception_event,tx_cal_exception_event_mm',
    //            'tx_cal_exception_event.uid = tx_cal_exception_event_mm.uid_foreign AND tx_cal_exception_event_mm.uid_local=' . $eventUid
    //        );
    //        if ($result) {
    //            while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
    //                $exceptionEventUidsToBeDeleted[] = $row['uid'];
    //            }
    //            $GLOBALS['TYPO3_DB']->sql_free_result($result);
    //        }
    //        if (!empty($exceptionEventUidsToBeDeleted)) {
    //            $GLOBALS['TYPO3_DB']->exec_DELETEquery(
    //                'tx_cal_exception_event',
    //                'uid in (' . implode(',', $exceptionEventUidsToBeDeleted) . ')'
    //            );
    //        }
    //
    //        $exceptionEventGroupUidsToBeDeleted = [];
    //        $result = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
    //            'tx_cal_exception_event_group.uid',
    //            'tx_cal_exception_event_group,tx_cal_exception_event_group_mm',
    //            'tx_cal_exception_event_group.uid = tx_cal_exception_event_group_mm.uid_foreign AND tx_cal_exception_event_group_mm.uid_local=' . $eventUid
    //        );
    //        if ($result) {
    //            while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
    //                $exceptionEventGroupUidsToBeDeleted[] = $row['uid'];
    //            }
    //            $GLOBALS['TYPO3_DB']->sql_free_result($result);
    //        }
    //        if (!empty($exceptionEventGroupUidsToBeDeleted)) {
    //            $GLOBALS['TYPO3_DB']->exec_DELETEquery(
    //                'tx_cal_exception_event_group',
    //                'uid in (' . implode(',', $exceptionEventGroupUidsToBeDeleted) . ')'
    //            );
    //        }
    //
    //        $where = ' uid_local=' . $eventUid;
    //        $GLOBALS['TYPO3_DB']->exec_DELETEquery('tx_cal_exception_event_mm', $where);
    //        $GLOBALS['TYPO3_DB']->exec_DELETEquery('tx_cal_exception_event_group_mm', $where);
    //
    //        // Exceptions:
    //        if ($component->getAttribute('EXDATE')) {
    //            if (is_array($component->getAttribute('EXDATE'))) {
    //                foreach ($component->getAttribute('EXDATE') as $exceptionDescription) {
    //                    $this->createException($pid, $cruserId, $eventUid, $exceptionDescription);
    //                }
    //            } else {
    //                $this->createException($pid, $cruserId, $eventUid, $component->getAttribute('EXDATE'));
    //            }
    //        }
    //        if ($component->getAttribute('EXRULE')) {
    //            if (is_array($component->getAttribute('EXRULE'))) {
    //                foreach ($component->getAttribute('EXRULE') as $exceptionDescription) {
    //                    $this->createExceptionRule($pid, $cruserId, $eventUid, $exceptionDescription);
    //                }
    //            } else {
    //                $this->createExceptionRule($pid, $cruserId, $eventUid, $component->getAttribute('EXRULE'));
    //            }
    //        }
    //    }

//    /**
//     * @param $eventUid
//     * @param $pid
//     */
//    private function generateIndexEntries($eventUid, $pid)
//    {
//        $pageTSConf = BackendUtility::getPagesTSconfig($pid);
//        if ($pageTSConf['options.']['tx_cal_controller.']['pageIDForPlugin']) {
//            $pageIDForPlugin = $pageTSConf['options.']['tx_cal_controller.']['pageIDForPlugin'];
//        } else {
//            $pageIDForPlugin = $pid;
//        }
//        /** @var RecurrenceGenerator $rgc */
//        $rgc = GeneralUtility::makeInstance(RecurrenceGenerator::class, $pageIDForPlugin);
//        $rgc->generateIndexForUid($eventUid, 'tx_cal_event');
//    }
//
//    /**
//     * @param $eventUid
//     * @param $pid
//     * @param $insertFields
//     */
//    private function sendReminders($eventUid, $pid, $insertFields)
//    {
//        if ($this->conf['view.']['event.']['remind']) {
//            /* Schedule reminders for new and changed events */
//            $reminderService = &Functions::getReminderService();
//            $reminderService->scheduleReminder($eventUid);
//        }
//    }
//
//    /**
//     * @param $categoryUids
//     * @param $eventUid
//     */
//    private function connectCategories($categoryUids, $eventUid)
//    {
//        /* Delete the old category relations */
//        $where = ' uid_local=' . $eventUid;
//        $GLOBALS['TYPO3_DB']->exec_DELETEquery('tx_cal_event_category_mm', $where);
//        $i = 0;
//        foreach ($categoryUids as $uid) {
//            $result = $GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_cal_event_category_mm', [
//                'uid_local' => $eventUid,
//                'uid_foreign' => $uid,
//                'sorting' => $i++
//            ]);
//            if (false === $result) {
//                throw new RuntimeException(
//                    'Could not write tx_cal_event_category_mm record to database: ' . $GLOBALS['TYPO3_DB']->sql_error(),
//                    1431458146
//                );
//            }
//        }
//    }
//
//    /**
//     * @param $deleteNotUsedCategories
//     * @param $calId
//     * @param $insertedOrUpdatedCategoryUids
//     */
//    private function cleanupCategories($deleteNotUsedCategories, $calId, $insertedOrUpdatedCategoryUids)
//    {
//        if ($deleteNotUsedCategories) {
//            /* Delete the categories */
//            $where = ' calendar_id=' . $calId;
//            if (!empty($insertedOrUpdatedCategoryUids)) {
//                array_unique($insertedOrUpdatedCategoryUids);
//                $where .= ' AND uid NOT IN (' . implode(',', $insertedOrUpdatedCategoryUids) . ')';
//            }
//            $GLOBALS['TYPO3_DB']->exec_DELETEquery('sys_category', $where);
//        }
//    }
//
//    /**
//     * @param $eventRow
//     * @param $insertFields
//     * @return mixed
//     */
//    private function saveOrUpdate($eventRow, $insertFields)
//    {
//        $table = 'tx_cal_event';
//        if ($eventRow['uid']) {
//            $GLOBALS['TYPO3_DB']->exec_UPDATEquery($table, 'uid=' . $eventRow['uid'], $insertFields);
//            return $eventRow['uid'];
//        }
//        $result = $GLOBALS['TYPO3_DB']->exec_INSERTquery($table, $insertFields);
//        if (false === $result) {
//            throw new RuntimeException(
//                'Could not write ' . $table . ' record to database: ' . $GLOBALS['TYPO3_DB']->sql_error(),
//                1431458144
//            );
//        }
//        return $GLOBALS['TYPO3_DB']->sql_insert_id();
//    }
//
//    /**
//     * @param $component
//     * @param $insertFields
//     * @param $pid
//     * @param $eventUid
//     */
//    private function setAttachments($component, &$insertFields, $pid, $eventUid)
//    {
//        $this->clearAllImagesAndAttachments($eventUid);
//        $attachmentUrls = $component->getAttribute('ATTACH');
//        if (is_array($attachmentUrls)) {
//            foreach ($attachmentUrls as $attachmentUrl) {
//                $this->storeAttachment($attachmentUrl, $insertFields, $eventUid, $pid);
//            }
//        } elseif (is_string($attachmentUrls) && strlen(trim($attachmentUrls)) > 0) {
//            $this->storeAttachment($attachmentUrls, $insertFields, $eventUid, $pid);
//        }
//    }
//
//    /**
//     * @param $uid
//     */
//    public function clearAllImagesAndAttachments($uid)
//    {
//        $fileIndexRepository = GeneralUtility::makeInstance(FileIndexRepository::class);
//        $result = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
//            '*',
//            'sys_file_reference',
//            'tablenames="tx_cal_event" and uid_foreign =' . $uid
//        );
//        if ($result) {
//            while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
//                if ($GLOBALS['TYPO3_DB']->exec_SELECTcountRows(
//                    '*',
//                    'sys_file_reference',
//                    'uid_local=' . $row['uid_local']
//                    ) == 1) {
//                    $fileIndexRepository->remove($row['uid_local']);
//                }
//            }
//        }
//        $GLOBALS['TYPO3_DB']->exec_DELETEquery(
//            'sys_file_reference',
//            'tablenames="tx_cal_event" and uid_foreign =' . $uid
//        );
//    }
//
//    /**
//     * @param $externalUrl
//     * @param $insertFields
//     * @param $eventUid
//     * @param $pid
//     */
//    private function storeAttachment($externalUrl, $insertFields, $eventUid, $pid)
//    {
//        if (!$this->fileFunc) {
//            $this->fileFunc = new BasicFileUtility();
//        }
//
//        $qParts = parse_url($externalUrl);
//        $fI = pathinfo($qParts['path']);
//        $ext = strtolower($fI['extension']);
//
//        $report = [];
//        GeneralUtility::getUrl($externalUrl, 1, false, $report);
//        $content = GeneralUtility::getUrl($externalUrl);
//
//        $imageExt = explode(',', $GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext']);
//        $type = 'attachment';
//        if (false !== stripos($report['content_type'], 'image') || in_array($ext, $imageExt)) {
//            $type = 'image';
//        }
//
//        $allowedExt = [];
//        $denyExt = [];
//        if ($type == 'image') {
//            $allowedExt = explode(',', $GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext']);
//        } elseif ($type == 'attachment') {
//            $allowedExt = ['*'];
//            $denyExt = explode(',', PHP_EXTENSIONS_DEFAULT);
//        }
//
//        if ((string)$content === '' || (!empty($denyExt) && in_array(
//            $ext,
//            $denyExt
//                )) || (!empty($allowedExt) && !in_array($ext, $allowedExt))) {
//            return;
//        }
//
//        $theDestFile = $this->fileFunc->getUniqueName(
//            $this->fileFunc->cleanFileName($fI['basename']),
//            PATH_site . 'typo3temp/'
//        );
//        GeneralUtility::writeFile($theDestFile, $content);
//        $insertFields[$type] = '__NEW__' . basename($theDestFile);
//        $insertFields['pid'] = $pid;
//        if (!isset($this->controller->piVars)) {
//            if (!isset($this->controller)) {
//                $this->controller = GeneralUtility::makeInstance(Controller::class);
//            }
//            $this->controller->piVars = [];
//        }
//
//        $tempType = $this->controller->piVars[$type];
//        $this->controller->piVars[$type] = [];
//        $this->checkOnTempFile($type, $insertFields, 'tx_cal_event', $eventUid);
//        $this->controller->piVars[$type] = $tempType;
//    }
//
    /**
     * Test insertCalEventsIntoDB an existing calendar events
     *
     * @test
     *
     */
    public function canInsertCalEventsIntoDBTest() {
        /**
         * @param array $iCalendarComponentArray
         *            component array
         * @param int $calId
         *            The calendar uid to add the events/todos to
         * @param string $pid
         *            The save page id
         * @param string $cruserId
         *            The create user id
         * @param number $isTemp
         *            are the records only temporary (1 == true, 0 == false)
         * @param string $deleteNotUsedCategories
         *            Should not assigned categories be deleted
         * @return array The inserted or updated event uids
         * @throws RuntimeException
         */
        $calId = 1;
        $this->calService->update(1);
        $c = $this->calService->insertCalEventsIntoDB(
            $iCalendarComponentArray = [],
            $calId,
            $pid = '1',
            $cruserId = '1',
            $isTemp = 1,
            $deleteNotUsedCategories = true
        );
        $this->assertEquals($calId, $c);

    }
//
//    /**
//     * @param $rule
//     * @param array $insertFields
//     */
//    private function insertRuleValues($rule, &$insertFields)
//    {
//        $data = str_replace('RRULE:', '', $rule);
//        $rule = explode(';', $data);
//        foreach ($rule as $recur) {
//            preg_match('/(.*)=(.*)/', $recur, $regs);
//            $rrule_array[$regs[1]] = $regs[2];
//        }
//        foreach ($rrule_array as $key => $val) {
//            switch ($key) {
//                case 'FREQ':
//                    switch ($val) {
//                        case 'YEARLY':
//                            $freq_type = 'year';
//                            break;
//                        case 'MONTHLY':
//                            $freq_type = 'month';
//                            break;
//                        case 'WEEKLY':
//                            $freq_type = 'week';
//                            break;
//                        case 'DAILY':
//                            $freq_type = 'day';
//                            break;
//                        case 'HOURLY':
//                            $freq_type = 'hour';
//                            break;
//                        case 'MINUTELY':
//                            $freq_type = 'minute';
//                            break;
//                        case 'SECONDLY':
//                            $freq_type = 'second';
//                            break;
//                    }
//                    $insertFields['freq'] = strtolower($freq_type);
//                    break;
//                case 'COUNT':
//                    $insertFields['cnt'] = $val;
//                    break;
//                case 'UNTIL':
//                    $until = str_replace('T', '', $val);
//                    $until = str_replace('Z', '', $until);
//                    if (strlen($until) == 8) {
//                        $until = $until . '235959';
//                    }
//                    preg_match('/([0-9]{4})([0-9]{2})([0-9]{2})([0-9]{2})([0-9]{2})/', $until, $regs);
//                    $insertFields['until'] = $regs[1] . $regs[2] . $regs[3];
//                    break;
//                case 'INTERVAL':
//                    $insertFields['intrval'] = $val;
//                    break;
//                case 'BYDAY':
//                    $insertFields['byday'] = strtolower($val);
//                    break;
//                case 'BYMONTHDAY':
//                    $insertFields['bymonthday'] = strtolower($val);
//                    break;
//                case 'BYMONTH':
//                    $insertFields['bymonth'] = strtolower($val);
//                    break;
//            }
//        }
//    }
//
//    /**
//     * @param $pid
//     * @param $cruserId
//     * @param $eventUid
//     * @param $exceptionDescription
//     * @throws RuntimeException
//     */
//    private function createException($pid, $cruserId, $eventUid, $exceptionDescription)
//    {
//        $exceptionDate = new CalDate($exceptionDescription);
//
//        $insertFields = [];
//        $insertFields['tstamp'] = time();
//        $insertFields['crdate'] = time();
//        $insertFields['pid'] = $pid;
//        $insertFields['cruser_id'] = $cruserId;
//        $insertFields['title'] = 'Exception for event ' . $eventUid . ' on ' . $exceptionDate->format('%Y%m%d');
//        $insertFields['start_date'] = $exceptionDate->format('%Y%m%d');
//
//        $result = $GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_cal_exception_event', $insertFields);
//        if (false === $result) {
//            throw new RuntimeException(
//                'Could not write tx_cal_exception_event record to database: ' . $GLOBALS['TYPO3_DB']->sql_error(),
//                1431458147
//            );
//        }
//        $result = $GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_cal_exception_event_mm', [
//            'tablenames' => 'tx_cal_exception_event',
//            'uid_local' => $eventUid,
//            'uid_foreign' => $GLOBALS['TYPO3_DB']->sql_insert_id()
//        ]);
//        if (false === $result) {
//            throw new RuntimeException(
//                'Could not write tx_cal_exception_event_mm record to database: ' . $GLOBALS['TYPO3_DB']->sql_error(),
//                1431458148
//            );
//        }
//    }
//
//    /**
//     * @param $pid
//     * @param $cruserId
//     * @param $eventUid
//     * @param $exceptionRuleDescription
//     * @throws RuntimeException
//     */
//    private function createExceptionRule($pid, $cruserId, $eventUid, $exceptionRuleDescription)
//    {
//        $event = BackendUtilityReplacementUtility::getRawRecord('tx_cal_event', 'uid=' . $eventUid);
//
//        $insertFields = [];
//        $insertFields['tstamp'] = time();
//        $insertFields['crdate'] = time();
//        $insertFields['pid'] = $pid;
//        $insertFields['cruser_id'] = $cruserId;
//        $insertFields['title'] = 'Exception rule for event ' . $eventUid;
//        $insertFields['start_date'] = $event['start_date'];
//        $this->insertRuleValues($exceptionRuleDescription, $insertFields);
//
//        $result = $GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_cal_exception_event', $insertFields);
//        if (false === $result) {
//            throw new RuntimeException(
//                'Could not write tx_cal_exception_event_mm record to database: ' . $GLOBALS['TYPO3_DB']->sql_error(),
//                1431458149
//            );
//        }
//        $result = $GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_cal_exception_event_mm', [
//            'tablenames' => 'tx_cal_exception_event',
//            'uid_local' => $eventUid,
//            'uid_foreign' => $GLOBALS['TYPO3_DB']->sql_insert_id()
//        ]);
//        if (false === $result) {
//            throw new RuntimeException(
//                'Could not write tx_cal_exception_event_mm record to database: ' . $GLOBALS['TYPO3_DB']->sql_error(),
//                1431458150
//            );
//        }
//    }

    /**
     * Test if calendar can be updated
     * (like cron would)
     *
     * @test
     */
    public function canupdateICalServiceTest()
    {
        $calid = 1;
        $this->calService->update(1);
        $c = $this->calService->getServiceInfo();
        $this->assertEquals($calid, $c);
    }
}
