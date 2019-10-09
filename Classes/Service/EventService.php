<?php

/*
 * This file is part of the web-tp3/cal.
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace TYPO3\CMS\Cal\Service;

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
use RuntimeException;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Cal\Controller\Calendar;
use TYPO3\CMS\Cal\Controller\DateParser;
use TYPO3\CMS\Cal\Domain\Repository\EventRepository;
use TYPO3\CMS\Cal\Domain\Repository\EventSharedUserMMRepository;
use TYPO3\CMS\Cal\Domain\Repository\SubscriptionRepository;
use TYPO3\CMS\Cal\Model\AttendeeModel;
use TYPO3\CMS\Cal\Model\CalendarDateTime;
use TYPO3\CMS\Cal\Model\EventModel;
use TYPO3\CMS\Cal\Model\EventDeviationModel;
use TYPO3\CMS\Cal\Model\EventRecModel;
use TYPO3\CMS\Cal\Model\Model;
use TYPO3\CMS\Cal\Model\Pear\Date\Calc;
use TYPO3\CMS\Cal\Utility\Functions;
use TYPO3\CMS\Cal\Utility\RecurrenceGenerator;
use TYPO3\CMS\Cal\Utility\Registry;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Database\Query\Restriction\FrontendRestrictionContainer;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Cal\Domain\Repository\EventDeviationRepository;

/**
 * Class EventService
 */
class EventService extends BaseService
{
    public $location;

    /**
     * @var int
     */
    public $calnumber = 1;

    /**
     * @var CalendarDateTime
     */
    public $starttime;

    /**
     * @var CalendarDateTime
     */
    public $endtime;

    /**
     * @var string
     */
    public $internalAdditionWhere = '';

    /**
     * @var string
     */
    public $internalAdditionTable = '';

    /**
     * @var EventSharedUserMMRepository
     */
    protected $eventSharedUserMMRepository;

    /**
     * @var EventDeviationRepository
     */
    protected $eventdeviationRepository;

    /**
     * @var SubscriptionRepository
     */
    protected $subscriptionRepository;

    /**
     * @var EventRepository
     */
    protected $eventRepository;

    /**
     * EventModel constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->eventSharedUserMMRepository = $this->objectManager->get(EventSharedUserMMRepository::class);
        $this->subscriptionRepository = $this->objectManager->get(SubscriptionRepository::class);
        $this->eventRepository = $this->objectManager->get(EventRepository::class);
        $this->rightsObj =  $this->objectManager->get(RightsService::class);
    }

    /**
     * @return int
     */
    public function getCalNumber(): int
    {
        return $this->calnumber;
    }

    /**
     * @param int $calnumber
     */
    public function setCalNumber($calnumber)
    {
        $this->calnumber = $calnumber;
    }

    /**
     * Finds all events within a given range.
     *
     * @param CalendarDateTime $start_date
     * @param CalendarDateTime $end_date
     * @param $pidList
     * @param string $eventType
     * @param string $additionalWhere
     * @return array array of events represented by the model.
     */
    public function findAllWithin(
        $start_date,
        $end_date,
        $pidList,
        $eventType = '0,1,2,3',
        $additionalWhere = ''
    ): array {
        // How to get the events
        // 1st get Calendar specified
        // 2nd get categories specified
        // 3rd get all related events
        // make an array out of the list, so we can handle it better
        $includeRecurring = true;
        if ($this->conf['view'] === 'ics' || $this->conf['view'] === 'single_ics') {
            $includeRecurring = false;
        }
        $this->setStartAndEndPoint($start_date, $end_date);
        $dontShowOldEvents = (int)$this->conf['view.'][$this->conf['view'] . '.']['dontShowOldEvents'];
        if ($dontShowOldEvents > 0) {
            $now = new CalendarDateTime();
            if ($dontShowOldEvents === 2) {
                $now->setHour(0);
                $now->setMinute(0);
                $now->setSecond(0);
            }

            if ($start_date->format('U') <= $now->format('U')) {
                $start_date->copy($now);
            }
            if ($end_date->format('U') <= $now->format('U')) {
                $end_date->copy($now);
                $end_date->addSeconds(86400);
            }
            $this->starttime->copy($start_date);
            $this->endtime->copy($end_date);
        }
        $formattedStarttime = $this->starttime->format('Ymd');
        $formattedEndtime = $this->endtime->format('Ymd');

        $recurringClause = '';
        // only include the recurring clause if we don't use the new recurring model or a view not needing recurring events.
        if ($includeRecurring) {
            $tmpUids = $this->eventRepository->findUidsOfRecurringEvents($start_date, $end_date);
            if (count($tmpUids)) {
                $recurringClause = ' OR (tx_cal_event.uid IN (' . implode(',', $tmpUids) . ')) ';
            }
        }

        $calendarService = &$this->modelObj->getServiceObjByKey('cal_calendar_model', 'calendar', 'tx_cal_calendar');
        $categoryService = $this->objectManager->get(SysCategoryService::class);

        $calendarSearchString = $calendarService->getCalendarSearchString(
            $pidList,
            true,
            $this->conf['calendar'] ?: ''
        );

        // putting everything together
        // Franz: added simple check/include for rdate events at the end of this where clause.
        // But we need to find a way to only include rdate events within the searched timerange
        // - otherwise we'll flood the results after some time. I think we need a mm-table for that!
        $additionalWhere = ' AND tx_cal_event.pid IN (' . $pidList . ') ' . $this->cObj->enableFields('tx_cal_event') . ' AND ((tx_cal_event.start_date>=' . $formattedStarttime . ' AND tx_cal_event.start_date<=' . $formattedEndtime . ') OR (tx_cal_event.end_date<=' . $formattedEndtime . ' AND tx_cal_event.end_date>=' . $formattedStarttime . ') OR (tx_cal_event.end_date>=' . $formattedEndtime . ' AND tx_cal_event.start_date<=' . $formattedStarttime . ')' . $recurringClause . ')' . $additionalWhere;

        $additionalWhere = $calendarSearchString . $additionalWhere;

        // creating the arrays the user is allowed to see
        $categories = [];

        $categoryService->getCategoryArray($pidList, $categories);

        // creating events
        return $this->getEventsFromTable(
            $categories[0][0],
            $includeRecurring,
            $additionalWhere,
            $this->getServiceKey(),
            true,
            false,
            $eventType
        );
    }

    /**
     * Search for events with an according category.uid
     *
     * @param $categories array
     *            available categories
     * @param $includeRecurring boolean
     *            recurring events should be included
     * @param $additionalWhere String
     *            string; will be added to the where-clause
     *
     * @param string $serviceKey
     * @param bool $addCategoryWhere
     * @param bool $onlyMeetingsWithoutStatus
     * @param string $eventType
     * @return array array of tx_cal_phpcalendar_model events
     */
    public function getEventsFromTable(
        &$categories,
        $includeRecurring = false,
        $additionalWhere = '',
        $serviceKey = '',
        $addCategoryWhere = false,
        $onlyMeetingsWithoutStatus = false,
        $eventType = '0,1,2,3'
    ): array {
        $calendarService = &$this->modelObj->getServiceObjByKey('cal_calendar_model', 'calendar', 'tx_cal_calendar');
        $categoryService = $this->objectManager->get(SysCategoryService::class);

        $events = [];

        $select = 'tx_cal_calendar.uid AS calendar_uid, ' . 'tx_cal_calendar.owner AS calendar_owner, ' . 'tx_cal_calendar.headerstyle AS calendar_headerstyle, ' . 'tx_cal_calendar.bodystyle AS calendar_bodystyle, ' . 'tx_cal_event.*';
        $table = 'tx_cal_event LEFT JOIN tx_cal_calendar ON tx_cal_calendar.uid = tx_cal_event.calendar_id ';
      //  $table .= 'left JOIN tx_cal_index  ON tx_cal_event.uid = tx_cal_index.event_uid ';
        if ($GLOBALS['TSFE']->sys_language_content > 0 ?? 0 === strpos($this->conf['view'], 'search')) {
            $select .= implode(
                ',tx_cal_event_l18n.',
                GeneralUtility::trimExplode(',', $this->conf['view.']['search.']['searchEventFieldList'], 1)
            );
            $table .= 'LEFT JOIN tx_cal_event as tx_cal_event_l18n ON tx_cal_event.uid = tx_cal_event_l18n.l18n_parent ';
        }
        //AND tx_cal_index.tablename = "tx_cal_event"
        $where = '1=1  ' . $additionalWhere;
        $orderBy = ' tx_cal_event.start_date ASC, tx_cal_event.start_time ASC';
        $groupBy = 'tx_cal_event.uid';

        $allowedEventTypes = GeneralUtility::trimExplode(',', $eventType, 1);
        if (!empty($allowedEventTypes)) {
            $where .= ' AND tx_cal_event.type IN (' . implode(',', $allowedEventTypes) . ')';
        }

        if ($this->conf['view.'][$this->conf['view'] . '.']['event.']['additionalWhere']) {
            $where .= ' ' . $this->cObj->cObjGetSingle(
                $this->conf['view.'][$this->conf['view'] . '.']['event.']['additionalWhere'],
                $this->conf['view.'][$this->conf['view'] . '.']['event.']['additionalWhere.']
            );
        }
        #todo event categorie service - if no category breaks call (ask before)
//        if ($addCategoryWhere) {
//            $categoryService->enhanceEventQuery($select, $table, $where, $groupBy, $orderBy);
//        }

        if ($onlyMeetingsWithoutStatus) {
            $table .= ', tx_cal_attendee';
            $where .= ' AND tx_cal_attendee.event_id = tx_cal_event.uid';
        }

        if (TYPO3_MODE !== 'BE') {
            $where .= $this->getAdditionalWhereForLocalizationAndVersioning('tx_cal_event');
        }

        $where .= $this->internalAdditionWhere;
        $table .= $this->internalAdditionTable;

        $limit = '';

        $hookObjectsArr = Functions::getHookObjectsArray(
            'tx_cal_event_service',
            'eventServiceClass',
            'service'
        );

        foreach ($hookObjectsArr as $hookObj) {
            if (method_exists($hookObj, 'preGetEventsFromTableExec')) {
                $hookObj->preGetEventsFromTableExec($this, $select, $table, $where, $groupBy, $orderBy, $limit);
            }
        }

        $result = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select, $table, $where, $groupBy, $orderBy, $limit);

        $eventOwnerArray = $calendarService->getCalendarOwner();

        $resultRows = [];

        // fetching all categories attached to all events in the current view
        $categoriesArray = [];
        // allow all categories, so unset 'allowedCategory' in the 'conf' array
        $categoryService->conf['view.']['allowedCategory'] = false;
        $categoryService->getCategoryArray($this->conf['pidList'], $categoriesArray);
        $selectFields = $GLOBALS['TYPO3_DB']->admin_get_fields('tx_cal_event');
        //$rows =[];
        $eventUids = [];
        if ($result) {
            while ($rows = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
                if ($GLOBALS['TSFE']->sys_page->versioningPreview) {
                    $interRow = array_intersect_key($rows, $selectFields);
                    $GLOBALS['TSFE']->sys_page->versionOL('tx_cal_event', $interRow);
                    $GLOBALS['TSFE']->sys_page->fixVersioningPid('tx_cal_event', $interRow);
                    $rows = array_merge($rows, $interRow);
                }

                if ($GLOBALS['TSFE']->sys_language_content) {
                    $rows = $GLOBALS['TSFE']->sys_page->getRecordOverlay(
                        'tx_cal_event',
                        $rows,
                        $GLOBALS['TSFE']->sys_language_content,
                        $GLOBALS['TSFE']->sys_language_contentOL
                    );
                }
                if (!$rows['uid']) {
                    continue;
                }

                // collect event uids for optimized queries. f.e. for exception events etc.
                $uid = $rows['uid'];

                // prepare category_uid
                $resultRows[$uid] = $rows;
            }
            //$GLOBALS['TYPO3_DB']->sql_free_result($result);
        }

