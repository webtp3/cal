<?php

/*
 * This file is part of the web-tp3/cal.
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

use TYPO3\CMS\Cal\Backend\TCA\Labels;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

defined('TYPO3_MODE') or die();

$configuration = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['cal']);

$sPid = '###CURRENT_PID###'; // storage pid????

$useLocationStructure = $configuration['useLocationStructure'] ?: 'tx_cal_location';
$useOrganizerStructure = $configuration['useOrganizerStructure'] ?: 'tx_cal_organizer';

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

$tx_cal_event_deviation = [
    'ctrl' => [
        'title' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xlf:tx_cal_event.deviation',
        'label' => 'title',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'default_sortby' => 'start_date',
        'delete' => 'deleted',
        'iconfile' => 'EXT:cal/Resources/Public/Icons/tx_cal_event_deviation.svg',
        'enablecolumns' => [
            'disabled' => 'hidden'
        ],
        'versioningWS' => true,
     //   'hideTable' => $configuration['hideDeviationRecords'],
        'searchFields' => 'title,organizer,organizer_link,location,location_link,teaser,description,image,imagecaption,imagealttext,imagetitletext,attachment,attachmentcaption',
        'label_userFunc' => Labels::class . '->getDeviationRecordLabel'
    ],
    'interface' => [
        'showRecordFieldList' => 'hidden,title,start_date,start_time,allday,end_date,end_time,organizer,location,description,image,attachment'
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
        'parentid' => [
            'config' => [
                'type' => 'passthrough'
            ]
        ],
        'title' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xlf:tx_cal_event.title',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'max' => 128,
                'default' => '',
            ]
        ],
        'starttime' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:lang/Resources/Private/Language/locallang_general.xlf:LGL.starttime',
            'config' => [
                'type' => 'input',
                'renderType' => 'inputDateTime',
                'size' => 12,
                'eval' => 'datetime',
                'default' => 0,
                'checkbox' => 0
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
                'checkbox' => 0
            ]
        ],
        'orig_start_date' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xlf:tx_cal_event.orig_start_date',
            'config' => [
                'type' => 'input',
                'renderType' => 'inputDateTime',
                'size' => 12,
                'eval' => 'required,date'
            ]
        ],
        'orig_start_time' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xlf:tx_cal_event.orig_start_time',
            'config' => [
                'type' => 'input',
                'renderType' => 'inputDateTime',
                'size' => 12,
                'default' => 0,
                'eval' => 'time'
            ]
        ],
        'start_date' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xlf:tx_cal_event.start_date',
            'config' => [
                'type' => 'input',
                'renderType' => 'inputDateTime',
                'size' => 12,
                'default' => 0,
                'eval' => 'date'
            ]
        ],
        'allday' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xlf:tx_cal_event.allday',
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
                'eval' => 'date',
                'default' => 0,
                'tx_cal_event' => 'start_date'
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
                'minitems' => 0,
                'default' => 0,
            ]
        ],
        'organizer_link' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xlf:tx_cal_event.organizer_link',
            'config' => [
                'type' => 'input',
                'size' => 25,
                'checkbox' => '',
                'default' =>'',
                'eval' => 'trim',
                'renderType' => 'inputLink'
            ]
        ],
        'location' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xlf:tx_cal_event.location',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'max' => 128,
                'default' => '',
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
                'minitems' => 0,
                'default' => 0,
            ]
        ],
        'location_link' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xlf:tx_cal_event.location_link',
            'config' => [
                'type' => 'input',
                'size' => 25,
                'checkbox' => '',
                'default' => '',
                'eval' => 'trim',
                'renderType' => 'inputLink',
                'wizards' => [
                    '_PADDING' => 2,
                    'link' => [
                        'type' => 'popup',
                        'title' => 'Link',
                        'icon' => 'actions-wizard-link',
                        'module' => [
                            'name' => 'wizard_link'
                        ],
                        'JSopenParams' => 'height=300,width=500,status=0,menubar=0,scrollbars=1'
                    ]
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
                'default' => '',
                'enableRichtext' => true,
                'fieldControl' => [
                    'fullScreenRichtext' => [
                        'disabled' => '',
                        'options' => [
                            'title' => 'LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:bodytext.W.RTE',
                        ],
                    ]
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
        'image' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:lang/Resources/Private/Language/locallang_general.xlf:LGL.images',
            'config' => ExtensionManagementUtility::getFileFieldTCAConfig('image', [
                'maxitems' => 5,
                'default' => 0,
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
                ]
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
                ]
            ])
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
            'showitem' => '--div--;LLL:EXT:cal/Resources/Private/Language/locallang_db.xlf:tx_cal_event.general_sheet,--palette--;LLL:EXT:cal/Resources/Private/Language/locallang_db.xlf:tx_cal_event.orig_start;3, title, --palette--;;1,--palette--;LLL:EXT:cal/Resources/Private/Language/locallang_db.xlf:tx_cal_event.start;5,--palette--;LLL:EXT:cal/Resources/Private/Language/locallang_db.xlf:tx_cal_event.end;6,' . ($configuration['useTeaser'] ? 'teaser,' : '') . 'description, --div--;LLL:EXT:cal/Resources/Private/Language/locallang_db.xlf:tx_cal_event.location_sheet,' . ($configuration['hideLocationTextfield'] ? 'location_id,location_pid,location_link' : 'location,location_id,location_pid,location_link') . ',--div--;LLL:EXT:cal/Resources/Private/Language/locallang_db.xlf:tx_cal_event.organizer_sheet,' . ($configuration['hideOrganizerTextfield'] ? 'organizer_id,organizer_pid,organizer_link' : 'organizer,organizer_id,organizer_pid,organizer_link') . ',--div--;LLL:EXT:cal/Resources/Private/Language/locallang_db.xlf:tx_cal_event.files_sheet,image, --palette--;;4,imagecaption,attachment,attachmentcaption'
        ]
    ],
    'palettes' => [
        '1' => [
            'showitem' => 'hidden,l18n_parent,sys_language_uid,t3ver_label'
        ],
        '3' => [
            'showitem' => 'orig_start_date,orig_start_time',
            'canNotCollapse' => 1
        ],
        '5' => [
            'showitem' => 'allday,--linebreak--,start_date,start_time',
            'canNotCollapse' => 1
        ],
        '6' => [
            'showitem' => 'end_date,end_time',
            'canNotCollapse' => 1
        ]
    ]
];

$tx_cal_event_deviation['columns']['attachment']['config'] = ExtensionManagementUtility::getFileFieldTCAConfig(
    'attachment',
    [
        'appearance' => [
            'createNewRelationLinkTitle' => 'LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:media.addFileReference'
        ],
    ]
);

return $tx_cal_event_deviation;
