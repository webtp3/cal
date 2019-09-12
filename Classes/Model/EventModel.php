<?php

namespace TYPO3\CMS\Cal\Model;

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
use TYPO3\CMS\Cal\Controller\Controller;
use TYPO3\CMS\Cal\Domain\Repository\EventSharedUserMMRepository;
use TYPO3\CMS\Cal\Domain\Repository\SubscriptionRepository;
use TYPO3\CMS\Cal\Service\RightsService;
use TYPO3\CMS\Cal\Service\SysCategoryService;
use TYPO3\CMS\Cal\Utility\Functions;
use TYPO3\CMS\Cal\Utility\Registry;
use TYPO3\CMS\Core\Mail\MailMessage;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class EventModel
 */
class EventModel extends Model
{
    /**
     * @var LocationModel
     */
    public $location;

    /**
     * @var bool
     */
    public $isException = false;

    /**
     * @var int
     */
    public $createUserId = 0;

    /**
     * @var bool
     */
    public $isTomorrow = false;

    /**
     * @var string
     */
    public $teaser = '';

    /**
     * @var string
     */
    public $limitAttendeeToThisEmail = '';

    /**
     * @var string
     */
    public $timezone = 'UTC';

    /**
     * @var bool
     */
    public $sendOutInvitation = false;

    /**
     * @var array
     */
    public $markerCache = [];

    /**
     * @var EventSharedUserMMRepository
     */
    protected $eventSharedUserMMRepository;

    /**
     * @var SubscriptionRepository
     */
    protected $subscriptionRepository;

    /**
     * EventModel constructor.
     * @param $row
     * @param $isException
     * @param $serviceKey
     */
    public function __construct($row, $isException, $serviceKey)
    {
        parent::__construct($serviceKey);

        $this->eventSharedUserMMRepository = GeneralUtility::makeInstance(EventSharedUserMMRepository::class);
        $this->subscriptionRepository = GeneralUtility::makeInstance(SubscriptionRepository::class);
        if (is_array($row)) {
            $this->createEvent($row, $isException);
        }

        $this->isException = $isException;
        $this->setType($serviceKey);
        $this->setObjectType('event');
    }

    /**
     * @param $piVars
     */
    public function updateWithPIVars(&$piVars)
    {
        $startDateIsSet = false;
        $endDateIsSet = false;

        $customFieldArray = [];
        if ($this->conf['view'] === 'create_event' || $this->conf['view'] === 'edit_event') {
            $customFieldArray = GeneralUtility::trimExplode(
                ',',
                $this->conf['rights.'][$this->conf['view'] === 'create_event' ? 'create.' : 'edit.']['event.']['additionalFields'],
                1
            );
        } elseif ($this->conf['view'] === 'confirm_event') {
            if ($this->row['uid'] > 0) {
                $customFieldArray = GeneralUtility::trimExplode(
                    ',',
                    $this->conf['rights.']['edit.']['event.']['additionalFields'],
                    1
                );
            } else {
                $customFieldArray = GeneralUtility::trimExplode(
                    ',',
                    $this->conf['rights.']['create.']['event.']['additionalFields'],
                    1
                );
            }
        }

        if ($piVars['formCheck'] === '1') {
            $this->setAllDay(false);
        }

        foreach ($piVars as $key => $value) {
            switch ($key) {
                case 'hidden':
                    $this->setHidden($value);
                    unset($piVars['hidden']);
                    break;
                case 'calendar_id':
                    $this->setCalendarId(intval($piVars['calendar_id']));
                    unset($piVars['calendar_id']);
                    break;
                case 'category':
                case 'category_ids':
                    $this->setCategories([]);
                    $categories = [];
                    $categoryService = GeneralUtility::makeInstance(SysCategoryService::class);
                    $categoryService->getCategoryArray($this->conf['pidList'], $categories);
                    $piVarsCaregoryArray = explode(
                        ',',
                        Controller::convertLinkVarArrayToList($piVars[$key])
                    );
                    if (!empty($piVarsCaregoryArray)) {
                        foreach ($piVarsCaregoryArray as $categoryId) {
                            $this->addCategory($categories[0][0][$categoryId]);
                        }
                    }
                    unset($piVars['category'], $piVars['category_ids']);
                    break;
                case 'allday_checkbox':
                case 'allday':
                    if ((int)$piVars[$key] === 1) {
                        $this->setAllDay(true);
                    } elseif ($piVars[$key] !== '') {
                        $this->setAllDay(false);
                    }
                    break;
                case 'start_date':
                case 'start_time':
                    if (!$startDateIsSet) {
                        $start = new CalendarDateTime($piVars['start_date'] . '000000');
                        $start->addSeconds($piVars['start_time']);
                        $this->setStart($start);
                    }
                    unset($piVars['start_date'], $piVars['start_time']);
                    break;
                case 'startdate':
                case 'starttime':
                case 'startminutes':
                    if (!$startDateIsSet) {
                        $start = new CalendarDateTime(Functions::getYmdFromDateString(
                            $this->conf,
                            strip_tags($piVars['startdate'] ?: $piVars['getdate'])
                            ) . '000000');
                        if (strlen($piVars['starttime']) === 4) {
                            $tempArray = [];
                            preg_match('/([0-9]{2})([0-9]{2})/', $piVars['starttime'], $tempArray);
                            $start->setHour(intval($tempArray[1]));
                            $start->setMinute(intval($tempArray[2]));
                        } else {
                            $start->setHour(intval($piVars['starttime']));
                            $start->setMinute(intval($piVars['startminutes']));
                        }
                        $start->setSecond(0);
                        $start->setTZbyID('UTC');
                        $this->setStart($start);
                        $startDateIsSet = true;
                    }
                    unset($piVars['startdate'], $piVars['starttime'], $piVars['startminutes']);
                    break;
                case 'end_date':
                case 'end_time':
                    if (!$endDateIsSet) {
                        $end = new CalendarDateTime($piVars['end_date'] . '000000');
                        $end->addSeconds($piVars['end_time']);
                        $this->setEnd($end);
                    }
                    unset($piVars['end_date'], $piVars['end_time']);
                    break;
                case 'enddate':
                case 'endtime':
                case 'endminutes':
                    if (!$endDateIsSet) {
                        $end = new CalendarDateTime(Functions::getYmdFromDateString(
                            $this->conf,
                            strip_tags($piVars['enddate'] ?: $piVars['getdate'])
                            ) . '000000');
                        if (strlen($piVars['endtime']) === 4) {
                            $tempArray = [];
                            preg_match('/([0-9]{2})([0-9]{2})/', $piVars['endtime'], $tempArray);
                            $end->setHour(intval($tempArray[1]));
                            $end->setMinute(intval($tempArray[2]));
                        } else {
                            $end->setHour(intval($piVars['endtime']));
                            $end->setMinute(intval($piVars['endminutes']));
                        }
                        $end->setSecond(0);
                        $end->setTZbyID('UTC');
                        $this->setEnd($end);
                    }
                    unset($piVars['enddate'], $piVars['endtime'], $piVars['endminutes']);
                    $endDateIsSet = true;
                    break;
                case 'organizer':
                    $this->setOrganizer(strip_tags($piVars['organizer']));
                    unset($piVars['organizer']);
                    break;
                case 'location':
                    $this->setLocation(strip_tags($piVars['location']));
                    unset($piVars['location']);
                    break;
                case 'cal_organizer':
                    $this->setOrganizerId(intval($piVars['cal_organizer']));
                    unset($piVars['cal_organizer']);
                    break;
                case 'cal_location':
                    $this->setLocationId(intval($piVars['cal_location']));
                    unset($piVars['cal_location']);
                    break;
                case 'title':
                    $this->setTitle(strip_tags($piVars['title']));
                    unset($piVars['title']);
                    break;
                case 'description':
                    $this->setDescription(htmlspecialchars($piVars['description']));
                    unset($piVars['description'], $piVars['_TRANSFORM_description']);
                    break;
                case 'teaser':
                    $this->setTeaser(htmlspecialchars($piVars['teaser']));
                    unset($piVars['teaser']);
                    break;
                case 'image':
                    if (is_array($piVars['image'])) {
                        foreach ($piVars['image'] as $image) {
                            $this->addImage(strip_tags($image));
                        }
                    }
                    break;
                case 'attachment':
                    $this->setAttachment([]);
                    if (is_array($piVars['attachment'])) {
                        foreach ($piVars['attachment'] as $attachment) {
                            $this->addAttachment(strip_tags($attachment));
                        }
                    }
                    break;
                case 'frequency_id':
                    $valueArray = [
                        'none',
                        'day',
                        'week',
                        'month',
                        'year'
                    ];
                    $this->setFreq(in_array($piVars['frequency_id'], $valueArray, true) ? $piVars['frequency_id'] : 'none');
                    unset($piVars['frequency_id']);
                    break;
                case 'by_day':
                    if (is_array($piVars['by_day'])) {
                        $this->setByDay(strtolower(strip_tags(implode(',', $piVars['by_day']))));
                    } else {
                        $this->setByDay(strtolower(strip_tags($piVars['by_day'])));
                    }
                    unset($piVars['by_day']);
                    break;
                case 'by_monthday':
                    $this->setByMonthDay(strtolower(strip_tags($piVars['by_monthday'])));
                    unset($piVars['by_monthday']);
                    break;
                case 'by_month':
                    $this->setByMonth(strtolower(strip_tags($piVars['by_month'])));
                    unset($piVars['by_month']);
                    break;
                case 'until':
                    if ((int)$piVars['until'] !== 0) {
                        $until = new CalendarDateTime(Functions::getYmdFromDateString(
                            $this->conf,
                            strip_tags($piVars['until'])
                            ) . '000000');
                    } else {
                        $until = new CalendarDateTime('00000000000000');
                    }
                    $until->setTZbyID('UTC');
                    $this->setUntil($until);
                    unset($piVars['until']);
                    break;
                case 'count':
                    $this->setCount(intval($piVars['count']));
                    unset($piVars['count']);
                    break;
                case 'interval':
                    $this->setInterval(intval($piVars['interval']));
                    unset($piVars['interval']);
                    break;
                case 'rdate':
                    $this->setRdate(strtolower(strip_tags($piVars['rdate'])));
                    unset($piVars['rdate']);
                    break;
                case 'rdate_type':
                    $this->setRdateType(strtolower(strip_tags($piVars['rdate_type'])));
                    unset($piVars['rdate_type']);
                    break;
                case 'exception_ids':
                    $this->setExceptionSingleIds([]);
                    $this->setExceptionGroupIds([]);
                    foreach (GeneralUtility::trimExplode(',', $piVars['exception_ids'], 1) as $valueInner) {
                        preg_match('/(^[a-z])_([0-9]+)/', $valueInner, $idname);
                        if ($idname[1] === 'u') {
                            $this->addExceptionSingleId($idname[2]);
                        } elseif ($idname[1] === 'g') {
                            $this->addExceptionGroupId($idname[2]);
                        }
                    }
                    break;
                case 'shared':
                case 'shared_ids':
                    $this->setSharedGroups([]);
                    $this->setSharedUsers([]);
                    $values = $piVars[$key];
                    if (!is_array($piVars[$key])) {
                        $values = GeneralUtility::trimExplode(',', $piVars[$key], 1);
                    }
                    foreach ($values as $entry) {
                        preg_match('/(^[a-z])_([0-9]+)/', $entry, $idname);
                        if ($idname[1] === 'u') {
                            $this->addSharedUser($idname[2]);
                        } elseif ($idname[1] === 'g') {
                            $this->addSharedGroup($idname[2]);
                        }
                    }
                    break;
                case 'event_type':
                    $this->setEventType(intval($piVars['event_type']));
                    unset($piVars['event_type']);
                    break;
                case 'attendee':
                    $serviceKey = '';
                    $attendeeIndex = [];
                    $attendeeServices = $this->getAttendees();
                    $emptyAttendeeArray = [];
                    $this->setAttendees($emptyAttendeeArray);
                    $attendeeServiceKeys = array_keys($attendeeServices);
                    foreach ($attendeeServiceKeys as $serviceKey) {
                        $attendeeKeys = array_keys($attendeeServices[$serviceKey]);
                        foreach ($attendeeKeys as $attendeeKey) {
                            /** @var AttendeeModel $attendee */
                            $attendee = $attendeeServices[$serviceKey][$attendeeKey];
                            $attendeeIndex[$serviceKey . '_' . ($attendee->getFeUserId() ?: $attendee->getEmail())] = &$attendee;
                        }
                    }

                    $values = $piVars[$key];
                    if (!is_array($piVars[$key])) {
                        $values = GeneralUtility::trimExplode(',', $piVars[$key], 1);
                    }
                    $attendance = $piVars['attendance'];
                    if (!is_array($piVars['attendance'])) {
                        $attendanceTemp = GeneralUtility::trimExplode(',', $piVars['attendance'], 1);
                        $attendance = [];
                        $i = 0;
                        foreach ($values as $entry) {
                            $attendance[$entry] = $attendanceTemp[$i];
                            $i++;
                        }
                    }
                    foreach ($values as $entry) {
                        preg_match('/(^[emailu]+)_([^*]+)/', $entry, $idname);

                        if (is_object($attendeeIndex[$serviceKey . '_' . $idname[2]])) {
                            // Attendee has been already assigned -> updating attendance
                            $attendeeIndex[$serviceKey . '_' . $idname[2]]->setAttendance($attendance[$entry]);
                            $this->addAttendee($attendeeIndex[$serviceKey . '_' . $idname[2]]);
                        } else {
                            $initRow = [];
                            $attendee = new AttendeeModel($initRow, 'cal_attendee_model');
                            if ($idname[1] === 'u') {
                                $attendee->setFeUserId($idname[2]);
                                $attendee->setAttendance($attendance[$entry]);
                            } elseif ($idname[1] === 'email') {
                                $attendee->setEmail($idname[2]);
                                $attendee->setAttendance('OPT-PARTICIPANT');
                            }
                            $this->addAttendee($attendee);
                        }
                    }
                    foreach (GeneralUtility::trimExplode(',', $piVars['attendee_external'], 1) as $emailAddress) {
                        if (is_object($attendeeIndex[$serviceKey . '_' . $emailAddress])) {
                            // Attendee has been already assigned -> updating attendance
                            $attendeeIndex[$serviceKey . '_' . $emailAddress]->setAttendance($attendance[$entry]);
                            $this->addAttendee($attendeeIndex[$serviceKey . '_' . $emailAddress]);
                        } else {
                            $initRow = [];
                            $attendee = new AttendeeModel($initRow, 'cal_attendee_model');
                            $attendee->setEmail($emailAddress);
                            $attendee->setAttendance('OPT-PARTICIPANT');
                            $this->addAttendee($attendee);
                        }
                    }
                    unset($piVars['attendee'], $piVars['attendance']);
                    break;
                case 'sendout_invitation':
                    $this->setSendOutInvitation((int)$piVars['sendout_invitation'] === 1);
                    unset($piVars['sendout_invitation']);
                    break;
                default:
                    if (in_array($key, $customFieldArray, true)) {
                        $this->row[$key] = $value;
                    }
            }
        }

        if ($this->getEventType() !== Model::EVENT_TYPE_MEETING) {
            $newAttendeeArray = [];
            $this->setAttendees($newAttendeeArray);
        }

        if ($this->conf['rights.']['create.']['event.']['fields.']['dynamicStarttimeOffset']) {
            $now = new CalendarDateTime();

            $now->addSeconds(intval($this->conf['rights.']['create.']['event.']['fields.']['dynamicStarttimeOffset']));

            if (is_object($start)) {
                $start->setHour($now->getHour());
                $start->setMinute($now->getMinute());
                $this->setStart($start);
            }
        }

        if (!$startDateIsSet && $piVars['mygetdate']) {
            $startDay = strip_tags($piVars['mygetdate']);
            $startHour = '00';
            $startMinutes = '00';
            if ($piVars['gettime']) {
                $startHour = substr(strip_tags($piVars['gettime']), 0, 2);
                $startMinutes = substr(strip_tags($piVars['gettime']), 2, 2);
            }

            $start = new CalendarDateTime($startDay . ' ' . $startHour . ':' . $startMinutes . ':00');
            $start->setTZbyID('UTC');
            $this->setStart($start);
        }
        if (!$endDateIsSet && $piVars['mygetdate']) {
            $end = new CalendarDateTime();
            $end->copy($start);
            $end->addSeconds($this->conf['view.']['event.']['event.']['defaultEventLength']);
            $this->setEnd($end);
        }
    }