        static $exceptionEventCache = [];
        // bug in php cache -> $row turns to false from while breaks call
        foreach ($resultRows as $row) {
            $row['event_owner'] = &$eventOwnerArray[$row['calendar_uid']];
            if ($row['end_date'] === 0) {
                $row['end_date'] = $row['start_date'];
            }
            $event = $this->createEvent($row, false);

            if ((int)$this->conf['view.']['showEditableEventsOnly'] === 1 && (!$event->isUserAllowedToEdit() && !$event->isUserAllowedToDelete())) {
                continue;
            }

            if ($row['category_uid'] > 0) {
                $categoryArray = $categoryService->getCategoriesForEvent($row['uid']);
                if (is_array($categoryArray)) {
                    foreach ($categoryArray as $category) {
                        $event->addCategory($category);
                    }
                }
            }

            $events_tmp = [];
            if (!is_object($event)) {
                return $events_tmp;
            }

            if ($row['shared_user_cnt'] > 0) {
                $sharedUids = $this->eventSharedUserMMRepository->findSharedUidsByEventUid($this->getUid());
                foreach ($sharedUids as $sharedUid) {
                    if ($sharedUid['tablenames'] === 'fe_users') {
                        $this->addSharedUser($sharedUid['uid_foreign']);
                    } elseif ($sharedUid['tablenames'] === 'fe_groups') {
                        $this->addSharedGroup($sharedUid['uid_foreign']);
                    }
                }
            }

            // get exception events:
            $where = 'AND tx_cal_event.uid = ' . $event->getUid() . ' AND tx_cal_exception_event_mm.tablenames="tx_cal_exception_event_group" ' . $this->cObj->enableFields('tx_cal_exception_event_group');
            $orderBy = '';
            $groupBy = '';
            $limit = '';
            $ex_events_group = [];

            if (!$includeRecurring) {
                $tmp_starttime = new CalendarDateTime();
                $tmp_starttime->copy($this->starttime);
                $tmp_endtime = new CalendarDateTime();
                $tmp_endtime->copy($this->endtime);
                $this->starttime->copy($event->getStart());
                $this->endtime->copy(new CalendarDateTime($this->conf['view.'][$this->conf['view'] . '.']['maxDate'] . '000000'));
            }

            $result3 = $GLOBALS['TYPO3_DB']->exec_SELECT_mm_query(
                'tx_cal_exception_event_group.*',
                'tx_cal_event',
                'tx_cal_exception_event_mm',
                'tx_cal_exception_event_group',
                $where,
                $groupBy,
                $orderBy,
                $limit
            );
            while ($row3 = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result3)) {
                $event->addExceptionGroupId($row3['uid']);
                $where = 'AND tx_cal_exception_event_group.uid = ' . $row3['uid'] . $this->cObj->enableFields('tx_cal_exception_event');

                $result4 = $GLOBALS['TYPO3_DB']->exec_SELECT_mm_query(
                    'tx_cal_exception_event.*',
                    'tx_cal_exception_event_group',
                    'tx_cal_exception_event_group_mm',
                    'tx_cal_exception_event',
                    $where,
                    $groupBy,
                    $orderBy,
                    $limit
                );
                while ($row4 = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result4)) {
                    if ($row4['end_date'] === 0) {
                        $row4['end_date'] = $row4['start_date'];
                    }
                    if (!$exceptionEventCache[$row4['uid']]) {
                        $ex_event = $this->createEvent($row4, true);
                        $recurringInstances = $this->getRecurringEventsFromIndex($ex_event);
                        $exceptionEventCache[$row4['uid']] = $recurringInstances;
                        $ex_events_group[] = $recurringInstances;
                    } else {
                        $ex_events_group[] = $exceptionEventCache[$row4['uid']];
                    }
                }
               // $GLOBALS['TYPO3_DB']->sql_free_result($result4);
            }
            //$GLOBALS['TYPO3_DB']->sql_free_result($result3);

            $where = 'AND tx_cal_event.uid = ' . $row['uid'] . ' AND tx_cal_exception_event_mm.tablenames="tx_cal_exception_event" ' . $this->cObj->enableFields('tx_cal_exception_event');
            $orderBy = ''; // 'tx_cal_exception_event.start_time ASC';
            $groupBy = '';
            $limit = '';

            $result2 = $GLOBALS['TYPO3_DB']->exec_SELECT_mm_query(
                'tx_cal_exception_event.*',
                'tx_cal_event',
                'tx_cal_exception_event_mm',
                'tx_cal_exception_event',
                $where,
                $groupBy,
                $orderBy,
                $limit
            );
            while ($row2 = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result2)) {
                if (!$exceptionEventCache[$row2['uid']]) {
                    $event->addExceptionSingleId($row2['uid']);
                    if ($row2['end_date'] === 0) {
                        $row2['end_date'] = $row2['start_date'];
                    }
                    $ex_event = $this->createEvent($row2, true);
                    $recurringInstances = $this->getRecurringEventsFromIndex($ex_event);
                    $exceptionEventCache[$row2['uid']] = $recurringInstances;
                    $ex_events_group[] = $recurringInstances;
                } else {
                    $ex_events_group[] = $exceptionEventCache[$row2['uid']];
                }
            }
            //$GLOBALS['TYPO3_DB']->sql_free_result($result2);

            if (!$includeRecurring) {
                $this->starttime->copy($tmp_starttime);
                $this->endtime->copy($tmp_endtime);

                $eventStart = $event->getStart();
                $events_tmp[$eventStart->format('Ymd')][$event->isAllday() ? '-1' : $eventStart->format('Hi')][$event->getUid()] = $event;

                $eventStart = $event->getStart();
                foreach ($ex_events_group as $ex_events) {
                    foreach ($ex_events as $ex_event_day) {
                        foreach ($ex_event_day as $ex_event_array) {
                            foreach ($ex_event_array as $ex_event) {
                                $events_tmp[$eventStart->format('Ymd')][$event->isAllday() ? '-1' : $eventStart->format('Hi')][$event->getUid()]->addExceptionEvent($ex_event);
                            }
                        }
                    }
                }
            } elseif (is_object($event)) {
                if (in_array($event->getFreq(), ['year', 'month', 'week', 'day']) ||
                    ($event->getRdate() && in_array($event->getRdateType(), ['date', 'date_time', 'period']))) {
                    $ex_events_dates = [];
                    foreach ($ex_events_group as $ex_events) {
                        foreach ($ex_events as $ex_event_day) {
                            foreach ($ex_event_day as $ex_event_array) {
                                foreach ($ex_event_array as $ex_event) {
                                    /** @var EventModel $ex_event */
                                    $ex_events_dates[$ex_event->getStart()->format('Ymd')] = 1;
                                }
                            }
                        }
                    }
                    $events_tmp = $this->getRecurringEventsFromIndex($event, $ex_events_dates);
                } else {
                    $eventStart = $event->getStart();
                    $events_tmp[$eventStart->format('Ymd')][$event->isAllday() ? '-1' : $eventStart->format('Hi')][$event->getUid()] = $event;
                }
            }

            if (!empty($events)) {
                $this->mergeEvents($events, $events_tmp);
            } else if (!empty($events_tmp)) {
                $this->mergeEvents($events, $events_tmp);
            } else {
                $events[$event->getStart()->format('Ymd')][$event->isAllday() ? '-1' : $event->getStart()->format('Hi')][$event->getUid()] = $event;
            }
        }

        $categoryIdArray = GeneralUtility::trimExplode(
            ',',
            implode(',', (array)$this->controller->piVars['category']),
            1
        );

        if (
            $addCategoryWhere
            && !(
                ($this->conf['view'] === 'ics' || $this->conf['view'] === 'search_event')
                && !empty($categoryIdArray)
            )
            && !in_array((int)$this->conf['view.']['categoryMode'], [1, 3, 4], true)
        ) {

            /**
             * Jan: I don't see the reason to exclude every event with any
             * category. Maybe we should add another switch here like
             * "Find only events without categories"
             */
            /*
            $uidCollector = $categoryService->getUidsOfEventsWithCategories();

            if (!empty($uidCollector)) {
               $additionalWhere .= ' AND tx_cal_event.uid NOT IN (' . implode(',', $uidCollector) . ')';
            }
            */
            $eventsWithoutCategory = $this->getEventsFromTable(
                $categories,
                $includeRecurring,
                $additionalWhere,
                $serviceKey,
                false,
                $onlyMeetingsWithoutStatus,
                $eventType
            );

            if (!empty($eventsWithoutCategory)) {
                $this->mergeEvents($events, $eventsWithoutCategory);
            }
        }

        return $events;
    }

    /**
     * Finds all events.
     *
     * @param $pidList
     * @param string $eventType
     * @return array array of events represented by the model.
     */
    public function findAll($pidList, $eventType = '0,1,2,3'): array
    {
        // How to get the events
        // 1st get Calendar specified
        // 2nd get categories specified
        // 3rd get all related events
        // make an array out of the list, so we can handle it better
        $start_date = new CalendarDateTime('00000001000000');
        if (isset($this->conf['view.'][$this->conf['view'] . '.']['minDate'])) {
            $start_date = $this->getTimeParsed($this->conf['view.'][$this->conf['view'] . '.']['minDate']);
        }
        $start_date->setTZbyID('UTC');

        $end_date = $this->getTimeParsed('+2 years');
        if (isset($this->conf['view.'][$this->conf['view'] . '.']['maxDate'])) {
            $end_date = $this->getTimeParsed($this->conf['view.'][$this->conf['view'] . '.']['maxDate']);
        }
        $end_date->setTZbyID('UTC');
        $this->setStartAndEndPoint($start_date, $end_date);

        $this->endtime->setHour(0);
        $this->endtime->setMinute(0);

        $formattedStarttime = $this->starttime->format('Ymd');
        $formattedEndtime = $this->endtime->format('Ymd');

        $calendarService = &$this->modelObj->getServiceObjByKey('cal_calendar_model', 'calendar', 'tx_cal_calendar');
        $categoryService = $this->objectManager->get(SysCategoryService::class);

        $calendarSearchString = $calendarService->getCalendarSearchString(
            $pidList,
            true,
            $this->conf['calendar'] ?: ''
        );

        $recurringClause = ' OR (tx_cal_event.start_date<=' . $formattedEndtime . ' AND (tx_cal_event.freq IN ("day", "week", "month", "year") AND (tx_cal_event.until>=' . $formattedStarttime . ' OR tx_cal_event.until=0))) OR (tx_cal_event.rdate AND tx_cal_event.rdate_type IN ("date_time", "date", "period")) ';

        // putting everything together
        $additionalWhere = $calendarSearchString . ' AND tx_cal_event.pid IN (' . $pidList . ') ' . $this->cObj->enableFields('tx_cal_event') . ' AND ((tx_cal_event.start_date>=' . $formattedStarttime . ' AND tx_cal_event.start_date<=' . $formattedEndtime . ') OR (tx_cal_event.end_date<=' . $formattedEndtime . ' AND tx_cal_event.end_date>=' . $formattedStarttime . ') OR (tx_cal_event.end_date>=' . $formattedEndtime . ' AND tx_cal_event.start_date<=' . $formattedStarttime . ')' . $recurringClause . ')' . $additionalWhere;

        // creating the arrays the user is allowed to see

        $categories = [];

        $categoryService->getCategoryArray($pidList, $categories);
        // creating events

        if ($this->conf['view'] === 'ics' || $this->conf['view'] === 'single_ics') {
            $includeRecurring = false;
        } else {
            $includeRecurring = true;
        }

        // creating events
        if ($pidList) {
            return $this->getEventsFromTable(
                $categories[0][0],
                $includeRecurring,
                $additionalWhere,
                $this->getServiceKey(),
                true,
                false,
                $eventType
            );
        }
        return [];
    }

    /**
     * Get the time parsed
     *
     * @param string $timeString
     *
     * @return CalendarDateTime
     */
    protected function getTimeParsed($timeString): CalendarDateTime
    {
        /** @var DateParser $dp */
        $dp = $this->objectManager->get(DateParser::class);
        $dp->parse($timeString, 0, '');
        return $dp->getDateObjectFromStack();
    }

    /**
     * @param $row
     * @param $isException
     * @return EventModel
     */
    public function createEvent($row, $isException): EventModel
    {
        return new EventModel($row, $isException, $this->getServiceKey());
    }

    /**
     * Finds a single event.
     *
     * @param $uid
     * @param $pidList
     * @param bool $showHiddenEvents
     * @param bool $showDeletedEvents
     * @param bool $getAllInstances
     * @param bool $disableCalendarSearchString
     * @param bool $disableCategorySearchString
     * @param string $eventType
     * @return EventModel event represented by the model.
     */
    public function find(
        $uid,
        $pidList,
        $showHiddenEvents = false,
        $showDeletedEvents = false,
        $getAllInstances = false,
        $disableCalendarSearchString = false,
        $disableCategorySearchString = false,
        $eventType = '0,1,2,3'
    ): EventModel {
        $uid = (int)$uid;
        if ($getAllInstances) {
            $start_date = new CalendarDateTime($this->conf['view.'][$this->conf['view'] . '.']['minDate'] . '000000');
            $start_date->setTZbyID('UTC');
            $end_date = new CalendarDateTime($this->conf['view.'][$this->conf['view'] . '.']['maxDate'] . '000000');
            $end_date->setTZbyID('UTC');
            $this->setStartAndEndPoint($start_date, $end_date);

            $this->endtime->setHour(0);
            $this->endtime->setMinute(0);
        } else {
            $this->starttime = new CalendarDateTime();
            if ($this->controller->getDateTimeObject) {
                $this->starttime->copy($this->controller->getDateTimeObject);
            }
            $this->endtime = new CalendarDateTime();
            if ($this->controller->getDateTimeObject) {
                $this->endtime->copy($this->controller->getDateTimeObject);
            }
            $this->endtime->addSeconds(86400);
        }

        $categories = [];

        $categoryService = $this->objectManager->get(SysCategoryService::class);

        $categoryService->getCategoryArray($pidList, $categories);

        $calendarSearchString = '';
        if (!$disableCalendarSearchString) {
            $calendarService = &$this->modelObj->getServiceObjByKey(
                'cal_calendar_model',
                'calendar',
                'tx_cal_calendar'
            );
            $calendarSearchString = $calendarService->getCalendarSearchString(
                $pidList,
                true,
                $this->conf['calendar'] ?: ''
            );
        }

        // putting everything together

        $additionalWhere = $calendarSearchString . ' AND tx_cal_event.uid=' . $uid  ;
        if (!$showHiddenEvents) {
            $additionalWhere .= ' AND tx_cal_event.hidden = 0';
        }
        if (!$showDeletedEvents) {
            $additionalWhere .= ' AND tx_cal_event.deleted = 0';
        }

        if ($this->conf['view'] === 'ics' || $this->conf['view'] === 'single_ics' || $this->conf['view'] === 'create_event' || $this->conf['view'] === 'edit_event' || $this->conf['view'] === 'subscription') {
            $getAllInstances = false;
        }

        // In single event view we might have an instance of the recurring event
        if ($this->conf['view'] === 'event') {
            $getAllInstances = true;
        }

        $events = $this->getEventsFromTable(
            $categories[0][0],
            $getAllInstances,
            $additionalWhere,
            $this->getServiceKey(),
            !$disableCategorySearchString,
            false,
            $eventType
        );

        // It is still the single view and we need to get the right instance and not all of them
        if ($this->conf['view'] === 'event') {
            $getAllInstances = false;
        }

        if ($getAllInstances) {
            return $events;
        }

        if ($this->conf['getdate']) {
            foreach ($events as $date => $time) {
                foreach ($time as $eventArray) {
                    foreach ($eventArray as $event) {
                        /** @var EventModel $event */
                        $eventStart = $event->getStart();
                        $eventEnd = $event->getEnd();
                        if ($eventStart->format('Ymd') <= $this->conf['getdate'] && $eventEnd->format('Ymd') >= $this->conf['getdate'] && $event->getUid() === $uid) {
                            return $event;
                        }
                    }
                }
            }
        }
        if (empty($events)) {
            return [];
        }
        if ($this->conf['getdate'] && $events[$this->conf['getdate']]) {
            $event = array_pop(array_pop($events[$this->conf['getdate']]));
            return $event;
        }
        return array_pop(array_pop(array_pop($events)));
    }

    /**
     * @param $pid
     * @return EventModel
     * @throws \TYPO3\CMS\Core\Exception
     */
    public function saveEvent($pid)
    {
        $object = $this->modelObj->createEvent('tx_cal_phpicalendar');
        $object->updateWithPIVars($this->controller->piVars);

        $crdate = time();
        $insertFields = [];
        $insertFields['pid'] = $pid;
        $insertFields['tstamp'] = $crdate;
        $insertFields['crdate'] = $crdate;

        if ($GLOBALS['TSFE']->sys_language_content > 0 && $this->conf['showRecordsWithoutDefaultTranslation'] == 1 && $this->rightsObj->isAllowedTo(
            'create',
            'translation'
        )) {
            $insertFields['sys_language_uid'] = $GLOBALS['TSFE']->sys_language_content;
        }

        // TODO: Check if all values are correct
        $this->searchForAdditionalFieldsToAddFromPostData($insertFields, 'event');
        $this->filterDataToBeSaved($insertFields, $object);

        if (!$insertFields['calendar_id'] && $this->conf['rights.']['create.']['event.']['fields.']['calendar_id.']['default']) {
            $insertFields['calendar_id'] = $this->conf['rights.']['create.']['event.']['fields.']['calendar_id.']['default'];
        }

        $insertFields['cruser_id'] = $this->rightsObj->getUserId();
        $insertFields['fe_cruser_id'] = $this->rightsObj->getUserId();

        if (is_array($this->controller->piVars['notify'])) {
            $insertFields['notify_ids'] = implode(',', $this->controller->piVars['notify']);
        } else {
            $insertFields['notify_ids'] = $this->controller->piVars['notify_ids'];
        }
        if (is_array($this->controller->piVars['exception_ids'])) {
            $insertFields['exception_ids'] = implode(',', $this->controller->piVars['exception_ids']);
        } else {
            $insertFields['exception_ids'] = $this->controller->piVars['exception_ids'];
        }

        $uid = $this->_saveEvent($insertFields, $object);
        $object->setUid($uid);

        $this->conf['category'] = $this->conf['view.']['allowedCategories'];
        $this->conf['calendar'] = $this->conf['view.']['allowedCalendar'];

        $this->unsetPiVars();
        $insertFields['uid'] = $uid;
        $insertFields['category'] = $this->controller->piVars['category_ids'];
        self::_notify($insertFields);
        if ($object->getSendOutInvitation()) {
            $this->_invite($object);
        }

        self::_scheduleReminder($uid);

        /** @var RecurrenceGenerator $rgc */
        $rgc = $this->objectManager->get(RecurrenceGenerator::class, $GLOBALS['TSFE']->id);
        $rgc->generateIndexForUid($uid, 'tx_cal_event');

        // Hook: saveEvent
        $hookObjectsArr = Functions::getHookObjectsArray(
            'tx_cal_event_service',
            'eventServiceClass'
        );
        Functions::executeHookObjectsFunction($hookObjectsArr, 'saveEvent', $this, $object);

        Functions::clearCache();
        return $this->find($uid, $pid);
    }

    /**
     * @param $eventData
     * @param EventModel $object
     * @return int
     */
    public function _saveEvent(&$eventData, $object): int
    {
        $tempValues = [];
        $tempValues['notify_ids'] = $eventData['notify_ids'];
        unset($eventData['notify_ids']);
        $tempValues['exception_ids'] = $eventData['exception_ids'];
        unset($eventData['exception_ids']);
        $tempValues['attendee_ids'] = $eventData['attendee_ids'];
        unset($eventData['attendee_ids']);

        // Creating DB records
        $table = 'tx_cal_event';
        $connection = $this->connectionPool->getConnectionForTable($table);

        $queryBuilder = $connection->createQueryBuilder();
        if (TYPO3_MODE == 'BE') {
            $queryBuilder
                ->getRestrictions()
                ->removeAll()
                ->add(GeneralUtility::makeInstance(DeletedRestriction::class));
        } else {
            $queryBuilder->setRestrictions(GeneralUtility::makeInstance(FrontendRestrictionContainer::class));
        }
//        $result = $GLOBALS['TYPO3_DB']->exec_INSERTquery($table, $eventData);
        $result =  $queryBuilder->insert($table,$eventData)
            ->execute();
        if (false === $result) {
            throw new RuntimeException(
                'Could not write event record to database: ' . debug($queryBuilder->getSQL()),
                1431458130
            );
        }

        $uid =  $connection->lastInsertId($table);

        if ($this->rightsObj->isAllowedTo('create', 'event', 'image')) {
            $this->checkOnNewOrDeletableFiles('tx_cal_event', 'image', $eventData, $uid);
        }

        if ($this->rightsObj->isAllowedTo('create', 'event', 'attachment')) {
            $this->checkOnNewOrDeletableFiles('tx_cal_event', 'attachment', $eventData, $uid);
        }

        // creating relation records
        if ($this->rightsObj->isAllowedToCreateEventNotify()) {
            if ($tempValues['notify_ids'] !== '') {
                $user = [];
                $group = [];
                self::splitUserAndGroupIds(explode(',', strip_tags($tempValues['notify_ids'])), $user, $group);
                foreach ($user as $u) {
                    $userOffsetArray = GeneralUtility::trimExplode('_', $u, 1);
                    $this->subscriptionRepository->insert(
                        [
                            'uid_local' => $uid,
                            'uid_foreign' => $userOffsetArray[0],
                            'tablenames' => 'fe_users',
                            'sorting' => 1,
                            'offset' => $userOffsetArray[1] ?? $this->conf['view.']['event.']['remind.']['time'],
                            'pid' => $eventData['pid']
                        ]
                    );
                }
                $ignore = GeneralUtility::trimExplode(
                    ',',
                    $this->conf['rights.']['create.']['event.']['addFeGroupToNotify.']['ignore'],
                    1
                );
                foreach ($group as $g) {
                    $groupOffsetArray = GeneralUtility::trimExplode('_', $g, 1);
                    if (!in_array($groupOffsetArray[0], $ignore, true)) {
                        $this->subscriptionRepository->insert(
                            [
                                'uid_local' => $uid,
                                'uid_foreign' => $groupOffsetArray[0],
                                'tablenames' => 'fe_groups',
                                'sorting' => 1,
                                'offset' => $userOffsetArray[1] ?? $this->conf['view.']['event.']['remind.']['time'],
                                'pid' => $eventData['pid']
                            ]
                        );
                    }
                }
            }
        } elseif ($this->conf['rights.']['create.']['event.']['fields.']['notify.']['defaultUser'] || $this->conf['rights.']['create.']['event.']['fields.']['notify.']['defaultGroup']) {
            $idArray = GeneralUtility::trimExplode(
                ',',
                $this->conf['rights.']['create.']['event.']['fields.']['notify.']['defaultUser'],
                1
            );
            if ($this->conf['rights.']['create.']['event.']['addFeUserToNotify']) {
                $idArray[] = $this->rightsObj->getUserId();
            }
            foreach (array_unique($idArray) as $key => $userUid) {
                $this->subscriptionRepository->insert(
                    [
                        'uid_local' => $uid,
                        'uid_foreign' => $userUid,
                        'tablenames' => 'fe_users',
                        'sorting' => $key + 1,
                        'offset' => $this->conf['view.']['event.']['remind.']['time'],
                        'pid' => $eventData['pid']
                    ]
                );
            }
            $idArray = GeneralUtility::trimExplode(
                ',',
                $this->conf['rights.']['create.']['event.']['fields.']['notify.']['defaultGroup'],
                1
            );
            if ($this->conf['rights.']['create.']['event.']['addFeGroupToNotify']) {
                $idArray = array_merge($idArray, $this->rightsObj->getUserGroups());
            }
            foreach (array_unique($idArray) as $key => $groupUid) {
                $this->subscriptionRepository->insert(
                    [
                        'uid_local' => $uid,
                        'uid_foreign' => $groupUid,
                        'tablenames' => 'fe_groups',
                        'sorting' => $key + 1,
                        'offset' => $this->conf['view.']['event.']['remind.']['time'],
                        'pid' => $eventData['pid']
                    ]
                );
            }
        } elseif ($this->conf['rights.']['create.']['event.']['addFeUserToNotify'] && $this->rightsObj->isLoggedIn()) {
            $this->subscriptionRepository->insert(
                [
                    'uid_local' => $uid,
                    'uid_foreign' => $this->rightsObj->getUserId(),
                    'tablenames' => 'fe_users',
                    'sorting' =>  1,
                    'offset' => $this->conf['view.']['event.']['remind.']['time'],
                    'pid' => $eventData['pid']
                ]
            );
        }
        if ($this->conf['rights.']['create.']['event.']['public']) {
            foreach (GeneralUtility::trimExplode(',', $this->conf['rights.']['create.']['event.']['notifyUsersOnPublicCreate'], 1) as $key => $userUid) {
                $this->subscriptionRepository->insert(
                    [
                        'uid_local' => $uid,
                        'uid_foreign' => $userUid,
                        'tablenames' => 'fe_users',
                        'sorting' => $key + 1,
                        'offset' => $this->conf['view.']['event.']['remind.']['time'],
                        'pid' => $eventData['pid']
                    ]
                );
            }
        }
        if ($tempValues['exception_ids'] !== '' && $this->rightsObj->isAllowedToCreateEventException()) {
            $user = [];
            $group = [];
            self::splitUserAndGroupIds(explode(',', strip_tags($tempValues['exception_ids'])), $user, $group);
            self::insertIdsIntoTableWithMMRelation('tx_cal_exception_event_mm', $user, $uid, 'tx_cal_exception_event');
            self::insertIdsIntoTableWithMMRelation(
                'tx_cal_exception_event_group_mm',
                $group,
                $uid,
                'tx_cal_exception_event_group'
            );
        }

        if ($this->rightsObj->isAllowedToCreateEventShared()) {
            $user = $object->getSharedUsers();
            $group = $object->getSharedGroups();
            if ($this->conf['rights.']['create.']['event.']['addFeUserToShared']) {
                $user[] = $this->rightsObj->getUserId();
            }
            $this->eventSharedUserMMRepository->insertIdsIntoTableWithMMRelation(array_unique($user), $uid, 'fe_users');
            $ignore = GeneralUtility::trimExplode(
                ',',
                $this->conf['rights.']['create.']['event.']['addFeGroupToShared.']['ignore'],
                1
            );
            $groupArray = array_diff($group, $ignore);
            $this->eventSharedUserMMRepository->insertIdsIntoTableWithMMRelation(array_unique($groupArray), $uid, 'fe_groups');
        } else {
            $idArray = explode(',', $this->conf['rights.']['create.']['event.']['fields.']['shared.']['defaultUser']);
            if ($this->conf['rights.']['create.']['event.']['addFeUserToShared']) {
                $idArray[] = $this->rightsObj->getUserId();
            }
            $this->eventSharedUserMMRepository->insertIdsIntoTableWithMMRelation(array_unique($idArray), $uid, 'fe_users');

            $groupArray = GeneralUtility::trimExplode(
                ',',
                $this->conf['rights.']['create.']['event.']['fields.']['shared.']['defaultGroup'],
                1
            );
            if ($this->conf['rights.']['create.']['event.']['addFeGroupToShared']) {
                $idArray = $this->rightsObj->getUserGroups();
                $ignore = GeneralUtility::trimExplode(
                    ',',
                    $this->conf['rights.']['create.']['event.']['addFeGroupToShared.']['ignore'],
                    1
                );
                $groupArray = array_diff($idArray, $ignore);
            }
            $this->eventSharedUserMMRepository->insertIdsIntoTableWithMMRelation(array_unique($groupArray), $uid, 'fe_groups');
        }

        $category_mm_relation_table = 'sys_category_record_mm';
        $switchUidLocalForeign = true;
        if ($this->rightsObj->isAllowedToCreateEventCategory()) {
            $categoryIds = [];
            foreach ($object->getCategories() as $category) {
                if (is_object($category)) {
                    $categoryIds[] = $category->getUid();
                }
            }
            self::insertIdsIntoTableWithMMRelation(
                $category_mm_relation_table,
                $categoryIds,
                $uid,
                '',
                [],
                $switchUidLocalForeign
            );
        } else {
            self::insertIdsIntoTableWithMMRelation($category_mm_relation_table, [
                $this->conf['rights.']['create.']['event.']['fields.']['category.']['default']
            ], $uid, '', [], $switchUidLocalForeign);
        }

        if ($this->rightsObj->isAllowedTo(
            'create',
            'event',
            'attendee'
        ) && $object->getEventType() === Model::EVENT_TYPE_MEETING) {
            $attendeeUids = [];
            $modelObj = &Registry::Registry('basic', 'modelcontroller');
            $attendeeService = $modelObj->getServiceObjByKey('cal_attendee_model', 'attendee', 'tx_cal_attendee');
            foreach ($object->getAttendees() as $serviceKey => $attendees) {
                foreach ($attendees as $attendee) {
                    /** @var AttendeeModel $attendee */
                    $attendeeValues = [
                        'pid' => $this->conf['rights.']['create.']['attendee.']['saveAttendeeToPid'] ?: $insertFields['pid'],
                        'tstamp' => $insertFields['tstamp'],
                        'crdate' => $insertFields['crdate']
                    ];
                    $attendeeValues['event_id'] = $uid;
                    $attendeeValues['fe_user_id'] = $attendee->getFeUserId();
                    $attendeeValues['email'] = $attendee->getEmail();
                    $attendeeValues['attendance'] = $attendee->getAttendance();
                    $attendeeValues['status'] = $attendee->getStatus();
                    $attendeeValues['cruser_id'] = $insertFields['cruser_id'];
                    $attendeeService->_saveAttendee($attendeeValues);
                    $attendeeUids[] =  $connection->lastInsertId($table);
                }
            }
            $insertFields['attendee'] = count($attendeeUids);
        }
        return $uid;
    }

    /**
     * @param $uid
     * @return EventModel
     * @throws \TYPO3\CMS\Core\Exception
     */
    public function updateEvent($uid): EventModel
    {
        $insertFields = [
            'tstamp' => time()
        ];

        $event = $this->find($uid, $this->conf['pidList'], true, true, false, true, true, '0,1,2,3,4');
        $event_old = $this->find($uid, $this->conf['pidList'], true, true, false, true, true, '0,1,2,3,4');

        $uid = self::checkUidForLanguageOverlay($uid, 'tx_cal_event');

        $this->conf['category'] = $this->conf['view.']['allowedCategories'];
        $this->conf['calendar'] = $this->conf['view.']['allowedCalendar'];

        $event->updateWithPIVars($this->controller->piVars);

        if ($this->conf['option'] === 'move') {
            if ($this->rightsObj->isAllowedToEditEventDateTime()) {
                if (is_object($event->getStart())) {
                    /** @var CalendarDateTime $start */
                    $start = $event->getStart();
                    $insertFields['start_date'] = $start->format('Ymd');
                    $insertFields['start_time'] = intval($start->format('H')) * 3600 + intval($start->format('i')) * 60;
                }
                if (is_object($event->getEnd())) {
                    /** @var CalendarDateTime $end */
                    $end = $event->getEnd();
                    $insertFields['end_date'] = $end->format('Ymd');
                    $insertFields['end_time'] = intval($end->format('H')) * 3600 + intval($end->format('i')) * 60;
                }
            }
        } else {
            $this->searchForAdditionalFieldsToAddFromPostData($insertFields, 'event', false);

            $this->filterDataToBeUpdated($insertFields, $event);

            if (isset($this->controller->piVars['notify_ids'])) {
                $insertFields['notify_ids'] = strip_tags($this->controller->piVars['notify_ids']);
            } elseif (is_array($this->controller->piVars['notify'])) {
                $insertFields['notify_ids'] = strip_tags(implode(',', $this->controller->piVars['notify']));
            }
            if (isset($this->controller->piVars['exception_ids'])) {
                if (is_array($this->controller->piVars['exception_ids'])) {
                    $insertFields['exception_ids'] = strip_tags(implode(
                        ',',
                        $this->controller->piVars['exception_ids']
                    ));
                } else {
                    $insertFields['exception_ids'] = strip_tags($this->controller->piVars['exception_ids']);
                }
            }

            $this->_updateEvent($uid, $insertFields, $event);
        }

        self::_notifyOfChanges($event_old, $insertFields);
        if ($event->getSendOutInvitation()) {
            $this->_invite($event);
        }
        $this->unsetPiVars();

        /** @var RecurrenceGenerator $rgc */
        $rgc = $this->objectManager->get(RecurrenceGenerator::class, $GLOBALS['TSFE']->id);
        $rgc->generateIndexForUid($uid, 'tx_cal_event');

        // Hook: updateEvent
        $hookObjectsArr = Functions::getHookObjectsArray(
            'tx_cal_event_service',
            'eventServiceClass'
        );
        Functions::executeHookObjectsFunction($hookObjectsArr, 'updateEvent', $this, $event);

        Functions::clearCache();
        return $event;
    }

    /**
     * @param $uid
     * @param $eventData
     * @param EventModel $object
     */
    public function _updateEvent($uid, $eventData, $object)
    {
        $tempValues = [];
        $tempValues['notify_ids'] = $eventData['notify_ids'];
        $tempValues['notify_offset'] = $eventData['notify_offset'] ?: $this->conf['view.']['event.']['remind.']['time'];
        unset($eventData['notify_ids']);
        $tempValues['exception_ids'] = $eventData['exception_ids'];
        unset($eventData['exception_ids']);
        $tempValues['attendee_ids'] = $eventData['attendee_ids'];
        unset($eventData['attendee_ids']);

        // Creating DB records
        $table = 'tx_cal_event';
        $where = ['uid' =>  $uid];
        $connection = $this->connectionPool->getConnectionForTable($table);

        $queryBuilder = $connection->createQueryBuilder();
        if (TYPO3_MODE == 'BE') {
            $queryBuilder
                ->getRestrictions()
                ->removeAll()
                ->add(GeneralUtility::makeInstance(DeletedRestriction::class));
        } else {
            $queryBuilder->setRestrictions(GeneralUtility::makeInstance(FrontendRestrictionContainer::class));
        }
        $result = $queryBuilder->update($table, $where, $eventData);
        if (false === $result) {
            throw new RuntimeException(
                'Could not write event record to database: ' . debug($queryBuilder->getSQL()),
                1431458130
            );
        }
        $eventData['pid'] = $object->getPid();

        if ($this->rightsObj->isAllowedTo('edit', 'event', 'image')) {
            $this->checkOnNewOrDeletableFiles('tx_cal_event', 'image', $eventData, $uid);
        }

        if ($this->rightsObj->isAllowedTo('edit', 'event', 'attachment')) {
            $this->checkOnNewOrDeletableFiles('tx_cal_event', 'attachment', $eventData, $uid);
        }

        if ($this->rightsObj->isAllowedToEditEventCategory()) {
            $where = 'uid_local = ' . $uid;
            $category_mm_relation_table = 'sys_category_record_mm';
            $switchUidLocalForeign = true;
            $GLOBALS['TYPO3_DB']->exec_DELETEquery($category_mm_relation_table, $where);

            $categoryIds = [];
            foreach ($object->getCategories() as $category) {
                if (is_object($category)) {
                    $categoryIds[] = $category->getUid();
                }
            }
            self::insertIdsIntoTableWithMMRelation(
                $category_mm_relation_table,
                $categoryIds,
                $uid,
                '',
                [],
                $switchUidLocalForeign
            );
        }

        if ($this->rightsObj->isAllowedToEditEventNotify() && $tempValues['notify_ids'] !== null) {
            $this->subscriptionRepository->deleteByEventUid($uid);
            if ($tempValues['notify_ids'] !== '') {
                $user = [];
                $group = [];
                self::splitUserAndGroupIds(explode(',', strip_tags($tempValues['notify_ids'])), $user, $group);
                foreach ($user as $u) {
                    $userOffsetArray = GeneralUtility::trimExplode('_', $u, 1);
                    $this->subscriptionRepository->insert(
                        [
                            'uid_local' => $uid,
                            'uid_foreign' => $userOffsetArray[0],
                            'tablenames' => 'fe_users',
                            'sorting' => 1,
                            'offset' => $userOffsetArray[1] ?? $this->conf['view.']['event.']['remind.']['time'],
                            'pid' => $eventData['pid']
                        ]
                    );
                }
                $ignore = GeneralUtility::trimExplode(
                    ',',
                    $this->conf['rights.']['create.']['event.']['addFeGroupToNotify.']['ignore'],
                    1
                );
                foreach ($group as $g) {
                    $groupOffsetArray = GeneralUtility::trimExplode('_', $g, 1);
                    if (!in_array($groupOffsetArray[0], $ignore, true)) {
                        $this->subscriptionRepository->insert(
                            [
                                'uid_local' => $uid,
                                'uid_foreign' => $groupOffsetArray[0],
                                'tablenames' => 'fe_groups',
                                'sorting' => 1,
                                'offset' => $groupOffsetArray[1] ?? $this->conf['view.']['event.']['remind.']['time'],
                                'pid' => $eventData['pid']
                            ]
                        );
                    }
                }
            }
        } else {
            $userIdArray = GeneralUtility::trimExplode(
                ',',
                $this->conf['rights.']['edit.']['event.']['fields.']['notify.']['defaultUser'],
                1
            );
            if ($this->conf['rights.']['edit.']['event.']['addFeUserToNotify']) {
                $userIdArray[] = $this->rightsObj->getUserId();
            }

            $groupIdArray = GeneralUtility::trimExplode(
                ',',
                $this->conf['rights.']['edit.']['event.']['fields.']['notify.']['defaultGroup'],
                1
            );
            if ($this->conf['rights.']['edit.']['event.']['addFeGroupToNotify']) {
                $groupIdArray = $this->rightsObj->getUserGroups();
                $ignore = GeneralUtility::trimExplode(
                    ',',
                    $this->conf['rights.']['edit.']['event.']['addFeGroupToNotify.']['ignore'],
                    1
                );
                $groupIdArray = array_diff($groupIdArray, $ignore);
            }
            if (!empty($userIdArray) || !empty($groupIdArray)) {
                $this->subscriptionRepository->deleteByEventUid($uid);
                foreach (array_unique($userIdArray) as $key => $userUid) {
                    $this->subscriptionRepository->insert(
                        [
                            'uid_local' => $uid,
                            'uid_foreign' => $userUid,
                            'tablenames' => 'fe_users',
                            'sorting' => $key + 1,
                            'offset' => $tempValues['notify_offset'],
                            'pid' => $object->getPid()
                        ]
                    );
                }
                foreach (array_unique($groupIdArray) as $key => $groupUid) {
                    $this->subscriptionRepository->insert(
                        [
                            'uid_local' => $uid,
                            'uid_foreign' => $groupUid,
                            'tablenames' => 'fe_groups',
                            'sorting' => $key + 1,
                            'offset' => $tempValues['notify_offset'],
                            'pid' => $object->getPid()
                        ]
                    );
                }
            }
        }

        if ($tempValues['exception_ids'] !== null && $tempValues['exception_ids'] !== '' && $this->rightsObj->isAllowedToEditEventException()) {
            $table = 'tx_cal_exception_event_mm';
            $where = ['uid_local' => $uid];
            $connection = $this->connectionPool->getConnectionForTable($table);

            $queryBuilder = $connection->createQueryBuilder();
            if (TYPO3_MODE == 'BE') {
                $queryBuilder
                    ->getRestrictions()
                    ->removeAll()
                    ->add(GeneralUtility::makeInstance(DeletedRestriction::class));
            } else {
                $queryBuilder->setRestrictions(GeneralUtility::makeInstance(FrontendRestrictionContainer::class));
            }
            $queryBuilder->delete($table, $where);
            $user = [];
            $group = [];
            self::splitUserAndGroupIds(explode(',', strip_tags($tempValues['exception_ids'])), $user, $group);
            self::insertIdsIntoTableWithMMRelation($table, $user, $uid, 'tx_cal_exception_event');
            self::insertIdsIntoTableWithMMRelation($table, $group, $uid, 'tx_cal_exception_event_group');
        }

        if ($this->rightsObj->isAllowedTo('edit', 'event', 'shared')) {
            $this->eventSharedUserMMRepository->deleteByEventUid($uid);
            $this->eventSharedUserMMRepository->insertIdsIntoTableWithMMRelation(array_unique($object->getSharedUsers()), $uid, 'fe_users');
            $this->eventSharedUserMMRepository->insertIdsIntoTableWithMMRelation(array_unique($object->getSharedGroups()), $uid, 'fe_groups');
        } else {
            $userIdArray = GeneralUtility::trimExplode(
                ',',
                $this->conf['rights.']['edit.']['event.']['fields.']['shared.']['defaultUser'],
                1
            );
            if ($this->conf['rights.']['edit.']['event.']['addFeUserToShared']) {
                $userIdArray[] = $this->rightsObj->getUserId();
            }

            $groupIdArray = GeneralUtility::trimExplode(
                ',',
                $this->conf['rights.']['edit.']['event.']['fields.']['shared.']['defaultGroup'],
                1
            );
            if ($this->conf['rights.']['edit.']['event.']['addFeGroupToShared']) {
                $groupIdArray = $this->rightsObj->getUserGroups();
                $ignore = GeneralUtility::trimExplode(
                    ',',
                    $this->conf['rights.']['edit.']['event.']['addFeGroupToShared.']['ignore'],
                    1
                );
                $groupIdArray = array_diff($groupIdArray, $ignore);
            }
            if (!empty($userIdArray) || !empty($groupIdArray)) {
                $this->eventSharedUserMMRepository->deleteByEventUid($uid);
                $this->eventSharedUserMMRepository->insertIdsIntoTableWithMMRelation(array_unique($userIdArray), $uid, 'fe_users');
                $this->eventSharedUserMMRepository->insertIdsIntoTableWithMMRelation(array_unique($groupIdArray), $uid, 'fe_groups');
            }
        }
        if ($this->rightsObj->isAllowedTo(
            'edit',
            'event',
            'attendee'
        ) && $object->getEventType() === Model::EVENT_TYPE_MEETING) {
            $modelObj = &Registry::Registry('basic', 'modelcontroller');
            $attendeeServices = $modelObj->findEventAttendees($uid);

            $attendeeIndex = [];
            $attendeeServiceKeys = array_keys($attendeeServices);
            $servKey = 'tx_cal_attendee';
            $oldAttendeeUids = [
                $servKey => []
            ];
            foreach ($attendeeServiceKeys as $serviceKey) {
                $attendeeKeys = array_keys($attendeeServices[$serviceKey]);
                foreach ($attendeeKeys as $attendeeKey) {
                    $attendeeIndex[$serviceKey . '_' . ($attendeeServices[$serviceKey][$attendeeKey]->getFeUserId() ?: $attendeeServices[$serviceKey][$attendeeKey]->getEmail())] = &$attendeeServices[$serviceKey][$attendeeKey];
                    $oldAttendeeUids[$serviceKey][] = $attendeeServices[$serviceKey][$attendeeKey]->getUid();
                }
            }

            $attendeeService = $modelObj->getServiceObjByKey('cal_attendee_model', 'attendee', $servKey);

            $attendeeUids = [];
            $attendees = &$object->getAttendees();

            foreach ($attendees[$servKey] as $attendee) {
                /** @var AttendeeModel $attendee */
                if (is_object($attendeeIndex[$serviceKey . '_' . ($attendee->getFeUserId() ?: $attendee->getEmail())])) {
                    // Attendee is already assigned -> updating attendance
                    $attendeeValues = [];
                    $attendeeValues['attendance'] = $attendee->getAttendance();
                    $attendeeValues['status'] = $attendee->getStatus();
                    $attendeeValues['event_id'] = $attendee->getEventUid();
                    $attendeeService->_updateAttendee($attendee->getUid(), $attendeeValues);
                    $attendeeUids[] = $attendee->getUid();
                } else {
                    // It's a new attendee -> creating new one
                    $crdate = time();
                    $attendeeValues = [
                        'pid' => $this->conf['rights.']['create.']['attendee.']['saveAttendeeToPid'] ?: $object->getPid(),
                        'tstamp' => $crdate,
                        'crdate' => $crdate
                    ];
                    $attendeeValues['event_id'] = $uid;
                    $attendeeValues['cruser_id'] = $this->rightsObj->getUserId();
                    $attendeeValues['fe_user_id'] = $attendee->getFeUserId();
                    $attendeeValues['email'] = $attendee->getEmail();
                    $attendeeValues['attendance'] = $attendee->getAttendance();
                    $attendeeValues['status'] = $attendee->getStatus();
                    $attendeeService->_saveAttendee($attendeeValues);
                    $attendeeUids[] =  $connection->lastInsertId($table);
                }
            }
            $uidsToBeDeleted = array_diff($oldAttendeeUids[$servKey], $attendeeUids);
            if (!empty($uidsToBeDeleted)) {
               $table = 'tx_cal_attendee';
               $connection = $this->connectionPool->getConnectionForTable($table);

                $queryBuilder = $connection->createQueryBuilder();
                if (TYPO3_MODE == 'BE') {
                    $queryBuilder
                        ->getRestrictions()
                        ->removeAll()
                        ->add(GeneralUtility::makeInstance(DeletedRestriction::class));
                } else {
                    $queryBuilder->setRestrictions(GeneralUtility::makeInstance(FrontendRestrictionContainer::class));
                }
                $queryBuilder->delete('tx_cal_attendee')->where(
                   //
                // 'uid in (' . implode(',', $uidsToBeDeleted) . ')'
                    $queryBuilder->expr()->in(
                        'uid',implode(',', $uidsToBeDeleted)
                    )
                );
            }
            $eventData['attendee'] = count($attendeeUids);
        }
    }

    /**
     * @param $uid
     */
    public function removeEvent($uid)
    {
        $event = $this->find($uid, $this->conf['pidList'], true, true);
        if (is_object($event) && $event->isUserAllowedToDelete()) {
            $config = $this->conf['calendar'];
            $this->conf['calendar'] = intval($this->controller->piVars['calendar_id']);
            $event = $this->find($uid, $this->conf['pidList'], true, true);
            $this->conf['calendar'] = $config;

            $updateFields = [
                'tstamp' => time(),
                'deleted' => 1
            ];
            $table = 'tx_cal_event';
            $where = 'uid = ' . $uid;
            $result = $GLOBALS['TYPO3_DB']->exec_UPDATEquery($table, $where, $updateFields);
            if (false === $result) {
                throw new RuntimeException(
                    'Could not delete event record from database: ' . debug($queryBuilder->getSQL()),
                    1431458133
                );
            }

            $fields = $event->getValuesAsArray();
            $fields['deleted'] = 1;
            $fields['tstamp'] = $updateFields['tstamp'];

            self::_notify($fields);
            self::stopReminder($uid);

            /** @var RecurrenceGenerator $rgc */
            $rgc = $this->objectManager->get(RecurrenceGenerator::class);
            $rgc->cleanIndexTableOfUid($uid, $table);

            // Hook: removeEvent
            $hookObjectsArr = Functions::getHookObjectsArray(
                'tx_cal_event_service',
                'eventServiceClass'
            );
            Functions::executeHookObjectsFunction($hookObjectsArr, 'removeEvent', $this, $event);
            Functions::clearCache();
            $this->unsetPiVars();
        }
    }

    /**
     * @param $insertFields
     * @param EventModel $object
     */
    public function filterDataToBeSaved(&$insertFields, &$object)
    {
        $hidden = 0;
        if (!$this->rightsObj->isAllowedToEditEventHidden() && isset($this->conf['rights.']['create.']['event.']['fields.']['hidden.']['default']) && !$this->rightsObj->isAllowedToCreateEventHidden()) {
            $hidden = $this->conf['rights.']['create.']['event.']['fields.']['hidden.']['default'];
        } elseif ($object->isHidden() && $this->rightsObj->isAllowedToCreateEventHidden()) {
            $hidden = 1;
        }
        $insertFields['hidden'] = $hidden;
        $insertFields['type'] = $object->getEventType();
        if (empty($insertFields['type'])) {
            $insertFields['type'] = Model::EVENT_TYPE_DEFAULT;
        }

        $insertFields['allday'] = $object->isAllDay() ? '1' : '0';
        if (!$this->rightsObj->isAllowedTo('create', 'event', 'allday')) {
            $insertFields['allday'] = $this->conf['rights.']['create.']['event.']['fields.']['allday.']['default'];
        }
        if ($this->rightsObj->isAllowedToCreateEventCalendar()) {
            if ($object->getCalendarUid() != '') {
                $insertFields['calendar_id'] = $object->getCalendarUid();
            } elseif ($this->conf['rights.']['create.']['event.']['fields.']['calendar.']['default']) {
                $insertFields['calendar_id'] = $this->conf['rights.']['create.']['event.']['fields.']['calendar_id.']['default'];
            } else {
                $insertFields['calendar_id'] = ''; // TODO: Set the calendar_id to some value
            }
        }

        if ($this->rightsObj->isAllowedToCreateEventDateTime()) {
            if (is_object($object->getStart())) {
                $start = $object->getStart();
                $insertFields['start_date'] = $start->format('Ymd');
                $insertFields['start_time'] = intval($start->format('H')) * 3600 + intval($start->format('i')) * 60;
            } else {
                return;
            }
            if (is_object($object->getEnd())) {
                $end = $object->getEnd();
                $insertFields['end_date'] = $end->format('Ymd');
                $insertFields['end_time'] = intval($end->format('H')) * 3600 + intval($end->format('i')) * 60;
            } else {
                return;
            }
        }
        if ($this->rightsObj->isAllowedToCreateEventTitle()) {
            $insertFields['title'] = $object->getTitle();
        }

        if ($this->rightsObj->isAllowedToCreateEventOrganizer()) {
            $insertFields['organizer'] = $object->getOrganizer();
        }
        if (empty($insertFields['organizer'])) {
            $insertFields['organizer'] = '';
        }
        if ($this->rightsObj->isAllowedTo('create', 'event', 'cal_organizer')) {
            $insertFields['organizer_id'] = $object->getOrganizerId();
        }
        if ($this->rightsObj->isAllowedToCreateEventLocation()) {
            $insertFields['location'] = $object->getLocation();
        }
        if (empty($insertFields['location'])) {
            $insertFields['location'] = '';
        }
        if ($this->rightsObj->isAllowedTo('create', 'event', 'cal_location')) {
            $insertFields['location_id'] = $object->getLocationId();
        }
        if ($object->getTeaser() !== '' && $this->rightsObj->isAllowedToCreateEventTeaser()) {
            $insertFields['teaser'] = $object->getTeaser();
        }
        if ($object->getDescription() !== '' && $this->rightsObj->isAllowedToCreateEventDescription()) {
            $insertFields['description'] = $object->getDescription();
        }
        if ($this->rightsObj->isAllowedToCreateEventRecurring()) {
            $insertFields['freq'] = $object->getFreq();
            $insertFields['byday'] = strtolower(implode(',', $object->getByDay()));
            $insertFields['bymonthday'] = implode(',', $object->getByMonthDay());
            $insertFields['bymonth'] = implode(',', $object->getByMonth());
            $until = $object->getUntil();
            if (is_object($until)) {
                $insertFields['until'] = $until->format('Ymd');
            }
            $insertFields['cnt'] = $object->getCount();
            $insertFields['intrval'] = $object->getInterval();
        }

        $eventArray = ['calendar_id' => $object->getCalendarUid(), 'uid' => $object->getUid()];
        $insertFields['icsUid'] = Functions::getIcsUid($this->conf, $eventArray);

        // Hook initialization:
        $hookObjectsArr = [];
        if (is_array($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['EXTCONF']['ext/cal/service/class.tx_cal_event_service.php']['addAdditionalField'])) {
            foreach ($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['EXTCONF']['ext/cal/service/class.tx_cal_event_service.php']['addAdditionalField'] as $classRef) {
                $hookObjectsArr[] = &GeneralUtility::getUserObj($classRef);
            }
        }

        foreach ($hookObjectsArr as $hookObj) {
            if (method_exists($hookObj, 'addAdditionalField')) {
                $hookObj->addAdditionalField($insertFields, $this);
            }
        }
    }

    /**
     * @param $insertFields
     * @param EventModel $object
     */
    public function filterDataToBeUpdated(&$insertFields, &$object)
    {
        $hidden = 0;
        if (!$this->rightsObj->isAllowedToEditEventHidden() && isset($this->conf['rights.']['edit.']['event.']['fields.']['hidden.']['default']) && !$this->rightsObj->isAllowedToCreateEventHidden()) {
            $hidden = $this->conf['rights.']['edit.']['event.']['fields.']['hidden.']['default'];
        } elseif ($object->isHidden() && $this->rightsObj->isAllowedToEditEventHidden()) {
            $hidden = 1;
        }
        $insertFields['hidden'] = $hidden;

        if ($this->rightsObj->isAllowedTo('edit', 'event', 'type')) {
            $insertFields['type'] = $object->getEventType();
        }

        $insertFields['allday'] = $object->isAllDay() ? '1' : '0';
        if (!$this->rightsObj->isAllowedTo('edit', 'event', 'allday')) {
            $insertFields['allday'] = $this->conf['rights.']['edit.']['event.']['fields.']['allday.']['default'];
        }

        if ($this->rightsObj->isAllowedToEditEventCalendar()) {
            if ($object->getCalendarUid() != '') {
                $insertFields['calendar_id'] = $object->getCalendarUid();
            } elseif ($this->conf['rights.']['edit.']['event.']['fields.']['calendar.']['default']) {
                $insertFields['calendar_id'] = $this->conf['rights.']['edit.']['event.']['fields.']['calendar_id.']['default'];
            } else {
                $insertFields['calendar_id'] = ''; // TODO: Set the calendar_id to some value
            }
        }

        if ($this->rightsObj->isAllowedToEditEventDateTime()) {
            if (is_object($object->getStart())) {
                $start = $object->getStart();
                $insertFields['start_date'] = $start->format('Ymd');
                $insertFields['start_time'] = intval($start->format('H')) * 3600 + intval($start->format('i')) * 60;
            } else {
                return;
            }
            if (is_object($object->getEnd())) {
                $end = $object->getEnd();
                $insertFields['end_date'] = $end->format('Ymd');
                $insertFields['end_time'] = intval($end->format('H')) * 3600 + intval($end->format('i')) * 60;
            } else {
                return;
            }
        }
        if ($this->rightsObj->isAllowedToEditEventTitle()) {
            $insertFields['title'] = $object->getTitle();
        }

        if ($this->rightsObj->isAllowedToEditEventOrganizer()) {
            $insertFields['organizer'] = $object->getOrganizer();
        }
        if ($this->rightsObj->isAllowedTo('edit', 'event', 'cal_organizer')) {
            $insertFields['organizer_id'] = $object->getOrganizerId();
        }
        if ($this->rightsObj->isAllowedToEditEventLocation()) {
            $insertFields['location'] = $object->getLocation();
        }
        if ($this->rightsObj->isAllowedTo('edit', 'event', 'cal_location')) {
            $insertFields['location_id'] = $object->getLocationId();
        }
        if ($object->getTeaser() !== '' && $this->rightsObj->isAllowedToEditEventTeaser()) {
            $insertFields['teaser'] = $object->getTeaser();
        }
        if ($object->getDescription() !== '' && $this->rightsObj->isAllowedToEditEventDescription()) {
            $insertFields['description'] = $object->getDescription();
        }
        if ($this->rightsObj->isAllowedToEditEventRecurring()) {
            $insertFields['freq'] = $object->getFreq();
            $insertFields['byday'] = strtolower(implode(',', $object->getByDay()));
            $insertFields['bymonthday'] = implode(',', $object->getByMonthDay());
            $insertFields['bymonth'] = implode(',', $object->getByMonth());
            $until = $object->getUntil();
            $insertFields['until'] = $until->format('Ymd');
            $insertFields['cnt'] = $object->getCount();
            $insertFields['intrval'] = $object->getInterval();
            $insertFields['rdate_type'] = $object->getRdateType();
            $insertFields['rdate'] = $object->getRdate();
        }

        // Hook initialization:
        $hookObjectsArr = [];
        if (is_array($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['EXTCONF']['ext/cal/service/class.tx_cal_event_service.php']['addAdditionalField'])) {
            foreach ($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['EXTCONF']['ext/cal/service/class.tx_cal_event_service.php']['addAdditionalField'] as $classRef) {
                $hookObjectsArr[] = &GeneralUtility::getUserObj($classRef);
            }
        }

        foreach ($hookObjectsArr as $hookObj) {
            if (method_exists($hookObj, 'addAdditionalField')) {
                $hookObj->addAdditionalField($insertFields, $this);
            }
        }
    }

    /**
     * @param string $pidList
     * @param CalendarDateTime $start_date
     * @param CalendarDateTime $end_date
     * @param string $searchword
     * @param string $locationIds
     * @param string $organizerIds
     * @param string $eventType
     * @return array
     */
    public function search(
        $pidList,
        $start_date,
        $end_date,
        $searchword,
        $locationIds = '',
        $organizerIds = '',
        $eventType = '0,1,2,3'
    ): array {
        $start_date->subtractSeconds($this->conf['view.'][$this->conf['view'] . '.']['startPointCorrection']);
        $end_date->addSeconds($this->conf['view.'][$this->conf['view'] . '.']['endPointCorrection']);

        $this->starttime = new CalendarDateTime();
        $this->endtime = new CalendarDateTime();

        $this->starttime->copy($start_date);
        $this->endtime->copy($end_date);

        $formattedStarttime = $this->starttime->format('Ymd');
        $formattedEndtime = $this->endtime->format('Ymd');

        $additionalSearch = '';
        if ($searchword !== '') {
            $additionalSearch = $this->searchWhere($searchword);
        }

        $linkIds = $this->conf['calendar'] ?: '';

        /**
         * @fixme Always include public events.
         * Do we really want to do this?
         * If so, find a prettier way than hardcoding it.
         */
        $includePublic = 1;

        $calendarService = &$this->modelObj->getServiceObjByKey('cal_calendar_model', 'calendar', 'tx_cal_calendar');
        $categoryService = $this->objectManager->get(SysCategoryService::class);

        $calendarSearchString = $calendarService->getCalendarSearchString(
            $pidList,
            $includePublic,
            $linkIds,
            $this->conf['view.']['calendar'] ?: ''
        );

        $timeSearchString = ' AND tx_cal_event.pid IN (' . $pidList . ') ' . $this->cObj->enableFields('tx_cal_event') . ' AND (((tx_cal_event.start_date>=' . $formattedStarttime . ' AND tx_cal_event.start_date<=' . $formattedEndtime . ') OR (tx_cal_event.end_date<=' . $formattedEndtime . ' AND tx_cal_event.end_date>=' . $formattedStarttime . ') OR (tx_cal_event.end_date>=' . $formattedEndtime . ' AND tx_cal_event.start_date<=' . $formattedStarttime . ') OR (tx_cal_event.start_date<=' . $formattedEndtime . ' AND (tx_cal_event.freq IN ("day","week","month","year") AND (tx_cal_event.until>=' . $formattedStarttime . ' OR tx_cal_event.until=0)))) OR (tx_cal_event.rdate AND tx_cal_event.rdate_type IN ("date_time","date","period"))) ';

        if ($locationIds !== '' && $locationIds !== '0') {
            $locationSearchString = ' AND location_id in (' . $locationIds . ')';
        }

        if ($organizerIds !== '' && $organizerIds !== '0') {
            $organizerSearchString = ' AND organizer_id in (' . $organizerIds . ')';
        }

        // putting everything together
        $additionalWhere = $calendarSearchString . $timeSearchString . $locationSearchString . $organizerSearchString . $additionalSearch;
        $categories = [];
        $categoryService->getCategoryArray($pidList, $categories);
        return $this->getEventsFromTable($categories[0][0], true, $additionalWhere, '', true, false, $eventType);
    }

    /**
     * Generates a search where clause.
     *
     * @param string $sw :
     * @return string
     */
    public function searchWhere($sw): string
    {
        if (0 === strpos($this->conf['view'], 'search') && $GLOBALS['TSFE']->sys_language_content > 0) {
            return $this->cObj->searchWhere(
                $sw,
                $this->conf['view.']['search.']['searchEventFieldList'],
                'tx_cal_event_l18n'
            );
        }
        return $this->cObj->searchWhere($sw, $this->conf['view.']['search.']['searchEventFieldList'], 'tx_cal_event');
    }

    /**
     * This function looks, if the event is a recurring event
     * and creates the recurrings events for a given time.
     * The starting and ending dates are calculated from the conf
     * array ('gedate' and 'view').
     *
     * @param EventModel $event object
     * @return array
     */
    public function recurringEvent(EventModel $event): array
    {
        $deviations = [];
        $select = '*';
        $table = 'tx_cal_event_deviation';
        $connection = $this->connectionPool->getConnectionForTable('tx_cal_calendar');

        $queryBuilder = $connection->createQueryBuilder();

        if (TYPO3_MODE == 'BE') {
            $where = 'parentid = ' . $event->getUid() . BackendUtility::BEenableFields('tx_cal_event_deviation');

            $queryBuilder
                ->getRestrictions()
                ->removeAll()
                ->add(GeneralUtility::makeInstance(DeletedRestriction::class));
        } else {
            $where = 'parentid = ' . $event->getUid() . $this->cObj->enableFields('tx_cal_event_deviation');
            $queryBuilder->setRestrictions(GeneralUtility::makeInstance(FrontendRestrictionContainer::class));
        }
        $deviationResult =  $queryBuilder->select('*')
            ->from($table)
            ->where(
                $queryBuilder->expr()->eq(
                    'parentid',
                    $queryBuilder->createNamedParameter($event->getUid(), \PDO::PARAM_INT)
                )
            )
            ->execute();
       // $deviationResult = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select, $table, $where);
        if ($deviationResult->rowCount()>0) {
            while ($deviationRow = $deviationResult->fetch(\PDO::FETCH_ASSOC)) {
                if ($deviationRow['deleted']) {
                    continue;
                }
                $origStartDate = GeneralUtility::makeInstance(CalendarDateTime::class)->createFromFormat('U', $deviationRow['orig_start_date']);
                $origStartDate->add(new \DateInterval('PT' . $deviationRow['orig_start_time'] . 'S'));
                $deviations[$origStartDate->format('YmdHi')] = $deviationRow;
            }
          //  $GLOBALS['TYPO3_DB']->sql_free_result($deviationResult);
        }
//        else{
//            $new_event_deviation = new EventDeviationModel(
//                $event,
//                $event->getRow(),
//                $nextOccuranceTime,
//                $currentUntil
//            );
//
//            $queryBuilder->insert($table)->values($new_event_deviation->getRow())->execute();
//        }
        #todo geneerate deviations if not found
        $event->setDeviationDates($deviations);

        $this->filterFalseCombinations($event);
        $this->checkRecurringSettings($event);

        $master_array = [];
        //$until = new CalendarDateTime();
        $until = $event->getUntil();
        $until->addSeconds(86399);
        $count = intval($event->getCount());

        if ($this->endtime->before($until)) {
            $until->copy($this->endtime);
        }
        $byyear = [];
   //     $eventStart = new CalendarDateTime();
        $eventStart = $event->getStart();
        $i = $eventStart->getYear();
        if ($event->getFreq() === 'year') {
            $i = intval($this->starttime->getYear()) - (($this->starttime->getYear() - $eventStart->getYear()) % $event->getInterval());
        }
        else if ($event->getFreq() != "none"){
            $until->setYear($this->endtime->getYear());
            $i = intval($this->starttime->getYear()) - (($this->starttime->getYear() - $eventStart->getYear()) % $event->getInterval());
        }

        for (; $i < intval($until->getYear()) + 1; $i++) {
            $byyear[] = $i;
        }
        /* If starttime is before or at the same time as the event date, add the event */
        if ($this->starttime->compare($this->starttime, $eventStart) !== 1 || $event->getFreq() === 'none') {
            if ($event->isAllday()) {
                $master_array[$eventStart->format('Ymd')]['-1'][$event->getUid()] = $event;
            } else {
                $master_array[$eventStart->format('Ymd')][$eventStart->format('Hi')][$event->getUid()] = $event;
            }
        }

        // new feature for limiting f.e. the listed recurring events in listView
        $maxRecurringEvents = [];
        if (TYPO3_MODE !== 'BE') {
            $maxRecurringEvents = (int)$this->conf['view.'][$this->conf['view'] . '.']['maxRecurringEvents'];
        }
        $maxRecurringEvents = !empty($maxRecurringEvents) ? $maxRecurringEvents : $count;

        $counter = 1;
        $total = 1;

        // if the 'parent' event is still in future, set $added to 1 (true), because we already have one instance of this event
        $added = (int)$eventStart->isFuture();
        $nextOccuranceTime = new CalendarDateTime();
        $nextOccuranceTime->copy($event->getStart());
        $nextOccuranceTime->addSeconds(86400);

        if ($event->getRdateType() && $event->getRdateType() !== 'none') {
            $this->getRecurringDate($master_array, $event, $added);
        }

        switch ($event->getFreq()) {
            case 'day':
                $this->findDailyWithin(
                    $master_array,
                    $event,
                    $nextOccuranceTime,
                    $until,
                    $event->getByDay(),
                    $count,
                    $counter,
                    $total,
                    $added,
                    $maxRecurringEvents
                );
                break;
            case 'week':
            case 'month':
            case 'year':

                $bymonth = $event->getByMonth();
                $byday = $event->getByDay();
                $hour = $eventStart->format('H');
                $minute = $eventStart->format('i');
                // 2007, 2008...
                // walk thru years to index (not event)
                $table = 'tx_cal_event_deviation';
                $connection = $this->connectionPool->getConnectionForTable($table);
                $queryBuilder = $connection->createQueryBuilder();
                if (TYPO3_MODE == 'BE') {

                    $queryBuilder
                        ->getRestrictions()
                        ->removeAll()
                        ->add(GeneralUtility::makeInstance(DeletedRestriction::class));
                } else {
                    $queryBuilder->setRestrictions(GeneralUtility::makeInstance(FrontendRestrictionContainer::class));
                }

                foreach ($byyear as $year) {
                    if ($counter < $count && $until->after($nextOccuranceTime) && $added < $maxRecurringEvents) {
                        // 1,2,3,4,5,6,7,8,9,10,11,12
                        foreach ($bymonth as $month) {
                            if ($counter < $count && $until->after($nextOccuranceTime) && (int)str_pad(
                                $year,
                                2,
                                '0',
                                STR_PAD_LEFT
                            ) . str_pad(
                                        $month,
                                        2,
                                        '0',
                                        STR_PAD_LEFT
                                    ) >= (int)$nextOccuranceTime->format('Y') . $nextOccuranceTime->format('m') && $added < $maxRecurringEvents) {

                                $bymonthday =  $this->getMonthDaysAccordingly($event, $month, $year);
                                // 1,2,3,4....31
                                foreach ($bymonthday as $day) {
                                    $nextOccuranceTime->setHour($hour);
                                    $nextOccuranceTime->setMinute($minute);
                                    $nextOccuranceTime->setSecond(0);
                                    $nextOccuranceTime->setDay($day);
                                    $nextOccuranceTime->setMonth($month);
                                    $nextOccuranceTime->setYear($year);

                                    if ($counter < $count && ($until->after($nextOccuranceTime) || $until->equals($nextOccuranceTime)) && $added < $maxRecurringEvents) {
                                        $currentUntil = new CalendarDateTime();
                                        $currentUntil->copy($nextOccuranceTime);
                                        $currentUntil->addSeconds(86399);
                                        if (((int)$nextOccuranceTime->getMonth() === $month && $eventStart->before($nextOccuranceTime)) || $eventStart->equals($nextOccuranceTime)) {

                                        //# todo insert records


                                           // $this->eventdeviationRepository->add($new_event_deviation);
                                            $this->findDailyWithin(
                                                $master_array,
                                                $event,
                                                $nextOccuranceTime,
                                                $currentUntil,
                                                $byday,
                                                $count,
                                                $counter,
                                                $total,
                                                $added,
                                                $maxRecurringEvents
                                            );
//                                            $new_event_deviation = new EventDeviationModel(
//                                                $event,
//                                                $event->getRow(),
//                                                $nextOccuranceTime,
//                                                $currentUntil
//                                            );
////
//
//
//                                            $queryBuilder->insert($table)->values($new_event_deviation->getRow())->execute();
                                        } else {
                                            continue;
                                        }
                                    } else {
                                        return $master_array;
                                    }
                                }
                            }
                        }
                    } else {
                        return $master_array;
                    }
                }
                break; // switch-case break
        }
        return $master_array;
    }

    /**
     * @param EventModel $event
     * @param array $ex_event_dates
     * @return array
     */
    public function getRecurringEventsFromIndex(EventModel $event, $ex_event_dates = []): array
    {
        $maxRecurringEvents = (int)$this->conf['view.'][$this->conf['view'] . '.']['maxRecurringEvents'];
        $maxRecurringEvents = !empty($maxRecurringEvents) ? $maxRecurringEvents : 999;

        $master_array = [];
        $startDate = $event->getStart();
        $dontShowOldEvents = (int)$this->conf['view.'][$this->conf['view'] . '.']['dontShowOldEvents'];
        if ($dontShowOldEvents > 0) {
            $now = new CalendarDateTime();
            if ($dontShowOldEvents === 2) {
                $now->setHour(0);
                $now->setMinute(0);
                $now->setSecond(0);
            }

            if ($startDate->format('U') > $now->format('U') && !$ex_event_dates[$startDate->format('Ymd')]) {
                $master_array[$startDate->format('Ymd')][$event->isAllday() ? '-1' : $startDate->format('Hi')][$event->getUid()] = &$event;
            }
        } elseif (!$ex_event_dates[$startDate->format('Ymd')]
            && (!$event->getStart()->after($this->endtime))
            && (!$event->getEnd()->before($this->starttime))) {
            $master_array[$startDate->format('Ymd')][$event->isAllday() ? '-1' : $startDate->format('Hi')][$event->getUid()] = &$event;
        }

        $added = 0;
        // if the 'parent' event is still in future, set $added to 1, because we already have one instance of this event
        $now = new CalendarDateTime();
        if ((int)$now->format('YmdHi') < (int)$event->getStart()->format('YmdHi')) {
            $added = 1;
        }
        $select = '*';
        $table = 'tx_cal_index';
        $where = 'event_uid = ' . $event->getUid() . ' AND start_datetime >= ' .$event->getStart()->format('YmdHi') . ' AND start_datetime <= ' . $event->getEnd()->format('YmdHi') . ' AND tablename = "' . ($event->getType() === 'tx_cal_phpicalendar' ? ($event->isException ? 'tx_cal_exception_event' : 'tx_cal_event') : $event->getType()) . '"';
        $result = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select, $table, $where, '', 'start_datetime');
        if ($result) {
            while ($r = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
                if ($added < $maxRecurringEvents) {
                    $nextOccuranceTime = new CalendarDateTime($r['start_datetime']);
                    $nextOccuranceEndTime = new CalendarDateTime($r['end_datetime']);
                    $new_event = null;
                    if ($r['event_deviation_uid'] > 0) {
                        $result2 = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
                            '*',
                            'tx_cal_event_deviation',
                            'uid=' . $r['event_deviation_uid'] . $this->cObj->enableFields('tx_cal_event_deviation')
                        );
                        if ($result2) {
                            while ($r2 = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result2)) {
                                $new_event = new EventDeviationModel(
                                    $event,
                                    $r2,
                                    $nextOccuranceTime,
                                    $nextOccuranceEndTime
                                );
                            }
                        }
                    } else {
                        $new_event = new EventModel(
                            $event,
                            'false',
                            'tx_cal_phpicalendar',
                            $nextOccuranceTime,
                            $nextOccuranceEndTime
                        );
                    }
                    if (!$ex_event_dates[$new_event->getStart()->format('Ymd')]) {
                        if ($new_event->isAllday()) {
                            $master_array[$nextOccuranceTime->format('Ymd')]['-1'][$event->getUid()] = $new_event;
                        } else {
                            $master_array[$nextOccuranceTime->format('Ymd')][$nextOccuranceTime->format('Hi')][$event->getUid()] = $new_event;

                        }
                        //?Insert$new_event
                        $connection = $this->connectionPool->getConnectionForTable('tx_cal_exception_event');
                        $queryBuilder = $connection->createQueryBuilder();
                        if (TYPO3_MODE == 'BE') {

                            $queryBuilder
                                ->getRestrictions()
                                ->removeAll()
                                ->add(GeneralUtility::makeInstance(DeletedRestriction::class));
                        } else {
                            $queryBuilder->setRestrictions(GeneralUtility::makeInstance(FrontendRestrictionContainer::class));
                        }
//                        $new_event->isException = true;
//                        $result = $this->connectionPool->getConnectionForTable($table)->createQueryBuilder()
//                            ->insert($table)->values($new_event)->execute();
//                      //  $this->businessadressrepository->add($adress);
//                        //$this->persistenceManager->persistAll();
//                        //uid, tablename, start_datetime, end_datetime, event_uid, event_deviation_uid
//                        $index = 'tx_cal_index';
//                        $insertFields['event_uid'] = $event->getUid();
//                        $insertFields['start_datetime'] =$nextOccuranceTime->format('Ymd').$nextOccuranceTime->format('Hi');
//                        $insertFields['end_datetime'] = $event->getUid();
//                        $insertFields['event_uid'] = $event->getUid();
//                        $insertFields['event_deviation_uid'] = $event->getUid();
//                        $insertFields['tablename'] = $table;
//
//
//                        $result = $this->connectionPool->getConnectionForTable($index)->createQueryBuilder()
//                            ->insert($index)->values($insertFields)->execute();
                        $added++;
                    }
                }
            }
          //  $GLOBALS['TYPO3_DB']->sql_free_result($result);
        }
        return $master_array;
    }

    /**
     * @param $master_array
     * @param EventModel $event
     * @param $addedCount
     */
    public function getRecurringDate(&$master_array, &$event, &$addedCount)
    {
        $connection = $this->connectionPool->getConnectionForTable('tx_cal_index');

        $queryBuilder = $connection->createQueryBuilder();
        if (TYPO3_MODE == 'BE') {
            $queryBuilder
                ->getRestrictions()
                ->removeAll()
                ->add(GeneralUtility::makeInstance(DeletedRestriction::class));
        } else {
            $queryBuilder->setRestrictions(GeneralUtility::makeInstance(FrontendRestrictionContainer::class));
        }


        switch ($event->getRdateType()) {
            case 'date':
                foreach ($event->getRdateValues() as $rdateValue) {
                    preg_match('/(^[0-9]{4})([0-9]{2})([0-9]{2})/', $rdateValue, $dateArray);
                    $new_event = $event->cloneEvent();
                    $start = &$new_event->getStart();
                    $end = &$new_event->getEnd();
                    $diff = $end->format('U') - $start->format('U');
                    $start->setDay($dateArray[3]);
                    $start->setMonth($dateArray[2]);
                    $start->setYear($dateArray[1]);
                    $new_event->setStart($start);
                    $new_event->setEnd($start);
                    $end = $new_event->getEnd();
                    $end->addSeconds($diff);
                    $new_event->setEnd($end);
                    #todo checkindex
                    if ($end->after($this->starttime) && $start->before($this->endtime)) {
                        $table = 'tx_cal_index';
                        $eventData = [
                            'start_datetime' => $start->format('Ymd') . $start->format('His'),
                            'end_datetime' => $end->format('Ymd') . $end->format('His'),
                            'event_uid' => $event->getUid(),
                            'tablename' => $event->isException ? 'tx_cal_exception_event' : 'tx_cal_event'
                        ];
                        $this->updateEventDataWithDeviations($event, $eventData);
                        $result = $queryBuilder->insert($table)->values($eventData)->execute();
                        //$result = $GLOBALS['TYPO3_DB']->exec_INSERTquery($table, $eventData);
                        if (false === $result) {
                            throw new RuntimeException(
                                'Could not write event index record to database: ' . $queryBuilder->getSQL(),
                                1431458131
                            );
                        }
                        $addedCount++;
                    }
                }
                break;
            case 'period':
                foreach ($event->getRdateValues() as $rdateValue) {
                    preg_match(
                        '/([0-9]{4})(-?([0-9]{2})((-?[0-9]{2})(T([0-9]{2}):?([0-9]{2})(:?([0-9]{2})(\.([0-9]+))?)?(Z|(([-+])([0-9]{2}):([0-9]{2})))?)?)?)?/',
                        $rdateValue,
                        $dateArray
                    );
                    preg_match(
                        '/\/P((\d+)Y)?((\d+)M)?((\d+)W)?((\d+)D)?T((\d+)H)?((\d+)M)?((\d+)S)?/',
                        $rdateValue,
                        $durationArray
                    );
                    $new_event = $event->cloneEvent();
                    $start = &$new_event->getStart();
                    $end = &$new_event->getStart();
                    $diff = 0;
                    $start->setDay($dateArray[5]);
                    $start->setMonth($dateArray[3]);
                    $start->setYear($dateArray[1]);
                    $start->setHour($dateArray[7]);
                    $start->setMinute($dateArray[8]);
                    $start->setSecond($dateArray[10]);
                    $new_event->setStart($start);
                    $new_event->setEnd($start);
                    $end = $new_event->getEnd();
                    if ($durationArray[2]) {
                        // Year
                        $end->setYear($end->getYear() + intval($durationArray[2]));
                    }
                    if ($durationArray[4]) {
                        // Month
                        $end->setMonth($end->getMonth() + intval($durationArray[4]));
                    }
                    if ($durationArray[6]) {
                        // Week
                        $diff += intval($durationArray[6]) * 60 * 60 * 24 * 7;
                    }
                    if ($durationArray[8]) {
                        // Day
                        $diff += intval($durationArray[8]) * 60 * 60 * 24;
                    }
                    if ($durationArray[10]) {
                        // Hour
                        $diff += intval($durationArray[10]) * 60 * 60;
                    }
                    if ($durationArray[12]) {
                        // Minute
                        $diff += intval($durationArray[12]) * 60;
                    }
                    if ($durationArray[14]) {
                        // Second
                        $diff += intval($durationArray[14]);
                    }

                    $end->addSeconds($diff);
                    $new_event->setEnd($end);
#todo checkindex
                    if ($end->after($this->starttime) && $start->before($this->endtime)) {
                        $table = 'tx_cal_index';
                        $eventData = [
                            'start_datetime' => $start->format('Ymd') . $start->format('His'),
                            'end_datetime' => $end->format('Ymd') . $end->format('His'),
                            'event_uid' => $event->getUid(),
                            'tablename' => $event->isException ? 'tx_cal_exception_event' : 'tx_cal_event'
                        ];
                        $this->updateEventDataWithDeviations($event, $eventData);

                        $connection = $this->connectionPool->getConnectionForTable($table);

                        $result = $queryBuilder->insert($table)->values($eventData)->execute();

                       // $result = $GLOBALS['TYPO3_DB']->exec_INSERTquery($table, $eventData);
                        if (false === $result) {
                            throw new RuntimeException(
                                'Could not write event index record to database: ' . $queryBuilder->getSQL(),
                                1431458132
                            );
                        }
                        $addedCount++;
                    }
                }
                break;
            default:
                foreach ($event->getRdateValues() as $rdateValue) {
                    preg_match(
                        '/([0-9]{4})(-?([0-9]{2})((-?[0-9]{2})(T([0-9]{2}):?([0-9]{2})(:?([0-9]{2})(\.([0-9]+))?)?(Z|(([-+])([0-9]{2}):([0-9]{2})))?)?)?)?/',
                        $rdateValue,
                        $dateArray
                    );
                    $new_event = $event->cloneEvent();
                    $start = &$new_event->getStart();
                    $end = &$new_event->getEnd();
                    $diff = $end->format('U') - $start->format('U');
                    $start->setDay($dateArray[5]);
                    $start->setMonth($dateArray[3]);
                    $start->setYear($dateArray[1]);
                    $start->setHour($dateArray[7]);
                    $start->setMinute($dateArray[8]);
                    $start->setSecond($dateArray[10]);
                    $new_event->setStart($start);
                    $new_event->setEnd($start);
                    $end = $new_event->getEnd();
                    $end->addSeconds($diff);
                    $new_event->setEnd($end);
                    if ($end->after($this->starttime) && $start->before($this->endtime)) {
                        $table = 'tx_cal_index';
                        $eventData = [
                            'start_datetime' => $start->format('YmdHi'),
                            'end_datetime' => $end->format('YmdHi'),
                            'event_uid' => $event->getUid(),
                            'tablename' => $event->isException ? 'tx_cal_exception_event' : 'tx_cal_event'
                        ];
                        $this->updateEventDataWithDeviations($event, $eventData);
                        $connection = $this->connectionPool->getConnectionForTable($table);
                        $queryBuilder = $connection->createQueryBuilder();
                        if (TYPO3_MODE == 'BE') {
                            $queryBuilder
                                ->getRestrictions()
                                ->removeAll()
                                ->add(GeneralUtility::makeInstance(DeletedRestriction::class));
                        } else {
                            $queryBuilder->setRestrictions(GeneralUtility::makeInstance(FrontendRestrictionContainer::class));
                        }
                        $result = $queryBuilder->insert($table)->values($eventData)->execute();

//                        $result = $GLOBALS['TYPO3_DB']->exec_INSERTquery($table, $eventData);
                        if (false === $result) {
                            throw new RuntimeException(
                                'Could not write event index record to database: ' .  $queryBuilder->getSQL(),
                                1431458133
                            );
                        }
                        $addedCount++;
                    }
                }
                break;
        }
    }

    /**
     * This function merges an array of events with another array of events.
     * The structure is:[date][time][event]
     *
     * @param $events array
     *            the events should be added into
     * @param $events_tmp array
     *            is supposed to be merged
     */
    public function mergeEvents(&$events, &$events_tmp)
    {
        $dates = array_keys($events_tmp);
        foreach ($dates as $event_date) {
            $eventsThatDay = &$events_tmp[$event_date];
            if (array_key_exists($event_date, $events)) {
                $times = array_keys($eventsThatDay);
                foreach ($times as $event_time) {
                    $eventsThatTime = &$eventsThatDay[$event_time];
                    $eventIDs = array_keys($eventsThatTime);
                    foreach ($eventIDs as $key) {
                        $events[$event_date][$event_time][$key] = &$eventsThatTime[$key];
                    }
                }
            } else {
                $events[$event_date] = &$eventsThatDay;
            }
        }
    }

    /**
     * This function removes an array of events from another array of events.
     * The structure is:[date][time][event]
     *
     * @param $events_tmp array
     *            is supposed to be deleted
     * @param $ex_events
     */
    public function removeEvents(&$events_tmp, &$ex_events)
    {
        foreach ($events_tmp as $event_tmp_key => $event_tmp) {
            if (array_key_exists($event_tmp_key, $ex_events)) {
                array_splice($events_tmp[$event_tmp_key], 0);
            }
        }
    }

    /**
     * This function returns an array of weekdays (english)
     */
    public function getDaysOfWeekShort(): array
    {
        return [
            'Sun',
            'Mon',
            'Tue',
            'Wed',
            'Thu',
            'Fri',
            'Sat'
        ];
    }

    /**
     * @param $pid
     */
    public function saveExceptionEvent($pid)
    {
        $crdate = time();
        $insertFields = [
            'pid' => $pid,
            'tstamp' => $crdate,
            'crdate' => $crdate
        ];
        // TODO: Check if all values are correct

        if ($this->controller->piVars['exception_start_day'] !== '') {
            $insertFields['start_date'] = strip_tags($this->controller->piVars['exception_start_day']);
        } else {
            return;
        }

        if ($this->controller->piVars['exception_end_day'] !== '') {
            $insertFields['end_date'] = strip_tags($this->controller->piVars['exception_end_day']);
        }

        if ($this->controller->piVars['exception_title'] !== '') {
            $insertFields['title'] = strip_tags($this->controller->piVars['exception_title']);
        }

        // Creating DB records
        $insertFields['cruser_id'] = $this->rightsObj->getUserId();
        $insertFields['fe_cruser_id'] = $this->rightsObj->getUserId();
        if ($insertFields['title'] === '') {
            $insertFields['title'] = strip_tags($this->controller->piVars['exception_start_day']) . ' exception';
        }
        $table = 'tx_cal_exception_event';
        $connection = $this->connectionPool->getConnectionForTable($table);
        $queryBuilder = $connection->createQueryBuilder();
        if (TYPO3_MODE == 'BE') {
            $queryBuilder
                ->getRestrictions()
                ->removeAll()
                ->add(GeneralUtility::makeInstance(DeletedRestriction::class));
        } else {
            $queryBuilder->setRestrictions(GeneralUtility::makeInstance(FrontendRestrictionContainer::class));
        }
        $result = $queryBuilder->insert($table)->values($insertFields)->execute();

       // $result = $GLOBALS['TYPO3_DB']->exec_INSERTquery($table, $insertFields);
        if (false === $result) {
            throw new RuntimeException(
                'Could not write exception event record to database: ' .  $queryBuilder->getSQL(),
                1431458134
            );
        }
        $uid = $connection->lastInsertId($table);;// $GLOBALS['TYPO3_DB']->sql_insert_id();

        self::insertIdsIntoTableWithMMRelation('tx_cal_exception_event_mm', [
            $uid
        ], intval($this->controller->piVars['event_uid']), 'tx_cal_exception_event');
        $this->unsetPiVars();
        Functions::clearCache();
    }

    public function unsetPiVars()
    {
        unset($this->controller->piVars['hidden'], $this->controller->piVars['_TRANSFORM_description'], $this->controller->piVars['uid'], $this->controller->piVars['calendar_id'], $this->controller->piVars['calendar'], $this->controller->piVars['switch_calendar'], $this->controller->piVars['type'], $this->controller->piVars['allday'], $this->controller->piVars['startdate'], $this->controller->piVars['starttime'], $this->controller->piVars['startminutes'], $this->controller->piVars['enddate'], $this->controller->piVars['endtime'], $this->controller->piVars['endminutes'], $this->controller->piVars['gettime'], $this->controller->piVars['title'], $this->controller->piVars['organizer'], $this->controller->piVars['organizer_id'], $this->controller->piVars['location'], $this->controller->piVars['location_id'], $this->controller->piVars['description'], $this->controller->piVars['frequency_id'], $this->controller->piVars['by_day'], $this->controller->piVars['by_monthday'], $this->controller->piVars['by_month'], $this->controller->piVars['until'], $this->controller->piVars['count'], $this->controller->piVars['interval'], $this->controller->piVars['category'], $this->controller->piVars['category_ids'], $this->controller->piVars['category_display_ids'], $this->controller->piVars['user_ids'], $this->controller->piVars['group_ids'], $this->controller->piVars['single_exception_ids'], $this->controller->piVars['group_exception_ids'], $this->controller->piVars['gettime'], $this->controller->piVars['notify'], $this->controller->piVars['notify_ids'], $this->controller->piVars['teaser'], $this->controller->piVars['image'], $this->controller->piVars['image_caption'], $this->controller->piVars['image_title'], $this->controller->piVars['image_alt'], $this->controller->piVars['image_old'], $this->controller->piVars['remove_image'], $this->controller->piVars['cal_location'], $this->controller->piVars['cal_organizer'], $this->controller->piVars['attachment'], $this->controller->piVars['attachment_caption']);
    }

    /**
     * @param EventModel $event
     */
    public function checkRecurringSettings(&$event)
    {
        $this->checkFrequency($event);
        if ($event->getFreq() === 'none') {
            return;
        }
        $this->checkInterval($event);
        $this->checkByMonth($event);
        $this->checkByWeekno($event);
        $this->checkByYearday($event);
        $this->checkByMonthday($event);
        $this->checkByDay($event);
        $this->checkByHour($event);
        $this->checkByMinute($event);
        $this->checkBySecond($event);
        $this->checkBySetpos($event);
        $this->checkCount($event);
        $this->checkUntil($event);
        $this->checkWkst($event);
    }

    /**
     * @param EventModel $event
     */
    public function filterFalseCombinations(&$event)
    {
        switch ($event->getFreq()) {
            case '':
            case 'none':
                break;
            case 'day':
                $event->setByMonth('');
                $event->setByWeekNo('');
                $event->setByYearDay('');
                $event->setByMonthDay('');
                $event->setByDay('');
                break;
            case 'week':
                $event->setByMonth('');
                $event->setByWeekNo('');
                $event->setByYearDay('');
                $event->setByMonthDay('');
                break;
            case 'month':
                $event->setByMonth('');
                $event->setByWeekNo('');
                $event->setByYearDay('');
                break;
            case 'year':
                if (count($event->getByMonth()) > 0) {
                    $event->setByWeekNo('');
                    $event->setByYearDay('');
                } elseif (count($event->getByWeekNo()) > 0) {
                    $event->setByYearDay('');
                } elseif (count($event->getByYearDay()) > 0) {
                    $event->setByMonthDay('');
                } elseif (count($event->getByMonthDay()) > 0) {
                    $event->setByDay('');
                }
                break;
        }
    }

    /**
     * @param EventModel $event
     */
    public function checkFrequency(&$event)
    {
        $allowedValues = [
            'second',
            'minute',
            'hour',
            'day',
            'week',
            'month',
            'year'
        ];
        if (!in_array($event->getFreq(), $allowedValues, true)) {
            $event->setFreq('none');
        }
    }

    /**
     * @param EventModel $event
     */
    public function checkInterval(&$event)
    {
        if (!$event->getInterval() || $event->getInterval() < 1) {
            $event->setInterval(1);
        }
    }

    /**
     * @param EventModel $event
     */
    public function checkCount(&$event)
    {
        if (!$event->getCount() || $event->getCount() < 1) {
            $event->setCount(9999999);
        }
    }

    /**
     * @param EventModel $event
     */
    public function checkUntil(&$event)
    {
        if (!$event->getUntil()) {
            $event->setUntil($this->endtime);
        }
    }

    /**
     * @param EventModel $event
     */
    public function checkBySecond(&$event)
    {
        if (intval($event->getBySecond()) < 0 || intval($event->getBySecond()) > 59) {
            $eventStart = $event->getStart();
            $event->setBySecond($eventStart->getSecond());
        }
    }

    /**
     * @param EventModel $event
     */
    public function checkByMinute(&$event)
    {
        if (intval($event->getByMinute()) < 0 || intval($event->getByMinute()) > 59) {
            $eventStart = $event->getStart();
            $event->setByMinute($eventStart->getMinute());
        }
    }

    /**
     * @param EventModel $event
     */
    public function checkByHour(&$event)
    {
        if (intval($event->getByHour()) < 0 || intval($event->getByHour()) > 23) {
            $eventStart = $event->getStart();
            $event->setByHour($eventStart->getHour());
        }
    }

    /**
     * @param EventModel $event
     */
    public function checkByDay(&$event)
    {
        $allowedValues = [];
        $allowedWeekdayValues = [
            'SU',
            'MO',
            'TU',
            'WE',
            'TH',
            'FR',
            'SA'
        ];
        // example: -2TU -> 2nd last Tuesday
        // +1TU -> 1st Tuesday
        // WE,FR -> Wednesday and Friday
        $byDayArray = $event->getByDay();
        if ($event->getFreq() === 'day') {
            $event->setByDay('all');
            return;
        }
        foreach ($byDayArray as $i => $iValue) {
            $byDayArray[$i] = strtoupper($iValue);
            if (preg_match('/([-\+]{0,1})?([0-9]{1})?([A-Z]{2})/', $byDayArray[$i], $byDaySplit)) {
                if (!in_array($byDaySplit[3], $allowedWeekdayValues, true)) {
                    continue;
                }
                if (!($byDaySplit[2] > 0 && ($event->getFreq() === 'month' || $event->getFreq() === 'year'))) {
                    // n-th values are not allowed for monthly and yearly
                    unset($byDaySplit[1], $byDaySplit[2]);
                }
                unset($byDaySplit[0]);
                $allowedValues[] = implode('', $byDaySplit);
            }
            // the current byday setting is not valid
        }
        if (count($allowedValues) === 0) {
            if ($event->getFreq() === 'week') {
                $eventStart = $event->getStart();
                $allowedValues = [
                    $allowedWeekdayValues[$eventStart->getDayOfWeek()]
                ];
            } else {
                $allowedValues = [
                    'all'
                ];
            }
        }
        $event->setByDay(implode(',', $allowedValues));
    }

    /**
     * @param EventModel $event
     */
    public function checkByMonth(&$event)
    {
        $byMonth = $event->getByMonth();
        if (!is_array($byMonth) || count($byMonth) === 0) {
            if ($event->getFreq() === 'year') {
                $eventStart = $event->getStart();
                $event->setByMonth($eventStart->getMonth());
            } else {
                $event->setByMonth('all');
            }
            return;
        }
        $allowedValues = [];
        foreach ($byMonth as $month) {
            if ($month > 0 && $month < 13) {
                $allowedValues[] = $month;
            }
        }
        sort(array_unique($allowedValues));
        $event->setByMonth(implode(',', $allowedValues));
    }

    /**
     * @param EventModel $event
     */
    public function checkByMonthday(&$event)
    {
        /* If there's not a monthday set, pick a default value */
        if (count($event->getByMonthDay()) === 0) {

            /**
             * If there's no day of the week either, assume that we only want
             * to recur on the event start day.
             * If there is a day of the
             * week, assume that we want to recur anytime that day of the week
             * occurs.
             */
            if ($event->getFreq() !== 'week' && empty($event->getByDay())) {
                $eventStart = $event->getStart();
                $event->setByMonthDay($eventStart->getDay());
            } else {
                $event->setByMonthDay('all');
            }
        } else {
            $event->setByMonthDay(implode(',', array_filter(
                $event->getByMonthDay(),
                function ($value) {
                    $value = intval($value);
                    return !($value < -12 || $value > 12 || $value === 0);
                }
            )));
        }
    }

    /**
     * @param EventModel $event
     */
    public function checkByYearday(&$event)
    {
        if (!empty($event->getByYearDay())) {
            $event->setByYearDay(implode(',', array_filter(
                $event->getByYearDay(),
                function ($value) {
                    $value = intval($value);
                    return !($value < -31 || $value > 31 || $value === 0);
                }
            )));
        }
    }

    /**
     * @param EventModel $event
     */
    public function checkByWeekno(&$event)
    {
        if ($event->getFreq() === 'yearly') {
            $event->setByWeekNo(implode(',', array_filter(
                $event->getByWeekNo(),
                function ($value) {
                    $value = intval($value);
                    return !($value < -53 || $value > 53 || $value === 0);
                }
            )));
        } else {
            $event->setByWeekNo('');
        }
    }

    /**
     * @param EventModel $event
     */
    public function checkWkst(&$event)
    {
        $allowedWeekdayValues = [
            'MO',
            'TU',
            'WE',
            'TH',
            'FR',
            'SA',
            'SU'
        ];
        $wkst = strtoupper($event->getWkst());
        if (!in_array($wkst, $allowedWeekdayValues, true)) {
            $wkst = '';
        }
        $event->setWkst($wkst);
    }

    /**
     * @param EventModel $event
     */
    public function checkBySetpos(&$event)
    {
        $event->setBySetPos(intval($event->getBySetPos()));
    }

    /**
     * @param $master_array
     * @param EventModel $event
     * @param CalendarDateTime $startRange
     * @param CalendarDateTime $endRange
     * @param $weekdays
     * @param $maxCount
     * @param $currentCount
     * @param $totalCount
     * @param $addedCount
     * @param $maxRecurringEvents
     */
    public function findDailyWithin(
        &$master_array,
        $event,
        $startRange,
        $endRange,
        $weekdays,
        $maxCount,
        &$currentCount,
        &$totalCount,
        &$addedCount,
        &$maxRecurringEvents
    ) {
        $nextOccuranceTime = $startRange;
        $connection = $this->connectionPool->getConnectionForTable('tx_cal_index');

        $queryBuilder = $connection->createQueryBuilder();
        if (TYPO3_MODE == 'BE') {
            $queryBuilder
                ->getRestrictions()
                ->removeAll()
                ->add(GeneralUtility::makeInstance(DeletedRestriction::class));
        } else {
            $queryBuilder->setRestrictions(GeneralUtility::makeInstance(FrontendRestrictionContainer::class));
        }

        while ($currentCount < $maxCount && ($nextOccuranceTime->before($endRange) || $nextOccuranceTime->equals($endRange)) && $addedCount < $maxRecurringEvents) {
            if ($nextOccuranceTime->after($event->getStart()) && !$nextOccuranceTime->equals($event->getStart())) {
                if (($totalCount % $event->getInterval()) === 0) {
                    $nextOccuranceEndTime = new CalendarDateTime();
                    $nextOccuranceEndTime->copy($nextOccuranceTime);
                    $nextOccuranceEndTime->addSeconds($event->getLengthInSeconds());
                    if ($this->starttime->before($nextOccuranceEndTime) || $this->starttime->equals($nextOccuranceTime)) {
                        $table = 'tx_cal_index';
                        if ($event->isException) {
                            $eventData = [
                                'start_datetime' => $nextOccuranceTime->format('Ymd') . $nextOccuranceTime->format('His'),
                                'end_datetime' => $nextOccuranceEndTime->format('Ymd') . $nextOccuranceEndTime->format('His'),
                                'event_uid' => $event->getUid(),
                                'tablename' => $event->getType() === 'tx_cal_phpicalendar' ? ('tx_cal_exception_event') : $event->getType()
                            ];
                        } else {
                            $eventData = [
                                'start_datetime' => $nextOccuranceTime->format('Ymd') . $event->getStart()->format('Hi'),
                                'end_datetime' => $nextOccuranceEndTime->format('Ymd') . $event->getEnd()->format('Hi'),
                                'event_uid' => $event->getUid(),
                                'tablename' => $event->getType() === 'tx_cal_phpicalendar' ? ('tx_cal_event') : $event->getType()
                            ];
                        }
                        $this->updateEventDataWithDeviations($event, $eventData);
                        //$result = $GLOBALS['TYPO3_DB']->exec_INSERTquery($table, $eventData);
                        $result = $queryBuilder->insert($table)->values($eventData)->execute();
                        if (false === $result) {
                            throw new RuntimeException(
                                'Could not write event index record to database: ' . $queryBuilder->getSQL(),
                                1431458135
                            );
                        }
                        $addedCount++;
                    }
                    $currentCount++;
                }
                $totalCount++;
            }
            $nextOccuranceTime->addSeconds(86400);
        }
    }

    /**
     * @param EventModel $event
     * @param $eventData
     */
    private function updateEventDataWithDeviations($event, &$eventData)
    {
        $deviationDates = $event->getDeviationDates();
        if (array_key_exists($eventData['start_datetime'], $deviationDates)) {
            $startDate = null;
            if ($deviationDates[$eventData['start_datetime']]['start_date']) {
                $startDate = new CalendarDateTime($deviationDates[$eventData['start_datetime']]['start_date']);
            } else {
                $startDate = new CalendarDateTime();
                $startDate->copy($nextOccuranceTime);
            }
            $endDate = null;
            if ($deviationDates[$eventData['start_datetime']]['end_date']) {
                $endDate = new CalendarDateTime($deviationDates[$eventData['start_datetime']]['end_date']);
            } else {
                $endDate = new CalendarDateTime();
                $endDate->copy($nextOccuranceEndTime);
            }

            if (!$deviationDates[$eventData['start_datetime']]['allday']) {
                $startDate->addSeconds($deviationDates[$eventData['start_datetime']]['start_time']);
                $endDate->addSeconds($deviationDates[$eventData['start_datetime']]['end_time']);
            }

            $eventData['event_deviation_uid'] = $deviationDates[$eventData['start_datetime']]['uid'];
            $eventData['start_datetime'] = $startDate->format('Ymd') . $startDate->format('His');
            $eventData['end_datetime'] = $endDate->format('Ymd') . $endDate->format('His');
        }
        else{
            return false;
        }
    }

    /**
     * @param EventModel $event
     * @param int $month
     * @param $year
     * @return array
     */
    public function getMonthDaysAccordingly(&$event, $month, $year): array
    {
        $byDayArray = $event->getByDay();
        $byMonthDays = $event->getByMonthDay();
        $resultDays = [];
        if (count($byDayArray) === 0) {
            $resultDays = [
                1,
                2,
                3,
                4,
                5,
                6,
                7,
                8,
                9,
                10,
                11,
                12,
                13,
                14,
                15,
                16,
                17,
                18,
                19,
                20,
                21,
                22,
                23,
                24,
                25,
                26,
                27,
                28,
                29,
                30,
                31
            ];
            return $resultDays;
        }
        foreach ($byDayArray as $i => $iValue) {
            if (preg_match('/([-\+]{0,1})?([0-9]{1})?([A-Z]{2})/', $byDayArray[$i], $byDaySplit)) {
                $dayOfWeekday = Calendar::two2threeCharDays($byDaySplit[3], false);
                $monthStartTime = new  CalendarDateTime($year . '-' . sprintf(
                    '%02d',
                    $month
                    ) . '-01 00:00:00');
                $monthStartTime->setTZbyID('UTC');
                $monthEndTime = Calendar::calculateEndMonthTime($monthStartTime);
                if ($byDaySplit[2] > 0) {
                    if ($byDaySplit[1] === '-') {
                        $monthTime = new  CalendarDateTime(Calc::prevDayOfWeek(
                            $dayOfWeekday,
                            $monthEndTime->getDay(),
                            $monthEndTime->getMonth(),
                            $monthEndTime->getYear(),
                            '%Y%m%d',
                            true
                        ));
                        $monthTime->setTZbyID('UTC');
                        $monthTime->subtractSeconds(($byDaySplit[2] - 1) * 604800);
                    } else {
                        $monthTime = new  CalendarDateTime(Calc::nextDayOfWeek(
                            $dayOfWeekday,
                            $monthStartTime->getDay(),
                            $monthStartTime->getMonth(),
                            $monthStartTime->getYear(),
                            '%Y%m%d',
                            true
                        ));
                        $monthTime->setTZbyID('UTC');
                        $monthTime->addSeconds(($byDaySplit[2] - 1) * 604800);
                    }
                    if (($monthTime->getMonth() === $month) && in_array($monthTime->getDay(), $byMonthDays, true)) {
                        $resultDays[] = $monthTime->getDay();
                    }
                } else {
                    $monthTime = new  CalendarDateTime(Calc::prevDayOfWeek(
                        $dayOfWeekday,
                        $monthStartTime->getDay(),
                        $monthStartTime->getMonth(),
                        $monthStartTime->getYear(),
                        '%Y%m%d',
                        true
                    ));
                    $monthTime->setTZbyID('UTC');
                    if ($monthTime->before($monthStartTime)) {
                        $monthTime->addSeconds(604800);
                    }
                    while ($monthTime->before($monthEndTime)) {
                        $resultDays[] = $monthTime->getDay();
                        $monthTime->addSeconds(604800);
                    }
                }
            }
        }

        $resultDays = array_intersect($resultDays, $event->getByMonthDay());
        sort($resultDays);

        return $resultDays;
    }

    /**
     * @param $uid
     * @param $overlay
     * @deprecated since ext:cal v2, will be removed in ext:cal v3
     */
    public function createTranslation($uid, $overlay)
    {
        trigger_error('Deprecated since ext:cal v2, will be removed in ext:cal v3.', E_USER_DEPRECATED);

        $languageFlag = $GLOBALS['TSFE']->sys_language_content;
        // resetting the language to find the default translation!
        $GLOBALS['TSFE']->sys_language_content = 0;
        $event = $this->find($uid, $this->conf['pidList']);
        $GLOBALS['TSFE']->sys_language_content = $languageFlag;
        $table = 'tx_cal_event';
        $select = $table . '.*';
        $where = $table . '.uid = ' . $uid;
        $result = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select, $table, $where);
        if ($result) {
            $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result);
            unset($row['uid']);
            $crdate = time();
            $row['tstamp'] = $crdate;
            $row['crdate'] = $crdate;
            $row['l18n_parent'] = $uid;
            $row['sys_language_uid'] = $overlay;
            $this->_saveEvent($row, $event);
            $GLOBALS['TYPO3_DB']->sql_free_result($result);
        }
    }

    /**
     * @param CalendarDateTime $start_date
     * @param CalendarDateTime $end_date
     */
    public function setStartAndEndPoint(&$start_date, &$end_date)
    {
        $start_date->subtractSeconds($this->conf['view.'][$this->conf['view'] . '.']['startPointCorrection']);
        $end_date->addSeconds($this->conf['view.'][$this->conf['view'] . '.']['endPointCorrection']);

        $this->starttime = new CalendarDateTime();
        $this->endtime = new CalendarDateTime();

        $this->starttime->copy($start_date);
        $this->endtime->copy($end_date);

        if ($this->endtime->equals($this->starttime)) {
            $this->endtime->addSeconds(86400);
        }
    }

    /**
     * @param $pidList
     * @return array
     */
    public function findMeetingEventsWithEmptyStatus($pidList)
    {
        if ($this->rightsObj->isLoggedIn()) {
            $start_date = new CalendarDateTime();
            $start_date->setTZbyID('UTC');
            $end_date = new CalendarDateTime();
            $end_date->copy($start_date);
            $end_date->addSeconds($this->conf['view.'][$this->conf['view'] . '.']['event.']['meeting.']['lookingAhead']);
            $this->setStartAndEndPoint($start_date, $end_date);

            $formattedStarttime = $this->starttime->format('Ymd');
            $formattedEndtime = $this->endtime->format('Ymd');

            $calendarService = &$this->modelObj->getServiceObjByKey(
                'cal_calendar_model',
                'calendar',
                'tx_cal_calendar'
            );
            $categoryService = $this->objectManager->get(SysCategoryService::class);

            $calendarSearchString = $calendarService->getCalendarSearchString(
                $pidList,
                true,
                $this->conf['calendar'] ?: ''
            );

            // putting everything together
            $additionalWhere = $calendarSearchString . ' AND tx_cal_event.pid IN (' . $pidList . ') ' . $this->cObj->enableFields('tx_cal_event') . ' AND ((tx_cal_event.start_date>=' . $formattedStarttime . ' AND tx_cal_event.start_date<=' . $formattedEndtime . ') OR (tx_cal_event.end_date<=' . $formattedEndtime . ' AND tx_cal_event.end_date>=' . $formattedStarttime . ') OR (tx_cal_event.end_date>=' . $formattedEndtime . ' AND tx_cal_event.start_date<=' . $formattedStarttime . ') OR (tx_cal_event.start_date<=' . $formattedEndtime . ' AND (tx_cal_event.freq IN ("day","week","month","year") AND tx_cal_event.until>=' . $formattedStarttime . ')))';
            $additionalWhere .= ' AND tx_cal_attendee.status IN ("0","NEEDS-ACTION") AND tx_cal_attendee.attendance <> "CHAIR" AND tx_cal_event.type = 3 AND tx_cal_attendee.fe_user_id = ' . $this->rightsObj->getUserId();

            // creating the arrays the user is allowed to see
            $categories = [];

            $categoryService->getCategoryArray($pidList, $categories);
            $includeRecurring = true;
            if ($this->conf['view'] === 'ics' || $this->conf['view'] === 'single_ics') {
                $includeRecurring = false;
            }
            // creating events
            return $this->getEventsFromTable(
                $categories[0][0],
                $includeRecurring,
                $additionalWhere,
                $this->getServiceKey(),
                true,
                true,
                '3'
            );
        }
    }

    /**
     * @param $eventUid
     */
    public function updateAttendees($eventUid)
    {
        $select = 'tx_cal_event.*';
        $table = 'tx_cal_event';
        $where = 'uid=' . $eventUid;
        $eventRow = [];
        $result = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select, $table, $where);
        if ($result) {
            $eventRow = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result);
            $GLOBALS['TYPO3_DB']->sql_free_result($result);
        }
        $eventObject = $this->createEvent($eventRow, false);

        $modelObj = &Registry::Registry('basic', 'modelcontroller');
        $attendeeRecordsArray = $modelObj->findEventAttendees($eventObject->getUid());

        if (!empty($attendeeRecordsArray['tx_cal_attendee'])) {
            $attendeeRecords = $attendeeRecordsArray['tx_cal_attendee'];
            // update related event record in attendee calendar

            $updatedCalendar = [
                0
            ];
            // attendees have changed, we need to go through every one of them :(
            /** @var AttendeeModel $attendee */
            foreach ($attendeeRecords as $attendee) {
                // Check if attendee is a fe-user and has a private calendar defined
                $select = 'tx_cal_calendar.uid, tx_cal_calendar.pid, tx_cal_event.uid AS event_id';
                $table = 'fe_users, tx_cal_calendar, tx_cal_event';
                $where = 'fe_users.uid = ' . $attendee->getFeUserId() . ' AND tx_cal_calendar.uid NOT IN (' . $eventObject->getCalendarId() . ') AND fe_users.tx_cal_calendar=tx_cal_calendar.uid AND tx_cal_calendar.uid = tx_cal_event.calendar_id AND tx_cal_event.ref_event_id = ' . $eventObject->getUid() . ' AND fe_users.disable=0 AND fe_users.deleted=0 AND tx_cal_calendar.hidden=0 AND tx_cal_calendar.deleted=0';
                $groupBy = 'tx_cal_calendar.uid';
                $result = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select, $table, $where, $groupBy);
                if ($result) {
                    while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
                        // found private calendar of attendee
                        $eventService = &Functions::getEventService();
                        $eventData = $eventRow;
                        $uid = $eventData['uid'];
                        $this->cleanEventData($eventData);
                        $eventData['pid'] = $row['pid'];
                        $crdate = time();
                        $eventData['crdate'] = $crdate;
                        $eventData['tstamp'] = $crdate;
                        $eventData['calendar_id'] = $row['uid'];
                        $eventData['ref_event_id'] = $eventObject->getUid();
                        $eventData['attendee_ids'] = implode(',', array_keys($attendeeRecords));
                        $eventService->conf['rights.']['edit.']['event.']['fields.']['attendee.']['public'] = 1;
                        $eventService->_updateEvent($row['event_id'], $eventData, $eventObject);
                        $updatedCalendar[] = $row['uid'];
                    }
                    $GLOBALS['TYPO3_DB']->sql_free_result($result);
                }
            }
            $updatedCalendar[] = $eventObject->getCalendarId();
            foreach ($attendeeRecords as $attendee) {
                // Check if attendee is a fe-user and has a private calendar defined
                $select = 'tx_cal_calendar.uid, tx_cal_calendar.pid';
                $table = 'fe_users, tx_cal_calendar';
                $where = 'tx_cal_calendar.uid NOT IN (' . implode(
                    ',',
                    $updatedCalendar
                ) . ') AND fe_users.uid = ' . $attendee->getFeUserId() . ' AND fe_users.tx_cal_calendar=tx_cal_calendar.uid AND fe_users.disable=0 AND fe_users.deleted=0 AND tx_cal_calendar.hidden=0 AND tx_cal_calendar.deleted=0';
                $groupBy = 'tx_cal_calendar.uid';
                $result = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select, $table, $where, $groupBy);
                if ($result) {
                    while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
                        // found private calendar of attendee
                        $eventService = &Functions::getEventService();
                        $eventData = $eventRow;
                        $this->cleanEventData($eventData);
                        $eventData['pid'] = $row['pid'];
                        $crdate = time();
                        $eventData['crdate'] = $crdate;
                        $eventData['tstamp'] = $crdate;
                        $eventData['calendar_id'] = $row['uid'];
                        $eventData['ref_event_id'] = $eventObject->getUid();
                        $eventData['attendee_ids'] = implode(',', array_keys($attendeeRecords));
                        $eventService->conf['rights.']['create.']['event.']['fields.']['attendee.']['public'] = 1;
                        $eventService->_saveEvent($eventData, $eventObject);
                    }
                    $GLOBALS['TYPO3_DB']->sql_free_result($result);
                }
            }
        } else {
            // Lets delete events with a ref_event_id to this event, b/c there are no attendees anymore

            // But first we have to find the events
            $select = 'tx_cal_event.uid';
            $table = 'tx_cal_event';
            $where = 'ref_event_id=' . $eventObject->getUid();
            $rememberUids = [
                0
            ];
            $result = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select, $table, $where);
            if ($result) {
                while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
                    $rememberUids[] = $row['uid'];
                }
                $GLOBALS['TYPO3_DB']->sql_free_result($result);
            }
            // Now lets delete the mm relations to the attendees
            $where = 'uid IN (' . implode(',', $rememberUids) . ')';
            $GLOBALS['TYPO3_DB']->exec_DELETEquery('tx_cal_attendee', $where);

            // Now delete the events
            $where = 'ref_event_id=' . $eventObject->getUid();
            $GLOBALS['TYPO3_DB']->exec_DELETEquery('tx_cal_event', $where);
        }
    }

    /**
     * @param $eventData
     */
    public function cleanEventData(&$eventData)
    {
        unset($eventData['uid'], $eventData['deleted'], $eventData['hidden'], $eventData['categories'], $eventData['category_id'], $eventData['category_string'], $eventData['category_headerstyle'], $eventData['category_bodystyle'], $eventData['exception_single_ids'], $eventData['exceptionGroupIds'], $eventData['event_owner']);
    }

    /**
     * @param string $where
     * @return array
     */
    public function findAllWithAdditionalWhere($where = ''): array
    {
        $categoryService = $this->objectManager->get(SysCategoryService::class);

        // putting everything together
        //
        // Franz: added simple check/include for rdate events at the end of this where clause.
        // But we need to find a way to only include rdate events within the searched timerange
        // - otherwise we'll flood the results after some time. I think we need a mm-table for that!
        $additionalWhere = $where . ' AND tx_cal_event.pid IN (' . $this->conf['pidList'] . ') ' . $this->cObj->enableFields('tx_cal_event');
        $additionalWhere .= '';
        // creating the arrays the user is allowed to see
        $categories = [];

        $categoryService->getCategoryArray($this->conf['pidList'], $categories);
        // creating events
        return $this->getEventsFromTable(
            $categories[0][0],
            false,
            $additionalWhere,
            $this->getServiceKey(),
            true,
            false,
            ''
        );
    }
}
