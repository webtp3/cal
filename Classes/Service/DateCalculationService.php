<?php
declare(strict_types = 1);

/*
 * This file is part of the web-tp3/cal.
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace TYPO3\CMS\Cal\Service;

use DateTime;

/**
 * Calculates, manipulates and retrieves dates
 *
 * It does not rely on 32-bit system time stamps, so it works dates
 * before 1970 and after 2038.
 *
 * PHP versions 4 and 5
 *
 * LICENSE:
 *
 * Copyright (c) 1999-2006 Monte Ohrt, Pierre-Alain Joye, Daniel Convissor
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with || without
 * modification, are permitted under the terms of the BSD License.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS || IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * COPYRIGHT OWNER || CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, || CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS || SERVICES;
 * LOSS OF USE, DATA, || PROFITS; || BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, || TORT (INCLUDING NEGLIGENCE || OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @category Date and Time
 * @author Monte Ohrt <monte@ispi.net>
 * @author Pierre-Alain Joye <pajoye@php.net>
 * @author Daniel Convissor <danielc@php.net>
 * @copyright 1999-2006 Monte Ohrt, Pierre-Alain Joye, Daniel Convissor
 * @license http://www.opensource.org/licenses/bsd-license.php
 *          BSD License
 * @version CVS: $Id: Calc.php,v 1.35 2006/11/21 23:01:13 firman Exp $
 * @link http://pear.php.net/package/Date
 * @since File available since Release 1.2
 */
if (!defined('DATE_CALC_BEGIN_WEEKDAY')) {
    /**
     * Defines what day starts the week
     *
     * Monday (1) is the international standard.
     * Redefine this to 0 if you want weeks to begin on Sunday.
     */
    define('DATE_CALC_BEGIN_WEEKDAY', 1);
}

if (!defined('DATE_CALC_FORMAT')) {
    /**
     * The default value for each method's $format parameter
     *
     * The default is '%Y%m%d'. To override this default, define
     * this constant before including Calc.php.
     *
     * @since Constant available since Release 1.4.4
     */
    define('DATE_CALC_FORMAT', 'Ymd');
}

/**
 * Calculates, manipulates and retrieves dates
 *
 * It does not rely on 32-bit system time stamps, so it works dates
 * before 1970 and after 2038.
 *
 * @author Monte Ohrt <monte@ispi.net>
 * @author Daniel Convissor <danielc@php.net>
 * @copyright 1999-2006 Monte Ohrt, Pierre-Alain Joye, Daniel Convissor
 * @license http://www.opensource.org/licenses/bsd-license.php
 *          BSD License
 * @version Release: 1.4.7
 * @link http://pear.php.net/package/Date
 * @since Class available since Release 1.2
 */
