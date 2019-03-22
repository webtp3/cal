<?php

namespace TYPO3\CMS\Cal\Tests\Unit\Model;

/**
 * This file is part of the "news" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */
use CAG\CagTests\Core\Unit\UnitTestCase;
use TYPO3\CMS\Cal\Model\CalendarModel;

/**
 * Tests for domains model Cal
 *
 */
class CalendarModelTest extends UnitTestCase
{

    /**
     * @var Cal
     */
    protected $calModelInstance;

    /**
     * Set up framework
     *
     */
    protected function setUp()
    {
        // * @param string $serviceKey Service key, must be prefixed "tx_", "Tx_" or "user_"
        $this->calModelInstance = new CalendarModel('','tx_cal_calendar');
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
        $this->calModelInstance->setTitle($title);
        $this->assertEquals($title, $this->calModelInstance->getTitle());
    }



    /**
     * Test if ActivateFreeAndBusy can be set
     *
     * @test
     */
    public function canSetActivateFreeAndBusy()
    {
        $this->calModelInstance->setActivateFreeAndBusy(1);
        $this->assertEquals(1, $this->calModelInstance->getActivateFreeAndBusy());
    }

    /**
     * Test if CalendarType can be set
     *
     * @test
     */
    public function canSetCalendarType()
    {
        $this->calModelInstance->setCalendarType(2);
        $this->assertEquals(2, $this->calModelInstance->getCalendarType());
    }

    /**
     * Test if ExtUrl can be set
     *
     * @test
     */
    public function canSetExtUrl()
    {
        $url = 'https://www.typotest.de';
        $this->calModelInstance->setExtUrl($url);
        $this->assertEquals($url, $this->calModelInstance->getExtUrl());
    }



    /**
     * Test if IcsFile can be set
     *
     * @test
     */

    public function canSetIcsFile()
    {
        $url = 'https://www.typotest.de';
        $this->calModelInstance->setIcsFile($url);
        $this->assertEquals($url, $this->calModelInstance->getIcsFile());
    }


    //#todo model testing cal

//    /**
//     * @param $icsFile
//     */
//    public function setIcsFile($icsFile)
//    {
//        $this->icsFile = $icsFile;
//    }
//
//    /**
//     * @return int
//     */
//    public function getRefresh(): int
//    {
//        return $this->refresh;
//    }
//
//    /**
//     * @param $refresh
//     */
//    public function setRefresh($refresh)
//    {
//        $this->refresh = $refresh;
//    }
//
//    /**
//     * @return string
//     */
//    public function getMD5(): string
//    {
//        return $this->md5;
//    }
//
//    /**
//     * @param $md5
//     */
//    public function setMD5($md5)
//    {
//        $this->md5 = $md5;
//    }
//
//    /**
//     * @param $table
//     * @param int $index
//     * @return mixed
//     */
//    public function getFreeAndBusyUser($table, $index = 0)
//    {
//        if ($index > 0 && count($this->freeAndBusyUser[$table]) > $index) {
//            return $this->freeAndBusyUser[$table][$index];
//        }
//        return $this->freeAndBusyUser[$table];
//    }
//
//    /**
//     * @param $table
//     * @param $freeAndBusyUser
//     */
//    public function setFreeAndBusyUser($table, $freeAndBusyUser)
//    {
//        $this->freeAndBusyUser[$table] = $freeAndBusyUser;
//    }
//
//    /**
//     * @param $table
//     * @param $freeAndBusyUser
//     */
//    public function addFreeAndBusyUser($table, $freeAndBusyUser)
//    {
//        $this->freeAndBusyUser[$table][] = $freeAndBusyUser;
//    }

    /**
     * @param $table
     * @param int $index
     * @return mixed
     */
    public function getOwner($table, $index = 0)
    {
        if ($index > 0 && count($this->owner[$table]) > $index) {
            return $this->owner[$table][$index];
        }
        return $this->owner[$table];
    }

    /**
     * @param $table
     * @param $owner
     */
    public function setOwner($table, $owner)
    {
        $this->owner[$table] = $owner;
        $this->isPublic = false;
    }

    /**
     * @param $table
     * @param $owner
     */
    public function addOwner($table, $owner)
    {
        $this->owner[$table][] = $owner;
        $this->isPublic = false;
    }

    /**
     * @param $template
     * @param $sims
     * @param $rems
     * @param $view
     */
    public function getExtUrlMarker(& $template, & $sims, & $rems, $view)
    {
        $this->initLocalCObject();
        $sims['###EXTURL###'] = $this->local_cObj->stdWrap(
            $this->getExtUrl(),
            $this->conf['view.'][$view . '.']['exturl_stdWrap.']
        );
    }

