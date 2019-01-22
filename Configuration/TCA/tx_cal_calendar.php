<?php
defined('TYPO3_MODE') or die();

$configuration = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['cal']);

$tx_cal_calendar = [
    'ctrl' => [
        'title' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_calendar',
        'label' => 'title',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'default_sortby' => 'ORDER BY title',
        'delete' => 'deleted',
        'enablecolumns' => [
            'disabled' => 'hidden',
            'starttime' => 'starttime',
            'endtime' => 'endtime'
        ],
        'type' => 'type',
        'typeicon_column' => 'type',
        'typeicon_classes' => [
            'default' => 'cal-calendar-standard',
            0 => 'cal-calendar-standard',
            1 => 'cal-calendar-exturl',
            2 => 'cal-calendar-ics'
        ],
        'versioningWS' => true,
        'origUid' => 't3_origuid',
        'shadowColumnsForNewPlaceholders' => 'sys_language_uid,l18n_parent',
        'transOrigPointerField' => 'l18n_parent',
        'transOrigDiffSourceField' => 'l18n_diffsource',
        'languageField' => 'sys_language_uid',
        'iconfile' => 'EXT:cal/Resources/Public/Icons/tx_cal_calendar.svg',
        'searchFields' => 'title,ext_url,ext_url_notes,ics_file'
    ],
    'feInterface' => [
        'fe_admin_fieldList' => 'hidden, title, starttime, endtime'
    ],
    'interface' => [
        'showRecordFieldList' => 'hidden,title,headerstyle,bodystyle,activate_fnb,fnb_user_cnt,nearby'
    ],
    'columns' => [
        'hidden' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.hidden',
            'config' => [
                'type' => 'check',
                'default' => 0
            ]
        ],
        'title' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_calendar.title',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'max' => 128,
                'eval' => 'unique, required'
            ]
        ],
        'owner' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_calendar.owner',
            'config' => [
                'type' => 'group',
                'internal_type' => 'db',
                'MM' => 'tx_cal_calendar_user_group_mm',
                'size' => 4,
                'minitems' => 0,
                'autoSizeMax' => 25,
                'maxitems' => 500,
                'allowed' => 'fe_users,fe_groups',
                'default' => 0,
            ]
        ],
        'activate_fnb' => [
            'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_calendar.activate_fnb',
            'onChange' => 'reload',
            'config' => [
                'type' => 'check',
                'default' => 0,
            ]
        ],
        'fnb_user_cnt' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_calendar.fb_users_groups',
            'displayCond' => 'FIELD:activate_fnb:=:1',
            'config' => [
                'type' => 'group',
                'internal_type' => 'db',
                'MM' => 'tx_cal_calendar_fnb_user_group_mm',
                'size' => 6,
                'minitems' => 0,
                'maxitems' => 100,
                'allowed' => 'fe_users,fe_groups',
                'default' => 0,
            ]
        ],
        'nearby' => [
            'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_calendar.nearby',
            'config' => [
                'type' => 'check',
                'default' => 0,
            ]
        ],
        'type' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_calendar.type',
            'config' => [
                'renderType' => 'selectSingle',
                'type' => 'select',
                'size' => 1,
                'items' => [
                    [
                        'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_calendar.type.I.0',
                        0
                    ],
                    [
                        'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_calendar.type.I.1',
                        1
                    ],
                    [
                        'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_calendar.type.I.2',
                        2
                    ]
                ],
                'default' => 0
            ]
        ],

        'ext_url' => [
            'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_calendar.ext_url',
            'config' => [
                'type' => 'user',
                'renderType' => 'calExtUrlElement',
                'default' => ''
            ]
        ],

        'ext_url_notes' => [
            'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_calendar.ext_url_notes',
            'config' => [
                'type' => 'text',
                'default' => ''
            ]
        ],

        'ics_file' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_calendar.ics_file',
            'config' => [
                'type' => 'group',
                'internal_type' => 'file',
                'allowed' => 'ics', // Must be empty for disallowed to work.
                'max_size' => 10000,
                'uploadfolder' => 'uploads/tx_cal/ics',
                'size' => 1,
                'fieldWizard' => [
                    'fileThumbnails' => [
                        'disabled' => true,
                    ]
                ],
                'autoSizeMax' => 1,
                'maxitems' => 1,
                'minitems' => 0,
                'default' => ''
            ]
        ],

        'refresh' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_calendar.refresh',
            'config' => [
                'type' => 'input',
                'size' => 6,
                'max' => 4,
                'eval' => 'num',
                'default' => 60
            ]
        ],
        'schedulerId' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_calendar.schedulerId',
            'config' => [
                'type' => 'input',
                'size' => 5,
                'readOnly' => 1,
                'default' => 0,
            ]
        ],

        'md5' => [
            'config' => [
                'type' => 'passthrough'
            ]
        ],
        'headerstyle' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:sys_category.headerstyle',
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
            'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:sys_category.bodystyle',
            'config' => [
                'type' => 'user',
                'renderType' => 'calStylesElement',
                'parameters' => [
                    'stylesFor' => 'body',
                ],
                'default' => '',
            ]
        ],
        'sys_language_uid' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.language',
            'config' => [
                'renderType' => 'selectSingle',
                'type' => 'select',
                'foreign_table' => 'sys_language',
                'foreign_table_where' => 'ORDER BY sys_language.title',
                'items' => [
                    [
                        'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.allLanguages',
                        -1
                    ],
                    [
                        'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.default_value',
                        0
                    ]
                ],
                'default' => 0
            ]
        ],
        'l18n_parent' => [
            'displayCond' => 'FIELD:sys_language_uid:>:0',
            'exclude' => 1,
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.l18n_parent',
            'config' => [
                'renderType' => 'selectSingle',
                'type' => 'select',
                'items' => [
                    [
                        '',
                        0
                    ]
                ],
                'foreign_table' => 'tx_cal_calendar',
                'foreign_table_where' => 'AND tx_cal_calendar.sys_language_uid IN (-1,0)'
            ]
        ],
        'l18n_diffsource' => [
            'config' => [
                'type' => 'passthrough'
            ]
        ],
        't3ver_label' => [
            'displayCond' => 'FIELD:t3ver_label:REQ:true',
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.versionLabel',
            'config' => [
                'type' => 'none',
                'cols' => 27
            ]
        ]
    ],
    'types' => [
        '0' => [
            'showitem' => 'type,title, --palette--;;1,owner,headerstyle,bodystyle,activate_fnb,fnb_user_cnt,nearby'
        ],
        '1' => [
            'showitem' => 'type,title, --palette--;;1,owner,headerstyle,bodystyle,activate_fnb,fnb_user_cnt,nearby,ext_url,refresh,schedulerId'
        ],
        '2' => [
            'showitem' => 'type,title, --palette--;;1,owner,headerstyle,bodystyle,activate_fnb,fnb_user_cnt,nearby,ics_file,refresh,schedulerId'
        ]
    ],
    'palettes' => [
        '1' => [
            'showitem' => 'hidden,l18n_parent,sys_language_uid,t3ver_label'
        ]
    ]
];

return $tx_cal_calendar;
