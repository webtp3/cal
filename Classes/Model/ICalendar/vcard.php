<?php

/*
 * This file is part of the web-tp3/cal.
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace TYPO3\CMS\Cal\Model\ICalendar;

// The following were shamelessly yoinked from Contact_Vcard_Build
// Part numbers for N components.
use TYPO3\CMS\Cal\Model\ICalendar;

define('VCARD_N_FAMILY', 0);
define('VCARD_N_GIVEN', 1);
define('VCARD_N_ADDL', 2);
define('VCARD_N_PREFIX', 3);
define('VCARD_N_SUFFIX', 4);

// Part numbers for ADR components.
define('VCARD_ADR_POB', 0);
define('VCARD_ADR_EXTEND', 1);
define('VCARD_ADR_STREET', 2);
define('VCARD_ADR_LOCALITY', 3);
define('VCARD_ADR_REGION', 4);
define('VCARD_ADR_POSTCODE', 5);
define('VCARD_ADR_COUNTRY', 6);

// Part numbers for GEO components.
define('VCARD_GEO_LAT', 0);
define('VCARD_GEO_LON', 1);

/**
 * Class representing vCard entries.
 *
 * $Horde: framework/iCalendar/iCalendar/vcard.php,v 1.3.10.7 2006/03/03 09:07:21 jan Exp $
 *
 * Copyright 2003-2006 Karsten Fourmont (karsten@horde.org)
 *
 * See the enclosed file COPYING for license information (LGPL). If you
 * did not receive this file, see http://www.fsf.org/copyleft/lgpl.html.
 *
 */
class vcard extends ICalendar
{
    public function __construct($version = '2.1')
    {
        return parent::__construct($version);
    }

    public function getType() : string
    {
        return 'vcard';
    }

    public function parsevCalendar($data, $base = 'VCALENDAR', $charset = 'utf8', $clear = true) :string
    {
        return parent::parsevCalendar($data, 'vcard');
    }

    /**
     * Unlike vevent and vtodo, a vcard is normally not enclosed in an
     * iCalendar container.
     * (BEGIN..END)
     */
    public function exportvCalendar()
    {
        $requiredAttributes = [];
        $requiredAttributes['BODY'] = '';
        $requiredAttributes['VERSION'] = '2.1';

        foreach ($requiredAttributes as $name => $default_value) {
            if (is_a($this->getAttribute($name), 'PEAR_Error')) {
                $this->setAttribute($name, $default_value);
            }
        }

        return $this->_exportvData('VCARD');
    }

    /**
     * Returns the contents of the "N" tag as a printable Name:
     * i.e.
     * converts:
     *
     * N:Duck;Dagobert;T;Professor;Sen.
     * to
     * "Professor Dagobert T Duck Sen"
     *
     * @return string Full name of vcard "N" tag
     *         or null if no N tag.
     */
    public function printableName()
    {
        $name_parts = $this->getAttributeValues('N');
        if (is_a($name_parts, 'PEAR_Error')) {
            return null;
        }

        $name_arr = [];
        if (!empty($name_parts[VCARD_N_PREFIX])) {
            $name_arr[] = $name_parts[VCARD_N_PREFIX];
        }
        if (!empty($name_parts[VCARD_N_GIVEN])) {
            $name_arr[] = $name_parts[VCARD_N_GIVEN];
        }
        if (!empty($name_parts[VCARD_N_ADDL])) {
            $name_arr[] = $name_parts[VCARD_N_ADDL];
        }
        if (!empty($name_parts[VCARD_N_FAMILY])) {
            $name_arr[] = $name_parts[VCARD_N_FAMILY];
        }
        if (!empty($name_parts[VCARD_N_SUFFIX])) {
            $name_arr[] = $name_parts[VCARD_N_SUFFIX];
        }

        return implode(' ', $name_arr);
    }
}
