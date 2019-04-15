<?php

/*
 * This file is part of the web-tp3/cal.
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

use TYPO3\CMS\Cal\Backend\Modul\CalIndexer;
use TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider;
use TYPO3\CMS\Core\Imaging\IconRegistry;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

if (! defined('TYPO3_MODE')) {
    die('Access denied.');
}

$extPath = ExtensionManagementUtility::extPath('cal');

// Allow all calendar records on standard pages, in addition to SysFolders.
ExtensionManagementUtility::allowTableOnStandardPages('tx_cal_event');
ExtensionManagementUtility::allowTableOnStandardPages('tx_cal_calendar');
ExtensionManagementUtility::allowTableOnStandardPages('tx_cal_exception_event');
ExtensionManagementUtility::allowTableOnStandardPages('tx_cal_exception_event_group');
ExtensionManagementUtility::allowTableOnStandardPages('tx_cal_location');
ExtensionManagementUtility::allowTableOnStandardPages('tx_cal_organizer');
ExtensionManagementUtility::allowTableOnStandardPages('tx_cal_unknown_users');
ExtensionManagementUtility::allowTableOnStandardPages('tx_cal_attendee');
ExtensionManagementUtility::allowTableOnStandardPages('tx_cal_fe_user_event_monitor_mm');
ExtensionManagementUtility::allowTableOnStandardPages('tx_cal_event_deviation');

// Add Calendar Events to the "Insert Records" content element
ExtensionManagementUtility::addToInsertRecords('tx_cal_event');

/**
 * Register icons
 */
$iconRegistry = GeneralUtility::makeInstance(IconRegistry::class);
$iconRegistry->registerIcon(
    'tx-cal-wizard',
    SvgIconProvider::class,
    [ 'source' => 'EXT:cal/Resources/Public/Icons/tx_cal_calendar.svg' ]
);

$iconRegistry->registerIcon(
    'cal-pagetree-root',
    SvgIconProvider::class,
    [ 'source' => 'EXT:cal/Resources/Public/Icons/tx_cal_calendar.svg' ]
);

$iconRegistry->registerIcon(
    'cal-eventtype-standard',
    SvgIconProvider::class,
    [ 'source' => 'EXT:cal/Resources/Public/Icons/tx_cal_event.svg' ]
);

$iconRegistry->registerIcon(
    'cal-eventtype-intlnk',
    SvgIconProvider::class,
    [ 'source' => 'EXT:cal/Resources/Public/Icons/tx_cal_event_link.svg' ]
);

$iconRegistry->registerIcon(
    'cal-eventtype-exturl',
    SvgIconProvider::class,
    [ 'source' => 'EXT:cal/Resources/Public/Icons/tx_cal_event_link.svg' ]
);

$iconRegistry->registerIcon(
    'cal-eventtype-meeting',
    SvgIconProvider::class,
    [ 'source' => 'EXT:cal/Resources/Public/Icons/tx_cal_event_meeting.svg' ]
);

$iconRegistry->registerIcon(
    'cal-eventtype-todo',
    SvgIconProvider::class,
    [ 'source' => 'EXT:cal/Resources/Public/Icons/tx_cal_event_todo.svg' ]
);

$iconRegistry->registerIcon(
    'cal-calendar-standard',
    SvgIconProvider::class,
    [ 'source' => 'EXT:cal/Resources/Public/Icons/tx_cal_calendar.svg' ]
);

$iconRegistry->registerIcon(
    'cal-calendar-exturl',
    SvgIconProvider::class,
    [ 'source' => 'EXT:cal/Resources/Public/Icons/tx_cal_calendar_link.svg' ]
);

$iconRegistry->registerIcon(
    'cal-calendar-ics',
    SvgIconProvider::class,
    [ 'source' => 'EXT:cal/Resources/Public/Icons/tx_cal_calendar_link.svg' ]
);

if (TYPO3_MODE === 'BE') {
        // Add module
    ExtensionManagementUtility::addModule(
            'tools',
            'txcalM1',
            '',
            '',
            [
            'routeTarget' => CalIndexer::class . '::mainAction',
                    'access' => 'admin',
                    'name' => 'tools_txcalM1',
            'icon' => 'EXT:cal/Resources/Public/Icons/Module.svg',
            'labels' => 'LLL:EXT:cal/Resources/Private/Language/locallang_indexer_mod.xlf'
            ]
        );
    }
