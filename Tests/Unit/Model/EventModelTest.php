<?php

namespace TYPO3\CMS\Cal\Tests\Unit\Model;

/**
 * This file is part of the "news" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */
use CAG\CagTests\Core\Unit\UnitTestCase;
use TYPO3\CMS\Cal\Model\EventModel;

/**
 * Tests for domains model EventModel
 *
 */
class EventModelTest extends UnitTestCase
{

    /**
     * @var EventModel
     */
    protected $eventModelInstance;

    /**
     * Set up framework
     *
     */
    protected function setUp()
    {
        /**
         * EventModel constructor.
         * @param $row
         * @param $isException
         * @param $serviceKey
         */
        $this->eventModelInstance = new EventModel('',0,'tx_cal_event');
    }

    /**
     * Test if tests work fine
     * @test
     */
    public function dummyMethod() {
        $this->assertTrue(true);
    }

    /**
     * Test if Teaser can be set
     *
     * @test
     */
    public function canTeaserBeSet()
    {
        $title = 'Cal title';
        $this->eventModelInstance->setTeaser($title);
        $this->assertEquals($title, $this->eventModelInstance->getTeaser());
    }


    /**
     * Test setTstamp
     *
     * @test
     */
    public function canSetTstamp()
    {
        $ts = time();
        $this->eventModelInstance->setTstamp($ts);
        $this->assertEquals($ts, $this->eventModelInstance->getTstamp());
    }

    /**
     * Test setOrganizer
     *
     * @test
     */
    public function canSetOrganizer()
    {
        //  * @param $organizer String
        $title = 'Cal title';
        $this->eventModelInstance->setOrganizer($title);
        $this->assertEquals($title, $this->eventModelInstance->getOrganizer());
    }


    /**
     * Test setLocation
     *
     * @test
     */
    public function canSetLocation()
    {
        $title = 'Cal title';
        $this->eventModelInstance->setLocation($title);
        $this->assertEquals($title, $this->eventModelInstance->getLocation());
    }
    /**
     * Test setLocationLinkUrl
     *
     * @test
     */
    public function canSetLocationLinkUrl()
    {
        $title = 'Cal title';
        $this->eventModelInstance->setLocationLinkUrl($title);
        $this->assertEquals($title, $this->eventModelInstance->getLocationLinkUrl());
    }
    /**
     * Test setLocationPage
     *
     * @test
     */
    public function cansetLocationPage()
    {
        $title = 1;
        $this->eventModelInstance->setLocationPage($title);
        $this->assertEquals($title, $this->eventModelInstance->getLocationPage());
    }
//
//    public function setStart($start)
//    public function setEnd($end)
//    public function setCalNumber($calnumber)
//    public function setCalendarUid($uid)
//    public function setCalName($calname)
//    public function setOverlap($overlap)
//    public function setTimezone($timezone)
//    public function setAllday($boolean)
//    public function setRecur($recur = [])
//    public function setUrl($url)
//    public function setVAlarmDescription($alarmdescription)
//    public function setIsClone($boolean)
//    public function setByMonth($bymonth)
//    public function setByDay($byday)
//    public function setByMonthday($bymonthday)
//    public function setByWeekDay($byweekday)
//    public function setByWeekNo($byweekno)
//    public function setByMinute($byminute)
//    public function setByHour($byhour)
//    public function setBySecond($bysecond)
//    public function setByYearDay($byyearday)
//    public function setBySetPos($bysetpos)
//    public function setWkst($wkst)
//    public function setInterval($interval)
//    public function setSummary($summary)
//    public function setClass($class)
//    public function setDisplayEnd($displayend)
//    public function setContent($t)
//    public function setDescription($description)
//    public function setUntil($until)
//    public function setFreq($freq)
//    public function setCount($count)
//    public function setRdate($rdate)
//    public function setRdateValues($rdateArray)
//    public function setRdateType($rdateType)
//    public function setSpansDay($spansday)
//    public function setCategories($categories)
//    public function setExceptionEvents($ex_events)
//    public function setEditable($editable)
//    public function setOrganizerId($id)
//    public function setOrganizerLinkUrl($id)
//    public function setOrganizerPage($pid)
//    public function setLocationId($id)
//    public function setExceptionSingleIds($idArray)
//    public function setExceptionGroupIds($idArray)
//    public function setHeaderStyle($style)
//    public function setBodyStyle($style)
//    public function setPage($t)
//    public function setExtUrl($t)
//    public function setEventType($t)
//    public function setSharedUsers($userIds)
//    public function setSharedGroups($groupIds)
//    public function setEventOwner($owner)
//    public function setAttendees(&$attendees)
//    public function setStatus($status)
//    public function setPriority($priority)
//    public function setCompleted($completed)
//    public function setDeviationDates($deviationDates)

}
