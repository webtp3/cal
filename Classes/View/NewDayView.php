<?php

/*
 * This file is part of the web-tp3/cal.
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace TYPO3\CMS\Cal\View;

use TYPO3\CMS\Cal\Model\CalendarDateTime;
use TYPO3\CMS\Cal\Model\EventModel;
use TYPO3\CMS\Cal\Utility\Registry;
use TYPO3\CMS\Core\Exception;

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

/**
 * Base model for the day.
 */
class NewDayView extends NewTimeView
{
    private $hasAlldayEvents = false;
    private $Ymd;
    private $time;
    private $events = [];

    /**
     * Constructor.
     * @param $day
     * @param $month
     * @param $year
     * @param int $parentMonth
     */
    public function __construct($day, $month, $year, $parentMonth = -1)
    {
        parent::__construct();
        $this->setMySubpart('DAY_SUBPART');
        $this->setDay(intval($day));
        $this->setMonth(intval($month));
        $this->setYear(intval($year));
        $date = new CalendarDateTime();
        $date->setDay($this->getDay());
        $date->setMonth($this->getMonth());
        $date->setYear($this->getYear());
        $this->setWeekdayNumber($date->format('w'));
        $this->setYmd($date->format('Ymd'));
        $this->time = $date->format('U');
        if ($parentMonth >= 0) {
            $this->setParentMonth(intval($parentMonth));
        } else {
            $this->setParentMonth($this->getMonth());
        }
    }

    /**
     * @param EventModel $event
     */
    public function addEvent(&$event)
    {
        $this->events[$event->getStart()->format('Hi')][$event->getUid()] = &$event;
    }

    /**
     * @param $template
     * @param $sims
     * @param $rems
     * @param $wrapped
     * @param $view
     */
    public function getEventsMarker(& $template, & $sims, & $rems, & $wrapped, $view)
    {
        $content = '';
        $timeKeys = array_keys($this->events);
        foreach ($timeKeys as $timeKey) {
            $eventKeys = array_keys($this->events[$timeKey]);
            foreach ($eventKeys as $eventKey) {
                if (!$this->events[$timeKey][$eventKey]->isAllday()) {
                    $content .= $this->events[$timeKey][$eventKey]->renderEventFor($view);
                }
            }
        }

        $sims['###EVENTS###'] = $content;
    }

