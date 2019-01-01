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
use TYPO3\CMS\Cal\Model\Pear\Date\Calc;
use TYPO3\CMS\Cal\Utility\Registry;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Base model for the calendar.
 * Provides basic model functionality that other
 * models can use or override by extending the class.
 */
class Model extends BaseModel
{
    public $row;
    public $isClone = false;
    public $tstamp;
    public $sequence = 1;
    public $title;
    public $organizer;
    public $location;
    public $content;
    public $start;
    public $end;
    public $allday = 0;
    public $timezone;
    public $calnumber = 1;
    public $calname;
    public $calendarUid;
    public $url;
    public $alarmdescription;
    public $summary;
    public $description;
    public $overlap = 1;
    public $_class;
    public $until;
    public $freq = '';
    public $reccuring_end;
    public $cnt;
    public $bysecond = [];
    public $byminute = [];
    public $byhour = [];
    public $byday = [];
    public $byweekno = [];
    public $bymonth = [];
    public $byyearday = [];
    public $bymonthday = [];
    public $byweekday = [];
    public $bysetpos = [];
    public $wkst = '';
    public $rdateType = '';
    public $rdate = '';
    public $rdateValues = [];
    public $displayend;
    public $spansday;
    public $categories = [];
    public $categoriesAsString;
    public $categoryUidsAsArray;
    public $location_id = 0;
    public $organizer_id = 0;
    public $locationLink;
    public $organizerLink;
    public $locationPage;
    public $organizerPage;
    public $organizerObject;
    public $locationObject;
    public $exception_single_ids = [];
    public $notifyUserIds = [];
    public $exceptionGroupIds = [];
    public $notifyGroupIds = [];
    public $creatorUserIds = [];
    public $creatorGroupIds = [];
    public $exceptionEvents = [];
    public $editable = false;
    public $headerstyle = 'default_catheader'; // '#557CA3';//'#0000ff';
    public $bodystyle = 'default_catbody'; // ''#6699CC';//'#ccffcc';
    public $crdate = 0;
    public $deviationDates;

    /* new */
    public $event_type;
    public $page;
    public $ext_url;
    /* new */
    public $externalPlugin = 0;
    public $sharedUsers = [];
    public $sharedGroups = [];
    public $eventOwner;
    public $attendees = [];
    public $status = 0;
    public $priority = 0;
    public $completetd = 0;
    const EVENT_TYPE_DEFAULT = 0;
    const EVENT_TYPE_SHORTCUT = 1;
    const EVENT_TYPE_EXTERNAL = 2;
    const EVENT_TYPE_MEETING = 3;
    const EVENT_TYPE_TODO = 4;

    /**
     * Constructor.
     *
     * @param $serviceKey String
     *            serviceKey for this model
     */
    public function __construct($serviceKey)
    {
        $this->setObjectType('event');
        parent::__construct($serviceKey);
    }

    /**
     * Returns the timestamp value.
     *
     * @return int timestamp.
     */
    public function getTstamp()
    {
        return $this->tstamp;
    }

    /**
     * Sets the timestamp value.
     *
     * @param $timestamp Integer
     */
    public function setTstamp($timestamp)
    {
        $this->tstamp = $timestamp;
    }

    /**
     * Returns the sequence value.
     *
     * @return array sequence.
     */
    public function getSequence()
    {
        return $this->sequence;
    }

    /**
     * Sets the sequence value.
     *
     * @param $sequence array
     */
    public function setSequence($sequence)
    {
        $this->sequence = $sequence;
    }

    /**
     * Sets the event organizer.
     *
     * @param $organizer String
     *            of the event.
     */
    public function setOrganizer($organizer)
    {
        $this->organizer = $organizer;
    }

    /**
     * Returns the event organizer.
     *
     * @return string organizer of the event.
     */
    public function getOrganizer()
    {
        return $this->organizer;
    }

    /**
     * Sets the event title.
     *
     * @param $title String
     *            of the event.
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * Returns the event title.
     *
     * @return string title of the event.
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Sets the event creation time.
     *
     * @param $timestamp Integer
     *            the event creation.
     */
    public function setCreationDate($timestamp)
    {
        $this->crdate = $timestamp;
    }

    /**
     * Returns timestamp of the event creation.
     *
     * @return int of the event creation.
     */
    public function getCreationDate()
    {
        return $this->crdate;
    }