    /**
     * @param $template
     * @param $sims
     * @param $rems
     * @param $view
     */
    public function getIcsFileMarker(& $template, & $sims, & $rems, $view)
    {
        $this->initLocalCObject();
        $sims['###ICSFILE###'] = $this->local_cObj->stdWrap(
            $this->getIcsFile(),
            $this->conf['view.'][$view . '.']['icsfile_stdWrap.']
        );
    }

    /**
     * @param $template
     * @param $sims
     * @param $rems
     * @param $view
     */
    public function getRefreshMarker(& $template, & $sims, & $rems, $view)
    {
        $this->initLocalCObject();
        $sims['###REFRESH###'] = $this->local_cObj->stdWrap(
            $this->getRefresh(),
            $this->conf['view.'][$this->conf['view'] . '.']['refresh_stdWrap.']
        );
    }

    /**
     * @param $template
     * @param $sims
     * @param $rems
     * @param $view
     */
    public function getTitleMarker(& $template, & $sims, & $rems, $view)
    {
        $this->initLocalCObject();
        $sims['###TITLE###'] = $this->local_cObj->stdWrap(
            $this->getTitle(),
            $this->conf['view.'][$this->conf['view'] . '.']['title_stdWrap.']
        );
    }

    /**
     * @return bool
     */
    public function isPublic(): bool
    {
        return $this->isPublic;
    }

    /**
     * @param string $feUserUid
     * @param array $feGroupsArray
     * @return bool
     */
    public function isUserAllowedToEdit($feUserUid = '', $feGroupsArray = []): bool
    {
        $rightsObj = &Registry::Registry('basic', 'rightscontroller');
        if (!$rightsObj->isViewEnabled('edit_calendar')) {
            return false;
        }
        if ($rightsObj->isCalAdmin()) {
            return true;
        }

        if ($feUserUid === '') {
            $feUserUid = $rightsObj->getUserId();
        }
        if (empty($feGroupsArray)) {
            $feGroupsArray = $rightsObj->getUserGroups();
        }
        $isCalendarOwner = $this->isCalendarOwner($feUserUid, $feGroupsArray);

        $isAllowedToEditCalendars = $rightsObj->isAllowedToEditCalendar();
        $isAllowedToEditOwnCalendarsOnly = $rightsObj->isAllowedToEditOnlyOwnCalendar();
        $isAllowedToEditPublicCalendars = $rightsObj->isAllowedToEditPublicCalendar();

        if ($isAllowedToEditOwnCalendarsOnly) {
            return $isCalendarOwner;
        }
        return $isAllowedToEditCalendars && ($isCalendarOwner || ($this->isPublic && $isAllowedToEditPublicCalendars));
    }

    /**
     * @param string $feUserUid
     * @param array $feGroupsArray
     * @return bool
     */
    public function isUserAllowedToDelete($feUserUid = '', $feGroupsArray = []): bool
    {
        $rightsObj = &Registry::Registry('basic', 'rightscontroller');
        if (!$rightsObj->isViewEnabled('delete_calendar')) {
            return false;
        }
        if ($rightsObj->isCalAdmin()) {
            return true;
        }

        if ($feUserUid === '') {
            $feUserUid = $rightsObj->getUserId();
        }
        if (empty($feGroupsArray)) {
            $feGroupsArray = $rightsObj->getUserGroups();
        }
        $isCalendarOwner = $this->isCalendarOwner($feUserUid, $feGroupsArray);

        $isAllowedToDeleteCalendars = $rightsObj->isAllowedToDeleteCalendar();
        $isAllowedToDeleteOwnCalendarsOnly = $rightsObj->isAllowedToDeleteOnlyOwnCalendar();
        $isAllowedToDeletePublicCalendars = $rightsObj->isAllowedToDeletePublicCalendar();

        if ($isAllowedToDeleteOwnCalendarsOnly) {
            return $isCalendarOwner;
        }
        return $isAllowedToDeleteCalendars && ($isCalendarOwner || ($this->isPublic && $isAllowedToDeletePublicCalendars));
    }