    /**
     * @param $template
     * @param $sims
     * @param $rems
     * @param $wrapped
     * @param $view
     */
    public function getEventsColumnMarker(& $template, & $sims, & $rems, & $wrapped, $view)
    {
        $conf = &Registry::Registry('basic', 'conf');
        $dayStart = $conf['view.']['day.']['dayStart']; // '0700'; // Start time for day grid
        $dayEnd = $conf['view.']['day.']['dayEnd']; // '2300'; // End time for day grid
        $gridLength = $conf['view.']['day.']['gridLength']; // '15'; // Grid distance in minutes for day view, multiples of 15 preferred

        while (strlen($dayStart) < 6) {
            $dayStart .= '0';
        }
        while (strlen($dayEnd) < 6) {
            $dayEnd .= '0';
        }
        if ((int)$gridLength === 0) {
            $gridLength = 15;
        }

        $d_start = new CalendarDateTime($this->getYmd() . $dayStart);
        $d_start->setTZbyID('UTC');
        $d_end = new CalendarDateTime($this->getYmd() . $dayEnd);
        $d_end->setTZbyID('UTC');

        // splitting the events into H:M, to find out if events run in parallel
        $i = new CalendarDateTime();
        $eventArray = [];
        $viewArray = [];
        $positionArray = [];
        $timeKeys = array_keys($this->events);

        // Sort by starttime, otherwise $pos_array keys may be assigned multiple times and events may therefore overwrite each other
        asort($timeKeys);
        //throw new Exception('Web/typo3conf/ext/cal/Classes/View/NewDayView.php:130');

        foreach ($timeKeys as $timeKey) {
            $eventKeys = array_keys($this->events[$timeKey]);
            foreach ($eventKeys as $eventKey) {
                if (!$this->events[$timeKey][$eventKey]->isAllday() && ($this->events[$timeKey][$eventKey]->getStart()->format('Ymd') === $this->events[$timeKey][$eventKey]->getEnd()->format('Ymd'))) {
                    $eventMappingKey = $this->events[$timeKey][$eventKey]->getType() . '_' . $this->events[$timeKey][$eventKey]->getUid() . '_' . $this->events[$timeKey][$eventKey]->getStart()->format('YmdHi');
                    $eventArray[$eventMappingKey] = &$this->events[$timeKey][$eventKey];

                    $i->copy($this->events[$timeKey][$eventKey]->getStart());
                    $time = $i->format('U');
                    $time -= ($time % ($gridLength * 60));
                    $i = new CalendarDateTime(date('Y-m-d H:i', $time));
                    if ($i->before($d_start)) {
                        $i->copy($d_start);
                    }

                    $entries = 0;
                    for (; $i->before($this->events[$timeKey][$eventKey]->getEnd()); $i->addSeconds($gridLength * 60)) {
                        $ymd = $i->format('Ymd');
                        $hm = $i->format('Hi');
                        $viewArray[$ymd][$hm][] = $eventMappingKey;
                        $entries++;

                        $count = count($viewArray[$ymd][$hm]);

                        foreach ($viewArray[$ymd][$hm] as $mappingKey) {
                            if (!$positionArray[$mappingKey] || $positionArray[$mappingKey] < $count) {
                                $positionArray[$mappingKey] = $count;
                            }
                        }
                    }
                    $rowspan_array[$this->getYmd()][$eventMappingKey] = $entries;
                }
            }
        }

        if (!empty($viewArray[$this->getYmd()])) {
            $max = [];
            foreach ($viewArray[$this->getYmd()] as $array_time => $time_val) {
                $c = count($viewArray[$this->getYmd()][$array_time]);
                $max[] = $c;
            }
            $nbrGridCols = max($max);
        } else {
            $nbrGridCols = 1;
        }

        // splitting the events into H:M, to find out if events run in parallel
        $pos_array = [];
        $i->copy($d_start);
        $t_array = [];

        while ($i->before($d_end)) {
            $i_formatted = $i->format('Hi');

            if (is_array($viewArray[$this->getYmd()][$i_formatted]) && count($viewArray[$this->getYmd()][$i_formatted]) > 0) {
                foreach ($viewArray[$this->getYmd()][$i_formatted] as $eventKey) {
                    $event = &$eventArray[$eventKey];
                    $eventStart = $event->getStart();
                    $eventMappingKey = $event->getType() . '_' . $event->getUid() . '_' . $eventStart->format('YmdHi');
                    if (array_key_exists($eventMappingKey, $pos_array)) {
                        $eventEnd = $event->getEnd();
                        $eventEnd->subtractSeconds(($eventEnd->getMinute() % $gridLength) * 60);
                        if ($i_formatted >= $eventEnd->format('Hi')) {
                            $t_array[$i_formatted][$pos_array[$eventMappingKey]] = [
                                'ended' => $eventMappingKey
                            ];
                        } else {
                            $t_array[$i_formatted][$pos_array[$eventMappingKey]] = [
                                'started' => $eventMappingKey
                            ];
                        }
                    } else {
                        for ($j = 0; $j < $nbrGridCols; $j++) {
                            if (empty($t_array[$i_formatted][$j])) {
                                $pos_array[$eventMappingKey] = $j;
                                $t_array[$i_formatted][$j] = [
                                    'begin' => $eventMappingKey
                                ];
                                break;
                            }
                        }
                    }
                }
            } else {
                $t_array[$i_formatted] = '';
            }

            $i->addSeconds($gridLength * 60);
        }

        $sims['###EVENTS_COLUMN###'] = $this->renderEventsColumn(
            $eventArray,
            $d_start,
            $d_end,
            $viewArray,
            $t_array,
            $positionArray
        );
    }