    /**
     * Returns the rendered event.
     *
     * @return string event.
     */
    public function renderEvent()
    {
        $cObj = &Registry::Registry('basic', 'cobj');
        $d = nl2br($cObj->parseFunc($this->getDescription(), $this->conf['parseFunc.']));
        $eventStart = $this->getStart();
        $eventEnd = $this->getEnd();
        return '<h3>' . $this->getTitle() . '</h3><font color="#000000"><ul>' . '<li>Start: ' . $eventStart->format('%H:%M') . '</li>' . '<li>End: ' . $eventEnd->format('%H:%M') . '</li>' . '<li> Organizer: ' . $this->getOrganizer() . '</li>' . '<li>Location: ' . $this->getLocation() . '</li>' . '<li>Description: ' . $d . '</li></ul></font>';
    }

    /**
     * Returns the rendered event for allday.
     *
     * @return string event for allday -> the title.
     */
    public function renderEventForAllDay()
    {
        return $this->getTitle();
    }

    /**
     * Returns the rendered event for day.
     *
     * @return string event for day -> the title.
     */
    public function renderEventForDay()
    {
        return $this->title;
    }

    /**
     * Returns the rendered event for week.
     *
     * @return string event for week -> the title.
     */
    public function renderEventForWeek()
    {
        return $this->title;
    }

    /**
     * Returns the rendered event month day.
     *
     * @return string event for month -> the title.
     */
    public function renderEventForMonth()
    {
        return $this->title;
    }

    /**
     * Returns the rendered event month day for a mini month view.
     *
     * @return string event for a mini month -> the title.
     */
    public function renderEventForMiniMonth()
    {
        return $this->title;
    }

    /**
     * Returns the rendered event for year.
     *
     * @return string event for year -> the title.
     */
    public function renderEventForYear()
    {
        return $this->title;
    }

    /**
     * Returns the location value.
     *
     * @return string location.
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * Sets the event location value.
     *
     * @param $location String
     */
    public function setLocation($location)
    {
        $this->location = $location;
    }

    /**
     * Returns the location link value.
     *
     * @return string location link.
     */
    public function getLocationLinkUrl()
    {
        return $this->locationLink;
    }

    /**
     * Sets the event location link value.
     *
     * @param $locationLink String
     *            link.
     */
    public function setLocationLinkUrl($locationLink)
    {
        $this->locationLink = $locationLink;
    }

    /**
     * Sets the event location page value.
     *
     * @param $page Integer
     *            page.
     */
    public function setLocationPage($page)
    {
        $this->locationPage = $page;
    }

    /**
     * Returns the locationPage.
     *
     * @return int pid to link the location to
     */
    public function getLocationPage()
    {
        return $this->locationPage;
    }

    /**
     * Returns the startdate object.
     *
     * @return int startdate timeObject
     */
    public function getStart()
    {
        return $this->start;
    }

    /**
     * Returns the enddate object.
     *
     * @return int enddate timeObject
     */
    public function getEnd()
    {
        if (!$this->end) {
            $this->setEnd($this->getStart());
            $this->end->addSeconds($this->conf['view.']['event.']['event.']['defaultEventLength']);
        }
        return $this->end;
    }

    /**
     * Sets the event start.
     *
     * @param $start Object
     *            object
     */
    public function setStart($start)
    {
        $this->start = new CalDate();
        $this->start->copy($start);
        $this->row['start_date'] = $start->format('%Y%m%d');
        $this->row['start_time'] = $start->getHour() * 3600 + $start->getMinute() * 60;
    }

    /**
     * Sets the event end.
     *
     * @param $end Object
     *            object
     */
    public function setEnd($end)
    {
        $this->end = new CalDate();
        $this->end->copy($end);
        $this->row['end_date'] = $end->format('%Y%m%d');
        $this->row['end_time'] = $end->getHour() * 3600 + $end->getMinute() * 60;
    }

    /**
     * Returns the startdate as unix timestamp.
     *
     * @return int startdate as unix timestamp
     */
    public function getStartAsTimestamp()
    {
        $start = &$this->getStart();
        return $start->getDate(DATE_FORMAT_UNIXTIME);
    }

    /**
     * Returns the enddate as unix timestamp.
     *
     * @return int enddate as unix timestamp
     */
    public function getEndAsTimestamp()
    {
        $end = &$this->getEnd();
        return $end->getDate(DATE_FORMAT_UNIXTIME);
    }

    /**
     * Returns the ? value.
     *
     * @return ? ?
     * @TODO field is missing
     */
    public function getConfirmed()
    {
        return;
    }

    /**
     * Returns the cal recu value.
     *
     * @return array ? - empty array
     * @TODO What is that for?
     */
    public function getCalRecu()
    {
        return [];
    }

    /**
     * Returns the cal number value.
     *
     * @return string calnumber
     */
    public function getCalNumber()
    {
        return $this->calnumber;
    }

