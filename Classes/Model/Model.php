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

use TYPO3\CMS\Cal\Controller\ModelController;
use TYPO3\CMS\Cal\Model\Pear\Date\Calc;
use TYPO3\CMS\Cal\Utility\Registry;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Base model for the calendar.
 * Provides basic model functionality that other
 * models can use or override by extending the class.
 */
abstract class Model extends BaseModel
{
    /**
     * @var CalendarDateTime
     */
    protected $start;

    /**
     * @var CalendarDateTime
     */
    protected $end;

    /**
     * @var int
     */
    protected $allday = 0;

    /**
     * @var string
     */
    protected $timezone = '';

    /**
     * @var string
     */
    protected $title = '';

    /**
     * @var int
     */
    protected $calendar_id = 0;

    /**
     * Name of the organizer (if one-time)
     * @var string
     */
    protected $organizer = '';

    /**
     * ID of the organizer within the TYPO3 system
     * @var int
     */
    protected $organizer_id = 0;

    /**
     * Link to the organizers page within the TYPO3 system
     * @var int
     */
    protected $organizer_pid = 0;

    /**
     * Link to the organizers homepage
     * @var string
     */
    protected $organizer_link = '';

    /**
     * @var bool
     */
    public $isClone = false;

    /**
     * @var
     */
    public $location;

    /**
     * @var
     */
    public $content;

    /**
     * @var int
     */
    public $calnumber = 1;

    /**
     * @var string
     */
    public $calname = '';

    /**
     * @var
     */
    public $url;

    /**
     * @var
     */
    public $alarmdescription;

    /**
     * @var
     */
    public $summary;

    /**
     * @var
     */
    public $description;

    /**
     * @var int
     */
    public $overlap = 1;

    /**
     * @var
     */
    public $_class;

    /**
     * @var CalendarDateTime
     */
    public $until;

    /**
     * @var string
     */
    public $freq = '';

    /**
     * @var
     */
    public $reccuring_end;

    /**
     * @var int
     */
    public $cnt = 0;

    /**
     * @var array
     */
    public $bysecond = [];

    /**
     * @var array
     */
    public $byminute = [];

    /**
     * @var array
     */
    public $byhour = [];

    /**
     * @var array
     */
    public $byday = [];

    /**
     * @var array
     */
    public $byweekno = [];

    /**
     * @var array
     */
    public $bymonth = [];

    /**
     * @var array
     */
    public $byyearday = [];

    /**
     * @var array
     */
    public $bymonthday = [];

    /**
     * @var array
     */
    public $byweekday = [];

    /**
     * @var array
     */
    public $bysetpos = [];

    /**
     * @var int
     */
    protected $intrval = 0;

    /**
     * @var string
     */
    public $wkst = '';

    /**
     * @var string
     */
    public $rdateType = '';

    /**
     * @var string
     */
    public $rdate = '';

    /**
     * @var string
     */
    public $rdateValues = '';

    /**
     * @var
     */
    public $displayend;

    /**
     * @var
     */
    public $spansday;

    /**
     * @var array <CategoryModel>
     */
    public $categories = [];

    /**
     * @var
     */
    public $categoriesAsString;

    /**
     * @var
     */
    public $categoryUidsAsArray;

    /**
     * @var int
     */
    public $location_id = 0;

    /**
     * @var
     */
    public $locationLink;

    /**
     * @var
     */
    public $locationPage;

    /**
     * @var
     */
    public $locationObject;

    /**
     * @var array
     */
    public $exception_single_ids = [];

    /**
     * @var array
     */
    public $notifyUserIds = [];

    /**
     * @var array
     */
    public $exceptionGroupIds = [];

    /**
     * @var array
     */
    public $notifyGroupIds = [];

    /**
     * @var array
     */
    public $creatorUserIds = [];

    /**
     * @var array
     */
    public $creatorGroupIds = [];

    /**
     * @var array
     */
    public $exceptionEvents = [];

    /**
     * @var bool
     */
    public $editable = false;

    /**
     * @var string
     */
    public $headerstyle = 'default_catheader'; // '#557CA3';//'#0000ff';

    /**
     * @var string
     */
    public $bodystyle = 'default_catbody'; // ''#6699CC';//'#ccffcc';

    /**
     * @var
     */
    public $deviationDates;

    /* new */
    /**
     * @var int
     */
    public $event_type = 0;

    /**
     * @var
     */
    public $page;

    /**
     * @var
     */
    public $ext_url;
    /* new */
    /**
     * @var int
     */
    public $externalPlugin = 0;