    /**
     * @param $eventArray
     * @param $d_start
     * @param $d_end
     * @param $view_array
     * @param $t_array
     * @param $positionArray
     * @return string
     */
    private function renderEventsColumn(
        &$eventArray,
        &$d_start,
        &$d_end,
        &$view_array,
        &$t_array,
        &$positionArray
    ): string {
        $daydisplay = '';
        $conf = &Registry::Registry('basic', 'conf');

        $cal_time_obj = new CalendarDateTime($this->getYmd() . '000000');
        $cal_time_obj->setTZbyID('UTC');
        foreach ($t_array as $cal_time => $val) {
            preg_match('/([0-9]{2})([0-9]{2})/', $cal_time, $dTimeStart);
            $cal_time_obj->setHour($dTimeStart[1] ?? 0);
            $cal_time_obj->setMinute($dTimeStart[2] ?? 0);

            if ($val !== '' && count($val) > 0) {
                for ($i = 0, $iMax = count($val); $i < $iMax; $i++) {
                    if (!empty($val[$i])) {
                        $keys = array_keys($val[$i]);
                        if ($keys[0] === 'begin') {
                            $event = &$eventArray[$val[$i][$keys[0]]];
                            $eventContent = $event->renderEventFor($conf['view']);
                            $colSpan = $positionArray[$val[$i][$keys[0]]];
                            // left
                            // 1 = 0
                            // 2 = 50
                            // 3 = 33.333
                            // 4 = 25

                            $left = 0;
                            if ($colSpan > 1) {
                                $left = 100 / $colSpan * $i;
                            }

                            // width
                            // 1 = 100
                            // 2 = 85,50
                            // 3 = 56.666, 56.666, 33.333
                            // 4 = 42.5, 42.5, 42.5, 25
                            // 5 = 34,34,34,34,20

                            $width = 100;
                            if ($colSpan > 1) {
                                $width = 135 / $colSpan;
                            }

                            // TODO: move this into a hook
                            $eventContent = str_replace([
                                '***LEFT***',
                                '***WIDTH***'
                            ], [
                                $left,
                                $width
                            ], $eventContent);

                            $daydisplay .= $eventContent;
                            // End event drawing
                        }
                    }
                }
            }
        }
        return $daydisplay;
    }

    /**
     * @param $template
     * @param $sims
     * @param $rems
     * @param $wrapped
     * @param $view
     */
    public function getDayClassesMarker(& $template, & $sims, & $rems, & $wrapped, $view)
    {
        $classes = 'day weekday' . $this->getWeekdayNumber();
        if ($this->current) {
            $classes .= ' currentDay';
        }
        if ($this->selected) {
            $classes .= ' selectedDay';
        }
        if (!empty($this->events) || $this->hasAlldayEvents) {
            $classes .= ' withEventDay';
        }
        if (intval($this->getParentMonth()) !== intval($this->getMonth())) {
            $classes .= ' monthOff';
        }

        $sims['###DAY_CLASSES###'] = $classes;
    }

    /**
     * @param $template
     * @param $sims
     * @param $rems
     * @param $wrapped
     * @param $view
     */
    public function getDayTitleMarker(& $template, & $sims, & $rems, & $wrapped, $view)
    {
        $sims['###DAY_TITLE###'] = $this->getWeekdayString($this->time);
    }

    /**
     * @param $template
     * @param $sims
     * @param $rems
     * @param $wrapped
     * @param $view
     */
    public function getDayLinkMarker(& $template, & $sims, & $rems, & $wrapped, $view)
    {
        $sims['###DAY_LINK###'] = $this->getDayLink($view, $this->time);
    }