    /**
     * @param $userId
     * @param $groupIdArray
     * @return bool
     */
    public function isCalendarOwner($userId, $groupIdArray): bool
    {
        if (is_array($this->owner['fe_users']) && in_array($userId, $this->owner['fe_users'], true)) {
            return true;
        }
        foreach ($groupIdArray as $id) {
            if (is_array($this->owner['fe_groups']) && in_array($id, $this->owner['fe_groups'], true)) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param $template
     * @param $sims
     * @param $rems
     * @param $view
     * @return string
     */
    public function getEditLink(& $template, & $sims, & $rems, $view): string
    {
        $editlink = '';
        if ($this->isUserAllowedToEdit()) {
            $this->initLocalCObject($this->getValuesAsArray());
            $this->local_cObj->setCurrentVal($this->controller->pi_getLL('l_edit_calendar'));
            $this->controller->getParametersForTyposcriptLink(
                $this->local_cObj->data,
                [
                    'view' => 'edit_calendar',
                    'type' => $this->getType(),
                    'uid' => $this->getUid()
                ],
                $this->conf['cache'],
                $this->conf['clear_anyway'],
                $this->conf['view.']['calendar.']['editCalendarViewPid']
            );
            $editlink = $this->local_cObj->cObjGetSingle(
                $this->conf['view.'][$view . '.']['calendar.']['editLink'],
                $this->conf['view.'][$view . '.']['calendar.']['editLink.']
            );
        }
        if ($this->isUserAllowedToDelete()) {
            $this->initLocalCObject($this->getValuesAsArray());
            $this->local_cObj->setCurrentVal($this->controller->pi_getLL('l_delete_calendar'));
            $this->controller->getParametersForTyposcriptLink(
                $this->local_cObj->data,
                [
                    'view' => 'delete_calendar',
                    'type' => $this->getType(),
                    'uid' => $this->getUid()
                ],
                $this->conf['cache'],
                $this->conf['clear_anyway'],
                $this->conf['view.']['calendar.']['deleteCalendarViewPid']
            );
            $editlink .= $this->local_cObj->cObjGetSingle(
                $this->conf['view.'][$view . '.']['calendar.']['deleteLink'],
                $this->conf['view.'][$view . '.']['calendar.']['deleteLink.']
            );
        }
        return $editlink;
    }

    /**
     * @param $piVars
     */
    public function updateWithPIVars(&$piVars)
    {
        foreach ($piVars as $key => $value) {
            switch ($key) {
                case 'title':
                    $this->setTitle(strip_tags($piVars['title']));
                    unset($piVars['title']);
                    break;
                case 'calendarType':
                    $this->setCalendarType(strip_tags($piVars['calendarType'], []));
                    unset($piVars['calendarType']);
                    break;
                case 'owner':
                    foreach ((array)strip_tags($this->controller->piVars['owner']) as $valueInner) {
                        $idname = [];
                        preg_match('/(^[a-z])_([0-9]+)_(.*)/', $valueInner, $idname);
                        if ($idname[1] === 'u') {
                            $this->setOwner('fe_users', $idname[2]);
                        } else {
                            $this->setOwner('fe_groups', $idname[2]);
                        }
                    }
                    break;
                case 'activateFreeAndBusy':
                    $this->setActivateFreeAndBusy(intval($piVars['activateFreeAndBusy']));
                    unset($piVars['activateFreeAndBusy']);
                    break;
                case 'freeAndBusyUser':
                    foreach ((array)strip_tags($this->controller->piVars['freeAndBusyUser']) as $valueInner) {
                        preg_match('/(^[a-z])_([0-9]+)_(.*)/', $valueInner, $idname);
                        if ($idname[1] === 'u') {
                            $this->setOwner('fe_users', $idname[2]);
                        } else {
                            $this->setOwner('fe_groups', $idname[2]);
                        }
                    }
                    break;
                case 'icsfile':
                    $this->setIcsFile(strip_tags($piVars['icsfile']));
                    unset($piVars['icsfile']);
                    break;
                case 'ics_file':
                    if (is_array($piVars['ics_file'])) {
                        $this->setIcsFile(strip_tags($piVars['ics_file'][0]));
                    }
                    unset($piVars['ics_file']);
                    break;
                case 'exturl':
                    $this->setExtUrl(strip_tags($piVars['exturl']));
                    unset($piVars['exturl']);
                    break;
                case 'refresh':
                    $this->setRefresh(strip_tags($piVars['refresh']));
                    unset($piVars['refresh']);
                    break;
            }
        }
    }

//
//    ####################################################

//
//    /**
//     * Test setTstamp
//     *
//     * @test
//     */
//    public function canSetTstamp()
//    {
//        $title = 'Cal title';
//        $this->calModelInstance->setTstamp($title);
//        $this->assertEquals($title, $this->calModelInstance->getTstamp());
//    }
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
//        $title = 'Cal title';
//        $this->calModelInstance->setOrganizer($title);
//        $this->assertEquals($title, $this->calModelInstance->getOrganizer());
//    }
//
//    /**
//     * Test setCreationDate
//     *
//     * @test
//     */
//    public function caSetCreationDate()
//    {
//        //    * @param $sequence Array
//        $title = 'Cal title';
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
//        $title = 'Cal title';
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
//        $title = 'Cal title';
//        $this->calModelInstance->setLocationLinkUrl($title);
//        $this->assertEquals($title, $this->calModelInstance->getLocationLinkUrl());
//    }
//    /**
//     * Test setLocationPage
//     *
//     * @test
//     */
//    public function setLocationPage()
//    {
//        $title = 'Cal title';
//        $this->calModelInstance->setLocationPage($title);
//        $this->assertEquals($title, $this->calModelInstance->getLocationPage());
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