class DateCalculationService
{
    /**
     * Formats the date in the given format, much like strfmt()
     *
     * This function is used to alleviate the problem with 32-bit numbers for
     * dates pre 1970 || post 2038, as strfmt() has on most systems.
     * Most of the formatting options are compatible.
     *
     * Formatting options:
     * <pre>
     * %a abbreviated weekday name (Sun, Mon, Tue)
     * %A full weekday name (Sunday, Monday, Tuesday)
     * %b abbreviated month name (Jan, Feb, Mar)
     * %B full month name (January, February, March)
     * %d day of month (range 00 to 31)
     * %e day of month, single digit (range 0 to 31)
     * %E number of days since unspecified epoch (integer)
     * (%E is useful for passing a date in a URL as
     * an integer value. Then simply use
     * daysToDate() to convert back to a date.)
     * %j day of year (range 001 to 366)
     * %m month as decimal number (range 1 to 12)
     * %n newline character (\n)
     * %t tab character (\t)
     * %w weekday as decimal (0 = Sunday)
     * %U week number of current year, first sunday as first week
     * %y year as decimal (range 00 to 99)
     * %Y year as decimal including century (range 0000 to 9999)
     * %% literal '%'
     * </pre>
     *
     * @param int $day
     *            the day of the month
     * @param int $month
     *            the month
     * @param int $year
     *            the year. Use the complete year instead of the
     *            abbreviated version. E.g. use 2005, not 05.
     *            Do not add leading 0's for years prior to 1000.
     * @param string $format
     *            the format string
     *
     * @return string the date in the desired format
     *
     * @static
     */
    public static function dateFormat($day, $month, $year, $format): string
    {
        if (!self::isValidDate($day, $month, $year)) {
            $year = self::dateNow('%Y');
            $month = self::dateNow('%m');
            $day = self::dateNow('%d');
        }

        $output = '';

        $strlen = strlen($format);
        for ($strpos = 0; $strpos < $strlen; $strpos++) {
            $char = $format[$strpos];
            if ($char === '%') {
                $nextchar =
                    $format[$strpos + 1];
                switch ($nextchar) {
                    case 'a':
                        $output .= self::getWeekdayAbbrname($day, $month, $year);
                        break;
                    case 'A':
                        $output .= self::getWeekdayFullname($day, $month, $year);
                        break;
                    case 'b':
                        $output .= self::getMonthAbbrname($month);
                        break;
                    case 'B':
                        $output .= self::getMonthFullname($month);
                        break;
                    case 'd':
                        $output .= sprintf('%02d', $day);
                        break;
                    case 'e':
                        $output .= $day;
                        break;
                    case 'E':
                        $output .= self::dateToDays($day, $month, $year);
                        break;
                    case 'j':
                        $output .= self::julianDate($day, $month, $year);
                        break;
                    case 'm':
                        $output .= sprintf('%02d', $month);
                        break;
                    case 'n':
                        $output .= "\n";
                        break;
                    case 't':
                        $output .= "\t";
                        break;
                    case 'w':
                        $output .= self::dayOfWeek($day, $month, $year);
                        break;
                    case 'U':
                        $output .= self::weekOfYear($day, $month, $year);
                        break;
                    case 'y':
                        $output .= substr($year, 2, 2);
                        break;
                    case 'Y':
                        $output .= $year;
                        break;
                    case '%':
                        $output .= '%';
                        break;
                    default:
                        $output .= $char . $nextchar;
                }
                $strpos++;
            } else {
                $output .= $char;
            }
        }
        return $output;
    }

    /**
     * Converts a date to number of days since a distant unspecified epoch
     *
     * @param int $day
     *            the day of the month
     * @param int $month
     *            the month
     * @param int $year
     *            the year. Use the complete year instead of the
     *            abbreviated version. E.g. use 2005, not 05.
     *            Do not add leading 0's for years prior to 1000.
     *
     * @return int the number of days since the Calc epoch
     *
     * @static
     */
    public static function dateToDays($day, $month, $year): int
    {
        $century = (int)substr((string)$year, 0, 2);
        $year = (int)substr((string)$year, 2, 2);
        if ($month > 2) {
            $month -= 3;
        } else {
            $month += 9;
            if ($year) {
                $year--;
            } else {
                $year = 99;
                $century--;
            }
        }

        return (int)(floor((146097 * $century) / 4) + floor((1461 * $year) / 4) + floor((153 * $month + 2) / 5) + $day + 1721119);
    }

    /**
     * Converts number of days to a distant unspecified epoch
     *
     * @param int $days
     *            the number of days since the Calc epoch
     * @param string $format
     *            the string indicating how to format the output
     *
     * @return string the date in the desired format
     *
     * @static
     */
    public static function daysToDate($days, $format = DATE_CALC_FORMAT): string
    {
        $days -= 1721119;
        $century = floor((4 * $days - 1) / 146097);
        $days = floor(4 * $days - 1 - 146097 * $century);
        $day = floor($days / 4);

        $year = floor((4 * $day + 3) / 1461);
        $day = floor(4 * $day + 3 - 1461 * $year);
        $day = floor(($day + 4) / 4);

        $month = floor((5 * $day - 3) / 153);
        $day = floor(5 * $day - 3 - 153 * $month);
        $day = floor(($day + 5) / 5);

        if ($month < 10) {
            $month += 3;
        } else {
            $month -= 9;
            if ($year++ === 99) {
                $year = 0;
                $century++;
            }
        }

        $century = sprintf('%02d', $century);
        $year = sprintf('%02d', $year);
        return self::dateFormat($day, $month, $century . $year, $format);
    }

