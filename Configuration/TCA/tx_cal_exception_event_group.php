<?php

/*
 * This file is part of the web-tp3/cal.
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

defined('TYPO3_MODE') or die();

$tx_cal_exception_event_group = [
    'ctrl' => [
        'title' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xlf:tx_cal_exception_event_group',
        'label' => 'title',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'default_sortby' => 'ORDER BY title',
        'delete' => 'deleted',
        'iconfile' => 'EXT:cal/Resources/Public/Icons/tx_cal_exception_event_group.svg',
        'enablecolumns' => [
            'disabled' => 'hidden'
        ],
        'versioningWS' => true,
        'searchFields' => 'title'
    ],
    'feInterface' => [
        'fe_admin_fieldList' => 'title'
    ],
    'interface' => [
        'showRecordFieldList' => 'hidden,title,tx_cal_exception_event_cnt'
    ],
    'columns' => [
        'hidden' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:lang/Resources/Private/Language/locallang_general.xlf:LGL.hidden',
            'config' => [
                'type' => 'check',
                'default' => 0
            ]
        ],
        'title' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xlf:tx_cal_exception_event_group.title',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'max' => 128,
                'eval' => 'required'
            ]
        ],
        'exception_event_cnt' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xlf:tx_cal_exception_event_group.exception_event_cnt',
            'config' => [
                'type' => 'group',
                'internal_type' => 'db',
                'allowed' => 'tx_cal_exception_event',
                'size' => 6,
                'minitems' => 0,
                'maxitems' => 100,
                'default' => 0,
                'MM' => 'tx_cal_exception_event_group_mm',
            ]
        ],
        't3ver_label' => [
            'displayCond' => 'FIELD:t3ver_label:REQ:true',
            'label' => 'LLL:EXT:lang/Resources/Private/Language/locallang_general.xlf:LGL.versionLabel',
            'config' => [
                'type' => 'none',
                'cols' => 27
            ]
        ]
    ],
    'types' => [
        '0' => [
            'showitem' => 'title, --palette--;;1,color,exception_event_cnt'
        ]
    ],
    'palettes' => [
        '1' => [
            'showitem' => 'hidden,t3ver_label'
        ]
    ]
];

return $tx_cal_exception_event_group;
