<?php

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
    public function execute()
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
