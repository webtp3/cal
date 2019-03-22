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
 * Tests for domains model Cal
 *
 */
class EventModelTest extends UnitTestCase
{

    /**
     * @var Cal
     */
    protected $eventModelInstance;

    /**
     * Set up framework
     *
     */
    protected function setUp()
    {
        // * @param string $serviceKey Service key, must be prefixed "tx_", "Tx_" or "user_"
        $this->eventModelInstance = new EventModel('','tx_cal_event');
    }

    /**
     * Test if tests work fine
     * @test
     */
    public function dummyMethod() {
        $this->assertTrue(true);
    }

    /**
     * Test if title can be set
     *
     * @test
     */
    public function titleCanBeSet()
    {
        $title = 'Cal title';
        $this->eventModelInstance->setTitle($title);
        $this->assertEquals($title, $this->eventModelInstance->getTitle());
    }


    /**
     * Test setTstamp
     *
     * @test
     */
    public function canSetTstamp()
    {
        $title = 'Cal title';
        $this->eventModelInstance->setTstamp($title);
        $this->assertEquals($title, $this->eventModelInstance->getTstamp());
    }
    /**
     * Test setSequence
     *
     * @test
     */
    public function canSetSequence()
    {
        //    * @param $sequence Array

        $title = [];
        $this->eventModelInstance->setSequence($title);
        $this->assertEquals($title, $this->eventModelInstance->getSequence());
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
     * Test setCreationDate
     *
     * @test
     */
    public function caSetCreationDate()
    {
        //    * @param $sequence Array
        $title = 'Cal title';
        $this->eventModelInstance->setCreationDate($title);
        $this->assertEquals($title, $this->eventModelInstance->getCreationDate());
    }
    /**
     * Test setLocation
     *
     * @test
     */
    public function CanSetLocation()
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
    public function setLocationPage()
    {
        $title = 'Cal title';
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