    /**
     * @param $view
     * @param $value
     * @param bool $hasEvent
     * @return mixed
     */
    public function getDayLink($view, $value, $hasEvent = false)
    {
        $rightsObj = &Registry::Registry('basic', 'rightscontroller');
        $conf = &Registry::Registry('basic', 'conf');
        $dayLinkViewTarget = $conf['view.']['dayLinkTarget'];
        $isAllowedToCreateEvent = $rightsObj->isAllowedToCreateEvent();

        $local_cObj = &$this->getLocalCObject();
        $local_cObj->setCurrentVal($value);
        $local_cObj->data['view'] = $dayLinkViewTarget;
        $local_cObj->data['link_timestamp'] = $value;
        $controller = &Registry::Registry('basic', 'controller');

        if (($hasEvent || !empty($this->events) || $this->hasAlldayEvents || $isAllowedToCreateEvent) && ($rightsObj->isViewEnabled($dayLinkViewTarget) || $conf['view.'][$dayLinkViewTarget . '.'][$dayLinkViewTarget . 'ViewPid'])) {
            $controller->getParametersForTyposcriptLink(
                $local_cObj->data,
                [
                    'getdate' => $this->getYmd(),
                    'view' => $dayLinkViewTarget,
                    $controller->getPointerName() => null
                ],
                $conf['cache'],
                $conf['clear_anyway'],
                $conf['view.'][$dayLinkViewTarget . '.'][$dayLinkViewTarget . 'ViewPid']
            );
        }
        return $local_cObj->cObjGetSingle(
            $conf['view.'][$view . '.'][$dayLinkViewTarget . 'ViewLink'],
            $conf['view.'][$view . '.'][$dayLinkViewTarget . 'ViewLink.']
        );
    }

    /**
     * @param $template
     * @param $sims
     * @param $rems
     * @param $wrapped
     * @param $view
     */
    public function getAlldayMarker(& $template, & $sims, & $rems, & $wrapped, $view)
    {
        $content = '';
        $timeKeys = array_keys($this->events);
        foreach ($timeKeys as $timeKey) {
            $eventKeys = array_keys($this->events[$timeKey]);
            foreach ($eventKeys as $eventKey) {
                if ($this->events[$timeKey][$eventKey]->isAllday() || ($this->events[$timeKey][$eventKey]->getStart()->format('Ymd') !== $this->events[$timeKey][$eventKey]->getEnd()->format('Ymd'))) {
                    $content .= $this->events[$timeKey][$eventKey]->renderEventFor($view);
                }
            }
        }
        if ($content === '' && ($view === 'week' || $view === 'day')) {
            $content = '<td class="st-c st-s">&nbsp;</td>';
        }
        $sims['###ALLDAY###'] = $content;
    }

    /**
     * @param $dateObject
     */
    public function setCurrent(&$dateObject)
    {
        if ($this->getDay() === $dateObject->day && $this->getMonth() === $dateObject->month && $this->getYear() === $dateObject->year) {
            $this->current = true;
        }
    }

    /**
     * @param $dateObject
     */
    public function setSelected(&$dateObject)
    {
        if ($this->getDay() === $dateObject->day && $this->getMonth() === $dateObject->month && $this->getYear() === $dateObject->year) {
            $this->selected = true;
        }
    }

    /**
     * @return int
     */
    public function getTime(): int
    {
        return $this->time;
    }

    /**
     * @return mixed
     */
    public function getYmd()
    {
        return $this->Ymd;
    }

    /**
     * @param $ymd
     */
    public function setYmd($ymd)
    {
        $this->Ymd = $ymd;
    }

    /**
     * @return array
     */
    public function getEvents(): array
    {
        return $this->events;
    }

    /**
     * @param $events
     */
    public function setEvents($events)
    {
        $this->events = $events;
    }

    /**
     * @return bool
     */
    public function getHasAlldayEvents(): bool
    {
        return $this->hasAlldayEvents;
    }

    /**
     * @param $hasAlldayEvents
     */
    public function setHasAlldayEvents($hasAlldayEvents)
    {
        $this->hasAlldayEvents = $hasAlldayEvents;
    }

    /**
     * @return bool
     */
    public function hasEvents(): bool
    {
        return !empty($this->getEvents()) || $this->getHasAlldayEvents();
    }
}
