<?php

/*
 * This file is part of the web-tp3/cal.
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace TYPO3\CMS\Cal\Controller;

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
use PDO;
use TYPO3\CMS\Cal\Model\AttendeeModel;
use TYPO3\CMS\Cal\Model\CalendarDateTime;
use TYPO3\CMS\Cal\Model\CategoryModel;
use TYPO3\CMS\Cal\Model\EventModel;
use TYPO3\CMS\Cal\Model\Location;
use TYPO3\CMS\Cal\Model\Organizer;
use TYPO3\CMS\Cal\Service\AttendeeService;
use TYPO3\CMS\Cal\Service\CalculateDateTimeService;
use TYPO3\CMS\Cal\Service\CalendarService;
use TYPO3\CMS\Cal\Service\EventService;
use TYPO3\CMS\Cal\Service\LocationService;
use TYPO3\CMS\Cal\Service\OrganizerService;
use TYPO3\CMS\Cal\Service\SysCategoryService;
use TYPO3\CMS\Cal\Utility\Functions;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Back controller for the calendar base.
 * Takes requests from the main
 * controller and starts processing in the appropriate calendar models by
 * utilizing TYPO3 services.
 */
class ModelController extends BaseController
{
    /** @var ConnectionPool $connectionPool */
    public $connectionPool;

    private $todoSubtype;

