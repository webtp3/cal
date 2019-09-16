<?php
declare(strict_types = 1);

/*
 * This file is part of the web-tp3/cal.
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace TYPO3\CMS\Cal\Service;

use DateInterval;
use DateTime;
use Exception;

/**
 * This class offers a couple of helpful DateTime calculation
 * functions.
 */
class CalculateDateTimeService
{
    /**
     * @param DateTime $dateTime
     * @return DateTime
     */
    public static function calculateStartOfDay(DateTime $dateTime): DateTime
    {
        $dateTime->modify('0:00:00');
        return $dateTime;
    }

    /**
     * @param DateTime $dateTime
     * @return DateTime
     */
    public static function calculateEndOfDay(DateTime $dateTime): DateTime
    {
        $dateTime->modify('23:59:59');
        return $dateTime;
    }

    /**
     * @param DateTime $dateTime
     * @param int $startDayOfWeek 0 = Sunday, 1 = Monday
     * @return DateTime
     */
    public static function calculateStartOfWeek(DateTime $dateTime, int $startDayOfWeek = 1): DateTime
    {
        $dateTime->modify('Monday this week 0:00:00');
        return $dateTime;
    }

    /**
     * @param DateTime $dateTime
     * @param int $startDayOfWeek 0 = Sunday, 1 = Monday
     * @return DateTime
     */
    public static function calculateEndOfWeek(DateTime $dateTime, int $startDayOfWeek = 1): DateTime
    {
        $dateTime->modify('Sunday this week 23:59:59');
        return $dateTime;
    }

    /**
     * @param DateTime $dateTime
     * @param int $startDayOfWeek 0 = Sunday, 1 = Monday
     * @return DateTime
     */
    public static function calculateStartOfNextWeek(DateTime $dateTime, int $startDayOfWeek = 1): DateTime
    {
        $dateTime->modify('Monday next week 0:00:00');
        return $dateTime;
    }

    /**
     * @param DateTime $dateTime
     * @param int $startDayOfWeek 0 = Sunday, 1 = Monday
     * @return DateTime
     */
    public static function calculateEndOfNextWeek(DateTime $dateTime, int $startDayOfWeek = 1): DateTime
    {
        $dateTime->modify('Sunday next week 23:59:59');
        return $dateTime;
    }

    /**
     * @param DateTime $dateTime
     * @param int $startDayOfWeek 0 = Sunday, 1 = Monday
     * @return DateTime
     */
    public static function calculateStartOfLastWeek(DateTime $dateTime, int $startDayOfWeek = 1): DateTime
    {
        $dateTime->modify('Monday last week 0:00:00');
        return $dateTime;
    }

    /**
     * @param DateTime $dateTime
     * @param int $startDayOfWeek 0 = Sunday, 1 = Monday
     * @return DateTime
     */
    public static function calculateEndOfLastWeek(DateTime $dateTime, int $startDayOfWeek = 1): DateTime
    {
        $dateTime->modify('Sunday last week 23:59:59');
        return $dateTime;
    }

    /**
     * @param string $tcaString
     * @param DateTime $dateTime
     * @return DateTime
     */
    public static function parseTcaString(string $tcaString, DateTime $dateTime = null): DateTime
    {
        if ($dateTime === null) {
            $dateTime = new DateTime();
        }
        switch ($tcaString) {
            case '+1 month':
                $dateTime->modify('+1 month');
                break;
            case '+1 week':
                $dateTime->modify('+1 week');
                break;
            case '+1 year':
                $dateTime->modify('+1 year');
                break;
            case 'cal:monthend':
                $dateTime = self::calculateEndOfMonth($dateTime);
                break;
            case 'cal:monthstart':
                $dateTime = self::calculateStartOfMonth($dateTime);
                break;
            case 'cal:quarterend':
                $dateTime = self::calculateEndOfQuarter($dateTime);
                break;
            case 'cal:quarterstart':
                $dateTime = self::calculateStartOfQuarter($dateTime);
                break;
            case 'cal:today':
                $dateTime = self::calculateStartOfDay($dateTime);
                break;
            case 'cal:tomorrow':
                $dateTime = self::calculateStartOfDay($dateTime->modify('tomorrow'));
                break;
            case 'cal:weekend':
                $dateTime = self::calculateEndOfWeek($dateTime);
                break;
            case 'cal:weekstart':
                $dateTime = self::calculateStartOfWeek($dateTime);
                break;
            case 'cal:yearend':
                $dateTime = self::calculateEndOfYear($dateTime);
                break;
            case 'cal:yearstart':
                $dateTime = self::calculateStartOfYear($dateTime);
                break;
            case 'cal:yesterday':
                $dateTime = self::calculateStartOfDay($dateTime->modify('yesterday'));
                break;
            case '':
            case 'now':
            default:
                // Do nothing, keep the DateTime element
        }
        return $dateTime;
    }

