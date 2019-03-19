<?php

/*
 * This file is part of the web-tp3/cal.
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

use TYPO3\CMS\Cal\Backend\TCA\ItemsProcFunc;
use TYPO3\CMS\Cal\Backend\TCA\Labels;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

defined('TYPO3_MODE') or die();

$configuration = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['cal']);

$sPid = '###CURRENT_PID###'; // storage pid????

$useLocationStructure = $configuration['useLocationStructure'] ?: 'tx_cal_location';
$useOrganizerStructure = $configuration['useOrganizerStructure'] ?: 'tx_cal_organizer';

ExtensionManagementUtility::makeCategorizable(
    'cal',
    'tx_cal_event',
    'category_id',
    [
        'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xlf:tx_cal_event.category',
        'exclude' => false,
        'fieldConfiguration' => [
            'foreign_table_where' => ' AND sys_category.sys_language_uid IN (-1, 0) ORDER BY sys_category.title ASC',
        ],
        'l10n_mode' => 'exclude',
        'l10n_display' => 'hideDiff',
    ]
);

switch ($useLocationStructure) {
    case 'tx_tt_address':
        $useLocationStructure = 'tt_address';
        break;
}
switch ($useOrganizerStructure) {
    case 'tx_tt_address':
        $useOrganizerStructure = 'tt_address';
        break;
    case 'tx_feuser':
        $useOrganizerStructure = 'fe_users';
        break;
}

$tx_cal_event = [
    'ctrl' => [
        'title' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xlf:tx_cal_event',
        'label' => 'title',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'default_sortby' => 'ORDER BY start_date DESC, start_time DESC',
        'delete' => 'deleted',
        'versioningWS' => true,
        'origUid' => 't3_origuid',
        'shadowColumnsForNewPlaceholders' => 'sys_language_uid,l18n_parent',
        'transOrigPointerField' => 'l18n_parent',
        'transOrigDiffSourceField' => 'l18n_diffsource',
        'languageField' => 'sys_language_uid',
        'type' => 'type',
        'typeicon_column' => 'type',
        'typeicon_classes' => [
            'default' => 'cal-eventtype-standard',
            0 => 'cal-eventtype-standard',
            1 => 'cal-eventtype-intlnk',
            2 => 'cal-eventtype-exturl',
            3 => 'cal-eventtype-meeting',
            4 => 'cal-eventtype-todo'
        ],
        'dividers2tabs' => $configuration['noTabDividers'] ? false : true,
        'enablecolumns' => [
            'disabled' => 'hidden',
            'starttime' => 'starttime',
            'endtime' => 'endtime'
        ],
        'iconfile' => 'EXT:cal/Resources/Public/Icons/tx_cal_event.svg',
        'searchFields' => 'title,organizer,organizer_link,location,location_link,teaser,description,ext_url,image,imagecaption,imagealttext,imagetitletext,attachment,attachmentcaption',
        'label_userFunc' => Labels::class . '->getEventRecordLabel'
    ],
    'feInterface' => [
        'fe_admin_fieldList' => 'hidden, title, starttime, endtime, start_date, start_time, end_date, end_time, relation_cnt, organizer, organizer_id, organizer_pid, location, location_id, location_pid, description, freq, byday, bymonthday, bymonth, until, count, interval, rdate_type, rdate, notify_cnt'
    ],
    'interface' => [
        'showRecordFieldList' => 'hidden,category_id,title,start_date,start_time,allday,end_date,end_time,organizer,location,description,image,attachment,freq,byday,bymonthday,bymonth,until,count,rdate_type,rdate,end,intrval,exception_cnt, shared_user_cnt,attendee,status,priority,completed'
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
            'exclude' => 1,
            'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xlf:tx_cal_event.title',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'max' => 128,
                'eval' => 'required'
            ]
        ],
        'starttime' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:lang/Resources/Private/Language/locallang_general.xlf:LGL.starttime',
            'config' => [
                'type' => 'input',
                'size' => 12,
                'max' => 20,
                'renderType' => 'inputDateTime',
                'eval' => 'datetime',
                'default' => 0,
                'checkbox' => '0'
            ]
        ],
        'endtime' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:lang/Resources/Private/Language/locallang_general.xlf:LGL.endtime',
            'config' => [
                'type' => 'input',
                'renderType' => 'inputDateTime',
                'size' => 12,
                'eval' => 'datetime',
                'default' => 0,
                'checkbox' => '0'
            ]
        ],
        'calendar_id' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xlf:tx_cal_event.calendar',
            'onChange' => 'reload',
            'config' => [
                'renderType' => 'selectSingle',
                'type' => 'select',
                'size' => 1,
                'minitems' => 1,
                'maxitems' => 1,
                'itemsProcFunc' => ItemsProcFunc::class . '->getRecords',
                'itemsProcFunc_config' => [
                    'table' => 'tx_cal_calendar',
                    'orderBy' => 'tx_cal_calendar.title'
                ],
                'fieldControl' => [
                    'addRecord' => [
                        'disabled' => '',
                        'options' => [
                            'pid' => $sPid,
                            'setValue' => 'set',
                            'table' => 'tx_cal_calendar',
                            'title' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xlf:tx_cal_calendar.createNew',
                        ]
                    ],
                    'editPopup' => [
                        'disabled' => '',
                        'options' => [
                            'windowOpenParameters' => 'height=500,width=660,status=0,menubar=0,scrollbars=1',
                            'title' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xlf:tx_cal_calendar.edit',
                        ]
                    ]
                ]
            ]
        ],
        'start_date' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xlf:tx_cal_event.start_date',
            'config' => [
                'type' => 'input',
                'renderType' => 'inputDateTime',
                'size' => 12,
                'eval' => 'required,date',
                'tx_cal_event' => 'start_date'
            ]
        ],
        'allday' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xlf:tx_cal_event.allday',
            'onChange' => 'reload',
            'config' => [
                'type' => 'check',
                'default' => 0
            ]
        ],
        'start_time' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xlf:tx_cal_event.start_time',
            'displayCond' => 'FIELD:allday:!=:1',
            'config' => [
                'type' => 'input',
                'renderType' => 'inputDateTime',
                'size' => 12,
                'eval' => 'time',
                'default' => 0
            ]
        ],
        'end_date' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xlf:tx_cal_event.end_date',
            'config' => [
                'type' => 'input',
                'renderType' => 'inputDateTime',
                'size' => 12,
                'eval' => 'required,date',
                'tx_cal_event' => 'end_date'
            ]
        ],
        'end_time' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xlf:tx_cal_event.end_time',
            'displayCond' => 'FIELD:allday:!=:1',
            'config' => [
                'type' => 'input',
                'renderType' => 'inputDateTime',
                'size' => 12,
                'eval' => 'time',
                'default' => 0
            ]
        ],
        'organizer' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xlf:tx_cal_event.organizer',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'max' => 128
            ]
        ],
        'organizer_id' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xlf:tx_cal_event.organizer_id',
            'config' => [
                'type' => 'group',
                'internal_type' => 'db',
                'size' => 1,
                'minitems' => 0,
                'maxitems' => 1,
                'default' => 0,
                'allowed' => $useOrganizerStructure,
                'fieldControl' => [
                    'addRecord' => [
                        'disabled' => '',
                        'options' => [
                            'pid' => $sPid,
                            'setValue' => 'set',
                            'table' => $useOrganizerStructure,
                            'title' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xlf:tx_cal_organizer.createNew',
                        ]
                    ],
                    'editPopup' => [
                        'disabled' => '',
                        'options' => [
                            'windowOpenParameters' => 'height=600,width=525,status=0,menubar=0,scrollbars=1',
                            'title' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xlf:tx_cal_organizer.edit',
                        ]
                    ]
                ],
                'wizards' => [
                    '_PADDING' => 2,
                    '_VERTICAL' => 1,
                ]
            ]
        ],
        'organizer_pid' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xlf:tx_cal_event.organizer_pid',
            'config' => [
                'type' => 'group',
                'internal_type' => 'db',
                'allowed' => 'pages',
                'size' => 1,
                'maxitems' => 1,
                'minitems' => '0',
                'default' => 0,
            ]
        ],
        'organizer_link' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xlf:tx_cal_event.organizer_link',
            'config' => [
                'type' => 'input',
                'size' => 25,
                'max' => 128,
                'checkbox' => '',
                'eval' => 'trim',
                'renderType' => 'inputLink',
                'wizards' => [
                    '_PADDING' => 2,
                ]
            ]
        ],
        'location' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xlf:tx_cal_event.location',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'max' => 128
            ]
        ],
        'location_id' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xlf:tx_cal_event.location_id',
            'config' => [
                'type' => 'group',
                'internal_type' => 'db',
                'size' => 1,
                'minitems' => 0,
                'maxitems' => 1,
                'default' => 0,
                'allowed' => $useLocationStructure,
                'fieldControl' => [
                    'addRecord' => [
                        'disabled' => '',
                        'options' => [
                            'pid' => $sPid,
                            'setValue' => 'set',
                            'table' => $useLocationStructure,
                            'title' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xlf:tx_cal_location.createNew',
                        ]
                    ],
                    'editPopup' => [
                        'disabled' => '',
                        'options' => [
                            'windowOpenParameters' => 'height=600,width=525,status=0,menubar=0,scrollbars=1',
                            'title' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xlf:tx_cal_location.edit',
                        ]
                    ]
                ],
                'wizards' => [
                    '_PADDING' => 2,
                    '_VERTICAL' => 1,
                ]
            ]
        ],
        'location_pid' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xlf:tx_cal_event.location_pid',
            'config' => [
                'type' => 'group',
                'internal_type' => 'db',
                'allowed' => 'pages',
                'size' => 1,
                'maxitems' => 1,
                'minitems' => '0',
                'default' => 0,
            ]
        ],
        'location_link' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xlf:tx_cal_event.location_link',
            'config' => [
                'type' => 'input',
                'size' => 25,
                'max' => 128,
                'checkbox' => '',
                'eval' => 'trim',
                'default' => 0,
                'renderType' => 'inputLink',
                'wizards' => [
                    '_PADDING' => 2,
                ]
            ]
        ],
        'teaser' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xlf:tx_cal_event.teaser',
            'config' => [
                'type' => 'text',
                'cols' => 40,
                'rows' => 6,
                'softref' => 'rtehtmlarea_images,typolink_tag,images,email[subst],url',
                'enableRichtext' => true,
                'fieldControl' => [
                    'fullScreenRichtext' => [
                        'disabled' => '',
                        'options' => [
                            'title' => 'LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:bodytext.W.RTE',
                        ],
                    ]
                ],
                'wizards' => [
                    '_PADDING' => 4,
                ]
            ]
        ],
        'description' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xlf:tx_cal_event.description',
            'config' => [
                'type' => 'text',
                'cols' => 40,
                'rows' => 6,
                'softref' => 'rtehtmlarea_images,typolink_tag,images,email[subst],url',
                'enableRichtext' => true,
                'fieldControl' => [
                    'fullScreenRichtext' => [
                        'disabled' => '',
                        'options' => [
                            'title' => 'LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:bodytext.W.RTE',
                        ],
                    ]
                ],
                'wizards' => [
                    '_PADDING' => 4,
                ]
            ]
        ],
        'freq' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xlf:tx_cal_event.freq',
            'onChange' => 'reload',
            'config' => [
                'renderType' => 'selectSingle',
                'type' => 'select',
                'size' => 1,
                'items' => [
                    [
                        'LLL:EXT:cal/Resources/Private/Language/locallang_db.xlf:frequency.none',
                        'none'
                    ],
                    [
                        'LLL:EXT:cal/Resources/Private/Language/locallang_db.xlf:frequency.day',
                        'day'
                    ],
                    [
                        'LLL:EXT:cal/Resources/Private/Language/locallang_db.xlf:frequency.week',
                        'week'
                    ],
                    [
                        'LLL:EXT:cal/Resources/Private/Language/locallang_db.xlf:frequency.month',
                        'month'
                    ],
                    [
                        'LLL:EXT:cal/Resources/Private/Language/locallang_db.xlf:frequency.year',
                        'year'
                    ]
                ]
            ]
        ],
        'byday' => [
            'exclude' => 1,
            'displayCond' => 'FIELD:freq:IN:week,month,year',
            'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xlf:tx_cal_event.byday_short',
            'config' => [
                'type' => 'user',
                'renderType' => 'calByDayElement',
                'default' => ''
            ]
        ],
        'bymonthday' => [
            'exclude' => 1,
            'displayCond' => 'FIELD:freq:IN:month,year',
            'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xlf:tx_cal_event.bymonthday_short',
            'config' => [
                'type' => 'user',
                'renderType' => 'calByMonthDayElement',
                'default' => ''
            ]
        ],
        'bymonth' => [
            'exclude' => 1,
            'displayCond' => 'FIELD:freq:IN:year',
            'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xlf:tx_cal_event.bymonth_short',
            'config' => [
                'type' => 'user',
                'renderType' => 'calByMonthElement',
                'default' => ''
            ]
        ],
        'until' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xlf:tx_cal_event.until',
            'displayCond' => 'FIELD:freq:IN:day,week,month,year',
            'config' => [
                'type' => 'input',
                'renderType' => 'inputDateTime',
                'size' => 12,
                'eval' => 'date'
            ]
        ],
        'cnt' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xlf:tx_cal_event.count',
            'displayCond' => 'FIELD:freq:IN:day,week,month,year',
            'config' => [
                'type' => 'input',
                'size' => 4,
                'eval' => 'num',
                'checkbox' => '0'
            ]
        ],
        'intrval' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xlf:tx_cal_event.interval',
            'displayCond' => 'FIELD:freq:IN:day,week,month,year',
            'config' => [
                'type' => 'input',
                'size' => 4,
                'eval' => 'num',
                'default' => '1'
            ]
        ],
        'rdate_type' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xlf:tx_cal_event.rdate_type',
            'onChange' => 'reload',
            'config' => [
                'renderType' => 'selectSingle',
                'type' => 'select',
                'size' => 1,
                'items' => [
                    [
                        'LLL:EXT:cal/Resources/Private/Language/locallang_db.xlf:rdate_type.none',
                        'none'
                    ],
                    [
                        'LLL:EXT:cal/Resources/Private/Language/locallang_db.xlf:rdate_type.date',
                        'date'
                    ],
                    [
                        'LLL:EXT:cal/Resources/Private/Language/locallang_db.xlf:rdate_type.date_time',
                        'date_time'
                    ],
                    [
                        'LLL:EXT:cal/Resources/Private/Language/locallang_db.xlf:rdate_type.period',
                        'period'
                    ]
                ],
                'default' => 'none'
            ]
        ],
        'rdate' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xlf:tx_cal_event.rdate',
            'displayCond' => 'FIELD:rdate_type:IN:date_time,date,period',
            'config' => [
                'type' => 'user',
                'renderType' => 'calRDateElement',
                'default' => ''
            ]
        ],
        'deviation' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xlf:tx_cal_event.deviation',
            'config' => [
                'type' => 'inline',
                'foreign_table' => 'tx_cal_event_deviation',
                'foreign_field' => 'parentid',
                'foreign_label' => 'title',
                'maxitems' => 10,
                'appearance' => [
                    'collapseAll' => 1,
                    'expandSingle' => 1,
                    'useSortable' => 1
                ]
            ]
        ],
        'monitor_cnt' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xlf:tx_cal_fe_user_event.monitor',
            'config' => [
                'type' => 'inline',
                'foreign_table' => 'tx_cal_fe_user_event_monitor_mm',
                'foreign_field' => 'uid_local',
                'appearance' => [
                    'collapseAll' => 1,
                    'expandSingle' => 1,
                    'useSortable' => 1
                ]
            ]
        ],
        'exception_cnt' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xlf:tx_cal_event.exception',
            'config' => [
                'type' => 'group',
                'internal_type' => 'db',
                'allowed' => 'tx_cal_exception_event,tx_cal_exception_event_group',
                'size' => 6,
                'minitems' => 0,
                'maxitems' => 100,
                'MM' => 'tx_cal_exception_event_mm'
            ]
        ],
        'fe_cruser_id' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xlf:tx_cal_event.fe_cruser_id',
            'config' => [
                'type' => 'group',
                'internal_type' => 'db',
                'allowed' => 'fe_users',
                'size' => 1,
                'minitems' => 0,
                'maxitems' => 1,
            ]
        ],
        'fe_crgroup_id' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xlf:tx_cal_event.fe_crgroup_id',
            'config' => [
                'type' => 'group',
                'internal_type' => 'db',
                'allowed' => 'fe_groups',
                'size' => 1,
                'minitems' => 0,
                'maxitems' => 1,
            ]
        ],

        'shared_user_cnt' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xlf:tx_cal_event.shared_user',
            'config' => [
                'type' => 'group',
                'internal_type' => 'db',
                'allowed' => 'fe_users,fe_groups',
                'size' => 6,
                'minitems' => 0,
                'maxitems' => 100,
                'MM' => 'tx_cal_event_shared_user_mm',
            ]
        ],

        /* new */
        'type' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xlf:tx_cal_event.type',
            'config' => [
                'renderType' => 'selectSingle',
                'type' => 'select',
                'size' => 1,
                'items' => [
                    [
                        'LLL:EXT:cal/Resources/Private/Language/locallang_db.xlf:tx_cal_event.type.I.0',
                        0
                    ],
                    [
                        'LLL:EXT:cal/Resources/Private/Language/locallang_db.xlf:tx_cal_event.type.I.1',
                        1
                    ],
                    [
                        'LLL:EXT:cal/Resources/Private/Language/locallang_db.xlf:tx_cal_event.type.I.2',
                        2
                    ],
                    [
                        'LLL:EXT:cal/Resources/Private/Language/locallang_db.xlf:tx_cal_event.type.I.3',
                        3
                    ],
                    [
                        'LLL:EXT:cal/Resources/Private/Language/locallang_db.xlf:tx_cal_event.type.I.4',
                        4
                    ]
                ],
                'default' => 0
            ]
        ],

        'ext_url' => [
            'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xlf:tx_cal_event.external',
            'config' => [
                'type' => 'input',
                'size' => 40,
                'max' => 256,
                'eval' => 'required',
                'renderType' => 'inputLink',
                'wizards' => [
                    '_PADDING' => 2,
                ],
                'default' => ''
            ]
        ],

        'page' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xlf:tx_cal_event.shortcut_page',
            'config' => [
                'type' => 'group',
                'internal_type' => 'db',
                'allowed' => 'pages',
                'size' => 1,
                'maxitems' => 1,
                'minitems' => '0',
                'eval' => 'required',
            ]
        ],
        /* new */
        'image' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:lang/Resources/Private/Language/locallang_general.xlf:LGL.images',
            'config' => ExtensionManagementUtility::getFileFieldTCAConfig('image', [
                'maxitems' => 5,
                // Use the imageoverlayPalette instead of the basicoverlayPalette
                'foreign_types' => [
                    '0' => [
                        'showitem' => '
											--palette--;LLL:EXT:lang/Resources/Private/Language/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
											--palette--;;filePalette'
                    ],
                    File::FILETYPE_IMAGE => [
                        'showitem' => '
											--palette--;LLL:EXT:lang/Resources/Private/Language/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
											--palette--;;filePalette'
                    ]
                ],
                'default' => ''
            ])
        ],

        'attachment' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:media',
            'config' => ExtensionManagementUtility::getFileFieldTCAConfig('attachment', [
                'maxitems' => 5,
                // Use the imageoverlayPalette instead of the basicoverlayPalette
                'foreign_types' => [
                    '0' => [
                        'showitem' => '
											--palette--;LLL:EXT:lang/Resources/Private/Language/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
											--palette--;;filePalette'
                    ],
                    File::FILETYPE_TEXT => [
                        'showitem' => '
											--palette--;LLL:EXT:lang/Resources/Private/Language/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
											--palette--;;filePalette'
                    ],
                    File::FILETYPE_IMAGE => [
                        'showitem' => '
											--palette--;LLL:EXT:lang/Resources/Private/Language/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
											--palette--;;filePalette'
                    ],
                    File::FILETYPE_AUDIO => [
                        'showitem' => '
											--palette--;LLL:EXT:lang/Resources/Private/Language/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
											--palette--;;filePalette'
                    ],
                    File::FILETYPE_VIDEO => [
                        'showitem' => '
											--palette--;LLL:EXT:lang/Resources/Private/Language/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
											--palette--;;filePalette'
                    ],
                    File::FILETYPE_APPLICATION => [
                        'showitem' => '
											--palette--;LLL:EXT:lang/Resources/Private/Language/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
											--palette--;;filePalette'
                    ]
                ],
                'default' => ''
            ])
        ],

        'attendee' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xlf:tx_cal_event.attendee',
            'config' => [
                'type' => 'inline',
                'foreign_table' => 'tx_cal_attendee',
                'foreign_field' => 'event_id',
                'appearance' => [
                    'collapseAll' => 1,
                    'expandSingle' => 1,
                    'useSortable' => 1
                ],
                'default' => 0
            ]
        ],
        'send_invitation' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xlf:tx_cal_event.send_invitation',
            'config' => [
                'type' => 'check',
                'default' => 0
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
                        'LLL:EXT:cal/Resources/Private/Language/locallang_db.xlf:tx_cal_event.status.COMPLETED',
                        'COMPLETED'
                    ],
                    [
                        'LLL:EXT:cal/Resources/Private/Language/locallang_db.xlf:tx_cal_event.status.IN-PROGRESS',
                        'IN-PROGRESS'
                    ],
                    [
                        'LLL:EXT:cal/Resources/Private/Language/locallang_db.xlf:tx_cal_event.status.CANCELLED',
                        'CANCELLED'
                    ]
                ],
                'size' => 1,
                'minitems' => 1,
                'maxitems' => 1
            ]
        ],
        'priority' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xlf:tx_cal_event.priority',
            'config' => [
                'renderType' => 'selectSingle',
                'type' => 'select',
                'items' => [
                    [
                        0,
                        0
                    ],
                    [
                        1,
                        1
                    ],
                    [
                        2,
                        2
                    ],
                    [
                        3,
                        3
                    ],
                    [
                        4,
                        4
                    ],
                    [
                        5,
                        5
                    ],
                    [
                        6,
                        6
                    ],
                    [
                        7,
                        7
                    ],
                    [
                        8,
                        8
                    ],
                    [
                        9,
                        9
                    ]
                ],
                'size' => 1,
                'minitems' => 1,
                'maxitems' => 1,
                'default' => 0,
            ]
        ],
        'completed' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xlf:tx_cal_event.completed',
            'config' => [
                'type' => 'input',
                'size' => 3,
                'eval' => 'num',
                'checkbox' => 0,
                'default' => 0,
            ]
        ],
        'sys_language_uid' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:lang/Resources/Private/Language/locallang_general.xlf:LGL.language',
            'config' => [
                'renderType' => 'selectSingle',
                'type' => 'select',
                'foreign_table' => 'sys_language',
                'foreign_table_where' => 'ORDER BY sys_language.title',
                'items' => [
                    [
                        'LLL:EXT:lang/Resources/Private/Language/locallang_general.xlf:LGL.allLanguages',
                        -1
                    ],
                    [
                        'LLL:EXT:lang/Resources/Private/Language/locallang_general.xlf:LGL.default_value',
                        0
                    ]
                ]
            ]
        ],
        'l18n_parent' => [
            'displayCond' => 'FIELD:sys_language_uid:>:0',
            'exclude' => 1,
            'label' => 'LLL:EXT:lang/Resources/Private/Language/locallang_general.xlf:LGL.l18n_parent',
            'config' => [
                'renderType' => 'selectSingle',
                'type' => 'select',
                'items' => [
                    [
                        '',
                        0
                    ]
                ],
                'foreign_table' => 'tx_cal_event',
                'foreign_table_where' => 'AND tx_cal_event.sys_language_uid IN (-1,0)'
            ]
        ],
        'l18n_diffsource' => [
            'config' => [
                'type' => 'passthrough'
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
            'showitem' => '--div--;LLL:EXT:cal/Resources/Private/Language/locallang_db.xlf:tx_cal_event.general_sheet,type, title, --palette--;;1,--palette--;LLL:EXT:cal/Resources/Private/Language/locallang_db.xlf:tx_cal_event.start;5,--palette--;LLL:EXT:cal/Resources/Private/Language/locallang_db.xlf:tx_cal_event.end;6,calendar_id,category_id,' . ($configuration['useTeaser'] ? 'teaser,' : '') . 'description,--div--;LLL:EXT:cal/Resources/Private/Language/locallang_db.xlf:tx_cal_event.recurrence_sheet, freq, --palette--;;2, byday, bymonthday, bymonth, rdate_type, --palette--;;7, deviation, exception_cnt,--div--;LLL:EXT:cal/Resources/Private/Language/locallang_db.xlf:tx_cal_event.location_sheet,' . ($configuration['hideLocationTextfield'] ? 'location_id,location_pid,location_link' : 'location,location_id,location_pid,location_link') . ',--div--;LLL:EXT:cal/Resources/Private/Language/locallang_db.xlf:tx_cal_event.organizer_sheet,' . ($configuration['hideOrganizerTextfield'] ? 'organizer_id,organizer_pid,organizer_link' : 'organizer,organizer_id,organizer_pid,organizer_link') . ',--div--;LLL:EXT:cal/Resources/Private/Language/locallang_db.xlf:tx_cal_event.files_sheet,image,attachment,--div--;LLL:EXT:cal/Resources/Private/Language/locallang_db.xlf:tx_cal_event.other_sheet, monitor_cnt, shared_user_cnt'
        ],
        '1' => [
            'showitem' => '--div--;LLL:EXT:cal/Resources/Private/Language/locallang_db.xlf:tx_cal_event.general_sheet,type, page,title, --palette--;;1,--palette--;LLL:EXT:cal/Resources/Private/Language/locallang_db.xlf:tx_cal_event.start;5,--palette--;LLL:EXT:cal/Resources/Private/Language/locallang_db.xlf:tx_cal_event.end;6,calendar_id,category_id,' . ($configuration['useTeaser'] ? 'teaser,' : '') . '--div--;LLL:EXT:cal/Resources/Private/Language/locallang_db.xlf:tx_cal_event.recurrence_sheet, freq, --palette--;;2, byday, bymonthday, bymonth, rdate_type, --palette--;;7, deviation, exception_cnt, --div--;LLL:EXT:cal/Resources/Private/Language/locallang_db.xlf:tx_cal_event.location_sheet,' . ($configuration['hideLocationTextfield'] ? 'location_id,location_pid,location_link' : 'location,location_id,location_pid,location_link') . ',--div--;LLL:EXT:cal/Resources/Private/Language/locallang_db.xlf:tx_cal_event.organizer_sheet,' . ($configuration['hideOrganizerTextfield'] ? 'organizer_id,organizer_pid,organizer_link' : 'organizer,organizer_id,organizer_pid,organizer_link') . ',--div--;LLL:EXT:cal/Resources/Private/Language/locallang_db.xlf:tx_cal_event.other_sheet, monitor_cnt, shared_user_cnt'
        ],
        '2' => [
            'showitem' => '--div--;LLL:EXT:cal/Resources/Private/Language/locallang_db.xlf:tx_cal_event.general_sheet,type, ext_url,title, --palette--;;1,--palette--;LLL:EXT:cal/Resources/Private/Language/locallang_db.xlf:tx_cal_event.start;5,--palette--;LLL:EXT:cal/Resources/Private/Language/locallang_db.xlf:tx_cal_event.end;6,calendar_id,category_id,' . ($configuration['useTeaser'] ? 'teaser,' : '') . '--div--;LLL:EXT:cal/Resources/Private/Language/locallang_db.xlf:tx_cal_event.recurrence_sheet, freq, --palette--;;2, byday, bymonthday, bymonth, rdate_type, --palette--;;7, deviation, exception_cnt, --div--;LLL:EXT:cal/Resources/Private/Language/locallang_db.xlf:tx_cal_event.location_sheet,' . ($configuration['hideLocationTextfield'] ? 'location_id,location_pid,location_link' : 'location,location_id,location_pid,location_link') . ',--div--;LLL:EXT:cal/Resources/Private/Language/locallang_db.xlf:tx_cal_event.organizer_sheet,' . ($configuration['hideOrganizerTextfield'] ? 'organizer_id,organizer_pid,organizer_link' : 'organizer,organizer_id,organizer_pid,organizer_link') . ',--div--;LLL:EXT:cal/Resources/Private/Language/locallang_db.xlf:tx_cal_event.other_sheet, monitor_cnt, shared_user_cnt'
        ],
        '3' => [
            'showitem' => '--div--;LLL:EXT:cal/Resources/Private/Language/locallang_db.xlf:tx_cal_event.general_sheet,type, title, --palette--;;1,--palette--;LLL:EXT:cal/Resources/Private/Language/locallang_db.xlf:tx_cal_event.start;5,--palette--;LLL:EXT:cal/Resources/Private/Language/locallang_db.xlf:tx_cal_event.end;6,calendar_id,category_id,--div--;LLL:EXT:cal/Resources/Private/Language/locallang_db.xlf:tx_cal_event.attendance_sheet,attendee,send_invitation,--div--;LLL:EXT:cal/Resources/Private/Language/locallang_db.xlf:tx_cal_event.recurrence_sheet, freq, --palette--;;2, byday, bymonthday, bymonth, rdate_type, --palette--;;7, deviation, exception_cnt,--div--;LLL:EXT:cal/Resources/Private/Language/locallang_db.xlf:tx_cal_event.location_sheet,' . ($configuration['hideLocationTextfield'] ? 'location_id,location_pid,location_link' : 'location,location_id,location_pid,location_link') . ',--div--;LLL:EXT:cal/Resources/Private/Language/locallang_db.xlf:tx_cal_event.organizer_sheet,' . ($configuration['hideOrganizerTextfield'] ? 'organizer_id,organizer_pid,organizer_link' : 'organizer,organizer_id,organizer_pid,organizer_link')
        ],
        '4' => [
            'showitem' => '--div--;LLL:EXT:cal/Resources/Private/Language/locallang_db.xlf:tx_cal_event.general_sheet,type, title, --palette--;;1,--palette--;LLL:EXT:cal/Resources/Private/Language/locallang_db.xlf:tx_cal_event.start;5,--palette--;LLL:EXT:cal/Resources/Private/Language/locallang_db.xlf:tx_cal_event.due;6,calendar_id,category_id,description,--div--;LLL:EXT:cal/Resources/Private/Language/locallang_db.xlf:tx_cal_event.todo_sheet, status, priority, completed,--div--;LLL:EXT:cal/Resources/Private/Language/locallang_db.xlf:tx_cal_event.recurrence_sheet, freq, --palette--;;2, byday, bymonthday, bymonth, rdate_type, --palette--;;7, deviation, exception_cnt,--div--;LLL:EXT:cal/Resources/Private/Language/locallang_db.xlf:tx_cal_event.location_sheet,' . ($configuration['hideLocationTextfield'] ? 'location_id,location_pid,location_link' : 'location,location_id,location_pid,location_link') . ',--div--;LLL:EXT:cal/Resources/Private/Language/locallang_db.xlf:tx_cal_event.organizer_sheet,' . ($configuration['hideOrganizerTextfield'] ? 'organizer_id,organizer_pid,organizer_link' : 'organizer,organizer_id,organizer_pid,organizer_link') . ',--div--;LLL:EXT:cal/Resources/Private/Language/locallang_db.xlf:tx_cal_event.files_sheet,attachment'
        ]
    ],
    'palettes' => [
        '1' => [
            'showitem' => 'hidden,l18n_parent,sys_language_uid,t3ver_label',
            'canNotCollapse' => 1
        ],
        '2' => [
            'showitem' => 'until, cnt, intrval',
            'canNotCollapse' => 1
        ],
        '5' => [
            'showitem' => 'allday,--linebreak--,start_date,start_time',
            'canNotCollapse' => 1
        ],
        '6' => [
            'showitem' => 'end_date,end_time',
            'canNotCollapse' => 1
        ],
        '7' => [
            'showitem' => 'rdate',
            'canNotCollapse' => 1
        ]
    ]
];

$tx_cal_event['columns']['attachment']['config'] = ExtensionManagementUtility::getFileFieldTCAConfig(
    'attachment',
    [
        'appearance' => [
            'createNewRelationLinkTitle' => 'LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:media.addFileReference'
        ],
    ]
);

return $tx_cal_event;
