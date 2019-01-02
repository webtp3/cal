<?php
defined('TYPO3_MODE') or die();

$tx_cal_category = [
    'ctrl' => [
        'title' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_category',
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
        'versioningWS' => true,
        'origUid' => 't3_origuid',
        'shadowColumnsForNewPlaceholders' => 'sys_language_uid,l18n_parent',
        'transOrigPointerField' => 'l18n_parent',
        'transOrigDiffSourceField' => 'l18n_diffsource',
        'languageField' => 'sys_language_uid',
        'iconfile' => 'EXT:cal/Resources/Public/icons/icon_tx_cal_category.gif',
        // 'treeParentField' => 'calendar_id',
        'searchFields' => 'title,notification_emails'
    ],
    'feInterface' => [
        'fe_admin_fieldList' => 'hidden, title, starttime, endtime'
    ],
    'interface' => [
        'showRecordFieldList' => 'hidden,title,headerstyle,bodystyle,calendar_id,single_pid,shared_user_allowed,notification_emails,icon'
    ],
    'columns' => [
        'hidden' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.hidden',
            'config' => [
                'type' => 'check',
                'default' => '0'
            ]
        ],
        'title' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_category.title',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'max' => 128,
                'eval' => 'required'
            ]
        ],
        'headerstyle' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_category.headerstyle',
            'config' => [
                'type' => 'user',
                'userFunc' => 'TYPO3\CMS\Cal\Backend\TCA\CustomTca->getHeaderStyles'
            ]
        ],
        'bodystyle' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_category.bodystyle',
            'config' => [
                'type' => 'user',
                'userFunc' => 'TYPO3\CMS\Cal\Backend\TCA\CustomTca->getBodyStyles'
            ]
        ],
        'calendar_id' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_category.calendar',
            'onChange' => 'reload',
            'config' => [
                'renderType' => 'selectSingle',
                'type' => 'select',
                'itemsProcFunc' => 'TYPO3\CMS\Cal\Backend\TCA\ItemsProcFunc->getRecords',
                'itemsProcFunc_config' => [
                    'table' => 'tx_cal_calendar',
                    'orderBy' => 'tx_cal_calendar.title'
                ],
                'items' => [
                    [
                        'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_category.none',
                        0
                    ]
                ],
                'size' => 1,
                'minitems' => 0,
                'maxitems' => 1,
                'allowed' => 'tx_cal_calendar',
            ]
        ],
        'parent_category' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_category.parent_category',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectTree',
                'parameterArray' => [
                    'fieldConf' => [
                        'config' => [
                            'renderMode' => 'tree',
                        ],
                    ],
                ],
                'treeConfig' => [
// 								'dataProvider' => 'TYPO3\\CMS\\Cal\\TreeProvider\\DatabaseTreeDataProvider',
                    'parentField' => 'parent_category',
                    'appearance' => [
                        'showHeader' => true,
                        'expandAll' => true,
                        'maxLevels' => 99
                    ]
                ],
                'form_type' => 'user',
                'userFunc' => 'TYPO3\CMS\Cal\TreeProvider\TreeView->displayCategoryTree',
                'treeView' => 1,
                'size' => 20,
                'itemListStyle' => 'height:300px;',
                'minitems' => 0,
                'maxitems' => 2,
                'foreign_table' => 'tx_cal_category'
            ]
        ],
        'shared_user_allowed' => [
            'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_category.shared_user_allowed',
            'config' => [
                'type' => 'check'
            ]
        ],
        'single_pid' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_category.single_pid',
            'config' => [
                'type' => 'group',
                'internal_type' => 'db',
                'allowed' => 'pages',
                'size' => 1,
                'maxitems' => 1,
                'minitems' => 0,
            ]
        ],
        'notification_emails' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_category.notification_emails',
            'config' => [
                'type' => 'input',
                'size' => 30
            ]
        ],
        'icon' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_category.icon',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'max' => 128
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
                ]
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
                'foreign_table' => 'tx_cal_category',
                'foreign_table_where' => 'AND tx_cal_category.sys_language_uid IN (-1,0)'
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
            'showitem' => 'type,title, --palette--;;1,calendar_id,parent_category,shared_user_allowed,single_pid,notification_emails,icon'
        ]
    ],
    'palettes' => [
        '1' => [
            'showitem' => 'hidden,l18n_parent,sys_language_uid,t3ver_label,headerstyle,bodystyle'
        ]
    ]
];

return $tx_cal_category;