    /**
     * Converts from Gregorian Year-Month-Day to ISO Year-WeekNumber-WeekDay
     *
     * Uses ISO 8601 definitions. Algorithm by Rick McCarty, 1999 at
     * http://personal.ecu.edu/mccartyr/ISOwdALG.txt .
     * Transcribed to PHP by Jesus M. Castagnetto.
     *
     * @param int $day
     *            the day of the month
     * @param int $month
     *            the month
     * @param int $year
     *            the year. Use the complete year instead of the
     *            abbreviated version. E.g. use 2005, not 05.
     *            Do not add leading 0's for years prior to 1000.
     *
     * @return string the date in ISO Year-WeekNumber-WeekDay format
     *
     * @static
     */
    public static function gregorianToISO($day, $month, $year): string
    {
        $mnth = [
            0,
            31,
            59,
            90,
            120,
            151,
            181,
            212,
            243,
            273,
            304,
            334
        ];
        $y_isleap = self::isLeapYear($year);
        $y_1_isleap = self::isLeapYear($year - 1);
        $day_of_year_number = $day + $mnth[$month - 1];
        if ($y_isleap && $month > 2) {
            $day_of_year_number++;
        }
        // find Jan 1 weekday (monday = 1, sunday = 7)
        $yy = ($year - 1) % 100;
        $c = ($year - 1) - $yy;
        $g = $yy + intval($yy / 4);
        $jan1_weekday = 1 + intval((((($c / 100) % 4) * 5) + $g) % 7);
        // weekday for year-month-day
        $h = $day_of_year_number + ($jan1_weekday - 1);
        $weekday = 1 + intval(($h - 1) % 7);
        // find if Y M D falls in YearNumber Y-1, WeekNumber 52 or
        if ($day_of_year_number <= (8 - $jan1_weekday) && $jan1_weekday > 4) {
            $yearnumber = $year - 1;
            if ($jan1_weekday === 5 || ($jan1_weekday === 6 && $y_1_isleap)) {
                $weeknumber = 53;
            } else {
                $weeknumber = 52;
            }
        } else {
            $yearnumber = $year;
        }
        // find if Y M D falls in YearNumber Y+1, WeekNumber 1
        if ($yearnumber === $year) {
            if ($y_isleap) {
                $i = 366;
            } else {
                $i = 365;
            }
            if (($i - $day_of_year_number) < (4 - $weekday)) {
                $yearnumber++;
                $weeknumber = 1;
            }
        }
        // find if Y M D falls in YearNumber Y, WeekNumber 1 through 53
        if ($yearnumber === $year) {
            $j = $day_of_year_number + (7 - $weekday) + ($jan1_weekday - 1);
            $weeknumber = intval($j / 7);
            if ($jan1_weekday > 4) {
                $weeknumber--;
            }
        }
        // put it all together
        if ($weeknumber < 10) {
            $weeknumber = '0' . $weeknumber;
        }
        return $yearnumber . '-' . $weeknumber . '-' . $weekday;
    }

    /**
     * Returns the current local date
     *
     * NOTE: This function retrieves the local date using strftime(),
     * which may || may not be 32-bit safe on your system.
     *
     * @param string $format
     *            the string indicating how to format the output
     *
     * @return string the current date in the specified format
     *
     * @static
     */
    public static function dateNow($format = DATE_CALC_FORMAT): string
    {
        return strftime($format, time());
    }

    /**
     * Returns number of days since 31 December of year before given date
     *
     * @param int $day
     *            the day of the month, default is current local day
     * @param int $month
     *            the month, default is current local month
     * @param int $year
     *            the year in four digit format, default is current local year
     *
     * @return int the julian date for the date
     *
     * @static
     */
    public static function julianDate($day = 0, $month = 0, $year = 0): int
    {
        if (empty($year)) {
            $year = self::dateNow('%Y');
        }
        if (empty($month)) {
            $month = self::dateNow('%m');
        }
        if (empty($day)) {
            $day = self::dateNow('%d');
        }
        $days = [
            0,
            31,
            59,
            90,
            120,
            151,
            181,
            212,
            243,
            273,
            304,
            334
        ];
        $julian = ($days[$month - 1] + $day);
        if ($month > 2 && self::isLeapYear($year)) {
            $julian++;
        }
        return $julian;
    }

