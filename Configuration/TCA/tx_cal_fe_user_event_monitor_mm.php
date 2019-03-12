<?php

/*
 * This file is part of the web-tp3/cal.
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

use TYPO3\CMS\Cal\Backend\TCA\Labels;

defined('TYPO3_MODE') or die();

$tx_cal_fe_user_event_monitor_mm = [
    'ctrl' => [
        'requestUpdate' => '',
        'title' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xlf:tx_cal_fe_user_event.monitor',
        'label' => 'tablenames',
        'label_alt' => 'tablenames,offset',
        'label_alt_force' => 1,
        'iconfile' => 'EXT:cal/Resources/Public/Icons/tx_cal_fe_user_event_monitor_mm.svg',
        'label_userFunc' => Labels::class . '->getMonitoringRecordLabel'
    ],
    'feInterface' => [
        'fe_admin_fieldList' => ''
    ],
    'interface' => [
        'showRecordFieldList' => 'uid_foreign,uid_local,tablenames,offset,schedulerId'
    ],
    'columns' => [
        'uid_foreign' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xlf:tx_cal_fe_user_event.monitor',
            'config' => [
                'type' => 'group',
                'internal_type' => 'db',
                'allowed' => 'fe_users,fe_groups,tx_cal_unknown_users',
                'size' => 1,
                'minitems' => 0,
                'maxitems' => 1,
                'default' => 0,
            ]
        ],
        'uid_local' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xlf:tx_cal_event',
            'config' => [
                'type' => 'group',
                'internal_type' => 'db',
                'allowed' => 'tx_cal_event',
                'size' => 1,
                'minitems' => 0,
                'maxitems' => 1,
                'default' => 0,
            ]
        ],
        'tablenames' => [
            'exclude' => 1,
            'label' => 'tablenames',
            'config' => [
                'type' => 'input',
                'size' => 12,
                'default' => '',
            ]
        ],
        'offset' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xlf:tx_cal_fe_user_event.offset',
            'config' => [
                'type' => 'input',
                'size' => 6,
                'max' => 4,
                'eval' => 'num',
                'default' => 60
            ]
        ],
        'schedulerId' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xlf:tx_cal_fe_user_event.schedulerId',
            'config' => [
                'type' => 'input',
                'size' => 5,
                'readOnly' => 1,
                'default' => 0,
            ]
        ]
    ],
    'types' => [
        '0' => [
            'showitem' => 'uid_foreign,uid_local,offset,schedulerId'
        ]
    ]
];

return $tx_cal_fe_user_event_monitor_mm;
