<?php

use TYPO3\CMS\Cal\Backend\TCA\Labels;

defined('TYPO3_MODE') or die();

$tx_cal_attendee = [
    'ctrl' => [
        'title' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xlf:tx_cal_attendee',
        'label' => 'uid',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'default_sortby' => 'uid',
        'delete' => 'deleted',
        'iconfile' => 'EXT:cal/Resources/Public/Icons/tx_cal_attendee.svg',
        'enablecolumns' => [
            'disabled' => 'hidden'
        ],
        'versioningWS' => true,
        'searchFields' => 'email',
        'label_userFunc' => Labels::class . '->getAttendeeRecordLabel'
    ],
    'interface' => [
        'showRecordFieldList' => 'hidden,fe_user_id,email,attendance,status'
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
        'fe_user_id' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xlf:tx_cal_attendee.fe_user_id',
            'config' => [
                'type' => 'group',
                'internal_type' => 'db',
                'size' => 1,
                'minitems' => 0,
                'maxitems' => 1,
                'allowed' => 'fe_users',
                'default' => 0,
            ]
        ],
        'fe_group_id' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xlf:tx_cal_attendee.fe_group_id',
            'config' => [
                'type' => 'group',
                'internal_type' => 'db',
                'size' => 1,
                'minitems' => 0,
                'maxitems' => 1,
                'allowed' => 'fe_groups',
                'default' => 0,
            ]
        ],
        'email' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xlf:tx_cal_attendee.email',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'max' => 64,
                'eval' => 'lower',
                'default' => '',
            ]
        ],
        'attendance' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xlf:tx_cal_attendee.attendance',
            'config' => [
                'renderType' => 'selectSingle',
                'type' => 'select',
                'items' => [
                    [
                        'LLL:EXT:cal/Resources/Private/Language/locallang_db.xlf:tx_cal_attendee.attendance.NON',
                        'NON'
                    ],
                    [
                        'LLL:EXT:cal/Resources/Private/Language/locallang_db.xlf:tx_cal_attendee.attendance.OPT-PARTICIPANT',
                        'OPT-PARTICIPANT'
                    ],
                    [
                        'LLL:EXT:cal/Resources/Private/Language/locallang_db.xlf:tx_cal_attendee.attendance.REQ-PARTICIPANT',
                        'REQ-PARTICIPANT'
                    ],
                    [
                        'LLL:EXT:cal/Resources/Private/Language/locallang_db.xlf:tx_cal_attendee.attendance.CHAIR',
                        'CHAIR'
                    ]
                ],
                'size' => 1,
                'minitems' => 1,
                'maxitems' => 1,
                'default' => 'NON',
            ]
        ],
        'status' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xlf:tx_cal_attendee.status',
            'config' => [
                'renderType' => 'selectSingle',
                'type' => 'select',
                'items' => [
                    [
                        'LLL:EXT:cal/Resources/Private/Language/locallang_db.xlf:tx_cal_attendee.status.0',
                        '0'
                    ],
                    [
                        'LLL:EXT:cal/Resources/Private/Language/locallang_db.xlf:tx_cal_attendee.status.NEEDS-ACTION',
                        'NEEDS-ACTION'
                    ],
                    [
                        'LLL:EXT:cal/Resources/Private/Language/locallang_db.xlf:tx_cal_attendee.status.ACCEPTED',
                        'ACCEPTED'
                    ],
                    [
                        'LLL:EXT:cal/Resources/Private/Language/locallang_db.xlf:tx_cal_attendee.status.DECLINE',
                        'DECLINE'
                    ],
                    [
                        'LLL:EXT:cal/Resources/Private/Language/locallang_db.xlf:tx_cal_attendee.status.TENTATIVE',
                        'TENTATIVE'
                    ],
                    [
                        'LLL:EXT:cal/Resources/Private/Language/locallang_db.xlf:tx_cal_attendee.status.DELEGATED',
                        'DELEGATED'
                    ]
                ],
                'size' => 1,
                'minitems' => 1,
                'maxitems' => 1,
                'default' => 0,
            ]
        ]
    ],
    'types' => [
        '0' => [
            'showitem' => 'hidden,fe_user_id,fe_group_id,email,attendance,status'
        ]
    ],
    'palettes' => [
        '1' => [
            ''
        ]
    ]
];

return $tx_cal_attendee;