    /**
     * @var
     */
    public $eventOwner;

    /**
     * @var array
     */
    public $attendees = [];

    /**
     * @var int
     */
    public $status = 0;

    /**
     * @var int
     */
    public $priority = 0;

    /**
     * @var int
     */
    public $completed = 0;
    /**
     *
     */
    const EVENT_TYPE_DEFAULT = 0;
    /**
     *
     */
    const EVENT_TYPE_SHORTCUT = 1;
    /**
     *
     */
    const EVENT_TYPE_EXTERNAL = 2;
    /**
     *
     */
    const EVENT_TYPE_MEETING = 3;
    /**
     *
     */
    const EVENT_TYPE_TODO = 4;

    /**
     * @var Organizer
     */
    public $organizerObject;

    /**
     * @var CalendarModel
     */
    private $calendarObject;

    /**
     * Constructor.
     *
     * @param string $serviceKey
     */
    public function __construct($serviceKey)
    {
        $this->setObjectType('event');
        parent::__construct($serviceKey);
    }

    /**
     * Sets the event organizer.
     *
     * @param string $organizer
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
    public function getOrganizer(): string
    {
        return $this->organizer;
    }

    /**
     * Sets the event title.
     *
     * @param string $title
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
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * Returns the rendered event.
     *
     * @return string event.
     */
    public function renderEvent(): string
    {
        $cObj = &Registry::Registry('basic', 'cobj');
        $d = nl2br($cObj->parseFunc($this->getDescription(), $this->conf['parseFunc.']));
        $eventStart = $this->getStart();
        $eventEnd = $this->getEnd();
        return '<h3>' . $this->getTitle() . '</h3><span color="#000000"><ul>' . '<li>Start: ' . $eventStart->format('H:i') . '</li>' . '<li>End: ' . $eventEnd->format('H:i') . '</li>' . '<li> Organizer: ' . $this->getOrganizer() . '</li>' . '<li>Location: ' . $this->getLocation() . '</li>' . '<li>Description: ' . $d . '</li></ul></span>';
    }

    /**
     * Returns the rendered event for allday.
     *
     * @return string event for allday -> the title.
     */
    public function renderEventForAllDay(): string
    {
        return $this->getTitle();
    }

    /**
     * Returns the rendered event for day.
     *
     * @return string event for day -> the title.
     */
    public function renderEventForDay(): string
    {
        return $this->title;
    }

    /**
     * Returns the rendered event for week.
     *
     * @return string event for week -> the title.
     */
    public function renderEventForWeek(): string
    {
        return $this->title;
    }

    /**
     * Returns the rendered event month day.
     *
     * @return string event for month -> the title.
     */
    public function renderEventForMonth(): string
    {
        return $this->title;
    }

    /**
     * Returns the rendered event month day for a mini month view.
     *
     * @return string event for a mini month -> the title.
     */
    public function renderEventForMiniMonth(): string
    {
        return $this->title;
    }

    /**
     * Returns the rendered event for year.
     *
     * @return string event for year -> the title.
     */
    public function renderEventForYear(): string
    {
        return $this->title;
    }

    /**
     * Returns the location value.
     *
     * @return string location.
     */
    public function getLocation(): string
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
    public function getLocationLinkUrl(): string
    {
        return $this->locationLink;
    }

    /**
     * Sets the event location link value.
     *
     * @param string $locationLink
     */
    public function setLocationLinkUrl($locationLink)
    {
        $this->locationLink = $locationLink;
    }

    /**
     * Sets the event location page value.
     *
     * @param int $page
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
    public function getLocationPage(): int
    {
        return $this->locationPage;
    }

    /**
     * Returns the startdate object.
     *
     * @return CalendarDateTime startdate timeObject
     */
    public function getStart(): CalendarDateTime
    {
        return $this->start;
    }

    /**
     * Returns the enddate object.
     *
     * @return CalendarDateTime enddate timeObject
     */
    public function getEnd(): CalendarDateTime
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
     * @param CalendarDateTime $start
     */
    public function setStart($start)
    {
        $this->start = new CalendarDateTime();
        $this->start->copy($start);
        $this->row['start_date'] = $start->format('Ymd');
        $this->row['start_time'] = $start->getHour() * 3600 + $start->getMinute() * 60;
    }

    /**
     * Sets the event end.
     *
     * @param CalendarDateTime $end
     */
    public function setEnd($end)
    {
        $this->end = new CalendarDateTime();
        $this->end->copy($end);
        $this->row['end_date'] = $end->format('Ymd');
        $this->row['end_time'] = $end->getHour() * 3600 + $end->getMinute() * 60;
    }

