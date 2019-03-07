<?php

/*
 * This file is part of the web-tp3/cal.
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

use TYPO3\CMS\Cal\Backend\TCA\ItemsProcFunc;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

$ll = 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xlf:';

/**
 * Add extra fields to the sys_category record
 */
$newCalSysCategoryColumns = [
    'icon' => [
        'exclude' => 1,
        'l10n_mode' => 'mergeIfNotBlank',
        'label' => $ll . 'sys_category.icon',
        'config' => ExtensionManagementUtility::getFileFieldTCAConfig(
            'images',
            [
                'appearance' => [
                    'createNewRelationLinkTitle' => 'LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:images.addFileReference',
                    'showPossibleLocalizationRecords' => 1,
                    'showRemovedLocalizationRecords' => 1,
                    'showAllLocalizationLink' => 1,
                    'showSynchronizationLink' => 1
                ],
                'foreign_match_fields' => [
                    'fieldname' => 'images',
                    'tablenames' => 'sys_category',
                    'table_local' => 'sys_file',
                ],
            ],
            $GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext']
        )
    ],
    'single_pid' => [
        'exclude' => 1,
        'l10n_mode' => 'mergeIfNotBlank',
        'label' => $ll . 'sys_category.single_pid',
        'config' => [
            'type' => 'group',
            'internal_type' => 'db',
            'allowed' => 'pages',
            'size' => 1,
            'maxitems' => 1,
            'minitems' => 0,
            'default' => 0,
        ]
    ],
    'shortcut' => [
        'exclude' => 1,
        'l10n_mode' => 'mergeIfNotBlank',
        'label' => $ll . 'sys_category.shortcut',
        'config' => [
            'type' => 'group',
            'internal_type' => 'db',
            'allowed' => 'pages',
            'size' => 1,
            'maxitems' => 1,
            'minitems' => 0,
            'default' => 0,
        ]
    ],
    'headerstyle' => [
        'exclude' => 1,
        'label' => $ll . 'sys_category.headerstyle',
        'config' => [
            'type' => 'user',
            'renderType' => 'calStylesElement',
            'parameters' => [
                'stylesFor' => 'header',
            ],
            'default' => '',
        ]
    ],
    'bodystyle' => [
        'exclude' => 1,
        'label' => $ll . 'sys_category.bodystyle',
        'config' => [
            'type' => 'user',
            'renderType' => 'calStylesElement',
            'parameters' => [
                'stylesFor' => 'body',
            ],
            'default' => '',
        ]
    ],
    'calendar_id' => [
        'exclude' => 1,
        'label' => $ll . 'sys_category.calendar',
        'config' => [
            'renderType' => 'selectSingle',
            'type' => 'select',
            'itemsProcFunc' => ItemsProcFunc::class . '->getRecords',
            'itemsProcFunc_config' => [
                'table' => 'tx_cal_calendar',
                'orderBy' => 'tx_cal_calendar.title'
            ],
            'items' => [
                [
                    '',
                    0
                ]
            ],
            'size' => 1,
            'minitems' => 0,
            'maxitems' => 1,
            'allowed' => 'tx_cal_calendar',
            'default' => 0,
        ]
    ],
    'shared_user_allowed' => [
        'label' => $ll . 'sys_category.shared_user_allowed',
        'config' => [
            'type' => 'check',
            'default' => 0,
        ]
    ],

    'notification_emails' => [
        'exclude' => 0,
        'label' => $ll . 'sys_category.notification_emails',
        'config' => [
            'type' => 'input',
            'size' => '30',
            'default' => '',
        ]
    ],
];

ExtensionManagementUtility::addTCAcolumns('sys_category', $newCalSysCategoryColumns);
ExtensionManagementUtility::addToAllTCAtypes(
    'sys_category',
    '--div--;LLL:EXT:frontend/Resources/Private/Language/locallang_tca.xlf:pages.tabs.options, icon',
    '',
    'before:description'
);
ExtensionManagementUtility::addToAllTCAtypes(
    'sys_category',
    'single_pid',
    '',
    'after:description'
);
ExtensionManagementUtility::addToAllTCAtypes('sys_category', 'shortcut', '', 'after:shortcut');
ExtensionManagementUtility::addToAllTCAtypes(
    'sys_category',
    'headerstyle',
    '',
    'after:single_pid'
);
ExtensionManagementUtility::addToAllTCAtypes(
    'sys_category',
    'bodystyle',
    '',
    'after:headerstyle'
);
ExtensionManagementUtility::addToAllTCAtypes(
    'sys_category',
    'calendar_id',
    '',
    'after:bodystyle'
);
ExtensionManagementUtility::addToAllTCAtypes(
    'sys_category',
    'shared_user_allowed',
    '',
    'after:calendar_id'
);
ExtensionManagementUtility::addToAllTCAtypes(
    'sys_category',
    'notification_emails',
    '',
    'after:shared_user_allowed'
);