    /**
     * @param $row
     * @param $isException
     */
    public function createEvent(&$row, $isException)
    {
        $this->row = &$row;
        $this->setType($this->serviceKey);
        $this->setUid($row['uid']);
        $this->setPid($row['pid']);
        $this->setCrdate($row['crdate']);
        $this->setCreateUserId($row['cruser_id']);
        $this->setHidden($row['hidden']);
        $this->setTstamp($row['tstamp']);

        $this->setCalendarId($row['calendar_id']);

        $this->setTimezone($row['timezone']);

        if ($row['allday']) {
            $row['start_time'] = 0;
            $row['end_time'] = 0;
        } elseif ($row['start_time'] === 0 && $row['end_time'] === 0) {
            $row['allday'] = 1;
        }
        $tempDate = new CalendarDateTime($row['start_date'] > 0 ? $row['start_date'] . '000000' :'');
        $tempDate->setTZbyID('UTC');
        $tempDate->addSeconds($row['start_time']);
        $this->setStart($tempDate);
        $tempDate = new CalendarDateTime($row['end_date'] >  0 ? $row['end_date'] . '000000': '');
        $tempDate->setTZbyID('UTC');
        $tempDate->addSeconds($row['end_time']);
        $this->setEnd($tempDate);

        $this->setAllDay($row['allday']);
        $eventStart = $this->getStart();
        $eventEnd = $this->getEnd();
        if ($eventStart->after($this->getEnd()) || !$this->isAllDay() && $eventStart->equals($this->getEnd())) {
            $tempDate = new CalendarDateTime($row['start_date']);
            $tempDate->setTZbyID('UTC');
            $tempDate->addSeconds($row['start_time'] + $this->conf['view.']['event.']['event.']['defaultEventLength']);
            $this->setEnd($tempDate);
        }

        if ($this->isAllDay()) {
            $eventEnd->addSeconds(86399);
            $this->setEnd($eventEnd);
        }

        $this->setTitle($row['title']);
        $this->setCategories($row['categories']);

        $this->setFreq($row['freq']);
        $this->setByDay($row['byday']);
        $this->setByMonthDay($row['bymonthday']);
        $this->setByMonth($row['bymonth']);

        $tempDate = new CalendarDateTime($row['until'] ?: '000000' . '000000');
        $tempDate->setTZbyID('UTC');
        $this->setUntil($tempDate);

        $cnt = $row['cnt'];
        if ($row['rdate']) {
            $cnt += count(explode(',', $row['rdate']));
        }
        $this->setCount($cnt);

        $this->setInterval($row['intrval']);

        $this->setRdateType($row['rdate_type']);
        $this->setRdate($row['rdate']);

        /* new */
        $this->setEventType($row['type']);

        if ($row['type'] === 3) { // meeting
            $modelObj = &Registry::Registry('basic', 'modelcontroller');
            $this->setAttendees($modelObj->findEventAttendees($this->getUid()));
        }

        $this->setPage($row['page']);
        $this->setExtUrl($row['ext_url']);
        /* new */

        $this->setImage(GeneralUtility::trimExplode(',', $row['image'], 1));

        if ($row['attachment']) {
            $fileArr = explode(',', $row['attachment']);
            foreach ($fileArr as $key => $val) {
                // fills the marker ###FILE_LINK### with the links to the attached files
                $this->addAttachment($val);
            }
        }

        if ($row['exception_single_ids']) {
            $ids = explode(',', $row['exception_single_ids']);
            foreach ($ids as $id) {
                $this->addExceptionSingleId($id);
            }
        }
        if ($row['exceptionGroupIds']) {
            $ids = explode(',', $row['exceptionGroupIds']);
            foreach ($ids as $id) {
                $this->addExceptionGroupId($id);
            }
        }
        if ($row['calendar_headerstyle'] !== '') {
            $this->setHeaderStyle($row['calendar_headerstyle']);
        }

        if ($row['calendar_bodystyle'] !== '') {
            $this->setBodyStyle($row['calendar_bodystyle']);
        }

        $this->setEventOwner($row['event_owner']);

        if (!$isException) {
            $this->setTeaser($row['teaser']);
            $this->setDescription($row['description']);

            $this->setLocationId($row['location_id']);
            $this->setLocation($row['location']);
            $this->setLocationPage($row['location_pid']);
            $this->setLocationLinkUrl($row['location_link']);

            $this->setOrganizerId($row['organizer_id']);
            $this->setOrganizer($row['organizer']);
            $this->setOrganizerPid($row['organizer_pid']);
            $this->setOrganizerLink($row['organizer_link']);
        }

        $sharedUids = $this->eventSharedUserMMRepository->findSharedUidsByEventUid($this->getUid());
        foreach ($sharedUids as $sharedUid) {
            if ($sharedUid['tablenames'] === 'fe_users') {
                $this->addSharedUser($sharedUid['uid_foreign']);
            } elseif ($sharedUid['tablenames'] === 'fe_groups') {
                $this->addSharedGroup($sharedUid['uid_foreign']);
            }
        }

        $events = $this->subscriptionRepository->findSubscribingUsersAndGroupsByEventUid($this->getUid());
        foreach ($events as $event) {
            if ($event['tablenames'] === 'fe_users') {
                $this->addNotifyUser($event['uid_foreign'] . '|' . $event['offset']);
            } elseif ($event['tablenames'] === 'fe_groups') {
                $this->addNotifyGroup($event['uid_foreign'] . '|' . $event['offset']);
            }
        }
    }

    /**
     * @return EventModel
     */
    public function cloneEvent(): EventModel
    {
        $thisClass = get_class($this);
        /** @var EventModel $event */
        $event = new $thisClass($this->getValuesAsArray(), $this->isException, $this->getType());
        $event->markerCache = $this->markerCache;
        $event->setIsClone(true);
        return $event;
    }

    /**
     * Gets the teaser of the event.
     *
     * @return string teaser.
     */
    public function getTeaser(): string
    {
        return $this->teaser;
    }

    /**
     * Sets the teaser of the event.
     * @param string $teaser
     */
    public function setTeaser($teaser)
    {
        $this->teaser = $teaser ?? '';
    }

    /**
     * @param $view
     * @return string
     */
    public function getLocationLink($view): string
    {
        $locationLink = '';
        if ($this->getLocationId() > 0) {
            /** @var LocationModel $location */
            $location = $this->getLocationObject();

            if (is_object($location)) {
                $tempData = $location->getValuesAsArray();
                $this->initLocalCObject($tempData);
                unset($tempData);
                $this->local_cObj->setCurrentVal($location->getName());
                /* If a specific location page is defined, link to it */
                if ($this->getLocationPage() > 0) {
                    $this->local_cObj->data['link_parameter'] = $this->getLocationPage();
                } else {
                    /* If location view is allowed, link to it */
                    $rightsObj = &Registry::Registry('basic', 'rightscontroller');
                    if ($this->conf['view.']['location.']['locationViewPid'] || $rightsObj->isViewEnabled($this->conf['view.']['locationLinkTarget'])) {
                        $location->getLinkToLocation('|');
                        $this->local_cObj->data['link_parameter'] = $this->controller->cObj->lastTypoLinkUrl;
                    } else {
                        /* Just show the name of the location */
                        $this->local_cObj->data['link_parameter'] = '';
                    }
                }
                $locationLink = $this->local_cObj->cObjGetSingle(
                    $this->conf['view.'][$view . '.']['event.']['location'],
                    $this->conf['view.'][$view . '.']['event.']['location.']
                );
            } else {
                $locationLink = '';
            }
        }
        return $locationLink;
    }

    /**
     * @param $view
     * @return string
     */
    public function getOrganizerLink($view = ''): string
    {
        $organizerLink = '';
        if ($this->getOrganizerId() > 0) {
            $organizer = $this->getOrganizerObject();

            if (is_object($organizer)) {
                $tempData = $organizer->getValuesAsArray();
                $this->initLocalCObject($tempData);
                unset($tempData);
                $this->local_cObj->setCurrentVal($organizer->getName());

                /* If a specific organizer page is defined, link to it */
                if ($this->getOrganizerPid() > 0) {
                    $this->local_cObj->data['link_parameter'] = $this->getOrganizerPid();
                } else {
                    /* If organizer view is allowed, link to it */
                    $rightsObj = &Registry::Registry('basic', 'rightscontroller');
                    if ($this->conf['view.']['organizer.']['organizerViewPid'] || $rightsObj->isViewEnabled($this->conf['view.']['organizerLinkTarget'])) {
                        $organizer->getLinkToOrganizer('|');
                        $this->local_cObj->data['link_parameter'] = $this->controller->cObj->lastTypoLinkUrl;
                    } else {
                        /* Just show the name of the organizer */
                        $this->local_cObj->data['link_parameter'] = '';
                    }
                }
                $organizerLink = $this->local_cObj->cObjGetSingle(
                    $this->conf['view.'][$view . '.']['event.']['organizer'],
                    $this->conf['view.'][$view . '.']['event.']['organizer.']
                );
            } else {
                $organizerLink = '';
            }
        }
        return $organizerLink;
    }

    /**
     * Returns the headerstyle name
     */
    public function getHeaderStyle(): string
    {
        /** @var RightsService $rightsObj */
        $rightsObj = &Registry::Registry('basic', 'rightscontroller');
        if ($this->row['isFreeAndBusyEvent'] === 1) {
            return $this->conf['view.']['freeAndBusy.']['headerStyle'];
        }
        if ($this->conf['view.'][$this->conf['view'] . '.']['event.']['differentStyleIfOwnEvent'] && $rightsObj->getUserId() === $this->getCreateUserId()) {
            return $this->conf['view.']['event.']['event.']['headerStyleOfOwnEvent'];
        }
        if (
            count($this->categories)
            && is_object($this->categories[0])
            && $this->categories[0]->getHeaderStyle() !== ''
        ) {
            return $this->categories[0]->getHeaderStyle();
        }
        return $this->headerstyle;
    }

