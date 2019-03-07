<?php

/*
 * This file is part of the web-tp3/cal.
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace TYPO3\CMS\Cal\Model\ICalendar;

use TYPO3\CMS\Cal\Model\ICalendar;

/**
 * Class representing vTimezones.
 *
 * $Horde: framework/iCalendar/iCalendar/vtimezone.php,v 1.8.10.4 2006/01/01 21:28:47 jan Exp $
 *
 * Copyright 2003-2006 Mike Cochrane <mike@graftonhall.co.nz>
 *
 * See the enclosed file COPYING for license information (LGPL). If you
 * did not receive this file, see http://www.fsf.org/copyleft/lgpl.html.
 *
 * @since Horde 3.0
 */
class standard extends ICalendar
{
    public function getType(): string
    {
        return 'standard';
    }

    /**
     * Parses a string containing vCalendar data.
     *
     * @param string $text
     *            The data to parse.
     * @param string $base
     *            The type of the base object.
     * @param string $charset
     *            The encoding charset for $text. Defaults to
     *            utf-8.
     * @param bool $clear
     *            If true clears the iCal object before parsing.
     *
     * @return bool True on successful import, false otherwise.
     */
    public function parsevCalendar($data, $base = 'VCALENDAR', $charset = 'utf8', $clear = true): bool
    {
        parent::parsevCalendar($data, 'STANDARD');
    }

    /**
     * Export as vCalendar format.
     */
    public function exportvCalendar(): string
    {
        return parent::_exportvData('STANDARD');
    }
}