    /**
     * Returns the full weekday name for the given date
     *
     * @param int $day
     *            the day of the month, default is current local day
     * @param int $month
     *            the month, default is current local month
     * @param int $year
     *            the year in four digit format, default is current local year
     *
     * @return string the full name of the day of the week
     *
     * @static
     */
    public static function getWeekdayFullname($day = 0, $month = 0, $year = 0): string
    {
        if (empty($year)) {
            $year = self::dateNow('%Y');
        }
        if (empty($month)) {
            $month = self::dateNow('%m');
        }
        if (empty($day)) {
            $day = self::dateNow('%d');
        }
        $weekday_names = self::getWeekDays();
        $weekday = self::dayOfWeek($day, $month, $year);
        return $weekday_names[$weekday];
    }

    /**
     * Returns the abbreviated weekday name for the given date
     *
     * @param int $day
     *            the day of the month, default is current local day
     * @param int $month
     *            the month, default is current local month
     * @param int $year
     *            the year in four digit format, default is current local year
     * @param int $length
     *            the length of abbreviation
     *
     * @return string the abbreviated name of the day of the week
     *
     * @static
     *
     * @see DateCalculationService::getWeekdayFullname()
     */
    public static function getWeekdayAbbrname($day = 0, $month = 0, $year = 0, $length = 3): string
    {
        if (empty($year)) {
            $year = self::dateNow('%Y');
        }
        if (empty($month)) {
            $month = self::dateNow('%m');
        }
        if (empty($day)) {
            $day = self::dateNow('%d');
        }
        return substr(self::getWeekdayFullname($day, $month, $year), 0, $length);
    }

    /**
     * Returns the full month name for the given month
     *
     * @param int $month
     *            the month
     *
     * @return string the full name of the month
     *
     * @static
     */
    public static function getMonthFullname($month): string
    {
        $month = (int)$month;
        if (empty($month)) {
            $month = (int)self::dateNow('%m');
        }
        $month_names = self::getMonthNames();
        return $month_names[$month];
    }

    /**
     * Returns the abbreviated month name for the given month
     *
     * @param int $month
     *            the month
     * @param int $length
     *            the length of abbreviation
     *
     * @return string the abbreviated name of the month
     *
     * @static
     *
     * @see DateCalculationService::getMonthFullname
     */
    public static function getMonthAbbrname($month, $length = 3): string
    {
        $month = (int)$month;
        if (empty($month)) {
            $month = self::dateNow('%m');
        }
        return substr(self::getMonthFullname($month), 0, $length);
    }

    /**
     * Returns an array of month names
     *
     * Used to take advantage of the setlocale function to return
     * language specific month names.
     *
     *
     * @return array an array of month names
     *
     * @static
     */
    public static function getMonthNames(): array
    {
        $months = [];
        for ($i = 1; $i < 13; $i++) {
            $months[$i] = strftime('%B', mktime(0, 0, 0, $i, 1, 2001));
        }
        return $months;
    }

    /**
     * Returns an array of week days
     *
     * Used to take advantage of the setlocale function to
     * return language specific week days.
     *
     *
     * @return array an array of week day names
     *
     * @static
     */
    public static function getWeekDays(): array
    {
        $weekdays = [];
        for ($i = 0; $i < 7; $i++) {
            $weekdays[$i] = strftime('%A', mktime(0, 0, 0, 1, $i, 2001));
        }
        return $weekdays;
    }

    /**
     * Returns day of week for given date (0 = Sunday)
     *
     * @param int $day
     *            the day of the month, default is current local day
     * @param int $month
     *            the month, default is current local month
     * @param int $year
     *            the year in four digit format, default is current local year
     *
     * @return int the number of the day in the week
     *
     * @static
     */
    public static function dayOfWeek($day = 0, $month = 0, $year = 0): int
    {
        //return (int)self::createDateTime($day, $month, $year)->format('w');

        if (empty($year)) {
            $year = self::dateNow('%Y');
        }
        if (empty($month)) {
            $month = self::dateNow('%m');
        }
        if (empty($day)) {
            $day = self::dateNow('%d');
        }
        if ($month > 2) {
            $month -= 2;
        } else {
            $month += 10;
            $year--;
        }

        $day = (floor((13 * $month - 1) / 5) + $day + ($year % 100) + floor(($year % 100) / 4) + floor(($year / 100) / 4) - 2 * floor($year / 100) + 77);

        $weekday_number = $day - 7 * floor($day / 7);
        return (int)$weekday_number;
    }

