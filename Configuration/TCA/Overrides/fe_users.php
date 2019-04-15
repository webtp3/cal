<?php

/*
 * This file is part of the web-tp3/cal.
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

defined('TYPO3_MODE') or die();

$sPid = '###CURRENT_PID###'; // storage pid????

// Define the TCA for the access control calendar selector.
$tempColumns = [
    'tx_cal_calendar' => [
        'exclude' => 1,
        'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_calendar_private',
        'config' => [
            'type' => 'group',
            'internal_type' => 'db',
            'allowed' => 'tx_cal_calendar',
            'minitems' => 0,
            'maxitems' => 99,
            'fieldControl' => [
                'addRecord' => [
                    'disabled' => '',
                    'options' => [
                        'pid' => $sPid,
                        'setValue' => 'set',
                        'table' => 'tx_cal_calendar',
                        'title' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_calendar.createNew',
                    ]
                ]
            ],
            'wizards' => [
                '_PADDING' => 2,
                '_VERTICAL' => 1,
            ]
        ]
    ],
    'tx_cal_calendar_subscription' => [
        'exclude' => 1,
        'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_calendar_subscription',
        'config' => [
            'type' => 'group',
            'internal_type' => 'db',
            'allowed' => 'tx_cal_calendar',
            'minitems' => 0,
            'maxitems' => 99,
            'fieldControl' => [
                'addRecord' => [
                    'disabled' => '',
                    'options' => [
                        'pid' => $sPid,
                        'setValue' => 'set',
                        'table' => 'tx_cal_calendar',
                        'title' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_calendar.createNew',
                    ]
                ]
            ],
            'wizards' => [
                '_PADDING' => 2,
                '_VERTICAL' => 1,
            ]
        ]
    ]
];

// Add the calendar selector for backend users.
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('fe_users', $tempColumns);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('fe_users', 'tx_cal_calendar,tx_cal_calendar_subscription');
