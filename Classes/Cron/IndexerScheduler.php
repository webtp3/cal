<?php

/*
 * This file is part of the web-tp3/cal.
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace TYPO3\CMS\Cal\Cron;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Scheduler\Task\AbstractTask;

/**
 * IndexerScheduler
 */
class IndexerScheduler extends AbstractTask
{
    public $eventFolder = '';

    public $typoscriptPage = '';

    public $starttime = '';

    public $endtime = '';

    public function execute()
    {
        $success = true;
        $logger = GeneralUtility::makeInstance('TYPO3\CMS\Core\Log\LogManager')->getLogger(__CLASS__);

        $starttime = $this->getTimeParsed($this->starttime)->format('%Y%m%d');
        $endtime = $this->getTimeParsed($this->endtime)->format('%Y%m%d');

        $logger->info('Starting to index cal events from ' . $starttime . ' until ' . $endtime . '. Using Typoscript page ' . $this->typoscriptPage . ' as configuration reference.');
        /** @var \TYPO3\CMS\Cal\Utility\RecurrenceGenerator $rgc */
        $rgc = GeneralUtility::makeInstance('TYPO3\\CMS\\Cal\\Utility\\RecurrenceGenerator', $this->typoscriptPage, $starttime, $endtime);
        foreach (explode(',', $this->eventFolder) as $folderId) {
            $eventFolder = intval($folderId);
            if ($eventFolder > 0) {
                $logger->info('Working with folder ' . $eventFolder);
                $rgc->cleanIndexTable($eventFolder);
                $logger->info('Starting to index... ');
                $rgc->generateIndex($eventFolder);
                $logger->info('done.');
            }
        }
        $logger->info('IndexerScheduler done.');
        return $success;
    }

    private function getTimeParsed($timeString)
    {
        $dp = GeneralUtility::makeInstance('TYPO3\\CMS\\Cal\\Controller\\DateParser');
        $dp->parse($timeString, 0, '');
        return $dp->getDateObjectFromStack();
    }
}