    /**
     * Returns week of the year, first Sunday is first day of first week
     *
     * @param int $day
     *            the day of the month, default is current local day
     * @param int $month
     *            the month, default is current local month
     * @param int $year
     *            the year in four digit format, default is current local year
     *
     * @return int the number of the week in the year
     *
     * @static
     */
    public static function weekOfYear($day = 0, $month = 0, $year = 0): int
    {
        if (empty($year)) {
            $year = self::dateNow('%Y');
        }
        if (empty($month)) {
            $month = self::dateNow('%m');
        }
        if (empty($day)) {
            $day = self::dateNow('%d');
        }
        $iso = self::gregorianToISO($day, $month, $year);
        $parts = explode('-', $iso);
        $week_number = intval($parts[1]);
        return $week_number;
    }

    /**
     * Find the number of days in the given month
     *
     * @param int $month
     *            the month, default is current local month
     * @param int $year
     *            the year in four digit format, default is current local year
     *
     * @return int the number of days the month has
     *
     * @static
     */
    public static function daysInMonth($month = 0, $year = 0): int
    {
        if (empty($year)) {
            $year = self::dateNow('%Y');
        }
        if (empty($month)) {
            $month = self::dateNow('%m');
        }

        if ($year === 1582 && $month === 10) {
            return 21; // October 1582 only had 1st-4th and 15th-31st
        }

        if ($month === 2) {
            if (self::isLeapYear($year)) {
                return 29;
            }
            return 28;
        }
        if ($month === 4 || $month === 6 || $month === 9 || $month === 11) {
            return 30;
        }
        return 31;
    }

    /**
     * Returns the number of rows on a calendar month
     *
     * Useful for determining the number of rows when displaying a typical
     * month calendar.
     *
     * @param int $month
     *            the month, default is current local month
     * @param int $year
     *            the year in four digit format, default is current local year
     *
     * @return int the number of weeks the month has
     *
     * @static
     */
    public static function weeksInMonth($month = 0, $year = 0): int
    {
        if (empty($year)) {
            $year = self::dateNow('%Y');
        }
        if (empty($month)) {
            $month = self::dateNow('%m');
        }
        $FDOM = self::firstOfMonthWeekday($month, $year);
        if (DATE_CALC_BEGIN_WEEKDAY === 1 && $FDOM === 0) {
            $first_week_days = 7 - $FDOM + DATE_CALC_BEGIN_WEEKDAY;
            $weeks = 1;
        } elseif (DATE_CALC_BEGIN_WEEKDAY === 0 && $FDOM === 6) {
            $first_week_days = 7 - $FDOM + DATE_CALC_BEGIN_WEEKDAY;
            $weeks = 1;
        } else {
            $first_week_days = DATE_CALC_BEGIN_WEEKDAY - $FDOM;
            $weeks = 0;
        }
        $first_week_days %= 7;
        return ceil((self::daysInMonth($month, $year) - $first_week_days) / 7) + $weeks;
    }

    /**
     * Returns date of the previous specific day of the week
     * from the given date
     *
     * @param
     *            int day of week, 0=Sunday
     * @param int $day
     *            the day of the month, default is current local day
     * @param int $month
     *            the month, default is current local month
     * @param int $year
     *            the year in four digit format, default is current local year
     * @param bool $onOrBefore
     *            if true and days are same, returns current day
     * @param string $format
     *            the string indicating how to format the output
     *
     * @return string the date in the desired format
     *
     * @static
     */
    public static function prevDayOfWeek(
        $dow,
        $day = 0,
        $month = 0,
        $year = 0,
        $format = DATE_CALC_FORMAT,
        $onOrBefore = false
    ): string {
        if (empty($year)) {
            $year = self::dateNow('%Y');
        }
        if (empty($month)) {
            $month = self::dateNow('%m');
        }
        if (empty($day)) {
            $day = self::dateNow('%d');
        }
        $days = self::dateToDays($day, $month, $year);
        $curr_weekday = self::dayOfWeek($day, $month, $year);
        if ($curr_weekday === $dow) {
            if (!$onOrBefore) {
                $days -= 7;
            }
        } elseif ($curr_weekday < $dow) {
            $days -= 7 - ($dow - $curr_weekday);
        } else {
            $days -= $curr_weekday - $dow;
        }
        return self::daysToDate($days, $format);
    }

