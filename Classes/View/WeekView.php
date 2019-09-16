<?php

/*
 * This file is part of the web-tp3/cal.
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace TYPO3\CMS\Cal\View;

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
use TYPO3\CMS\Cal\Model\CalendarDateTime;
use TYPO3\CMS\Cal\Model\EventModel;
use TYPO3\CMS\Cal\Model\Pear\Date\Calc;
use TYPO3\CMS\Cal\Utility\Functions;

/**
 * A concrete view for the calendar.
 * It is based on the phpicalendar project
 */
class WeekView extends BaseView
{
    /**
     * @param $master_array
     * @param $getdate
     * @return mixed
     */
    public function newDrawWeek(&$master_array, $getdate)
    {
        if (!isset($getdate) || $getdate === '') {
            $getdate = new CalendarDateTime();
        } else {
            $getdate = new CalendarDateTime($getdate);
        }
        $week = $getdate->getWeekOfYear();
        $year = $getdate->getYear();
        if ($getdate->getMonth() === 12 && $week === 1) {
            $year++;
        }
        $weekModel = new NewWeekView($week, $year);
        $today = new CalendarDateTime();
        $weekModel->setCurrent($today);
        $weekModel->setSelected($getdate);

        $weekdayLength = intval($this->conf['view.']['month.']['weekdayLength' . ucwords($type) . 'Month']);
        if ($weekdayLength > 0) {
            $weekModel->setWeekDayLength($weekdayLength);
        }

        $masterArrayKeys = array_keys($master_array);
        foreach ($masterArrayKeys as $dateKey) {
            $dateArray = &$master_array[$dateKey];
            $dateArrayKeys = array_keys($dateArray);
            foreach ($dateArrayKeys as $timeKey) {
                $arrayOfEvents = &$dateArray[$timeKey];
                $eventKeys = array_keys($arrayOfEvents);
                foreach ($eventKeys as $eventKey) {
                    $weekModel->addEvent($arrayOfEvents[$eventKey]);
                }
            }
        }

        $subpart = Functions::getContent($this->conf['view.']['week.']['newWeekTemplate']);
        $page = Functions::getContent($this->conf['view.']['week.']['weekTemplate']);
        $page = str_replace('###WEEK###', $weekModel->render($subpart), $page);

        $rems = [];

        return $this->finish($page, $rems);
    }