    /**
     * Sets the calnumber.
     *
     * @param $calnumber String
     */
    public function setCalNumber($calnumber)
    {
        $this->calnumber = $calnumber;
    }

    /**
     * Returns the calendar uid.
     *
     * @return int calendar uid
     */
    public function getCalendarUid()
    {
        return $this->calendarUid;
    }

    /**
     * Sets the calendar uid.
     *
     * @param $uid Integer
     *            uid.
     */
    public function setCalendarUid($uid)
    {
        $this->calendarUid = $uid;
    }

    /**
     * Returns the calendar object
     *
     * @return Model calendar object
     */
    public function getCalendarObject()
    {
        if (!$this->calendarObject) {
            $modelObj = &Registry::Registry('basic', 'modelcontroller');
            $this->calendarObject = $modelObj->findCalendar($this->getCalendarUid());
        }

        return $this->calendarObject;
    }

    /**
     * Returns the calendar name.
     *
     * @return string calendar name
     */
    public function getCalName()
    {
        return $this->calname;
    }

    /**
     * Sets the calendar name.
     *
     * @param $name String
     *            name.
     */
    public function setCalName($calname)
    {
        $this->calname = $calname;
    }

    /**
     * @return int
     */
    public function getOverlap()
    {
        return $this->overlap;
    }

    /**
     * @param $overlap
     */
    public function setOverlap($overlap)
    {
        $this->overlap = $overlap;
    }

    /**
     * @return mixed
     */
    public function getTimezone()
    {
        return $this->timezone;
    }

    /**
     * @param $timezone
     */
    public function setTimezone($timezone)
    {
        $this->timezone = $timezone;
    }

    /**
     * @return mixed
     */
    public function getDuration()
    {
        return $this->end->getTime() - $this->start->getTime();
    }

    /**
     * @param $duration
     * @return string
     */
    public function getFormatedDurationString($duration)
    {
        $durationString = '';
        if ($duration < 0) {
            $durationString .= '-';
        }
        $duration = abs($duration);
        $durationString .= 'P';
        $rest = $duration % (60 * 60 * 24);
        $days = ($duration - $rest) / (60 * 60 * 24);
        if ($days > 0) {
            $durationString .= $days . 'D';
        }
        $durationString .= 'T';
        $rest2 = $rest % (60 * 60);
        $hours = ($rest - $rest2) / (60 * 60);
        if ($hours > 0) {
            $durationString .= $hours . 'H';
        }

        $rest3 = $rest2 % (60);
        $minutes = ($rest2 - $rest3) / (60);
        if ($minutes > 0) {
            $durationString .= $minutes . 'M';
        }

        if ($rest3 > 0) {
            $durationString .= $rest3 . 'M';
        }
        return $durationString;
    }

    /**
     * @return int
     */
    public function isAllday()
    {
        return $this->allday;
    }

    /**
     * @return int
     */
    public function getAllday()
    {
        return $this->allday;
    }

    /**
     * @param $boolean
     */
    public function setAllday($boolean)
    {
        $this->allday = $boolean;
    }

    /**
     * @return array|void
     */
    public function getRecurringRule()
    {
        if ($this->freq != 'none' && $this->freq != '') {
            $return = [];
            $return['FREQ'] = $this->freq;
            $return['INTERVAL'] = $this->interval;
            return $return;
        }
        return;
    }

    /**
     * @param array $recur
     */
    public function setRecur($recur = [])
    {
        // TODO?
    }

    /**
     * @return mixed
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * @return mixed
     */
    public function getVAlarmDescription()
    {
        return $this->alarmdescription;
    }

    /**
     * @param $alarmdescription
     */
    public function setVAlarmDescription($alarmdescription)
    {
        $this->alarmdescription = $alarmdescription;
    }

    /**
     * @return bool
     */
    public function isClone()
    {
        return $this->isClone;
    }

    /**
     * @param $boolean
     */
    public function setIsClone($boolean)
    {
        $this->isClone = $boolean;
    }

    /**
     * @return array
     */
    public function getRecurrance()
    {
        $a = [];
        $a['tzid'] = $this->getTimezone();
        $a['date'] = $this->startdate;
        $a['time'] = $this->starthour;
        return $a;
    }

    /**
     * @return array
     */
    public function getByMonth()
    {
        return $this->bymonth;
    }

    /**
     * @param $bymonth
     */
    public function setByMonth($bymonth)
    {
        if ($bymonth != '') {
            $this->bymonth = explode(',', $bymonth);
        }
        if (strtoupper($bymonth) == 'ALL' || in_array('all', $this->bymonth)) {
            $this->bymonth = [
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
                12
            ];
        }
    }

