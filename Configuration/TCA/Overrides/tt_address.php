<?php

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

defined('TYPO3_MODE') or die();

if (ExtensionManagementUtility::isLoaded('tt_address')) {
    $configuration = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['cal']);

    // Append backend search configuration for tt_address:
    $delimeter = isset($GLOBALS['TCA']['tt_address']['ctrl']['searchFields']) ? ',' : '';
    $GLOBALS['TCA']['tt_address']['ctrl']['searchFields'] .= $delimeter . 'tx_cal_controller_latitude,tx_cal_controller_longitude';

    // Get the location and organizer structures.
    $useLocationStructure = $configuration['useLocationStructure'] ?: 'tx_cal_location';
    $useOrganizerStructure = $configuration['useOrganizerStructure'] ?: 'tx_cal_organizer';

    if ($useLocationStructure == 'tx_tt_address') {
        $tempColumns = [
            'tx_cal_controller_islocation' => [
                'exclude' => 1,
                'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xlf:tx_cal_location.islocation',
                'config' => [
                    'type' => 'check',
                    'default' => 1
                ]
            ]
        ];
        ExtensionManagementUtility::addTCAcolumns('tt_address', $tempColumns);
        ExtensionManagementUtility::addToAllTCAtypes(
            'tt_address',
            'tx_cal_controller_islocation,'
        );
    }

    if ($useOrganizerStructure == 'tx_tt_address') {
        $tempColumns = [
            'tx_cal_controller_isorganizer' => [
                'exclude' => 1,
                'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xlf:tx_cal_organizer.isorganizer',
                'config' => [
                    'type' => 'check',
                    'default' => 0
                ]
            ]
        ];

        ExtensionManagementUtility::addTCAcolumns('tt_address', $tempColumns);
        ExtensionManagementUtility::addToAllTCAtypes(
            'tt_address',
            'tx_cal_controller_isorganizer,'
        );
    }
}
