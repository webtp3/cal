<?php
namespace TYPO3\CMS\Cal\Hooks;

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
use Doctrine\DBAL\FetchMode;
use OutOfBoundsException;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Cal\Controller\Api;
use TYPO3\CMS\Cal\Service\ICalendarService;
use TYPO3\CMS\Cal\Utility\Functions;
use TYPO3\CMS\Cal\Utility\RecurrenceGenerator;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Scheduler\Scheduler;

/**
 * This hook extends the tcemain class.
 * It catches changes on tx_cal_event
 */
class TceMainProcesscmdmap
{
    /**
     * @param string $command
     * @param string $table
     * @param $id
     * @param $value
     * @param $tce
     * @throws \TYPO3\CMS\Core\Error\Http\PageNotFoundException
     * @throws \TYPO3\CMS\Core\Error\Http\ServiceUnavailableException
     * @throws \TYPO3\CMS\Core\Exception
     */
    public function processCmdmap_postProcess(&$command, &$table, &$id, &$value, &$tce)
    {
        /** @var ConnectionPool $connectionPool */
        $connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);

        switch ($table) {
            case 'tx_cal_event':
                $query = $connectionPool->getConnectionForTable('tx_cal_event');
                $result = $query->select(['*'], 'tx_cal_event', ['uid' => $id]);

                if ($result) {
                    while ($row = $result->fetch(FetchMode::ASSOCIATIVE)) {

                        /* If we're in a workspace, don't notify anyone about the event */
                        if ($row ['pid'] > 0 && !$GLOBALS['BE_USER']->workspace) {
                            /* Check Page TSConfig for a preview page that we should use */
                            $pageIDForPlugin = $this->getPageIDForPlugin($row ['pid']);

                            $page = BackendUtility::getRecord('pages', intval($pageIDForPlugin), 'doktype');
                            if ($page ['doktype'] !== 254) {
                                $tx_cal_api = GeneralUtility::makeInstance(Api::class);
                                $tx_cal_api = &$tx_cal_api->tx_cal_api_without($pageIDForPlugin);

                                $notificationService = Functions::getNotificationService();
                                if ($command === 'delete') {
                                    /* If the deleted event is temporary, reset the MD5 of the parent calendar */
                                    if ($row ['isTemp']) {
                                        $calendar_id = $row ['calendar_id'];
                                        $insertFields = [
                                                'md5' => ''
                                        ];
                                        $update = $connectionPool->getConnectionForTable('tx_cal_calendar');
                                        $update->update('tx_cal_calendar', $insertFields, ['uid' => $calendar_id]);
                                    }

                                    /** @var RecurrenceGenerator $rgc */
                                    $rgc = GeneralUtility::makeInstance(RecurrenceGenerator::class);
                                    $rgc->cleanIndexTableOfUid($id, $table);

                                    /* Delete all deviations of the event */
                                    $delete = $connectionPool->getConnectionForTable('tx_cal_event_deviation');
                                    $delete->delete('tx_cal_event_deviation', ['parentid' => $id]);
                                } else {
                                    $notificationService->notifyOfChanges($row, [
                                            $command => $value
                                    ]);
                                }
                            }
                        }
                    }
                }
                break;
            case 'tx_cal_calendar':
                /* If a calendar has been deleted, we might need to clean up. */

                if ($command === 'delete') {
                    /* Using getRecordRaw rather than getRecord since the record has already been deleted. */
                    $calendarRow = BackendUtility::getRecord('tx_cal_calendar', $id, '*', '', false);
                    /* If the calendar is an External URL or ICS file, then we need to clean up */
                    if (($calendarRow ['type'] === 1) || ($calendarRow ['type'] === 2)) {
                        $service = new ICalendarService();
                        $service->deleteTemporaryEvents($id);
                        $service->deleteTemporaryCategories($id);
                        $service->deleteScheduledUpdates($id);
                        $service->deleteSchedulerTask($id);
                    }
                }

                if ($command === 'copy') {
                    $newCalendarIds = $tce->copyMappingArray ['tx_cal_calendar'];

                    // check if source of copy has a scheduler task attached
                    $calendarRow = BackendUtility::getRecord('tx_cal_calendar', $id);
                    if ($calendarRow ['schedulerId'] > 0) {
                        $scheduler = new Scheduler();
                        $service = new ICalendarService();
                        foreach ($newCalendarIds as $newCalendarId) {
                            $service->createSchedulerTask($scheduler, 0, $newCalendarId);
                        }
                    }
                }
                break;

            case 'tx_cal_exception_event_group':
            case 'tx_cal_exception_event':
                if ($command === 'delete') {
                    $query = $connectionPool->getConnectionForTable($table);
                    $result = $query->select(['*'], $table, ['uid' => $id]);
                    if ($result) {
                        while ($row = $result->fetch(FetchMode::ASSOCIATIVE)) {

                            /* If we're in a workspace, don't notify anyone about the event */
                            if ($row ['pid'] > 0 && !$GLOBALS['BE_USER']->workspace) {
                                /* Check Page TSConfig for a preview page that we should use */
                                $pageIDForPlugin = $this->getPageIDForPlugin($row ['pid']);

                                $page = BackendUtility::getRecord('pages', intval($pageIDForPlugin), 'doktype');
                                if ($page ['doktype'] !== 254) {
                                    $tx_cal_api = new Api();
                                    $tx_cal_api = &$tx_cal_api->tx_cal_api_without($pageIDForPlugin);

                                    /** @var RecurrenceGenerator $rgc */
                                    $rgc = GeneralUtility::makeInstance(RecurrenceGenerator::class);
                                    $rgc->cleanIndexTableOfUid($id, $table);
                                }
                            }
                        }
                    }
                }
                break;
                case 'tx_cal_event_deviation':
                    if ($command === 'delete') {
                        $query = $connectionPool->getQueryBuilderForTable('tx_cal_event_deviation');
                        $result = $query->select(['tx_cal_event.uid', 'tx_cal_event.pid'])->from('tx_cal_event', 'E')
                            ->join('E', 'tx_cal_index', 'I', 'I.event_uid = E.uid')
                            ->where($query->expr()->eq('I.event_deviation_uid', $id))->execute();
                        if ($result) {
                            while ($row = $result->fetch(FetchMode::ASSOCIATIVE)) {
                                $this->reindexEvent($row['uid'], $row['pid']);
                            }
                        }
                    }
                    break;
        }
    }

    /**
     * @param string $command
     * @param string $table
     * @param $id
     * @param $value
     * @param $tce
     * @throws \TYPO3\CMS\Core\Error\Http\PageNotFoundException
     * @throws \TYPO3\CMS\Core\Error\Http\ServiceUnavailableException
     */
    public function processCmdmap_preProcess(&$command, &$table, &$id, &$value, &$tce)
    {
        /** @var ConnectionPool $connectionPool */
        $connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);

        switch ($table) {
            case 'tx_cal_event':
                if ($command === 'delete') {
                    $query = $connectionPool->getConnectionForTable('tx_cal_event');
                    $result = $query->select(['*'], 'tx_cal_event', ['uid' => $id]);

                    if ($result) {
                        while ($row = $result->fetch(FetchMode::ASSOCIATIVE)) {

                            /* If we're in a workspace, don't notify anyone about the event */
                            if ($row ['pid'] > 0 && !$GLOBALS['BE_USER']->workspace) {
                                /* Check Page TSConfig for a preview page that we should use */
                                $pageTSConf = BackendUtility::getPagesTSconfig($row ['pid']);
                                if ($pageTSConf ['options.'] ['tx_cal_controller.'] ['pageIDForPlugin']) {
                                    $pageIDForPlugin = $pageTSConf ['options.'] ['tx_cal_controller.'] ['pageIDForPlugin'];
                                } else {
                                    $pageIDForPlugin = $row ['pid'];
                                }

                                $page = BackendUtility::getRecord('pages', intval($pageIDForPlugin), 'doktype');
                                if ($page ['doktype'] !== 254) {
                                    $tx_cal_api = GeneralUtility::makeInstance(Api::class);
                                    $tx_cal_api = &$tx_cal_api->tx_cal_api_without($pageIDForPlugin);

                                    $notificationService = & Functions::getNotificationService();
                                    // Need to enforce deletion mode
                                    $notificationService->notify($row, 1);
                                }
                            }
                        }
                    }
                    // We have to delete the gabriel/scheduler events BEFORE the tx_cal_events and
                    // its related tx_cal_fe_user_event_monitor_mm records are gone

                    /* Clean up any pending reminders for this event */
                    $reminderService = &Functions::getReminderService();
                    try {
                        $reminderService->deleteReminderForEvent($id);
                    } catch (OutOfBoundsException $e) {
                    }
                }
                break;
            case 'tx_cal_fe_user_event_monitor_mm':
                if ($command === 'delete') {
                    $relationRecord = BackendUtility::getRecord('tx_cal_fe_user_event_monitor_mm', $id);
                    // We have to delete the gabriel events BEFORE the tx_cal_events and
                    // its related tx_cal_fe_user_event_monitor_mm records are gone

                    /* Clean up any pending reminders for this event */
                    $reminderService = &Functions::getReminderService();
                    try {
                        $reminderService->deleteReminder($relationRecord ['uid_local']);
                    } catch (OutOfBoundsException $e) {
                    }
                }
                break;
        }
    }

    /**
     * @param int $eventUid
     * @param int $pid
     * @throws \TYPO3\CMS\Core\Exception
     */
    public function reindexEvent($eventUid, $pid)
    {
        /* If we're in a workspace, don't notify anyone about the event */
        if ($pid > 0 && !$GLOBALS['BE_USER']->workspace) {
            /* Check Page TSConfig for a preview page that we should use */
            $pageIDForPlugin = $this->getPageIDForPlugin($pid);

            $page = BackendUtility::getRecord('pages', intval($pageIDForPlugin), 'doktype');
            if ($page ['doktype'] !== 254) {
                /** @var RecurrenceGenerator $rgc */
                $rgc = GeneralUtility::makeInstance(RecurrenceGenerator::class);
                $rgc->generateIndexForUid($eventUid, 'tx_cal_event');
            }
        }
    }

    /**
     * @param int $pid
     * @return int
     */
    public function getPageIDForPlugin($pid): int
    {
        $pageTSConf = BackendUtility::getPagesTSconfig($pid);
        if ($pageTSConf ['options.'] ['tx_cal_controller.'] ['pageIDForPlugin']) {
            return (int)$pageTSConf ['options.'] ['tx_cal_controller.'] ['pageIDForPlugin'];
        }
        return $pid;
    }
}