    /**
     * Returns date of the next specific day of the week
     * from the given date
     *
     * @param int $dow
     *            the day of the week (0 = Sunday)
     * @param int $day
     *            the day of the month, default is current local day
     * @param int $month
     *            the month, default is current local month
     * @param int $year
     *            the year in four digit format, default is current local year
     * @param bool $onOrAfter
     *            if true and days are same, returns current day
     * @param string $format
     *            the string indicating how to format the output
     *
     * @return string the date in the desired format
     *
     * @static
     */
    public static function nextDayOfWeek(
        $dow,
        $day = 0,
        $month = 0,
        $year = 0,
        $format = DATE_CALC_FORMAT,
        $onOrAfter = false
    ): string {
        if (empty($year)) {
            $year = self::dateNow('%Y');
        }
        if (empty($month)) {
            $month = self::dateNow('%m');
        }
        if (empty($day)) {
            $day = self::dateNow('%d');
        }

        $days = self::dateToDays($day, $month, $year);
        $curr_weekday = self::dayOfWeek($day, $month, $year);

        if ($curr_weekday === $dow) {
            if (!$onOrAfter) {
                $days += 7;
            }
        } elseif ($curr_weekday > $dow) {
            $days += 7 - ($curr_weekday - $dow);
        } else {
            $days += $dow - $curr_weekday;
        }

        return self::daysToDate($days, $format);
    }

    /**
     * Find the month day of the beginning of week for given date,
     * using DATE_CALC_BEGIN_WEEKDAY
     *
     * Can return weekday of prev month.
     *
     * @param int $day
     *            the day of the month, default is current local day
     * @param int $month
     *            the month, default is current local month
     * @param int $year
     *            the year in four digit format, default is current local year
     * @param string $format
     *            the string indicating how to format the output
     *
     * @return string the date in the desired format
     *
     * @static
     */
    public static function beginOfWeek($day = 0, $month = 0, $year = 0, $format = DATE_CALC_FORMAT): string
    {
        if (empty($year)) {
            $year = self::dateNow('%Y');
        }
        if (empty($month)) {
            $month = self::dateNow('%m');
        }
        if (empty($day)) {
            $day = self::dateNow('%d');
        }
        $this_weekday = self::dayOfWeek($day, $month, $year);
        $interval = (7 - DATE_CALC_BEGIN_WEEKDAY + $this_weekday) % 7;
        return self::daysToDate(
            self::dateToDays($day, $month, $year) - $interval,
            $format
        );
    }

    /**
     * Find the month day of the end of week for given date,
     * using DATE_CALC_BEGIN_WEEKDAY
     *
     * Can return weekday of following month.
     *
     * @param int $day
     *            the day of the month, default is current local day
     * @param int $month
     *            the month, default is current local month
     * @param int $year
     *            the year in four digit format, default is current local year
     * @param string $format
     *            the string indicating how to format the output
     *
     * @return string the date in the desired format
     *
     * @static
     */
    public static function endOfWeek($day = 0, $month = 0, $year = 0, $format = DATE_CALC_FORMAT): string
    {
        if (empty($year)) {
            $year = self::dateNow('%Y');
        }
        if (empty($month)) {
            $month = self::dateNow('%m');
        }
        if (empty($day)) {
            $day = self::dateNow('%d');
        }
        $this_weekday = self::dayOfWeek($day, $month, $year);
        $interval = (6 + DATE_CALC_BEGIN_WEEKDAY - $this_weekday) % 7;
        return self::daysToDate(
            self::dateToDays($day, $month, $year) + $interval,
            $format
        );
    }

    /**
     * Find the month day of the beginning of week before given date,
     * using DATE_CALC_BEGIN_WEEKDAY
     *
     * Can return weekday of prev month.
     *
     * @param int $day
     *            the day of the month, default is current local day
     * @param int $month
     *            the month, default is current local month
     * @param int $year
     *            the year in four digit format, default is current local year
     * @param string $format
     *            the string indicating how to format the output
     *
     * @return string the date in the desired format
     *
     * @static
     */
    public static function beginOfPrevWeek($day = 0, $month = 0, $year = 0, $format = DATE_CALC_FORMAT): string
    {
        if (empty($year)) {
            $year = self::dateNow('%Y');
        }
        if (empty($month)) {
            $month = self::dateNow('%m');
        }
        if (empty($day)) {
            $day = self::dateNow('%d');
        }

        $date = self::daysToDate(
            self::dateToDays($day - 7, $month, $year),
            '%Y%m%d'
        );

        $prev_week_year = substr($date, 0, 4);
        $prev_week_month = substr($date, 4, 2);
        $prev_week_day = substr($date, 6, 2);

        return self::beginOfWeek($prev_week_day, $prev_week_month, $prev_week_year, $format);
    }