    /**
     * Returns the bodystyle name
     */
    public function getBodyStyle(): string
    {
        /** @var RightsService $rightsObj */
        $rightsObj = &Registry::Registry('basic', 'rightscontroller');
        if ($this->row['isFreeAndBusyEvent'] === 1) {
            return $this->conf['view.']['freeAndBusy.']['bodyStyle'];
        }
        if ($this->conf['view.'][$this->conf['view'] . '.']['event.']['differentStyleIfOwnEvent'] && $rightsObj->getUserId() === $this->getCreateUserId()) {
            return $this->conf['view.']['event.']['event.']['bodyStyleOfOwnEvent'];
        }
        if (
            count($this->categories)
            && is_object($this->categories[0])
            && $this->categories[0]->getBodyStyle() !== ''
        ) {
            return $this->categories[0]->getBodyStyle();
        }

        return $this->bodystyle;
    }

    /**
     * Gets the createUserId of the event.
     *
     * @return int create user id.
     */
    public function getCreateUserId(): int
    {
        return $this->createUserId;
    }

    /**
     * Sets the createUserId of the event.
     *
     * @param string
     */
    public function setCreateUserId($createUserId)
    {
        $this->createUserId = $createUserId;
    }

    /**
     * @return string
     */
    public function renderEventForOrganizer(): string
    {
        return $this->renderEventFor('ORGANIZER');
    }

    /**
     * @return string
     */
    public function renderEventForLocation(): string
    {
        return $this->renderEventFor('LOCATION');
    }

    /**
     * @return string
     */
    public function renderEventForDay(): string
    {
        return $this->renderEventFor('DAY');
    }

    /**
     * @return string
     */
    public function renderEventForWeek(): string
    {
        return $this->renderEventFor('WEEK');
    }

    /**
     * @return string
     */
    public function renderEventForAllDay(): string
    {
        return $this->renderEventFor('ALLDAY');
    }

    /**
     * @return string
     */
    public function renderEventForMonth(): string
    {
        if ($this->isAllDay()) {
            return $this->renderEventFor('MONTH_ALLDAY');
        }
        return $this->renderEventFor('MONTH');
    }

    /**
     * @return string
     */
    public function renderEventForMiniMonth(): string
    {
        if ($this->isAllDay()) {
            return $this->renderEventFor('MONTH_MINI_ALLDAY');
        }
        return $this->renderEventFor('MONTH_MINI');
    }

    /**
     * @return string
     */
    public function renderEventForMediumMonth(): string
    {
        if ($this->isAllDay()) {
            return $this->renderEventFor('MONTH_MEDIUM_ALLDAY');
        }
        return $this->renderEventFor('MONTH_MEDIUM');
    }

    /**
     * @return string
     */
    public function renderEventForYear(): string
    {
        return $this->renderEventFor('year');
    }

    /**
     * @return string
     */
    public function renderEvent(): string
    {
        return $this->fillTemplate('###TEMPLATE_PHPICALENDAR_EVENT###');
    }

    /**
     * @param string $subpartSuffix
     * @return string
     */
    public function renderEventForList($subpartSuffix = 'LIST_ODD'): string
    {
        return $this->renderEventFor($subpartSuffix);
    }

    /**
     * @param $viewType
     * @param string $subpartSuffix
     * @return string
     */
    public function renderEventFor($viewType, $subpartSuffix = ''): string
    {
        if ($this->row['isFreeAndBusyEvent'] === 1) {
            $viewType .= '_FNB';
        }
        if (substr(
            $viewType,
            -6
            ) !== 'ALLDAY' && ($this->isAllday() || $this->getStart()->format('Ymd') !== $this->getEnd()->format('Ymd'))) {
            $subpartSuffix .= 'ALLDAY';
        }
        $hookObjectsArr = Functions::getHookObjectsArray(
            'tx_cal_phpicalendar_model',
            'eventModelClass',
            'model'
        );

        foreach ($hookObjectsArr as $hookObj) {
            if (method_exists($hookObj, 'preFillTemplate')) {
                $hookObj->preFillTemplate($this, $viewType, $subpartSuffix);
            }
        }

        return $this->fillTemplate('###TEMPLATE_PHPICALENDAR_EVENT_' . strtoupper($viewType) . ($subpartSuffix ? '_' : '') . $subpartSuffix . '###');
    }

    /**
     * @param $subpartMarker
     * @return string
     */
    public function fillTemplate($subpartMarker): string
    {
        $templatePath = $this->conf['view.']['event.']['eventModelTemplate'];

        $page = Functions::getContent($templatePath);

        if ($page === '') {
            return '<h3>calendar: no event model template file found:</h3>' . $templatePath;
        }
        $page = $this->markerBasedTemplateService->getSubpart($page, $subpartMarker);
        if (!$page) {
            return 'could not find the >' . str_replace(
                '###',
                '',
                $subpartMarker
                ) . '< subpart-marker in ' . $templatePath;
        }
        $rems = [];
        $sims = [];
        $wrapped = [];
        $this->getMarker($page, $sims, $rems, $wrapped);
        return $this->finish(Functions::substituteMarkerArrayNotCached(
            $page,
            $sims,
            $rems,
            $wrapped
        ));
    }

    /**
     * @return string
     */
    public function renderEventPreview(): string
    {
        $this->isPreview = true;
        return $this->fillTemplate('###TEMPLATE_PHPICALENDAR_EVENT_PREVIEW###');
    }

    /**
     * @return string
     */
    public function renderTomorrowsEvent(): string
    {
        $this->isTomorrow = true;
        return $this->fillTemplate('###TEMPLATE_PHPICALENDAR_EVENT_TOMORROW###');
    }

    /**
     * @param $template
     * @param $sims
     * @param $rems
     * @param $wrapped
     * @param $view
     */
    public function getSubscriptionMarker(& $template, & $sims, & $rems, &$wrapped, $view)
    {
        $uid = $this->conf['uid'];
        $type = $this->conf['type'];
        $monitoring = $this->conf['monitor'];
        $getdate = $this->conf['getdate'];
        $rems['###SUBSCRIPTION###'] = '';
        $sims['###NOTLOGGEDIN_NOMONITORING_HEADING###'] = '';
        $sims['###NOTLOGGEDIN_NOMONITORING_SUBMIT###'] = '';
        $sims['###NOTLOGGEDIN_MONITORING_HEADING###'] = '';
        $sims['###NOTLOGGEDIN_MONITORING_SUBMIT###'] = '';
        $sims_temp['L_CAPTCHA_START_SUCCESS'] = '';
        $sims_temp['L_CAPTCHA_STOP_SUCCESS'] = '';

        $rightsObj = &Registry::Registry('basic', 'rightscontroller');
        if ($uid && ((int)$this->conf['allowSubscribe'] === 1 || ((int)$this->conf['subscribeFeUser'] === 1 && $rightsObj->isLoggedIn()))) {
            if (!empty($monitoring)) {
                $user_uid = $rightsObj->getUserId();
                switch ($monitoring) {
                    case 'start':
                        {
                            if ($user_uid > 0) {
                                $this->subscriptionRepository->insert(
                                    [
                                        'uid_local' => $uid,
                                        'uid_foreign' => $user_uid,
                                        'tablenames' => 'fe_users',
                                        'sorting' => 1,
                                        'pid' => $this->conf['rights.']['create.']['event.']['saveEventToPid'],
                                        'offset' => $this->conf['view.']['event.']['remind.']['time']
                                    ]
                                );

                                $date = new CalendarDateTime();
                                $date->setTZbyID('UTC');
                                $reminderService = &Functions::getReminderService();
                                $reminderService->scheduleReminder($uid);
                            } else {
                                if ((int)$this->conf['subscribeWithCaptcha'] === 1 && ExtensionManagementUtility::isLoaded('captcha')) {
                                    session_start();
                                    $captchaStr = $_SESSION['tx_captcha_string'];
                                    $_SESSION['tx_captcha_string'] = '';
                                } else {
                                    $captchaStr = -1;
                                }

                                if (($captchaStr && $this->controller->piVars['captcha'] === $captchaStr) || ((int)$this->conf['subscribeWithCaptcha'] === 0)) {
                                    // send confirm email!!
                                    $email = $this->controller->piVars['email'];

                                    $mailer = $mail = new MailMessage();

                                    if (GeneralUtility::validEmail($this->conf['view.']['event.']['notify.']['emailAddress'])) {
                                        $mailer->setFrom([
                                            $this->conf['view.']['event.']['notify.']['emailAddress'] => $this->conf['view.']['event.']['notify.']['fromName']
                                        ]);
                                    }

                                    if (GeneralUtility::validEmail($this->conf['view.']['event.']['notify.']['emailReplyAddress'])) {
                                        $mailer->setReplyTo([
                                            $this->conf['view.']['event.']['notify.']['emailReplyAddress'] => $this->conf['view.']['event.']['notify.']['replyToName']
                                        ]);
                                    }

                                    $mailer->getHeaders()->addTextHeader(
                                        'Organization: ',
                                        $this->conf['view.']['event.']['notify.']['organisation']
                                    );

                                    $local_template = Functions::getContent($this->conf['view.']['event.']['notify.']['confirmTemplate']);

                                    $htmlTemplate = $this->markerBasedTemplateService->getSubpart($local_template, '###HTML###');
                                    $plainTemplate = $this->markerBasedTemplateService->getSubpart($local_template, '###PLAIN###');

                                    $local_switch = [];
                                    $local_rems = [];
                                    $local_wrapped = [];
                                    $this->getMarker(
                                        $htmlTemplate,
                                        $local_switch,
                                        $local_rems,
                                        $local_wrapped,
                                        'event'
                                    );
                                    $this->getMarker(
                                        $plainTemplate,
                                        $local_switch,
                                        $local_rems,
                                        $local_wrapped,
                                        'event'
                                    );

                                    $local_switch['###CONFIRM_LINK###'] = GeneralUtility::getIndpEnv('TYPO3_SITE_URL') . $this->controller->pi_getPageLink(
                                        $this->conf['view.']['event.']['notify.']['subscriptionViewPid'],
                                        '',
                                        [
                                                'tx_cal_controller[view]' => 'subscription',
                                                'tx_cal_controller[monitor]' => 'start',
                                                'tx_cal_controller[email]' => $email,
                                                'tx_cal_controller[uid]' => $this->getUid(),
                                                'tx_cal_controller[sid]' => md5($this->getUid() . $email . $this->getCrdate())
                                            ]
                                        );

                                    $local_switch['###EVENT_LINK###'] = GeneralUtility::getIndpEnv('TYPO3_SITE_URL') . $this->controller->pi_getPageLink(
                                        $this->conf['view.']['event.']['eventViewPid'],
                                        '',
                                        [
                                                'tx_cal_controller[view]' => 'event',
                                                'tx_cal_controller[uid]' => $this->getUid(),
                                                'tx_cal_controller[type]' => $this->getType(),
                                                'tx_cal_controller[getdate]' => $this->getStart()->format('Ymd')
                                            ]
                                        );
                                    $htmlTemplate = Functions::substituteMarkerArrayNotCached(
                                        $htmlTemplate,
                                        $local_switch,
                                        $local_rems,
                                        $local_wrapped
                                    );

                                    $htmlTemplate = Functions::substituteMarkerArrayNotCached(
                                        $htmlTemplate,
                                        $local_switch,
                                        $local_rems,
                                        $local_wrapped
                                    );
                                    $plainTemplate = Functions::substituteMarkerArrayNotCached(
                                        $plainTemplate,
                                        $local_switch,
                                        $local_rems,
                                        $local_wrapped
                                    );

                                    $mailer->setSubject($this->conf['view.']['event.']['notify.']['confirmTitle']);

                                    $rems['###SUBSCRIPTION###'] = $this->controller->pi_getLL('l_monitor_start_thanks');
                                    $this->controller->finish($htmlTemplate);
                                    $this->controller->finish($plainTemplate);
                                    $mailer->setTo([$email]);
                                    $mailer->setBody(strip_tags($plainTemplate), 'text/plain');
                                    $mailer->addPart(
                                        Functions::fixURI($htmlTemplate),
                                        'text/html'
                                    );
                                    $mailer->send();

                                    return;
                                }
                                $sims_temp['L_CAPTCHA_START_SUCCESS'] = $this->controller->pi_getLL('l_monitor_wrong_captcha');
                            }
                            break;
                        }
                    case 'stop':
                        {
                            if ($user_uid > 0) {
                                $this->subscriptionRepository->deleteByEventUidAndSharedUidAndTable($uid, $user_uid, 'fe_users');
                            } else {
                                if ((int)$this->conf['subscribeWithCaptcha'] === 1 && ExtensionManagementUtility::isLoaded('captcha')) {
                                    session_start();
                                    $captchaStr = $_SESSION['tx_captcha_string'];
                                    $_SESSION['tx_captcha_string'] = '';
                                } else {
                                    $captchaStr = -1;
                                }

                                if (($captchaStr && $this->controller->piVars['captcha'] === $captchaStr) || ((int)$this->conf['subscribeWithCaptcha'] === 0)) {
                                    $email = $this->controller->piVars['email'];
                                    $table = 'tx_cal_unknown_users';
                                    $select = 'crdate';
                                    $where = 'email = "' . $email . '"';
                                    $result = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select, $table, $where);
                                    $crdate = 0;
                                    if ($result) {
                                        $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result);
                                        $crdate = $row['crdate'];
                                        $GLOBALS['TYPO3_DB']->sql_free_result($result);
                                    }

                                    $mailer = $mail = new MailMessage();
                                    $mailer->setFrom([
                                        $this->conf['view.']['event.']['notify.']['emailAddress'] => $this->conf['view.']['event.']['notify.']['fromName']
                                    ]);
                                    $mailer->setReplyTo([
                                        $this->conf['view.']['event.']['notify.']['emailReplyAddress'] => $this->conf['view.']['event.']['notify.']['replyToName']
                                    ]);
                                    $mailer->getHeaders()->addTextHeader(
                                        'Organization: ',
                                        $this->conf['view.']['event.']['notify.']['organisation']
                                    );

                                    $local_template = Functions::getContent($this->conf['view.']['event.']['notify.']['unsubscribeConfirmTemplate']);

                                    $htmlTemplate = $this->markerBasedTemplateService->getSubpart($local_template, '###HTML###');
                                    $plainTemplate = $this->markerBasedTemplateService->getSubpart($local_template, '###PLAIN###');

                                    $local_switch = [];
                                    $local_rems = [];
                                    $local_wrapped = [];
                                    $this->getMarker(
                                        $htmlTemplate,
                                        $local_switch,
                                        $local_rems,
                                        $local_wrapped,
                                        'event'
                                    );
                                    $local_switch['###CONFIRM_LINK###'] = GeneralUtility::getIndpEnv('TYPO3_SITE_URL') . $this->controller->pi_getPageLink(
                                        $this->conf['view.']['event.']['notify.']['subscriptionViewPid'],
                                        '',
                                        [
                                                'tx_cal_controller[view]' => 'subscription',
                                                'tx_cal_controller[monitor]' => 'stop',
                                                'tx_cal_controller[email]' => $email,
                                                'tx_cal_controller[uid]' => $this->getUid(),
                                                'tx_cal_controller[sid]' => md5($this->getUid() . $email . $crdate)
                                            ]
                                        );
                                    $htmlTemplate = Functions::substituteMarkerArrayNotCached(
                                        $htmlTemplate,
                                        $local_switch,
                                        $local_rems,
                                        $local_wrapped
                                    );

                                    $local_switch = [];
                                    $local_rems = [];
                                    $local_wrapped = [];
                                    $this->getMarker(
                                        $plainTemplate,
                                        $local_switch,
                                        $local_rems,
                                        $local_wrapped,
                                        'event'
                                    );
                                    $local_switch['###CONFIRM_LINK###'] = GeneralUtility::getIndpEnv('TYPO3_SITE_URL') . $this->controller->pi_getPageLink(
                                        $this->conf['view.']['event.']['notify.']['subscriptionViewPid'],
                                        '',
                                        [
                                                'tx_cal_controller[view]' => 'subscription',
                                                'tx_cal_controller[monitor]' => 'stop',
                                                'tx_cal_controller[email]' => $email,
                                                'tx_cal_controller[uid]' => $this->getUid(),
                                                'tx_cal_controller[sid]' => md5($this->getUid() . $email . $crdate)
                                            ]
                                        );
                                    $plainTemplate = Functions::substituteMarkerArrayNotCached(
                                        $plainTemplate,
                                        $local_switch,
                                        $local_rems,
                                        $local_wrapped
                                    );

                                    $mailer->setSubject($this->conf['view.']['event.']['notify.']['unsubscribeConfirmTitle']);

                                    $rems['###SUBSCRIPTION###'] = $this->controller->pi_getLL('l_monitor_stop_thanks');
                                    $this->controller->finish($htmlTemplate);
                                    $this->controller->finish($plainTemplate);

                                    $mailer->setTo([
                                        $email
                                    ]);
                                    $mailer->setBody(strip_tags($plainTemplate), 'text/plain');
                                    $mailer->addPart(
                                        Functions::fixURI($htmlTemplate),
                                        'text/html'
                                    );
                                    $mailer->send();

                                    return;
                                }
                                $sims_temp['L_CAPTCHA_STOP_SUCCESS'] = $this->controller->pi_getLL('l_monitor_wrong_captcha');
                            }
                            break;
                        }
                }
            }