    /**
     * @return array
     */
    public function getByDay()
    {
        return $this->byday;
    }

    /**
     * @param $byday
     */
    public function setByDay($byday)
    {
        $byday = strtoupper($byday);
        if ($byday != '') {
            $this->byday = explode(',', $byday);
        }

        if (strtoupper($byday) == 'ALL' || in_array('all', $this->byday)) {
            $this->byday = [
                'MO',
                'TU',
                'WE',
                'TH',
                'FR',
                'SA',
                'SU'
            ];
        }
    }

    /**
     * @return array
     */
    public function getByMonthDay()
    {
        return $this->bymonthday;
    }

    /**
     * @param $bymonthday
     */
    public function setByMonthday($bymonthday)
    {
        if ($bymonthday != '') {
            $this->bymonthday = GeneralUtility::trimExplode(',', $bymonthday, 1);
        }
        if (strtoupper($bymonthday) == 'ALL' || in_array('all', $this->bymonthday)) {
            $this->bymonthday = [
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
        }
    }

    /**
     * @return array
     */
    public function getByWeekDay()
    {
        return $this->byweekday;
    }

    /**
     * @param $byweekday
     */
    public function setByWeekDay($byweekday)
    {
        $this->byweekday = explode(',', $byweekday);
    }

    /**
     * @return array
     */
    public function getByWeekNo()
    {
        return $this->byweekno;
    }

    /**
     * @param $byweekno
     */
    public function setByWeekNo($byweekno)
    {
        $this->byweekno = explode(',', $byweekno);
    }

    /**
     * @return array
     */
    public function getByMinute()
    {
        return $this->byminute;
    }

    /**
     * @param $byminute
     */
    public function setByMinute($byminute)
    {
        $this->byminute = explode(',', $byminute);
    }

    /**
     * @return array
     */
    public function getByHour()
    {
        return $this->byhour;
    }

    /**
     * @param $byhour
     */
    public function setByHour($byhour)
    {
        $this->byhour = explode(',', $byhour);
    }

    /**
     * @return array
     */
    public function getBySecond()
    {
        return $this->bysecond;
    }

    /**
     * @param $bysecond
     */
    public function setBySecond($bysecond)
    {
        $this->bysecond = explode(',', $bysecond);
    }

    /**
     * @return array
     */
    public function getByYearDay()
    {
        return $this->byyearday;
    }

    /**
     * @param $byyearday
     */
    public function setByYearDay($byyearday)
    {
        $this->byyearday = explode(',', $byyearday);
    }

    /**
     * @return array
     */
    public function getBySetPos()
    {
        return $this->bysetpos;
    }

    /**
     * @param $bysetpos
     */
    public function setBySetPos($bysetpos)
    {
        $this->bysetpos = $bysetpos;
    }

    /**
     * @return string
     */
    public function getWkst()
    {
        return $this->wkst;
    }

    /**
     * @param $wkst
     */
    public function setWkst($wkst)
    {
        $this->wkst = $wkst;
    }

    /**
     * @return mixed
     */
    public function getInterval()
    {
        return $this->interval;
    }

    /**
     * @param $interval
     */
    public function setInterval($interval)
    {
        $this->interval = $interval;
    }

    /**
     * @return mixed
     */
    public function getSummary()
    {
        return $this->summary;
    }

    /**
     * @param $summary
     */
    public function setSummary($summary)
    {
        $this->summary = $summary;
    }

    /**
     * @return mixed
     */
    public function getClass()
    {
        return $this->_class;
    }

    /**
     * @param $class
     */
    public function setClass($class)
    {
        $this->_class = $class;
    }

    /**
     * @return mixed
     */
    public function getDisplayEnd()
    {
        return $this->displayend;
    }

    /**
     * @param $displayend
     */
    public function setDisplayEnd($displayend)
    {
        $this->displayend = $displayend;
    }

    /**
     * @return mixed
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param $t
     */
    public function setContent($t)
    {
        $this->content = $t;
    }

    /**
     * Returns
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Sets the discription attribute
     *
     * @param $description string
     *            the event
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * Returns the until date object
     */
    public function getUntil()
    {
        return $this->until;
    }

    /**
     * Sets the until object.
     *
     * @param $until object
     *            object
     */
    public function setUntil($until)
    {
        $this->until = $until;
    }

    /**
     * @return string
     */
    public function getFreq()
    {
        return $this->freq;
    }

    /**
     * Sets the recurring frequency
     */
    public function setFreq($freq)
    {
        $this->freq = $freq;
    }

    /**
     * Returns how often a recurring event is supposed to recurr as max
     */
    public function getCount()
    {
        return $this->cnt;
    }

    /**
     * Sets how often a recurring event is supposed to recurr as max
     *
     * @param $count int
     *            a recurring event is supposed to recurr as max
     */
    public function setCount($count)
    {
        $this->cnt = $count;
    }

    /**
     * Returns the rdate value.
     *
     * @return string rdate value
     */
    public function getRdate()
    {
        return $this->rdate;
    }

    /**
     * Sets the rdate value.
     *
     * @param $rdate String
     *            value
     */
    public function setRdate($rdate)
    {
        $this->rdate = strtoupper($rdate);
    }

    /**
     * Returns the rdate value as array split by comma.
     *
     * @return array
     */
    public function getRdateValues()
    {
        return GeneralUtility::trimExplode(',', $this->rdate, 1);
    }

    /**
     * Sets the rdate value.
     *
     * @param $rdate String
     *            value
     */
    public function setRdateValues($rdateArray)
    {
        $this->rdate = implode(',', $rdateArray);
    }

    /**
     * Returns the rdate type value.
     *
     * @return string rdate type value
     */
    public function getRdateType()
    {
        return $this->rdateType;
    }

    /**
     * Sets the rdate type value.
     *
     * @param $rdateType String
     *            type value
     */
    public function setRdateType($rdateType)
    {
        $this->rdateType = $rdateType;
    }

    /**
     * Returns TRUE if the events lasts the whole day
     */
    public function getSpansDay()
    {
        return $this->spansday;
    }

    /**
     * Sets the spansday attribute
     *
     * @param $spansday boolean
     *            the event lasts the whole day
     */
    public function setSpansDay($spansday)
    {
        $this->spansday = $spansday;
    }

    /**
     * Returns the categories (array)
     */
    public function getCategories()
    {
        return $this->categories;
    }

    /**
     * Sets the categories
     *
     * @param $categories array representation of the categories
     */
    public function setCategories($categories)
    {
        if (is_array($categories)) {
            $this->categories = $categories;
        }
    }

    /**
     * Adds an event to the exceptionEvents array
     *
     * @param $ex_events object
     *            this class (tx_cal_model)
     */
    public function addExceptionEvent($ex_event)
    {
        array_push($this->exceptionEvents, $ex_event);
    }

    /**
     * Sets the exceptionEvents
     *
     * @param $ex_events array
     *            exception events
     */
    public function setExceptionEvents($ex_events)
    {
        $this->exceptionEvents = $ex_events;
    }

    /**
     * Returns the exceptionEvents array
     */
    public function getExceptionEvents()
    {
        return $this->exceptionEvents;
    }

    /**
     * Sets the editable value
     *
     * @param $editable boolean
     *            the event should be editable
     */
    public function setEditable($editable)
    {
        $this->editable = $editable;
    }

    /**
     * Returns TRUE if this event is editable
     */
    public function getEditable()
    {
        return $this->editable;
    }

    /**
     * Sets the organizer_id
     *
     * @param $id int
     *            id
     */
    public function setOrganizerId($id)
    {
        $this->organizer_id = $id;
    }

    /**
     * Returns the organizer_id
     */
    public function getOrganizerId()
    {
        return $this->organizer_id;
    }

    /**
     * Returns the organizer object.
     */
    public function getOrganizerObject()
    {
        if (!$this->organizerObject) {
            $confArr = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['cal']);
            $useOrganizerStructure = ($confArr['useOrganizerStructure'] ? $confArr['useOrganizerStructure'] : 'tx_cal_organizer');
            $modelObj = &Registry::Registry('basic', 'modelcontroller');
            $this->organizerObject = $modelObj->findOrganizer(
                $this->getOrganizerId(),
                $useOrganizerStructure,
                $this->conf['pidList']
            );
        }

        return $this->organizerObject;
    }