    /**
     * Find the month day of the beginning of week after given date,
     * using DATE_CALC_BEGIN_WEEKDAY
     *
     * Can return weekday of prev month.
     *
     * @param int $day
     *            the day of the month, default is current local day
     * @param int $month
     *            the month, default is current local month
     * @param int $year
     *            the year in four digit format, default is current local year
     * @param string $format
     *            the string indicating how to format the output
     *
     * @return string the date in the desired format
     *
     * @static
     */
    public static function beginOfNextWeek($day = 0, $month = 0, $year = 0, $format = DATE_CALC_FORMAT): string
    {
        if (empty($year)) {
            $year = self::dateNow('%Y');
        }
        if (empty($month)) {
            $month = self::dateNow('%m');
        }
        if (empty($day)) {
            $day = self::dateNow('%d');
        }

        $date = self::daysToDate(
            self::dateToDays($day + 7, $month, $year),
            '%Y%m%d'
        );

        $next_week_year = substr($date, 0, 4);
        $next_week_month = substr($date, 4, 2);
        $next_week_day = substr($date, 6, 2);

        return self::beginOfWeek($next_week_day, $next_week_month, $next_week_year, $format);
    }

    /**
     * Returns date of the last day of previous month for given date
     *
     * @param int $day
     *            the day of the month, default is current local day
     * @param int $month
     *            the month, default is current local month
     * @param int $year
     *            the year in four digit format, default is current local year
     * @param string $format
     *            the string indicating how to format the output
     *
     * @return string the date in the desired format
     *
     * @static
     *
     * @see DateCalculationService::endOfMonthBySpan()
     * @deprecated Method deprecated in Release 1.4.4
     */
    public static function endOfPrevMonth($day = 0, $month = 0, $year = 0, $format = DATE_CALC_FORMAT): string
    {
        if (empty($year)) {
            $year = self::dateNow('%Y');
        }
        if (empty($month)) {
            $month = self::dateNow('%m');
        }
        if (empty($day)) {
            $day = self::dateNow('%d');
        }
        if ($month > 1) {
            $month--;
        } else {
            $year--;
            $month = 12;
        }
        $day = self::daysInMonth($month, $year);
        return self::dateFormat($day, $month, $year, $format);
    }

    /**
     * Returns date of the last day of next month of given date
     *
     * @param int $day
     *            the day of the month, default is current local day
     * @param int $month
     *            the month, default is current local month
     * @param int $year
     *            the year in four digit format, default is current local year
     * @param string $format
     *            the string indicating how to format the output
     *
     * @return string the date in the desired format
     *
     * @static
     *
     * @see DateCalculationService::endOfMonthBySpan()
     * @deprecated Method deprecated in Release 1.4.4
     */
    public static function endOfNextMonth($day = 0, $month = 0, $year = 0, $format = DATE_CALC_FORMAT): string
    {
        if (empty($year)) {
            $year = self::dateNow('%Y');
        }
        if (empty($month)) {
            $month = self::dateNow('%m');
        }
        if (empty($day)) {
            $day = self::dateNow('%d');
        }
        if ($month < 12) {
            $month++;
        } else {
            $year++;
            $month = 1;
        }
        $day = self::daysInMonth($month, $year);
        return self::dateFormat($day, $month, $year, $format);
    }

