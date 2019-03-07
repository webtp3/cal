<?php

/*
 * This file is part of the web-tp3/cal.
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace TYPO3\CMS\Cal\Cron;

use TYPO3\CMS\Cal\Service\ICalendarService;
use TYPO3\CMS\Scheduler\Task\AbstractTask;

/**
 * CalendarScheduler
 */
class CalendarScheduler extends AbstractTask
{
    public $uid;

    /**
     * @return bool
     */
    public function execute(): bool
    {
        $success = true;
        $service = new ICalendarService();
        $service->update($this->uid);

        return $success;
    }

    /**
     * @return mixed
     */
    public function getUID()
    {
        return $this->uid;
    }

    /**
     * @param $uid
     */
    public function setUID($uid)
    {
        $this->uid = $uid;
    }
}
