<?php

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
use TYPO3\CMS\Cal\Model\CalendarDateTime;

/**
 * This class combines all the time related functions
 */
class Calendar
{

    /**
     * Takes iCalendar 2 day format and makes it into 3 characters
     * if $txt is true, it returns the 3 letters, otherwise it returns the
     * integer of that day; 0=Sun, 1=Mon, etc.
     * @param string $day
     * @param bool $txt
     * @return string
     */
    public static function two2threeCharDays($day, $txt = true): string
    {
        switch ($day) {
            case 'SU':
                return $txt ? 'sun' : '0';
            case 'MO':
                return $txt ? 'mon' : '1';
            case 'TU':
                return $txt ? 'tue' : '2';
            case 'WE':
                return $txt ? 'wed' : '3';
            case 'TH':
                return $txt ? 'thu' : '4';
            case 'FR':
                return $txt ? 'fri' : '5';
            case 'SA':
                return $txt ? 'sat' : '6';
        }
        return '';
    }

    /**
     * @param $date
     * @return mixed
     */
    public static function getYear($date)
    {
        $day_array2 = [];
        preg_match('/([0-9]{4})([0-9]{2})([0-9]{2})/', $date, $day_array2);
        return $day_array2[1];
    }

    /**
     * @param $date
     * @return mixed
     */
    public static function getMonth($date)
    {
        $day_array2 = [];
        preg_match('/([0-9]{4})([0-9]{2})([0-9]{2})/', $date, $day_array2);
        return $day_array2[2];
    }

    /**
     * @param $date
     * @return mixed
     */
    public static function getDay($date)
    {
        $day_array2 = [];
        preg_match('/([0-9]{4})([0-9]{2})([0-9]{2})/', $date, $day_array2);
        return $day_array2[3];
    }

    /**
     * @param CalendarDateTime $dateObject
     * @return CalendarDateTime
     */
    public static function calculateStartDayTime(CalendarDateTime $dateObject): CalendarDateTime
    {
        $dateObject->setTZbyID('UTC');
        $dateObject->setHour(0);
        $dateObject->setMinute(0);
        $dateObject->setSecond(0);
        return $dateObject;
    }

    /**
     * @param CalendarDateTime $dateObject
     * @return CalendarDateTime
     */
    public static function calculateEndDayTime(CalendarDateTime $dateObject): CalendarDateTime
    {
        $dateObject->setTZbyID('UTC');
        $dateObject->setHour(23);
        $dateObject->setMinute(59);
        $dateObject->setSecond(59);
        return $dateObject;
    }

    /**
     * @param CalendarDateTime $dateObject
     * @return CalendarDateTime
     */
    public static function calculateStartWeekTime(CalendarDateTime $dateObject): CalendarDateTime
    {
        $dateObject = self::calculateStartDayTime($dateObject);
        $dateObject->setDay($dateObject->format('j') - $dateObject->format('w'));
        return $dateObject;
    }

    /**
     * @param CalendarDateTime $dateObject
     * @return CalendarDateTime
     */
    public static function calculateEndWeekTime(CalendarDateTime $dateObject): CalendarDateTime
    {
        $dateObject = self::calculateStartWeekTime($dateObject);
        $dateObject->addSeconds(604799);
        return $dateObject;
    }

    /**
     * @param CalendarDateTime $dateObject
     * @return CalendarDateTime
     */
    public static function calculateStartMonthTime(CalendarDateTime $dateObject): CalendarDateTime
    {
        $dateObject = self::calculateStartDayTime($dateObject);
        $dateObject->setDay(1);
        return $dateObject;
    }

    /**
     * @param CalendarDateTime $dateObject
     * @return CalendarDateTime
     */
    public static function calculateEndMonthTime(CalendarDateTime $dateObject): CalendarDateTime
    {
        $dateObject = self::calculateEndDayTime($dateObject);
        $dateObject->setDay($dateObject->format('t'));
        return $dateObject;
    }

    /**
     * @param CalendarDateTime $dateObject
     * @return CalendarDateTime
     */
    public static function calculateStartYearTime(CalendarDateTime $dateObject): CalendarDateTime
    {
        $dateObject = self::calculateStartMonthTime($dateObject);
        $dateObject->setMonth(1);
        return $dateObject;
    }

    /**
     * @param CalendarDateTime $dateObject
     * @return CalendarDateTime
     */
    public static function calculateEndYearTime(CalendarDateTime $dateObject): CalendarDateTime
    {
        $dateObject = self::calculateStartYearTime($dateObject);
        $dateObject->setYear($dateObject->getYear() + 1);
        $dateObject->subtractSeconds(1);
        return $dateObject;
    }

    /**
     * @param $time
     * @return string
     */
    public static function getHourFromTime($time): string
    {
        $retVal = '';
        $time = str_replace(':', '', $time);

        if ($time) {
            $retVal = substr($time, 0, -2);
        }
        return $retVal;
    }

    /**
     * @param $time
     * @return string
     */
    public static function getMinutesFromTime($time): string
    {
        $retVal = '';
        $time = str_replace(':', '', $time);
        if ($time) {
            $retVal = substr($time, -2);
        }
        return $retVal;
    }

    /**
     * @param int $timestamp
     * @return number
     */
    public static function getTimeFromTimestamp($timestamp = 0)
    {
        if ($timestamp > 0) {
            // gmdate and gmmktime are ok, as long as the timestamp just holds information about 24h.
            return gmmktime(gmdate('H', $timestamp), gmdate('i', $timestamp), 0, 0, 0, 1) - gmmktime(0, 0, 0, 0, 0, 1);
        }
        return 0;
    }
}
