<?php

/*
 * This file is part of the web-tp3/cal.
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace TYPO3\CMS\Cal\Backend\TCA;

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
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Cal\Model\CalendarDateTime;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class Labels
 */
class Labels
{
    /**
     * @param $params
     * @param $pObj
     * @return string
     * @deprecated since ext:cal version 2.x. Will be removed in version 3.0.0
     */
    public function getEventRecordLabel(&$params, &$pObj)
    {
        if ($params['table'] !== 'tx_cal_event' && $params['table'] !== 'tx_cal_exception_event') {
            return '';
        }

        // Get complete record
        $rec = BackendUtility::getRecordWSOL($params['table'], $params['row']['uid']);
//        $dateObj = new CalendarDateTime($rec['start_date'] ? $rec['start_date'] : date('Ymd') . '000000');
       // $dateObj->setTZbyID('UTC');

        $dateObj = GeneralUtility::makeInstance(CalendarDateTime::class, $rec['start_date'] ??  date('Ymd') . '000000');
           // ->setTimezone(new \DateTimeZone(date('T')));
        $format = $GLOBALS['TYPO3_CONF_VARS']['SYS']['ddmmyy'];


        if ($rec['allday'] || $params['table'] === 'tx_cal_exception_event') {
            /* If we have an all day event, only show the date */
            $datetime = $dateObj->format($format);
            $params['start_date'] = $datetime;
        } else {
            /* For normal events, show both the date and time */
            // gmdate is ok, as long as $rec['start_time'] just holds information about 24h.
            $datetime = $dateObj->format($format);
            $params['start_date'] = $datetime;
            $extConf =  is_array($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['cal']) ? $GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['cal'] : unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['cal']);
            if(!empty($params['end_date']) && $params['start_date'] != $params['end_date'] ){
                $dateObj = GeneralUtility::makeInstance(CalendarDateTime::class, $rec['end_date'] ??  date('Ymd') . '000000');
                // ->setTimezone(new \DateTimeZone(date('T')));
                $format = $GLOBALS['TYPO3_CONF_VARS']['SYS']['ddmmyy'];
                $datetime = $dateObj->format($format);
                $params['end_date'] = $datetime;
            }
            if ($extConf['showTimes'] == 1) {
                $format = $GLOBALS['TYPO3_CONF_VARS']['SYS']['hhmm'];
                $dateObj = GeneralUtility::makeInstance(CalendarDateTime::class)
                    ->createFromFormat('U', $rec['start_time'] ?:date('U') )
                    ->setTimezone(new \DateTimeZone('UTC'));
                $datetime .= ' '.$dateObj->format($format);
               // $datetime .= ' ' . gmdate($GLOBALS['TYPO3_CONF_VARS']['SYS']['hhmm'], $rec['start_time']);

                if ($rec['end_time']) {
                    $dateObj = GeneralUtility::makeInstance(CalendarDateTime::class)
                        ->createFromFormat('U', $rec['end_time'] ?:date('U'))
                        ->setTimezone(new \DateTimeZone('UTC'));
                    $datetime .= '-'.$dateObj->format($format);
                }
            }

        }
        // Assemble the label
        $label = $datetime . ' / ' . $rec['title'];

        // Write to the label
        $params['title'] = $label;
    }

    /**
     * @param $params
     * @param $pObj
     * @return string
     * @deprecated since ext:cal version 2.x. Will be removed in version 3.0.0
     */
    public function getAttendeeRecordLabel(&$params, &$pObj)
    {
        if (!$params['table'] === 'tx_cal_attendee') {
            return '';
        }

        // Get complete record
        $rec = BackendUtility::getRecord($params['table'], $params['row']['uid']);

        $label = $rec['email'];
        if ($rec['fe_user_id']) {
            $feUserRec = BackendUtility::getRecord('fe_users', $rec['fe_user_id']);
            $label = $feUserRec['name'] != '' ? $feUserRec['name'] : $feUserRec['username'];
        }
        $label .= ' (' . $GLOBALS['LANG']->sL('LLL:EXT:cal/Resources/Private/Language/locallang_db.xlf:tx_cal_attendee.attendance.' . $rec['attendance']) . ' -> ' . $rec['status'] . ')';

        // Write to the label
        $params['title'] = $label;
    }

    /**
     * @param $params
     * @param $pObj
     * @return string
     * @deprecated since ext:cal version 2.x. Will be removed in version 3.0.0
     */
    public function getMonitoringRecordLabel(&$params, &$pObj)
    {
        if (!$params['table'] === 'tx_cal_fe_user_event_monitor_mm') {
            return '';
        }

        // Get complete record
        $rec = BackendUtility::getRecord($params['table'], $params['row']['uid']);

        $label = '';
        switch ($rec['tablenames']) {
            case 'fe_users':
                $feUserRec = BackendUtility::getRecord('fe_users', $rec['uid_foreign']);
                $label = $feUserRec['name'] != '' ? $feUserRec['name'] : $feUserRec['username'];
                break;
            case 'fe_groups':
                $feUserRec = BackendUtility::getRecord('fe_groups', $rec['uid_foreign']);
                $label = $feUserRec['title'];
                break;
            case 'tx_cal_unknown_users':
                $feUserRec = BackendUtility::getRecord('tx_cal_unknown_users', $rec['uid_foreign']);
                $label = $feUserRec['email'];
                break;
        }

        // Write to the label
        $params['title'] = $label . ' (' . $GLOBALS['LANG']->sL('LLL:EXT:cal/Resources/Private/Language/locallang_db.xlf:tx_cal_fe_user_event.offset') . ': ' . $rec['offset'] . ')';
    }

    /**
     * @param $params
     * @param $pObj
     * @return string
     * @deprecated since ext:cal version 2.x. Will be removed in version 3.0.0
     */
    public function getDeviationRecordLabel(&$params, &$pObj)
    {
        if (!$params['table'] === 'tx_cal_event_deviation') {
            return '';
        }

        // Get complete record
        $rec = BackendUtility::getRecord($params['table'], $params['row']['uid']);

        $label = $GLOBALS['LANG']->sL('LLL:EXT:cal/Resources/Private/Language/locallang_db.xlf:tx_cal_event.deviation') . ': ';

        if ($rec['orig_start_date']) {
//            $dateObj = new CalendarDateTime($rec['orig_start_date'] . '000000');
//            $dateObj->setTZbyID('UTC');
            $dateObj = GeneralUtility::makeInstance(CalendarDateTime::class)->createFromFormat('U', $rec['orig_start_date'])->setTimezone(new \DateTimeZone(date('T')));
            $format = $GLOBALS['TYPO3_CONF_VARS']['SYS']['ddmmyy'];
            $datetime = $dateObj->format($format);
            if ($rec['orig_end_date']) {
                $dateObj = GeneralUtility::makeInstance(CalendarDateTime::class)->createFromFormat('U', $rec['orig_end_date'])->setTimezone(new \DateTimeZone(date('T')));
                $format = $GLOBALS['TYPO3_CONF_VARS']['SYS']['ddmmyy'];
                $datetime .= $dateObj->format($format);

            }
            $label .= $datetime;
        }

        if ($rec['orig_start_time']) {
            $label .= ' (' . gmdate($GLOBALS['TYPO3_CONF_VARS']['SYS']['hhmm'], $rec['orig_start_time']) . ')';
        }

        // Write to the label
        $params['title'] = $label;
    }
}
