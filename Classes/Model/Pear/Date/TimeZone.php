<?php

/*
 * This file is part of the web-tp3/cal.
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace TYPO3\CMS\Cal\Model\Pear\Date;

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4 foldmethod=marker: */

// {{{ Header

/**
 * TimeZone representation class, along with time zone information data
 *
 * PHP versions 4 and 5
 *
 * LICENSE:
 *
 * Copyright (c) 1997-2006 Baba Buehler, Pierre-Alain Joye
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted under the terms of the BSD License.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @category Date and Time
 * @copyright 1997-2006 Baba Buehler, Pierre-Alain Joye
 * @license http://www.opensource.org/licenses/bsd-license.php
 *          BSD License
 * @version CVS: $Id: TimeZone.php,v 1.14 2006/11/22 01:03:12 firman Exp $
 * @link http://pear.php.net/package/Date
 */

// }}}
// {{{ Class: TimeZone

/**
 * TimeZone representation class, along with time zone information data
 *
 * The default timezone is set from the first valid timezone id found
 * in one of the following places, in this order:
 * + global $_DATE_TIMEZONE_DEFAULT
 * + system environment variable PHP_TZ
 * + system environment variable TZ
 * + the result of date('T')
 *
 * If no valid timezone id is found, the default timezone is set to 'UTC'.
 * You may also manually set the default timezone by passing a valid id to
 * TimeZone::setDefault().
 *
 * This class includes time zone data (from zoneinfo) in the form of a
 * global array, $_DATE_TIMEZONE_DATA.
 *
 * @copyright 1997-2006 Baba Buehler, Pierre-Alain Joye
 * @license http://www.opensource.org/licenses/bsd-license.php
 *          BSD License
 * @version Release: 1.4.7
 * @link http://pear.php.net/package/Date
 */
class TimeZone
{
    // {{{ Properties

    /**
     * Time Zone ID of this time zone
     *
     * @var string
     */
    public $id;

    /**
     * Long Name of this time zone (ie Central Standard Time)
     *
     * @var string
     */
    public $longname;

    /**
     * Short Name of this time zone (ie CST)
     *
     * @var string
     */
    public $shortname;

    /**
     * true if this time zone observes daylight savings time
     *
     * @var bool
     */
    public $hasdst;

    /**
     * DST Long Name of this time zone
     *
     * @var string
     */
    public $dstlongname;

    /**
     * DST Short Name of this timezone
     *
     * @var string
     */
    public $dstshortname;

    /**
     * offset, in milliseconds, of this timezone
     *
     * @var int
     */
    public $offset;

    /**
     * System Default Time Zone
     *
     * @var object TimeZone
     */
    public $default;

    // }}}
    // {{{ Constructor

    /**
     * Constructor
     *
     * Creates a new Date::TimeZone object, representing the time zone
     * specified in $id. If the supplied ID is invalid, the created
     * time zone is UTC.
     *
     * @access public
     * @param string $id
     *        	the time zone id
     * @return object TimeZone the new TimeZone object
     */
    public function __construct($id)
    {
        $_DATE_TIMEZONE_DATA = & $GLOBALS ['_DATE_TIMEZONE_DATA'];
        if (self::isValidID($id)) {
            $this->id = $id;
            $this->longname = $_DATE_TIMEZONE_DATA [$id] ['longname'];
            $this->shortname = $_DATE_TIMEZONE_DATA [$id] ['shortname'];
            $this->offset = $_DATE_TIMEZONE_DATA [$id] ['offset'];
            if ($_DATE_TIMEZONE_DATA [$id] ['hasdst']) {
                $this->hasdst = true;
                $this->dstlongname = $_DATE_TIMEZONE_DATA [$id] ['dstlongname'];
                $this->dstshortname = $_DATE_TIMEZONE_DATA [$id] ['dstshortname'];
            } else {
                $this->hasdst = false;
                $this->dstlongname = $this->longname;
                $this->dstshortname = $this->shortname;
            }
        } else {
            $this->id = 'UTC';
            $this->longname = $_DATE_TIMEZONE_DATA [$this->id] ['longname'];
            $this->shortname = $_DATE_TIMEZONE_DATA [$this->id] ['shortname'];
            $this->hasdst = $_DATE_TIMEZONE_DATA [$this->id] ['hasdst'];
            $this->offset = $_DATE_TIMEZONE_DATA [$this->id] ['offset'];
        }
    }

    // }}}
    // {{{ getDefault()

    /**
     * Return a TimeZone object representing the system default time zone
     *
     * Return a TimeZone object representing the system default time zone,
     * which is initialized during the loading of TimeZone.php.
     *
     * @access public
     * @return object TimeZone the default time zone
     */
    public static function getDefault()
    {
        return new self($GLOBALS ['_DATE_TIMEZONE_DEFAULT']);
    }

    // }}}
    // {{{ setDefault()

    /**
     * Sets the system default time zone to the time zone in $id
     *
     * Sets the system default time zone to the time zone in $id
     *
     * @access public
     * @param string $id
     *        	the time zone id to use
     */
    public static function setDefault($id)
    {
        if (self::isValidID($id)) {
            $GLOBALS ['_DATE_TIMEZONE_DEFAULT'] = $id;
        }
    }

    // }}}
    // {{{ isValidID()

    /**
     * Tests if given id is represented in the $_DATE_TIMEZONE_DATA time zone data
     *
     * Tests if given id is represented in the $_DATE_TIMEZONE_DATA time zone data
     *
     * @access public
     * @param string $id
     *        	the id to test
     * @return bool true if the supplied ID is valid
     */
    public static function isValidID($id)
    {
        if (isset($GLOBALS ['_DATE_TIMEZONE_DATA'] [$id])) {
            return true;
        } else {
            return false;
        }
    }

    // }}}
    // {{{ isEqual()

    /**
     * Is this time zone equal to another
     *
     * Tests to see if this time zone is equal (ids match)
     * to a given TimeZone object.
     *
     * @access public
     * @param
     *        	object TimeZone $tz the timezone to test
     * @return bool true if this time zone is equal to the supplied time zone
     */
    public function isEqual($tz)
    {
        if (strcasecmp($this->id, $tz->id) == 0) {
            return true;
        } else {
            return false;
        }
    }

    // }}}
    // {{{ isEquivalent()

    /**
     * Is this time zone equivalent to another
     *
     * Tests to see if this time zone is equivalent to
     * a given time zone object. Equivalence in this context
     * is defined by the two time zones having an equal raw
     * offset and an equal setting of "hasdst". This is not true
     * equivalence, as the two time zones may have different rules
     * for the observance of DST, but this implementation does not
     * know DST rules.
     *
     * @access public
     * @param
     *        	object TimeZone $tz the timezone object to test
     * @return bool true if this time zone is equivalent to the supplied time zone
     */
    public function isEquivalent($tz)
    {
        if ($this->offset == $tz->offset && $this->hasdst == $tz->hasdst) {
            return true;
        } else {
            return false;
        }
    }

    // }}}
    // {{{ hasDaylightTime()

    /**
     * Returns true if this zone observes daylight savings time
     *
     * Returns true if this zone observes daylight savings time
     *
     * @access public
     * @return bool true if this time zone has DST
     */
    public function hasDaylightTime()
    {
        return $this->hasdst;
    }

    // }}}
    // {{{ inDaylightTime()

    /**
     * Is the given date/time in DST for this time zone
     *
     * Attempts to determine if a given Date object represents a date/time
     * that is in DST for this time zone. WARNINGS: this basically attempts to
     * "trick" the system into telling us if we're in DST for a given time zone.
     * This uses putenv() which may not work in safe mode, and relies on unix time
     * which is only valid for dates from 1970 to ~2038. This relies on the
     * underlying OS calls, so it may not work on Windows or on a system where
     * zoneinfo is not installed or configured properly.
     *
     * @access public
     * @param
     *        	object Date $date the date/time to test
     * @return bool true if this date is in DST for this time zone
     */
    public function inDaylightTime($date)
    {
        $env_tz = '';
        if (isset($_ENV ['TZ']) && getenv('TZ')) {
            $env_tz = getenv('TZ');
        }

        putenv('TZ=' . $this->id);
        $ltime = localtime($date->getTime(), true);
        if ($env_tz != '') {
            putenv('TZ=' . $env_tz);
        }
        return $ltime ['tm_isdst'];
    }

    // }}}
    // {{{ getDSTSavings()

    /**
     * Get the DST offset for this time zone
     *
     * Returns the DST offset of this time zone, in milliseconds,
     * if the zone observes DST, zero otherwise. Currently the
     * DST offset is hard-coded to one hour.
     *
     * @access public
     * @return int the DST offset, in milliseconds or zero if the zone does not observe DST
     */
    public function getDSTSavings()
    {
        if ($this->hasdst) {
            return 3600000;
        } else {
            return 0;
        }
    }

    // }}}
    // {{{ getOffset()

    /**
     * Get the DST-corrected offset to UTC for the given date
     *
     * Attempts to get the offset to UTC for a given date/time, taking into
     * account daylight savings time, if the time zone observes it and if
     * it is in effect. Please see the WARNINGS on Date::TimeZone::inDaylightTime().
     *
     *
     * @access public
     * @param
     *        	object Date $date the Date to test
     * @return int the corrected offset to UTC in milliseconds
     */
    public function getOffset($date)
    {
        if ($this->inDaylightTime($date)) {
            return $this->offset + $this->getDSTSavings();
        } else {
            return $this->offset;
        }
    }

    // }}}
    // {{{ getAvailableIDs()

    /**
     * Returns the list of valid time zone id strings
     *
     * Returns the list of valid time zone id strings
     *
     * @access public
     * @return mixed an array of strings with the valid time zone IDs
     */
    public function getAvailableIDs()
    {
        return array_keys($GLOBALS ['_DATE_TIMEZONE_DATA']);
    }

    // }}}
    // {{{ getID()

    /**
     * Returns the id for this time zone
     *
     * Returns the time zone id for this time zone, i.e. "America/Chicago"
     *
     * @access public
     * @return string the id
     */
    public function getID()
    {
        return $this->id;
    }

    // }}}
    // {{{ getLongName()

    /**
     * Returns the long name for this time zone
     *
     * Returns the long name for this time zone,
     * i.e. "Central Standard Time"
     *
     * @access public
     * @return string the long name
     */
    public function getLongName()
    {
        return $this->longname;
    }

    // }}}
    // {{{ getShortName()

    /**
     * Returns the short name for this time zone
     *
     * Returns the short name for this time zone, i.e. "CST"
     *
     * @access public
     * @return string the short name
     */
    public function getShortName()
    {
        return $this->shortname;
    }

    // }}}
    // {{{ getDSTLongName()

    /**
     * Returns the DST long name for this time zone
     *
     * Returns the DST long name for this time zone, i.e. "Central Daylight Time"
     *
     * @access public
     * @return string the daylight savings time long name
     */
    public function getDSTLongName()
    {
        return $this->dstlongname;
    }

    // }}}
    // {{{ getDSTShortName()

    /**
     * Returns the DST short name for this time zone
     *
     * Returns the DST short name for this time zone, i.e. "CDT"
     *
     * @access public
     * @return string the daylight savings time short name
     */
    public function getDSTShortName()
    {
        return $this->dstshortname;
    }

    // }}}
    // {{{ getRawOffset()

    /**
     * Returns the raw (non-DST-corrected) offset from UTC/GMT for this time zone
     *
     * Returns the raw (non-DST-corrected) offset from UTC/GMT for this time zone
     *
     * @access public
     * @return int the offset, in milliseconds
     */
    public function getRawOffset()
    {
        return $this->offset;
    }

    // }}}
}

// }}}

/**
 * Time Zone Data offset is in miliseconds
 *
 * @global array $GLOBALS['_DATE_TIMEZONE_DATA']
 */