    /**
     * @param DateTime $dateTime
     * @return DateTime
     */
    public static function calculateStartOfMonth(DateTime $dateTime): DateTime
    {
        $dateTime = self::calculateStartOfDay($dateTime);
        $dateTime = self::setDay($dateTime, 1);
        return $dateTime;
    }

    /**
     * @param DateTime $dateTime
     * @return DateTime
     */
    public static function calculateEndOfMonth(DateTime $dateTime): DateTime
    {
        $dateTime = self::calculateEndOfDay($dateTime);
        $dateTime = self::setDay($dateTime, (int)$dateTime->format('t'));
        return $dateTime;
    }

    /**
     * @param DateTime $dateTime
     * @return DateTime
     */
    public static function calculateStartOfNextMonth(DateTime $dateTime): DateTime
    {
        $dateTime->modify('next month 0:00:00');
        $dateTime = self::setDay($dateTime, 1);
        return $dateTime;
    }

    /**
     * @param DateTime $dateTime
     * @return DateTime
     */
    public static function calculateEndOfNextMonth(DateTime $dateTime): DateTime
    {
        $dateTime->modify('next month 23:59:59');
        $dateTime = self::setDay($dateTime, (int)$dateTime->format('t'));
        return $dateTime;
    }

    /**
     * @param DateTime $dateTime
     * @return DateTime
     */
    public static function calculateStartOfLastMonth(DateTime $dateTime): DateTime
    {
        $dateTime->modify('last month 0:00:00');
        $dateTime = self::setDay($dateTime, 1);
        return $dateTime;
    }

    /**
     * @param DateTime $dateTime
     * @return DateTime
     */
    public static function calculateEndOfLastMonth(DateTime $dateTime): DateTime
    {
        $dateTime->modify('last month 23:59:59');
        $dateTime = self::setDay($dateTime, (int)$dateTime->format('t'));
        return $dateTime;
    }

    /**
     * @param DateTime $dateTime
     * @return DateTime
     */
    public static function calculateStartOfYear(DateTime $dateTime): DateTime
    {
        $dateTime = self::calculateStartOfMonth($dateTime);
        $dateTime = self::setMonth($dateTime, 1);
        return $dateTime;
    }

    /**
     * @param DateTime $dateTime
     * @return DateTime
     */
    public static function calculateEndOfYear(DateTime $dateTime): DateTime
    {
        $dateTime = self::calculateStartOfYear($dateTime);
        $dateTime = self::setYear($dateTime, (int)$dateTime->format('Y') + 1);
        $dateTime = self::subtractSeconds($dateTime, 1);
        return $dateTime;
    }

    /**
     * @param DateTime $dateTime
     * @return DateTime
     */
    public static function calculateStartOfNextYear(DateTime $dateTime): DateTime
    {
        $dateTime->modify('next year');
        $dateTime = self::calculateStartOfYear($dateTime);
        return $dateTime;
    }

    /**
     * @param DateTime $dateTime
     * @return DateTime
     */
    public static function calculateEndOfNextYear(DateTime $dateTime): DateTime
    {
        $dateTime->modify('next year 23:59:59');
        $dateTime = self::calculateEndOfYear($dateTime);
        return $dateTime;
    }

    /**
     * @param DateTime $dateTime
     * @return DateTime
     */
    public static function calculateStartOfLastYear(DateTime $dateTime): DateTime
    {
        $dateTime->modify('last year');
        $dateTime = self::calculateStartOfYear($dateTime);
        return $dateTime;
    }

    /**
     * @param DateTime $dateTime
     * @return DateTime
     */
    public static function calculateEndOfLastYear(DateTime $dateTime): DateTime
    {
        $dateTime->modify('last year');
        $dateTime = self::calculateEndOfYear($dateTime);
        return $dateTime;
    }

    /**
     * @param DateTime $dateTime
     * @return int
     */
    public static function calculateQuarter(DateTime $dateTime): int
    {
        $month = $dateTime->format('m');
        switch ($month) {
            case 1:
            case 2:
            case 3:
                $quarter = 1;
                break;
            case 4:
            case 5:
            case 6:
                $quarter = 2;
                break;
            case 7:
            case 8:
            case 9:
                $quarter = 3;
                break;
            case 10:
            case 11:
            case 12:
                $quarter = 4;
                break;
        }
        return $quarter;
    }