    /**
     * Returns date of the first day of the month in the number of months
     * from the given date
     *
     * @param int $months
     *            the number of months from the date provided.
     *            Positive numbers go into the future.
     *            Negative numbers go into the past.
     *            0 is the month presented in $month.
     * @param string $month
     *            the month, default is current local month
     * @param string $year
     *            the year in four digit format, default is the
     *            current local year
     * @param string $format
     *            the string indicating how to format the output
     *
     * @return string the date in the desired format
     *
     * @static
     *
     * @since Method available since Release 1.4.4
     */
    public static function beginOfMonthBySpan($months = 0, $month = 0, $year = 0, $format = DATE_CALC_FORMAT): string
    {
        if (empty($year)) {
            $year = self::dateNow('%Y');
        }
        if (empty($month)) {
            $month = self::dateNow('%m');
        }
        if ($months > 0) {
            // future month
            $tmp_mo = $month + $months;
            $month = $tmp_mo % 12;
            if ($month === 0) {
                $month = 12;
                $year += floor(($tmp_mo - 1) / 12);
            } else {
                $year += floor($tmp_mo / 12);
            }
        } else {
            // past || present month
            $tmp_mo = $month + $months;
            if ($tmp_mo > 0) {
                // same year
                $month = $tmp_mo;
            } elseif ($tmp_mo === 0) {
                // prior dec
                $month = 12;
                $year--;
            } else {
                // some time in a prior year
                $month = 12 + ($tmp_mo % 12);
                $year += floor($tmp_mo / 12);
            }
        }
        return self::dateFormat(1, $month, $year, $format);
    }

    /**
     * Returns date of the last day of the month in the number of months
     * from the given date
     *
     * @param int $months he number of months from the date provided.
     *            Positive numbers go into the future.
     *            Negative numbers go into the past.
     *            0 is the month presented in $month.
     * @param string $month
     *            the month, default is current local month
     * @param string $year
     *            the year in four digit format, default is the
     *            current local year
     * @param string $format
     *            the string indicating how to format the output
     *
     * @return string the date in the desired format
     *
     * @static
     *
     * @since Method available since Release 1.4.4
     */
    public static function endOfMonthBySpan($months = 0, $month = 0, $year = 0, $format = DATE_CALC_FORMAT): string
    {
        if (empty($year)) {
            $year = self::dateNow('%Y');
        }
        if (empty($month)) {
            $month = self::dateNow('%m');
        }
        if ($months > 0) {
            // future month
            $tmp_mo = $month + $months;
            $month = $tmp_mo % 12;
            if ($month === 0) {
                $month = 12;
                $year += floor(($tmp_mo - 1) / 12);
            } else {
                $year += floor($tmp_mo / 12);
            }
        } else {
            // past || present month
            $tmp_mo = $month + $months;
            if ($tmp_mo > 0) {
                // same year
                $month = $tmp_mo;
            } elseif ($tmp_mo === 0) {
                // prior dec
                $month = 12;
                $year--;
            } else {
                // some time in a prior year
                $month = 12 + ($tmp_mo % 12);
                $year += floor($tmp_mo / 12);
            }
        }
        return self::dateFormat(
            self::daysInMonth($month, $year),
            $month,
            $year,
            $format
        );
    }

    /**
     * Find the day of the week for the first of the month of given date
     *
     * @param int $month
     * @param int $year
     *
     * @return int number of weekday for the first day, 0=Sunday
     *
     * @static
     */
    public static function firstOfMonthWeekday($month = 0, $year = 0): int
    {
        return (int)self::createDateTime(1, $month, $year)->format('w');
    }

    /**
     * Returns true for valid date, false for invalid date
     *
     * @param int $day
     * @param int $month
     * @param int $year
     *
     * @return bool
     *
     * @static
     */
    public static function isValidDate($day, $month, $year): bool
    {
        return checkdate($month, $day, $year);
    }

    /**
     * Returns true for a leap year, else false
     *
     * @param int $year
     *
     * @return bool
     *
     * @static
     */
    public static function isLeapYear(int $year = 0): bool
    {
        return (bool)self::createDateTime(1, 1, $year)->format('L');
    }

    /**
     * Returns number of days between two given dates
     *
     * @param int $day1
     * @param int $month1
     * @param int $year1
     * @param int $day2
     * @param int $month2
     * @param int $year2
     *
     * @return int the absolute number of days between the two dates.
     *
     * @static
     */
    public static function dateDiff($day1, $month1, $year1, $day2, $month2, $year2): int
    {
        $datetime1 = self::createDateTime($day1, $month1, $year1);
        $datetime2 = self::createDateTime($day2, $month2, $year2);
        $interval = $datetime1->diff($datetime2);
        return (int)$interval->format('%a');
    }

    /**
     * @param int $day
     * @param int $month
     * @param int $year
     * @return DateTime
     */
    protected static function createDateTime(int $day = 1, int $month = 1, int $year = 1): DateTime
    {
        return new DateTime($year . '-' . $month . '-' . $day);
    }
}
