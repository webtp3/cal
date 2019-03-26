<?php

/*
 * This file is part of the web-tp3/cal.
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace TYPO3\CMS\Cal\Tests\Functional\Controller;

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
use TYPO3\CMS\Cal\Controller\Api;


/**
 * API for calendar base (cal)
 */
class ApiControllerTest extends \CAG\CagTests\Core\Functional\FunctionalTestCase
{


    /** @var \TYPO3\CMS\Extbase\Object\ObjectManagerInterface The object manager */
    protected $objectManager;

    /** @var  Api */
    protected $apiController;

    protected $testExtensionsToLoad = ['typo3conf/ext/cal'];

    public function setUp()
    {
        parent::setUp();
        $success = true;
        $this->objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
        $this->importDataSet(__DIR__ . '/../Fixtures/pages.xml');
        $this->importDataSet(__DIR__ . '/../Fixtures/tt_content.xml');
        $this->importDataSet(__DIR__ . '/../Fixtures/tx_cal_calendar.xml');
        $this->importDataSet(__DIR__ . '/../Fixtures/tx_cal_event.xml');
        $this->apiController = $this->objectManager->get(Api::class);
        //$this->setUpFrontendRootPage(1, ['EXT:cal/Configuration/TypoScript/']);


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
    public function canFindByUidTest(): array
    {
        /*
      * @param int $uid
      *            to search for
      * @param string $pidList
      *            to search in
      * @return array array ($row)
     */

        $c =  $this->apiController->findEvent(1,'tx_cal_phpicalendar','1');
        $this->assertEquals($c["uid"], 1);
    }


    /**
     * Test find tx_cal_api_without event by pid
     * @test
     *
     */
    public function canFindWithoutApi()
    {
        $c =  $this->apiController->tx_cal_api_without(1);
        $this->assertEquals($c["uid"], 1);

    }

    /**
     * Test find canCreateFindEvent event by pid
     * @test
     *
     */
    public function canCreateFindEvent()
    {
        $evt = new \TYPO3\CMS\Cal\Model\EventModel('',0,'tx_cal_phpicalendar');
        $evt->setPid(1);
        $evt->setUid(111);
        $type = "tx_cal_phpicalendar";
        $evt->setType($type);
        $title = "testtype event";
        $evt->setTitle($title);
        $this->apiController->saveEvent('','tx_cal_phpicalendar',1);
        $this->assertEquals($evt, $this->apiController->findEvent(111, $type, [1]));

    }

//    /**
//     * @param $uid
//     * @param $type
//     * @param string $pidList
//     * @return mixed
//     */
//    public function saveEvent($uid, $type, $pidList = '')
//    {
//        return $this->modelObj->saveEvent($uid, $type, $pidList);
//    }
//
//    /**
//     * @param $uid
//     * @param $type
//     */
//    public function removeEvent($uid, $type)
//    {
//        return $this->modelObj->removeEvent($uid, $type);
//    }
//
//    /**
//     * @param $uid
//     * @param $type
//     * @param string $pidList
//     * @return mixed
//     */
//    public function saveExceptionEvent($uid, $type, $pidList = '')
//    {
//        return $this->modelObj->saveExceptionEvent($uid, $type, $pidList);
//    }
//
//    /**
//     * @param $uid
//     * @param $type
//     * @param string $pidList
//     * @return Location
//     */
//    public function findLocation($uid, $type, $pidList = ''): Location
//    {
//        return $this->modelObj->findLocation($uid, $type, $pidList);
//    }
//
//    /**
//     * @param string $type
//     * @param string $pidList
//     * @return array
//     */
//    public function findAllLocations($type = '', $pidList = ''): array
//    {
//        return $this->modelObj->findAllLocations($type, $pidList);
//    }
//
//    /**
//     * @param $uid
//     * @param $type
//     * @param string $pidList
//     * @return mixed
//     */
//    public function saveLocation($uid, $type, $pidList = '')
//    {
//        return $this->modelObj->saveLocation($uid, $type, $pidList);
//    }
//
//    /**
//     * @param $uid
//     * @param $type
//     */
//    public function removeLocation($uid, $type)
//    {
//        return $this->modelObj->removeLocation($uid, $type);
//    }
//
//    /**
//     * @param $uid
//     * @param $type
//     * @param string $pidList
//     * @return Organizer
//     */
//    public function findOrganizer($uid, $type, $pidList = ''): Organizer
//    {
//        return $this->modelObj->findOrganizer($uid, $type, $pidList);
//    }
//
//    /**
//     * @param $uid
//     * @param $type
//     * @param string $pidList
//     * @return array
//     */
//    public function findCalendar($uid, $type, $pidList = ''): array
//    {
//        return $this->modelObj->findCalendar($uid, $type, $pidList);
//    }
//
//    /**
//     * @param string $type
//     * @param string $pidList
//     * @return array
//     */
//    public function findAllCalendar($type = '', $pidList = ''): array
//    {
//        return $this->modelObj->findAllCalendar($type, $pidList);
//    }
//
//    /**
//     * @param string $type
//     * @param string $pidList
//     * @return mixed
//     */
//    public function findAllOrganizer($type = '', $pidList = '')
//    {
//        return $this->modelObj->findAllOrganizer($type, $pidList);
//    }
//
//    /**
//     * @param $uid
//     * @param $type
//     * @param string $pidList
//     * @return mixed
//     */
//    public function saveOrganizer($uid, $type, $pidList = '')
//    {
//        return $this->modelObj->saveOrganizer($uid, $type, $pidList);
//    }
//
//    /**
//     * @param $uid
//     * @param $type
//     */
//    public function removeOrganizer($uid, $type)
//    {
//        return $this->modelObj->removeOrganizer($uid, $type);
//    }
//
//    /**
//     * @param $uid
//     * @param $type
//     * @param string $pidList
//     * @return mixed
//     */
//    public function saveCalendar($uid, $type, $pidList = '')
//    {
//        return $this->modelObj->saveCalendar($uid, $type, $pidList);
//    }
//
//    /**
//     * @param $uid
//     * @param $type
//     */
//    public function removeCalendar($uid, $type)
//    {
//        return $this->modelObj->removeCalendar($uid, $type);
//    }
//
//    /**
//     * @param $uid
//     * @param $type
//     * @param string $pidList
//     * @return mixed
//     */
//    public function saveCategory($uid, $type, $pidList = '')
//    {
//        return $this->modelObj->saveCategory($uid, $type, $pidList);
//    }
//
//    /**
//     * @param $uid
//     * @param $type
//     */
//    public function removeCategory($uid, $type)
//    {
//        return $this->modelObj->removeCategory($uid, $type);
//    }
//
//    /**
//     * @param $startTimestamp
//     * @param $endTimestamp
//     * @param string $type
//     * @param string $pidList
//     * @return array
//     */
//    public function findEventsWithin($startTimestamp, $endTimestamp, $type = '', $pidList = ''): array
//    {
//        return $this->modelObj->findAllWithin('cal_event_model', $startTimestamp, $endTimestamp, $type, 'event', $pidList);
//    }
//
//    /**
//     * @param $timestamp
//     * @param string $type
//     * @param string $pidList
//     * @return array
//     */
//    public function findEventsForDay($timestamp, $type = '', $pidList = ''): array
//    {
//        return $this->modelObj->findEventsForDay($timestamp, $type, $pidList);
//    }
//
//    /**
//     * @param $timestamp
//     * @param string $type
//     * @param string $pidList
//     * @return array
//     */
//    public function findEventsForWeek($timestamp, $type = '', $pidList = ''): array
//    {
//        return $this->modelObj->findEventsForWeek($timestamp, $type, $pidList);
//    }
//
//    /**
//     * @param $timestamp
//     * @param string $type
//     * @param string $pidList
//     * @return array
//     */
//    public function findEventsForMonth($timestamp, $type = '', $pidList = ''): array
//    {
//        return $this->modelObj->findEventsForMonth($timestamp, $type, $pidList);
//    }
//
//    /**
//     * @param $timestamp
//     * @param string $type
//     * @param string $pidList
//     * @return array
//     */
//    public function findEventsForYear($timestamp, $type = '', $pidList = ''): array
//    {
//        return $this->modelObj->findEventsForYear($timestamp, $type, $pidList);
//    }
//
//    /**
//     * @param $timestamp
//     * @param string $type
//     * @param string $pidList
//     * @return array
//     */
//    public function findEventsForList($timestamp, $type = '', $pidList = ''): array
//    {
//        return $this->modelObj->findEventsForList($timestamp, $type, $pidList);
//    }
//
//    /**
//     * @param string $type
//     * @param string $pidList
//     * @return array
//     */
//    public function findCategoriesForList($type = '', $pidList = ''): array
//    {
//        return $this->modelObj->findCategoriesForList($type, $pidList);
//    }
//
//    /**
//     * @param $timestamp
//     * @param $type
//     * @param $pidList
//     * @return array
//     */
//    public function findEventsForIcs($timestamp, $type, $pidList): array
//    {
//        return $this->modelObj->findEventsForIcs($type, $pidList);
//    }
//
//    /**
//     * @param string $type
//     * @param string $pidList
//     * @return array
//     */
//    public function searchEvents($type = '', $pidList = ''): array
//    {
//        return $this->modelObj->searchEvents($type, $pidList);
//    }
//
//    /**
//     * @param string $type
//     * @param string $pidList
//     * @return array
//     */
//    public function searchLocation($type = '', $pidList = ''): array
//    {
//        return $this->modelObj->searchLocation($type, $pidList);
//    }
//
//    /**
//     * @param string $type
//     * @param string $pidList
//     * @return array
//     */
//    public function searchOrganizer($type = '', $pidList = ''): array
//    {
//        return $this->modelObj->searchOrganizer($type, $pidList);
//    }
//
//    /**
//     * @param $master_array
//     * @param $getdate
//     * @param bool $sendHeaders
//     * @return string
//     */
//    public function drawIcs($master_array, $getdate, $sendHeaders = true): string
//    {
//        return $this->viewObj->drawIcs($master_array, $getdate, $sendHeaders);
//    }
//
//    /**
//     * process the Typoscript array to final output
//     * Note: Part of the code is taken from tsobj written by Jean-David Gadina (macmade@gadlab.net)
//     *
//     * @param string The Typoscrypt Object to process
//     * @param string The content between the tags to be merged with the TS Objected
//     * @return string Processed ooutput of the TS
//     * TODO: remove me!
//     */
//    private function __processTSObject($tsObjPath, $tag_content)
//    {
//        // Check for a non empty value
//        if ($tsObjPath) {
//
//            // Get complete TS template
//            $tsObj = &$this->__TSTemplate->setup;
//
//            // Get TS object hierarchy in template
//            $tmplPath = explode('.', $tsObjPath);
//            // Process TS object hierarchy
//            $error = 0;
//            for ($i = 0, $iMax = count($tmplPath); $i < $iMax; $i++) {
//
//                // Try to get content type
//                $cType = $tsObj [$tmplPath [$i]];
//
//                // Try to get TS object configuration array
//                $tsNewObj = $tsObj [$tmplPath [$i] . '.'];
//
//                // Merge Configuration found in the tags with typoscript config
//                if (count($tag_content)) {
//                    $tsNewObj = $this->array_merge_recursive2($tsNewObj, $tag_content [$tsObjPath . '.']);
//                }
//
//                // Check object
//                if (!$cType && !$tsNewObj) {
//                    // Object doesn't exist
//                    $error = 1;
//                    break;
//                }
//            }
//
//            // Check object and content type
//            if ($error) {
//
//                // Object not found
//                return '<strong>Not Found</strong> (' . $tsObjPath . ')';
//            }
//            if ($this->cTypes [$cType]) {
//                // Render Object
//                $code = $this->__local_cObj->cObjGetSingle($cType, $tsNewObj);
//            } else {
//
//                // Invalid content type
//                return '<strong>errors.invalid</strong> (' . $cType . ')';
//            }
//
//            // Return object
//            return $code;
//        }
//    }
//
//    /**
//     * Returns current PageRenderer
//     *
//     * @return PageRenderer
//     */
//    protected function getPageRenderer(): PageRenderer
//    {
//        return GeneralUtility::makeInstance(PageRenderer::class);
//    }
}