    /**
     * @param DateTime $dateTime
     * @return DateTime
     */
    public static function calculateStartOfQuarter(DateTime $dateTime): DateTime
    {
        $quarter = self::calculateQuarter($dateTime);
        $month = ($quarter - 1) * 3 + 1;
        $dateTime = self::setMonth($dateTime, $month);
        $dateTime = self::calculateStartOfMonth($dateTime);
        return $dateTime;
    }

    /**
     * @param DateTime $dateTime
     * @return DateTime
     */
    public static function calculateEndOfQuarter(DateTime $dateTime): DateTime
    {
        $quarter = self::calculateQuarter($dateTime);
        $month = $quarter * 3;
        $dateTime = self::setMonth($dateTime, $month);
        $dateTime = self::calculateEndOfMonth($dateTime);
        return $dateTime;
    }

    /**
     * @param DateTime $dateTime
     * @param int $sec
     * @return DateTime
     * @throws Exception
     */
    public static function subtractSeconds(DateTime $dateTime, int $sec): DateTime
    {
        if ($sec < 0) {
            return self::addSeconds($dateTime, abs($sec));
        }
        $dateTime->sub(new DateInterval('PT' . $sec . 'S'));
        return $dateTime;
    }

    /**
     * @param DateTime $dateTime
     * @param int $sec
     * @return DateTime
     * @throws Exception
     */
    public static function addSeconds(DateTime $dateTime, int $sec): DateTime
    {
        if ($sec < 0) {
            return self::subtractSeconds($dateTime, abs($sec));
        }
        $dateTime->add(new DateInterval('PT' . $sec . 'S'));
        return $dateTime;
    }

    /**
     * @param int $year
     * @param int $month
     * @param int $day
     * @param int $hour
     * @param int $minutes
     * @param int $second
     * @return DateTime
     */
    public static function generateDateTime(
        int $year,
        int $month,
        int $day,
        int $hour = 0,
        int $minutes = 0,
        int $second = 0
    ): DateTime {
        $dateTime = new DateTime();
        $dateTime = self::setYear($dateTime, $year);
        $dateTime = self::setMonth($dateTime, $month);
        $dateTime = self::setDay($dateTime, $day);
        $dateTime = self::setHour($dateTime, $hour);
        $dateTime = self::setMinute($dateTime, $minutes);
        $dateTime = self::setSecond($dateTime, $second);
        return $dateTime;
    }

    /**
     * @param DateTime $dateTime
     * @param int $y
     * @return DateTime
     */
    public static function setYear(DateTime $dateTime, int $y): DateTime
    {
        $dateTime->setDate($y, (int)$dateTime->format('m'), (int)$dateTime->format('d'));
        return $dateTime;
    }

    /**
     * @param DateTime $dateTime
     * @param int $m
     * @return DateTime
     */
    public static function setMonth(DateTime $dateTime, int $m): DateTime
    {
        $dateTime->setDate((int)$dateTime->format('Y'), $m, (int)$dateTime->format('d'));
        return $dateTime;
    }

    /**
     * @param DateTime $dateTime
     * @param int $d
     * @return DateTime
     */
    public static function setDay(DateTime $dateTime, int $d): DateTime
    {
        $dateTime->setDate((int)$dateTime->format('Y'), (int)$dateTime->format('m'), $d);
        return $dateTime;
    }

    /**
     * @param DateTime $dateTime
     * @param int $h
     * @return DateTime
     */
    public static function setHour(DateTime $dateTime, int $h): DateTime
    {
        $dateTime->setTime($h, (int)$dateTime->format('i'), (int)$dateTime->format('s'));
        return $dateTime;
    }

    /**
     * @param DateTime $dateTime
     * @param int $m
     * @return DateTime
     */
    public static function setMinute(DateTime $dateTime, int $m): DateTime
    {
        $dateTime->setTime((int)$dateTime->format('H'), $m, (int)$dateTime->format('s'));
        return $dateTime;
    }

    /**
     * @param DateTime $dateTime
     * @param int $s
     * @return DateTime
     */
    public static function setSecond(DateTime $dateTime, int $s): DateTime
    {
        $dateTime->setTime((int)$dateTime->format('H'), (int)$dateTime->format('i'), $s);
        return $dateTime;
    }

    /**
     * @param DateTime $dateTime
     * @param string $timeZone
     * @return DateTime
     */
    public static function setTZbyID(DateTime $dateTime, string $timeZone): DateTime
    {
        $dateTime->setTimezone(new \DateTimeZone($timeZone));
        return $dateTime;
    }
}