$GLOBALS ['_DATE_TIMEZONE_DATA'] = [
        'Etc/GMT+12' => [
                'offset' => - 43200000,
                'longname' => 'GMT-12:00',
                'shortname' => 'GMT-12:00',
                'hasdst' => false
        ],
        'Etc/GMT+11' => [
                'offset' => - 39600000,
                'longname' => 'GMT-11:00',
                'shortname' => 'GMT-11:00',
                'hasdst' => false
        ],
        'MIT' => [
                'offset' => - 39600000,
                'longname' => 'West Samoa Time',
                'shortname' => 'WST',
                'hasdst' => false
        ],
        'Pacific/Apia' => [
                'offset' => - 39600000,
                'longname' => 'West Samoa Time',
                'shortname' => 'WST',
                'hasdst' => false
        ],
        'Pacific/Midway' => [
                'offset' => - 39600000,
                'longname' => 'Samoa Standard Time',
                'shortname' => 'SST',
                'hasdst' => false
        ],
        'Pacific/Niue' => [
                'offset' => - 39600000,
                'longname' => 'Niue Time',
                'shortname' => 'NUT',
                'hasdst' => false
        ],
        'Pacific/Pago_Pago' => [
                'offset' => - 39600000,
                'longname' => 'Samoa Standard Time',
                'shortname' => 'SST',
                'hasdst' => false
        ],
        'Pacific/Samoa' => [
                'offset' => - 39600000,
                'longname' => 'Samoa Standard Time',
                'shortname' => 'SST',
                'hasdst' => false
        ],
        'US/Samoa' => [
                'offset' => - 39600000,
                'longname' => 'Samoa Standard Time',
                'shortname' => 'SST',
                'hasdst' => false
        ],
        'America/Adak' => [
                'offset' => - 36000000,
                'longname' => 'Hawaii-Aleutian Standard Time',
                'shortname' => 'HAST',
                'hasdst' => true,
                'dstlongname' => 'Hawaii-Aleutian Daylight Time',
                'dstshortname' => 'HADT'
        ],
        'America/Atka' => [
                'offset' => - 36000000,
                'longname' => 'Hawaii-Aleutian Standard Time',
                'shortname' => 'HAST',
                'hasdst' => true,
                'dstlongname' => 'Hawaii-Aleutian Daylight Time',
                'dstshortname' => 'HADT'
        ],
        'Etc/GMT+10' => [
                'offset' => - 36000000,
                'longname' => 'GMT-10:00',
                'shortname' => 'GMT-10:00',
                'hasdst' => false
        ],
        'HST' => [
                'offset' => - 36000000,
                'longname' => 'Hawaii Standard Time',
                'shortname' => 'HST',
                'hasdst' => false
        ],
        'Pacific/Fakaofo' => [
                'offset' => - 36000000,
                'longname' => 'Tokelau Time',
                'shortname' => 'TKT',
                'hasdst' => false
        ],
        'Pacific/Honolulu' => [
                'offset' => - 36000000,
                'longname' => 'Hawaii Standard Time',
                'shortname' => 'HST',
                'hasdst' => false
        ],
        'Pacific/Johnston' => [
                'offset' => - 36000000,
                'longname' => 'Hawaii Standard Time',
                'shortname' => 'HST',
                'hasdst' => false
        ],
        'Pacific/Rarotonga' => [
                'offset' => - 36000000,
                'longname' => 'Cook Is. Time',
                'shortname' => 'CKT',
                'hasdst' => false
        ],
        'Pacific/Tahiti' => [
                'offset' => - 36000000,
                'longname' => 'Tahiti Time',
                'shortname' => 'TAHT',
                'hasdst' => false
        ],
        'SystemV/HST10' => [
                'offset' => - 36000000,
                'longname' => 'Hawaii Standard Time',
                'shortname' => 'HST',
                'hasdst' => false
        ],
        'US/Aleutian' => [
                'offset' => - 36000000,
                'longname' => 'Hawaii-Aleutian Standard Time',
                'shortname' => 'HAST',
                'hasdst' => true,
                'dstlongname' => 'Hawaii-Aleutian Daylight Time',
                'dstshortname' => 'HADT'
        ],
        'US/Hawaii' => [
                'offset' => - 36000000,
                'longname' => 'Hawaii Standard Time',
                'shortname' => 'HST',
                'hasdst' => false
        ],
        'Pacific/Marquesas' => [
                'offset' => - 34200000,
                'longname' => 'Marquesas Time',
                'shortname' => 'MART',
                'hasdst' => false
        ],
        'AST' => [
                'offset' => - 32400000,
                'longname' => 'Alaska Standard Time',
                'shortname' => 'AKST',
                'hasdst' => true,
                'dstlongname' => 'Alaska Daylight Time',
                'dstshortname' => 'AKDT'
        ],
        'America/Anchorage' => [
                'offset' => - 32400000,
                'longname' => 'Alaska Standard Time',
                'shortname' => 'AKST',
                'hasdst' => true,
                'dstlongname' => 'Alaska Daylight Time',
                'dstshortname' => 'AKDT'
        ],
        'America/Juneau' => [
                'offset' => - 32400000,
                'longname' => 'Alaska Standard Time',
                'shortname' => 'AKST',
                'hasdst' => true,
                'dstlongname' => 'Alaska Daylight Time',
                'dstshortname' => 'AKDT'
        ],
        'America/Nome' => [
                'offset' => - 32400000,
                'longname' => 'Alaska Standard Time',
                'shortname' => 'AKST',
                'hasdst' => true,
                'dstlongname' => 'Alaska Daylight Time',
                'dstshortname' => 'AKDT'
        ],
        'America/Yakutat' => [
                'offset' => - 32400000,
                'longname' => 'Alaska Standard Time',
                'shortname' => 'AKST',
                'hasdst' => true,
                'dstlongname' => 'Alaska Daylight Time',
                'dstshortname' => 'AKDT'
        ],
        'Etc/GMT+9' => [
                'offset' => - 32400000,
                'longname' => 'GMT-09:00',
                'shortname' => 'GMT-09:00',
                'hasdst' => false
        ],
        'Pacific/Gambier' => [
                'offset' => - 32400000,
                'longname' => 'Gambier Time',
                'shortname' => 'GAMT',
                'hasdst' => false
        ],
        'SystemV/YST9' => [
                'offset' => - 32400000,
                'longname' => 'Gambier Time',
                'shortname' => 'GAMT',
                'hasdst' => false
        ],
        'SystemV/YST9YDT' => [
                'offset' => - 32400000,
                'longname' => 'Alaska Standard Time',
                'shortname' => 'AKST',
                'hasdst' => true,
                'dstlongname' => 'Alaska Daylight Time',
                'dstshortname' => 'AKDT'
        ],
        'US/Alaska' => [
                'offset' => - 32400000,
                'longname' => 'Alaska Standard Time',
                'shortname' => 'AKST',
                'hasdst' => true,
                'dstlongname' => 'Alaska Daylight Time',
                'dstshortname' => 'AKDT'
        ],
        'America/Dawson' => [
                'offset' => - 28800000,
                'longname' => 'Pacific Standard Time',
                'shortname' => 'PST',
                'hasdst' => true,
                'dstlongname' => 'Pacific Daylight Time',
                'dstshortname' => 'PDT'
        ],
        'America/Ensenada' => [
                'offset' => - 28800000,
                'longname' => 'Pacific Standard Time',
                'shortname' => 'PST',
                'hasdst' => true,
                'dstlongname' => 'Pacific Daylight Time',
                'dstshortname' => 'PDT'
        ],
        'America/Los_Angeles' => [
                'offset' => - 28800000,
                'longname' => 'Pacific Standard Time',
                'shortname' => 'PST',
                'hasdst' => true,
                'dstlongname' => 'Pacific Daylight Time',
                'dstshortname' => 'PDT'
        ],
        'America/Tijuana' => [
                'offset' => - 28800000,
                'longname' => 'Pacific Standard Time',
                'shortname' => 'PST',
                'hasdst' => true,
                'dstlongname' => 'Pacific Daylight Time',
                'dstshortname' => 'PDT'
        ],
        'America/Vancouver' => [
                'offset' => - 28800000,
                'longname' => 'Pacific Standard Time',
                'shortname' => 'PST',
                'hasdst' => true,
                'dstlongname' => 'Pacific Daylight Time',
                'dstshortname' => 'PDT'
        ],
        'America/Whitehorse' => [
                'offset' => - 28800000,
                'longname' => 'Pacific Standard Time',
                'shortname' => 'PST',
                'hasdst' => true,
                'dstlongname' => 'Pacific Daylight Time',
                'dstshortname' => 'PDT'
        ],
        'Canada/Pacific' => [
                'offset' => - 28800000,
                'longname' => 'Pacific Standard Time',
                'shortname' => 'PST',
                'hasdst' => true,
                'dstlongname' => 'Pacific Daylight Time',
                'dstshortname' => 'PDT'
        ],
        'Canada/Yukon' => [
                'offset' => - 28800000,
                'longname' => 'Pacific Standard Time',
                'shortname' => 'PST',
                'hasdst' => true,
                'dstlongname' => 'Pacific Daylight Time',
                'dstshortname' => 'PDT'
        ],
        'Etc/GMT+8' => [
                'offset' => - 28800000,
                'longname' => 'GMT-08:00',
                'shortname' => 'GMT-08:00',
                'hasdst' => false
        ],
        'Mexico/BajaNorte' => [
                'offset' => - 28800000,
                'longname' => 'Pacific Standard Time',
                'shortname' => 'PST',
                'hasdst' => true,
                'dstlongname' => 'Pacific Daylight Time',
                'dstshortname' => 'PDT'
        ],
        'PST' => [
                'offset' => - 28800000,
                'longname' => 'Pacific Standard Time',
                'shortname' => 'PST',
                'hasdst' => true,
                'dstlongname' => 'Pacific Daylight Time',
                'dstshortname' => 'PDT'
        ],
        'PST8PDT' => [
                'offset' => - 28800000,
                'longname' => 'Pacific Standard Time',
                'shortname' => 'PST',
                'hasdst' => true,
                'dstlongname' => 'Pacific Daylight Time',
                'dstshortname' => 'PDT'
        ],
        'Pacific/Pitcairn' => [
                'offset' => - 28800000,
                'longname' => 'Pitcairn Standard Time',
                'shortname' => 'PST',
                'hasdst' => false
        ],
        'SystemV/PST8' => [
                'offset' => - 28800000,
                'longname' => 'Pitcairn Standard Time',
                'shortname' => 'PST',
                'hasdst' => false
        ],
        'SystemV/PST8PDT' => [
                'offset' => - 28800000,
                'longname' => 'Pacific Standard Time',
                'shortname' => 'PST',
                'hasdst' => true,
                'dstlongname' => 'Pacific Daylight Time',
                'dstshortname' => 'PDT'
        ],
        'US/Pacific' => [
                'offset' => - 28800000,
                'longname' => 'Pacific Standard Time',
                'shortname' => 'PST',
                'hasdst' => true,
                'dstlongname' => 'Pacific Daylight Time',
                'dstshortname' => 'PDT'
        ],
        'US/Pacific-New' => [
                'offset' => - 28800000,
                'longname' => 'Pacific Standard Time',
                'shortname' => 'PST',
                'hasdst' => true,
                'dstlongname' => 'Pacific Daylight Time',
                'dstshortname' => 'PDT'
        ],
        'America/Boise' => [
                'offset' => - 25200000,
                'longname' => 'Mountain Standard Time',
                'shortname' => 'MST',
                'hasdst' => true,
                'dstlongname' => 'Mountain Daylight Time',
                'dstshortname' => 'MDT'
        ],
        'America/Cambridge_Bay' => [
                'offset' => - 25200000,
                'longname' => 'Mountain Standard Time',
                'shortname' => 'MST',
                'hasdst' => true,
                'dstlongname' => 'Mountain Daylight Time',
                'dstshortname' => 'MDT'
        ],
        'America/Chihuahua' => [
                'offset' => - 25200000,
                'longname' => 'Mountain Standard Time',
                'shortname' => 'MST',
                'hasdst' => true,
                'dstlongname' => 'Mountain Daylight Time',
                'dstshortname' => 'MDT'
        ],
        'America/Dawson_Creek' => [
                'offset' => - 25200000,
                'longname' => 'Mountain Standard Time',
                'shortname' => 'MST',
                'hasdst' => false
        ],
        'America/Denver' => [
                'offset' => - 25200000,
                'longname' => 'Mountain Standard Time',
                'shortname' => 'MST',
                'hasdst' => true,
                'dstlongname' => 'Mountain Daylight Time',
                'dstshortname' => 'MDT'
        ],
        'America/Edmonton' => [
                'offset' => - 25200000,
                'longname' => 'Mountain Standard Time',
                'shortname' => 'MST',
                'hasdst' => true,
                'dstlongname' => 'Mountain Daylight Time',
                'dstshortname' => 'MDT'
        ],
        'America/Hermosillo' => [
                'offset' => - 25200000,
                'longname' => 'Mountain Standard Time',
                'shortname' => 'MST',
                'hasdst' => false
        ],
        'America/Inuvik' => [
                'offset' => - 25200000,
                'longname' => 'Mountain Standard Time',
                'shortname' => 'MST',
                'hasdst' => true,
                'dstlongname' => 'Mountain Daylight Time',
                'dstshortname' => 'MDT'
        ],
        'America/Mazatlan' => [
                'offset' => - 25200000,
                'longname' => 'Mountain Standard Time',
                'shortname' => 'MST',
                'hasdst' => true,
                'dstlongname' => 'Mountain Daylight Time',
                'dstshortname' => 'MDT'
        ],
        'America/Phoenix' => [
                'offset' => - 25200000,
                'longname' => 'Mountain Standard Time',
                'shortname' => 'MST',
                'hasdst' => false
        ],
        'America/Shiprock' => [
                'offset' => - 25200000,
                'longname' => 'Mountain Standard Time',
                'shortname' => 'MST',
                'hasdst' => true,
                'dstlongname' => 'Mountain Daylight Time',
                'dstshortname' => 'MDT'
        ],
        'America/Yellowknife' => [
                'offset' => - 25200000,
                'longname' => 'Mountain Standard Time',
                'shortname' => 'MST',
                'hasdst' => true,
                'dstlongname' => 'Mountain Daylight Time',
                'dstshortname' => 'MDT'
        ],
        'Canada/Mountain' => [
                'offset' => - 25200000,
                'longname' => 'Mountain Standard Time',
                'shortname' => 'MST',
                'hasdst' => true,
                'dstlongname' => 'Mountain Daylight Time',
                'dstshortname' => 'MDT'
        ],
        'Etc/GMT+7' => [
                'offset' => - 25200000,
                'longname' => 'GMT-07:00',
                'shortname' => 'GMT-07:00',
                'hasdst' => false
        ],
        'MST' => [
                'offset' => - 25200000,
                'longname' => 'Mountain Standard Time',
                'shortname' => 'MST',
                'hasdst' => true,
                'dstlongname' => 'Mountain Daylight Time',
                'dstshortname' => 'MDT'
        ],
        'MST7MDT' => [
                'offset' => - 25200000,
                'longname' => 'Mountain Standard Time',
                'shortname' => 'MST',
                'hasdst' => true,
                'dstlongname' => 'Mountain Daylight Time',
                'dstshortname' => 'MDT'
        ],
        'Mexico/BajaSur' => [
                'offset' => - 25200000,
                'longname' => 'Mountain Standard Time',
                'shortname' => 'MST',
                'hasdst' => true,
                'dstlongname' => 'Mountain Daylight Time',
                'dstshortname' => 'MDT'
        ],
        'Navajo' => [
                'offset' => - 25200000,
                'longname' => 'Mountain Standard Time',
                'shortname' => 'MST',
                'hasdst' => true,
                'dstlongname' => 'Mountain Daylight Time',
                'dstshortname' => 'MDT'
        ],
        'PNT' => [
                'offset' => - 25200000,
                'longname' => 'Mountain Standard Time',
                'shortname' => 'MST',
                'hasdst' => false
        ],
        'SystemV/MST7' => [
                'offset' => - 25200000,
                'longname' => 'Mountain Standard Time',
                'shortname' => 'MST',
                'hasdst' => false
        ],
        'SystemV/MST7MDT' => [
                'offset' => - 25200000,
                'longname' => 'Mountain Standard Time',
                'shortname' => 'MST',
                'hasdst' => true,
                'dstlongname' => 'Mountain Daylight Time',
                'dstshortname' => 'MDT'
        ],
        'US/Arizona' => [
                'offset' => - 25200000,
                'longname' => 'Mountain Standard Time',
                'shortname' => 'MST',
                'hasdst' => false
        ],
        'US/Mountain' => [
                'offset' => - 25200000,
                'longname' => 'Mountain Standard Time',
                'shortname' => 'MST',
                'hasdst' => true,
                'dstlongname' => 'Mountain Daylight Time',
                'dstshortname' => 'MDT'
        ],
        'America/Belize' => [
                'offset' => - 21600000,
                'longname' => 'Central Standard Time',
                'shortname' => 'CST',
                'hasdst' => false
        ],
        'America/Cancun' => [
                'offset' => - 21600000,
                'longname' => 'Central Standard Time',
                'shortname' => 'CST',
                'hasdst' => true,
                'dstlongname' => 'Central Daylight Time',
                'dstshortname' => 'CDT'
        ],
        'America/Chicago' => [
                'offset' => - 21600000,
                'longname' => 'Central Standard Time',
                'shortname' => 'CST',
                'hasdst' => true,
                'dstlongname' => 'Central Daylight Time',
                'dstshortname' => 'CDT'
        ],
        'America/Costa_Rica' => [
                'offset' => - 21600000,
                'longname' => 'Central Standard Time',
                'shortname' => 'CST',
                'hasdst' => false
        ],
        'America/El_Salvador' => [
                'offset' => - 21600000,
                'longname' => 'Central Standard Time',
                'shortname' => 'CST',
                'hasdst' => false
        ],
        'America/Guatemala' => [
                'offset' => - 21600000,
                'longname' => 'Central Standard Time',
                'shortname' => 'CST',
                'hasdst' => false
        ],
        'America/Managua' => [
                'offset' => - 21600000,
                'longname' => 'Central Standard Time',
                'shortname' => 'CST',
                'hasdst' => false
        ],
        'America/Menominee' => [
                'offset' => - 21600000,
                'longname' => 'Central Standard Time',
                'shortname' => 'CST',
                'hasdst' => true,
                'dstlongname' => 'Central Daylight Time',
                'dstshortname' => 'CDT'
        ],
        'America/Merida' => [
                'offset' => - 21600000,
                'longname' => 'Central Standard Time',
                'shortname' => 'CST',
                'hasdst' => true,
                'dstlongname' => 'Central Daylight Time',
                'dstshortname' => 'CDT'
        ],
        'America/Mexico_City' => [
                'offset' => - 21600000,
                'longname' => 'Central Standard Time',
                'shortname' => 'CST',
                'hasdst' => false
        ],
        'America/Monterrey' => [
                'offset' => - 21600000,
                'longname' => 'Central Standard Time',
                'shortname' => 'CST',
                'hasdst' => true,
                'dstlongname' => 'Central Daylight Time',
                'dstshortname' => 'CDT'
        ],
        'America/North_Dakota/Center' => [
                'offset' => - 21600000,
                'longname' => 'Central Standard Time',
                'shortname' => 'CST',
                'hasdst' => true,
                'dstlongname' => 'Central Daylight Time',
                'dstshortname' => 'CDT'
        ],
        'America/Rainy_River' => [
                'offset' => - 21600000,
                'longname' => 'Central Standard Time',
                'shortname' => 'CST',
                'hasdst' => true,
                'dstlongname' => 'Central Daylight Time',
                'dstshortname' => 'CDT'
        ],
        'America/Rankin_Inlet' => [
                'offset' => - 21600000,
                'longname' => 'Eastern Standard Time',
                'shortname' => 'EST',
                'hasdst' => true,
                'dstlongname' => 'Eastern Daylight Time',
                'dstshortname' => 'EDT'
        ],
        'America/Regina' => [
                'offset' => - 21600000,
                'longname' => 'Central Standard Time',
                'shortname' => 'CST',
                'hasdst' => false
        ],
        'America/Swift_Current' => [
                'offset' => - 21600000,
                'longname' => 'Central Standard Time',
                'shortname' => 'CST',
                'hasdst' => false
        ],
        'America/Tegucigalpa' => [
                'offset' => - 21600000,
                'longname' => 'Central Standard Time',
                'shortname' => 'CST',
                'hasdst' => false
        ],
        'America/Winnipeg' => [
                'offset' => - 21600000,
                'longname' => 'Central Standard Time',
                'shortname' => 'CST',
                'hasdst' => true,
                'dstlongname' => 'Central Daylight Time',
                'dstshortname' => 'CDT'
        ],
        'CST' => [
                'offset' => - 21600000,
                'longname' => 'Central Standard Time',
                'shortname' => 'CST',
                'hasdst' => true,
                'dstlongname' => 'Central Daylight Time',
                'dstshortname' => 'CDT'
        ],
        'CST6CDT' => [
                'offset' => - 21600000,
                'longname' => 'Central Standard Time',
                'shortname' => 'CST',
                'hasdst' => true,
                'dstlongname' => 'Central Daylight Time',
                'dstshortname' => 'CDT'
        ],
        'Canada/Central' => [
                'offset' => - 21600000,
                'longname' => 'Central Standard Time',
                'shortname' => 'CST',
                'hasdst' => true,
                'dstlongname' => 'Central Daylight Time',
                'dstshortname' => 'CDT'
        ],
        'Canada/East-Saskatchewan' => [
                'offset' => - 21600000,
                'longname' => 'Central Standard Time',
                'shortname' => 'CST',
                'hasdst' => false
        ],
        'Canada/Saskatchewan' => [
                'offset' => - 21600000,
                'longname' => 'Central Standard Time',
                'shortname' => 'CST',
                'hasdst' => false
        ],
        'Chile/EasterIsland' => [
                'offset' => - 21600000,
                'longname' => 'Easter Is. Time',
                'shortname' => 'EAST',
                'hasdst' => true,
                'dstlongname' => 'Easter Is. Summer Time',
                'dstshortname' => 'EASST'
        ],
        'Etc/GMT+6' => [
                'offset' => - 21600000,
                'longname' => 'GMT-06:00',
                'shortname' => 'GMT-06:00',
                'hasdst' => false
        ],
        'Mexico/General' => [
                'offset' => - 21600000,
                'longname' => 'Central Standard Time',
                'shortname' => 'CST',
                'hasdst' => false
        ],
        'Pacific/Easter' => [
                'offset' => - 21600000,
                'longname' => 'Easter Is. Time',
                'shortname' => 'EAST',
                'hasdst' => true,
                'dstlongname' => 'Easter Is. Summer Time',
                'dstshortname' => 'EASST'
        ],
        'Pacific/Galapagos' => [
                'offset' => - 21600000,
                'longname' => 'Galapagos Time',
                'shortname' => 'GALT',
                'hasdst' => false
        ],
        'SystemV/CST6' => [
                'offset' => - 21600000,
                'longname' => 'Central Standard Time',
                'shortname' => 'CST',
                'hasdst' => false
        ],
        'SystemV/CST6CDT' => [
                'offset' => - 21600000,
                'longname' => 'Central Standard Time',
                'shortname' => 'CST',
                'hasdst' => true,
                'dstlongname' => 'Central Daylight Time',
                'dstshortname' => 'CDT'
        ],
        'US/Central' => [
                'offset' => - 21600000,
                'longname' => 'Central Standard Time',
                'shortname' => 'CST',
                'hasdst' => true,
                'dstlongname' => 'Central Daylight Time',
                'dstshortname' => 'CDT'
        ],
        'America/Bogota' => [
                'offset' => - 18000000,
                'longname' => 'Colombia Time',
                'shortname' => 'COT',
                'hasdst' => false
        ],
        'America/Cayman' => [
                'offset' => - 18000000,
                'longname' => 'Eastern Standard Time',
                'shortname' => 'EST',
                'hasdst' => false
        ],
        'America/Detroit' => [
                'offset' => - 18000000,
                'longname' => 'Eastern Standard Time',
                'shortname' => 'EST',
                'hasdst' => true,
                'dstlongname' => 'Eastern Daylight Time',
                'dstshortname' => 'EDT'
        ],
        'America/Eirunepe' => [
                'offset' => - 18000000,
                'longname' => 'Acre Time',
                'shortname' => 'ACT',
                'hasdst' => false
        ],
        'America/Fort_Wayne' => [
                'offset' => - 18000000,
                'longname' => 'Eastern Standard Time',
                'shortname' => 'EST',
                'hasdst' => false
        ],
        'America/Grand_Turk' => [
                'offset' => - 18000000,
                'longname' => 'Eastern Standard Time',
                'shortname' => 'EST',
                'hasdst' => true,
                'dstlongname' => 'Eastern Daylight Time',
                'dstshortname' => 'EDT'
        ],
        'America/Guayaquil' => [
                'offset' => - 18000000,
                'longname' => 'Ecuador Time',
                'shortname' => 'ECT',
                'hasdst' => false
        ],
        'America/Havana' => [
                'offset' => - 18000000,
                'longname' => 'Central Standard Time',
                'shortname' => 'CST',
                'hasdst' => true,
                'dstlongname' => 'Central Daylight Time',
                'dstshortname' => 'CDT'
        ],
        'America/Indiana/Indianapolis' => [
                'offset' => - 18000000,
                'longname' => 'Eastern Standard Time',
                'shortname' => 'EST',
                'hasdst' => false
        ],
        'America/Indiana/Knox' => [
                'offset' => - 18000000,
                'longname' => 'Eastern Standard Time',
                'shortname' => 'EST',
                'hasdst' => false
        ],
        'America/Indiana/Marengo' => [
                'offset' => - 18000000,
                'longname' => 'Eastern Standard Time',
                'shortname' => 'EST',
                'hasdst' => false
        ],
        'America/Indiana/Vevay' => [
                'offset' => - 18000000,
                'longname' => 'Eastern Standard Time',
                'shortname' => 'EST',
                'hasdst' => false
        ],
        'America/Indianapolis' => [
                'offset' => - 18000000,
                'longname' => 'Eastern Standard Time',
                'shortname' => 'EST',
                'hasdst' => false
        ],
        'America/Iqaluit' => [
                'offset' => - 18000000,
                'longname' => 'Eastern Standard Time',
                'shortname' => 'EST',
                'hasdst' => true,
                'dstlongname' => 'Eastern Daylight Time',
                'dstshortname' => 'EDT'
        ],
        'America/Jamaica' => [
                'offset' => - 18000000,
                'longname' => 'Eastern Standard Time',
                'shortname' => 'EST',
                'hasdst' => false
        ],
        'America/Kentucky/Louisville' => [
                'offset' => - 18000000,
                'longname' => 'Eastern Standard Time',
                'shortname' => 'EST',
                'hasdst' => true,
                'dstlongname' => 'Eastern Daylight Time',
                'dstshortname' => 'EDT'
        ],
        'America/Kentucky/Monticello' => [
                'offset' => - 18000000,
                'longname' => 'Eastern Standard Time',
                'shortname' => 'EST',
                'hasdst' => true,
                'dstlongname' => 'Eastern Daylight Time',
                'dstshortname' => 'EDT'
        ],
        'America/Knox_IN' => [
                'offset' => - 18000000,
                'longname' => 'Eastern Standard Time',
                'shortname' => 'EST',
                'hasdst' => false
        ],
        'America/Lima' => [
                'offset' => - 18000000,
                'longname' => 'Peru Time',
                'shortname' => 'PET',
                'hasdst' => false
        ],
        'America/Louisville' => [
                'offset' => - 18000000,
                'longname' => 'Eastern Standard Time',
                'shortname' => 'EST',
                'hasdst' => true,
                'dstlongname' => 'Eastern Daylight Time',
                'dstshortname' => 'EDT'
        ],
        'America/Montreal' => [
                'offset' => - 18000000,
                'longname' => 'Eastern Standard Time',
                'shortname' => 'EST',
                'hasdst' => true,
                'dstlongname' => 'Eastern Daylight Time',
                'dstshortname' => 'EDT'
        ],
        'America/Nassau' => [
                'offset' => - 18000000,
                'longname' => 'Eastern Standard Time',
                'shortname' => 'EST',
                'hasdst' => true,
                'dstlongname' => 'Eastern Daylight Time',
                'dstshortname' => 'EDT'
        ],
        'America/New_York' => [
                'offset' => - 18000000,
                'longname' => 'Eastern Standard Time',
                'shortname' => 'EST',
                'hasdst' => true,
                'dstlongname' => 'Eastern Daylight Time',
                'dstshortname' => 'EDT'
        ],
        'America/Nipigon' => [
                'offset' => - 18000000,
                'longname' => 'Eastern Standard Time',
                'shortname' => 'EST',
                'hasdst' => true,
                'dstlongname' => 'Eastern Daylight Time',
                'dstshortname' => 'EDT'
        ],
        'America/Panama' => [
                'offset' => - 18000000,
                'longname' => 'Eastern Standard Time',
                'shortname' => 'EST',
                'hasdst' => false
        ],
        'America/Pangnirtung' => [
                'offset' => - 18000000,
                'longname' => 'Eastern Standard Time',
                'shortname' => 'EST',
                'hasdst' => true,
                'dstlongname' => 'Eastern Daylight Time',
                'dstshortname' => 'EDT'
        ],
        'America/Port-au-Prince' => [
                'offset' => - 18000000,
                'longname' => 'Eastern Standard Time',
                'shortname' => 'EST',
                'hasdst' => false
        ],
        'America/Porto_Acre' => [
                'offset' => - 18000000,
                'longname' => 'Acre Time',
                'shortname' => 'ACT',
                'hasdst' => false
        ],
        'America/Rio_Branco' => [
                'offset' => - 18000000,
                'longname' => 'Acre Time',
                'shortname' => 'ACT',
                'hasdst' => false
        ],
        'America/Thunder_Bay' => [
                'offset' => - 18000000,
                'longname' => 'Eastern Standard Time',
                'shortname' => 'EST',
                'hasdst' => true,
                'dstlongname' => 'Eastern Daylight Time',
                'dstshortname' => 'EDT'
        ],
        'Brazil/Acre' => [
                'offset' => - 18000000,
                'longname' => 'Acre Time',
                'shortname' => 'ACT',
                'hasdst' => false
        ],
        'Canada/Eastern' => [
                'offset' => - 18000000,
                'longname' => 'Eastern Standard Time',
                'shortname' => 'EST',
                'hasdst' => true,
                'dstlongname' => 'Eastern Daylight Time',
                'dstshortname' => 'EDT'
        ],
        'Cuba' => [
                'offset' => - 18000000,
                'longname' => 'Central Standard Time',
                'shortname' => 'CST',
                'hasdst' => true,
                'dstlongname' => 'Central Daylight Time',
                'dstshortname' => 'CDT'
        ],
        'EST' => [
                'offset' => - 18000000,
                'longname' => 'Eastern Standard Time',
                'shortname' => 'EST',
                'hasdst' => true,
                'dstlongname' => 'Eastern Daylight Time',
                'dstshortname' => 'EDT'
        ],
        'EST5EDT' => [
                'offset' => - 18000000,
                'longname' => 'Eastern Standard Time',
                'shortname' => 'EST',
                'hasdst' => true,
                'dstlongname' => 'Eastern Daylight Time',
                'dstshortname' => 'EDT'
        ],
        'Etc/GMT+5' => [
                'offset' => - 18000000,
                'longname' => 'GMT-05:00',
                'shortname' => 'GMT-05:00',
                'hasdst' => false
        ],
        'IET' => [
                'offset' => - 18000000,
                'longname' => 'Eastern Standard Time',
                'shortname' => 'EST',
                'hasdst' => false
        ],
        'Jamaica' => [
                'offset' => - 18000000,
                'longname' => 'Eastern Standard Time',
                'shortname' => 'EST',
                'hasdst' => false
        ],
        'SystemV/EST5' => [
                'offset' => - 18000000,
                'longname' => 'Eastern Standard Time',
                'shortname' => 'EST',
                'hasdst' => false
        ],
        'SystemV/EST5EDT' => [
                'offset' => - 18000000,
                'longname' => 'Eastern Standard Time',
                'shortname' => 'EST',
                'hasdst' => true,
                'dstlongname' => 'Eastern Daylight Time',
                'dstshortname' => 'EDT'
        ],
        'US/East-Indiana' => [
                'offset' => - 18000000,
                'longname' => 'Eastern Standard Time',
                'shortname' => 'EST',
                'hasdst' => false
        ],
        'US/Eastern' => [
                'offset' => - 18000000,
                'longname' => 'Eastern Standard Time',
                'shortname' => 'EST',
                'hasdst' => true,
                'dstlongname' => 'Eastern Daylight Time',
                'dstshortname' => 'EDT'
        ],
        'US/Indiana-Starke' => [
                'offset' => - 18000000,
                'longname' => 'Eastern Standard Time',
                'shortname' => 'EST',
                'hasdst' => false
        ],
        'US/Michigan' => [
                'offset' => - 18000000,
                'longname' => 'Eastern Standard Time',
                'shortname' => 'EST',
                'hasdst' => true,
                'dstlongname' => 'Eastern Daylight Time',
                'dstshortname' => 'EDT'
        ],
        'America/Anguilla' => [
                'offset' => - 14400000,
                'longname' => 'Atlantic Standard Time',
                'shortname' => 'AST',
                'hasdst' => false
        ],
        'America/Antigua' => [
                'offset' => - 14400000,
                'longname' => 'Atlantic Standard Time',
                'shortname' => 'AST',
                'hasdst' => false
        ],
        'America/Aruba' => [
                'offset' => - 14400000,
                'longname' => 'Atlantic Standard Time',
                'shortname' => 'AST',
                'hasdst' => false
        ],
        'America/Asuncion' => [
                'offset' => - 14400000,
                'longname' => 'Paraguay Time',
                'shortname' => 'PYT',
                'hasdst' => true,
                'dstlongname' => 'Paraguay Summer Time',
                'dstshortname' => 'PYST'
        ],
        'America/Barbados' => [
                'offset' => - 14400000,
                'longname' => 'Atlantic Standard Time',
                'shortname' => 'AST',
                'hasdst' => false
        ],
        'America/Boa_Vista' => [
                'offset' => - 14400000,
                'longname' => 'Amazon Standard Time',
                'shortname' => 'AMT',
                'hasdst' => false
        ],
        'America/Caracas' => [
                'offset' => - 14400000,
                'longname' => 'Venezuela Time',
                'shortname' => 'VET',
                'hasdst' => false
        ],
        'America/Cuiaba' => [
                'offset' => - 14400000,
                'longname' => 'Amazon Standard Time',
                'shortname' => 'AMT',
                'hasdst' => true,
                'dstlongname' => 'Amazon Summer Time',
                'dstshortname' => 'AMST'
        ],
        'America/Curacao' => [
                'offset' => - 14400000,
                'longname' => 'Atlantic Standard Time',
                'shortname' => 'AST',
                'hasdst' => false
        ],
        'America/Dominica' => [
                'offset' => - 14400000,
                'longname' => 'Atlantic Standard Time',
                'shortname' => 'AST',
                'hasdst' => false
        ],
        'America/Glace_Bay' => [
                'offset' => - 14400000,
                'longname' => 'Atlantic Standard Time',
                'shortname' => 'AST',
                'hasdst' => true,
                'dstlongname' => 'Atlantic Daylight Time',
                'dstshortname' => 'ADT'
        ],
        'America/Goose_Bay' => [
                'offset' => - 14400000,
                'longname' => 'Atlantic Standard Time',
                'shortname' => 'AST',
                'hasdst' => true,
                'dstlongname' => 'Atlantic Daylight Time',
                'dstshortname' => 'ADT'
        ],
        'America/Grenada' => [
                'offset' => - 14400000,
                'longname' => 'Atlantic Standard Time',
                'shortname' => 'AST',
                'hasdst' => false
        ],
        'America/Guadeloupe' => [
                'offset' => - 14400000,
                'longname' => 'Atlantic Standard Time',
                'shortname' => 'AST',
                'hasdst' => false
        ],
        'America/Guyana' => [
                'offset' => - 14400000,
                'longname' => 'Guyana Time',
                'shortname' => 'GYT',
                'hasdst' => false
        ],
        'America/Halifax' => [
                'offset' => - 14400000,
                'longname' => 'Atlantic Standard Time',
                'shortname' => 'AST',
                'hasdst' => true,
                'dstlongname' => 'Atlantic Daylight Time',
                'dstshortname' => 'ADT'
        ],
        'America/La_Paz' => [
                'offset' => - 14400000,
                'longname' => 'Bolivia Time',
                'shortname' => 'BOT',
                'hasdst' => false
        ],
        'America/Manaus' => [
                'offset' => - 14400000,
                'longname' => 'Amazon Standard Time',
                'shortname' => 'AMT',
                'hasdst' => false
        ],
        'America/Martinique' => [
                'offset' => - 14400000,
                'longname' => 'Atlantic Standard Time',
                'shortname' => 'AST',
                'hasdst' => false
        ],
        'America/Montserrat' => [
                'offset' => - 14400000,
                'longname' => 'Atlantic Standard Time',
                'shortname' => 'AST',
                'hasdst' => false
        ],
        'America/Port_of_Spain' => [
                'offset' => - 14400000,
                'longname' => 'Atlantic Standard Time',
                'shortname' => 'AST',
                'hasdst' => false
        ],
        'America/Porto_Velho' => [
                'offset' => - 14400000,
                'longname' => 'Amazon Standard Time',
                'shortname' => 'AMT',
                'hasdst' => false
        ],
        'America/Puerto_Rico' => [
                'offset' => - 14400000,
                'longname' => 'Atlantic Standard Time',
                'shortname' => 'AST',
                'hasdst' => false
        ],
        'America/Santiago' => [
                'offset' => - 14400000,
                'longname' => 'Chile Time',
                'shortname' => 'CLT',
                'hasdst' => true,
                'dstlongname' => 'Chile Summer Time',
                'dstshortname' => 'CLST'
        ],
        'America/Santo_Domingo' => [
                'offset' => - 14400000,
                'longname' => 'Atlantic Standard Time',
                'shortname' => 'AST',
                'hasdst' => false
        ],
        'America/St_Kitts' => [
                'offset' => - 14400000,
                'longname' => 'Atlantic Standard Time',
                'shortname' => 'AST',
                'hasdst' => false
        ],
        'America/St_Lucia' => [
                'offset' => - 14400000,
                'longname' => 'Atlantic Standard Time',
                'shortname' => 'AST',
                'hasdst' => false
        ],
        'America/St_Thomas' => [
                'offset' => - 14400000,
                'longname' => 'Atlantic Standard Time',
                'shortname' => 'AST',
                'hasdst' => false
        ],
        'America/St_Vincent' => [
                'offset' => - 14400000,
                'longname' => 'Atlantic Standard Time',
                'shortname' => 'AST',
                'hasdst' => false
        ],
        'America/Thule' => [
                'offset' => - 14400000,
                'longname' => 'Atlantic Standard Time',
                'shortname' => 'AST',
                'hasdst' => false
        ],
        'America/Tortola' => [
                'offset' => - 14400000,
                'longname' => 'Atlantic Standard Time',
                'shortname' => 'AST',
                'hasdst' => false
        ],
        'America/Virgin' => [
                'offset' => - 14400000,
                'longname' => 'Atlantic Standard Time',
                'shortname' => 'AST',
                'hasdst' => false
        ],
        'Antarctica/Palmer' => [
                'offset' => - 14400000,
                'longname' => 'Chile Time',
                'shortname' => 'CLT',
                'hasdst' => true,
                'dstlongname' => 'Chile Summer Time',
                'dstshortname' => 'CLST'
        ],
        'Atlantic/Bermuda' => [
                'offset' => - 14400000,
                'longname' => 'Atlantic Standard Time',
                'shortname' => 'AST',
                'hasdst' => true,
                'dstlongname' => 'Atlantic Daylight Time',
                'dstshortname' => 'ADT'
        ],
        'Atlantic/Stanley' => [
                'offset' => - 14400000,
                'longname' => 'Falkland Is. Time',
                'shortname' => 'FKT',
                'hasdst' => true,
                'dstlongname' => 'Falkland Is. Summer Time',
                'dstshortname' => 'FKST'
        ],
        'Brazil/West' => [
                'offset' => - 14400000,
                'longname' => 'Amazon Standard Time',
                'shortname' => 'AMT',
                'hasdst' => false
        ],
        'Canada/Atlantic' => [
                'offset' => - 14400000,
                'longname' => 'Atlantic Standard Time',
                'shortname' => 'AST',
                'hasdst' => true,
                'dstlongname' => 'Atlantic Daylight Time',
                'dstshortname' => 'ADT'
        ],
        'Chile/Continental' => [
                'offset' => - 14400000,
                'longname' => 'Chile Time',
                'shortname' => 'CLT',
                'hasdst' => true,
                'dstlongname' => 'Chile Summer Time',
                'dstshortname' => 'CLST'
        ],
        'Etc/GMT+4' => [
                'offset' => - 14400000,
                'longname' => 'GMT-04:00',
                'shortname' => 'GMT-04:00',
                'hasdst' => false
        ],
        'PRT' => [
                'offset' => - 14400000,
                'longname' => 'Atlantic Standard Time',
                'shortname' => 'AST',
                'hasdst' => false
        ],
        'SystemV/AST4' => [
                'offset' => - 14400000,
                'longname' => 'Atlantic Standard Time',
                'shortname' => 'AST',
                'hasdst' => false
        ],
        'SystemV/AST4ADT' => [
                'offset' => - 14400000,
                'longname' => 'Atlantic Standard Time',
                'shortname' => 'AST',
                'hasdst' => true,
                'dstlongname' => 'Atlantic Daylight Time',
                'dstshortname' => 'ADT'
        ],
        'America/St_Johns' => [
                'offset' => - 12600000,
                'longname' => 'Newfoundland Standard Time',
                'shortname' => 'NST',
                'hasdst' => true,
                'dstlongname' => 'Newfoundland Daylight Time',
                'dstshortname' => 'NDT'
        ],
        'CNT' => [
                'offset' => - 12600000,
                'longname' => 'Newfoundland Standard Time',
                'shortname' => 'NST',
                'hasdst' => true,
                'dstlongname' => 'Newfoundland Daylight Time',
                'dstshortname' => 'NDT'
        ],
        'Canada/Newfoundland' => [
                'offset' => - 12600000,
                'longname' => 'Newfoundland Standard Time',
                'shortname' => 'NST',
                'hasdst' => true,
                'dstlongname' => 'Newfoundland Daylight Time',
                'dstshortname' => 'NDT'
        ],
        'AGT' => [
                'offset' => - 10800000,
                'longname' => 'Argentine Time',
                'shortname' => 'ART',
                'hasdst' => false
        ],
        'America/Araguaina' => [
                'offset' => - 10800000,
                'longname' => 'Brazil Time',
                'shortname' => 'BRT',
                'hasdst' => true,
                'dstlongname' => 'Brazil Summer Time',
                'dstshortname' => 'BRST'
        ],
        'America/Belem' => [
                'offset' => - 10800000,
                'longname' => 'Brazil Time',
                'shortname' => 'BRT',
                'hasdst' => false
        ],
        'America/Buenos_Aires' => [
                'offset' => - 10800000,
                'longname' => 'Argentine Time',
                'shortname' => 'ART',
                'hasdst' => false
        ],
        'America/Catamarca' => [
                'offset' => - 10800000,
                'longname' => 'Argentine Time',
                'shortname' => 'ART',
                'hasdst' => false
        ],
        'America/Cayenne' => [
                'offset' => - 10800000,
                'longname' => 'French Guiana Time',
                'shortname' => 'GFT',
                'hasdst' => false
        ],
        'America/Cordoba' => [
                'offset' => - 10800000,
                'longname' => 'Argentine Time',
                'shortname' => 'ART',
                'hasdst' => false
        ],
        'America/Fortaleza' => [
                'offset' => - 10800000,
                'longname' => 'Brazil Time',
                'shortname' => 'BRT',
                'hasdst' => true,
                'dstlongname' => 'Brazil Summer Time',
                'dstshortname' => 'BRST'
        ],
        'America/Godthab' => [
                'offset' => - 10800000,
                'longname' => 'Western Greenland Time',
                'shortname' => 'WGT',
                'hasdst' => true,
                'dstlongname' => 'Western Greenland Summer Time',
                'dstshortname' => 'WGST'
        ],
        'America/Jujuy' => [
                'offset' => - 10800000,
                'longname' => 'Argentine Time',
                'shortname' => 'ART',
                'hasdst' => false
        ],
        'America/Maceio' => [
                'offset' => - 10800000,
                'longname' => 'Brazil Time',
                'shortname' => 'BRT',
                'hasdst' => true,
                'dstlongname' => 'Brazil Summer Time',
                'dstshortname' => 'BRST'
        ],
        'America/Mendoza' => [
                'offset' => - 10800000,
                'longname' => 'Argentine Time',
                'shortname' => 'ART',
                'hasdst' => false
        ],
        'America/Miquelon' => [
                'offset' => - 10800000,
                'longname' => 'Pierre & Miquelon Standard Time',
                'shortname' => 'PMST',
                'hasdst' => true,
                'dstlongname' => 'Pierre & Miquelon Daylight Time',
                'dstshortname' => 'PMDT'
        ],
        'America/Montevideo' => [
                'offset' => - 10800000,
                'longname' => 'Uruguay Time',
                'shortname' => 'UYT',
                'hasdst' => false
        ],
        'America/Paramaribo' => [
                'offset' => - 10800000,
                'longname' => 'Suriname Time',
                'shortname' => 'SRT',
                'hasdst' => false
        ],
        'America/Recife' => [
                'offset' => - 10800000,
                'longname' => 'Brazil Time',
                'shortname' => 'BRT',
                'hasdst' => true,
                'dstlongname' => 'Brazil Summer Time',
                'dstshortname' => 'BRST'
        ],
        'America/Rosario' => [
                'offset' => - 10800000,
                'longname' => 'Argentine Time',
                'shortname' => 'ART',
                'hasdst' => false
        ],
        'America/Sao_Paulo' => [
                'offset' => - 10800000,
                'longname' => 'Brazil Time',
                'shortname' => 'BRT',
                'hasdst' => true,
                'dstlongname' => 'Brazil Summer Time',
                'dstshortname' => 'BRST'
        ],
        'BET' => [
                'offset' => - 10800000,
                'longname' => 'Brazil Time',
                'shortname' => 'BRT',
                'hasdst' => true,
                'dstlongname' => 'Brazil Summer Time',
                'dstshortname' => 'BRST'
        ],
        'Brazil/East' => [
                'offset' => - 10800000,
                'longname' => 'Brazil Time',
                'shortname' => 'BRT',
                'hasdst' => true,
                'dstlongname' => 'Brazil Summer Time',
                'dstshortname' => 'BRST'
        ],
        'Etc/GMT+3' => [
                'offset' => - 10800000,
                'longname' => 'GMT-03:00',
                'shortname' => 'GMT-03:00',
                'hasdst' => false
        ],
        'America/Noronha' => [
                'offset' => - 7200000,
                'longname' => 'Fernando de Noronha Time',
                'shortname' => 'FNT',
                'hasdst' => false
        ],
        'Atlantic/South_Georgia' => [
                'offset' => - 7200000,
                'longname' => 'South Georgia Standard Time',
                'shortname' => 'GST',
                'hasdst' => false
        ],
        'Brazil/DeNoronha' => [
                'offset' => - 7200000,
                'longname' => 'Fernando de Noronha Time',
                'shortname' => 'FNT',
                'hasdst' => false
        ],
        'Etc/GMT+2' => [
                'offset' => - 7200000,
                'longname' => 'GMT-02:00',
                'shortname' => 'GMT-02:00',
                'hasdst' => false
        ],
        'America/Scoresbysund' => [
                'offset' => - 3600000,
                'longname' => 'Eastern Greenland Time',
                'shortname' => 'EGT',
                'hasdst' => true,
                'dstlongname' => 'Eastern Greenland Summer Time',
                'dstshortname' => 'EGST'
        ],
        'Atlantic/Azores' => [
                'offset' => - 3600000,
                'longname' => 'Azores Time',
                'shortname' => 'AZOT',
                'hasdst' => true,
                'dstlongname' => 'Azores Summer Time',
                'dstshortname' => 'AZOST'
        ],
        'Atlantic/Cape_Verde' => [
                'offset' => - 3600000,
                'longname' => 'Cape Verde Time',
                'shortname' => 'CVT',
                'hasdst' => false
        ],
        'Etc/GMT+1' => [
                'offset' => - 3600000,
                'longname' => 'GMT-01:00',
                'shortname' => 'GMT-01:00',
                'hasdst' => false
        ],
        'Africa/Abidjan' => [
                'offset' => 0,
                'longname' => 'Greenwich Mean Time',
                'shortname' => 'GMT',
                'hasdst' => false
        ],
        'Africa/Accra' => [
                'offset' => 0,
                'longname' => 'Greenwich Mean Time',
                'shortname' => 'GMT',
                'hasdst' => false
        ],
        'Africa/Bamako' => [
                'offset' => 0,
                'longname' => 'Greenwich Mean Time',
                'shortname' => 'GMT',
                'hasdst' => false
        ],
        'Africa/Banjul' => [
                'offset' => 0,
                'longname' => 'Greenwich Mean Time',
                'shortname' => 'GMT',
                'hasdst' => false
        ],
        'Africa/Bissau' => [
                'offset' => 0,
                'longname' => 'Greenwich Mean Time',
                'shortname' => 'GMT',
                'hasdst' => false
        ],
        'Africa/Casablanca' => [
                'offset' => 0,
                'longname' => 'Western European Time',
                'shortname' => 'WET',
                'hasdst' => false
        ],
        'Africa/Conakry' => [
                'offset' => 0,
                'longname' => 'Greenwich Mean Time',
                'shortname' => 'GMT',
                'hasdst' => false
        ],
        'Africa/Dakar' => [
                'offset' => 0,
                'longname' => 'Greenwich Mean Time',
                'shortname' => 'GMT',
                'hasdst' => false
        ],
        'Africa/El_Aaiun' => [
                'offset' => 0,
                'longname' => 'Western European Time',
                'shortname' => 'WET',
                'hasdst' => false
        ],
        'Africa/Freetown' => [
                'offset' => 0,
                'longname' => 'Greenwich Mean Time',
                'shortname' => 'GMT',
                'hasdst' => false
        ],
        'Africa/Lome' => [
                'offset' => 0,
                'longname' => 'Greenwich Mean Time',
                'shortname' => 'GMT',
                'hasdst' => false
        ],
        'Africa/Monrovia' => [
                'offset' => 0,
                'longname' => 'Greenwich Mean Time',
                'shortname' => 'GMT',
                'hasdst' => false
        ],
        'Africa/Nouakchott' => [
                'offset' => 0,
                'longname' => 'Greenwich Mean Time',
                'shortname' => 'GMT',
                'hasdst' => false
        ],
        'Africa/Ouagadougou' => [
                'offset' => 0,
                'longname' => 'Greenwich Mean Time',
                'shortname' => 'GMT',
                'hasdst' => false
        ],
        'Africa/Sao_Tome' => [
                'offset' => 0,
                'longname' => 'Greenwich Mean Time',
                'shortname' => 'GMT',
                'hasdst' => false
        ],
        'Africa/Timbuktu' => [
                'offset' => 0,
                'longname' => 'Greenwich Mean Time',
                'shortname' => 'GMT',
                'hasdst' => false
        ],
        'America/Danmarkshavn' => [
                'offset' => 0,
                'longname' => 'Greenwich Mean Time',
                'shortname' => 'GMT',
                'hasdst' => false
        ],
        'Atlantic/Canary' => [
                'offset' => 0,
                'longname' => 'Western European Time',
                'shortname' => 'WET',
                'hasdst' => true,
                'dstlongname' => 'Western European Summer Time',
                'dstshortname' => 'WEST'
        ],
        'Atlantic/Faeroe' => [
                'offset' => 0,
                'longname' => 'Western European Time',
                'shortname' => 'WET',
                'hasdst' => true,
                'dstlongname' => 'Western European Summer Time',
                'dstshortname' => 'WEST'
        ],
        'Atlantic/Madeira' => [
                'offset' => 0,
                'longname' => 'Western European Time',
                'shortname' => 'WET',
                'hasdst' => true,
                'dstlongname' => 'Western European Summer Time',
                'dstshortname' => 'WEST'
        ],
        'Atlantic/Reykjavik' => [
                'offset' => 0,
                'longname' => 'Greenwich Mean Time',
                'shortname' => 'GMT',
                'hasdst' => false
        ],
        'Atlantic/St_Helena' => [
                'offset' => 0,
                'longname' => 'Greenwich Mean Time',
                'shortname' => 'GMT',
                'hasdst' => false
        ],
        'Eire' => [
                'offset' => 0,
                'longname' => 'Greenwich Mean Time',
                'shortname' => 'GMT',
                'hasdst' => true,
                'dstlongname' => 'Irish Summer Time',
                'dstshortname' => 'IST'
        ],
        'Etc/GMT' => [
                'offset' => 0,
                'longname' => 'GMT+00:00',
                'shortname' => 'GMT+00:00',
                'hasdst' => false
        ],
        'Etc/GMT+0' => [
                'offset' => 0,
                'longname' => 'GMT+00:00',
                'shortname' => 'GMT+00:00',
                'hasdst' => false
        ],
        'Etc/GMT-0' => [
                'offset' => 0,
                'longname' => 'GMT+00:00',
                'shortname' => 'GMT+00:00',
                'hasdst' => false
        ],
        'Etc/GMT0' => [
                'offset' => 0,
                'longname' => 'GMT+00:00',
                'shortname' => 'GMT+00:00',
                'hasdst' => false
        ],
        'Etc/Greenwich' => [
                'offset' => 0,
                'longname' => 'Greenwich Mean Time',
                'shortname' => 'GMT',
                'hasdst' => false
        ],
        'Etc/UCT' => [
                'offset' => 0,
                'longname' => 'Coordinated Universal Time',
                'shortname' => 'UTC',
                'hasdst' => false
        ],
        'Etc/UTC' => [
                'offset' => 0,
                'longname' => 'Coordinated Universal Time',
                'shortname' => 'UTC',
                'hasdst' => false
        ],
        'Etc/Universal' => [
                'offset' => 0,
                'longname' => 'Coordinated Universal Time',
                'shortname' => 'UTC',
                'hasdst' => false
        ],
        'Etc/Zulu' => [
                'offset' => 0,
                'longname' => 'Coordinated Universal Time',
                'shortname' => 'UTC',
                'hasdst' => false
        ],
        'Europe/Belfast' => [
                'offset' => 0,
                'longname' => 'Greenwich Mean Time',
                'shortname' => 'GMT',
                'hasdst' => true,
                'dstlongname' => 'British Summer Time',
                'dstshortname' => 'BST'
        ],
        'Europe/Dublin' => [
                'offset' => 0,
                'longname' => 'Greenwich Mean Time',
                'shortname' => 'GMT',
                'hasdst' => true,
                'dstlongname' => 'Irish Summer Time',
                'dstshortname' => 'IST'
        ],
        'Europe/Lisbon' => [
                'offset' => 0,
                'longname' => 'Western European Time',
                'shortname' => 'WET',
                'hasdst' => true,
                'dstlongname' => 'Western European Summer Time',
                'dstshortname' => 'WEST'
        ],
        'Europe/London' => [
                'offset' => 0,
                'longname' => 'Greenwich Mean Time',
                'shortname' => 'GMT',
                'hasdst' => true,
                'dstlongname' => 'British Summer Time',
                'dstshortname' => 'BST'
        ],
        'GB' => [
                'offset' => 0,
                'longname' => 'Greenwich Mean Time',
                'shortname' => 'GMT',
                'hasdst' => true,
                'dstlongname' => 'British Summer Time',
                'dstshortname' => 'BST'
        ],
        'GB-Eire' => [
                'offset' => 0,
                'longname' => 'Greenwich Mean Time',
                'shortname' => 'GMT',
                'hasdst' => true,
                'dstlongname' => 'British Summer Time',
                'dstshortname' => 'BST'
        ],
        'GMT' => [
                'offset' => 0,
                'longname' => 'Greenwich Mean Time',
                'shortname' => 'GMT',
                'hasdst' => false
        ],
        'GMT0' => [
                'offset' => 0,
                'longname' => 'GMT+00:00',
                'shortname' => 'GMT+00:00',
                'hasdst' => false
        ],
        'Greenwich' => [
                'offset' => 0,
                'longname' => 'Greenwich Mean Time',
                'shortname' => 'GMT',
                'hasdst' => false
        ],
        'Iceland' => [
                'offset' => 0,
                'longname' => 'Greenwich Mean Time',
                'shortname' => 'GMT',
                'hasdst' => false
        ],
        'Portugal' => [
                'offset' => 0,
                'longname' => 'Western European Time',
                'shortname' => 'WET',
                'hasdst' => true,
                'dstlongname' => 'Western European Summer Time',
                'dstshortname' => 'WEST'
        ],
        'UCT' => [
                'offset' => 0,
                'longname' => 'Coordinated Universal Time',
                'shortname' => 'UTC',
                'hasdst' => false
        ],
        'UTC' => [
                'offset' => 0,
                'longname' => 'Coordinated Universal Time',
                'shortname' => 'UTC',
                'hasdst' => false
        ],
        'Universal' => [
                'offset' => 0,
                'longname' => 'Coordinated Universal Time',
                'shortname' => 'UTC',
                'hasdst' => false
        ],
        'WET' => [
                'offset' => 0,
                'longname' => 'Western European Time',
                'shortname' => 'WET',
                'hasdst' => true,
                'dstlongname' => 'Western European Summer Time',
                'dstshortname' => 'WEST'
        ],
        'Zulu' => [
                'offset' => 0,
                'longname' => 'Coordinated Universal Time',
                'shortname' => 'UTC',
                'hasdst' => false
        ],
        'Africa/Algiers' => [
                'offset' => 3600000,
                'longname' => 'Central European Time',
                'shortname' => 'CET',
                'hasdst' => false
        ],
        'Africa/Bangui' => [
                'offset' => 3600000,
                'longname' => 'Western African Time',
                'shortname' => 'WAT',
                'hasdst' => false
        ],
        'Africa/Brazzaville' => [
                'offset' => 3600000,
                'longname' => 'Western African Time',
                'shortname' => 'WAT',
                'hasdst' => false
        ],
        'Africa/Ceuta' => [
                'offset' => 3600000,
                'longname' => 'Central European Time',
                'shortname' => 'CET',
                'hasdst' => true,
                'dstlongname' => 'Central European Summer Time',
                'dstshortname' => 'CEST'
        ],
        'Africa/Douala' => [
                'offset' => 3600000,
                'longname' => 'Western African Time',
                'shortname' => 'WAT',
                'hasdst' => false
        ],
        'Africa/Kinshasa' => [
                'offset' => 3600000,
                'longname' => 'Western African Time',
                'shortname' => 'WAT',
                'hasdst' => false
        ],
        'Africa/Lagos' => [
                'offset' => 3600000,
                'longname' => 'Western African Time',
                'shortname' => 'WAT',
                'hasdst' => false
        ],
        'Africa/Libreville' => [
                'offset' => 3600000,
                'longname' => 'Western African Time',
                'shortname' => 'WAT',
                'hasdst' => false
        ],
        'Africa/Luanda' => [
                'offset' => 3600000,
                'longname' => 'Western African Time',
                'shortname' => 'WAT',
                'hasdst' => false
        ],
        'Africa/Malabo' => [
                'offset' => 3600000,
                'longname' => 'Western African Time',
                'shortname' => 'WAT',
                'hasdst' => false
        ],
        'Africa/Ndjamena' => [
                'offset' => 3600000,
                'longname' => 'Western African Time',
                'shortname' => 'WAT',
                'hasdst' => false
        ],
        'Africa/Niamey' => [
                'offset' => 3600000,
                'longname' => 'Western African Time',
                'shortname' => 'WAT',
                'hasdst' => false
        ],
        'Africa/Porto-Novo' => [
                'offset' => 3600000,
                'longname' => 'Western African Time',
                'shortname' => 'WAT',
                'hasdst' => false
        ],
        'Africa/Tunis' => [
                'offset' => 3600000,
                'longname' => 'Central European Time',
                'shortname' => 'CET',
                'hasdst' => false
        ],
        'Africa/Windhoek' => [
                'offset' => 3600000,
                'longname' => 'Western African Time',
                'shortname' => 'WAT',
                'hasdst' => true,
                'dstlongname' => 'Western African Summer Time',
                'dstshortname' => 'WAST'
        ],
        'Arctic/Longyearbyen' => [
                'offset' => 3600000,
                'longname' => 'Central European Time',
                'shortname' => 'CET',
                'hasdst' => true,
                'dstlongname' => 'Central European Summer Time',
                'dstshortname' => 'CEST'
        ],
        'Atlantic/Jan_Mayen' => [
                'offset' => 3600000,
                'longname' => 'Eastern Greenland Time',
                'shortname' => 'EGT',
                'hasdst' => true,
                'dstlongname' => 'Eastern Greenland Summer Time',
                'dstshortname' => 'EGST'
        ],
        'CET' => [
                'offset' => 3600000,
                'longname' => 'Central European Time',
                'shortname' => 'CET',
                'hasdst' => true,
                'dstlongname' => 'Central European Summer Time',
                'dstshortname' => 'CEST'
        ],
        'CEST' => [
                'offset' => 3600000,
                'longname' => 'Central European Time',
                'shortname' => 'CET',
                'hasdst' => true,
                'dstlongname' => 'Central European Summer Time',
                'dstshortname' => 'CEST'
        ],
        'ECT' => [
                'offset' => 3600000,
                'longname' => 'Central European Time',
                'shortname' => 'CET',
                'hasdst' => true,
                'dstlongname' => 'Central European Summer Time',
                'dstshortname' => 'CEST'
        ],
        'Etc/GMT-1' => [
                'offset' => 3600000,
                'longname' => 'GMT+01:00',
                'shortname' => 'GMT+01:00',
                'hasdst' => false
        ],
        'Europe/Amsterdam' => [
                'offset' => 3600000,
                'longname' => 'Central European Time',
                'shortname' => 'CET',
                'hasdst' => true,
                'dstlongname' => 'Central European Summer Time',
                'dstshortname' => 'CEST'
        ],
        'Europe/Andorra' => [
                'offset' => 3600000,
                'longname' => 'Central European Time',
                'shortname' => 'CET',
                'hasdst' => true,
                'dstlongname' => 'Central European Summer Time',
                'dstshortname' => 'CEST'
        ],
        'Europe/Belgrade' => [
                'offset' => 3600000,
                'longname' => 'Central European Time',
                'shortname' => 'CET',
                'hasdst' => true,
                'dstlongname' => 'Central European Summer Time',
                'dstshortname' => 'CEST'
        ],
        'Europe/Berlin' => [
                'offset' => 3600000,
                'longname' => 'Central European Time',
                'shortname' => 'CET',
                'hasdst' => true,
                'dstlongname' => 'Central European Summer Time',
                'dstshortname' => 'CEST'
        ],
        'Europe/Bratislava' => [
                'offset' => 3600000,
                'longname' => 'Central European Time',
                'shortname' => 'CET',
                'hasdst' => true,
                'dstlongname' => 'Central European Summer Time',
                'dstshortname' => 'CEST'
        ],
        'Europe/Brussels' => [
                'offset' => 3600000,
                'longname' => 'Central European Time',
                'shortname' => 'CET',
                'hasdst' => true,
                'dstlongname' => 'Central European Summer Time',
                'dstshortname' => 'CEST'
        ],
        'Europe/Budapest' => [
                'offset' => 3600000,
                'longname' => 'Central European Time',
                'shortname' => 'CET',
                'hasdst' => true,
                'dstlongname' => 'Central European Summer Time',
                'dstshortname' => 'CEST'
        ],
        'Europe/Copenhagen' => [
                'offset' => 3600000,
                'longname' => 'Central European Time',
                'shortname' => 'CET',
                'hasdst' => true,
                'dstlongname' => 'Central European Summer Time',
                'dstshortname' => 'CEST'
        ],
        'Europe/Gibraltar' => [
                'offset' => 3600000,
                'longname' => 'Central European Time',
                'shortname' => 'CET',
                'hasdst' => true,
                'dstlongname' => 'Central European Summer Time',
                'dstshortname' => 'CEST'
        ],
        'Europe/Ljubljana' => [
                'offset' => 3600000,
                'longname' => 'Central European Time',
                'shortname' => 'CET',
                'hasdst' => true,
                'dstlongname' => 'Central European Summer Time',
                'dstshortname' => 'CEST'
        ],
        'Europe/Luxembourg' => [
                'offset' => 3600000,
                'longname' => 'Central European Time',
                'shortname' => 'CET',
                'hasdst' => true,
                'dstlongname' => 'Central European Summer Time',
                'dstshortname' => 'CEST'
        ],
        'Europe/Madrid' => [
                'offset' => 3600000,
                'longname' => 'Central European Time',
                'shortname' => 'CET',
                'hasdst' => true,
                'dstlongname' => 'Central European Summer Time',
                'dstshortname' => 'CEST'
        ],
        'Europe/Malta' => [
                'offset' => 3600000,
                'longname' => 'Central European Time',
                'shortname' => 'CET',
                'hasdst' => true,
                'dstlongname' => 'Central European Summer Time',
                'dstshortname' => 'CEST'
        ],
        'Europe/Monaco' => [
                'offset' => 3600000,
                'longname' => 'Central European Time',
                'shortname' => 'CET',
                'hasdst' => true,
                'dstlongname' => 'Central European Summer Time',
                'dstshortname' => 'CEST'
        ],
        'Europe/Oslo' => [
                'offset' => 3600000,
                'longname' => 'Central European Time',
                'shortname' => 'CET',
                'hasdst' => true,
                'dstlongname' => 'Central European Summer Time',
                'dstshortname' => 'CEST'
        ],
        'Europe/Paris' => [
                'offset' => 3600000,
                'longname' => 'Central European Time',
                'shortname' => 'CET',
                'hasdst' => true,
                'dstlongname' => 'Central European Summer Time',
                'dstshortname' => 'CEST'
        ],
        'Europe/Prague' => [
                'offset' => 3600000,
                'longname' => 'Central European Time',
                'shortname' => 'CET',
                'hasdst' => true,
                'dstlongname' => 'Central European Summer Time',
                'dstshortname' => 'CEST'
        ],
        'Europe/Rome' => [
                'offset' => 3600000,
                'longname' => 'Central European Time',
                'shortname' => 'CET',
                'hasdst' => true,
                'dstlongname' => 'Central European Summer Time',
                'dstshortname' => 'CEST'
        ],
        'Europe/San_Marino' => [
                'offset' => 3600000,
                'longname' => 'Central European Time',
                'shortname' => 'CET',
                'hasdst' => true,
                'dstlongname' => 'Central European Summer Time',
                'dstshortname' => 'CEST'
        ],
        'Europe/Sarajevo' => [
                'offset' => 3600000,
                'longname' => 'Central European Time',
                'shortname' => 'CET',
                'hasdst' => true,
                'dstlongname' => 'Central European Summer Time',
                'dstshortname' => 'CEST'
        ],
        'Europe/Skopje' => [
                'offset' => 3600000,
                'longname' => 'Central European Time',
                'shortname' => 'CET',
                'hasdst' => true,
                'dstlongname' => 'Central European Summer Time',
                'dstshortname' => 'CEST'
        ],
        'Europe/Stockholm' => [
                'offset' => 3600000,
                'longname' => 'Central European Time',
                'shortname' => 'CET',
                'hasdst' => true,
                'dstlongname' => 'Central European Summer Time',
                'dstshortname' => 'CEST'
        ],
        'Europe/Tirane' => [
                'offset' => 3600000,
                'longname' => 'Central European Time',
                'shortname' => 'CET',
                'hasdst' => true,
                'dstlongname' => 'Central European Summer Time',
                'dstshortname' => 'CEST'
        ],
        'Europe/Vaduz' => [
                'offset' => 3600000,
                'longname' => 'Central European Time',
                'shortname' => 'CET',
                'hasdst' => true,
                'dstlongname' => 'Central European Summer Time',
                'dstshortname' => 'CEST'
        ],
        'Europe/Vatican' => [
                'offset' => 3600000,
                'longname' => 'Central European Time',
                'shortname' => 'CET',
                'hasdst' => true,
                'dstlongname' => 'Central European Summer Time',
                'dstshortname' => 'CEST'
        ],
        'Europe/Vienna' => [
                'offset' => 3600000,
                'longname' => 'Central European Time',
                'shortname' => 'CET',
                'hasdst' => true,
                'dstlongname' => 'Central European Summer Time',
                'dstshortname' => 'CEST'
        ],
        'Europe/Warsaw' => [
                'offset' => 3600000,
                'longname' => 'Central European Time',
                'shortname' => 'CET',
                'hasdst' => true,
                'dstlongname' => 'Central European Summer Time',
                'dstshortname' => 'CEST'
        ],
        'Europe/Zagreb' => [
                'offset' => 3600000,
                'longname' => 'Central European Time',
                'shortname' => 'CET',
                'hasdst' => true,
                'dstlongname' => 'Central European Summer Time',
                'dstshortname' => 'CEST'
        ],
        'Europe/Zurich' => [
                'offset' => 3600000,
                'longname' => 'Central European Time',
                'shortname' => 'CET',
                'hasdst' => true,
                'dstlongname' => 'Central European Summer Time',
                'dstshortname' => 'CEST'
        ],
        'MET' => [
                'offset' => 3600000,
                'longname' => 'Middle Europe Time',
                'shortname' => 'MET',
                'hasdst' => true,
                'dstlongname' => 'Middle Europe Summer Time',
                'dstshortname' => 'MEST'
        ],
        'Poland' => [
                'offset' => 3600000,
                'longname' => 'Central European Time',
                'shortname' => 'CET',
                'hasdst' => true,
                'dstlongname' => 'Central European Summer Time',
                'dstshortname' => 'CEST'
        ],
        'ART' => [
                'offset' => 7200000,
                'longname' => 'Eastern European Time',
                'shortname' => 'EET',
                'hasdst' => true,
                'dstlongname' => 'Eastern European Summer Time',
                'dstshortname' => 'EEST'
        ],
        'Africa/Blantyre' => [
                'offset' => 7200000,
                'longname' => 'Central African Time',
                'shortname' => 'CAT',
                'hasdst' => false
        ],
        'Africa/Bujumbura' => [
                'offset' => 7200000,
                'longname' => 'Central African Time',
                'shortname' => 'CAT',
                'hasdst' => false
        ],
        'Africa/Cairo' => [
                'offset' => 7200000,
                'longname' => 'Eastern European Time',
                'shortname' => 'EET',
                'hasdst' => true,
                'dstlongname' => 'Eastern European Summer Time',
                'dstshortname' => 'EEST'
        ],
        'Africa/Gaborone' => [
                'offset' => 7200000,
                'longname' => 'Central African Time',
                'shortname' => 'CAT',
                'hasdst' => false
        ],
        'Africa/Harare' => [
                'offset' => 7200000,
                'longname' => 'Central African Time',
                'shortname' => 'CAT',
                'hasdst' => false
        ],
        'Africa/Johannesburg' => [
                'offset' => 7200000,
                'longname' => 'South Africa Standard Time',
                'shortname' => 'SAST',
                'hasdst' => false
        ],
        'Africa/Kigali' => [
                'offset' => 7200000,
                'longname' => 'Central African Time',
                'shortname' => 'CAT',
                'hasdst' => false
        ],
        'Africa/Lubumbashi' => [
                'offset' => 7200000,
                'longname' => 'Central African Time',
                'shortname' => 'CAT',
                'hasdst' => false
        ],
        'Africa/Lusaka' => [
                'offset' => 7200000,
                'longname' => 'Central African Time',
                'shortname' => 'CAT',
                'hasdst' => false
        ],
        'Africa/Maputo' => [
                'offset' => 7200000,
                'longname' => 'Central African Time',
                'shortname' => 'CAT',
                'hasdst' => false
        ],
        'Africa/Maseru' => [
                'offset' => 7200000,
                'longname' => 'South Africa Standard Time',
                'shortname' => 'SAST',
                'hasdst' => false
        ],
        'Africa/Mbabane' => [
                'offset' => 7200000,
                'longname' => 'South Africa Standard Time',
                'shortname' => 'SAST',
                'hasdst' => false
        ],
        'Africa/Tripoli' => [
                'offset' => 7200000,
                'longname' => 'Eastern European Time',
                'shortname' => 'EET',
                'hasdst' => false
        ],
        'Asia/Amman' => [
                'offset' => 7200000,
                'longname' => 'Eastern European Time',
                'shortname' => 'EET',
                'hasdst' => true,
                'dstlongname' => 'Eastern European Summer Time',
                'dstshortname' => 'EEST'
        ],
        'Asia/Beirut' => [
                'offset' => 7200000,
                'longname' => 'Eastern European Time',
                'shortname' => 'EET',
                'hasdst' => true,
                'dstlongname' => 'Eastern European Summer Time',
                'dstshortname' => 'EEST'
        ],
        'Asia/Damascus' => [
                'offset' => 7200000,
                'longname' => 'Eastern European Time',
                'shortname' => 'EET',
                'hasdst' => true,
                'dstlongname' => 'Eastern European Summer Time',
                'dstshortname' => 'EEST'
        ],
        'Asia/Gaza' => [
                'offset' => 7200000,
                'longname' => 'Eastern European Time',
                'shortname' => 'EET',
                'hasdst' => true,
                'dstlongname' => 'Eastern European Summer Time',
                'dstshortname' => 'EEST'
        ],
        'Asia/Istanbul' => [
                'offset' => 7200000,
                'longname' => 'Eastern European Time',
                'shortname' => 'EET',
                'hasdst' => true,
                'dstlongname' => 'Eastern European Summer Time',
                'dstshortname' => 'EEST'
        ],
        'Asia/Jerusalem' => [
                'offset' => 7200000,
                'longname' => 'Israel Standard Time',
                'shortname' => 'IST',
                'hasdst' => true,
                'dstlongname' => 'Israel Daylight Time',
                'dstshortname' => 'IDT'
        ],
        'Asia/Nicosia' => [
                'offset' => 7200000,
                'longname' => 'Eastern European Time',
                'shortname' => 'EET',
                'hasdst' => true,
                'dstlongname' => 'Eastern European Summer Time',
                'dstshortname' => 'EEST'
        ],
        'Asia/Tel_Aviv' => [
                'offset' => 7200000,
                'longname' => 'Israel Standard Time',
                'shortname' => 'IST',
                'hasdst' => true,
                'dstlongname' => 'Israel Daylight Time',
                'dstshortname' => 'IDT'
        ],
        'CAT' => [
                'offset' => 7200000,
                'longname' => 'Central African Time',
                'shortname' => 'CAT',
                'hasdst' => false
        ],
        'EET' => [
                'offset' => 7200000,
                'longname' => 'Eastern European Time',
                'shortname' => 'EET',
                'hasdst' => true,
                'dstlongname' => 'Eastern European Summer Time',
                'dstshortname' => 'EEST'
        ],
        'Egypt' => [
                'offset' => 7200000,
                'longname' => 'Eastern European Time',
                'shortname' => 'EET',
                'hasdst' => true,
                'dstlongname' => 'Eastern European Summer Time',
                'dstshortname' => 'EEST'
        ],
        'Etc/GMT-2' => [
                'offset' => 7200000,
                'longname' => 'GMT+02:00',
                'shortname' => 'GMT+02:00',
                'hasdst' => false
        ],
        'Europe/Athens' => [
                'offset' => 7200000,
                'longname' => 'Eastern European Time',
                'shortname' => 'EET',
                'hasdst' => true,
                'dstlongname' => 'Eastern European Summer Time',
                'dstshortname' => 'EEST'
        ],
        'Europe/Bucharest' => [
                'offset' => 7200000,
                'longname' => 'Eastern European Time',
                'shortname' => 'EET',
                'hasdst' => true,
                'dstlongname' => 'Eastern European Summer Time',
                'dstshortname' => 'EEST'
        ],
        'Europe/Chisinau' => [
                'offset' => 7200000,
                'longname' => 'Eastern European Time',
                'shortname' => 'EET',
                'hasdst' => true,
                'dstlongname' => 'Eastern European Summer Time',
                'dstshortname' => 'EEST'
        ],
        'Europe/Helsinki' => [
                'offset' => 7200000,
                'longname' => 'Eastern European Time',
                'shortname' => 'EET',
                'hasdst' => true,
                'dstlongname' => 'Eastern European Summer Time',
                'dstshortname' => 'EEST'
        ],
        'Europe/Istanbul' => [
                'offset' => 7200000,
                'longname' => 'Eastern European Time',
                'shortname' => 'EET',
                'hasdst' => true,
                'dstlongname' => 'Eastern European Summer Time',
                'dstshortname' => 'EEST'
        ],
        'Europe/Kaliningrad' => [
                'offset' => 7200000,
                'longname' => 'Eastern European Time',
                'shortname' => 'EET',
                'hasdst' => true,
                'dstlongname' => 'Eastern European Summer Time',
                'dstshortname' => 'EEST'
        ],
        'Europe/Kiev' => [
                'offset' => 7200000,
                'longname' => 'Eastern European Time',
                'shortname' => 'EET',
                'hasdst' => true,
                'dstlongname' => 'Eastern European Summer Time',
                'dstshortname' => 'EEST'
        ],
        'Europe/Minsk' => [
                'offset' => 7200000,
                'longname' => 'Eastern European Time',
                'shortname' => 'EET',
                'hasdst' => true,
                'dstlongname' => 'Eastern European Summer Time',
                'dstshortname' => 'EEST'
        ],
        'Europe/Nicosia' => [
                'offset' => 7200000,
                'longname' => 'Eastern European Time',
                'shortname' => 'EET',
                'hasdst' => true,
                'dstlongname' => 'Eastern European Summer Time',
                'dstshortname' => 'EEST'
        ],
        'Europe/Riga' => [
                'offset' => 7200000,
                'longname' => 'Eastern European Time',
                'shortname' => 'EET',
                'hasdst' => true,
                'dstlongname' => 'Eastern European Summer Time',
                'dstshortname' => 'EEST'
        ],
        'Europe/Simferopol' => [
                'offset' => 7200000,
                'longname' => 'Eastern European Time',
                'shortname' => 'EET',
                'hasdst' => true,
                'dstlongname' => 'Eastern European Summer Time',
                'dstshortname' => 'EEST'
        ],
        'Europe/Sofia' => [
                'offset' => 7200000,
                'longname' => 'Eastern European Time',
                'shortname' => 'EET',
                'hasdst' => true,
                'dstlongname' => 'Eastern European Summer Time',
                'dstshortname' => 'EEST'
        ],
        'Europe/Tallinn' => [
                'offset' => 7200000,
                'longname' => 'Eastern European Time',
                'shortname' => 'EET',
                'hasdst' => false
        ],
        'Europe/Tiraspol' => [
                'offset' => 7200000,
                'longname' => 'Eastern European Time',
                'shortname' => 'EET',
                'hasdst' => true,
                'dstlongname' => 'Eastern European Summer Time',
                'dstshortname' => 'EEST'
        ],
        'Europe/Uzhgorod' => [
                'offset' => 7200000,
                'longname' => 'Eastern European Time',
                'shortname' => 'EET',
                'hasdst' => true,
                'dstlongname' => 'Eastern European Summer Time',
                'dstshortname' => 'EEST'
        ],
        'Europe/Vilnius' => [
                'offset' => 7200000,
                'longname' => 'Eastern European Time',
                'shortname' => 'EET',
                'hasdst' => false
        ],
        'Europe/Zaporozhye' => [
                'offset' => 7200000,
                'longname' => 'Eastern European Time',
                'shortname' => 'EET',
                'hasdst' => true,
                'dstlongname' => 'Eastern European Summer Time',
                'dstshortname' => 'EEST'
        ],
        'Israel' => [
                'offset' => 7200000,
                'longname' => 'Israel Standard Time',
                'shortname' => 'IST',
                'hasdst' => true,
                'dstlongname' => 'Israel Daylight Time',
                'dstshortname' => 'IDT'
        ],
        'Libya' => [
                'offset' => 7200000,
                'longname' => 'Eastern European Time',
                'shortname' => 'EET',
                'hasdst' => false
        ],
        'Turkey' => [
                'offset' => 7200000,
                'longname' => 'Eastern European Time',
                'shortname' => 'EET',
                'hasdst' => true,
                'dstlongname' => 'Eastern European Summer Time',
                'dstshortname' => 'EEST'
        ],
        'Africa/Addis_Ababa' => [
                'offset' => 10800000,
                'longname' => 'Eastern African Time',
                'shortname' => 'EAT',
                'hasdst' => false
        ],
        'Africa/Asmera' => [
                'offset' => 10800000,
                'longname' => 'Eastern African Time',
                'shortname' => 'EAT',
                'hasdst' => false
        ],
        'Africa/Dar_es_Salaam' => [
                'offset' => 10800000,
                'longname' => 'Eastern African Time',
                'shortname' => 'EAT',
                'hasdst' => false
        ],
        'Africa/Djibouti' => [
                'offset' => 10800000,
                'longname' => 'Eastern African Time',
                'shortname' => 'EAT',
                'hasdst' => false
        ],
        'Africa/Kampala' => [
                'offset' => 10800000,
                'longname' => 'Eastern African Time',
                'shortname' => 'EAT',
                'hasdst' => false
        ],
        'Africa/Khartoum' => [
                'offset' => 10800000,
                'longname' => 'Eastern African Time',
                'shortname' => 'EAT',
                'hasdst' => false
        ],
        'Africa/Mogadishu' => [
                'offset' => 10800000,
                'longname' => 'Eastern African Time',
                'shortname' => 'EAT',
                'hasdst' => false
        ],
        'Africa/Nairobi' => [
                'offset' => 10800000,
                'longname' => 'Eastern African Time',
                'shortname' => 'EAT',
                'hasdst' => false
        ],
        'Antarctica/Syowa' => [
                'offset' => 10800000,
                'longname' => 'Syowa Time',
                'shortname' => 'SYOT',
                'hasdst' => false
        ],
        'Asia/Aden' => [
                'offset' => 10800000,
                'longname' => 'Arabia Standard Time',
                'shortname' => 'AST',
                'hasdst' => false
        ],
        'Asia/Baghdad' => [
                'offset' => 10800000,
                'longname' => 'Arabia Standard Time',
                'shortname' => 'AST',
                'hasdst' => true,
                'dstlongname' => 'Arabia Daylight Time',
                'dstshortname' => 'ADT'
        ],
        'Asia/Bahrain' => [
                'offset' => 10800000,
                'longname' => 'Arabia Standard Time',
                'shortname' => 'AST',
                'hasdst' => false
        ],
        'Asia/Kuwait' => [
                'offset' => 10800000,
                'longname' => 'Arabia Standard Time',
                'shortname' => 'AST',
                'hasdst' => false
        ],
        'Asia/Qatar' => [
                'offset' => 10800000,
                'longname' => 'Arabia Standard Time',
                'shortname' => 'AST',
                'hasdst' => false
        ],
        'Asia/Riyadh' => [
                'offset' => 10800000,
                'longname' => 'Arabia Standard Time',
                'shortname' => 'AST',
                'hasdst' => false
        ],
        'EAT' => [
                'offset' => 10800000,
                'longname' => 'Eastern African Time',
                'shortname' => 'EAT',
                'hasdst' => false
        ],
        'Etc/GMT-3' => [
                'offset' => 10800000,
                'longname' => 'GMT+03:00',
                'shortname' => 'GMT+03:00',
                'hasdst' => false
        ],
        'Europe/Moscow' => [
                'offset' => 10800000,
                'longname' => 'Moscow Standard Time',
                'shortname' => 'MSK',
                'hasdst' => true,
                'dstlongname' => 'Moscow Daylight Time',
                'dstshortname' => 'MSD'
        ],
        'Indian/Antananarivo' => [
                'offset' => 10800000,
                'longname' => 'Eastern African Time',
                'shortname' => 'EAT',
                'hasdst' => false
        ],
        'Indian/Comoro' => [
                'offset' => 10800000,
                'longname' => 'Eastern African Time',
                'shortname' => 'EAT',
                'hasdst' => false
        ],
        'Indian/Mayotte' => [
                'offset' => 10800000,
                'longname' => 'Eastern African Time',
                'shortname' => 'EAT',
                'hasdst' => false
        ],
        'W-SU' => [
                'offset' => 10800000,
                'longname' => 'Moscow Standard Time',
                'shortname' => 'MSK',
                'hasdst' => true,
                'dstlongname' => 'Moscow Daylight Time',
                'dstshortname' => 'MSD'
        ],
        'Asia/Riyadh87' => [
                'offset' => 11224000,
                'longname' => 'GMT+03:07',
                'shortname' => 'GMT+03:07',
                'hasdst' => false
        ],
        'Asia/Riyadh88' => [
                'offset' => 11224000,
                'longname' => 'GMT+03:07',
                'shortname' => 'GMT+03:07',
                'hasdst' => false
        ],
        'Asia/Riyadh89' => [
                'offset' => 11224000,
                'longname' => 'GMT+03:07',
                'shortname' => 'GMT+03:07',
                'hasdst' => false
        ],
        'Mideast/Riyadh87' => [
                'offset' => 11224000,
                'longname' => 'GMT+03:07',
                'shortname' => 'GMT+03:07',
                'hasdst' => false
        ],
        'Mideast/Riyadh88' => [
                'offset' => 11224000,
                'longname' => 'GMT+03:07',
                'shortname' => 'GMT+03:07',
                'hasdst' => false
        ],
        'Mideast/Riyadh89' => [
                'offset' => 11224000,
                'longname' => 'GMT+03:07',
                'shortname' => 'GMT+03:07',
                'hasdst' => false
        ],
        'Asia/Tehran' => [
                'offset' => 12600000,
                'longname' => 'Iran Time',
                'shortname' => 'IRT',
                'hasdst' => true,
                'dstlongname' => 'Iran Sumer Time',
                'dstshortname' => 'IRST'
        ],
        'Iran' => [
                'offset' => 12600000,
                'longname' => 'Iran Time',
                'shortname' => 'IRT',
                'hasdst' => true,
                'dstlongname' => 'Iran Sumer Time',
                'dstshortname' => 'IRST'
        ],
        'Asia/Aqtau' => [
                'offset' => 14400000,
                'longname' => 'Aqtau Time',
                'shortname' => 'AQTT',
                'hasdst' => true,
                'dstlongname' => 'Aqtau Summer Time',
                'dstshortname' => 'AQTST'
        ],
        'Asia/Baku' => [
                'offset' => 14400000,
                'longname' => 'Azerbaijan Time',
                'shortname' => 'AZT',
                'hasdst' => true,
                'dstlongname' => 'Azerbaijan Summer Time',
                'dstshortname' => 'AZST'
        ],
        'Asia/Dubai' => [
                'offset' => 14400000,
                'longname' => 'Gulf Standard Time',
                'shortname' => 'GST',
                'hasdst' => false
        ],
        'Asia/Muscat' => [
                'offset' => 14400000,
                'longname' => 'Gulf Standard Time',
                'shortname' => 'GST',
                'hasdst' => false
        ],
        'Asia/Tbilisi' => [
                'offset' => 14400000,
                'longname' => 'Georgia Time',
                'shortname' => 'GET',
                'hasdst' => true,
                'dstlongname' => 'Georgia Summer Time',
                'dstshortname' => 'GEST'
        ],
        'Asia/Yerevan' => [
                'offset' => 14400000,
                'longname' => 'Armenia Time',
                'shortname' => 'AMT',
                'hasdst' => true,
                'dstlongname' => 'Armenia Summer Time',
                'dstshortname' => 'AMST'
        ],
        'Etc/GMT-4' => [
                'offset' => 14400000,
                'longname' => 'GMT+04:00',
                'shortname' => 'GMT+04:00',
                'hasdst' => false
        ],
        'Europe/Samara' => [
                'offset' => 14400000,
                'longname' => 'Samara Time',
                'shortname' => 'SAMT',
                'hasdst' => true,
                'dstlongname' => 'Samara Summer Time',
                'dstshortname' => 'SAMST'
        ],
        'Indian/Mahe' => [
                'offset' => 14400000,
                'longname' => 'Seychelles Time',
                'shortname' => 'SCT',
                'hasdst' => false
        ],
        'Indian/Mauritius' => [
                'offset' => 14400000,
                'longname' => 'Mauritius Time',
                'shortname' => 'MUT',
                'hasdst' => false
        ],
        'Indian/Reunion' => [
                'offset' => 14400000,
                'longname' => 'Reunion Time',
                'shortname' => 'RET',
                'hasdst' => false
        ],
        'NET' => [
                'offset' => 14400000,
                'longname' => 'Armenia Time',
                'shortname' => 'AMT',
                'hasdst' => true,
                'dstlongname' => 'Armenia Summer Time',
                'dstshortname' => 'AMST'
        ],
        'Asia/Kabul' => [
                'offset' => 16200000,
                'longname' => 'Afghanistan Time',
                'shortname' => 'AFT',
                'hasdst' => false
        ],
        'Asia/Aqtobe' => [
                'offset' => 18000000,
                'longname' => 'Aqtobe Time',
                'shortname' => 'AQTT',
                'hasdst' => true,
                'dstlongname' => 'Aqtobe Summer Time',
                'dstshortname' => 'AQTST'
        ],
        'Asia/Ashgabat' => [
                'offset' => 18000000,
                'longname' => 'Turkmenistan Time',
                'shortname' => 'TMT',
                'hasdst' => false
        ],
        'Asia/Ashkhabad' => [
                'offset' => 18000000,
                'longname' => 'Turkmenistan Time',
                'shortname' => 'TMT',
                'hasdst' => false
        ],
        'Asia/Bishkek' => [
                'offset' => 18000000,
                'longname' => 'Kirgizstan Time',
                'shortname' => 'KGT',
                'hasdst' => true,
                'dstlongname' => 'Kirgizstan Summer Time',
                'dstshortname' => 'KGST'
        ],
        'Asia/Dushanbe' => [
                'offset' => 18000000,
                'longname' => 'Tajikistan Time',
                'shortname' => 'TJT',
                'hasdst' => false
        ],
        'Asia/Karachi' => [
                'offset' => 18000000,
                'longname' => 'Pakistan Time',
                'shortname' => 'PKT',
                'hasdst' => false
        ],
        'Asia/Samarkand' => [
                'offset' => 18000000,
                'longname' => 'Turkmenistan Time',
                'shortname' => 'TMT',
                'hasdst' => false
        ],
        'Asia/Tashkent' => [
                'offset' => 18000000,
                'longname' => 'Uzbekistan Time',
                'shortname' => 'UZT',
                'hasdst' => false
        ],
        'Asia/Yekaterinburg' => [
                'offset' => 18000000,
                'longname' => 'Yekaterinburg Time',
                'shortname' => 'YEKT',
                'hasdst' => true,
                'dstlongname' => 'Yekaterinburg Summer Time',
                'dstshortname' => 'YEKST'
        ],
        'Etc/GMT-5' => [
                'offset' => 18000000,
                'longname' => 'GMT+05:00',
                'shortname' => 'GMT+05:00',
                'hasdst' => false
        ],
        'Indian/Kerguelen' => [
                'offset' => 18000000,
                'longname' => 'French Southern & Antarctic Lands Time',
                'shortname' => 'TFT',
                'hasdst' => false
        ],
        'Indian/Maldives' => [
                'offset' => 18000000,
                'longname' => 'Maldives Time',
                'shortname' => 'MVT',
                'hasdst' => false
        ],
        'PLT' => [
                'offset' => 18000000,
                'longname' => 'Pakistan Time',
                'shortname' => 'PKT',
                'hasdst' => false
        ],
        'Asia/Calcutta' => [
                'offset' => 19800000,
                'longname' => 'India Standard Time',
                'shortname' => 'IST',
                'hasdst' => false
        ],
        'IST' => [
                'offset' => 19800000,
                'longname' => 'India Standard Time',
                'shortname' => 'IST',
                'hasdst' => false
        ],
        'Asia/Katmandu' => [
                'offset' => 20700000,
                'longname' => 'Nepal Time',
                'shortname' => 'NPT',
                'hasdst' => false
        ],
        'Antarctica/Mawson' => [
                'offset' => 21600000,
                'longname' => 'Mawson Time',
                'shortname' => 'MAWT',
                'hasdst' => false
        ],
        'Antarctica/Vostok' => [
                'offset' => 21600000,
                'longname' => 'Vostok time',
                'shortname' => 'VOST',
                'hasdst' => false
        ],
        'Asia/Almaty' => [
                'offset' => 21600000,
                'longname' => 'Alma-Ata Time',
                'shortname' => 'ALMT',
                'hasdst' => true,
                'dstlongname' => 'Alma-Ata Summer Time',
                'dstshortname' => 'ALMST'
        ],
        'Asia/Colombo' => [
                'offset' => 21600000,
                'longname' => 'Sri Lanka Time',
                'shortname' => 'LKT',
                'hasdst' => false
        ],
        'Asia/Dacca' => [
                'offset' => 21600000,
                'longname' => 'Bangladesh Time',
                'shortname' => 'BDT',
                'hasdst' => false
        ],
        'Asia/Dhaka' => [
                'offset' => 21600000,
                'longname' => 'Bangladesh Time',
                'shortname' => 'BDT',
                'hasdst' => false
        ],
        'Asia/Novosibirsk' => [
                'offset' => 21600000,
                'longname' => 'Novosibirsk Time',
                'shortname' => 'NOVT',
                'hasdst' => true,
                'dstlongname' => 'Novosibirsk Summer Time',
                'dstshortname' => 'NOVST'
        ],
        'Asia/Omsk' => [
                'offset' => 21600000,
                'longname' => 'Omsk Time',
                'shortname' => 'OMST',
                'hasdst' => true,
                'dstlongname' => 'Omsk Summer Time',
                'dstshortname' => 'OMSST'
        ],
        'Asia/Thimbu' => [
                'offset' => 21600000,
                'longname' => 'Bhutan Time',
                'shortname' => 'BTT',
                'hasdst' => false
        ],
        'Asia/Thimphu' => [
                'offset' => 21600000,
                'longname' => 'Bhutan Time',
                'shortname' => 'BTT',
                'hasdst' => false
        ],
        'BDT' => [
                'offset' => 21600000,
                'longname' => 'Bangladesh Time',
                'shortname' => 'BDT',
                'hasdst' => false
        ],
        'Etc/GMT-6' => [
                'offset' => 21600000,
                'longname' => 'GMT+06:00',
                'shortname' => 'GMT+06:00',
                'hasdst' => false
        ],
        'Indian/Chagos' => [
                'offset' => 21600000,
                'longname' => 'Indian Ocean Territory Time',
                'shortname' => 'IOT',
                'hasdst' => false
        ],
        'Asia/Rangoon' => [
                'offset' => 23400000,
                'longname' => 'Myanmar Time',
                'shortname' => 'MMT',
                'hasdst' => false
        ],
        'Indian/Cocos' => [
                'offset' => 23400000,
                'longname' => 'Cocos Islands Time',
                'shortname' => 'CCT',
                'hasdst' => false
        ],
        'Antarctica/Davis' => [
                'offset' => 25200000,
                'longname' => 'Davis Time',
                'shortname' => 'DAVT',
                'hasdst' => false
        ],
        'Asia/Bangkok' => [
                'offset' => 25200000,
                'longname' => 'Indochina Time',
                'shortname' => 'ICT',
                'hasdst' => false
        ],
        'Asia/Hovd' => [
                'offset' => 25200000,
                'longname' => 'Hovd Time',
                'shortname' => 'HOVT',
                'hasdst' => false
        ],
        'Asia/Jakarta' => [
                'offset' => 25200000,
                'longname' => 'West Indonesia Time',
                'shortname' => 'WIT',
                'hasdst' => false
        ],
        'Asia/Krasnoyarsk' => [
                'offset' => 25200000,
                'longname' => 'Krasnoyarsk Time',
                'shortname' => 'KRAT',
                'hasdst' => true,
                'dstlongname' => 'Krasnoyarsk Summer Time',
                'dstshortname' => 'KRAST'
        ],
        'Asia/Phnom_Penh' => [
                'offset' => 25200000,
                'longname' => 'Indochina Time',
                'shortname' => 'ICT',
                'hasdst' => false
        ],
        'Asia/Pontianak' => [
                'offset' => 25200000,
                'longname' => 'West Indonesia Time',
                'shortname' => 'WIT',
                'hasdst' => false
        ],
        'Asia/Saigon' => [
                'offset' => 25200000,
                'longname' => 'Indochina Time',
                'shortname' => 'ICT',
                'hasdst' => false
        ],
        'Asia/Vientiane' => [
                'offset' => 25200000,
                'longname' => 'Indochina Time',
                'shortname' => 'ICT',
                'hasdst' => false
        ],
        'Etc/GMT-7' => [
                'offset' => 25200000,
                'longname' => 'GMT+07:00',
                'shortname' => 'GMT+07:00',
                'hasdst' => false
        ],
        'Indian/Christmas' => [
                'offset' => 25200000,
                'longname' => 'Christmas Island Time',
                'shortname' => 'CXT',
                'hasdst' => false
        ],
        'VST' => [
                'offset' => 25200000,
                'longname' => 'Indochina Time',
                'shortname' => 'ICT',
                'hasdst' => false
        ],
        'Antarctica/Casey' => [
                'offset' => 28800000,
                'longname' => 'Western Standard Time (Australia)',
                'shortname' => 'WST',
                'hasdst' => false
        ],
        'Asia/Brunei' => [
                'offset' => 28800000,
                'longname' => 'Brunei Time',
                'shortname' => 'BNT',
                'hasdst' => false
        ],
        'Asia/Chongqing' => [
                'offset' => 28800000,
                'longname' => 'China Standard Time',
                'shortname' => 'CST',
                'hasdst' => false
        ],
        'Asia/Chungking' => [
                'offset' => 28800000,
                'longname' => 'China Standard Time',
                'shortname' => 'CST',
                'hasdst' => false
        ],
        'Asia/Harbin' => [
                'offset' => 28800000,
                'longname' => 'China Standard Time',
                'shortname' => 'CST',
                'hasdst' => false
        ],
        'Asia/Hong_Kong' => [
                'offset' => 28800000,
                'longname' => 'Hong Kong Time',
                'shortname' => 'HKT',
                'hasdst' => false
        ],
        'Asia/Irkutsk' => [
                'offset' => 28800000,
                'longname' => 'Irkutsk Time',
                'shortname' => 'IRKT',
                'hasdst' => true,
                'dstlongname' => 'Irkutsk Summer Time',
                'dstshortname' => 'IRKST'
        ],
        'Asia/Kashgar' => [
                'offset' => 28800000,
                'longname' => 'China Standard Time',
                'shortname' => 'CST',
                'hasdst' => false
        ],
        'Asia/Kuala_Lumpur' => [
                'offset' => 28800000,
                'longname' => 'Malaysia Time',
                'shortname' => 'MYT',
                'hasdst' => false
        ],
        'Asia/Kuching' => [
                'offset' => 28800000,
                'longname' => 'Malaysia Time',
                'shortname' => 'MYT',
                'hasdst' => false
        ],
        'Asia/Macao' => [
                'offset' => 28800000,
                'longname' => 'China Standard Time',
                'shortname' => 'CST',
                'hasdst' => false
        ],
        'Asia/Manila' => [
                'offset' => 28800000,
                'longname' => 'Philippines Time',
                'shortname' => 'PHT',
                'hasdst' => false
        ],
        'Asia/Shanghai' => [
                'offset' => 28800000,
                'longname' => 'China Standard Time',
                'shortname' => 'CST',
                'hasdst' => false
        ],
        'Asia/Singapore' => [
                'offset' => 28800000,
                'longname' => 'Singapore Time',
                'shortname' => 'SGT',
                'hasdst' => false
        ],
        'Asia/Taipei' => [
                'offset' => 28800000,
                'longname' => 'China Standard Time',
                'shortname' => 'CST',
                'hasdst' => false
        ],
        'Asia/Ujung_Pandang' => [
                'offset' => 28800000,
                'longname' => 'Central Indonesia Time',
                'shortname' => 'CIT',
                'hasdst' => false
        ],
        'Asia/Ulaanbaatar' => [
                'offset' => 28800000,
                'longname' => 'Ulaanbaatar Time',
                'shortname' => 'ULAT',
                'hasdst' => false
        ],
        'Asia/Ulan_Bator' => [
                'offset' => 28800000,
                'longname' => 'Ulaanbaatar Time',
                'shortname' => 'ULAT',
                'hasdst' => false
        ],
        'Asia/Urumqi' => [
                'offset' => 28800000,
                'longname' => 'China Standard Time',
                'shortname' => 'CST',
                'hasdst' => false
        ],
        'Australia/Perth' => [
                'offset' => 28800000,
                'longname' => 'Western Standard Time (Australia)',
                'shortname' => 'WST',
                'hasdst' => false
        ],
        'Australia/West' => [
                'offset' => 28800000,
                'longname' => 'Western Standard Time (Australia)',
                'shortname' => 'WST',
                'hasdst' => false
        ],
        'CTT' => [
                'offset' => 28800000,
                'longname' => 'China Standard Time',
                'shortname' => 'CST',
                'hasdst' => false
        ],
        'Etc/GMT-8' => [
                'offset' => 28800000,
                'longname' => 'GMT+08:00',
                'shortname' => 'GMT+08:00',
                'hasdst' => false
        ],
        'Hongkong' => [
                'offset' => 28800000,
                'longname' => 'Hong Kong Time',
                'shortname' => 'HKT',
                'hasdst' => false
        ],
        'PRC' => [
                'offset' => 28800000,
                'longname' => 'China Standard Time',
                'shortname' => 'CST',
                'hasdst' => false
        ],
        'Singapore' => [
                'offset' => 28800000,
                'longname' => 'Singapore Time',
                'shortname' => 'SGT',
                'hasdst' => false
        ],
        'Asia/Choibalsan' => [
                'offset' => 32400000,
                'longname' => 'Choibalsan Time',
                'shortname' => 'CHOT',
                'hasdst' => false
        ],
        'Asia/Dili' => [
                'offset' => 32400000,
                'longname' => 'East Timor Time',
                'shortname' => 'TPT',
                'hasdst' => false
        ],
        'Asia/Jayapura' => [
                'offset' => 32400000,
                'longname' => 'East Indonesia Time',
                'shortname' => 'EIT',
                'hasdst' => false
        ],
        'Asia/Pyongyang' => [
                'offset' => 32400000,
                'longname' => 'Korea Standard Time',
                'shortname' => 'KST',
                'hasdst' => false
        ],
        'Asia/Seoul' => [
                'offset' => 32400000,
                'longname' => 'Korea Standard Time',
                'shortname' => 'KST',
                'hasdst' => false
        ],
        'Asia/Tokyo' => [
                'offset' => 32400000,
                'longname' => 'Japan Standard Time',
                'shortname' => 'JST',
                'hasdst' => false
        ],
        'Asia/Yakutsk' => [
                'offset' => 32400000,
                'longname' => 'Yakutsk Time',
                'shortname' => 'YAKT',
                'hasdst' => true,
                'dstlongname' => 'Yaktsk Summer Time',
                'dstshortname' => 'YAKST'
        ],
        'Etc/GMT-9' => [
                'offset' => 32400000,
                'longname' => 'GMT+09:00',
                'shortname' => 'GMT+09:00',
                'hasdst' => false
        ],
        'JST' => [
                'offset' => 32400000,
                'longname' => 'Japan Standard Time',
                'shortname' => 'JST',
                'hasdst' => false
        ],
        'Japan' => [
                'offset' => 32400000,
                'longname' => 'Japan Standard Time',
                'shortname' => 'JST',
                'hasdst' => false
        ],
        'Pacific/Palau' => [
                'offset' => 32400000,
                'longname' => 'Palau Time',
                'shortname' => 'PWT',
                'hasdst' => false
        ],
        'ROK' => [
                'offset' => 32400000,
                'longname' => 'Korea Standard Time',
                'shortname' => 'KST',
                'hasdst' => false
        ],
        'ACT' => [
                'offset' => 34200000,
                'longname' => 'Central Standard Time (Northern Territory)',
                'shortname' => 'CST',
                'hasdst' => false
        ],
        'Australia/Adelaide' => [
                'offset' => 34200000,
                'longname' => 'Central Standard Time (South Australia)',
                'shortname' => 'CST',
                'hasdst' => true,
                'dstlongname' => 'Central Summer Time (South Australia)',
                'dstshortname' => 'CST'
        ],
        'Australia/Broken_Hill' => [
                'offset' => 34200000,
                'longname' => 'Central Standard Time (South Australia/New South Wales)',
                'shortname' => 'CST',
                'hasdst' => true,
                'dstlongname' => 'Central Summer Time (South Australia/New South Wales)',
                'dstshortname' => 'CST'
        ],
        'Australia/Darwin' => [
                'offset' => 34200000,
                'longname' => 'Central Standard Time (Northern Territory)',
                'shortname' => 'CST',
                'hasdst' => false
        ],
        'Australia/North' => [
                'offset' => 34200000,
                'longname' => 'Central Standard Time (Northern Territory)',
                'shortname' => 'CST',
                'hasdst' => false
        ],
        'Australia/South' => [
                'offset' => 34200000,
                'longname' => 'Central Standard Time (South Australia)',
                'shortname' => 'CST',
                'hasdst' => true,
                'dstlongname' => 'Central Summer Time (South Australia)',
                'dstshortname' => 'CST'
        ],
        'Australia/Yancowinna' => [
                'offset' => 34200000,
                'longname' => 'Central Standard Time (South Australia/New South Wales)',
                'shortname' => 'CST',
                'hasdst' => true,
                'dstlongname' => 'Central Summer Time (South Australia/New South Wales)',
                'dstshortname' => 'CST'
        ],
        'AET' => [
                'offset' => 36000000,
                'longname' => 'Eastern Standard Time (New South Wales)',
                'shortname' => 'EST',
                'hasdst' => true,
                'dstlongname' => 'Eastern Summer Time (New South Wales)',
                'dstshortname' => 'EST'
        ],
        'Antarctica/DumontDUrville' => [
                'offset' => 36000000,
                'longname' => 'Dumont-d\'Urville Time',
                'shortname' => 'DDUT',
                'hasdst' => false
        ],
        'Asia/Sakhalin' => [
                'offset' => 36000000,
                'longname' => 'Sakhalin Time',
                'shortname' => 'SAKT',
                'hasdst' => true,
                'dstlongname' => 'Sakhalin Summer Time',
                'dstshortname' => 'SAKST'
        ],
        'Asia/Vladivostok' => [
                'offset' => 36000000,
                'longname' => 'Vladivostok Time',
                'shortname' => 'VLAT',
                'hasdst' => true,
                'dstlongname' => 'Vladivostok Summer Time',
                'dstshortname' => 'VLAST'
        ],
        'Australia/ACT' => [
                'offset' => 36000000,
                'longname' => 'Eastern Standard Time (New South Wales)',
                'shortname' => 'EST',
                'hasdst' => true,
                'dstlongname' => 'Eastern Summer Time (New South Wales)',
                'dstshortname' => 'EST'
        ],
        'Australia/Brisbane' => [
                'offset' => 36000000,
                'longname' => 'Eastern Standard Time (Queensland)',
                'shortname' => 'EST',
                'hasdst' => false
        ],
        'Australia/Canberra' => [
                'offset' => 36000000,
                'longname' => 'Eastern Standard Time (New South Wales)',
                'shortname' => 'EST',
                'hasdst' => true,
                'dstlongname' => 'Eastern Summer Time (New South Wales)',
                'dstshortname' => 'EST'
        ],
        'Australia/Hobart' => [
                'offset' => 36000000,
                'longname' => 'Eastern Standard Time (Tasmania)',
                'shortname' => 'EST',
                'hasdst' => true,
                'dstlongname' => 'Eastern Summer Time (Tasmania)',
                'dstshortname' => 'EST'
        ],
        'Australia/Lindeman' => [
                'offset' => 36000000,
                'longname' => 'Eastern Standard Time (Queensland)',
                'shortname' => 'EST',
                'hasdst' => false
        ],
        'Australia/Melbourne' => [
                'offset' => 36000000,
                'longname' => 'Eastern Standard Time (Victoria)',
                'shortname' => 'EST',
                'hasdst' => true,
                'dstlongname' => 'Eastern Summer Time (Victoria)',
                'dstshortname' => 'EST'
        ],
        'Australia/NSW' => [
                'offset' => 36000000,
                'longname' => 'Eastern Standard Time (New South Wales)',
                'shortname' => 'EST',
                'hasdst' => true,
                'dstlongname' => 'Eastern Summer Time (New South Wales)',
                'dstshortname' => 'EST'
        ],
        'Australia/Queensland' => [
                'offset' => 36000000,
                'longname' => 'Eastern Standard Time (Queensland)',
                'shortname' => 'EST',
                'hasdst' => false
        ],
        'Australia/Sydney' => [
                'offset' => 36000000,
                'longname' => 'Eastern Standard Time (New South Wales)',
                'shortname' => 'EST',
                'hasdst' => true,
                'dstlongname' => 'Eastern Summer Time (New South Wales)',
                'dstshortname' => 'EST'
        ],
        'Australia/Tasmania' => [
                'offset' => 36000000,
                'longname' => 'Eastern Standard Time (Tasmania)',
                'shortname' => 'EST',
                'hasdst' => true,
                'dstlongname' => 'Eastern Summer Time (Tasmania)',
                'dstshortname' => 'EST'
        ],
        'Australia/Victoria' => [
                'offset' => 36000000,
                'longname' => 'Eastern Standard Time (Victoria)',
                'shortname' => 'EST',
                'hasdst' => true,
                'dstlongname' => 'Eastern Summer Time (Victoria)',
                'dstshortname' => 'EST'
        ],
        'Etc/GMT-10' => [
                'offset' => 36000000,
                'longname' => 'GMT+10:00',
                'shortname' => 'GMT+10:00',
                'hasdst' => false
        ],
        'Pacific/Guam' => [
                'offset' => 36000000,
                'longname' => 'Chamorro Standard Time',
                'shortname' => 'ChST',
                'hasdst' => false
        ],
        'Pacific/Port_Moresby' => [
                'offset' => 36000000,
                'longname' => 'Papua New Guinea Time',
                'shortname' => 'PGT',
                'hasdst' => false
        ],
        'Pacific/Saipan' => [
                'offset' => 36000000,
                'longname' => 'Chamorro Standard Time',
                'shortname' => 'ChST',
                'hasdst' => false
        ],
        'Pacific/Truk' => [
                'offset' => 36000000,
                'longname' => 'Truk Time',
                'shortname' => 'TRUT',
                'hasdst' => false
        ],
        'Pacific/Yap' => [
                'offset' => 36000000,
                'longname' => 'Yap Time',
                'shortname' => 'YAPT',
                'hasdst' => false
        ],
        'Australia/LHI' => [
                'offset' => 37800000,
                'longname' => 'Load Howe Standard Time',
                'shortname' => 'LHST',
                'hasdst' => true,
                'dstlongname' => 'Load Howe Summer Time',
                'dstshortname' => 'LHST'
        ],
        'Australia/Lord_Howe' => [
                'offset' => 37800000,
                'longname' => 'Load Howe Standard Time',
                'shortname' => 'LHST',
                'hasdst' => true,
                'dstlongname' => 'Load Howe Summer Time',
                'dstshortname' => 'LHST'
        ],
        'Asia/Magadan' => [
                'offset' => 39600000,
                'longname' => 'Magadan Time',
                'shortname' => 'MAGT',
                'hasdst' => true,
                'dstlongname' => 'Magadan Summer Time',
                'dstshortname' => 'MAGST'
        ],
        'Etc/GMT-11' => [
                'offset' => 39600000,
                'longname' => 'GMT+11:00',
                'shortname' => 'GMT+11:00',
                'hasdst' => false
        ],
        'Pacific/Efate' => [
                'offset' => 39600000,
                'longname' => 'Vanuatu Time',
                'shortname' => 'VUT',
                'hasdst' => false
        ],
        'Pacific/Guadalcanal' => [
                'offset' => 39600000,
                'longname' => 'Solomon Is. Time',
                'shortname' => 'SBT',
                'hasdst' => false
        ],
        'Pacific/Kosrae' => [
                'offset' => 39600000,
                'longname' => 'Kosrae Time',
                'shortname' => 'KOST',
                'hasdst' => false
        ],
        'Pacific/Noumea' => [
                'offset' => 39600000,
                'longname' => 'New Caledonia Time',
                'shortname' => 'NCT',
                'hasdst' => false
        ],
        'Pacific/Ponape' => [
                'offset' => 39600000,
                'longname' => 'Ponape Time',
                'shortname' => 'PONT',
                'hasdst' => false
        ],
        'SST' => [
                'offset' => 39600000,
                'longname' => 'Solomon Is. Time',
                'shortname' => 'SBT',
                'hasdst' => false
        ],
        'Pacific/Norfolk' => [
                'offset' => 41400000,
                'longname' => 'Norfolk Time',
                'shortname' => 'NFT',
                'hasdst' => false
        ],
        'Antarctica/McMurdo' => [
                'offset' => 43200000,
                'longname' => 'New Zealand Standard Time',
                'shortname' => 'NZST',
                'hasdst' => true,
                'dstlongname' => 'New Zealand Daylight Time',
                'dstshortname' => 'NZDT'
        ],
        'Antarctica/South_Pole' => [
                'offset' => 43200000,
                'longname' => 'New Zealand Standard Time',
                'shortname' => 'NZST',
                'hasdst' => true,
                'dstlongname' => 'New Zealand Daylight Time',
                'dstshortname' => 'NZDT'
        ],
        'Asia/Anadyr' => [
                'offset' => 43200000,
                'longname' => 'Anadyr Time',
                'shortname' => 'ANAT',
                'hasdst' => true,
                'dstlongname' => 'Anadyr Summer Time',
                'dstshortname' => 'ANAST'
        ],
        'Asia/Kamchatka' => [
                'offset' => 43200000,
                'longname' => 'Petropavlovsk-Kamchatski Time',
                'shortname' => 'PETT',
                'hasdst' => true,
                'dstlongname' => 'Petropavlovsk-Kamchatski Summer Time',
                'dstshortname' => 'PETST'
        ],
        'Etc/GMT-12' => [
                'offset' => 43200000,
                'longname' => 'GMT+12:00',
                'shortname' => 'GMT+12:00',
                'hasdst' => false
        ],
        'Kwajalein' => [
                'offset' => 43200000,
                'longname' => 'Marshall Islands Time',
                'shortname' => 'MHT',
                'hasdst' => false
        ],
        'NST' => [
                'offset' => 43200000,
                'longname' => 'New Zealand Standard Time',
                'shortname' => 'NZST',
                'hasdst' => true,
                'dstlongname' => 'New Zealand Daylight Time',
                'dstshortname' => 'NZDT'
        ],
        'NZ' => [
                'offset' => 43200000,
                'longname' => 'New Zealand Standard Time',
                'shortname' => 'NZST',
                'hasdst' => true,
                'dstlongname' => 'New Zealand Daylight Time',
                'dstshortname' => 'NZDT'
        ],
        'Pacific/Auckland' => [
                'offset' => 43200000,
                'longname' => 'New Zealand Standard Time',
                'shortname' => 'NZST',
                'hasdst' => true,
                'dstlongname' => 'New Zealand Daylight Time',
                'dstshortname' => 'NZDT'
        ],
        'Pacific/Fiji' => [
                'offset' => 43200000,
                'longname' => 'Fiji Time',
                'shortname' => 'FJT',
                'hasdst' => false
        ],
        'Pacific/Funafuti' => [
                'offset' => 43200000,
                'longname' => 'Tuvalu Time',
                'shortname' => 'TVT',
                'hasdst' => false
        ],
        'Pacific/Kwajalein' => [
                'offset' => 43200000,
                'longname' => 'Marshall Islands Time',
                'shortname' => 'MHT',
                'hasdst' => false
        ],
        'Pacific/Majuro' => [
                'offset' => 43200000,
                'longname' => 'Marshall Islands Time',
                'shortname' => 'MHT',
                'hasdst' => false
        ],
        'Pacific/Nauru' => [
                'offset' => 43200000,
                'longname' => 'Nauru Time',
                'shortname' => 'NRT',
                'hasdst' => false
        ],
        'Pacific/Tarawa' => [
                'offset' => 43200000,
                'longname' => 'Gilbert Is. Time',
                'shortname' => 'GILT',
                'hasdst' => false
        ],
        'Pacific/Wake' => [
                'offset' => 43200000,
                'longname' => 'Wake Time',
                'shortname' => 'WAKT',
                'hasdst' => false
        ],
        'Pacific/Wallis' => [
                'offset' => 43200000,
                'longname' => 'Wallis & Futuna Time',
                'shortname' => 'WFT',
                'hasdst' => false
        ],
        'NZ-CHAT' => [
                'offset' => 45900000,
                'longname' => 'Chatham Standard Time',
                'shortname' => 'CHAST',
                'hasdst' => true,
                'dstlongname' => 'Chatham Daylight Time',
                'dstshortname' => 'CHADT'
        ],
        'Pacific/Chatham' => [
                'offset' => 45900000,
                'longname' => 'Chatham Standard Time',
                'shortname' => 'CHAST',
                'hasdst' => true,
                'dstlongname' => 'Chatham Daylight Time',
                'dstshortname' => 'CHADT'
        ],
        'Etc/GMT-13' => [
                'offset' => 46800000,
                'longname' => 'GMT+13:00',
                'shortname' => 'GMT+13:00',
                'hasdst' => false
        ],
        'Pacific/Enderbury' => [
                'offset' => 46800000,
                'longname' => 'Phoenix Is. Time',
                'shortname' => 'PHOT',
                'hasdst' => false
        ],
        'Pacific/Tongatapu' => [
                'offset' => 46800000,
                'longname' => 'Tonga Time',
                'shortname' => 'TOT',
                'hasdst' => false
        ],
        'Etc/GMT-14' => [
                'offset' => 50400000,
                'longname' => 'GMT+14:00',
                'shortname' => 'GMT+14:00',
                'hasdst' => false
        ],
        'Pacific/Kiritimati' => [
                'offset' => 50400000,
                'longname' => 'Line Is. Time',
                'shortname' => 'LINT',
                'hasdst' => false
        ],
        'GMT-12:00' => [
                'offset' => - 43200000,
                'longname' => 'GMT-12:00',
                'shortname' => 'GMT-12:00',
                'hasdst' => false
        ],
        'GMT-11:00' => [
                'offset' => - 39600000,
                'longname' => 'GMT-11:00',
                'shortname' => 'GMT-11:00',
                'hasdst' => false
        ],
        'West Samoa Time' => [
                'offset' => - 39600000,
                'longname' => 'West Samoa Time',
                'shortname' => 'WST',
                'hasdst' => false
        ],
        'Samoa Standard Time' => [
                'offset' => - 39600000,
                'longname' => 'Samoa Standard Time',
                'shortname' => 'SST',
                'hasdst' => false
        ],
        'Niue Time' => [
                'offset' => - 39600000,
                'longname' => 'Niue Time',
                'shortname' => 'NUT',
                'hasdst' => false
        ],
        'Hawaii-Aleutian Standard Time' => [
                'offset' => - 36000000,
                'longname' => 'Hawaii-Aleutian Standard Time',
                'shortname' => 'HAST',
                'hasdst' => true,
                'dstlongname' => 'Hawaii-Aleutian Daylight Time',
                'dstshortname' => 'HADT'
        ],
        'GMT-10:00' => [
                'offset' => - 36000000,
                'longname' => 'GMT-10:00',
                'shortname' => 'GMT-10:00',
                'hasdst' => false
        ],
        'Hawaii Standard Time' => [
                'offset' => - 36000000,
                'longname' => 'Hawaii Standard Time',
                'shortname' => 'HST',
                'hasdst' => false
        ],
        'Tokelau Time' => [
                'offset' => - 36000000,
                'longname' => 'Tokelau Time',
                'shortname' => 'TKT',
                'hasdst' => false
        ],
        'Cook Is. Time' => [
                'offset' => - 36000000,
                'longname' => 'Cook Is. Time',
                'shortname' => 'CKT',
                'hasdst' => false
        ],
        'Tahiti Time' => [
                'offset' => - 36000000,
                'longname' => 'Tahiti Time',
                'shortname' => 'TAHT',
                'hasdst' => false
        ],
        'Marquesas Time' => [
                'offset' => - 34200000,
                'longname' => 'Marquesas Time',
                'shortname' => 'MART',
                'hasdst' => false
        ],
        'Alaska Standard Time' => [
                'offset' => - 32400000,
                'longname' => 'Alaska Standard Time',
                'shortname' => 'AKST',
                'hasdst' => true,
                'dstlongname' => 'Alaska Daylight Time',
                'dstshortname' => 'AKDT'
        ],
        'GMT-09:00' => [
                'offset' => - 32400000,
                'longname' => 'GMT-09:00',
                'shortname' => 'GMT-09:00',
                'hasdst' => false
        ],
        'Gambier Time' => [
                'offset' => - 32400000,
                'longname' => 'Gambier Time',
                'shortname' => 'GAMT',
                'hasdst' => false
        ],
        'Pacific Standard Time' => [
                'offset' => - 28800000,
                'longname' => 'Pacific Standard Time',
                'shortname' => 'PST',
                'hasdst' => true,
                'dstlongname' => 'Pacific Daylight Time',
                'dstshortname' => 'PDT'
        ],
        'GMT-08:00' => [
                'offset' => - 28800000,
                'longname' => 'GMT-08:00',
                'shortname' => 'GMT-08:00',
                'hasdst' => false
        ],
        'Pitcairn Standard Time' => [
                'offset' => - 28800000,
                'longname' => 'Pitcairn Standard Time',
                'shortname' => 'PST',
                'hasdst' => false
        ],
        'Mountain Standard Time' => [
                'offset' => - 25200000,
                'longname' => 'Mountain Standard Time',
                'shortname' => 'MST',
                'hasdst' => true,
                'dstlongname' => 'Mountain Daylight Time',
                'dstshortname' => 'MDT'
        ],
        'GMT-07:00' => [
                'offset' => - 25200000,
                'longname' => 'GMT-07:00',
                'shortname' => 'GMT-07:00',
                'hasdst' => false
        ],
        'Central Standard Time' => [
                'offset' => - 18000000,
                'longname' => 'Central Standard Time',
                'shortname' => 'CST',
                'hasdst' => true,
                'dstlongname' => 'Central Daylight Time',
                'dstshortname' => 'CDT'
        ],
        'Eastern Standard Time' => [
                'offset' => - 18000000,
                'longname' => 'Eastern Standard Time',
                'shortname' => 'EST',
                'hasdst' => true,
                'dstlongname' => 'Eastern Daylight Time',
                'dstshortname' => 'EDT'
        ],
        'Easter Is. Time' => [
                'offset' => - 21600000,
                'longname' => 'Easter Is. Time',
                'shortname' => 'EAST',
                'hasdst' => true,
                'dstlongname' => 'Easter Is. Summer Time',
                'dstshortname' => 'EASST'
        ],
        'GMT-06:00' => [
                'offset' => - 21600000,
                'longname' => 'GMT-06:00',
                'shortname' => 'GMT-06:00',
                'hasdst' => false
        ],
        'Galapagos Time' => [
                'offset' => - 21600000,
                'longname' => 'Galapagos Time',
                'shortname' => 'GALT',
                'hasdst' => false
        ],
        'Colombia Time' => [
                'offset' => - 18000000,
                'longname' => 'Colombia Time',
                'shortname' => 'COT',
                'hasdst' => false
        ],
        'Acre Time' => [
                'offset' => - 18000000,
                'longname' => 'Acre Time',
                'shortname' => 'ACT',
                'hasdst' => false
        ],
        'Ecuador Time' => [
                'offset' => - 18000000,
                'longname' => 'Ecuador Time',
                'shortname' => 'ECT',
                'hasdst' => false
        ],
        'Peru Time' => [
                'offset' => - 18000000,
                'longname' => 'Peru Time',
                'shortname' => 'PET',
                'hasdst' => false
        ],
        'GMT-05:00' => [
                'offset' => - 18000000,
                'longname' => 'GMT-05:00',
                'shortname' => 'GMT-05:00',
                'hasdst' => false
        ],
        'Atlantic Standard Time' => [
                'offset' => - 14400000,
                'longname' => 'Atlantic Standard Time',
                'shortname' => 'AST',
                'hasdst' => true,
                'dstlongname' => 'Atlantic Daylight Time',
                'dstshortname' => 'ADT'
        ],
        'Paraguay Time' => [
                'offset' => - 14400000,
                'longname' => 'Paraguay Time',
                'shortname' => 'PYT',
                'hasdst' => true,
                'dstlongname' => 'Paraguay Summer Time',
                'dstshortname' => 'PYST'
        ],
        'Amazon Standard Time' => [
                'offset' => - 14400000,
                'longname' => 'Amazon Standard Time',
                'shortname' => 'AMT',
                'hasdst' => false
        ],
        'Venezuela Time' => [
                'offset' => - 14400000,
                'longname' => 'Venezuela Time',
                'shortname' => 'VET',
                'hasdst' => false
        ],
        'Guyana Time' => [
                'offset' => - 14400000,
                'longname' => 'Guyana Time',
                'shortname' => 'GYT',
                'hasdst' => false
        ],
        'Bolivia Time' => [
                'offset' => - 14400000,
                'longname' => 'Bolivia Time',
                'shortname' => 'BOT',
                'hasdst' => false
        ],
        'Chile Time' => [
                'offset' => - 14400000,
                'longname' => 'Chile Time',
                'shortname' => 'CLT',
                'hasdst' => true,
                'dstlongname' => 'Chile Summer Time',
                'dstshortname' => 'CLST'
        ],
        'Falkland Is. Time' => [
                'offset' => - 14400000,
                'longname' => 'Falkland Is. Time',
                'shortname' => 'FKT',
                'hasdst' => true,
                'dstlongname' => 'Falkland Is. Summer Time',
                'dstshortname' => 'FKST'
        ],
        'GMT-04:00' => [
                'offset' => - 14400000,
                'longname' => 'GMT-04:00',
                'shortname' => 'GMT-04:00',
                'hasdst' => false
        ],
        'Newfoundland Standard Time' => [
                'offset' => - 12600000,
                'longname' => 'Newfoundland Standard Time',
                'shortname' => 'NST',
                'hasdst' => true,
                'dstlongname' => 'Newfoundland Daylight Time',
                'dstshortname' => 'NDT'
        ],
        'Argentine Time' => [
                'offset' => - 10800000,
                'longname' => 'Argentine Time',
                'shortname' => 'ART',
                'hasdst' => false
        ],
        'Brazil Time' => [
                'offset' => - 10800000,
                'longname' => 'Brazil Time',
                'shortname' => 'BRT',
                'hasdst' => true,
                'dstlongname' => 'Brazil Summer Time',
                'dstshortname' => 'BRST'
        ],
        'French Guiana Time' => [
                'offset' => - 10800000,
                'longname' => 'French Guiana Time',
                'shortname' => 'GFT',
                'hasdst' => false
        ],
        'Western Greenland Time' => [
                'offset' => - 10800000,
                'longname' => 'Western Greenland Time',
                'shortname' => 'WGT',
                'hasdst' => true,
                'dstlongname' => 'Western Greenland Summer Time',
                'dstshortname' => 'WGST'
        ],
        'Pierre & Miquelon Standard Time' => [
                'offset' => - 10800000,
                'longname' => 'Pierre & Miquelon Standard Time',
                'shortname' => 'PMST',
                'hasdst' => true,
                'dstlongname' => 'Pierre & Miquelon Daylight Time',
                'dstshortname' => 'PMDT'
        ],
        'Uruguay Time' => [
                'offset' => - 10800000,
                'longname' => 'Uruguay Time',
                'shortname' => 'UYT',
                'hasdst' => false
        ],
        'Suriname Time' => [
                'offset' => - 10800000,
                'longname' => 'Suriname Time',
                'shortname' => 'SRT',
                'hasdst' => false
        ],
        'GMT-03:00' => [
                'offset' => - 10800000,
                'longname' => 'GMT-03:00',
                'shortname' => 'GMT-03:00',
                'hasdst' => false
        ],
        'Fernando de Noronha Time' => [
                'offset' => - 7200000,
                'longname' => 'Fernando de Noronha Time',
                'shortname' => 'FNT',
                'hasdst' => false
        ],
        'South Georgia Standard Time' => [
                'offset' => - 7200000,
                'longname' => 'South Georgia Standard Time',
                'shortname' => 'GST',
                'hasdst' => false
        ],
        'GMT-02:00' => [
                'offset' => - 7200000,
                'longname' => 'GMT-02:00',
                'shortname' => 'GMT-02:00',
                'hasdst' => false
        ],
        'Eastern Greenland Time' => [
                'offset' => 3600000,
                'longname' => 'Eastern Greenland Time',
                'shortname' => 'EGT',
                'hasdst' => true,
                'dstlongname' => 'Eastern Greenland Summer Time',
                'dstshortname' => 'EGST'
        ],
        'Azores Time' => [
                'offset' => - 3600000,
                'longname' => 'Azores Time',
                'shortname' => 'AZOT',
                'hasdst' => true,
                'dstlongname' => 'Azores Summer Time',
                'dstshortname' => 'AZOST'
        ],
        'Cape Verde Time' => [
                'offset' => - 3600000,
                'longname' => 'Cape Verde Time',
                'shortname' => 'CVT',
                'hasdst' => false
        ],
        'GMT-01:00' => [
                'offset' => - 3600000,
                'longname' => 'GMT-01:00',
                'shortname' => 'GMT-01:00',
                'hasdst' => false
        ],
        'Greenwich Mean Time' => [
                'offset' => 0,
                'longname' => 'Greenwich Mean Time',
                'shortname' => 'GMT',
                'hasdst' => false
        ],
        'Western European Time' => [
                'offset' => 0,
                'longname' => 'Western European Time',
                'shortname' => 'WET',
                'hasdst' => true,
                'dstlongname' => 'Western European Summer Time',
                'dstshortname' => 'WEST'
        ],
        'GMT+00:00' => [
                'offset' => 0,
                'longname' => 'GMT+00:00',
                'shortname' => 'GMT+00:00',
                'hasdst' => false
        ],
        'Coordinated Universal Time' => [
                'offset' => 0,
                'longname' => 'Coordinated Universal Time',
                'shortname' => 'UTC',
                'hasdst' => false
        ],
        'Central European Time' => [
                'offset' => 3600000,
                'longname' => 'Central European Time',
                'shortname' => 'CET',
                'hasdst' => true,
                'dstlongname' => 'Central European Summer Time',
                'dstshortname' => 'CEST'
        ],
        'Western African Time' => [
                'offset' => 3600000,
                'longname' => 'Western African Time',
                'shortname' => 'WAT',
                'hasdst' => true,
                'dstlongname' => 'Western African Summer Time',
                'dstshortname' => 'WAST'
        ],
        'GMT+01:00' => [
                'offset' => 3600000,
                'longname' => 'GMT+01:00',
                'shortname' => 'GMT+01:00',
                'hasdst' => false
        ],
        'Middle Europe Time' => [
                'offset' => 3600000,
                'longname' => 'Middle Europe Time',
                'shortname' => 'MET',
                'hasdst' => true,
                'dstlongname' => 'Middle Europe Summer Time',
                'dstshortname' => 'MEST'
        ],
        'Eastern European Time' => [
                'offset' => 7200000,
                'longname' => 'Eastern European Time',
                'shortname' => 'EET',
                'hasdst' => true,
                'dstlongname' => 'Eastern European Summer Time',
                'dstshortname' => 'EEST'
        ],
        'Central African Time' => [
                'offset' => 7200000,
                'longname' => 'Central African Time',
                'shortname' => 'CAT',
                'hasdst' => false
        ],
        'South Africa Standard Time' => [
                'offset' => 7200000,
                'longname' => 'South Africa Standard Time',
                'shortname' => 'SAST',
                'hasdst' => false
        ],
        'Israel Standard Time' => [
                'offset' => 7200000,
                'longname' => 'Israel Standard Time',
                'shortname' => 'IST',
                'hasdst' => true,
                'dstlongname' => 'Israel Daylight Time',
                'dstshortname' => 'IDT'
        ],
        'GMT+02:00' => [
                'offset' => 7200000,
                'longname' => 'GMT+02:00',
                'shortname' => 'GMT+02:00',
                'hasdst' => false
        ],
        'Eastern African Time' => [
                'offset' => 10800000,
                'longname' => 'Eastern African Time',
                'shortname' => 'EAT',
                'hasdst' => false
        ],
        'Syowa Time' => [
                'offset' => 10800000,
                'longname' => 'Syowa Time',
                'shortname' => 'SYOT',
                'hasdst' => false
        ],
        'Arabia Standard Time' => [
                'offset' => 10800000,
                'longname' => 'Arabia Standard Time',
                'shortname' => 'AST',
                'hasdst' => false
        ],
        'GMT+03:00' => [
                'offset' => 10800000,
                'longname' => 'GMT+03:00',
                'shortname' => 'GMT+03:00',
                'hasdst' => false
        ],
        'Moscow Standard Time' => [
                'offset' => 10800000,
                'longname' => 'Moscow Standard Time',
                'shortname' => 'MSK',
                'hasdst' => true,
                'dstlongname' => 'Moscow Daylight Time',
                'dstshortname' => 'MSD'
        ],
        'GMT+03:07' => [
                'offset' => 11224000,
                'longname' => 'GMT+03:07',
                'shortname' => 'GMT+03:07',
                'hasdst' => false
        ],
        'Iran Time' => [
                'offset' => 12600000,
                'longname' => 'Iran Time',
                'shortname' => 'IRT',
                'hasdst' => true,
                'dstlongname' => 'Iran Sumer Time',
                'dstshortname' => 'IRST'
        ],
        'Aqtau Time' => [
                'offset' => 14400000,
                'longname' => 'Aqtau Time',
                'shortname' => 'AQTT',
                'hasdst' => true,
                'dstlongname' => 'Aqtau Summer Time',
                'dstshortname' => 'AQTST'
        ],
        'Azerbaijan Time' => [
                'offset' => 14400000,
                'longname' => 'Azerbaijan Time',
                'shortname' => 'AZT',
                'hasdst' => true,
                'dstlongname' => 'Azerbaijan Summer Time',
                'dstshortname' => 'AZST'
        ],
        'Gulf Standard Time' => [
                'offset' => 14400000,
                'longname' => 'Gulf Standard Time',
                'shortname' => 'GST',
                'hasdst' => false
        ],
        'Georgia Time' => [
                'offset' => 14400000,
                'longname' => 'Georgia Time',
                'shortname' => 'GET',
                'hasdst' => true,
                'dstlongname' => 'Georgia Summer Time',
                'dstshortname' => 'GEST'
        ],
        'Armenia Time' => [
                'offset' => 14400000,
                'longname' => 'Armenia Time',
                'shortname' => 'AMT',
                'hasdst' => true,
                'dstlongname' => 'Armenia Summer Time',
                'dstshortname' => 'AMST'
        ],
        'GMT+04:00' => [
                'offset' => 14400000,
                'longname' => 'GMT+04:00',
                'shortname' => 'GMT+04:00',
                'hasdst' => false
        ],
        'Samara Time' => [
                'offset' => 14400000,
                'longname' => 'Samara Time',
                'shortname' => 'SAMT',
                'hasdst' => true,
                'dstlongname' => 'Samara Summer Time',
                'dstshortname' => 'SAMST'
        ],
        'Seychelles Time' => [
                'offset' => 14400000,
                'longname' => 'Seychelles Time',
                'shortname' => 'SCT',
                'hasdst' => false
        ],
        'Mauritius Time' => [
                'offset' => 14400000,
                'longname' => 'Mauritius Time',
                'shortname' => 'MUT',
                'hasdst' => false
        ],
        'Reunion Time' => [
                'offset' => 14400000,
                'longname' => 'Reunion Time',
                'shortname' => 'RET',
                'hasdst' => false
        ],
        'Afghanistan Time' => [
                'offset' => 16200000,
                'longname' => 'Afghanistan Time',
                'shortname' => 'AFT',
                'hasdst' => false
        ],
        'Aqtobe Time' => [
                'offset' => 18000000,
                'longname' => 'Aqtobe Time',
                'shortname' => 'AQTT',
                'hasdst' => true,
                'dstlongname' => 'Aqtobe Summer Time',
                'dstshortname' => 'AQTST'
        ],
        'Turkmenistan Time' => [
                'offset' => 18000000,
                'longname' => 'Turkmenistan Time',
                'shortname' => 'TMT',
                'hasdst' => false
        ],
        'Kirgizstan Time' => [
                'offset' => 18000000,
                'longname' => 'Kirgizstan Time',
                'shortname' => 'KGT',
                'hasdst' => true,
                'dstlongname' => 'Kirgizstan Summer Time',
                'dstshortname' => 'KGST'
        ],
        'Tajikistan Time' => [
                'offset' => 18000000,
                'longname' => 'Tajikistan Time',
                'shortname' => 'TJT',
                'hasdst' => false
        ],
        'Pakistan Time' => [
                'offset' => 18000000,
                'longname' => 'Pakistan Time',
                'shortname' => 'PKT',
                'hasdst' => false
        ],
        'Uzbekistan Time' => [
                'offset' => 18000000,
                'longname' => 'Uzbekistan Time',
                'shortname' => 'UZT',
                'hasdst' => false
        ],
        'Yekaterinburg Time' => [
                'offset' => 18000000,
                'longname' => 'Yekaterinburg Time',
                'shortname' => 'YEKT',
                'hasdst' => true,
                'dstlongname' => 'Yekaterinburg Summer Time',
                'dstshortname' => 'YEKST'
        ],
        'GMT+05:00' => [
                'offset' => 18000000,
                'longname' => 'GMT+05:00',
                'shortname' => 'GMT+05:00',
                'hasdst' => false
        ],
        'French Southern & Antarctic Lands Time' => [
                'offset' => 18000000,
                'longname' => 'French Southern & Antarctic Lands Time',
                'shortname' => 'TFT',
                'hasdst' => false
        ],
        'Maldives Time' => [
                'offset' => 18000000,
                'longname' => 'Maldives Time',
                'shortname' => 'MVT',
                'hasdst' => false
        ],
        'India Standard Time' => [
                'offset' => 19800000,
                'longname' => 'India Standard Time',
                'shortname' => 'IST',
                'hasdst' => false
        ],
        'Nepal Time' => [
                'offset' => 20700000,
                'longname' => 'Nepal Time',
                'shortname' => 'NPT',
                'hasdst' => false
        ],
        'Mawson Time' => [
                'offset' => 21600000,
                'longname' => 'Mawson Time',
                'shortname' => 'MAWT',
                'hasdst' => false
        ],
        'Vostok time' => [
                'offset' => 21600000,
                'longname' => 'Vostok time',
                'shortname' => 'VOST',
                'hasdst' => false
        ],
        'Alma-Ata Time' => [
                'offset' => 21600000,
                'longname' => 'Alma-Ata Time',
                'shortname' => 'ALMT',
                'hasdst' => true,
                'dstlongname' => 'Alma-Ata Summer Time',
                'dstshortname' => 'ALMST'
        ],
        'Sri Lanka Time' => [
                'offset' => 21600000,
                'longname' => 'Sri Lanka Time',
                'shortname' => 'LKT',
                'hasdst' => false
        ],
        'Bangladesh Time' => [
                'offset' => 21600000,
                'longname' => 'Bangladesh Time',
                'shortname' => 'BDT',
                'hasdst' => false
        ],
        'Novosibirsk Time' => [
                'offset' => 21600000,
                'longname' => 'Novosibirsk Time',
                'shortname' => 'NOVT',
                'hasdst' => true,
                'dstlongname' => 'Novosibirsk Summer Time',
                'dstshortname' => 'NOVST'
        ],
        'Omsk Time' => [
                'offset' => 21600000,
                'longname' => 'Omsk Time',
                'shortname' => 'OMST',
                'hasdst' => true,
                'dstlongname' => 'Omsk Summer Time',
                'dstshortname' => 'OMSST'
        ],
        'Bhutan Time' => [
                'offset' => 21600000,
                'longname' => 'Bhutan Time',
                'shortname' => 'BTT',
                'hasdst' => false
        ],
        'GMT+06:00' => [
                'offset' => 21600000,
                'longname' => 'GMT+06:00',
                'shortname' => 'GMT+06:00',
                'hasdst' => false
        ],
        'Indian Ocean Territory Time' => [
                'offset' => 21600000,
                'longname' => 'Indian Ocean Territory Time',
                'shortname' => 'IOT',
                'hasdst' => false
        ],
        'Myanmar Time' => [
                'offset' => 23400000,
                'longname' => 'Myanmar Time',
                'shortname' => 'MMT',
                'hasdst' => false
        ],
        'Cocos Islands Time' => [
                'offset' => 23400000,
                'longname' => 'Cocos Islands Time',
                'shortname' => 'CCT',
                'hasdst' => false
        ],
        'Davis Time' => [
                'offset' => 25200000,
                'longname' => 'Davis Time',
                'shortname' => 'DAVT',
                'hasdst' => false
        ],
        'Indochina Time' => [
                'offset' => 25200000,
                'longname' => 'Indochina Time',
                'shortname' => 'ICT',
                'hasdst' => false
        ],
        'Hovd Time' => [
                'offset' => 25200000,
                'longname' => 'Hovd Time',
                'shortname' => 'HOVT',
                'hasdst' => false
        ],
        'West Indonesia Time' => [
                'offset' => 25200000,
                'longname' => 'West Indonesia Time',
                'shortname' => 'WIT',
                'hasdst' => false
        ],
        'Krasnoyarsk Time' => [
                'offset' => 25200000,
                'longname' => 'Krasnoyarsk Time',
                'shortname' => 'KRAT',
                'hasdst' => true,
                'dstlongname' => 'Krasnoyarsk Summer Time',
                'dstshortname' => 'KRAST'
        ],
        'GMT+07:00' => [
                'offset' => 25200000,
                'longname' => 'GMT+07:00',
                'shortname' => 'GMT+07:00',
                'hasdst' => false
        ],
        'Christmas Island Time' => [
                'offset' => 25200000,
                'longname' => 'Christmas Island Time',
                'shortname' => 'CXT',
                'hasdst' => false
        ],
        'Western Standard Time (Australia)' => [
                'offset' => 28800000,
                'longname' => 'Western Standard Time (Australia)',
                'shortname' => 'WST',
                'hasdst' => false
        ],
        'Brunei Time' => [
                'offset' => 28800000,
                'longname' => 'Brunei Time',
                'shortname' => 'BNT',
                'hasdst' => false
        ],
        'China Standard Time' => [
                'offset' => 28800000,
                'longname' => 'China Standard Time',
                'shortname' => 'CST',
                'hasdst' => false
        ],
        'Hong Kong Time' => [
                'offset' => 28800000,
                'longname' => 'Hong Kong Time',
                'shortname' => 'HKT',
                'hasdst' => false
        ],
        'Irkutsk Time' => [
                'offset' => 28800000,
                'longname' => 'Irkutsk Time',
                'shortname' => 'IRKT',
                'hasdst' => true,
                'dstlongname' => 'Irkutsk Summer Time',
                'dstshortname' => 'IRKST'
        ],
        'Malaysia Time' => [
                'offset' => 28800000,
                'longname' => 'Malaysia Time',
                'shortname' => 'MYT',
                'hasdst' => false
        ],
        'Philippines Time' => [
                'offset' => 28800000,
                'longname' => 'Philippines Time',
                'shortname' => 'PHT',
                'hasdst' => false
        ],
        'Singapore Time' => [
                'offset' => 28800000,
                'longname' => 'Singapore Time',
                'shortname' => 'SGT',
                'hasdst' => false
        ],
        'Central Indonesia Time' => [
                'offset' => 28800000,
                'longname' => 'Central Indonesia Time',
                'shortname' => 'CIT',
                'hasdst' => false
        ],
        'Ulaanbaatar Time' => [
                'offset' => 28800000,
                'longname' => 'Ulaanbaatar Time',
                'shortname' => 'ULAT',
                'hasdst' => false
        ],
        'GMT+08:00' => [
                'offset' => 28800000,
                'longname' => 'GMT+08:00',
                'shortname' => 'GMT+08:00',
                'hasdst' => false
        ],
        'Choibalsan Time' => [
                'offset' => 32400000,
                'longname' => 'Choibalsan Time',
                'shortname' => 'CHOT',
                'hasdst' => false
        ],
        'East Timor Time' => [
                'offset' => 32400000,
                'longname' => 'East Timor Time',
                'shortname' => 'TPT',
                'hasdst' => false
        ],
        'East Indonesia Time' => [
                'offset' => 32400000,
                'longname' => 'East Indonesia Time',
                'shortname' => 'EIT',
                'hasdst' => false
        ],
        'Korea Standard Time' => [
                'offset' => 32400000,
                'longname' => 'Korea Standard Time',
                'shortname' => 'KST',
                'hasdst' => false
        ],
        'Japan Standard Time' => [
                'offset' => 32400000,
                'longname' => 'Japan Standard Time',
                'shortname' => 'JST',
                'hasdst' => false
        ],
        'Yakutsk Time' => [
                'offset' => 32400000,
                'longname' => 'Yakutsk Time',
                'shortname' => 'YAKT',
                'hasdst' => true,
                'dstlongname' => 'Yaktsk Summer Time',
                'dstshortname' => 'YAKST'
        ],
        'GMT+09:00' => [
                'offset' => 32400000,
                'longname' => 'GMT+09:00',
                'shortname' => 'GMT+09:00',
                'hasdst' => false
        ],
        'Palau Time' => [
                'offset' => 32400000,
                'longname' => 'Palau Time',
                'shortname' => 'PWT',
                'hasdst' => false
        ],
        'Central Standard Time (Northern Territory)' => [
                'offset' => 34200000,
                'longname' => 'Central Standard Time (Northern Territory)',
                'shortname' => 'CST',
                'hasdst' => false
        ],
        'Central Standard Time (South Australia)' => [
                'offset' => 34200000,
                'longname' => 'Central Standard Time (South Australia)',
                'shortname' => 'CST',
                'hasdst' => true,
                'dstlongname' => 'Central Summer Time (South Australia)',
                'dstshortname' => 'CST'
        ],
        'Central Standard Time (South Australia/New South Wales)' => [
                'offset' => 34200000,
                'longname' => 'Central Standard Time (South Australia/New South Wales)',
                'shortname' => 'CST',
                'hasdst' => true,
                'dstlongname' => 'Central Summer Time (South Australia/New South Wales)',
                'dstshortname' => 'CST'
        ],
        'Eastern Standard Time (New South Wales)' => [
                'offset' => 36000000,
                'longname' => 'Eastern Standard Time (New South Wales)',
                'shortname' => 'EST',
                'hasdst' => true,
                'dstlongname' => 'Eastern Summer Time (New South Wales)',
                'dstshortname' => 'EST'
        ],
        'Dumont-d\'Urville Time' => [
                'offset' => 36000000,
                'longname' => 'Dumont-d\'Urville Time',
                'shortname' => 'DDUT',
                'hasdst' => false
        ],
        'Sakhalin Time' => [
                'offset' => 36000000,
                'longname' => 'Sakhalin Time',
                'shortname' => 'SAKT',
                'hasdst' => true,
                'dstlongname' => 'Sakhalin Summer Time',
                'dstshortname' => 'SAKST'
        ],
        'Vladivostok Time' => [
                'offset' => 36000000,
                'longname' => 'Vladivostok Time',
                'shortname' => 'VLAT',
                'hasdst' => true,
                'dstlongname' => 'Vladivostok Summer Time',
                'dstshortname' => 'VLAST'
        ],
        'Eastern Standard Time (Queensland)' => [
                'offset' => 36000000,
                'longname' => 'Eastern Standard Time (Queensland)',
                'shortname' => 'EST',
                'hasdst' => false
        ],
        'Eastern Standard Time (Tasmania)' => [
                'offset' => 36000000,
                'longname' => 'Eastern Standard Time (Tasmania)',
                'shortname' => 'EST',
                'hasdst' => true,
                'dstlongname' => 'Eastern Summer Time (Tasmania)',
                'dstshortname' => 'EST'
        ],
        'Eastern Standard Time (Victoria)' => [
                'offset' => 36000000,
                'longname' => 'Eastern Standard Time (Victoria)',
                'shortname' => 'EST',
                'hasdst' => true,
                'dstlongname' => 'Eastern Summer Time (Victoria)',
                'dstshortname' => 'EST'
        ],
        'GMT+10:00' => [
                'offset' => 36000000,
                'longname' => 'GMT+10:00',
                'shortname' => 'GMT+10:00',
                'hasdst' => false
        ],
        'Chamorro Standard Time' => [
                'offset' => 36000000,
                'longname' => 'Chamorro Standard Time',
                'shortname' => 'ChST',
                'hasdst' => false
        ],
        'Papua New Guinea Time' => [
                'offset' => 36000000,
                'longname' => 'Papua New Guinea Time',
                'shortname' => 'PGT',
                'hasdst' => false
        ],
        'Truk Time' => [
                'offset' => 36000000,
                'longname' => 'Truk Time',
                'shortname' => 'TRUT',
                'hasdst' => false
        ],
        'Yap Time' => [
                'offset' => 36000000,
                'longname' => 'Yap Time',
                'shortname' => 'YAPT',
                'hasdst' => false
        ],
        'Load Howe Standard Time' => [
                'offset' => 37800000,
                'longname' => 'Load Howe Standard Time',
                'shortname' => 'LHST',
                'hasdst' => true,
                'dstlongname' => 'Load Howe Summer Time',
                'dstshortname' => 'LHST'
        ],
        'Magadan Time' => [
                'offset' => 39600000,
                'longname' => 'Magadan Time',
                'shortname' => 'MAGT',
                'hasdst' => true,
                'dstlongname' => 'Magadan Summer Time',
                'dstshortname' => 'MAGST'
        ],
        'GMT+11:00' => [
                'offset' => 39600000,
                'longname' => 'GMT+11:00',
                'shortname' => 'GMT+11:00',
                'hasdst' => false
        ],
        'Vanuatu Time' => [
                'offset' => 39600000,
                'longname' => 'Vanuatu Time',
                'shortname' => 'VUT',
                'hasdst' => false
        ],
        'Solomon Is. Time' => [
                'offset' => 39600000,
                'longname' => 'Solomon Is. Time',
                'shortname' => 'SBT',
                'hasdst' => false
        ],
        'Kosrae Time' => [
                'offset' => 39600000,
                'longname' => 'Kosrae Time',
                'shortname' => 'KOST',
                'hasdst' => false
        ],
        'New Caledonia Time' => [
                'offset' => 39600000,
                'longname' => 'New Caledonia Time',
                'shortname' => 'NCT',
                'hasdst' => false
        ],
        'Ponape Time' => [
                'offset' => 39600000,
                'longname' => 'Ponape Time',
                'shortname' => 'PONT',
                'hasdst' => false
        ],
        'Norfolk Time' => [
                'offset' => 41400000,
                'longname' => 'Norfolk Time',
                'shortname' => 'NFT',
                'hasdst' => false
        ],
        'New Zealand Standard Time' => [
                'offset' => 43200000,
                'longname' => 'New Zealand Standard Time',
                'shortname' => 'NZST',
                'hasdst' => true,
                'dstlongname' => 'New Zealand Daylight Time',
                'dstshortname' => 'NZDT'
        ],
        'Anadyr Time' => [
                'offset' => 43200000,
                'longname' => 'Anadyr Time',
                'shortname' => 'ANAT',
                'hasdst' => true,
                'dstlongname' => 'Anadyr Summer Time',
                'dstshortname' => 'ANAST'
        ],
        'Petropavlovsk-Kamchatski Time' => [
                'offset' => 43200000,
                'longname' => 'Petropavlovsk-Kamchatski Time',
                'shortname' => 'PETT',
                'hasdst' => true,
                'dstlongname' => 'Petropavlovsk-Kamchatski Summer Time',
                'dstshortname' => 'PETST'
        ],
        'GMT+12:00' => [
                'offset' => 43200000,
                'longname' => 'GMT+12:00',
                'shortname' => 'GMT+12:00',
                'hasdst' => false
        ],
        'Marshall Islands Time' => [
                'offset' => 43200000,
                'longname' => 'Marshall Islands Time',
                'shortname' => 'MHT',
                'hasdst' => false
        ],
        'Fiji Time' => [
                'offset' => 43200000,
                'longname' => 'Fiji Time',
                'shortname' => 'FJT',
                'hasdst' => false
        ],
        'Tuvalu Time' => [
                'offset' => 43200000,
                'longname' => 'Tuvalu Time',
                'shortname' => 'TVT',
                'hasdst' => false
        ],
        'Nauru Time' => [
                'offset' => 43200000,
                'longname' => 'Nauru Time',
                'shortname' => 'NRT',
                'hasdst' => false
        ],
        'Gilbert Is. Time' => [
                'offset' => 43200000,
                'longname' => 'Gilbert Is. Time',
                'shortname' => 'GILT',
                'hasdst' => false
        ],
        'Wake Time' => [
                'offset' => 43200000,
                'longname' => 'Wake Time',
                'shortname' => 'WAKT',
                'hasdst' => false
        ],
        'Wallis & Futuna Time' => [
                'offset' => 43200000,
                'longname' => 'Wallis & Futuna Time',
                'shortname' => 'WFT',
                'hasdst' => false
        ],
        'Chatham Standard Time' => [
                'offset' => 45900000,
                'longname' => 'Chatham Standard Time',
                'shortname' => 'CHAST',
                'hasdst' => true,
                'dstlongname' => 'Chatham Daylight Time',
                'dstshortname' => 'CHADT'
        ],
        'GMT+13:00' => [
                'offset' => 46800000,
                'longname' => 'GMT+13:00',
                'shortname' => 'GMT+13:00',
                'hasdst' => false
        ],
        'Phoenix Is. Time' => [
                'offset' => 46800000,
                'longname' => 'Phoenix Is. Time',
                'shortname' => 'PHOT',
                'hasdst' => false
        ],
        'Tonga Time' => [
                'offset' => 46800000,
                'longname' => 'Tonga Time',
                'shortname' => 'TOT',
                'hasdst' => false
        ],
        'GMT+14:00' => [
                'offset' => 50400000,
                'longname' => 'GMT+14:00',
                'shortname' => 'GMT+14:00',
                'hasdst' => false
        ],
        'Line Is. Time' => [
                'offset' => 50400000,
                'longname' => 'Line Is. Time',
                'shortname' => 'LINT',
                'hasdst' => false
        ]
];

/**
 * Initialize default timezone
 *
 * First try _DATE_TIMEZONE_DEFAULT global, then PHP_TZ environment var,
 * then TZ environment var
 */
if (isset($GLOBALS ['_DATE_TIMEZONE_DEFAULT']) && TimeZone::isValidID($GLOBALS ['_DATE_TIMEZONE_DEFAULT'])) {
    TimeZone::setDefault($GLOBALS ['_DATE_TIMEZONE_DEFAULT']);
} elseif (getenv('PHP_TZ') && TimeZone::isValidID(getenv('PHP_TZ'))) {
    TimeZone::setDefault(getenv('PHP_TZ'));
} elseif (getenv('TZ') && TimeZone::isValidID(getenv('TZ'))) {
    TimeZone::setDefault(getenv('TZ'));
} elseif (TimeZone::isValidID(date('T'))) {
    TimeZone::setDefault(date('T'));
} else {
    TimeZone::setDefault('UTC');
}

/*
 * Local variables: mode: php tab-width: 4 c-basic-offset: 4 c-hanging-comment-ender-p: nil End:
 */
