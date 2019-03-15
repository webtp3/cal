<?php

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
use TYPO3\CMS\Cal\Controller\Calendar;
use TYPO3\CMS\Cal\Model\CalDate;
use TYPO3\CMS\Cal\Model\EventModel;
use TYPO3\CMS\Cal\Model\Pear\Date\Calc;
use TYPO3\CMS\Cal\Utility\Functions;

/**
 * A concrete view for the calendar.
 * It is based on the phpicalendar project
 */
class DayView extends BaseView
{
    /**
     * @param $master_array
     * @param $getdate
     * @return mixed
     */
    public function newDrawDay(&$master_array, $getdate)
    {
        if (!isset($getdate) || $getdate === '') {
            $getdate = new  CalDate();
        } else {
            $getdate = new  CalDate($getdate);
        }

        $dayModel = new NewDayView($getdate->getDay(), $getdate->getMonth(), $getdate->getYear());
        $today = new  CalDate();
        $dayModel->setCurrent($today);
        $dayModel->setSelected($getdate);

        $dayModel->setWeekDayFormat($this->conf['view.']['day.']['dateFormatDay']);
        $weekdayLength = intval($this->conf['view.']['day.']['weekdayLength']);
        if ($weekdayLength > 0) {
            $dayModel->setWeekDayFormat($weekdayLength);
        }

        $masterArrayKeys = array_keys($master_array);
        foreach ($masterArrayKeys as $dateKey) {
            $dateArray = &$master_array[$dateKey];
            $dateArrayKeys = array_keys($dateArray);
            foreach ($dateArrayKeys as $timeKey) {
                $arrayOfEvents = &$dateArray[$timeKey];
                $eventKeys = array_keys($arrayOfEvents);
                foreach ($eventKeys as $eventKey) {
                    $dayModel->addEvent($arrayOfEvents[$eventKey]);
                }
            }
        }
        $subpart = Functions::getContent($this->conf['view.']['day.']['newDayTemplate']);
        $page = Functions::getContent($this->conf['view.']['day.']['dayTemplate']);
        $page = str_replace('###DAY###', $dayModel->render($subpart), $page);
        $rems = [];

        return $this->finish($page, $rems);
    }

