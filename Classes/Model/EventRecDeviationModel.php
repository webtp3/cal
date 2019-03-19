<?php

/*
 * This file is part of the web-tp3/cal.
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace TYPO3\CMS\Cal\Model;

use TYPO3\CMS\Cal\Utility\Functions;

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

/**
 * A concrete model for the calendar.
 */
class EventRecDeviationModel extends EventModel
{
    /**
     * @var CalDate
     */
    private $origStartDate;

    /**
     * EventRecDeviationModel constructor.
     * @param EventModel $event
     * @param $row
     * @param $start
     * @param $end
     */
    public function __construct($event, $row, $start, $end)
    {
        parent::__construct($row, true, $event->serviceKey);
        $deviationId = $row['uid'];
        unset(
            $row['uid'],
            $row['pid'],
            $row['parentid'],
            $row['tstamp'],
            $row['crdate'],
            $row['cruser_id'],
            $row['deleted'],
            $row['hidden'],
            $row['starttime'],
            $row['endtime']
        );
        // storing allday in a temp var, in case it is set from 1 to 0
        $allday = $row['allday'];
        $row = array_merge($event->row, array_filter($row));
        $row['allday'] = $allday;
        $row['deviationId'] = $deviationId;
        $this->createEvent($row, false);

        $this->setStart($start);
        $this->setEnd($end);

        $this->setAllDay($row['allday']);
        $this->origStartDate = new  CalDate($row['orig_start_date']);
        $this->origStartDate->addSeconds($row['orig_start_time']);

        $this->setCategories($event->getCategories());
        $this->setSharedGroups($event->getSharedGroups());
        $this->setSharedUsers($event->getSharedUsers());
    }

    /**
     * @param $template
     * @param $sims
     * @param $rems
     * @param $wrapped
     * @param $view
     */
    public function getRRuleMarker(&$template, &$sims, &$rems, &$wrapped, $view)
    {
        $eventStart = $this->origStartDate;
        if ($this->isAllday()) {
            $sims['###RRULE###'] = 'RECURRENCE-ID;VALUE=DATE:' . $eventStart->format('Ymd');
        } elseif ($this->conf['view.']['ics.']['timezoneId'] !== '') {
            $sims['###RRULE###'] = 'RECURRENCE-ID;TZID=' . $this->conf['view.']['ics.']['timezoneId'] . ':' . $eventStart->format('YmdTHMS');
        } else {
            $offset = Functions::strtotimeOffset($eventStart->getTime());
            $eventStart->subtractSeconds($offset);
            $sims['###RRULE###'] = 'RECURRENCE-ID:' . $eventStart->format('YmdTHMSZ');
            $eventStart->addSeconds($offset);
        }
    }

    /**
     * @return mixed
     */
    public function getDeviationId()
    {
        return $this->row['deviationId'];
    }

    /**
     * @param $deviationId
     */
    public function setDeviationId($deviationId)
    {
        $this->row['deviationId'] = $deviationId;
    }
}