    /**
     * Sets the organizerLink
     *
     * @param $id string
     *            link to an organizer
     */
    public function setOrganizerLinkUrl($id)
    {
        $this->organizerLink = $id;
    }

    /**
     * Return the organizerLink.
     * A html link to an organizer
     */
    public function getOrganizerLinkUrl()
    {
        return $this->organizerLink;
    }

    /**
     * Return the organizerpage.
     * The pid to link the organizer to
     */
    public function getOrganizerPage()
    {
        return $this->organizerPage;
    }

    /**
     * Sets the organizerPage
     *
     * @param $pid int
     *            to link the organizer to
     */
    public function setOrganizerPage($pid)
    {
        $this->organizerPage = $pid;
    }

    /**
     * Sets the location_id
     *
     * @param $id int
     *            id
     */
    public function setLocationId($id)
    {
        $this->location_id = $id;
    }

    /**
     * Returns the location_id
     */
    public function getLocationId()
    {
        return $this->location_id;
    }

    /**
     * Returns the location object.
     */
    public function getLocationObject()
    {
        if (!$this->locationObject) {
            $confArr = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['cal']);
            $useLocationStructure = ($confArr['useLocationStructure'] ? $confArr['useLocationStructure'] : 'tx_cal_location');
            $modelObj = &Registry::Registry('basic', 'modelcontroller');
            $this->locationObject = $modelObj->findLocation(
                $this->getLocationId(),
                $useLocationStructure,
                $this->conf['pidList']
            );
        }
        return $this->locationObject;
    }

    /**
     * Adds an id to the exception_single_ids array
     *
     * @param $id int
     *            to be added
     */
    public function addExceptionSingleId($id)
    {
        $this->exception_single_ids[] = $id;
    }

    /**
     * Returns the exception_single_ids array
     */
    public function getExceptionSingleIds()
    {
        return $this->exception_single_ids;
    }

    /**
     * Sets the exception_single_ids array
     *
     * @param $idArray array exception single ids
     */
    public function setExceptionSingleIds($idArray)
    {
        $this->exception_single_ids = $idArray;
    }

    /**
     * Adds an id to the notifyUserIds array
     *
     * @param $id int
     *            to be added
     */
    public function addNotifyUser($id)
    {
        $this->notifyUserIds[] = $id;
    }

    /**
     * Adds an category object to the category array
     *
     * @param $category object
     *            to be added
     */
    public function addCategory($category)
    {
        $this->categories[] = $category;
    }

    /**
     * Returns the notifyUserIds array
     */
    public function getNotifyUserIds()
    {
        return $this->notifyUserIds;
    }

    /**
     * Adds am id to the exceptionGroupIds array
     *
     * @param $id int
     *            to be added
     */
    public function addExceptionGroupId($id)
    {
        if ($id > 0) {
            array_push($this->exceptionGroupIds, $id);
        }
    }

    /**
     * Returns the exceptionGroupIds array
     */
    public function getExceptionGroupIds()
    {
        return $this->exceptionGroupIds;
    }

    /**
     * Sets the exceptionGroupIds array
     *
     * @param $idArray array exception group ids
     */
    public function setExceptionGroupIds($idArray)
    {
        $this->exceptionGroupIds = $idArray;
    }

    /**
     * Adds an id to the notifyGroupIds array
     *
     * @param $id int
     *            to be added
     */
    public function addNotifyGroup($id)
    {
        if ($id > 0) {
            $this->notifyGroupIds[] = $id;
        }
    }

    /**
     * Returns the notifyGroupIds array
     */
    public function getNotifyGroupIds()
    {
        return $this->notifyGroupIds;
    }

    /**
     * Adds an id to the creatorUserIds array
     *
     * @param $id int
     *            to be added
     */
    public function addCreatorUserId($id)
    {
        array_push($this->creatorUserIds, $id);
    }

    /**
     * Returns the creatorUserIds array
     */
    public function getCreatorUserIds()
    {
        return $this->creatorUserIds;
    }

    /**
     * Adds an id to the creatorGroupIds array
     *
     * @param $id int
     *            to be added
     */
    public function addCreatorGroupId($id)
    {
        $this->creatorGroupIds[] = $id;
    }

    /**
     * Returns the creatorGroupIds array
     */
    public function getCreatorGroupIds()
    {
        return $this->creatorGroupIds;
    }

    /**
     * Sets the headerstyle
     *
     * @param $style String
     *            name
     */
    public function setHeaderStyle($style)
    {
        if ($style != '') {
            $this->headerstyle = $style;
        }
    }

    /**
     * Returns the headerstyle name
     */
    public function getHeaderStyle()
    {
        return $this->headerstyle;
    }

    /**
     * Sets the bodystyle
     *
     * @param $style String
     *            name
     */
    public function setBodyStyle($style)
    {
        if ($style != '') {
            $this->bodystyle = $style;
        }
    }

    /**
     * Returns the bodystyle name
     */
    public function getBodyStyle()
    {
        return $this->bodystyle;
    }

    /* new */
    /**
     * @param $t
     */
    public function setPage($t)
    {
        $this->page = $t;
    }

    /**
     * @return mixed
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * @param $t
     */
    public function setExtUrl($t)
    {
        $this->ext_url = $t;
    }

    /**
     * @return mixed
     */
    public function getEventType()
    {
        return $this->event_type;
    }

    /**
     * @param $t
     */
    public function setEventType($t)
    {
        $this->event_type = $t;
    }

    /* new */
    /**
     * @param string $pidList
     */
    public function search($pidList = '')
    {
    }

    /**
     * @param $id
     */
    public function addSharedUser($id)
    {
        $this->sharedUsers[] = $id;
    }

    /**
     * @param $id
     */
    public function addSharedGroup($id)
    {
        $this->sharedGroups[] = $id;
    }

    /**
     * @return array
     */
    public function getSharedUsers()
    {
        return $this->sharedUsers;
    }

    /**
     * @return array
     */
    public function getSharedGroups()
    {
        return $this->sharedGroups;
    }

    /**
     * @param $userIds
     */
    public function setSharedUsers($userIds)
    {
        $this->sharedUsers = $userIds;
    }

    /**
     * @param $groupIds
     */
    public function setSharedGroups($groupIds)
    {
        $this->sharedGroups = $groupIds;
    }

    /**
     * @param $owner
     */
    public function setEventOwner($owner)
    {
        $this->eventOwner = $owner;
    }

    /**
     * @return mixed
     */
    public function getEventOwner()
    {
        return $this->eventOwner;
    }

    /**
     * @param $userId
     * @param $groupIdArray
     * @return bool
     */
    public function isEventOwner($userId, $groupIdArray)
    {
        if (is_array($this->eventOwner['fe_users']) && in_array($userId, $this->eventOwner['fe_users'])) {
            return true;
        }
        foreach ($groupIdArray as $id) {
            if (is_array($this->eventOwner['fe_groups']) && in_array($id, $this->eventOwner['fe_groups'])) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param $userId
     * @param $groupIdArray
     * @return bool
     */
    public function isSharedUser($userId, $groupIdArray)
    {
        if (is_array($this->getSharedUsers()) && in_array($userId, $this->getSharedUsers())) {
            return true;
        }
        foreach ($groupIdArray as $id) {
            if (is_array($this->getSharedGroups()) && in_array($id, $this->getSharedGroups())) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array
     */
    public function getAdditionalValuesAsArray()
    {
        $values = [];

        $values['page'] = $this->getPage();
        $values['type'] = $this->getEventType();
        $values['model_type'] = $this->getType();
        $values['intrval'] = $this->getInterval();
        $values['cnt'] = $this->getCount();

        $until = $this->getUntil();
        if (is_object($until)) {
            $values['until'] = $until->format('%Y%m%d');
        } else {
            $values['until'] = '00000101';
        }
        $values['category_headerstyle'] = $this->getHeaderstyle();
        $values['category_bodystyle'] = $this->getBodystyle();
        $start = &$this->getStart();
        $values['start_date'] = $start->format('%Y%m%d');
        $values['start_time'] = $start->getHour() * 3600 + $start->getMinute() * 60;
        $values['start'] = $this->getStartAsTimestamp();
        $end = &$this->getEnd();
        $values['end_date'] = $end->format('%Y%m%d');
        $values['end_time'] = $end->getHour() * 3600 + $end->getMinute() * 60;
        $values['end'] = $this->getEndAsTimestamp();
        $values['allday'] = $this->isAllday();
        $values['calendar_id'] = $this->getCalendarUid();
        $values['category_string'] = $this->getCategoriesAsString(false);

        return $values;
    }

    /**
     * @param bool $asLink
     * @return string
     */
    public function getCategoriesAsString($asLink = true)
    {
        /*
         * if($this->categoriesAsString){ return $this->categoriesAsString; }
         */
        $this->categoriesAsString = [];
        $rememberCats = [];
        $objectType = $this->getObjectType();

        if (count($this->categories)) {
            foreach ($this->categories as $categoryObject) {
                if (is_object($categoryObject)) {
                    if (in_array($categoryObject->getUid(), $rememberCats)) {
                        continue;
                    }

                    $rememberCats[] = $categoryObject->getUid();
                    // init object and hand over the data of the category as fake DB values
                    $this->initLocalCObject($categoryObject->getValuesAsArray());
                    $categoryTitle = $this->local_cObj->stdWrap(
                        $categoryObject->getTitle(),
                        $this->conf['view.'][$this->conf['view'] . '.'][$objectType . '.']['categoryLink_stdWrap.']
                    );

                    if ($asLink) {
                        $headerstyle = $categoryObject->getHeaderStyle();
                        $this->local_cObj->data['link_ATagParams'] = $headerstyle != '' ? ' class="' . $headerstyle . '"' : '';
                        $parameter['category'] = $categoryObject->getUid();
                        $parameter['offset'] = null;

                        $this->controller->getParametersForTyposcriptLink(
                            $this->local_cObj->data,
                            $parameter,
                            $this->conf['cache'],
                            $this->conf['clear_anyway']
                        );
                        $this->local_cObj->setCurrentVal($categoryTitle);
                        $this->categoriesAsString[] = $this->local_cObj->cObjGetSingle(
                            $this->conf['view.'][$this->conf['view'] . '.'][$objectType . '.']['categoryLink'],
                            $this->conf['view.'][$this->conf['view'] . '.'][$objectType . '.']['categoryLink.']
                        );
                    } else {
                        $this->categoriesAsString[] = $categoryTitle;
                    }
                }
            }
        }
        // reset the object
        $this->initLocalCObject();
        return implode(
            $this->local_cObj->cObjGetSingle(
                $this->conf['view.'][$this->conf['view'] . '.'][$objectType . '.']['categoryLink_splitChar'],
                $this->conf['view.'][$this->conf['view'] . '.'][$objectType . '.']['categoryLink_splitChar.']
            ),
            $this->categoriesAsString
        );
    }

    /**
     * @return array
     */
    public function getCategoryUidsAsArray()
    {
        if ($this->categoryUidsAsArray) {
            return $this->categoryUidsAsArray;
        }
        $first = true;
        $this->categoryUidsAsArray = [];
        $categories = &$this->getCategories();
        if (is_array($categories) && count($categories)) {
            foreach ($this->getCategories() as $categoryArray) {
                if (is_object($categoryArray)) {
                    if ($first) {
                        $this->categoryUidsAsArray[] = $categoryArray->getUid();
                        $first = false;
                    } else {
                        $this->categoryUidsAsArray[] = $categoryArray->getUid();
                    }
                }
            }
        }
        return $this->categoryUidsAsArray;
    }

    /**
     * @return mixed
     */
    public function cloneEvent()
    {
        $thisClass = get_class($this);
        $event = new $thisClass($this->getType());
        $event->setIsClone(true);
        return $event;
    }

    /**
     * Calls user function defined in TypoScript
     *
     * @param int $mConfKey
     *            if this value is empty the var $mConfKey is not processed
     * @param mixed $passVar
     *            this var is processed in the user function
     * @return mixed processed $passVar
     */
    public function userProcess($mConfKey, $passVar)
    {
        if ($this->conf[$mConfKey]) {
            $funcConf = $this->conf[$mConfKey . '.'];
            $funcConf['parentObj'] = &$this;
            $passVar = $this->controller->cObj->callUserFunction($this->conf[$mConfKey], $funcConf, $passVar);
        }
        return $passVar;
    }

    /**
     * @return int
     */
    public function isExternalPluginEvent()
    {
        return $this->externalPlugin;
    }

    public function getExternalPluginEventLink()
    {
    }

    /**
     * @param $currentParams
     */
    public function addAdditionalSingleViewUrlParams(&$currentParams)
    {
    }

    /**
     * @return float|int
     */
    public function getLengthInSeconds()
    {
        $eventStart = $this->getStart();
        $eventEnd = $this->getEnd();
        $days = Calc::dateDiff(
            $eventStart->getDay(),
            $eventStart->getMonth(),
            $eventStart->getYear(),
            $eventEnd->getDay(),
            $eventEnd->getMonth(),
            $eventEnd->getYear()
        );
        $hours = $eventEnd->getHour() - $eventStart->getHour();
        $minutes = $eventEnd->getMinute() - $eventStart->getMinute();
        return $days * 86400 + $hours * 3600 + $minutes * 60;
    }

    /**
     * @param $attendee
     */
    public function addAttendee(&$attendee)
    {
        $this->attendees[$attendee->getType()][] = $attendee;
    }

    /**
     * @param $attendees
     */
    public function setAttendees(&$attendees)
    {
        $this->attendees = &$attendees;
    }

    /**
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return int
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * @param $priority
     */
    public function setPriority($priority)
    {
        $this->priority = $priority;
    }

    /**
     * @return mixed
     */
    public function getCompleted()
    {
        return $this->completed;
    }

    /**
     * @param $completed
     */
    public function setCompleted($completed)
    {
        $this->completed = $completed;
    }

    /**
     * @return mixed
     */
    public function getDeviationDates()
    {
        return $this->deviationDates;
    }

    /**
     * @param $deviationDates
     */
    public function setDeviationDates($deviationDates)
    {
        $this->deviationDates = $deviationDates;
    }
}
