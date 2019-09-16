<?php

/*
 * This file is part of the web-tp3/cal.
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace TYPO3\CMS\Cal\View;

use TYPO3\CMS\Cal\Model\CalendarDateTime;
use TYPO3\CMS\Cal\Utility\Functions;
use TYPO3\CMS\Cal\Utility\Registry;
use TYPO3\CMS\Core\Charset\CharsetConverter;
use TYPO3\CMS\Core\Service\MarkerBasedTemplateService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

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
abstract class NewTimeView
{
    private $day;
    private $month;
    private $year;
    private $weekdayNumber;
    private $mySubpart;
    private $template;
    private $cs_convert;
    private $weekDayLength = 100;
    private $weekDayFormat = '%A';
    protected $current = false;
    protected $selected = false;
    private $parentMonth;

    /**
     * @var MarkerBasedTemplateService
     */
    protected $markerBasedTemplateService;

    public function __construct()
    {
        $this->markerBasedTemplateService = GeneralUtility::makeInstance(MarkerBasedTemplateService::class);
    }

    /**
     * @param $event
     * @return mixed
     */
    abstract public function addEvent(&$event);

    /**
     * @param $template
     * @return string
     */
    public function render(&$template): string
    {
        $this->setTemplate($template);
        $rems = [];
        $sims = [];
        $wrapped = [];

        $subpart = $this->markerBasedTemplateService->getSubpart($template, $this->getMySubpart());
        $this->getMarker($subpart, $sims, $rems, $wrapped);

        return $this->finish(Functions::substituteMarkerArrayNotCached(
            $subpart,
            $sims,
            $rems,
            $wrapped
        ));
    }

    /**
     * @param $template
     * @param $sims
     * @param $rems
     * @param $wrapped
     * @param string $view
     * @param string $base
     */
    protected function getMarker(& $template, & $sims, & $rems, & $wrapped, $view = '', $base = 'view')
    {
        $conf = &Registry::Registry('basic', 'conf');
        if ($view === '' && $base === 'view') {
            $view = !empty($conf['alternateRenderingView']) && is_array($conf[$base . '.'][$conf['alternateRenderingView'] . '.']) ? $conf['alternateRenderingView'] : $conf['view'];
        }
        preg_match_all('!\<\!--[a-zA-Z0-9 ]*###([A-Z0-9_-|]*)\###[a-zA-Z0-9 ]*-->!is', $template, $match);
        $allMarkers = array_unique($match[1]);

        foreach ($allMarkers as $marker) {
            if (preg_match('/MODULE__([A-Z0-9_-])*/', $marker)) {
                $module = GeneralUtility::makeInstanceService(
                    substr($marker, 8),
                    'module'
                );
                if (is_object($module)) {
                    $rems['###' . $marker . '###'] = $module->start($this);
                }
            }
            $funcFromMarker = 'get' . str_replace(
                ' ',
                '',
                ucwords(str_replace('_', ' ', strtolower($marker)))
            ) . 'Marker';

            if (method_exists($this, $funcFromMarker)) {
                $this->$funcFromMarker($template, $sims, $rems, $wrapped, $view);
            }
        }

        preg_match_all('!\###([A-Z0-9_-|]*)\###!is', $template, $match);
        $allSingleMarkers = array_unique($match[1]);
        $allSingleMarkers = array_diff($allSingleMarkers, $allMarkers);

        foreach ($allSingleMarkers as $marker) {
            switch ($marker) {
                case 'ACTIONURL':
                case 'L_ENTER_EMAIL':
                case 'L_CAPTCHA_TEXT':
                case 'CAPTCHA_SRC':
                case 'IMG_PATH':
                    // do nothing
                    break;
                default:
                    if (preg_match('/.*_LABEL$/', $marker) || preg_match('/^L_.*/', $marker)) {
                        continue 2;
                    }
                    $funcFromMarker = 'get' . str_replace(
                        ' ',
                        '',
                        ucwords(str_replace('_', ' ', strtolower($marker)))
                    ) . 'Marker';
                    if (method_exists($this, $funcFromMarker)) {
                        $this->$funcFromMarker($template, $sims, $rems, $wrapped, $view);
                    } elseif (preg_match('/MODULE__([A-Z0-9_-|])*/', $marker)) {
                        $tmp = explode('___', substr($marker, 8));
                        $modules[$tmp[0]][] = $tmp[1];
                    } elseif ($conf[$base . '.'][$view . '.'][strtolower($marker)]) {
                        $current = '';

                        // first, try to fill $current with a method of the model matching the markers name
                        $functionName = 'get' . str_replace(
                            ' ',
                            '',
                            ucwords(str_replace('_', ' ', strtolower($marker)))
                        );
                        if (method_exists($this, $functionName)) {
                            $tmp = $this->$functionName();
                            if (!is_object($tmp) && !is_array($tmp)) {
                                $current = $tmp;
                            }
                            unset($tmp);
                        }
                        $cObj = $this->getLocalCObject();
                        $cObj->setCurrentVal($current);
                        $sims['###' . $marker . '###'] = $cObj->cObjGetSingle(
                            $conf[$base . '.'][$view . '.'][strtolower($marker)],
                            $conf[$base . '.'][$view . '.'][strtolower($marker) . '.']
                        );
                    }
                    break;
            }
        }

        // se alternativ way of MODULE__MARKER
        // yntax: ###MODULE__MODULENAME___MODULEMARKER###
        // ollect them, call each Modul, retrieve Array of Markers and replace them
        // his allows to spread the Module-Markers over complete template instead of one time
        // lso work with old way of MODULE__-Marker

        if (is_array($modules)) { // ODULE-MARKER FOUND
            foreach ($modules as $themodule => $markerArray) {
                $module = GeneralUtility::makeInstanceService($themodule, 'module');
                if (is_object($module)) {
                    if ($markerArray[0] === '') {
                        $sims['###MODULE__' . $themodule . '###'] = $module->start($this); // ld way
                    } else {
                        $moduleMarker = $module->start($this); // get Markerarray from Module
                        foreach ($moduleMarker as $key => $val) {
                            $sims['###MODULE__' . $themodule . '___' . $key . '###'] = $val;
                        }
                    }
                }
            }
        }

        $hookObjectsArr = Functions::getHookObjectsArray(
            'tx_cal_base_model',
            'searchForObjectMarker',
            'model'
        );
        // Hook: postSearchForObjectMarker
        foreach ($hookObjectsArr as $hookObj) {
            if (method_exists($hookObj, 'postSearchForObjectMarker')) {
                $hookObj->postSearchForObjectMarker($this, $template, $sims, $rems, $wrapped, $view);
            }
        }
    }

    /**
     * Method for post processing the rendered event
     *
     * @param string $content
     * @return string $content content/output
     */
    protected function finish(&$content): string
    {
        $hookObjectsArr = Functions::getHookObjectsArray(
            'tx_cal_base_model',
            'finishModelRendering',
            'model'
        );
        // Hook: preFinishModelRendering
        foreach ($hookObjectsArr as $hookObj) {
            if (method_exists($hookObj, 'preFinishModelRendering')) {
                $hookObj->preFinishModelRendering($this, $content);
            }
        }

        // translate output
        $this->translateLanguageMarker($content);

        // Hook: postFinishModelRendering
        foreach ($hookObjectsArr as $hookObj) {
            if (method_exists($hookObj, 'postFinishModelRendering')) {
                $hookObj->postFinishModelRendering($this, $content);
            }
        }
        return $content;
    }

    /**
     * @param $content
     * @return mixed
     */
    protected function translateLanguageMarker(&$content)
    {
        // translate leftover markers
        preg_match_all('!(###|%%%)([A-Z0-9_-|]*)\_LABEL\1!is', $content, $match);
        $allLanguageMarkers = array_unique($match[2]);

        if (count($allLanguageMarkers)) {
            $controller = &Registry::Registry('basic', 'controller');
            $sims = [];
            foreach ($allLanguageMarkers as $key => $marker) {
                $wrapper = $match[1][$key];
                $label = $controller->pi_getLL('l_' . strtolower($marker));
                if ($label === '') {
                    $label = $controller->pi_getLL('l_event_' . strtolower($marker));
                }
                $sims[$wrapper . $marker . '_LABEL' . $wrapper] = $label;
            }
            if (count($sims)) {
                $content = Functions::substituteMarkerArrayNotCached($content, $sims, [], []);
            }
        }
        return $content;
    }

    /**
     * Method to initialise a local content object, that can be used for customized TS rendering with own db values
     *
     * @param bool $customData array key => value pairs that should be used as fake db-values for TS rendering instead of the values of the current object
     * @return mixed
     */
    public function getLocalCObject($customData = false)
    {
        $local_cObj = &Registry::Registry('basic', 'local_cObj');
        if ($customData && is_array($customData)) {
            $local_cObj->data = $customData;
        } else {
            $values = [];
            $values['day'] = $this->day;
            $values['month'] = $this->month;
            $values['year'] = $this->year;
            $values['ymd'] = sprintf('%04d', $this->year) . sprintf('%02d', $this->month) . sprintf('%02d', $this->day);
            $values['weekdayNumber'] = $this->weekdayNumber;
            $values['hasEvents'] = $this->hasEvents();
            $values['parentMonth'] = $this->getParentMonth();
            $local_cObj->data = $values;
        }
        return $local_cObj;
    }

    /**
     * @param $template
     * @param $sims
     * @param $rems
     * @param $wrapped
     * @param $view
     */
    public function getMondayMarker(& $template, & $sims, & $rems, & $wrapped, $view)
    {
        $sims['###MONDAY###'] = $this->getWeekdayString(strtotime('2001-01-01'));
    }

    /**
     * @param $template
     * @param $sims
     * @param $rems
     * @param $wrapped
     * @param $view
     */
    public function getTuesdayMarker(& $template, & $sims, & $rems, & $wrapped, $view)
    {
        $sims['###TUESDAY###'] = $this->getWeekdayString(strtotime('2001-01-02'));
    }

    /**
     * @param $template
     * @param $sims
     * @param $rems
     * @param $wrapped
     * @param $view
     */
    public function getWednesdayMarker(& $template, & $sims, & $rems, & $wrapped, $view)
    {
        $sims['###WEDNESDAY###'] = $this->getWeekdayString(strtotime('2001-01-03'));
    }

    /**
     * @param $template
     * @param $sims
     * @param $rems
     * @param $wrapped
     * @param $view
     */
    public function getThursdayMarker(& $template, & $sims, & $rems, & $wrapped, $view)
    {
        $sims['###THURSDAY###'] = $this->getWeekdayString(strtotime('2001-01-04'));
    }

    /**
     * @param $template
     * @param $sims
     * @param $rems
     * @param $wrapped
     * @param $view
     */
    public function getFridayMarker(& $template, & $sims, & $rems, & $wrapped, $view)
    {
        $sims['###FRIDAY###'] = $this->getWeekdayString(strtotime('2001-01-05'));
    }

    /**
     * @param $template
     * @param $sims
     * @param $rems
     * @param $wrapped
     * @param $view
     */
    public function getSaturdayMarker(& $template, & $sims, & $rems, & $wrapped, $view)
    {
        $sims['###SATURDAY###'] = $this->getWeekdayString(strtotime('2001-01-06'));
    }

    /**
     * @param $template
     * @param $sims
     * @param $rems
     * @param $wrapped
     * @param $view
     */
    public function getSundayMarker(& $template, & $sims, & $rems, & $wrapped, $view)
    {
        $sims['###SUNDAY###'] = $this->getWeekdayString(strtotime('2001-01-07'));
    }

    /**
     * @param $timestamp
     * @return mixed
     */
    public function getWeekdayString($timestamp)
    {
        if (!is_object($this->cs_convert)) {
            $this->cs_convert = new CharsetConverter();
        }
        return mb_substr(
            strftime($this->getWeekDayFormat(), $timestamp),
            0,
            $this->getWeekDayLength()
        );
    }

    /**
     * @param $view
     * @param $wrap
     * @param $date
     * @param $createOffset
     * @param $isAllowedToCreateEvent
     * @param $remember
     * @param $class
     * @param $time
     * @return string
     */
    public function getCreateEventLink(
        $view,
        $wrap,
        $date,
        $createOffset,
        $isAllowedToCreateEvent,
        $remember,
        $class,
        $time
    ): string {
        $tmp = '';

        $rightsObj = &Registry::Registry('basic', 'rightscontroller');
        $conf = &Registry::Registry('basic', 'conf');
        $controller = &Registry::Registry('basic', 'controller');

        if (!$rightsObj->isViewEnabled('create_event')) {
            if ($conf['view.']['enableAjax']) {
                return sprintf($wrap, $remember, $class, '');
            }
            return sprintf($wrap, $remember, $class, '');
        }
        $now = new CalendarDateTime();
        $now->setTZbyID('UTC');
        $now->addSeconds($createOffset);

        $date = new CalendarDateTime();
        $date->setDay($this->day);
        $date->setMonth($this->month);
        $date->setYear($this->year);

        if ($isAllowedToCreateEvent && ($date->after($now) || $rightsObj->isAllowedToCreateEventInPast())) {
            $local_cObj = $this->getLocalCObject();
            $conf['clear_anyway'] = 1;
            if ($conf['view.']['enableAjax']) {
                $local_cObj->setCurrentVal($conf['view.'][$view . '.']['event.']['addIcon']);
                $local_cObj->data['link_ATagParams'] = sprintf(
                    ' onclick="' . $conf['view.'][$view . '.']['event.']['addLinkOnClick'] . '"',
                    $time,
                    $date->format('Ymd')
                );
                $controller->getParametersForTyposcriptLink($local_cObj->data, [
                    'gettime' => $time,
                    'getdate' => $date->format('Ymd'),
                    'view' => 'create_event'
                ], 0, $conf['clear_anyway'], $conf['view.']['event.']['createEventViewPid']);
                $tmp .= $local_cObj->cObjGetSingle(
                    $conf['view.'][$view . '.']['event.']['addLink'],
                    $conf['view.'][$view . '.']['event.']['addLink.']
                );
                if ($wrap) {
                    $tmp = sprintf(
                        $wrap,
                        'id="cell_' . $date->format('Ymd') . $time . '" ondblclick="javascript:eventUid=0;eventTime=\'' . $time . '\';eventDate=' . $date->format('Ymd') . ';EventDialog.showDialog(this);" ',
                        $remember,
                        $class,
                        $tmp,
                        $date->format('Y m d H M s')
                    );
                }
            } else {
                $local_cObj->setCurrentVal($conf['view.'][$view . '.']['event.']['addIcon']);
                $local_cObj->data['link_no_cache'] = 1;
                $local_cObj->data['link_additionalParams'] = '&tx_cal_controller[gettime]=' . $time . '&tx_cal_controller[getdate]=' . $date->format('Ymd') . '&tx_cal_controller[lastview]=' . $controller->extendLastView() . '&tx_cal_controller[view]=create_event';
                $local_cObj->data['link_section'] = 'default';
                $local_cObj->data['link_parameter'] = $conf['view.']['event.']['createEventViewPid'] ?: $GLOBALS['TSFE']->id;
                $tmp .= $local_cObj->cObjGetSingle(
                    $conf['view.'][$view . '.']['event.']['addLink'],
                    $conf['view.'][$view . '.']['event.']['addLink.']
                );
                if ($wrap) {
                    $tmp = sprintf($wrap, $remember, $class, $tmp, $date->format('Y m d H M s'));
                }
            }
        } elseif ($conf['view.']['enableAjax']) {
            $tmp = sprintf($wrap, $remember, $class, '');
        } else {
            $tmp = sprintf($wrap, $remember, $class, '');
        }
        return $tmp;
    }

    /**
     * @param $template
     * @param $sims
     * @param $rems
     * @param $wrapped
     * @param $view
     */
    public function getTimetableHeightMarker(& $template, & $sims, & $rems, & $wrapped, $view)
    {
        $conf = &Registry::Registry('basic', 'conf');
        $gridLength = $conf['view.']['day.']['gridLength'];
        $dayStart = $conf['view.']['day.']['dayStart']; // '0700'; // Start time for day grid
        $dayEnd = $conf['view.']['day.']['dayEnd']; // '2300'; // End time for day grid

        while (strlen($dayStart) < 6) {
            $dayStart .= '0';
        }
        while (strlen($dayEnd) < 6) {
            $dayEnd .= '0';
        }

        $d_start = new CalendarDateTime('20000101' . $dayStart);
        $d_end = new CalendarDateTime('20000101' . $dayEnd);

        $sims['###TIMETABLE_HEIGHT###'] = (($d_end->getHour() * 3600 + $d_end->getMinute() * 60) - ($d_start->getHour() * 3600 + $d_start->getMinute() * 60)) / $gridLength * 0.35;
    }

    /**
     * @param $template
     * @param $sims
     * @param $rems
     * @param $wrapped
     * @param $view
     */
    public function getTimeCellsMarker(& $template, & $sims, & $rems, & $wrapped, $view)
    {
        $rightsObj = &Registry::Registry('basic', 'rightscontroller');
        $timesSubpart = $this->markerBasedTemplateService->getSubpart($template, 'TIME_CELLS');

        $conf = &Registry::Registry('basic', 'conf');

        $gridTime = new CalendarDateTime();
        $gridTime->setTZbyID('UTC');
        $gridTime->setMinute(0);

        $times = '';

        $dayStart = $conf['view.']['day.']['dayStart']; // '0700'; // Start time for day grid
        $dayEnd = $conf['view.']['day.']['dayEnd']; // '2300'; // End time for day grid

        while (strlen($dayStart) < 6) {
            $dayStart .= '0';
        }
        while (strlen($dayEnd) < 6) {
            $dayEnd .= '0';
        }

        if ($conf['view'] === 'day') {
            $d_start = new CalendarDateTime($this->getYmd() . $dayStart);
            $d_end = new CalendarDateTime($this->getYmd() . $dayEnd);
        }
        if ($conf['view'] === 'week') {
            $d_start = new CalendarDateTime($this->weekStart . $dayStart);
            $d_end = new CalendarDateTime($this->weekStart . $dayEnd);
        }
        $d_start->setTZbyID('UTC');
        $d_end->setTZbyID('UTC');

        $createOffset = 0;
        $isAllowedToCreateEvent = $rightsObj->isAllowedToCreateEvent();
        if ($isAllowedToCreateEvent) {
            $createOffset = intval($conf['rights.']['create.']['event.']['timeOffset']) * 60;
        }

        while ($d_end->after($d_start)) {
            if ($isAllowedToCreateEvent) {
                $createLink = $this->getCreateEventLink(
                    $view,
                    '',
                    $d_start,
                    $createOffset,
                    true,
                    '',
                    '',
                    $d_start->format('HM')
                );
            } else {
                $createLink = '';
            }
            $times .= str_replace([
                '###TIME###',
                '###VALUE###',
                '###CREATE_EVENT_LINK###'
            ], [
                $d_start->format($conf['view.'][$conf['view'] . '.']['timeFormatDay']),
                $d_start->getHour(),
                $createLink
            ], $timesSubpart);
            $d_start->addSeconds(3600);
        }
        $rems['###TIME_CELLS###'] = $times;
    }

    /**
     * @param $template
     * @param $sims
     * @param $rems
     * @param $wrapped
     * @param $view
     */
    public function getHourCellsMarker(& $template, & $sims, & $rems, & $wrapped, $view)
    {
        $hoursSubpart = $this->markerBasedTemplateService->getSubpart($template, 'HOUR_CELLS');

        $conf = &Registry::Registry('basic', 'conf');

        $dayStart = $conf['view.']['day.']['dayStart']; // '0700'; // Start time for day grid
        $dayEnd = $conf['view.']['day.']['dayEnd']; // '2300'; // End time for day grid

        while (strlen($dayStart) < 6) {
            $dayStart .= '0';
        }
        while (strlen($dayEnd) < 6) {
            $dayEnd .= '0';
        }

        $count = intval($dayEnd - $dayStart) / 10000;
        $hours = '';
        for ($i = 0; $i < $count; $i++) {
            $hours .= $hoursSubpart;
        }
        $rems['###HOUR_CELLS###'] = $hours;
    }

    abstract public function hasEvents();

    /**
     * @param $dateObject
     * @return mixed
     */
    abstract public function setCurrent(&$dateObject);

    /**
     * @param $dateObject
     * @return mixed
     */
    abstract public function setSelected(&$dateObject);

    /**
     * @param $template
     * @param $sims
     * @param $rems
     * @param $wrapped
     * @param $view
     */
    public function getCreateEventLinkMarker(& $template, & $sims, & $rems, & $wrapped, $view)
    {
        $sims['###CREATE_EVENT_LINK###'] = '';
        $rightsObj = &Registry::Registry('basic', 'rightscontroller');
        $conf = &Registry::Registry('basic', 'conf');
        $controller = &Registry::Registry('basic', 'controller');
        if (!$rightsObj->isViewEnabled('create_event')) {
            return;
        }
        $than = new CalendarDateTime();
        $than->setTZbyID('UTC');
        $than->addSeconds();

        $date = new CalendarDateTime();
        $date->setDay($this->day);
        $date->setMonth($this->month);
        $date->setYear($this->year);
        $date->setHour($this->hour);
        $date->setMinute($this->minute);
        if ($rightsObj->isAllowedToCreateEventForTodayAndFuture()) {
            $date->setHour(23);
            $date->setMinute(59);
        }

        if ($rightsObj->isAllowedToCreateEvent() && ($date->after($than) || $rightsObj->isAllowedToCreateEventInPast())) {
            $local_cObj = $this->getLocalCObject();
            $timeParams = '';
            if ($view === 'day' || $view === 'week') {
                $timeParams = '&tx_cal_controller[gettime]=' . $date->format('HM');
            } elseif ($conf['rights.']['create.']['event.']['fields.']['starttime.']['default'] === 'now') {
                $now = new CalendarDateTime();
                $than->setTZbyID('UTC');
                $timeParams = '&tx_cal_controller[gettime]=' . $now->format('HM');
            }

            $local_cObj->setCurrentVal($conf['view.'][$view . '.']['event.']['addIcon']);
            $local_cObj->data['link_no_cache'] = 1;
            $local_cObj->data['link_additionalParams'] = $timeParams . '&tx_cal_controller[startdate]=' . $date->format('Ymd') . '&tx_cal_controller[lastview]=' . $controller->extendLastView() . '&tx_cal_controller[view]=create_event';
            $local_cObj->data['link_section'] = 'default';
            $local_cObj->data['link_parameter'] = $conf['view.']['event.']['createEventViewPid'] ?: $GLOBALS['TSFE']->id;

            $sims['###CREATE_EVENT_LINK###'] .= $local_cObj->cObjGetSingle(
                $conf['view.'][$view . '.']['event.']['addLink'],
                $conf['view.'][$view . '.']['event.']['addLink.']
            );
        }
    }

    public function getParentMonth()
    {
        return $this->parentMonth;
    }

    /**
     * @param $parentMonth
     */
    public function setParentMonth($parentMonth)
    {
        $this->parentMonth = $parentMonth;
    }

    public function getDay()
    {
        return $this->day;
    }

    /**
     * @param $day
     */
    public function setDay($day)
    {
        $this->day = $day;
    }

    public function getMonth()
    {
        return $this->month;
    }

    /**
     * @param $month
     */
    public function setMonth($month)
    {
        $this->month = $month;
    }

    public function getYear()
    {
        return $this->year;
    }

    /**
     * @param $year
     */
    public function setYear($year)
    {
        $this->year = $year;
    }

    public function getWeekdayNumber()
    {
        return $this->weekdayNumber;
    }

    /**
     * @param $weekdayNumber
     */
    public function setWeekdayNumber($weekdayNumber)
    {
        $this->weekdayNumber = $weekdayNumber;
    }

    public function getMySubpart()
    {
        return $this->mySubpart;
    }

    /**
     * @param $mySubpart
     */
    public function setMySubpart($mySubpart)
    {
        $this->mySubpart = $mySubpart;
    }

    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * @param $template
     */
    public function setTemplate($template)
    {
        $this->template = $template;
    }

    /**
     * @return int
     */
    public function getWeekDayLength(): int
    {
        return $this->weekDayLength;
    }

    /**
     * @param $length
     */
    public function setWeekDayLength($length)
    {
        $this->weekDayLength = $length;
    }

    /**
     * @return string
     */
    public function getWeekDayFormat(): string
    {
        return $this->weekDayFormat;
    }

    /**
     * @param $format
     */
    public function setWeekDayFormat($format)
    {
        $this->weekDayFormat = $format;
    }
}