    /**
     * Draws the day view
     *
     * @param $master_array array to be drawn.
     * @param $getdate integer of the event
     * @return string HTML output.
     */
    public function drawDay(&$master_array, $getdate): string
    {
        $this->_init($master_array);
        if ($this->conf['useNewTemplatesAndRendering']) {
            return $this->newDrawDay($master_array, $getdate);
        }

        $page = Functions::getContent($this->conf['view.']['day.']['dayTemplate']);
        if ($page === '') {
            return '<h3>day: no template file found:</h3>' . $this->conf['view.']['day.']['dayTemplate'] . "<br />Please check your template record and add both cal items at 'include static (from extension)'";
        }

        $dayTemplate = $this->markerBasedTemplateService->getSubpart($page, '###DAY_TEMPLATE###');
        if ($dayTemplate === '') {
            $rems = [];
            return $this->finish($page, $rems);
        }

        $dayStart = $this->conf['view.']['day.']['dayStart']; // '0700'; // Start time for day grid
        $dayEnd = $this->conf['view.']['day.']['dayEnd']; // '2300'; // End time for day grid
        $gridLength = $this->conf['view.']['day.']['gridLength']; // '15'; // Grid distance in minutes for day view, multiples of 15 preferred

        if (!isset($getdate) || $getdate === '') {
            $getdate_obj = new  CalDate();
            $getdate = $getdate_obj->format('Ymd');
        }

        $day_array2 = [];
        preg_match('/([0-9]{4})([0-9]{2})([0-9]{2})/', $getdate, $day_array2);

        list($this_year, $this_month, $this_day) = $day_array2;

        $unix_time = new  CalDate($getdate . '000000');

        $this->initLocalCObject();

        $this->local_cObj->setCurrentVal($this->conf['view.']['day.']['legendNextDayLink']);
        $legend_next_day_link = $this->local_cObj->cObjGetSingle(
            $this->conf['view.']['day.']['legendNextDayLink'],
            $this->conf['view.']['day.']['legendNextDayLink.']
        );

        $this->local_cObj->setCurrentVal($this->conf['view.']['day.']['legendPrevDayLink']);
        $legend_prev_day_link = $this->local_cObj->cObjGetSingle(
            $this->conf['view.']['day.']['legendPrevDayLink'],
            $this->conf['view.']['day.']['legendPrevDayLink.']
        );

        $next_month_obj = new  CalDate();
        $next_month_obj->copy($unix_time);
        $next_month_obj->addSeconds(604800);
        $prev_month_obj = new  CalDate();
        $prev_month_obj->copy($unix_time);
        $prev_month_obj->subtractSeconds(604801);

        $dateOfWeek = Calc::beginOfWeek($this_day, $this_month, $this_year);
        $week_start_day = new  CalDate($dateOfWeek . '000000');

        $start_day = $unix_time;
        $start_week_time = $start_day;

        $end_week_time = new  CalDate();
        $end_week_time->copy($start_week_time);
        $end_week_time->addSeconds(604799);

        // Nasty fix to work with TS strftime
        $start_day_time = new  CalDate($getdate . '000000');
        $start_day_time->setTZbyID('UTC');
        $end_day_time = Calendar::calculateEndDayTime($start_day_time);

        $GLOBALS['TSFE']->register['cal_day_starttime'] = $start_day_time->getTime();
        $GLOBALS['TSFE']->register['cal_day_endtime'] = $end_day_time->getTime();

        $display_date = $this->cObj->cObjGetSingle(
            $this->conf['view.']['day.']['titleWrap'],
            $this->conf['view.']['day.']['titleWrap.'],
            $TSkey = '__'
        );

        $dayTemplate = Functions::getContent($this->conf['view.']['day.']['dayTemplate']);
        if ($dayTemplate === '') {
            return '<h3>calendar: no template file found:</h3>' . $this->conf['view.']['day.']['dayTemplate'] . '<br />Please check your template record and add both cal items at "include static (from extension)"';
        }

        $dayTemplate = $this->replace_files($dayTemplate, [
            'sidebar' => $this->conf['view.']['other.']['sidebarTemplate']
        ]);

        $sims = [
            '###GETDATE###' => $getdate,
            '###DISPLAY_DATE###' => $display_date,
            '###LEGEND_PREV_DAY###' => $legend_prev_day_link,
            '###LEGEND_NEXT_DAY###' => $legend_next_day_link,
            '###SIDEBAR_DATE###' => ''
        ];

        // Replaces the daysofweek
        $loop_dof = $this->markerBasedTemplateService->getSubpart($dayTemplate, '###DAYSOFWEEK###');

        $fillTime = sprintf('%04d', $dayStart);
        $day_array = [];

        while ($fillTime < $dayEnd) {
            $day_array[] = $fillTime;
            $dTime = [];
            preg_match('/([0-9]{2})([0-9]{2})/', $fillTime, $dTime);
            list($fill_h, $fill_min) = $dTime;
            $fill_min = sprintf('%02d', $fill_min + $gridLength);
            if ((int)$fill_min === 60) {
                $fill_h = sprintf('%02d', $fill_h + 1);
                $fill_min = '00';
            }
            $fillTime = $fill_h . $fill_min;
        }
        $nbrGridCols = [];

        $dayborder = 0;

        $view_array = [];
        $rowspan_array = [];
        $eventArray = [];

        $endOfDay = new  CalDate();
        $startOfDay = new  CalDate();

        if (!empty($this->master_array)) {
            foreach ($this->master_array as $ovlKey => $ovlValue) {
                $dTimeStart = [];
                $dTimeEnd = [];
                $dDate = [];
                preg_match('/([0-9]{2})([0-9]{2})/', $dayStart, $dTimeStart);
                preg_match('/([0-9]{2})([0-9]{2})/', $dayEnd, $dTimeEnd);
                preg_match('/([0-9]{4})([0-9]{2})([0-9]{2})/', $ovlKey, $dDate);

                $d_start = new  CalDate($dDate[1] . $dDate[2] . $dDate[3] . ' ' . $dTimeStart[1] . ':' . $dTimeStart[2] . ':00');
                $d_start->setTZbyID('UTC');
                $d_end = new  CalDate($dDate[1] . $dDate[2] . $dDate[3] . ' ' . $dTimeEnd[1] . ':' . $dTimeEnd[2] . ':00');
                $d_end->setTZbyID('UTC');

                foreach ($ovlValue as $ovl_time_key => $ovl_time_Value) {
                    /** @var EventModel $event */
                    foreach ($ovl_time_Value as $event) {
                        $eventStart = $event->getStart();
                        $eventArray[$event->getType() . '_' . $event->getUid() . '_' . $eventStart->format('YmdHM')] = $event;
                        $starttime = new  CalDate();
                        $endtime = new  CalDate();
                        $j = new  CalDate();
                        if ($ovl_time_key === '-1') {
                            $starttime->copy($event->getStart());
                            $endtime->copy($event->getEnd());
                            $endtime->addSeconds(1);

                            for ($j->copy($starttime); $j->before($endtime) && $j->before($end_week_time); $j->addSeconds(86400)) {
                                $view_array[$j->format('Ymd')]['-1'][] = $event->getType() . '_' . $event->getUid() . '_' . $eventStart->format('YmdHM');
                            }
                        } else {
                            $starttime->copy($event->getStart());
                            $starttime->subtractSeconds(($starttime->getMinute() % $gridLength) * 60);

                            $endtime->copy($event->getEnd());
                            $endtime->subtractSeconds(($endtime->getMinute() % $gridLength) * 60);

                            $entries = 1;
                            $old_day = new  CalDate($ovlKey . '000000');
                            $old_day->setTZbyID('UTC');
                            $endOfDay->copy($d_end);
                            $startOfDay->copy($d_start);

                            // $d_start -= $gridLength * 60;
                            foreach ($view_array[$starttime->format('Ymd')][$starttime->format('HM')] as $k => $kValue) {
                                if (empty($view_array[$starttime->format('Ymd')][$starttime->format('HM')][$k])) {
                                    break;
                                }
                            }
                            $j->copy($starttime);
                            if ($j->before($startOfDay)) {
                                $j->copy($startOfDay);
                            }
                            while ($j->before($endtime) && $j->before($end_week_time)) {
                                if ($j->after($endOfDay)) {
                                    $rowspan_array[$old_day->format('Ymd')][$event->getType() . '_' . $event->getUid() . '_' . $eventStart->format('YmdHM')] = $entries - 1;

                                    $endOfDay->addSeconds(60 * 60 * 24);
                                    $old_day->copy($endOfDay);
                                    $startOfDay->addSeconds(60 * 60 * 24);
                                    $j->copy($startOfDay);
                                    $entries = 0;
                                    foreach ($view_array[$d_start->format('Ymd')][$startOfDay->format('HM')] as $k => $kValue) {
                                        if (empty($view_array[$d_start->format('Ymd')][$startOfDay->format('HM')][$k])) {
                                            break;
                                        }
                                    }
                                } else {
                                    $view_array[$j->format('Ymd')][$j->format('HM')][] = $event->getType() . '_' . $event->getUid() . '_' . $eventStart->format('YmdHM');
                                    $j->addSeconds($gridLength * 60);
                                }
                                $entries++;
                            }
                            $rowspan_array[$old_day->format('Ymd')][$event->getType() . '_' . $event->getUid() . '_' . $eventStart->format('YmdHM')] = $entries - 1;
                        }
                    }
                }
            }
        }

        if ((int)$this->conf['view.']['day.']['dynamic'] === 1) {
            $dayStart = '2359';
            $dayEnd = '0000';
            if (is_array($view_array[$getdate])) {
                $timeKeys = array_keys($view_array[$getdate]);
                $formatedLast = array_pop($timeKeys);
                $formatedFirst = $formatedLast;
                foreach ($timeKeys as $timeKey) {
                    if ($timeKey > 0) {
                        $formatedFirst = $timeKey;
                        break;
                    }
                }
                if (intval($formatedFirst) > 0 && intval($formatedFirst) < intval($dayStart)) {
                    $dayStart = sprintf('%04d', $formatedFirst);
                }
                if (intval($formatedLast) > intval($dayEnd)) {
                    $dayEnd = sprintf('%04d', $formatedLast + $gridLength);
                }
            }
            $dayStart = substr($dayStart, 0, 2) . '00';
        }
        if (!empty($view_array[$getdate])) {
            $max = [];
            foreach ($view_array[$getdate] as $array_time => $time_val) {
                $c = count($view_array[$getdate][$array_time]);
                $max[] = $c;
            }
            $nbrGridCols[$getdate] = max($max);
        } else {
            $nbrGridCols[$getdate] = 1;
        }
        $weekday_loop = '';
        $isAllowedToCreateEvent = $this->rightsObj->isAllowedToCreateEvent();
        $start_day = $week_start_day;
        for ($i = 0; $i < 7; $i++) {
            $daylink = $start_day->format('Ymd');

            $weekday = $start_day->format($this->conf['view.']['day.']['dateFormatDay']);

            if ((int)$daylink === $getdate) {
                $row1 = 'rowToday';
                $row2 = 'rowOn';
                $row3 = 'rowToday';
            } else {
                $row1 = 'rowOff';
                $row2 = 'rowOn';
                $row3 = 'rowOff';
            }
            $dayLinkViewTarget = $this->conf['view.']['dayLinkTarget'];
            if (($view_array[$daylink] || $isAllowedToCreateEvent) && ($this->rightsObj->isViewEnabled($dayLinkViewTarget) || $this->conf['view.'][$dayLinkViewTarget . '.'][$dayLinkViewTarget . 'ViewPid'])) {
                $this->initLocalCObject();
                $this->local_cObj->setCurrentVal($weekday);
                $this->local_cObj->data['view'] = $dayLinkViewTarget;
                $this->controller->getParametersForTyposcriptLink(
                    $this->local_cObj->data,
                    [
                        'getdate' => $daylink,
                        'view' => $dayLinkViewTarget,
                        $this->pointerName => null
                    ],
                    $this->conf['cache'],
                    $this->conf['clear_anyway'],
                    $this->conf['view.'][$dayLinkViewTarget . '.'][$dayLinkViewTarget . 'ViewPid']
                );
                $link = $this->local_cObj->cObjGetSingle(
                    $this->conf['view.'][$dayLinkViewTarget . '.'][$dayLinkViewTarget . 'ViewLink'],
                    $this->conf['view.'][$dayLinkViewTarget . '.'][$dayLinkViewTarget . 'ViewLink.']
                );
            } else {
                $link = $weekday;
            }
            $start_day->addSeconds(86400);

            $search = [
                '###LINK###',
                '###DAYLINK###',
                '###ROW1###',
                '###ROW2###',
                '###ROW3###'
            ];
            $replace = [
                $link,
                $daylink,
                $row1,
                $row2,
                $row3
            ];
            $loop_tmp = str_replace($search, $replace, $loop_dof);
            $weekday_loop .= $loop_tmp;
        }
        $rems['###DAYSOFWEEK###'] = $weekday_loop;

        // Replaces the allday events
        $replace = '';
        if (is_array($view_array[$getdate]['-1'])) {
            foreach ($view_array[$getdate]['-1'] as $uid => $allday) {
                $replace .= $eventArray[$allday]->renderEventForAllDay();
            }
        }
        $sims['###ALLDAY###'] = $replace;

        $view_array = $view_array[$getdate];
        $nbrGridCols = $nbrGridCols[$getdate] ?: 1;
        $t_array = [];
        $pos_array = [];
        preg_match('/([0-9]{4})([0-9]{2})([0-9]{2})/', $getdate, $dDate);
        preg_match('/([0-9]{2})([0-9]{2})/', $dayStart, $dTimeStart);
        preg_match('/([0-9]{2})([0-9]{2})/', $dayEnd, $dTimeEnd);
        $dTimeStart[2] -= $dTimeStart[2] % $gridLength;
        $dTimeEnd[2] -= $dTimeEnd[2] % $gridLength;

        $d_start = new  CalDate($dDate[1] . $dDate[2] . $dDate[3] . ' ' . $dTimeStart[1] . ':' . sprintf(
            '%02d',
            $dTimeStart[2]
            ) . ':00');
        $d_start->setTZbyID('UTC');
        $d_end = new  CalDate($dDate[1] . $dDate[2] . $dDate[3] . ' ' . $dTimeEnd[1] . ':' . sprintf(
            '%02d',
            $dTimeEnd[2]
            ) . ':00');
        $d_end->setTZbyID('UTC');

        $i = new  CalDate();
        $i->copy($d_start);
        $i->setTZbyID('UTC');
        while ($i->before($d_end)) {
            $i_formatted = $i->format('HM');
            if (is_array($view_array[$i_formatted]) && count($view_array[$i_formatted]) > 0) {
                foreach ($view_array[$i_formatted] as $eventKey) {
                    $event = &$eventArray[$eventKey];
                    $eventStart = $event->getStart();
                    if (array_key_exists(
                        $event->getType() . $event->getUid() . '_' . $eventStart->format('YmdHM'),
                        $pos_array
                    )) {
                        $eventEnd = $event->getEnd();
                        $nd = $eventEnd->subtractSeconds(($eventEnd->getMinute() % $gridLength) * 60);
                        if ($i_formatted >= $nd) {
                            $t_array[$i_formatted][$pos_array[$event->getType() . $event->getUid() . '_' . $eventStart->format('YmdHM')]] = [
                                'ended' => $event->getType() . '_' . $event->getUid() . '_' . $eventStart->format('YmdHM')
                            ];
                        } else {
                            $t_array[$i_formatted][$pos_array[$event->getType() . $event->getUid() . '_' . $eventStart->format('YmdHM')]] = [
                                'started' => $event->getType() . '_' . $event->getUid() . '_' . $eventStart->format('YmdHM')
                            ];
                        }
                    } else {
                        foreach ($t_array[$i_formatted] as $j => $jValue) {
                            if (!isset($t_array[$i_formatted][$j]) || count($jValue) === 0) {
                                $pos_array[$event->getType() . $event->getUid() . '_' . $eventStart->format('YmdHM')] = $j;
                                $t_array[$i_formatted][$j] = [
                                    'begin' => $event->getType() . '_' . $event->getUid() . '_' . $eventStart->format('YmdHM')
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

        $createOffset = intval($this->conf['rights.']['create.']['event.']['timeOffset']) * 60;
        $daydisplay = '';
        $cal_time_obj = new  CalDate($getdate . '000000');
        $cal_time_obj->setTZbyID('UTC');
        foreach ($t_array as $cal_time => $val) {
            preg_match('/([0-9]{2})([0-9]{2})/', $cal_time, $dTimeStart);
            $cal_time_obj->setHour($dTimeStart[1]);
            $cal_time_obj->setMinute($dTimeStart[2]);

            $key = $cal_time_obj->format($this->conf['view.']['day.']['timeFormatDay']);
            if (intval($dTimeStart[2]) === 0) {
                $daydisplay .= sprintf(
                    $this->conf['view.']['day.']['dayTimeCell'],
                    60 / $gridLength,
                    $key,
                    $gridLength
                );
            } elseif ($cal_time_obj->equals($d_start)) {
                $size_tmp = 60 - (int)substr($cal_time, 2, 2);
                $daydisplay .= sprintf(
                    $this->conf['view.']['day.']['dayTimeCell'],
                    $size_tmp / $gridLength,
                    $key,
                    $gridLength
                );
            } else {
                $daydisplay .= sprintf($this->conf['view.']['day.']['dayTimeCell2'], $gridLength);
            }
            if ($dayborder === 0) {
                $class = ' ' . $this->conf['view.']['day.']['classDayborder'];
                $dayborder++;
            } else {
                $class = ' ' . $this->conf['view.']['day.']['classDayborder2'];
                $dayborder = 0;
            }

            if ($val !== '' && count($val) > 0) {
                foreach ($val as $i => $iValue) {
                    if (!empty($val[$i])) {
                        $keys = array_keys($iValue);
                        if ($keys[0] === 'begin') {
                            $event = &$eventArray[$val[$i][$keys[0]]];
                            $dayEndTime = new  CalDate();
                            $dayEndTime->copy($event->getEnd());
                            $dayStartTime = new  CalDate();
                            $dayStartTime->copy($event->getStart());

                            $colSpan = $rowspan_array[$getdate][$val[$i][$keys[0]]];

                            $daydisplay .= sprintf($this->conf['view.']['day.']['dayEventPre'], $colSpan);
                            $daydisplay .= $event->renderEventForDay();
                            $daydisplay .= $this->conf['view.']['day.']['dayEventPost'];
                            // End event drawing
                        }
                    }
                }
                if (count($val) < $nbrGridCols) {
                    $remember = 0;
                    // Render cells with events
                    foreach ($val as $lValue) {
                        if (!$lValue) {
                            $remember++;
                        } elseif ($remember > 0) {
                            $daydisplay .= $this->getCreateEventLink(
                                'day',
                                $this->conf['view.']['day.']['normalCell'],
                                $cal_time_obj,
                                $createOffset,
                                $isAllowedToCreateEvent,
                                $remember,
                                $class,
                                $cal_time
                            );
                            $remember = 0;
                        }
                    }
                    // Render cells next to events
                    if ($remember > 0) {
                        $daydisplay .= $this->getCreateEventLink(
                            'day',
                            $this->conf['view.']['day.']['normalCell'],
                            $cal_time_obj,
                            $createOffset,
                            $isAllowedToCreateEvent,
                            $remember,
                            $class,
                            $cal_time
                        );
                    }
                }
            } else {
                // Render cells without events
                $daydisplay .= $this->getCreateEventLink(
                    'day',
                    $this->conf['view.']['day.']['normalCell'],
                    $cal_time_obj,
                    $createOffset,
                    $isAllowedToCreateEvent,
                    $nbrGridCols,
                    $class,
                    $cal_time
                );
            }
            $daydisplay .= $this->conf['view.']['day.']['dayFinishRow'];
        }

        $dayTemplate = Functions::substituteMarkerArrayNotCached($dayTemplate, $sims, [], []);
        $rems['###DAYEVENTS###'] = $daydisplay;
        $page = Functions::substituteMarkerArrayNotCached($page, [], [
            '###DAY_TEMPLATE###' => $dayTemplate
        ], []);
        return $this->finish($page, $rems);
    }
}
