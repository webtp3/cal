<?php

/*
 * This file is part of the web-tp3/cal.
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace TYPO3\CMS\Cal\Model\ICalendar;

use TYPO3\CMS\Cal\Model\ICalendar;

/**
 * Class representing vTodos.
 *
 * $Horde: framework/iCalendar/iCalendar/vtodo.php,v 1.13.10.4 2006/01/01 21:28:47 jan Exp $
 *
 * Copyright 2003-2006 Mike Cochrane <mike@graftonhall.co.nz>
 *
 * See the enclosed file COPYING for license information (LGPL). If you
 * did not receive this file, see http://www.fsf.org/copyleft/lgpl.html.
 *
 * @since Horde 3.0
 */
class vtodo extends ICalendar
{
    public function getType() : string
    {
        return 'vTodo';
    }

    public function parsevCalendar($data, $base = 'VCALENDAR', $charset = 'utf8', $clear = true) : bool
    {
        parent::parsevCalendar($data, 'VTODO');
    }

    public function exportvCalendar() : string
    {
        return parent::_exportvData('VTODO');
    }

    /**
     * Convert this todo to an array of attributes.
     *
     * @return array Array containing the details of the todo in a hash
     *         as used by Horde applications.
     */
    public function toArray()
    {
        $todo = [];

        $name = $this->getAttribute('SUMMARY');
        if (!is_array($name) && !is_a($name, 'PEAR_Error')) {
            $todo['name'] = $name;
        }
        $desc = $this->getAttribute('DESCRIPTION');
        if (!is_array($desc) && !is_a($desc, 'PEAR_Error')) {
            $todo['desc'] = $desc;
        }

        $priority = $this->getAttribute('PRIORITY');
        if (!is_array($priority) && !is_a($priority, 'PEAR_Error')) {
            $todo['priority'] = $priority;
        }

        $due = $this->getAttribute('DTSTAMP');
        if (!is_array($due) && !is_a($due, 'PEAR_Error')) {
            $todo['due'] = $due;
        }

        return $todo;
    }

    /**
     * Set the attributes for this todo item from an array.
     *
     * @param array $todo
     *            Array containing the details of the todo in
     *            the same format that toArray() exports.
     */
    public function fromArray($todo)
    {
        if (isset($todo['name'])) {
            $this->setAttribute('SUMMARY', $todo['name']);
        }
        if (isset($todo['desc'])) {
            $this->setAttribute('DESCRIPTION', $todo['desc']);
        }

        if (isset($todo['priority'])) {
            $this->setAttribute('PRIORITY', $todo['priority']);
        }

        if (isset($todo['due'])) {
            $this->setAttribute('DTSTAMP', $todo['due']);
        }
    }
}
