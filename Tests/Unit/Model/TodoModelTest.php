<?php

/*
 * This file is part of the web-tp3/cal.
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace TYPO3\CMS\Cal\Tests\Unit\Model;

/**
 * This file is part of the "news" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */
use Nimut\TestingFramework\TestCase\UnitTestCase;
use TYPO3\CMS\Cal\Model\TodoModel;
use TYPO3\CMS\Cal\Model\EventModel;

/**
 * Tests for domains model News
 *
 */
class TodoModelTest extends UnitTestCase
{

    /**
     * @var TodoModel
     */
    protected $calModelInstance;

    /**
     * Set up framework
     *
     */
    protected function setUp()
    {
        // * @param string $serviceKey Service key, must be prefixed "tx_", "Tx_" or "user_"
        $evt =  new EventModel('NEW', 0, 'tx_cal_phpicalendar');
        $this->calModelInstance = new TodoModel($evt,'tx_cal_todo');
    }

    /**
     * Test if title can be set
     *
     * @test
     */
    public function titleCanBeSet()
    {
        $title = 'News title';
        $this->calModelInstance->setTitle($title);
        $this->assertEquals($title, $this->calModelInstance->getTitle());
    }

    /**
     * Test setTstamp
     *
     * @test
     */
    public function canSetTstamp()
    {
        $title = 'News title';
        $this->calModelInstance->setTstamp($title);
        $this->assertEquals($title, $this->calModelInstance->getTstamp());
    }
//    /**
//     * Test setSequence
//     *
//     * @test
//     */
//    public function canSetSequence()
//    {
//        //    * @param $sequence Array
//
//        $title = [];
//        $this->calModelInstance->setSequence($title);
//        $this->assertEquals($title, $this->calModelInstance->getSequence());
//    }
//    /**
//     * Test setOrganizer
//     *
//     * @test
//     */
//    public function canSetOrganizer()
//    {
//        //  * @param $organizer String
//        $title = 'News title';
//        $this->calModelInstance->setOrganizer($title);
//        $this->assertEquals($title, $this->calModelInstance->getOrganizer());
//    }

//    /**
//     * Test setCreationDate
//     *
//     * @test
//     */
//    public function caSetCreationDate()
//    {
//        //    * @param $sequence Array
//        $title = 'News title';
//        $this->calModelInstance->setCreationDate($title);
//        $this->assertEquals($title, $this->calModelInstance->getCreationDate());
//    }
//    /**
//     * Test setLocation
//     *
//     * @test
//     */
//    public function CanSetLocation()
//    {
//        $title = 'News title';
//        $this->calModelInstance->setLocation($title);
//        $this->assertEquals($title, $this->calModelInstance->getLocation());
//    }
//    /**
//     * Test setLocationLinkUrl
//     *
//     * @test
//     */
//    public function canSetLocationLinkUrl()
//    {
//        $url = 'https://www.test.de';
//        $this->calModelInstance->setLocationLinkUrl($url);
//        $this->assertEquals($url, $this->calModelInstance->getLocationLinkUrl());
//    }
//    /**
//     * Test setLocationPage
//     *
//     * @test
//     */
//    public function setLocationPage()
//    {
//        $page = 1;
//        $this->calModelInstance->setLocationPage($page);
//        $this->assertEquals($page, $this->calModelInstance->getLocationPage());
//    }
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