    /**
     * Draws the week view.
     *
     * @param $master_array array The events to be drawn.
     * @param $getdate integer The date of the event
     * @return string The HTML output.
     */
    public function drawWeek(&$master_array, $getdate): string
    {
        if ($this->conf['useNewTemplatesAndRendering']) {
            return $this->newDrawWeek($master_array, $getdate);
        }
        $this->_init($master_array);

        $page = Functions::getContent($this->conf['view.']['week.']['weekTemplate']);
        if ($page === '') {
            return '<h3>week: no template file found:</h3>' . $this->conf['view.']['week.']['weekTemplate'] . "<br />Please check your template record and add both cal items at 'include static (from extension)'";
        }

        $weekTemplate = $this->markerBasedTemplateService->getSubpart($page, '###WEEK_TEMPLATE###');
        if ($weekTemplate === '') {
            $rems = [];
            return $this->finish($page, $rems);
        }

        $dayStart = $this->conf['view.']['day.']['dayStart']; // '0700'; // Start time for day grid
        $dayEnd = $this->conf['view.']['day.']['dayEnd']; // '2300'; // End time for day grid
        $gridLength = $this->conf['view.']['day.']['gridLength']; // '15'; // Grid distance in minutes for day view, multiples of 15 preferred

        if (!isset($getdate) || $getdate === '') {
            $getdate_obj = new CalendarDateTime();
            $getdate = $getdate_obj->format('Ymd');
        }

        $day_array2 = [];
        preg_match('/([0-9]{4})([0-9]{2})([0-9]{2})/', $getdate, $day_array2);
        list($this_year, $this_month, $this_day) = $day_array2;
        $unix_time = new CalendarDateTime($getdate . '000000');
        $today = new CalendarDateTime();
        $todayFormatted = $today->format('Ymd');

        $now = new CalendarDateTime($getdate . '000000');
        $now->addSeconds(60 * 60 * 24 * 31);

        $now = new CalendarDateTime($getdate . '000000');
        $startOfPrevMonth = new CalendarDateTime(Calc::endOfPrevMonth($this_day, $this_month, $this_year));
        $startOfPrevMonth->setDay(1);
        $now->subtractSeconds(60 * 60 * 24 * 31);

        $next_week_obj = new CalendarDateTime();
        $next_week_obj->copy($unix_time);
        $next_week_obj->addSeconds(60 * 60 * 24 * 7);
        $next_week_obj->subtractSeconds(60 * 60 * 24 * 7 * 2);

        $dateOfWeek = Calc::beginOfWeek($unix_time->getDay(), $unix_time->getMonth(), $unix_time->getYear());

        $week_start_day = new CalendarDateTime($dateOfWeek . '000000');

        // Nasty fix to work with TS strftime
        $start_week_time = new CalendarDateTime($dateOfWeek . '000000');
        $start_week_time->setTZbyID('UTC');
        $end_week_time = new CalendarDateTime();
        $end_week_time->copy($start_week_time);
        $end_week_time->addSeconds(604799);

        $GLOBALS['TSFE']->register['cal_week_endtime'] = $end_week_time->format('U');
        $GLOBALS['TSFE']->register['cal_week_starttime'] = $start_week_time->format('U');
        $display_date = $this->cObj->cObjGetSingle(
            $this->conf['view.']['week.']['titleWrap'],
            $this->conf['view.']['week.']['titleWrap.']
        );

        $this->initLocalCObject();
        $dayLinkViewTarget = &$this->conf['view.']['dayLinkTarget'];
        $this->local_cObj->data['view'] = $dayLinkViewTarget;

        $this->local_cObj->setCurrentVal($this->conf['view.']['week.']['legendNextDayLink']);
        $legend_next_day_link = $this->local_cObj->cObjGetSingle(
            $this->conf['view.']['week.']['legendNextDayLink'],
            $this->conf['view.']['week.']['legendNextDayLink.']
        );

        $this->local_cObj->setCurrentVal($this->conf['view.']['week.']['legendPrevDayLink']);
        $legend_prev_day_link = $this->local_cObj->cObjGetSingle(
            $this->conf['view.']['week.']['legendPrevDayLink'],
            $this->conf['view.']['week.']['legendPrevDayLink.']
        );

        $eventArray = [];

        $view_array = [];
        $rowspan_array = [];

        $endOfDay = new CalendarDateTime();
        $startOfDay = new CalendarDateTime();

        // creating the dateObjects only once:
        $starttime = new CalendarDateTime();
        $endtime = new CalendarDateTime();
        $j = new CalendarDateTime();

        if (count($this->master_array) > 0) {
            $masterKeys = array_keys($this->master_array);
            foreach ($masterKeys as $ovlKey) {
                $dTimeStart = [];
                $dTimeEnd = [];
                $dDate = [];
                preg_match('/([0-9]{2})([0-9]{2})/', $this->conf['view.']['day.']['dayStart'], $dTimeStart);
                preg_match('/([0-9]{2})([0-9]{2})/', $this->conf['view.']['day.']['dayEnd'], $dTimeEnd);
                preg_match('/([0-9]{4})([0-9]{2})([0-9]{2})/', $ovlKey, $dDate);

                $d_start = new CalendarDateTime($dDate[1] . $dDate[2] . $dDate[3] . ' ' . $dTimeStart[1] . ':' . sprintf(
                    '%02d',
                    $dTimeStart[2]
                ) . ':00');
                $d_start->setTZbyID('UTC');
                $d_end = new CalendarDateTime($dDate[1] . $dDate[2] . $dDate[3] . ' ' . $dTimeEnd[1] . ':' . sprintf(
                    '%02d',
                    $dTimeEnd[2]
                ) . ':00');
                $d_end->setTZbyID('UTC');

                // minus 1 second to allow endtime 24:00
                $d_end->subtractSeconds(1);
                $ovlTimeKeys = array_keys($this->master_array[$ovlKey]);
                foreach ($ovlTimeKeys as $ovl_time_key) {
                    $ovlDayKeys = array_keys($this->master_array[$ovlKey][$ovl_time_key]);
                    foreach ($ovlDayKeys as $ovl2Key) {
                        /** @var EventModel $event */
                        $event = &$this->master_array[$ovlKey][$ovl_time_key][$ovl2Key];
                        $eventStart = $event->getStart();
                        $eventMappingKey = $event->getType() . '_' . $event->getUid() . '_' . $eventStart->format('YmdHM');
                        $eventArray[$ovlKey . '_' . $eventMappingKey] = &$event;

                        $starttime->copy($event->getStart());
                        $endtime->copy($event->getEnd());

                        if ((int)$ovl_time_key === '-1') {
                            $j->copy($starttime);
                            $view_array[$j->format('Ymd')]['-1'][] = $ovlKey . '_' . $eventMappingKey;
                            $j->addSeconds(86400);
                            for (; $j->before($endtime) && $j->before($end_week_time); $j->addSeconds(86400)) {
                                $view_array[$j->format('Ymd')]['-1'][] = $ovlKey . '_' . $eventMappingKey;
                            }
                        } elseif ($starttime->before($end_week_time)) {
                            $starttime->subtractSeconds(($starttime->getMinute() % $gridLength) * 60);
                            $endtime->addSeconds(($endtime->getMinute() % $gridLength) * 60);

                            $entries = 1;
                            $old_day = new CalendarDateTime($ovlKey . '000000');

                            $endOfDay->copy($d_end);
                            $startOfDay->copy($d_start);

                            // get x-array possition
                            foreach ($view_array[$starttime->format('%Y%m%d')][$starttime->format('%H%M')] as $k => $kValue) {
                                if (empty($view_array[$starttime->format('Ymd')][$starttime->format('HM')][$k])) {
                                    break;
                                }
                            }

                            $j->copy($starttime);

                            if ($j->before($startOfDay)) {
                                $j->copy($startOfDay);
                            }

                            $counter = 0;

                            while ($j->before($endtime) && $j->before($end_week_time)) {
                                $counter++;
                                $view_array[$j->format('Ymd')][$j->format('HM')][] = $ovlKey . '_' . $eventMappingKey;
                                if ($j->after($endOfDay)) {
                                    $rowspan_array[$old_day->format('Ymd')][$eventMappingKey] = $entries - 1;
                                    $endOfDay->addSeconds(86400);
                                    $old_day->copy($endOfDay);
                                    $startOfDay->addSeconds(86400);
                                    $j->addSeconds(86400);
                                    $j->setHour($startOfDay->getHour());
                                    $j->setMinute($startOfDay->getMinute());
                                    $j->subtractSeconds($gridLength * 60);
                                    foreach ($view_array[$startOfDay->format('%Y%m%d')][$startOfDay->format('%H%M')] as $k => $kValue) {
                                        if (empty($view_array[$startOfDay->format('Ymd')][$startOfDay->format('HM')][$k])) {
                                            break;
                                        }
                                    }
                                    $entries = 0;
                                    $eventArray[$startOfDay->format('Ymd') . '_' . $eventMappingKey] = &$event;
                                }
                                $j->addSeconds($gridLength * 60);
                                $entries++;
                            }
                            $rowspan_array[$old_day->format('Ymd')][$eventMappingKey] = $entries - 1;
                        }
                    }
                }
            }
        }

        if ((int)$this->conf['view.']['week.']['dynamic'] === 1) {
            $dayStart = '2359';
            $dayEnd = '0000';
            $firstStart = true;
            $firstEnd = true;
            $dynamicEnd = intval($end_week_time->format('Ymd'));
            for ($dynamicStart = intval($start_week_time->format('Ymd')); $dynamicStart < $dynamicEnd; $dynamicStart++) {
                if (is_array($view_array[$dynamicStart])) {
                    $timeKeys = array_keys($view_array[$dynamicStart]);
                    $formatedLast = array_pop($timeKeys);
                    while (intval($formatedLast) < 0 && !empty($timeKeys)) {
                        $formatedLast = array_pop($timeKeys);
                    }

                    $formatedFirst = null;
                    if (count($timeKeys) > 0) {
                        do {
                            $formatedFirst = array_shift($timeKeys);
                        } while (intval($formatedFirst) < 0 && !empty($timeKeys));
                    } else {
                        $formatedFirst = $formatedLast;
                    }
                    if (intval($formatedFirst) > 0 && (intval($formatedFirst) < intval($dayStart) || $firstStart)) {
                        $dayStart = sprintf('%04d', $formatedFirst);
                        $firstStart = false;
                    }
                    if ($firstEnd || intval($formatedLast) > intval($dayEnd)) {
                        $dayEnd = sprintf('%04d', $formatedLast + $gridLength);
                        $firstEnd = false;
                    }
                }
            }
            $dayStart = substr($dayStart, 0, 2) . '00';
        }
        $startdate = new CalendarDateTime($start_week_time->format('Ymd') . '000000');
        $enddate = new CalendarDateTime();
        $enddate->copy($end_week_time);
        for ($i = $startdate; $enddate->after($i); $i->addSeconds(86400)) {
            if (!empty($view_array[$i->format('Ymd')])) {
                $max = [];
                foreach (array_keys($view_array[$i->format('Ymd')]) as $array_time) {
                    $c = count($view_array[$i->format('Ymd')][$array_time]);
                    $max[] = $c;
                }
                $nbrGridCols[$i->format('Ymd')] = max($max);
            } else {
                $nbrGridCols[$i->format('Ymd')] = 1;
            }
        }
        $t_array = [];
        $pos_array = [];
        preg_match('/([0-9]{2})([0-9]{2})/', $dayStart, $dTimeStart);
        preg_match('/([0-9]{2})([0-9]{2})/', $dayEnd, $dTimeEnd);

        $nd = new CalendarDateTime();

        foreach (array_keys($view_array) as $week_key) {
            preg_match('/([0-9]{4})([0-9]{2})([0-9]{2})/', $week_key, $dDate);
            $d_start = new CalendarDateTime($dDate[1] . $dDate[2] . $dDate[3] . ' ' . $dTimeStart[1] . ':' . sprintf(
                '%02d',
                $dTimeStart[2]
            ) . ':00');
            $d_start->setTZbyID('UTC');
            $d_end = new CalendarDateTime($dDate[1] . $dDate[2] . $dDate[3] . ' ' . $dTimeEnd[1] . ':' . sprintf(
                '%02d',
                $dTimeEnd[2]
            ) . ':00');
            $d_end->setTZbyID('UTC');

            $d_start->subtractSeconds(($d_start->getMinute() % $gridLength) * 60);
            $d_end->addSeconds(($gridLength - ($d_end->getMinute() % $gridLength)) * 60);

            for ($i->copy($d_start); !$i->after($d_end); $i->addSeconds($gridLength * 60)) {
                $timeKey = $i->format('HM');
                if (is_array($view_array[$week_key][$timeKey]) && count($view_array[$week_key][$timeKey]) > 0) {
                    foreach (array_keys($view_array[$week_key][$timeKey]) as $eventKey) {
                        $event = &$eventArray[$view_array[$week_key][$timeKey][$eventKey]];
                        $eventStart = $event->getStart();
                        $startFormatted = $eventStart->format('YmdHM');
                        $eventType = $event->getType();
                        $eventUid = $event->getUid();
                        if (is_array($pos_array[$week_key]) && array_key_exists(
                            $eventType . $eventUid . '_' . $startFormatted,
                            $pos_array[$week_key]
                        )) {
                            $nd->copy($event->getEnd());
                            $nd->addSeconds(($gridLength - ($nd->getMinute() % $gridLength)) * 60);
                            if ($nd->before($i)) {
                                $t_array[$week_key][$timeKey][$pos_array[$week_key][$eventType . $eventUid . '_' . $startFormatted]] = [
                                    'ended' => $week_key . '_' . $eventType . '_' . $eventUid . '_' . $startFormatted
                                ];
                            } else {
                                $t_array[$week_key][$timeKey][$pos_array[$week_key][$eventType . $eventUid . '_' . $startFormatted]] = [
                                    'started' => $week_key . '_' . $eventType . '_' . $eventUid . '_' . $startFormatted
                                ];
                            }
                        } else {
                            for ($j = 0; $j < $nbrGridCols[$week_key] ? $nbrGridCols[$week_key] : 1; $j++) {
                                if (!isset($t_array[$week_key][$timeKey][$j]) || count($t_array[$week_key][$timeKey][$j]) === 0) {
                                    $pos_array[$week_key][$event->getType() . $event->getUid() . '_' . $startFormatted] = $j;
                                    $t_array[$week_key][$timeKey][$j] = [
                                        'begin' => $week_key . '_' . $eventType . '_' . $eventUid . '_' . $startFormatted
                                    ];
                                    break;
                                }
                            }
                        }
                    }
                } else {
                    $t_array[$week_key][$timeKey] = '';
                }
            }
        }

        $thisdate = new CalendarDateTime();
        $thisdate->copy($week_start_day);

        for ($i = 0; $i < 7; $i++) {
            $weekarray[$i] = $thisdate->format('Ymd');
            $thisdate->addSeconds(86400);
        }

        $sims = [
            '###GETDATE###' => $getdate,
            '###DISPLAY_DATE###' => $display_date,
            '###LEGEND_PREV_DAY###' => $legend_prev_day_link,
            '###LEGEND_NEXT_DAY###' => $legend_next_day_link,
            '###SIDEBAR_DATE###' => ''
        ];

        // Replaces the allday events
        $alldays = $this->markerBasedTemplateService->getSubpart($weekTemplate, '###ALLDAYSOFWEEK##');

        $weekreplace = '';
        foreach ($weekarray as $get_date) {
            $replace = '';
            if (is_array($view_array[$get_date]['-1'])) {
                foreach ($view_array[$get_date]['-1'] as $id => $allday) {
                    $replace .= $eventArray[$allday]->renderEventForAllDay();
                }
            }
            $weekreplace .= Functions::substituteMarkerArrayNotCached($alldays, [
                '###COLSPAN###' => 'colspan="' . ($nbrGridCols[$get_date] ?: 1) . '"',
                '###ALLDAY###' => $replace
            ]);
        }

        $rems = [];
        $rems['###ALLDAYSOFWEEK###'] = $weekreplace;

        // Replaces the daysofweek
        $loop_dof = $this->markerBasedTemplateService->getSubpart($weekTemplate, '###DAYSOFWEEK###');

        $start_day = new CalendarDateTime();
        $start_day->copy($week_start_day);

        $isAllowedToCreateEvent = $this->rightsObj->isAllowedToCreateEvent();

        $weekday_loop = '';
        for ($i = 0; $i < 7; $i++) {
            $daylink = $start_day->format('Ymd');

            $weekday = $start_day->format($this->conf['view.']['week.']['dateFormatWeekList']);

            if ($daylink === $getdate) {
                $row1 = 'rowToday';
                $row2 = 'rowOn';
                $row3 = 'rowToday';
            } else {
                $row1 = 'rowOff';
                $row2 = 'rowOn';
                $row3 = 'rowOff';
            }

            $dayLinkViewTarget = &$this->conf['view.']['dayLinkTarget'];
            if (($this->rightsObj->isViewEnabled($dayLinkViewTarget) || $this->conf['view.'][$dayLinkViewTarget . '.'][$dayLinkViewTarget . 'ViewPid']) && ($view_array[$daylink] || $isAllowedToCreateEvent)) {
                $this->initLocalCObject();
                $this->local_cObj->setCurrentVal($weekday);
                if (!empty($this->conf['view.'][$dayLinkViewTarget . '.'][$dayLinkViewTarget . 'ViewPid'])) {
                    $this->controller->getParametersForTyposcriptLink(
                        $this->local_cObj->data,
                        [
                            'getdate' => $daylink,
                            'view' => $this->conf['view.']['dayLinkTarget'],
                            $this->pointerName => null
                        ],
                        $this->conf['cache'],
                        $this->conf['clear_anyway'],
                        $this->conf['view.'][$dayLinkViewTarget . '.'][$dayLinkViewTarget . 'ViewPid']
                    );
                } else {
                    $this->controller->getParametersForTyposcriptLink($this->local_cObj->data, [
                        'getdate' => $daylink,
                        'view' => $this->conf['view.']['dayLinkTarget'],
                        $this->pointerName => null
                    ], $this->conf['cache'], $this->conf['clear_anyway']);
                }
                $this->local_cObj->data['view'] = $dayLinkViewTarget;
                $link = $this->local_cObj->cObjGetSingle(
                    $this->conf['view.'][$dayLinkViewTarget . '.'][$dayLinkViewTarget . 'ViewLink'],
                    $this->conf['view.'][$dayLinkViewTarget . '.'][$dayLinkViewTarget . 'ViewLink.']
                );

                $link = $this->cObj->stdWrap($link, $this->conf['view.']['week.']['weekday_stdWrap.']);
            } else {
                $link = $this->cObj->stdWrap($weekday, $this->conf['view.']['week.']['weekday_stdWrap.']);
            }
            $start_day->addSeconds(86400);
            $colspan = 'colspan="' . ($nbrGridCols[$daylink] ?: 1) . '"';
            $search = [
                '###LINK###',
                '###DAYLINK###',
                '###ROW1###',
                '###ROW2###',
                '###ROW3###',
                '###COLSPAN###',
                '###TIME###'
            ];
            $replace = [
                $link,
                $daylink,
                $row1,
                $row2,
                $row3,
                $colspan,
                $start_day->format('Y m d H M s')
            ];
            $loop_tmp = str_replace($search, $replace, $loop_dof);
            $weekday_loop .= $loop_tmp;
        }

        $rems['###DAYSOFWEEK###'] = $weekday_loop;

        $dTimeStart[2] -= $dTimeStart[2] % $gridLength;
        $dTimeEnd[2] -= $dTimeEnd[2] % $gridLength;

        preg_match('/([0-9]{4})([0-9]{2})([0-9]{2})/', $week_key, $dDate);

        $loops = (($dTimeEnd[1] * 60 + $dTimeEnd[2]) - ($dTimeStart[1] * 60 + $dTimeStart[2])) / $gridLength;

        $weekdisplay = '';

        $createOffset = intval($this->conf['rights.']['create.']['event.']['timeOffset']) * 60;

        $cal_time_obj = new CalendarDateTime();
        $cal_time_obj->copy($week_start_day);
        $cal_time_obj->setHour(intval($dTimeStart[1]));
        $cal_time_obj->setMinute(intval($dTimeStart[2]));

        $start = 0;

        for ($i = $start; $i < $loops; $i++) {
            $time = $cal_time_obj->format('HM');
            for ($j = 0; $j < 7; $j++) {
                $day = $cal_time_obj->format('Ymd');
                if ($j === 0) {
                    $key = $cal_time_obj->format('I:M');
                    if (preg_match('/([0-9]{1,2}):00/', $key)) {
                        $weekdisplay .= sprintf(
                            $this->conf['view.']['week.']['weekDisplayFullHour'],
                            60 / $gridLength,
                            $cal_time_obj->format($this->conf['view.']['week.']['timeFormatWeek']),
                            $gridLength
                        );
                    } else {
                        $weekdisplay .= sprintf($this->conf['view.']['week.']['weekDisplayInbetween'], $gridLength);
                    }
                }
                $something = $t_array[$day][$time];

                $class = $this->conf['view.']['week.']['classWeekborder'];
                if ($day === $todayFormatted) {
                    $class .= ' ' . $this->conf['view.']['week.']['classTodayWeekborder'];
                }
                if (is_array($something) && $something !== '' && count($something) > 0) {
                    foreach ($something as $k => $kValue) {
                        if (!empty($something[$k])) {
                            $keys = array_keys($kValue);
                            if ($keys[0] === 'begin') {
                                $event = &$eventArray[$something[$k][$keys[0]]];

                                $weekdisplay .= sprintf(
                                    $this->conf['view.']['week.']['weekEventPre'],
                                    $rowspan_array[$day][$event->getType() . '_' . $event->getUid() . '_' . $event->getStart()->format('YmdHM')]
                                );
                                $weekdisplay .= $event->renderEventForWeek();
                                $weekdisplay .= $this->conf['view.']['week.']['weekEventPost'];
                                // End event drawing
                            }
                        }
                    }
                    if (count($something) < ($nbrGridCols[$day] ?: 1)) {
                        $remember = 0;
                        for ($l = 0; $l < ($nbrGridCols[$day] ?: 1); $l++) {
                            if (!$something[$l]) {
                                $remember++;
                            } elseif ($remember > 0) {
                                $weekdisplay .= $this->getCreateEventLink(
                                    'week',
                                    $this->conf['view.']['week.']['normalCell'],
                                    $cal_time_obj,
                                    $createOffset,
                                    $isAllowedToCreateEvent,
                                    $remember,
                                    $class,
                                    $time
                                );
                                $remember = 0;
                            }
                        }
                        if ($remember > 0) {
                            $weekdisplay .= $this->getCreateEventLink(
                                'week',
                                $this->conf['view.']['week.']['normalCell'],
                                $cal_time_obj,
                                $createOffset,
                                $isAllowedToCreateEvent,
                                $remember,
                                $class,
                                $time
                            );
                        }
                    }
                } else {
                    $weekdisplay .= $this->getCreateEventLink(
                        'week',
                        $this->conf['view.']['week.']['normalCell'],
                        $cal_time_obj,
                        $createOffset,
                        $isAllowedToCreateEvent,
                        $nbrGridCols[$day] ?: 1,
                        $class,
                        $time
                    );
                }

                if ($j === 6) {
                    $weekdisplay .= $this->conf['view.']['week.']['weekFinishRow'];
                }
                $cal_time_obj->addSeconds(86400);
            }
            $cal_time_obj->setYear($week_start_day->getYear());
            $cal_time_obj->setMonth($week_start_day->getMonth());
            $cal_time_obj->setDay($week_start_day->getDay());
            $cal_time_obj->addSeconds($gridLength * 60);
        }
        $weekTemplate = Functions::substituteMarkerArrayNotCached($weekTemplate, $sims, [], []);
        $rems['###LOOPEVENTS###'] = $weekdisplay;
        $page = Functions::substituteMarkerArrayNotCached($page, [], [
            '###WEEK_TEMPLATE###' => $weekTemplate
        ], []);
        return $this->finish($page, $rems);
    }
}