    public function __construct()
    {
        parent::__construct();
        $confArr = unserialize($GLOBALS ['TYPO3_CONF_VARS'] ['EXT'] ['extConf'] ['cal']);
        $this->todoSubtype = $confArr ['todoSubtype'];

        $this->connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);
    }

    /**
     * @param int $uid
     * @param string $type
     * @param string $pidList
     * @param bool $showHiddenEvents
     * @param bool $showDeletedEvents
     * @param bool $getAllInstances
     * @param bool $disableCalendarSearchString
     * @param bool $disableCategorySearchString
     * @param string $eventType
     * @return EventModel
     */
    public function findEvent(
        $uid,
        $type = 'tx_cal_phpicalendar',
        $pidList = '',
        $showHiddenEvents = false,
        $showDeletedEvents = false,
        $getAllInstances = false,
        $disableCalendarSearchString = false,
        $disableCategorySearchString = false,
        $eventType = '0,1,2,3'
    ): EventModel {
        if ($type === '') {
            $type = 'tx_cal_phpicalendar';
        } elseif ($type === 'tx_cal_preview') {
            $type = 'tx_cal_phpicalendar';
            $showHiddenEvents = true;
            $getAllInstances = true;
        }
        $event = $this->find(
            'cal_event_model',
            $uid,
            $type,
            'event',
            $pidList,
            $showHiddenEvents,
            $showDeletedEvents,
            $getAllInstances,
            $disableCalendarSearchString,
            $disableCategorySearchString,
            $eventType
        );
        return $event;
    }

    /**
     * @param int $uid
     * @param string $type
     * @param string $pidList
     * @param bool $showHiddenEvents
     * @param bool $showDeletedEvents
     * @param bool $getAllInstances
     * @param bool $disableCalendarSearchString
     * @param bool $disableCategorySearchString
     * @param string $eventType
     * @return EventModel
     */
    public function findTodo(
        $uid,
        $type = 'tx_cal_todo',
        $pidList = '',
        $showHiddenEvents = false,
        $showDeletedEvents = false,
        $getAllInstances = false,
        $disableCalendarSearchString = false,
        $disableCategorySearchString = false,
        $eventType = '0,1,2,3'
    ): EventModel {
        $event = $this->find(
            'cal_event_model',
            $uid,
            $type,
            $this->todoSubtype,
            $pidList,
            $showHiddenEvents,
            $showDeletedEvents,
            $getAllInstances,
            $disableCalendarSearchString,
            $disableCategorySearchString,
            $eventType
        );
        return $event;
    }

    /**
     * @param $type
     * @return EventModel
     */
    public function createEvent($type): EventModel
    {
        $event = $this->create('cal_event_model', $type, 'event');
        return $event;
    }

    /**
     * @param int $uid
     * @param string $type
     * @param string $pidList
     * @param bool $showHiddenEvents
     * @param bool $showDeletedEvents
     * @param bool $getAllInstances
     * @param bool $disableCalendarSearchString
     * @param bool $disableCategorySearchString
     * @param string $eventType
     * @return EventModel
     */
    public function findAllEventInstances(
        $uid,
        $type = '',
        $pidList = '',
        $showHiddenEvents = false,
        $showDeletedEvents = false,
        $getAllInstances = false,
        $disableCalendarSearchString = false,
        $disableCategorySearchString = false,
        $eventType = '0,1,2,3'
    ): EventModel {
        $event_s = $this->find(
            'cal_event_model',
            $uid,
            $type,
            'event',
            $pidList,
            $showHiddenEvents,
            $showDeletedEvents,
            $getAllInstances,
            $disableCalendarSearchString,
            $disableCategorySearchString,
            $eventType
        );
        return $event_s;
    }

    /**
     * @param int $uid
     * @param $type
     * @param string $pid
     * @return EventModel
     * @throws \TYPO3\CMS\Core\Exception
     */
    public function saveEvent($uid, $type, $pid = ''): EventModel
    {
        /** @var EventService $service */
        $service = $this->getServiceObjByKey('cal_event_model', 'event', $type);
        if ($uid > 0) {
            return $service->updateEvent($uid);
        }
        return $service->saveEvent($pid);
    }

    /**
     * @param int $uid
     * @param $type
     */
    public function removeEvent($uid, $type)
    {
        /** @var EventService $service */
        $service = $this->getServiceObjByKey('cal_event_model', 'event', $type);
        if ($uid > 0) {
            $service->removeEvent($uid);
        }
    }

    /**
     * @param int $uid
     * @param $type
     * @param string $pid
     * @return EventModel
     * @throws \TYPO3\CMS\Core\Exception
     */
    public function saveTodo(int $uid, $type, $pid = ''): EventModel
    {
        /** @var EventService $service */
        $service = $this->getServiceObjByKey('cal_event_model', $this->todoSubtype, $type);
        if ($uid > 0) {
            return $service->updateEvent($uid);
        }
        return $service->saveEvent($pid);
    }

    /**
     * @param int $uid
     * @param $type
     */
    public function removeTodo(int $uid, $type)
    {
        /** @var EventService $service */
        $service = $this->getServiceObjByKey('cal_event_model', $this->todoSubtype, $type);
        if ($uid > 0) {
            $service->removeEvent($uid);
        }
    }

    /**
     * @param int $uid
     * @param $type
     * @param string $pid
     * @return mixed
     */
    public function saveExceptionEvent($uid, $type, $pid = '')
    {
        $service = $this->getServiceObjByKey('cal_event_model', 'event', $type);
        if ($uid > 0) {
            return $service->updateExceptionEvent($uid);
        }
        return $service->saveExceptionEvent($pid);
    }

    /**
     * @param int $uid
     * @param string $type
     * @param string $pidList
     * @param bool $showHiddenEvents
     * @param bool $showDeletedEvents
     * @param bool $getAllInstances
     * @param bool $disableCalendarSearchString
     * @param bool $disableCategorySearchString
     * @param string $eventType
     * @return EventModel
     */
    public function findAllTodoInstances(
        $uid,
        $type = '',
        $pidList = '',
        $showHiddenEvents = false,
        $showDeletedEvents = false,
        $getAllInstances = false,
        $disableCalendarSearchString = false,
        $disableCategorySearchString = false,
        $eventType = '4'
    ): EventModel {
        return $this->find(
            'cal_event_model',
            $uid,
            $type,
            $this->todoSubtype,
            $pidList,
            $showHiddenEvents,
            $showDeletedEvents,
            $getAllInstances,
            $disableCalendarSearchString,
            $disableCategorySearchString,
            $eventType
        );
    }

    /**
     * @param int $uid
     * @param string $type
     * @param string $pidList
     * @return Location
     */
    public function findLocation($uid, $type = 'tx_cal_location', $pidList = '')// avoid 0 collision : Location
    {
        /** @var LocationService $service */
        $service = $this->getServiceObjByKey('cal_location_model', 'location', $type);
        $location = $service->find($uid, $pidList);
        return $location;
    }

    /**
     * @param string $type
     * @param string $pidList
     * @return array
     */
    public function findAllLocations($type = '', $pidList = ''): array
    {
        /** @var LocationService $service */
        $service = $this->getServiceObjByKey('cal_location_model', 'location', $type);
        $locations = $service->findAll($pidList);
        return $locations;
    }

    /**
     * @param int $uid
     * @param $type
     * @param string $pid
     * @return mixed
     */
    public function saveLocation($uid, $type, $pid = '')
    {
        /** @var LocationService $service */
        $service = $this->getServiceObjByKey('cal_location_model', 'location', $type);
        if ($uid > 0) {
            return $service->updateLocation($uid);
        }
        return $service->saveLocation($pid);
    }

    /**
     * @param int $uid
     * @param $type
     */
    public function removeLocation($uid, $type)
    {
        /** @var LocationService $service */
        $service = $this->getServiceObjByKey('cal_location_model', 'location', $type);
        if ($uid > 0) {
            $service->removeLocation($uid);
        }
    }

    /**
     * @param int $uid
     * @param string $type
     * @param string $pidList
     * @return Organizer
     */
    public function findOrganizer(int $uid, $type = 'tx_cal_organizer', $pidList = '')// avoid 0 collision : Organizer
    {
        /** @var OrganizerService $service */
        $service = $this->getServiceObjByKey('cal_organizer_model', 'organizer', $type);
        $organizer = $service->find($uid, $pidList);
        return $organizer;
    }

    /**
     * @param int $uid
     * @param string $type
     * @param string $pidList
     * @return array
     */
    public function findCalendar($uid, $type = 'tx_cal_calendar', $pidList = ''): array
    {
        /** @var CalendarService $service */
        $service = $this->getServiceObjByKey('cal_calendar_model', 'calendar', $type);
        $calendar = $service->find($uid, $pidList);
        return $calendar;
    }

    /**
     * @param string $type
     * @param string $pidList
     * @return array
     */
    public function findAllCalendar($type = '', $pidList = ''): array
    {
        /* No key provided so return all events */
        $serviceName = 'cal_calendar_model';
        $calendar = [];

        if ($type === '') {
            $serviceChain = '';

            /* Iterate over all classes providing the cal_model service */
            while (is_object($service = &GeneralUtility::makeInstanceService($serviceName, 'calendar', $serviceChain))) {
                $calendar [$service->getServiceKey()] = $service->findAll($pidList);
                $serviceChain .= ',' . $service->getServiceKey();
            }
        } else {
            $service = &$this->getServiceObjByKey($serviceName, 'calendar', $type);
            $calendar [$type] = $service->findAll($pidList);
        }

        return $calendar;
    }

    /**
     * @param string $type
     * @param string $pidList
     * @return array
     */
    public function findAllOrganizer($type = '', $pidList = ''): array
    {
        $service = $this->getServiceObjByKey('cal_organizer_model', 'organizer', $type);
        $organizer = $service->findAll($pidList);
        return $organizer;
    }

    /**
     * @param int $uid
     * @param $type
     * @param string $pid
     * @return Organizer
     */
    public function saveOrganizer($uid, $type, $pid = ''): Organizer
    {
        /** @var OrganizerService $service */
        $service = $this->getServiceObjByKey('cal_organizer_model', 'organizer', $type);
        if ($uid > 0) {
            return $service->updateOrganizer($uid);
        }
        return $service->saveOrganizer($pid);
    }

    /**
     * @param int $uid
     * @param $type
     */
    public function removeOrganizer($uid, $type)
    {
        /** @var OrganizerService $service */
        $service = $this->getServiceObjByKey('cal_organizer_model', 'organizer', $type);
        if ($uid > 0) {
            $service->removeOrganizer($uid);
        }
    }

    /**
     * @param int $uid
     * @param $type
     * @param string $pid
     * @return array
     */
    public function saveCalendar($uid, $type, $pid = ''): array
    {
        /** @var CalendarService $service */
        $service = $this->getServiceObjByKey('cal_calendar_model', 'calendar', $type);
        if ($uid > 0) {
            return $service->updateCalendar($uid);
        }
        return $service->saveCalendar($pid);
    }

    /**
     * @param int $uid
     * @param $type
     */
    public function removeCalendar($uid, $type)
    {
        /** @var CalendarService $service */
        $service = $this->getServiceObjByKey('cal_calendar_model', 'calendar', $type);
        if ($uid > 0) {
            $service->removeCalendar($uid);
        }
    }

    /**
     * @param int $uid
     * @param $type
     * @param string $pid
     * @return CategoryModel
     */
    public function saveCategory($uid, $type, $pid = ''): CategoryModel
    {
        /** @var SysCategoryService $service */
        $service = $this->getServiceObjByKey('cal_category_model', 'category', $type);
        if ($uid > 0) {
            return $service->updateCategory($uid);
        }
        return $service->saveCategory($pid);
    }

    /**
     * @param int $uid
     * @param $type
     */
    public function removeCategory($uid, $type)
    {
        /** @var SysCategoryService $service */
        $service = $this->getServiceObjByKey('cal_category_model', 'category', $type);
        if ($uid > 0) {
            $service->removeCategory($uid);
        }
    }

    /**
     * @param int $uid
     * @param string $type
     * @param string $pidList
     * @return AttendeeModel
     */
    public function findAttendee($uid, $type = '', $pidList = ''): AttendeeModel
    {
        /** @var AttendeeService $service */
        $service = $this->getServiceObjByKey('cal_attendee_model', 'attendee', $type);
        $attendee = $service->find($uid, $pidList);
        return $attendee;
    }

    /**
     * @param string $type
     * @param string $pidList
     * @return array
     */
    public function findAllAttendees($type = '', $pidList = ''): array
    {
        /** @var AttendeeService $service */
        $service = $this->getServiceObjByKey('cal_attendee_model', 'attendee', $type);
        $attendees = $service->findAll($pidList);
        return $attendees;
    }

    /**
     * @param $eventUid
     * @param string $type
     * @param string $pidList
     * @return array
     */
    public function findEventAttendees($eventUid, $type = '', $pidList = ''): array
    {
        $attendees = $this->findAllObjects('attendee', $type, $pidList, 'findEventAttendees', $eventUid);
        return $attendees;
    }

    /**
     * @param $eventUid
     * @param string $type
     * @param string $pidList
     */
    public function updateEventAttendees($eventUid, $type = '', $pidList = '')
    {
        $service = $this->getServiceObjByKey('cal_event_model', 'event', $type);
        $service->updateAttendees($eventUid);
    }

    /**
     * @param int $uid
     * @param $type
     * @param string $pid
     * @return AttendeeModel
     */
    public function saveAttendee($uid, $type, $pid = ''): AttendeeModel
    {
        /** @var AttendeeService $service */
        $service = $this->getServiceObjByKey('cal_attendee_model', 'attendee', $type);
        if ($uid > 0) {
            return $service->updateAttendee($uid);
        }
        return $service->saveAttendee($pid);
    }

    /**
     * @param int $uid
     * @param $type
     */
    public function removeAttendee($uid, $type)
    {
        /** @var AttendeeService $service */
        $service = $this->getServiceObjByKey('cal_attendee_model', 'attendee', $type);
        if ($uid > 0) {
            $service->removeAttendee($uid);
        }
    }

    /**
     * @param $dateObject
     * @param string $type
     * @param string $pidList
     * @param string $eventType
     * @return array
     */
    public function findEventsForDay(&$dateObject, $type = '', $pidList = '', $eventType = '0,1,2,3'): array
    {
        $starttime = CalculateDateTimeService::calculateStartOfDay(clone $dateObject);
        $endtime = CalculateDateTimeService::calculateEndOfDay(clone $dateObject);
        return $this->findAllWithin('cal_event_model', clone $starttime, clone $endtime, $type, 'event', $pidList, $eventType);
    }

    /**
     * @param $dateObject
     * @param string $type
     * @param string $pidList
     * @param string $eventType
     * @return array
     */
    public function findEventsForWeek(&$dateObject, $type = '', $pidList = '', $eventType = '0,1,2,3'): array
    {
        $starttime = CalculateDateTimeService::calculateStartOfWeek(clone $dateObject);
        $endtime = CalculateDateTimeService::calculateEndOfWeek(clone $dateObject);
        return $this->findAllWithin('cal_event_model', clone $starttime, clone $endtime, $type, 'event', $pidList, $eventType);
    }

    /**
     * @param $dateObject
     * @param string $type
     * @param string $pidList
     * @param string $eventType
     * @return array
     */
    public function findEventsForMonth(&$dateObject, $type = '', $pidList = '', $eventType = '0,1,2,3'): array
    {
        $starttime = CalculateDateTimeService::calculateStartOfMonth(clone $dateObject);
        $endtime = CalculateDateTimeService::calculateEndOfMonth(clone $dateObject);
        return $this->findAllWithin('cal_event_model', clone $starttime, clone $endtime, $type, 'event', $pidList, $eventType);
    }

    /**
     * @param $dateObject
     * @param string $type
     * @param string $pidList
     * @param string $eventType
     * @return array
     */
    public function findEventsForYear(&$dateObject, $type = '', $pidList = '', $eventType = '0,1,2,3'): array
    {
        $starttime = CalculateDateTimeService::calculateStartOfYear(clone $dateObject);
        $endtime = CalculateDateTimeService::calculateEndOfYear(clone $dateObject);
        return $this->findAllWithin('cal_event_model', clone $starttime, clone $endtime, $type, 'event', $pidList, $eventType);
    }

    /**
     * @param $startDateObject
     * @param $endDateObject
     * @param string $type
     * @param string $pidList
     * @param string $eventType
     * @param string $additionalWhere
     * @return array
     */
    public function findEventsForList(
        &$startDateObject,
        &$endDateObject,
        $type = '',
        $pidList = '',
        $eventType = '0,1,2,3',
        $additionalWhere = ''
    ): array {
        return $this->findAllWithin('cal_event_model', clone $startDateObject, clone $endDateObject, $type, 'event', $pidList, $eventType, $additionalWhere);
    }

    /**
     * @param $dateObject
     * @param string $type
     * @param string $pidList
     * @param string $eventType
     * @return array
     */
    public function findTodosForDay(&$dateObject, $type = '', $pidList = '', $eventType = '4'): array
    {
        $starttime = CalculateDateTimeService::calculateStartOfDay(clone $dateObject);
        $endtime = CalculateDateTimeService::calculateEndOfDay(clone $dateObject);
        return $this->findAllWithin('cal_event_model', clone $starttime, clone $endtime, $type, $this->todoSubtype, $pidList, $eventType);
    }

    /**
     * @param $dateObject
     * @param string $type
     * @param string $pidList
     * @param string $eventType
     * @return array
     */
    public function findTodosForWeek(&$dateObject, $type = '', $pidList = '', $eventType = '4'): array
    {
        $starttime = CalculateDateTimeService::calculateStartOfWeek(clone $dateObject);
        $endtime = CalculateDateTimeService::calculateEndOfWeek(clone $dateObject);
        return $this->findAllWithin('cal_event_model', clone $starttime, clone $endtime, $type, $this->todoSubtype, $pidList, $eventType);
    }

    /**
     * @param $dateObject
     * @param string $type
     * @param string $pidList
     * @param string $eventType
     * @return array
     */
    public function findTodosForMonth(&$dateObject, $type = '', $pidList = '', $eventType = '4'): array
    {
        $starttime = CalculateDateTimeService::calculateStartOfMonth(clone $dateObject);
        $endtime = CalculateDateTimeService::calculateEndOfMonth(clone $dateObject);
        return $this->findAllWithin('cal_event_model', clone $starttime, clone $endtime, $type, $this->todoSubtype, $pidList, $eventType);
    }

    /**
     * @param $dateObject
     * @param string $type
     * @param string $pidList
     * @param string $eventType
     * @return array
     */
    public function findTodosForYear(&$dateObject, $type = '', $pidList = '', $eventType = '4'): array
    {
        $starttime = CalculateDateTimeService::calculateStartOfYear(clone $dateObject);
        $endtime = CalculateDateTimeService::calculateEndOfYear(clone $dateObject);
        return $this->findAllWithin('cal_event_model', clone $starttime, clone $endtime, $type, $this->todoSubtype, $pidList, $eventType);
    }

    /**
     * @param $startDateObject
     * @param $endDateObject
     * @param string $type
     * @param string $pidList
     * @param string $eventType
     * @return array
     */
    public function findTodosForList(&$startDateObject, &$endDateObject, $type = '', $pidList = '', $eventType = '4'): array
    {
        return $this->findAllWithin('cal_event_model', clone $startDateObject, clone $endDateObject, $type, $this->todoSubtype, $pidList, $eventType);
    }

    /**
     * @param string $type
     * @param string $pidList
     * @return array
     */
    public function findCurrentTodos($type = '', $pidList = ''): array
    {
        return $this->findAllObjects($this->todoSubtype, $type, $pidList, 'findCurrentTodos');
    }

    /**
     * @param string $type
     * @param string $pidList
     * @return array
     */
    public function findCategoriesForList($type = '', $pidList = ''): array
    {
        return $this->findAllCategories('cal_category_model', $type, $pidList);
    }

    /**
     * @param $type
     * @param $pidList
     * @return array
     */
    public function findEventsForIcs($type, $pidList): array
    {
        return $this->findAll('cal_event_model', $type, 'event', $pidList, '0,1,2,3');
    }

    /**
     * @param $startDateObject
     * @param $endDateObject
     * @param $type
     * @param $pidList
     * @return array
     */
    public function findEventsForRss(&$startDateObject, &$endDateObject, $type, $pidList): array
    {
        return $this->findAllWithin('cal_event_model', clone $startDateObject, clone $endDateObject, $type, 'event', $pidList, '0,1,2,3');
    }

    /**
     * @param $type
     * @param $pidList
     * @return array
     */
    public function findTodosForIcs($type, $pidList): array
    {
        return $this->findAll('cal_event_model', $type, 'event', $pidList, '4');
    }

    /**
     * @param $startDateObject
     * @param $endDateObject
     * @param $type
     * @param $pidList
     * @return array
     */
    public function findTodosForRss(&$startDateObject, &$endDateObject, $type, $pidList): array
    {
        return $this->findAllWithin('cal_event_model', clone $startDateObject, clone $endDateObject, $type, $this->todoSubtype, $pidList, '4');
    }

    /**
     * @param $type
     * @param $pidList
     * @param $startDateObject
     * @param $endDateObject
     * @param $searchword
     * @param $locationIds
     * @param $organizerIds
     * @return array
     */
    public function searchEvents(
        $type,
        $pidList,
        &$startDateObject,
        &$endDateObject,
        $searchword,
        $locationIds,
        $organizerIds
    ): array {
        return $this->_searchEvents('cal_event_model', $type, $pidList, $startDateObject, $endDateObject, $searchword, $locationIds, $organizerIds, '0,1,2,3');
    }

    /**
     * @param $type
     * @param $pidList
     * @param $startDateObject
     * @param $endDateObject
     * @param $searchword
     * @param $locationIds
     * @param $organizerIds
     * @return array
     */
    public function searchTodos(
        $type,
        $pidList,
        &$startDateObject,
        &$endDateObject,
        $searchword,
        $locationIds,
        $organizerIds
    ): array {
        return $this->_searchEvents('cal_event_model', $type, $pidList, $startDateObject, $endDateObject, $searchword, $locationIds, $organizerIds, '4');
    }

    /**
     * @param $type
     * @param $pidList
     * @param $searchword
     * @return array
     */
    public function searchLocation($type, $pidList, $searchword): array
    {
        return $this->_searchAddress('cal_location_model', $type, 'location', $pidList, $searchword);
    }

    /**
     * @param $type
     * @param $pidList
     * @param $searchword
     * @return array
     */
    public function searchOrganizer($type, $pidList, $searchword): array
    {
        return $this->_searchAddress('cal_organizer_model', $type, 'organizer', $pidList, $searchword);
    }

    /**
     * @param int $uid
     * @param $overlay
     * @param $serviceName
     * @param $type
     * @param $subtype
     * @deprecated since ext:cal v2, will be removed in ext:cal v3
     */
    public function createTranslation($uid, $overlay, $serviceName, $type, $subtype)
    {
        trigger_error('Deprecated since ext:cal v2, will be removed in ext:cal v3.', E_USER_DEPRECATED);

        $service = $this->getServiceObjByKey($serviceName, $subtype, $type);
        $service->createTranslation($uid, $overlay);
    }

    /**
     * @param int $uid
     * @return array
     */
    public function findFeUser($uid): array
    {
        $feUser = [];
        $connection = $this->connectionPool->getConnectionForTable('fe_users');
        $query = $connection->select(['*'], 'fe_users', ['uid' => intval($uid)]);
        if ($query->rowCount() > 0) {
            $feUser = $query->fetch(PDO::FETCH_ASSOC);
        }
        return $feUser;
    }

    /**
     * Returns events from all calendar models or a specified model.
     *
     * @param $serviceName
     * @param $startDateObject
     * @param $endDateObject
     * @param string $type
     * @param string $subtype
     * @param string $pidList
     * @param string $eventType
     * @param string $additionalWhere
     * @return array
     */
    public function findAllWithin(
        $serviceName,
        $startDateObject,
        $endDateObject,
        $type = '',
        $subtype = '',
        $pidList = '',
        $eventType = '',
        $additionalWhere = ''
    ): array {
        /* No key provided so return all events */
        if ($type === '') {
            $serviceChain = '';
            $events = [];
            /* Iterate over all classes providing the cal_model service */
            while (is_object($service = GeneralUtility::makeInstanceService($serviceName, $subtype, $serviceChain))) {
                $serviceChain .= ',' . $service->getServiceKey();
                /* Gets all events from the current model as an array */
                $eventsFromService = $service->findAllWithin(new CalendarDateTime($startDateObject->format('Y-m-d H:i')), new CalendarDateTime($endDateObject->format('Y-m-d H:i')), $pidList, $eventType, $additionalWhere);

                if (!empty($eventsFromService)) {
                    if (empty($events)) {
                        $events = $eventsFromService;
                    } else {
                        foreach ($eventsFromService as $eventdaykey => $eventday) {
                            if (array_key_exists($eventdaykey, $events)) {
                                foreach ($eventday as $eventtimekey => $eventtime) {
                                    if (array_key_exists($eventtimekey, $events [$eventdaykey])) {
                                        $events [$eventdaykey] [$eventtimekey] += $eventtime;
                                    } else {
                                        $events [$eventdaykey] [$eventtimekey] = $eventtime;
                                    }
                                }
                            } else {
                                $events [$eventdaykey] = $eventday;
                            }
                        }
                        $events += $eventsFromService;
                    }
                }
                /* Flattens the array returned by the current model into the top level array */
            }
            ksort($events);
            $return = [];
            foreach ($events as $key => $obj) {
                ksort($obj);
                $return [$key] = $obj;
            }
            return $return;
        }        /* Operate on the provided key only */

        /* Get the model represented by $key */
        $service = $this->getServiceObjByKey($serviceName, $subtype, $type);
        if (!is_object($service)) {
            return $this->findAllWithin($service, $startDateObject, $endDateObject, '', $subtype, $pidList, $eventType, $additionalWhere);
        }
        /* Get all events from the model as an array */
        $events = $service->findAllWithin($startDateObject, $endDateObject, $pidList, $eventType, $additionalWhere);
        ksort($events);
        $return = [];
        foreach ($events as $key => $obj) {
            ksort($obj);
            $return [$key] = $obj;
        }
        return $return;
    }

    /**
     * Returns events from all calendar models or a specified model.
     *
     * @param $serviceName
     * @param $type
     * @param $subtype
     * @param $pidList
     * @param string $eventTypes
     * @return array
     */
    public function findAll($serviceName, $type, $subtype, $pidList, $eventTypes = '0,1,2,3'): array
    {
        /* No key provided so return all events */
        if ($type === '') {
            $serviceChain = '';
            $events = [];

            /* Iterate over all classes providing the cal_model service */
            while (is_object($service = GeneralUtility::makeInstanceService($serviceName, $subtype, $serviceChain))) {
                $serviceChain .= ',' . $service->getServiceKey();
                /* Gets all events from the current model as an array */
                $eventsFromService = $service->findAll($pidList, $eventTypes);
                if (!empty($eventsFromService)) {
                    if (empty($events)) {
                        $events = $eventsFromService;
                    } else {
                        foreach ($eventsFromService as $eventdaykey => $eventday) {
                            if (array_key_exists($eventdaykey, $events)) {
                                foreach ($eventday as $eventtimekey => $eventtime) {
                                    if (array_key_exists($eventtimekey, $events [$eventdaykey])) {
                                        $events [$eventdaykey] [$eventtimekey] += $eventtime;
                                    } else {
                                        $events [$eventdaykey] [$eventtimekey] = $eventtime;
                                    }
                                }
                            } else {
                                $events [$eventdaykey] = $eventday;
                            }
                        }
                        $events += $eventsFromService;
                    }
                }
            }
            ksort($events);
            $return = [];
            foreach ($events as $key => $obj) {
                ksort($obj);
                $return [$key] = $obj;
            }
            return $return;
        }        /* Operate on the provided key only */

        /* Get the model represented by $key */
        $service = $this->getServiceObjByKey($serviceName, $subtype, $type);
        /* Get all events from the model as an array */
        $events = $service->findAll($pidList, $eventTypes);
        return $events;
    }

    /**
     * @param string $uid
     * @param string $type
     * @param string $pidList
     * @return CategoryModel
     */
    public function findCategory($uid, $type = '', $pidList = ''): CategoryModel
    {
        /** @var SysCategoryService $service */
        $service = $this->getServiceObjByKey('cal_category_model', 'category', $type);
        $category = $service->find($uid, $pidList);
        return $category;
    }

    /**
     * @param $serviceName
     * @param $type
     * @param $pidList
     * @return array
     */
    public function findAllCategories($serviceName, $type, $pidList): array
    {
        $serviceName = 'cal_category_model';
        $categoryArrayToBeFilled = [];
        $categories = [];

        if ($type === '') {
            $serviceChain = '';

            /* Iterate over all classes providing the cal_model service */
            while (is_object($service = &GeneralUtility::makeInstanceService($serviceName, 'category', $serviceChain))) {
                $service->findAll($pidList, $categoryArrayToBeFilled);
                $categories [$service->getServiceKey()] = $categoryArrayToBeFilled;
                $categoryArrayToBeFilled = [];
                $serviceChain .= ',' . $service->getServiceKey();
            }
        } else {
            $service = &$this->getServiceObjByKey($serviceName, 'category', $type);

            $service->findAll($pidList, $categoryArrayToBeFilled);
            $categories [$type] = $categoryArrayToBeFilled;
        }

        return $categories;
    }

    /**
     * @param $serviceName
     * @param $type
     * @param $pidList
     * @param $startDateObject
     * @param $endDateObject
     * @param $searchword
     * @param string $locationIds
     * @param string $organizerIds
     * @param string $eventType
     * @return array
     */
    public function _searchEvents(
        $serviceName,
        $type,
        $pidList,
        &$startDateObject,
        &$endDateObject,
        $searchword,
        $locationIds = '',
        $organizerIds = '',
        $eventType = '0,1,2,3'
    ): array {
        if ($type === '') {
            $serviceChain = '';
            $events = [];

            /* Iterate over all classes providing the cal_model service */
            while (is_object($service = GeneralUtility::makeInstanceService($serviceName, 'event', $serviceChain))) {
                $serviceChain .= ',' . $service->getServiceKey();
                /* Gets all events from the current model as an array */
                $eventsFromService = $service->search($pidList, $startDateObject, $endDateObject, $searchword, $locationIds, $organizerIds, $eventType);
                if (!empty($eventsFromService)) {
                    if (empty($events)) {
                        $events = $eventsFromService;
                    } else {
                        foreach ($eventsFromService as $eventdaykey => $eventday) {
                            if (array_key_exists($eventdaykey, $events)) {
                                foreach ($eventday as $eventtimekey => $eventtime) {
                                    if (array_key_exists($eventtimekey, $events [$eventdaykey])) {
                                        $events [$eventdaykey] [$eventtimekey] += $eventtime;
                                    } else {
                                        $events [$eventdaykey] [$eventtimekey] = $eventtime;
                                    }
                                }
                            } else {
                                $events [$eventdaykey] = $eventday;
                            }
                        }
                        $events += $eventsFromService;
                    }
                }
            }
            ksort($events);
            $return = [];
            foreach ($events as $key => $obj) {
                ksort($obj);
                $return [$key] = $obj;
            }
            return $return;
        }        /* Operate on the provided key only */

        /* Get the model represented by $key */
        $service = $this->getServiceObjByKey($serviceName, 'event', $type);
        /* Get all events from the model as an array */
        $events = $service->search($pidList, $startDateObject, $endDateObject, $searchword, $locationIds, $organizerIds, $eventType);
        return $events;
    }

    /**
     * @param $serviceName
     * @param $type
     * @param $subtype
     * @param $pidList
     * @param $searchword
     * @return array
     */
    public function _searchAddress($serviceName, $type, $subtype, $pidList, $searchword): array
    {
        /* No key provided so return all events */
        if ($type === '') {
            $serviceChain = '';
            $addressFromService = [];
            /* Iterate over all classes providing the cal_model service */
            while (is_object($service = GeneralUtility::makeInstanceService($serviceName, $subtype, $serviceChain))) {
                $serviceChain .= ',' . $service->getServiceKey();
                /* Gets all events from the current model as an array */
                $addressFromService [] = $service->search($pidList, $searchword);
            }
            return $addressFromService;
        }        /* Operate on the provided key only */

        /* Get the model represented by $key */
        $service = $this->getServiceObjByKey($serviceName, $subtype, $type);
        /* Get all events from the model as an array */
        $addressFromService = $service->search($pidList, $searchword);
        return $addressFromService;
    }

    /**
     * Returns a specific event with a given serviceKey and UID.
     *
     * @param $serviceName
     * @param int $uid
     * @param $type
     * @param $subtype
     * @param string $pidList
     * @param bool $showHiddenEvents
     * @param bool $showDeletedEvents
     * @param bool $getAllInstances
     * @param bool $disableCalendarSearchString
     * @param bool $disableCategorySearchString
     * @param string $eventType
     * @return EventModel event object matching the serviceKey and UID.
     */
    public function find(
        $serviceName,
        $uid,
        $type,
        $subtype,
        $pidList = '',
        $showHiddenEvents = false,
        $showDeletedEvents = false,
        $getAllInstances = false,
        $disableCalendarSearchString = false,
        $disableCategorySearchString = false,
        $eventType = '0,1,2,3'
    ): EventModel {
        $service = $this->getServiceObjByKey($serviceName, $subtype, $type);
        if (!is_object($service)) {
            return Functions::createErrorMessage(
                'Missing or wrong parameter. The object you are looking for could not be found.',
                'Please verify your URL parameters: tx_cal_controller[type] and tx_cal_controller[uid].'
            );
        }

        $event = $service->find($uid, $pidList, $showHiddenEvents, $showDeletedEvents, $getAllInstances, $disableCalendarSearchString, $disableCategorySearchString, $eventType);
        return $event;
    }

    /**
     * Returns a specific event with a given serviceKey and UID.
     *
     * @param $serviceName
     * @param $type
     * @param $subtype
     * @return EventModel event object matching the serviceKey and UID.
     */
    public function create($serviceName, $type, $subtype): EventModel
    {
        $service = $this->getServiceObjByKey($serviceName, $subtype, $type);
        if (!is_object($service)) {
            return Functions::createErrorMessage(
                'Missing or wrong parameter. The object you are looking for could not be found.',
                'Please verify your URL parameters: tx_cal_controller[type].'
            );
        }

        $event = $service->createEvent(null, false);
        return $event;
    }

    /**
     * Helper function to return a service object with the given type, subtype, and serviceKey
     *
     * @param  string    The type of the service.
     * @param  string    The subtype of the service.
     * @param  string    The serviceKey.
     * @return object service object.
     */
    public function &getServiceObjByKey($type, $subtype, $key)
    {
        $serviceChain = '';
        while (is_object($obj = &GeneralUtility::makeInstanceService($type, $subtype, $serviceChain))) {
            $serviceChain .= ',' . $obj->getServiceKey();
            if ($key === $obj->getServiceKey()) {
                return $obj;
            }
        }
        return null;
    }

    /**
     * Helper function to return a service object with the given type, subtype, and serviceKey
     *
     * @param  string    The type of the service.
     * @param  string    The subtype of the service.
     * @return array     service object.
     */
    public function getServiceTypes($type, $subtype): array
    {
        $serviceChain = '';
        $returnArray = [];
        while (is_object($obj = GeneralUtility::makeInstanceService($type, $subtype, $serviceChain))) {
            $serviceChain .= ',' . $obj->getServiceKey();
            $returnArray [] = $obj->getServiceKey();
        }
        return $returnArray;
    }

    /**
     * @param $key
     * @param $type
     * @param $pidList
     * @param string $functionTobeCalled
     * @param string $paramsToBePassedOn
     * @return array
     */
    public function findAllObjects($key, $type, $pidList, $functionTobeCalled = '', $paramsToBePassedOn = ''): array
    {
        /* No key provided so return all X */
        $serviceName = 'cal_' . $key . '_model';
        $objects = [];
        if ($type === '') {
            $serviceChain = '';
            /* Iterate over all classes providing the cal_X_model service */
            while (is_object($service = &GeneralUtility::makeInstanceService($serviceName, $key, $serviceChain))) {
                if ($functionTobeCalled) {
                    if (method_exists($service, $functionTobeCalled)) {
                        $objects [$service->getServiceKey()] = $service->$functionTobeCalled($paramsToBePassedOn);
                    }
                } else {
                    $objects [$service->getServiceKey()] = $service->findAll($pidList);
                }
                $serviceChain .= ',' . $service->getServiceKey();
            }
        } else {
            $service = &$this->getServiceObjByKey($serviceName, $key, $type);
            /* Look up a objects with a specific ID inside the model */
            if ($functionTobeCalled) {
                if (method_exists($service, $functionTobeCalled)) {
                    $objects [$type] = $service->$functionTobeCalled($paramsToBePassedOn);
                }
            } else {
                $objects [$type] = $service->findAll($pidList);
            }
        }

        return $objects;
    }
}
