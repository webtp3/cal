<?php

/*
 * This file is part of the web-tp3/cal.
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

defined('TYPO3_MODE') or die();

$tx_cal_unknown_users = [
    'ctrl' => [
        'title' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_unknown_users',
        'label' => 'email',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'default_sortby' => 'ORDER BY email',
        'delete' => 'deleted',
        'enablecolumns' => [],
        'versioningWS' => true,
        'iconfile' => 'EXT:cal/Resources/Public/icons/icon_tx_cal_unknown_users.gif',
        'searchFields' => 'email'
    ],
    'feInterface' => [
        'fe_admin_fieldList' => 'hidden, email'
    ],
    'interface' => [
        'showRecordFieldList' => 'hidden,email'
    ],
    'columns' => [
        'hidden' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.hidden',
            'config' => [
                'type' => 'check',
                'default' => '0'
            ]
        ],
        'email' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_unknown_users.email',
            'config' => [
                'type' => 'input',
                'size' => '30',
                'max' => '64',
                'eval' => 'required'
            ]
        ]
    ],
    'types' => [
        '0' => [
            'showitem' => 'hidden,email'
        ]
    ],
    'palettes' => [
        '1' => [
            ''
        ]
    ]
];

return $tx_cal_unknown_users;
