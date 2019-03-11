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
     * PHP4 wrapper for constructor,
     * have to be here even though the constructor is not defined in the derived class,
     * else the constructor of the parent class will not be called in PHP4
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function execute()
    {
        $success = true;
        $service = new ICalendarService();
        $service->update($this->uid);

        return $success;
    }

    public function getUID()
    {
        return $this->uid;
    }

    public function setUID($uid)
    {
        $this->uid = $uid;
    }
}