    /**
     * Returns the startdate as unix timestamp.
     *
     * @return int startdate as unix timestamp
     */
    public function getStartAsTimestamp(): int
    {
        return $this->getStart()->format('U');

        $start = &$this->getStart();
        return $start->getDate(DATE_FORMAT_UNIXTIME);
    }

    /**
     * Returns the enddate as unix timestamp.
     *
     * @return int enddate as unix timestamp
     */
    public function getEndAsTimestamp(): int
    {
        return $this->getEnd()->format('U');
        $end = &$this->getEnd();
        return $end->getDate(DATE_FORMAT_UNIXTIME);
    }

    /**
     * Returns the cal number value.
     *
     * @return string calnumber
     */
    public function getCalNumber(): string
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
    public function getCalendarId(): int
    {
        return $this->calendar_id;
    }

    /**
     * Sets the calendar uid.
     *
     * @param $uid int
     */
    public function setCalendarId($uid)
    {
        $this->calendar_id = $uid;
    }

    /**
     * Returns the calendar object
     *
     * @return CalendarModel calendar
     */
    public function getCalendarObject(): CalendarModel
    {
        if (!$this->calendarObject) {
            $modelObj = &Registry::Registry('basic', 'modelcontroller');
            $this->calendarObject = $modelObj->findCalendar($this->getCalendarId());
        }

        return $this->calendarObject;
    }

    /**
     * Returns the calendar name.
     *
     * @return string calendar name
     */
    public function getCalName(): string
    {
        return $this->calname;
    }

    /**
     * Sets the calendar name.
     *
     * @param string $calname
     */
    public function setCalName($calname)
    {
        $this->calname = $calname;
    }

    /**
     * @return int
     */
    public function getOverlap(): int
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
     * @return string
     */
    public function getTimezone(): string
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
     * @return int
     */
    public function getDuration(): int
    {
        return $this->end->format('U') - $this->start->format('U');
    }

    /**
     * @param $duration
     * @return string
     */
    public function getFormatedDurationString($duration): string
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

        $rest3 = $rest2 % 60;
        $minutes = ($rest2 - $rest3) / 60;
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
    public function isAllDay(): int
    {
        return $this->allday;
    }

    /**
     * @return int
     */
    public function getAllDay(): int
    {
        return $this->allday;
    }

