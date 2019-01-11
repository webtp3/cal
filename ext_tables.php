<?php

use TYPO3\CMS\Cal\Backend\CalWizIcon;
use TYPO3\CMS\Cal\Backend\Modul\CalIndexer;
use TYPO3\CMS\Core\Imaging\IconProvider\BitmapIconProvider;
use TYPO3\CMS\Core\Imaging\IconRegistry;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

if (!defined('TYPO3_MODE')) {
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
    BitmapIconProvider::class,
    [ 'source' => 'EXT:cal/Resources/Public/icons/ce_wiz.gif' ]
);

$iconRegistry->registerIcon(
    'cal-pagetree-root',
    BitmapIconProvider::class,
    [ 'source' => 'EXT:cal/Resources/Public/icons/icon_tx_cal_calendar.gif' ]
);

$iconRegistry->registerIcon(
    'cal-eventtype-standard',
    BitmapIconProvider::class,
    [ 'source' => 'EXT:cal/Resources/Public/icons/icon_tx_cal_events.gif' ]
);

$iconRegistry->registerIcon(
    'cal-eventtype-intlnk',
    BitmapIconProvider::class,
    [ 'source' => 'EXT:cal/Resources/Public/icons/icon_tx_cal_events_intlnk.gif' ]
);

$iconRegistry->registerIcon(
    'cal-eventtype-exturl',
    BitmapIconProvider::class,
    [ 'source' => 'EXT:cal/Resources/Public/icons/icon_tx_cal_events_exturl.gif' ]
);

$iconRegistry->registerIcon(
    'cal-eventtype-meeting',
    BitmapIconProvider::class,
    [ 'source' => 'EXT:cal/Resources/Public/icons/icon_tx_cal_events_meeting.gif' ]
);

$iconRegistry->registerIcon(
    'cal-eventtype-todo',
    BitmapIconProvider::class,
    [ 'source' => 'EXT:cal/Resources/Public/icons/icon_tx_cal_events_todo.gif' ]
);

$iconRegistry->registerIcon(
    'cal-calendar-standard',
    BitmapIconProvider::class,
    [ 'source' => 'EXT:cal/Resources/Public/icons/icon_tx_cal_calendar.gif' ]
);

$iconRegistry->registerIcon(
    'cal-calendar-exturl',
    BitmapIconProvider::class,
    [ 'source' => 'EXT:cal/Resources/Public/icons/icon_tx_cal_calendar_exturl.gif' ]
);

$iconRegistry->registerIcon(
    'cal-calendar-ics',
    BitmapIconProvider::class,
    [ 'source' => 'EXT:cal/Resources/Public/icons/icon_tx_cal_calendar_ics.gif' ]
);

if (TYPO3_MODE === 'BE') {
    $GLOBALS['TBE_MODULES_EXT']['xMOD_db_new_content_el']['addElClasses'][CalWizIcon::class] = $extPath . 'Classes/Backend/CalWizIcon.php';
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
            'icon' => 'EXT:cal/Classes/Backend/Modul/icon_tx_cal_indexer2.svg',
            'labels' => 'LLL:EXT:cal/Resources/Private/Language/locallang_indexer_mod.xml'
        ]
    );
}