            /* If we have a logged in user */
            if ($rightsObj->isLoggedIn()) {
                // create a local cObj with a customized data array, that is allowed to be changed
                $this->initLocalCObject($this->getValuesAsArray());
                $result = $this->subscriptionRepository->findSubscriptionByEventUidAndSubscribingUserUid($uid, $rightsObj->getUserId());
                if (!empty($result)) {
                    $this->local_cObj->setCurrentVal($this->controller->pi_getLL('l_monitor_event_logged_in_monitoring'));
                    $this->controller->getParametersForTyposcriptLink($this->local_cObj->data, [
                        'view' => 'event',
                        'monitor' => 'stop',
                        'type' => $type,
                        'uid' => $uid
                    ], $this->conf['cache'], $this->conf['clear_anyway']);
                    $rems['###SUBSCRIPTION###'] = $this->local_cObj->cObjGetSingle(
                        $this->conf['view.'][$view . '.']['event.']['isMonitoringEventLink'],
                        $this->conf['view.'][$view . '.']['event.']['isMonitoringEventLink.']
                    );
                } else {
                    $this->local_cObj->setCurrentVal($this->controller->pi_getLL('l_monitor_event_logged_in_nomonitoring'));
                    $this->controller->getParametersForTyposcriptLink($this->local_cObj->data, [
                        'view' => 'event',
                        'monitor' => 'start',
                        'type' => $type,
                        'uid' => $uid
                    ], $this->conf['cache'], $this->conf['clear_anyway']);
                    $rems['###SUBSCRIPTION###'] = $this->local_cObj->cObjGetSingle(
                        $this->conf['view.'][$view . '.']['event.']['isNotMonitoringEventLink'],
                        $this->conf['view.'][$view . '.']['event.']['isNotMonitoringEventLink.']
                    );
                }
            } else { /* Not a logged in user */

                /* If a CAPTCHA is required to subscribe, add a couple extra markers */
                if ((int)$this->conf['subscribeWithCaptcha'] === 1 && ExtensionManagementUtility::isLoaded('captcha')) {
                    $sims_temp['CAPTCHA_SRC'] = '<img src="' . ExtensionManagementUtility::siteRelPath('captcha') . 'captcha/captcha.php' . '" alt="" />';
                    $sims_temp['L_CAPTCHA_TEXT'] = $this->controller->pi_getLL('l_captcha_text');
                    $sims_temp['CAPTCHA_TEXT'] = '<input type="text" size=10 name="tx_cal_controller[captcha]" value="">';
                } else {
                    $sims_temp['CAPTCHA_SRC'] = '';
                    $sims_temp['L_CAPTCHA_TEXT'] = '';
                    $sims_temp['CAPTCHA_TEXT'] = '';
                }

                $notLoggedinNoMonitoring = $this->markerBasedTemplateService->getSubpart($template, '###NOTLOGGEDIN_NOMONITORING###');
                $parameter = [
                    'no_cache' => 1,
                    'view' => 'event',
                    'monitor' => 'start',
                    'type' => $type,
                    'uid' => $uid
                ];
                $actionUrl = htmlspecialchars($this->controller->pi_linkTP_keepPIvars_url($parameter));

                $parameter2 = [
                    'no_cache' => 1,
                    'getdate' => $getdate,
                    'view' => 'event',
                    'monitor' => 'stop'
                ];

                $actionUrl2 = htmlspecialchars($this->controller->pi_linkTP_keepPIvars_url($parameter2));
                $sims_temp['NOTLOGGEDIN_NOMONITORING_HEADING'] = $this->controller->pi_getLL('l_monitor_event_logged_in_nomonitoring');
                $sims_temp['NOTLOGGEDIN_NOMONITORING_SUBMIT'] = $this->controller->pi_getLL('l_submit');
                $sims_temp['L_ENTER_EMAIL'] = $this->controller->pi_getLL('l_enter_email');
                $sims_temp['ACTIONURL'] = $actionUrl;
                $monitor = Controller::replace_tags($sims_temp, $notLoggedinNoMonitoring);

                $sims_temp['ACTIONURL'] = $actionUrl2;
                $notLoggedinMonitoring = $this->markerBasedTemplateService->getSubpart($template, '###NOTLOGGEDIN_MONITORING###');
                $sims_temp['NOTLOGGEDIN_MONITORING_HEADING'] = $this->controller->pi_getLL('l_monitor_event_logged_in_monitoring');
                $sims_temp['NOTLOGGEDIN_MONITORING_SUBMIT'] = $this->controller->pi_getLL('l_submit');
                $sims_temp['L_ENTER_EMAIL'] = $this->controller->pi_getLL('l_enter_email');

                $monitor .= Controller::replace_tags($sims_temp, $notLoggedinMonitoring);
                $rems['###SUBSCRIPTION###'] = $monitor;
            }
        } else {
            $rems['###SUBSCRIPTION###'] = '';
        }
    }

    /**
     * @param $template
     * @param $sims
     * @param $rems
     * @param $wrapped
     * @param $view
     */
    public function getStartAndEndMarker(& $template, & $sims, & $rems, & $wrapped, $view)
    {
        $this->initLocalCObject();

        $eventStart = $this->getStart();
        $eventEnd = $this->getEnd();
        if ($eventStart->equals($eventEnd)) {
            $sims['###STARTTIME_LABEL###'] = '';
            $sims['###ENDTIME_LABEL###'] = '';
            $sims['###STARTTIME###'] = '';
            $sims['###ENDTIME###'] = '';
            $this->local_cObj->setCurrentVal($eventStart->format($this->conf['view.'][$view . '.']['event.']['dateFormat']));
            $sims['###STARTDATE###'] = $this->local_cObj->cObjGetSingle(
                $this->conf['view.'][$view . '.']['event.']['startdate'],
                $this->conf['view.'][$view . '.']['event.']['startdate.']
            );

            $sims['###STARTDATE_LABEL###'] = $this->controller->pi_getLL('l_event_allday');
            if ((int)$this->conf['view.'][$view . '.']['event.']['dontShowEndDateIfEqualsStartDateAllday'] === 1) {
                $sims['###ENDDATE###'] = '';
                $sims['###ENDDATE_LABEL###'] = '';
            } else {
                $this->local_cObj->setCurrentVal($eventEnd->format($this->conf['view.'][$view . '.']['event.']['dateFormat']));
                $sims['###ENDDATE###'] = $this->local_cObj->cObjGetSingle(
                    $this->conf['view.'][$view . '.']['event.']['enddate'],
                    $this->conf['view.'][$view . '.']['event.']['enddate.']
                );
                $sims['###ENDDATE_LABEL###'] = $this->controller->pi_getLL('l_event_enddate');
            }
        } else {
            if ($this->isAllDay()) {
                $sims['###STARTTIME_LABEL###'] = '';
                $sims['###STARTTIME###'] = '';
            } else {
                $sims['###STARTTIME_LABEL###'] = $this->controller->pi_getLL('l_event_starttime');
                $this->local_cObj->setCurrentVal($eventStart->format($this->conf['view.'][$view . '.']['event.']['timeFormat']));
                $sims['###STARTTIME###'] = $this->local_cObj->cObjGetSingle(
                    $this->conf['view.'][$view . '.']['event.']['starttime'],
                    $this->conf['view.'][$view . '.']['event.']['starttime.']
                );
            }
            if ($this->isAllDay()) {
                $sims['###ENDTIME_LABEL###'] = '';
                $sims['###ENDTIME###'] = '';
            } else {
                $sims['###ENDTIME_LABEL###'] = $this->controller->pi_getLL('l_event_endtime');
                $this->local_cObj->setCurrentVal($eventEnd->format($this->conf['view.'][$view . '.']['event.']['timeFormat']));
                $sims['###ENDTIME###'] = $this->local_cObj->cObjGetSingle(
                    $this->conf['view.'][$view . '.']['event.']['endtime'],
                    $this->conf['view.'][$view . '.']['event.']['endtime.']
                );
            }

            $this->local_cObj->setCurrentVal($eventStart->format($this->conf['view.'][$view . '.']['event.']['dateFormat']));
            $sims['###STARTDATE###'] = $this->local_cObj->cObjGetSingle(
                $this->conf['view.'][$view . '.']['event.']['startdate'],
                $this->conf['view.'][$view . '.']['event.']['startdate.']
            );
            if ($this->conf['view.'][$view . '.']['event.']['dontShowEndDateIfEqualsStartDate'] && $eventEnd->format('Ymd') === $eventStart->format('Ymd')) {
                $sims['###STARTDATE_LABEL###'] = $this->controller->pi_getLL('l_date');
                $sims['###ENDDATE_LABEL###'] = '';
                $sims['###ENDDATE###'] = '';
            } else {
                $sims['###STARTDATE_LABEL###'] = $this->controller->pi_getLL('l_event_startdate');
                $sims['###ENDDATE_LABEL###'] = $this->controller->pi_getLL('l_event_enddate');
                $this->local_cObj->setCurrentVal($eventEnd->format($this->conf['view.'][$view . '.']['event.']['dateFormat']));
                $sims['###ENDDATE###'] = $this->local_cObj->cObjGetSingle(
                    $this->conf['view.'][$view . '.']['event.']['enddate'],
                    $this->conf['view.'][$view . '.']['event.']['enddate.']
                );
            }
        }
    }

    /**
     * @param $template
     * @param $sims
     * @param $rems
     * @param $wrapped
     * @param $view
     */
    public function getTitleMarker(& $template, & $sims, & $rems, & $wrapped, $view)
    {
        $this->initLocalCObject();
        $this->local_cObj->setCurrentVal($this->getTitle());
        if (
            $this->isTomorrow
            && isset($this->conf['view.']['other.']['tomorrowsEvents'])
            && !in_array($view, [
                        'create_event',
                        'edit_event'
                    ])) {
            $sims['###TITLE###'] = $this->local_cObj->cObjGetSingle(
                $this->conf['view.']['other.']['tomorrowsEvents'],
                $this->conf['view.']['other.']['tomorrowsEvents.']
            );
        } elseif ($this->conf['view.'][$view . '.']['event.']['alldayTitle'] && $this->isAllDay()) {
            $sims['###TITLE###'] = $this->local_cObj->cObjGetSingle(
                $this->conf['view.'][$view . '.']['event.']['alldayTitle'],
                $this->conf['view.'][$view . '.']['event.']['alldayTitle.']
            );
        } elseif ($this->conf['view.'][$view . '.']['event.']['title']) {
            $sims['###TITLE###'] = $this->local_cObj->cObjGetSingle(
                $this->conf['view.'][$view . '.']['event.']['title'],
                $this->conf['view.'][$view . '.']['event.']['title.']
            );
        } else {
            $sims['###TITLE###'] = $this->getTitle();
        }
    }

    /**
     * @param $template
     * @param $sims
     * @param $rems
     * @param $wrapped
     * @param $view
     */
    public function getTitleFnbMarker(& $template, & $sims, & $rems, & $wrapped, $view)
    {
        $sims['###TITLE_FNB###'] = $this->conf['view.']['freeAndBusy.']['eventTitle'];
    }

    /**
     * @param $template
     * @param $sims
     * @param $rems
     * @param $wrapped
     * @param $view
     */
    public function getOrganizerMarker(& $template, & $sims, & $rems, & $wrapped, $view)
    {
        if ($this->getOrganizerId() > 0) {
            $sims['###ORGANIZER###'] = $this->getOrganizerLink($view);
        } else {
            $this->initLocalCObject($this->getValuesAsArray());
            if ($this->getOrganizerPid() > 0) {
                $this->local_cObj->data['link_parameter'] = $this->getOrganizerPid();
            } else {
                $this->local_cObj->data['link_parameter'] = $this->getOrganizerLink();
            }
            $this->local_cObj->setCurrentVal($this->getOrganizer());
            $sims['###ORGANIZER###'] = $this->local_cObj->cObjGetSingle(
                $this->conf['view.'][$view . '.']['event.']['organizer'],
                $this->conf['view.'][$view . '.']['event.']['organizer.']
            );
        }
        if ($view === 'ics' || $view === 'ics_single') {
            $sims['###ORGANIZER###'] = Functions::replaceLineFeed($sims['###ORGANIZER###']);
        }
    }

    /**
     * @param $template
     * @param $sims
     * @param $rems
     * @param $wrapped
     * @param $view
     */
    public function getLocationMarker(& $template, & $sims, & $rems, & $wrapped, $view)
    {
        if ($this->getLocationId() > 0) {
            $sims['###LOCATION###'] = $this->getLocationLink($view);
        } else {
            $this->initLocalCObject($this->getValuesAsArray());
            if ($this->getLocationPage() > 0) {
                $this->local_cObj->data['link_parameter'] = $this->getLocationPage();
            } else {
                $this->local_cObj->data['link_parameter'] = $this->getLocationLinkUrl();
            }
            $this->local_cObj->setCurrentVal($this->getLocation());
            $sims['###LOCATION###'] = $this->local_cObj->cObjGetSingle(
                $this->conf['view.'][$view . '.']['event.']['location'],
                $this->conf['view.'][$view . '.']['event.']['location.']
            );
        }
        if ($view === 'ics' || $view === 'ics_single') {
            $sims['###LOCATION###'] = Functions::replaceLineFeed($sims['###LOCATION###']);
        }
    }

    /**
     * @param $template
     * @param $sims
     * @param $rems
     * @param $wrapped
     * @param $view
     */
    public function getTeaserMarker(& $template, & $sims, & $rems, & $wrapped, $view)
    {
        $confArr = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['cal']);
        if ($confArr['useTeaser']) {
            $this->initLocalCObject();
            $this->local_cObj->setCurrentVal($this->getTeaser());
            $sims['###TEASER###'] = $this->local_cObj->cObjGetSingle(
                $this->conf['view.'][$view . '.']['event.']['teaser'],
                $this->conf['view.'][$view . '.']['event.']['teaser.']
            );
        } else {
            $sims['###TEASER###'] = '';
        }
    }

    /**
     * @param $template
     * @param $sims
     * @param $rems
     * @param $wrapped
     * @param $view
     */
    public function getIcsLinkMarker(& $template, & $sims, & $rems, & $wrapped, $view)
    {
        if ((int)$this->conf['view.']['ics.']['showIcsLinks'] === 1) {
            $this->initLocalCObject();
            $uid = $this->getUid();
            if ($this->row['l18n_parent'] > 0) {
                $uid = $this->row['l18n_parent'];
            }
            $this->controller->getParametersForTyposcriptLink($this->local_cObj->data, [
                'type' => $this->getType(),
                'view' => 'single_ics',
                'uid' => $uid
            ], $this->conf['cache'], $this->conf['clearAnyway'], $GLOBALS['TSFE']->id);
            $wrapped['###ICS_LINK###'] = explode(
                '$5&xs2',
                $this->local_cObj->cObjGetSingle(
                    $this->conf['view.'][$view . '.']['event.']['ics'],
                    $this->conf['view.'][$view . '.']['event.']['ics.']
                )
            );
        } else {
            $rems['###ICS_LINK###'] = '';
        }
    }

    /**
     * @param $template
     * @param $sims
     * @param $rems
     * @param $wrapped
     * @param $view
     */
    public function getCategoryMarker(& $template, & $sims, & $rems, & $wrapped, $view)
    {
        $this->initLocalCObject();
        $this->local_cObj->setCurrentVal($this->getCategoriesAsString(false));
        $sims['###CATEGORY###'] = $this->local_cObj->cObjGetSingle(
            $this->conf['view.'][$view . '.']['event.']['category'],
            $this->conf['view.'][$view . '.']['event.']['category.']
        );
    }

    /**
     * @param $template
     * @param $sims
     * @param $rems
     * @param $wrapped
     * @param $view
     */
    public function getCategoryLinkMarker(& $template, & $sims, & $rems, & $wrapped, $view)
    {
        $this->initLocalCObject();
        $this->local_cObj->setCurrentVal($this->getCategoriesAsString());
        $sims['###CATEGORY_LINK###'] = $this->local_cObj->cObjGetSingle(
            $this->conf['view.'][$view . '.']['event.']['category_link'],
            $this->conf['view.'][$view . '.']['event.']['category_link.']
        );
    }

    /**
     * @param $template
     * @param $sims
     * @param $rems
     * @param $wrapped
     * @param $view
     */
    public function getHeaderstyleMarker(& $template, & $sims, & $rems, & $wrapped, $view)
    {
        $sims['###HEADERSTYLE###'] = $this->getHeaderStyle();
    }

    /**
     * @param $template
     * @param $sims
     * @param $rems
     * @param $wrapped
     * @param $view
     */
    public function getBodystyleMarker(& $template, & $sims, & $rems, & $wrapped, $view)
    {
        $sims['###BODYSTYLE###'] = $this->getBodyStyle();
    }

    /**
     * @param $template
     * @param $sims
     * @param $rems
     * @param $wrapped
     * @param $view
     */
    public function getMapMarker(& $template, & $sims, & $rems, & $wrapped, $view)
    {
        $sims['###MAP###'] = '';
    }

    /**
     * @param $template
     * @param $sims
     * @param $rems
     * @param $wrapped
     * @param $view
     */
    public function getAttachmentMarker(& $template, & $sims, & $rems, & $wrapped, $view)
    {
        $sims['###ATTACHMENT###'] = '';
        $tempData = $this->getValuesAsArray();
        $this->initLocalCObject($tempData);
        $sims['###ATTACHMENT###'] = $this->local_cObj->cObjGetSingle(
            $this->conf['view.'][$view . '.']['event.']['attachment'],
            $this->conf['view.'][$view . '.']['event.']['attachment.']
        );
    }

    /**
     * @param $template
     * @param $sims
     * @param $rems
     * @param $wrapped
     * @param $view
     */
    public function getAttachmentUrlMarker(& $template, & $sims, & $rems, & $wrapped, $view)
    {
        $sims['###ATTACHMENT_URL###'] = '';
        $tempData = $this->getValuesAsArray();
        $this->initLocalCObject($tempData);
        $sims['###ATTACHMENT_URL###'] = $this->local_cObj->cObjGetSingle(
            $this->conf['view.'][$view . '.']['event.']['attachment_url'],
            $this->conf['view.'][$view . '.']['event.']['attachment_url.']
        );
    }

    /**
     * @param $template
     * @param $sims
     * @param $rems
     * @param $wrapped
     * @param $view
     */
    public function getEventLinkMarker(& $template, & $sims, & $rems, & $wrapped, $view)
    {
        $eventStart = $this->getStart();
        $wrapped['###EVENT_LINK###'] = explode(
            '$5&xs2',
            $this->getLinkToEvent('$5&xs2', $view, $eventStart->format('Ymd'))
        );
    }

    /**
     * @param $template
     * @param $sims
     * @param $rems
     * @param $wrapped
     * @param $view
     */
    public function getEventUrlMarker(& $template, & $sims, & $rems, & $wrapped, $view)
    {
        $eventStart = $this->getStart();
        $sims['###EVENT_URL###'] = htmlspecialchars($this->getLinkToEvent(
            '$5&xs2',
            $view,
            $eventStart->format('Ymd'),
            true
        ));
    }

    /**
     * @param $template
     * @param $sims
     * @param $rems
     * @param $wrapped
     * @param $view
     */
    public function getAbsoluteEventLinkMarker(& $template, & $sims, & $rems, & $wrapped, $view)
    {
        $eventStart = $this->getStart();
        $wrapped['###ABSOLUTE_EVENT_LINK###'] = explode(
            '$5&xs2',
            $this->getLinkToEvent('$5&xs2', $view, $eventStart->format('Ymd'))
        );
    }

    /**
     * @return mixed
     */
    public function getStartdate()
    {
        $start = $this->getStart();
        return $start->format(Functions::getFormatStringFromConf($this->conf));
    }

    /**
     * @return mixed
     */
    public function getEnddate()
    {
        $end = $this->getEnd();
        return $end->format(Functions::getFormatStringFromConf($this->conf));
    }

    /**
     * @param $template
     * @param $sims
     * @param $rems
     * @param $wrapped
     * @param $view
     */
    public function getEditLinkMarker(& $template, & $sims, & $rems, & $wrapped, $view)
    {
        $eventStart = $this->getStart();
        $sims['###EDIT_LINK###'] = '';

        if ($this->isUserAllowedToEdit()) {
            $linkConf = $this->getValuesAsArray();
            if ($this->conf['view.']['enableAjax']) {
                $temp = sprintf(
                    $this->conf['view.'][$view . '.']['event.']['editLinkOnClick'],
                    $this->getUid(),
                    $this->getType()
                );
                $linkConf['link_ATagParams'] = ' onclick="' . $temp . '"';
            }
            $linkConf['link_no_cache'] = 0;
            //$linkConf['link_useCacheHash'] = 0;
            $linkConf['link_additionalParams'] = '&tx_cal_controller[view]=edit_event&tx_cal_controller[type]=' . $this->getType() . '&tx_cal_controller[uid]=' . $this->getUid() . '&tx_cal_controller[getdate]=' . $eventStart->format('Ymd') . '&tx_cal_controller[lastview]=' . $this->controller->extendLastView();
            $linkConf['link_section'] = 'default';
            $linkConf['link_parameter'] = $this->conf['view.']['event.']['editEventViewPid'] ?: $GLOBALS['TSFE']->id;

            $this->initLocalCObject($linkConf);
            $this->local_cObj->setCurrentVal($this->conf['view.'][$view . '.']['event.']['editIcon']);
            $sims['###EDIT_LINK###'] = $this->local_cObj->cObjGetSingle(
                $this->conf['view.'][$view . '.']['event.']['editLink'],
                $this->conf['view.'][$view . '.']['event.']['editLink.']
            );
        }
        if ($this->isUserAllowedToDelete()) {
            $linkConf = $this->getValuesAsArray();
            if ($this->conf['view.']['enableAjax']) {
                $temp = sprintf(
                    $this->conf['view.'][$view . '.']['event.']['deleteLinkOnClick'],
                    $this->getUid(),
                    $this->getType()
                );
                $linkConf['link_ATagParams'] = ' onclick="' . $temp . '"';
            }
            $linkConf['link_no_cache'] = 0;
            $linkConf['link_additionalParams'] = '&tx_cal_controller[view]=delete_event&tx_cal_controller[type]=' . $this->getType() . '&tx_cal_controller[uid]=' . $this->getUid() . '&tx_cal_controller[getdate]=' . $eventStart->format('Ymd') . '&tx_cal_controller[lastview]=' . $this->controller->extendLastView();
            $linkConf['link_section'] = 'default';
            $linkConf['link_parameter'] = $this->conf['view.']['event.']['deleteEventViewPid'] ?: $GLOBALS['TSFE']->id;

            $this->initLocalCObject($linkConf);
            $this->local_cObj->setCurrentVal($this->conf['view.'][$view . '.']['event.']['deleteIcon']);
            $sims['###EDIT_LINK###'] .= $this->local_cObj->cObjGetSingle(
                $this->conf['view.'][$view . '.']['event.']['deleteLink'],
                $this->conf['view.'][$view . '.']['event.']['deleteLink.']
            );
        }
    }

    /**
     * @param $template
     * @param $sims
     * @param $rems
     * @param $wrapped
     * @param $view
     */
    public function getMoreLinkMarker(& $template, & $sims, & $rems, & $wrapped, $view)
    {
        $sims['###MORE_LINK###'] = '';
        if ($this->conf['preview'] && $this->conf['view.']['event.']['isPreview']) {
            $this->initLocalCObject();
            $this->local_cObj->setCurrentVal($this->controller->pi_getLL('l_more'));

            $this->controller->getParametersForTyposcriptLink($this->local_cObj->data, [
                'page_id' => $GLOBALS['TSFE']->id,
                'preview' => null,
                'view' => 'event',
                'uid' => $this->getUid(),
                'type' => $this->getType()
            ], $this->conf['cache'], $this->conf['clear_anyway'], $this->conf['view.']['event.']['eventViewPid']);

            $sims['###MORE_LINK###'] = $this->local_cObj->cObjGetSingle(
                $this->conf['view.'][$view . '.']['event.']['moreLink'],
                $this->conf['view.'][$view . '.']['event.']['moreLink.']
            );
        }
    }

    /**
     * @param $template
     * @param $sims
     * @param $rems
     * @param $wrapped
     * @param $view
     */
    public function getStartdateMarker(& $template, & $sims, & $rems, & $wrapped, $view)
    {
        $this->getStartAndEndMarker($template, $sims, $rems, $wrapped, $view);
    }

    /**
     * @param $template
     * @param $sims
     * @param $rems
     * @param $wrapped
     * @param $view
     */
    public function getEnddateMarker(& $template, & $sims, & $rems, & $wrapped, $view)
    {
        $this->getStartdateMarker($template, $sims, $rems, $wrapped, $view);
    }

    /**
     * @param $template
     * @param $sims
     * @param $rems
     * @param $wrapped
     * @param $view
     */
    public function getStarttimeMarker(& $template, & $sims, & $rems, & $wrapped, $view)
    {
        $this->getStartdateMarker($template, $sims, $rems, $wrapped, $view);
    }

    /**
     * @param $template
     * @param $sims
     * @param $rems
     * @param $wrapped
     * @param $view
     */
    public function getEndtimeMarker(& $template, & $sims, & $rems, & $wrapped, $view)
    {
        $this->getStartdateMarker($template, $sims, $rems, $wrapped, $view);
    }

    /**
     * @param $template
     * @param $sims
     * @param $rems
     * @param $wrapped
     * @param $view
     */
    public function getDescriptionStriptagsMarker(& $template, & $sims, & $rems, & $wrapped, $view)
    {
        $this->striptags = true;
        $this->getDescriptionMarker($template, $sims, $rems, $wrapped, $view);
        $this->striptags = false;
    }

    /**
     * @param $template
     * @param $sims
     * @param $rems
     * @param $wrapped
     * @param $view
     */
    public function getIconMarker(& $template, & $sims, & $rems, & $wrapped, $view)
    {
        $event_status = strtolower($this->getStatus());
        $confirmed = '';
        if ($event_status !== '') {
            $confirmed = sprintf($this->conf['view.'][$view . '.']['event.']['todoIcon'], $event_status);
        }
        $sims['###ICON###'] = $confirmed;
    }

    /**
     * @param $template
     * @param $sims
     * @param $rems
     * @param $wrapped
     * @param $view
     */
    public function getDescriptionMarker(& $template, & $sims, & $rems, & $wrapped, $view)
    {
        if (($view === 'ics') || ($view === 'single_ics')) {
            $description = preg_replace('/,/', '\,', preg_replace(
                '/' . chr(10) . '|' . chr(13) . '/',
                '\r\n',
                html_entity_decode(preg_replace('/&nbsp;/', ' ', strip_tags($this->getDescription())))
            ));
        } else {
            $description = $this->getDescription();
        }

        $this->initLocalCObject();
        $this->local_cObj->setCurrentVal($description);
        $this->local_cObj->data['bodytext'] = $description;
        if ($this->striptags) {
            $sims['###DESCRIPTION_STRIPTAGS###'] = strip_tags($this->local_cObj->cObjGetSingle(
                $this->conf['view.'][$view . '.'][$this->getObjectType() . '.']['description'],
                $this->conf['view.'][$view . '.'][$this->getObjectType() . '.']['description.']
            ));
        } elseif ($this->isPreview) {
            $sims['###DESCRIPTION###'] = $this->local_cObj->cObjGetSingle(
                $this->conf['view.'][$view . '.'][$this->getObjectType() . '.']['preview'],
                $this->conf['view.'][$view . '.'][$this->getObjectType() . '.']['preview.']
            );
        } else {
            $sims['###DESCRIPTION###'] = $this->local_cObj->cObjGetSingle(
                $this->conf['view.'][$view . '.'][$this->getObjectType() . '.']['description'],
                $this->conf['view.'][$view . '.'][$this->getObjectType() . '.']['description.']
            );
        }
    }

    /**
     * @return array
     */
    public function getAdditionalValuesAsArray(): array
    {
        $values = parent::getAdditionalValuesAsArray();
        $values['event_owner'] = $this->getEventOwner();
        $values['cruser_id'] = $this->getCreateUserId();
        if ($this->conf['view.']['enableAjax']) {
            $template = '';
            $sims = [];
            $rems = [];
            $wrapped = [];
            $this->getEventUrlMarker($template, $sims, $rems, $wrapped, 'event');
            $values['event_link'] = $wrapped['###EVENT_URL###'];
        }
        return $values;
    }

    /**
     * @param string $feUserUid
     * @param array $feGroupsArray
     * @return bool
     */
    public function isUserAllowedToEdit($feUserUid = '', $feGroupsArray = []): bool
    {
        $rightsObj = &Registry::Registry('basic', 'rightscontroller');

        if (!$rightsObj->isViewEnabled('edit_event')) {
            return false;
        }

        if ($rightsObj->isCalAdmin()) {
            return true;
        }
        $editOffset = $this->conf['rights.']['edit.']['event.']['timeOffset'] * 60;

        if ($feUserUid === '') {
            $feUserUid = $rightsObj->getUserId();
        }
        if (empty($feGroupsArray)) {
            $feGroupsArray = $rightsObj->getUserGroups();
        }
        $isEventOwner = $this->isEventOwner($feUserUid, $feGroupsArray);
        $isSharedUser = $this->isSharedUser($feUserUid, $feGroupsArray);
        if ($rightsObj->isAllowedToEditStartedEvent()) {
            $eventHasntStartedYet = true;
        } else {
            $temp = new CalendarDateTime();
            $temp->setTZbyID('UTC');
            $temp->addSeconds($editOffset);
            $eventStart = $this->getStart();
            $eventHasntStartedYet = $eventStart->after($temp);
        }
        $isAllowedToEditEvent = $rightsObj->isAllowedToEditEvent();
        $isAllowedToEditOwnEventsOnly = $rightsObj->isAllowedToEditOnlyOwnEvent();
        $isPublicAllowed = $rightsObj->isPublicAllowedToEditEvents();

        if ($isAllowedToEditOwnEventsOnly) {
            return ($isEventOwner || $isSharedUser) && ($eventHasntStartedYet || $rightsObj->isAllowedToEditEventInPast());
        }
        return $isAllowedToEditEvent && ($isEventOwner || $isSharedUser || $isPublicAllowed) && ($eventHasntStartedYet || $rightsObj->isAllowedToEditEventInPast());
    }

    /**
     * @param string $feUserUid
     * @param array $feGroupsArray
     * @return bool
     */
    public function isUserAllowedToDelete($feUserUid = '', $feGroupsArray = []): bool
    {
        /** @var RightsService $rightsObj */
        $rightsObj = &Registry::Registry('basic', 'rightscontroller');
        if (!$rightsObj->isViewEnabled('delete_event')) {
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
        $isEventOwner = $this->isEventOwner($feUserUid, $feGroupsArray);
        $isSharedUser = $this->isSharedUser($feUserUid, $feGroupsArray);
        if ($rightsObj->isAllowedToDeleteStartedEvents()) {
            $eventHasntStartedYet = true;
        } else {
            $temp = new CalendarDateTime();
            $temp->setTZbyID('UTC');
            $temp->addSeconds();
            $eventStart = $this->getStart();
            $eventHasntStartedYet = $eventStart->after($temp);
        }
        $isAllowedToDeleteEvents = $rightsObj->isAllowedToDeleteEvents();
        $isAllowedToDeleteOwnEventsOnly = $rightsObj->isAllowedToDeleteOnlyOwnEvents();

        if ($isAllowedToDeleteOwnEventsOnly) {
            return ($isEventOwner || $isSharedUser) && ($eventHasntStartedYet || $rightsObj->isAllowedToDeleteEventInPast());
        }
        return $isAllowedToDeleteEvents && ($isEventOwner || $isSharedUser) && ($eventHasntStartedYet || $rightsObj->isAllowedToDeleteEventInPast());
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return 'Phpicalendar ' . (is_object($this) ? 'object' : 'something') . ': ' . implode(',', $this->row);
    }

    /**
     * @return array
     */
    public function getAttendees(): array
    {
        return $this->attendees;
    }

    /**
     * @param $template
     * @param $sims
     * @param $rems
     * @param $wrapped
     * @param $view
     */
    public function getAttendeeMarker(& $template, & $sims, & $rems, & $wrapped, $view)
    {
        $sims['###ATTENDEE###'] = '';
        /** @var RightsService $rightsObj */
        $rightsObj = &Registry::Registry('basic', 'rightscontroller');
        $cObj = &Registry::Registry('basic', 'cobj');
        $globalAttendeeArray = $this->getAttendees();

        $isChairMan = false;
        foreach ($globalAttendeeArray as $serviceKey => $attendeeArray) {
            foreach ($attendeeArray as $attendee) {
                /** @var AttendeeModel $attendee */
                if ($attendee->getAttendance() === 'CHAIR' && $rightsObj->getUserId() === $attendee->getFeUserId()) {
                    $isChairMan = true;
                    break;
                }
            }
        }

        if (in_array($view, [
            'ics',
            'ics_single'
        ])) {
            if (!empty($globalAttendeeArray)) {
                foreach ($globalAttendeeArray as $serviceType => $attendeeArray) {
                    foreach ($attendeeArray as $attendee) {
                        /** @var AttendeeModel $attendee */
                        if ($attendee->getAttendance() === 'CHAIR') {
                            $sims['###ORGANIZER###'] = 'ORGANIZER;ROLE=' . $attendee->getAttendance() . ':MAILTO:' . $attendee->getEmail();
                        }
                        if ($this->limitAttendeeToThisEmail !== '' && $attendee->getEmail() !== $this->limitAttendeeToThisEmail) {
                            continue;
                        }
                        if ($attendee->getStatus() === 0) {
                            $attendee->setStatus('NEEDS-ACTION');
                        }
                        $sims['###ATTENDEE###'] .= 'ATTENDEE;ROLE=' . $attendee->getAttendance() . ';PARTSTAT=' . $attendee->getStatus() . ';RSVP=TRUE:MAILTO:' . $attendee->getEmail();
                    }
                }
            }
        } elseif (!empty($globalAttendeeArray) && $rightsObj->isLoggedIn()) {
            $formattedArray = [];
            $partOf = false;
            foreach ($globalAttendeeArray as $serviceKey => $attendeeArray) {
                foreach ($attendeeArray as $attendee) {
                    if ($attendee->getFeUserId()) {
                        $result = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
                            '*',
                            'fe_users',
                            'pid in (' . $this->conf['pidList'] . ')' . $cObj->enableFields('fe_users') . ' AND uid =' . $attendee->getFeUserId()
                        );
                        if ($result) {
                            while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
                                $this->initLocalCObject($row);
                                $displayConfig = $this->conf['view.'][$view . '.']['event.']['attendeeFeUserDisplayName'] ? 'attendeeFeUserDisplayName' : 'defaultFeUserDisplayName';
                                $attendee->setName($this->local_cObj->cObjGetSingle(
                                    $this->conf['view.'][$view . '.']['event.'][$displayConfig],
                                    $this->conf['view.'][$view . '.']['event.'][$displayConfig . '.']
                                ));
                                $attendee->setEmail($row['email']);
                            }
                            $GLOBALS['TYPO3_DB']->sql_free_result($result);
                        }
                        $finalString = $attendee->getName() . ' ';
                    } else {
                        $finalString = $attendee->getEmail() . ' ';
                    }

                    if ($attendee->getAttendance() === 'CHAIR') {
                        $finalString .= sprintf(
                            $this->conf['view.'][$view . '.']['event.']['attendeeIcon'],
                            $attendee->getAttendance(),
                            $this->controller->pi_getLL('l_event_attendee_' . $attendee->getAttendance()),
                            $this->controller->pi_getLL('l_event_attendee_' . $attendee->getAttendance())
                        );
                    } else {
                        $finalString .= sprintf(
                            $this->conf['view.'][$view . '.']['event.']['attendeeIcon'],
                            $attendee->getStatus(),
                            $this->controller->pi_getLL('l_event_attendee_' . $attendee->getStatus()),
                            $this->controller->pi_getLL('l_event_attendee_' . $attendee->getStatus())
                        );
                    }
                    if ($isChairMan || $rightsObj->getUserId() === $attendee->getFeUserId()) {
                        $partOf = true;
                        $this->initLocalCObject($this->getValuesAsArray());
                        if ($attendee->getAttendance() !== 'CHAIR') {
                            $finalString .= $this->controller->pi_getLL('l_meeting_changestatus');
                        }
                        if ($attendee->getAttendance() !== 'CHAIR' && ($attendee->getStatus() === 'ACCEPTED' || $attendee->getStatus() === '0' || $attendee->getStatus() === 'NEEDS-ACTION')) {
                            $this->local_cObj->setCurrentVal(sprintf(
                                $this->conf['view.'][$view . '.']['event.']['attendeeIcon'],
                                'DECLINE',
                                $this->controller->pi_getLL('l_meeting_decline'),
                                $this->controller->pi_getLL('l_meeting_decline')
                            ));
                            $this->controller->getParametersForTyposcriptLink(
                                $this->local_cObj->data,
                                [
                                    'view' => 'meeting',
                                    'attendee' => $attendee->getUid(),
                                    'uid' => $this->getUid(),
                                    'status' => 'decline',
                                    'sid' => md5($this->getUid() . $attendee->getEmail() . $attendee->row['crdate'])
                                ],
                                $this->conf['cache'],
                                $this->conf['clear_anyway'],
                                $this->conf['view.']['event.']['meeting.']['statusViewPid']
                            );
                            $finalString .= $this->local_cObj->cObjGetSingle(
                                $this->conf['view.'][$view . '.']['event.']['declineMeetingLink'],
                                $this->conf['view.'][$view . '.']['event.']['declineMeetingLink.']
                            );
                        }
                        if ($attendee->getAttendance() !== 'CHAIR' && ($attendee->getStatus() === 'DECLINE' || $attendee->getStatus() === '0' || $attendee->getStatus() === 'NEEDS-ACTION')) {
                            $this->local_cObj->setCurrentVal(sprintf(
                                $this->conf['view.'][$view . '.']['event.']['attendeeIcon'],
                                'ACCEPTED',
                                $this->controller->pi_getLL('l_meeting_accept'),
                                $this->controller->pi_getLL('l_meeting_accept')
                            ));
                            $this->controller->getParametersForTyposcriptLink(
                                $this->local_cObj->data,
                                [
                                    'view' => 'meeting',
                                    'attendee' => $attendee->getUid(),
                                    'uid' => $this->getUid(),
                                    'status' => 'accept',
                                    'sid' => md5($this->getUid() . $attendee->getEmail() . $attendee->row['crdate'])
                                ],
                                $this->conf['cache'],
                                $this->conf['clear_anyway'],
                                $this->conf['view.']['event.']['meeting.']['statusViewPid']
                            );
                            $finalString .= $this->local_cObj->cObjGetSingle(
                                $this->conf['view.'][$view . '.']['event.']['acceptMeetingLink'],
                                $this->conf['view.'][$view . '.']['event.']['acceptMeetingLink.']
                            );
                        }
                    }
                    $formattedArray[] = $finalString;
                }
            }
            if ($partOf) {
                $this->initLocalCObject();
                $this->local_cObj->setCurrentVal(implode('<br/>', $formattedArray));
                $sims['###ATTENDEE###'] = $this->local_cObj->cObjGetSingle(
                    $this->conf['view.'][$view . '.']['event.']['attendee'],
                    $this->conf['view.'][$view . '.']['event.']['attendee.']
                );
            }
        }
    }

    /**
     * @param $linktext
     * @param $view
     * @param $date
     * @param bool $urlOnly
     * @return string
     */
    public function getLinkToEvent($linktext, $view, $date, $urlOnly = false): string
    {
        /* new */
        if ($linktext === '') {
            $linktext = $this->controller->pi_getLL('l_no_title');
        }
        $rightsObj = &Registry::Registry('basic', 'rightscontroller');

        if (
            $this->getEventType() === Model::EVENT_TYPE_SHORTCUT
            || $this->getEventType() === Model::EVENT_TYPE_EXTERNAL
            || $rightsObj->isViewEnabled($this->getObjectType())
            || $this->conf['view.'][$this->getObjectType() . '.'][$this->getObjectType() . 'ViewPid']
        ) {
            $this->initLocalCObject($this->getValuesAsArray());
            $this->local_cObj->setCurrentVal($linktext);

            if (!$this->conf['view.'][$view . '.'][$this->getObjectType() . '.'][$this->getObjectType() . 'Link']) {
                $view = $this->getObjectType();
            }

            // create the link if the event points to a page or external URL
            // determine the link type
            switch ($this->getEventType()) {
                // shortcut to page - create the link
                case Model::EVENT_TYPE_SHORTCUT:
                    $this->local_cObj->data['link_parameter'] = $this->page;
                    break;
                // external url
                case Model::EVENT_TYPE_EXTERNAL:
                    $this->local_cObj->data['link_parameter'] = $this->ext_url;
                    $param = $this->page;
                    break;
                // regular event or custom type
                default:
                    $linkParams = [
                        'page_id' => $GLOBALS['TSFE']->id,
                        'getdate' => $date,
                        'view' => $this->getObjectType(),
                        'type' => $this->getType(),
                        'uid' => $this->getUid()
                    ];
                    if ($this->conf['view.'][$this->getObjectType() . '.']['isPreview']) {
                        $linkParams['preview'] = 1;
                    }
                    $pid = $this->conf['view.'][$this->getObjectType() . '.'][$this->getObjectType() . 'ViewPid'];
                    $categories = &$this->getCategories();
                    if (is_array($categories) && count($categories)) {
                        foreach ($this->getCategories() as $category) {
                            /** @var CategoryModel $category */
                            if (is_object($category) && $category->getSinglePid()) {
                                $pid = $category->getSinglePid();
                                break;
                            }
                        }
                    }

                    if (!$this->isClone()) {
                        $linkParams['lastview'] = $this->controller->extendLastView([
                            'getdate' => $this->conf['getdate']
                        ]);
                    }
                    $this->controller->getParametersForTyposcriptLink(
                        $this->local_cObj->data,
                        $linkParams,
                        $this->conf['cache'],
                        $this->conf['clear_anyway'],
                        $pid
                    );
                    break;
            }

            if ($urlOnly) {
                return $this->local_cObj->cObjGetSingle(
                    $this->conf['view.'][$view . '.'][$this->getObjectType() . '.'][$this->getObjectType() . 'Url'],
                    $this->conf['view.'][$view . '.'][$this->getObjectType() . '.'][$this->getObjectType() . 'Url.']
                );
            }

            // create & return the link
            $this->local_cObj->data['link'] = $param;
            return $this->local_cObj->cObjGetSingle(
                $this->conf['view.'][$view . '.'][$this->getObjectType() . '.'][$this->getObjectType() . 'Link'],
                $this->conf['view.'][$view . '.'][$this->getObjectType() . '.'][$this->getObjectType() . 'Link.']
            );
        }
        return $linktext;
    }

    /**
     * @param $template
     * @param $sims
     * @param $rems
     * @param $wrapped
     * @param $view
     */
    public function getEventIdMarker(& $template, & $sims, & $rems, & $wrapped, $view)
    {
        $start = $this->getStart();
        $sims['###EVENT_ID###'] = $this->getType() . $this->getUid() . $start->format('YmdHi');
    }

    /**
     * @param $template
     * @param $sims
     * @param $rems
     * @param $wrapped
     * @param $view
     */
    public function getGuidMarker(& $template, & $sims, & $rems, & $wrapped, $view)
    {
        if ($this->row['icsUid'] !== '') {
            $sims['###GUID###'] = $this->row['icsUid'];
        } else {
            $eventArray = ['calendar_id' => $this->getCalendarId(), 'uid' => $this->getUid()];
            $sims['###GUID###'] = Functions::getIcsUid($this->conf, $eventArray);
        }
    }

    /**
     * @param $template
     * @param $sims
     * @param $rems
     * @param $wrapped
     * @param $view
     */
    public function getDtstampMarker(& $template, & $sims, & $rems, & $wrapped, $view)
    {
        $sims['###DTSTAMP###'] = 'DTSTAMP:' . gmdate('Ymd', $this->getCrdate()) . 'T' . gmdate(
            'His',
            $this->getCrdate()
            );
    }

    /**
     * @param $template
     * @param $sims
     * @param $rems
     * @param $wrapped
     * @param $view
     */
    public function getDtstartYearMonthDayHourMinuteMarker(& $template, & $sims, & $rems, & $wrapped, $view)
    {
        $eventStart = $this->getStart();
        if ($this->isAllday()) {
            $sims['###DTSTART_YEAR_MONTH_DAY_HOUR_MINUTE###'] = 'DTSTART;VALUE=DATE:' . $eventStart->format('Ymd');
        } elseif ($this->conf['view.']['ics.']['timezoneId'] !== '') {
            $sims['###DTSTART_YEAR_MONTH_DAY_HOUR_MINUTE###'] = 'DTSTART;TZID=' . $this->conf['view.']['ics.']['timezoneId'] . ':' . $eventStart->format('YmdTHis');
        } else {
            $offset = Functions::strtotimeOffset($eventStart->format('U'));
            $eventStart->subtractSeconds($offset);
            $sims['###DTSTART_YEAR_MONTH_DAY_HOUR_MINUTE###'] = 'DTSTART:' . $eventStart->format('YmdTHisZ');
            $eventStart->addSeconds($offset);
        }
    }

    /**
     * @param $template
     * @param $sims
     * @param $rems
     * @param $wrapped
     * @param $view
     */
    public function getDtendYearMonthDayHourMinuteMarker(& $template, & $sims, & $rems, & $wrapped, $view)
    {
        $eventEnd = $this->getEnd();
        if ($this->isAllDay()) {
            $eventEnd->addSeconds(84600);
            $sims['###DTEND_YEAR_MONTH_DAY_HOUR_MINUTE###'] = 'DTEND;VALUE=DATE:' . $eventEnd->format('Ymd');
        } elseif ($this->conf['view.']['ics.']['timezoneId'] !== '') {
            $sims['###DTEND_YEAR_MONTH_DAY_HOUR_MINUTE###'] = 'DTEND;TZID=' . $this->conf['view.']['ics.']['timezoneId'] . ':' . $eventEnd->format('YmdTHMS');
        } else {
            $offset = Functions::strtotimeOffset($eventEnd->format('U'));
            $eventEnd->subtractSeconds($offset);
            $sims['###DTEND_YEAR_MONTH_DAY_HOUR_MINUTE###'] = 'DTEND:' . $eventEnd->format('YmdTHisZ');
            $eventEnd->addSeconds($offset);
        }
    }

    /**
     * @param $template
     * @param $sims
     * @param $rems
     * @param $wrapped
     * @param $view
     */
    public function getRRuleMarker(&$template, &$sims, &$rems, &$wrapped, $view)
    {
        $sims['###RRULE###'] = '';
        $rrule = $this->getRRule($this);
        if ($rrule) {
            $sims['###RRULE###'] = 'RRULE:' . $rrule;
        }
    }

    /**
     * @param EventModel $event
     * @return string
     */
    public function getRRule(&$event): string
    {
        $rrule = '';
        $allowedValues = [
            'second',
            'minute',
            'hour',
            'day',
            'week',
            'month',
            'year'
        ];
        if (in_array($event->getFreq(), $allowedValues, true)) {
            $rruleConfiguration = [];
            $rruleConfiguration['FREQ'] = 'FREQ=' . $event->getIcsFreqLabel($event->getFreq());
            $rruleConfiguration['INTERVAL'] = 'INTERVAL=' . $event->getInterval();

            if ($event->getCount() !== 0) {
                $rruleConfiguration['COUNT'] = 'COUNT=' . $event->getCount();
            }
            if (count($event->getByDay()) > 0) {
                $byDay = [];
                foreach ($event->getByDay() as $day) {
                    $byDay[] = $day;
                }
                $rruleConfiguration['BYDAY'] = 'BYDAY=' . implode(',', $byDay);
            }
            if (count($event->getByWeekNo()) > 0) {
                $byWeekNo = [];
                foreach ($event->getByWeekNo() as $week) {
                    $byWeekNo[] = $week;
                }
                $rruleConfiguration['BYWEEKNO'] = 'BYWEEKNO=' . implode(',', $byWeekNo);
            }
            if (count($event->getByMonth()) > 0) {
                $byMonth = [];
                foreach ($event->getByMonth() as $month) {
                    $byMonth[] = $month;
                }
                $rruleConfiguration['BYMONTH'] = 'BYMONTH=' . implode(',', $byMonth);
            }
            if (count($event->getByYearDay()) > 0) {
                $byYearDay = [];
                foreach ($event->getByYearDay() as $yearday) {
                    $byYearDay[] = $yearday;
                }
                $rruleConfiguration['BYYEARDAY'] = 'BYYEARDAY=' . implode(',', $byYearDay);
            }
            if (count($event->getByMonthDay()) > 0) {
                $byMonthDay = [];
                foreach ($event->getByMonthDay() as $monthday) {
                    $byMonthDay[] = $monthday;
                }
                $rruleConfiguration['BYMONTHDAY'] = 'BYMONTHDAY=' . implode(',', $byMonthDay);
            }
            if (count($event->getByWeekDay()) > 0) {
                $byWeekDay = [];
                foreach ($event->getByWeekDay() as $weekday) {
                    $byWeekDay[] = $weekday;
                }
                $rruleConfiguration['BYWEEKDAY'] = 'BYWEEKDAY=' . implode(',', $byWeekDay);
            }
            /** @var CalendarDateTime $until */
            $until = $event->getUntil();
            if (is_object($until) && $until->format('Ymd') > 19700101) {
                $eventEnd = $this->getEnd();
                $offset = Functions::strtotimeOffset($eventEnd->format('U'));
                $eventEnd->subtractSeconds($offset);
                $rruleConfiguration['UNTIL'] = 'UNTIL=' . $until->format('YmdT') . $eventEnd->format('HisZ');
                $eventEnd->addSeconds($offset);
            }
            $rrule = implode(';', $rruleConfiguration);
        }
        return strtoupper($rrule);
    }

    /**
     * @param $template
     * @param $sims
     * @param $rems
     * @param $wrapped
     * @param $view
     */
    public function getRdateMarker(&$template, &$sims, &$rems, &$wrapped, $view)
    {
        $sims['###RDATE###'] = '';
        if ($this->getRdateType() !== '' && $this->getRdateType() !== 'none' && $this->getRdate() != '') {
            $sims['###RDATE###'] = 'RDATE;VALUE=' . strtoupper($this->getRdateType() . ':' . $this->getRdate());
        }
    }

    /**
     * @param $template
     * @param $sims
     * @param $rems
     * @param $wrapped
     * @param $view
     */
    public function getExdateMarker(& $template, & $sims, & $rems, & $wrapped, $view)
    {
        $sims['###EXDATE###'] = '';
        $exceptionDates = [];
        $daySeconds = $this->getStart()->getHour() * 3600 + $this->getStart()->getMinute() * 60;
        $offset = $daySeconds - Functions::strtotimeOffset($this->getStart()->format('U'));
        $exceptionEventStart = new CalendarDateTime();
        /** @var EventModel $exceptionEvent */
        foreach ($this->getExceptionEvents() as $exceptionEvent) {
            $exceptionEventStart->copy($exceptionEvent->getStart());
            $exceptionEventStart->addSeconds($offset);
            $exceptionDates[] = 'EXDATE:' . $exceptionEventStart->format('YmdTHisZ');
        }

        if (count($exceptionDates)) {
            $sims['###EXDATE###'] = implode(LF, $exceptionDates);
        }
    }

    /**
     * @param $template
     * @param $sims
     * @param $rems
     * @param $wrapped
     * @param $view
     */
    public function getExruleMarker(& $template, & $sims, & $rems, & $wrapped, $view)
    {
        $sims['###EXRULE###'] = '';
    }

    /**
     * @param $eventFreq
     * @return string
     */
    public function getIcsFreqLabel($eventFreq): string
    {
        $freq_type = '';
        switch ($eventFreq) {
            case 'year':
                $freq_type = 'YEARLY';
                break;
            case 'month':
                $freq_type = 'MONTHLY';
                break;
            case 'week':
                $freq_type = 'WEEKLY';
                break;
            case 'day':
                $freq_type = 'DAILY';
                break;
            case 'hour':
                $freq_type = 'HOURLY';
                break;
            case 'minute':
                $freq_type = 'MINUTELY';
                break;
            case 'second':
                $freq_type = 'SECONDLY';
                break;
        }
        return $freq_type;
    }

    /**
     * @param $template
     * @param $sims
     * @param $rems
     * @param $wrapped
     * @param $view
     */
    public function getCruserNameMarker(& $template, & $sims, & $rems, & $wrapped, $view)
    {
        $modelObj = &Registry::Registry('basic', 'modelcontroller');
        $sims['###CRUSER_NAME###'] = '';
        $feUser = $modelObj->findFeUser($this->getCreateUserId());
        if (is_array($feUser)) {
            $this->initLocalCObject();
            $this->local_cObj->setCurrentVal($feUser[$this->conf['view.'][$view . '.']['event.']['cruser_name.']['db_field']]);
            $sims['###CRUSER_NAME###'] = $this->local_cObj->cObjGetSingle(
                $this->conf['view.'][$view . '.']['event.']['cruser_name'],
                $this->conf['view.'][$view . '.']['event.']['cruser_name.']
            );
        }
    }

    /**
     * @param $template
     * @param $sims
     * @param $rems
     * @param $wrapped
     * @param $view
     */
    public function getCalendarTitleMarker(& $template, & $sims, & $rems, & $wrapped, $view)
    {
        $sims['###CALENDAR_TITLE###'] = '';
        $calendarObject = &$this->getCalendarObject();
        $this->initLocalCObject($calendarObject->getValuesAsArray());
        $this->local_cObj->setCurrentVal($calendarObject->getTitle());
        $sims['###CALENDAR_TITLE###'] = $this->local_cObj->cObjGetSingle(
            $this->conf['view.'][$view . '.']['event.']['calendar_title'],
            $this->conf['view.'][$view . '.']['event.']['calendar_title.']
        );
    }

    /**
     * @param $template
     * @param $sims
     * @param $rems
     * @param $wrapped
     * @param $view
     */
    public function getTopMarker(& $template, & $sims, & $rems, & $wrapped, $view)
    {
        $sims['###TOP###'] = '';
        $this->initLocalCObject();
        $this->local_cObj->setCurrentVal($this->getStart()->getHour() + $this->getStart()->getMinute() / 60 - $this->getStartOffset());
        $sims['###TOP###'] = $this->local_cObj->cObjGetSingle(
            $this->conf['view.'][$view . '.']['event.']['top'],
            $this->conf['view.'][$view . '.']['event.']['top.']
        );
    }

    /**
     * @return float|int
     */
    public function getStartOffset()
    {
        if (!isset($this->startOffset)) {
            $dayStart = $this->conf['view.']['day.']['dayStart']; // '0700'; // Start time for day grid
            while (strlen($dayStart) < 6) {
                $dayStart .= '0';
            }
            $d_start = new CalendarDateTime('20000101' . $dayStart);
            $this->startOffset = $d_start->getHour() + $d_start->getMinute() / 60;
        }
        return $this->startOffset;
    }

    /**
     * @param $template
     * @param $sims
     * @param $rems
     * @param $wrapped
     * @param $view
     */
    public function getLengthMarker(& $template, & $sims, & $rems, & $wrapped, $view)
    {
        $sims['###LENGTH###'] = '';
        $this->initLocalCObject();
        $this->local_cObj->setCurrentVal($this->getEnd()->format('U') - $this->getStart()->format('U'));
        $sims['###LENGTH###'] = $this->local_cObj->cObjGetSingle(
            $this->conf['view.'][$view . '.']['event.']['length'],
            $this->conf['view.'][$view . '.']['event.']['length.']
        );
    }

    /**
     * @return CalendarDateTime
     */
    public function getNow(): CalendarDateTime
    {
        $now = new CalendarDateTime();
        $now->setTZbyID('UTC');
        return $now;
    }

    /**
     * @return CalendarDateTime
     */
    public function getToday(): CalendarDateTime
    {
        $today = new CalendarDateTime();
        $today->setTZbyID('UTC');
        $today->setHour(0);
        $today->setMinute(0);
        $today->setSecond(0);
        return $today;
    }

    /**
     * @param $sendOut
     */
    public function setSendOutInvitation($sendOut)
    {
        $this->sendOutInvitation = $sendOut;
    }

    /**
     * @return bool
     */
    public function getSendOutInvitation(): bool
    {
        return $this->sendOutInvitation;
    }

    /**
     * @param $template
     * @param $sims
     * @param $rems
     * @param $wrapped
     * @param $view
     */
    public function getCreatedMarker(& $template, & $sims, & $rems, & $wrapped, $view)
    {
        $sims['###CREATED###'] = 'CREATED:' . gmdate('Ymd', $this->getCrdate()) . 'T' . gmdate(
            'His',
            $this->getCrdate()
            ) . 'Z';
    }

    /**
     * @param $template
     * @param $sims
     * @param $rems
     * @param $wrapped
     * @param $view
     */
    public function getLastModifiedMarker(& $template, & $sims, & $rems, & $wrapped, $view)
    {
        $sims['###LAST_MODIFIED###'] = 'LAST_MODIFIED:' . gmdate('Ymd', $this->getTstamp()) . 'T' . gmdate(
            'His',
            $this->getTstamp()
            ) . 'Z';
    }

    /**
     * @return string
     */
    public function getAjaxEditLink(): string
    {
        if ($this->conf['view.']['enableAjax'] && $this->isUserAllowedToEdit()) {
            return 'dragZones[\'dragZone' . $this->getUid() . '\'] = new CalEvent.dd.MyDragZone(' . '\'cal_event_' . $this->getUid() . '\',' . '{ddGroup: \'cal_event\',' . 'scroll: false,' . 'start_time:\'' . ($this->getStart()->getHour() * 3600 + $this->getStart()->getMinute() * 60) . '\',' . 'start_day:\'' . $this->getStart()->format('Ymd') . '\',' . 'end_time:\'' . ($this->getEnd()->getHour() * 3600 + $this->getEnd()->getMinute() * 60) . '\',' . 'end_day:\'' . $this->getEnd()->format('Ymd') . '\',' . 'uid:\'' . $this->getUid() . '\',' . 'eventType:\'' . $this->getType() . '\'});';
        }
        return '';
    }

    /**
     * @param $template
     * @param $sims
     * @param $rems
     * @param $wrapped
     * @param $view
     */
    public function getCategoryIconMarker(& $template, & $sims, & $rems, & $wrapped, $view)
    {
        $this->initLocalCObject();
        $arr = &$this->getCategories();
        foreach ($arr as $cat) {
            /** @var CategoryModel $cat */
            $icon = $cat->getIcon();
            if ($icon) {
                $search = [
                    '%%%CATICON%%%',
                    '%%%CATTITLE%%%',
                    '%%%EVENTTITLE%%%',
                    '%%%STARTDATE%%%',
                    '%%%STARTTIME%%%'
                ];

                $eventStart = $this->getStart();
                // Startdate
                $this->local_cObj->setCurrentVal($eventStart->format($this->conf['view.'][$view . '.']['event.']['dateFormat']));
                $startdate = $this->local_cObj->cObjGetSingle(
                    $this->conf['view.'][$view . '.']['event.']['startdate'],
                    $this->conf['view.'][$view . '.']['event.']['startdate.']
                );

                // Starttime
                $this->local_cObj->setCurrentVal($eventStart->format($this->conf['view.'][$view . '.']['event.']['timeFormat']));
                $starttime = $this->local_cObj->cObjGetSingle(
                    $this->conf['view.'][$view . '.']['event.']['starttime'],
                    $this->conf['view.'][$view . '.']['event.']['starttime.']
                );

                $replace = [
                    $icon,
                    $cat->getTitle(),
                    $this->getTitle(),
                    $startdate,
                    $starttime
                ];
                $sims['###CATEGORY_ICON###'] .= str_replace(
                    $search,
                    $replace,
                    $this->conf['view.'][$view . '.']['event.']['categoryIcon']
                );
            } else {
                $sims['###CATEGORY_ICON###'] .= $this->conf['view.'][$view . '.']['event.']['categoryIconDefault'];
            }
        }
    }
}