    /**
     * @param $boolean
     */
    public function setAllDay($boolean)
    {
        $this->allday = (int)$boolean;
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
    public function isClone(): bool
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
    public function getByMonth(): array
    {
        return $this->bymonth;
    }

    /**
     * @param string $bymonth
     */
    public function setByMonth($bymonth)
    {
        if ($bymonth !== '') {
            $this->bymonth = explode(',', $bymonth);
        }
        if (strtoupper($bymonth) === 'ALL' || in_array('all', $this->bymonth, true)) {
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
    public function getByDay(): array
    {
        return $this->byday;
    }

    /**
     * @param string $byday
     */
    public function setByDay($byday)
    {
        $byday = strtoupper($byday);
        if ($byday !== '') {
            $this->byday = explode(',', $byday);
        }

        if (strtoupper($byday) === 'ALL' || in_array('all', $this->byday, true)) {
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
    public function getByMonthDay(): array
    {
        return $this->bymonthday;
    }

    /**
     * @param string $byMonthDay
     */
    public function setByMonthDay($byMonthDay)
    {
        if ($byMonthDay !== '') {
            $this->bymonthday = GeneralUtility::trimExplode(',', $byMonthDay, 1);
        }
        if (strtoupper($byMonthDay) === 'ALL' || in_array('all', $this->bymonthday, true)) {
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
    public function getByWeekDay(): array
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
    public function getByWeekNo(): array
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
    public function getByMinute(): array
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
    public function getByHour(): array
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
    public function getBySecond(): array
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
    public function getByYearDay(): array
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
    public function getBySetPos(): array
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
    public function getWkst(): string
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
     * @return int
     */
    public function getInterval(): int
    {
        return $this->intrval;
    }

    /**
     * @param $interval
     */
    public function setInterval($interval)
    {
        $this->intrval = $interval;
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
     * Sets the description attribute
     *
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * Returns the until date object
     */
    public function getUntil(): CalendarDateTime
    {
        return $this->until;
    }

    /**
     * Sets the until object.
     *
     * @param CalendarDateTime $until
     */
    public function setUntil($until)
    {
        $this->until = $until;
    }

    /**
     * @return string
     */
    public function getFreq(): string
    {
        return $this->freq;
    }

    /**
     * Sets the recurring frequency
     * @param $freq
     */
    public function setFreq($freq)
    {
        $this->freq = $freq;
    }

    /**
     * Returns how often a recurring event is supposed to recur as max
     */
    public function getCount(): int
    {
        return $this->cnt;
    }

    /**
     * Sets how often a recurring event is supposed to recur as max
     *
     * @param $count int
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
    public function getRdate(): string
    {
        return $this->rdate;
    }

    /**
     * Sets the rdate value.
     *
     * @param string $rdate
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
    public function getRdateValues(): array
    {
        return GeneralUtility::trimExplode(',', $this->rdateValues, 1);
    }

    /**
     * Sets the rdate value.
     *
     * @param $rdateArray
     */
    public function setRdateValues($rdateArray)
    {
        $this->rdateValues = implode(',', $rdateArray);
    }

    /**
     * Returns the rdate type value.
     *
     * @return string rdate type value
     */
    public function getRdateType(): string
    {
        return $this->rdateType;
    }

    /**
     * Sets the rdate type value.
     *
     * @param string $rdateType
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
     * @param $spansday boolean the event lasts the whole day
     */
    public function setSpansDay($spansday)
    {
        $this->spansday = $spansday;
    }

    /**
     * Returns the categories (array)
     * @return array
     */
    public function getCategories(): array
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
     * @param $ex_event
     */
    public function addExceptionEvent($ex_event)
    {
        $this->exceptionEvents[] = $ex_event;
    }

    /**
     * Sets the exceptionEvents
     *
     * @param array $ex_events
     */
    public function setExceptionEvents($ex_events)
    {
        $this->exceptionEvents = $ex_events;
    }

    /**
     * Returns the exceptionEvents array
     */
    public function getExceptionEvents(): array
    {
        return $this->exceptionEvents;
    }

    /**
     * Sets the editable value
     *
     * @param bool $editable
     */
    public function setEditable($editable)
    {
        $this->editable = $editable;
    }

    /**
     * Returns TRUE if this event is editable
     */
    public function getEditable(): bool
    {
        return $this->editable;
    }

    /**
     * Sets the organizer_id
     *
     * @param $id int
     */
    public function setOrganizerId($id)
    {
        $this->organizer_id = $id;
    }

    /**
     * Returns the organizer_id
     */
    public function getOrganizerId(): int
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
            $useOrganizerStructure = ($confArr['useOrganizerStructure'] ?: 'tx_cal_organizer');
            $modelObj = GeneralUtility::makeInstance(ModelController::class);//&Registry::Registry('basic', 'modelcontroller');
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
     * @param $url string link to an organizer
     */
    public function setOrganizerLink($url)
    {
        $this->organizer_link = $url;
    }

    /**
     * Return the organizerLink.
     * @param string $view
     * @return string
     */
    public function getOrganizerLink($view = ''): string
    {
        return $this->organizer_link;
    }

    /**
     * The pid to link the organizer to
     */
    public function getOrganizerPid(): int
    {
        return $this->organizer_pid;
    }

    /**
     * Sets the organizerPage
     *
     * @param $pid int
     */
    public function setOrganizerPid($pid)
    {
        $this->organizer_pid = $pid;
    }

    /**
     * Sets the location_id
     *
     * @param $id int
     */
    public function setLocationId($id)
    {
        $this->location_id = $id;
    }

    /**
     * Returns the location_id
     */
    public function getLocationId(): int
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
            $useLocationStructure = ($confArr['useLocationStructure'] ?: 'tx_cal_location');
            $modelObj =  GeneralUtility::makeInstance(ModelController::class);//&Registry::Registry('basic', 'modelcontroller');
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
     * @param int $id
     */
    public function addExceptionSingleId($id)
    {
        $this->exception_single_ids[] = $id;
    }

    /**
     * Returns the exception_single_ids array
     */
    public function getExceptionSingleIds(): array
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
     * @param int $id
     */
    public function addNotifyUser($id)
    {
        $this->notifyUserIds[] = $id;
    }

    /**
     * Adds an category object to the category array
     *
     * @param CategoryModel $category
     */
    public function addCategory($category)
    {
        $this->categories[] = $category;
    }

    /**
     * Returns the notifyUserIds array
     */
    public function getNotifyUserIds(): array
    {
        return $this->notifyUserIds;
    }

    /**
     * Adds am id to the exceptionGroupIds array
     *
     * @param int $id
     */
    public function addExceptionGroupId($id)
    {
        if ($id > 0) {
            $this->exceptionGroupIds[] = $id;
        }
    }

    /**
     * Returns the exceptionGroupIds array
     */
    public function getExceptionGroupIds(): array
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
     * @param int $id
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
    public function getNotifyGroupIds(): array
    {
        return $this->notifyGroupIds;
    }

    /**
     * Adds an id to the creatorUserIds array
     *
     * @param int $id
     */
    public function addCreatorUserId($id)
    {
        $this->creatorUserIds[] = $id;
    }

    /**
     * Returns the creatorUserIds array
     */
    public function getCreatorUserIds(): array
    {
        return $this->creatorUserIds;
    }

    /**
     * Adds an id to the creatorGroupIds array
     *
     * @param int $id
     */
    public function addCreatorGroupId($id)
    {
        $this->creatorGroupIds[] = $id;
    }

    /**
     * Returns the creatorGroupIds array
     */
    public function getCreatorGroupIds(): array
    {
        return $this->creatorGroupIds;
    }

    /**
     * Sets the headerstyle
     *
     * @param string $style
     */
    public function setHeaderStyle($style)
    {
        $this->headerstyle = $style;
    }

    /**
     * Returns the headerstyle name
     */
    public function getHeaderStyle(): string
    {
        return $this->headerstyle;
    }

    /**
     * Sets the bodystyle
     *
     * @param string $style
     */
    public function setBodyStyle($style)
    {
        $this->bodystyle = $style;
    }

    /**
     * Returns the bodystyle name
     */
    public function getBodyStyle(): string
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
     * @return int
     */
    public function getEventType(): int
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
    public function isEventOwner($userId, $groupIdArray): bool
    {
        if (is_array($this->eventOwner['fe_users']) && in_array($userId, $this->eventOwner['fe_users'], true)) {
            return true;
        }
        foreach ($groupIdArray as $id) {
            if (is_array($this->eventOwner['fe_groups']) && in_array($id, $this->eventOwner['fe_groups'], true)) {
                return true;
            }
        }
        return false;
    }

    /**
     * @return array
     */
    public function getAdditionalValuesAsArray(): array
    {
        $values = [];

        $values['page'] = $this->getPage();
        $values['type'] = $this->getEventType();
        $values['model_type'] = $this->getType();
        $values['intrval'] = $this->getInterval();
        $values['cnt'] = $this->getCount();

        /** @var CalendarDateTime $until */
        $until = $this->getUntil();
        if (is_object($until)) {
            $values['until'] = $until->format('Ymd');
        } else {
            $values['until'] = '00000101';
        }
        $values['category_headerstyle'] = $this->getHeaderStyle();
        $values['category_bodystyle'] = $this->getBodyStyle();
        $start = &$this->getStart();
        $values['start_date'] = $start->format('Ymd');
        $values['start_time'] = $start->getHour() * 3600 + $start->getMinute() * 60;
        $values['start'] = $this->getStartAsTimestamp();
        $end = &$this->getEnd();
        $values['end_date'] = $end->format('Ymd');
        $values['end_time'] = $end->getHour() * 3600 + $end->getMinute() * 60;
        $values['end'] = $this->getEndAsTimestamp();
        $values['allday'] = $this->isAllDay();
        $values['calendar_id'] = $this->getCalendarId();
        $values['category_string'] = $this->getCategoriesAsString(false);

        return $values;
    }

    /**
     * @param bool $asLink
     * @return string
     */
    public function getCategoriesAsString($asLink = true): string
    {
        $this->categoriesAsString = [];
        $rememberCats = [];
        $objectType = $this->getObjectType();

        if (count($this->categories)) {
            foreach ($this->categories as $categoryObject) {
                if (is_object($categoryObject)) {
                    if (in_array($categoryObject->getUid(), $rememberCats, true)) {
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
                        $this->local_cObj->data['link_ATagParams'] = $headerstyle !== '' ? ' class="' . $headerstyle . '"' : '';
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
    public function getCategoryUidsAsArray(): array
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
        /** @var EventModel $event */
        $event = new $thisClass($this->getType());
        $event->setIsClone(true);
        return $event;
    }

    /**
     * Calls user function defined in TypoScript
     *
     * @param int $mConfKey if this value is empty the var $mConfKey is not processed
     * @param mixed $passVar this var is processed in the user function
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
     * @param AttendeeModel $attendee
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
    public function getStatus(): int
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
    public function getPriority(): int
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
     * @return int
     */
    public function getCompleted(): int
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
