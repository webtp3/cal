<?php

use SJBR\StaticInfoTables\Hook\Backend\Form\Wizard\SuggestReceiver;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

defined('TYPO3_MODE') or die();

$tx_cal_location = [
    'ctrl' => [
        'title' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_location',
        'label' => 'name',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'default_sortby' => 'ORDER BY name',
        'delete' => 'deleted',
        'iconfile' => 'EXT:cal/Resources/Public/Icons/tx_cal_location.svg',
        'enablecolumns' => [
            'disabled' => 'hidden'
        ],
        'versioningWS' => true,
        'origUid' => 't3_origuid',
        'shadowColumnsForNewPlaceholders' => 'sys_language_uid,l18n_parent',
        'transOrigPointerField' => 'l18n_parent',
        'transOrigDiffSourceField' => 'l18n_diffsource',
        'languageField' => 'sys_language_uid',
        'searchFields' => 'name,description,street,zip,city,country_zone,country,phone,fax,email,image,imagecaption,imagealttext,imagetitletext,link,latitute,longitute'
    ],
    'feInterface' => [
        'fe_admin_fieldList' => 'name'
    ],
    'interface' => [
        'showRecordFieldList' => 'hidden, name,description,street,zip,city,country,phone,fax,email,image,link,shared_user_cnt'
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
        'name' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_location.name',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'max' => 128,
                'eval' => 'required',
            ]
        ],
        'description' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_location.description',
            'config' => [
                'type' => 'text',
                'cols' => 30,
                'rows' => 5,
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
                    '_PADDING' => 2,
                ],
                'default' => '',
            ]
        ],
        'street' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_location.street',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'max' => 128,
                'default' => '',
            ]
        ],
        'zip' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_location.zip',
            'config' => [
                'type' => 'input',
                'size' => 15,
                'max' => 15,
                'default' => '',
            ]
        ],
        'city' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_location.city',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'max' => 128,
                'default' => '',
            ]
        ],
        'phone' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_location.phone',
            'config' => [
                'type' => 'input',
                'size' => 15,
                'max' => 24,
                'default' => '',
            ]
        ],
        'fax' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_location.fax',
            'config' => [
                'type' => 'input',
                'size' => 15,
                'max' => 24,
                'default' => '',
            ]
        ],
        'email' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_location.email',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'max' => 64,
                'eval' => 'lower, email',
                'default' => '',
            ]
        ],
        'image' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_location.image',
            'config' => ExtensionManagementUtility::getFileFieldTCAConfig('image', [
                'maxitems' => 5,
                // Use the imageoverlayPalette instead of the basicoverlayPalette
                'foreign_types' => [
                    '0' => [
                        'showitem' => '
						--palette--;LLL:EXT:core/Resources/Private/Language/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
						--palette--;;filePalette'
                    ],
                    File::FILETYPE_IMAGE => [
                        'showitem' => '
						--palette--;LLL:EXT:core/Resources/Private/Language/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
						--palette--;;filePalette'
                    ]
                ]
            ])
        ],
        'link' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_location.link',
            'config' => [
                'type' => 'input',
                'size' => 25,
                'max' => 128,
                'checkbox' => '',
                'eval' => 'trim',
                'default' => '',
                'renderType' => 'inputLink',
                'wizards' => [
                    '_PADDING' => 2,
                ]
            ]
        ],
        'shared_user_cnt' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_event.shared_user',
            'config' => [
                'type' => 'group',
                'internal_type' => 'db',
                'allowed' => 'fe_users,fe_groups',
                'size' => 6,
                'minitems' => 0,
                'maxitems' => 100,
                'MM' => 'tx_cal_location_shared_user_mm',
                'default' => 0,
            ]
        ],
        'latitude' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_location.latitude',
            'config' => [
                'type' => 'input',
                'readOnly' => 1,
                'default' => 0,
            ]
        ],
        'longitude' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_location.longitude',
            'config' => [
                'type' => 'input',
                'readOnly' => 1,
                'default' => 0,
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
                'foreign_table' => 'tx_cal_location',
                'foreign_table_where' => 'AND tx_cal_location.sys_language_uid IN (-1,0)'
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
            'showitem' => 'name, --palette--;;1, description, street, city, country, country_zone, zip, latitude, longitude, phone, fax, email, image, link, shared_user_cnt'
        ]
    ],
    'palettes' => [
        '1' => [
            'showitem' => 'hidden,l18n_parent,sys_language_uid,t3ver_label'
        ]
    ]
];

$dummy = [
    'exclude' => 1,
    'label' => 'dummy',
    'config' => [
        'type' => 'text',
        'default' => '',
    ]
];

$tx_cal_location['columns']['imagecaption'] = $dummy;
$tx_cal_location['columns']['imagealttext'] = $dummy;
$tx_cal_location['columns']['imagetitletext'] = $dummy;

if (ExtensionManagementUtility::isLoaded('static_info_tables')) {
    $tx_cal_location['columns']['country_zone'] = [
        'exclude' => 1,
        'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_location.countryzone',
        'config' => [
            'type' => 'select',
            'renderType' => 'selectSingle',
            'items' => [
                [
                    '',
                    0
                ]
            ],
            'foreign_table' => 'static_country_zones',
            'foreign_table_where' => 'ORDER BY static_country_zones.zn_name_en',
            'itemsProcFunc' => 'SJBR\\StaticInfoTables\\Hook\\Backend\\Form\\ElementRenderingHelper->translateCountryZonesSelector',
            'size' => 1,
            'minitems' => 0,
            'maxitems' => 1,
            'default' => 0,
        ]
    ];
    $tx_cal_location['columns']['country'] = [
        'exclude' => 1,
        'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_location.country',
        'config' => [
            'type' => 'select',
            'renderType' => 'selectSingle',
            'items' => [
                [
                    '',
                    0
                ]
            ],
            'foreign_table' => 'static_countries',
            'foreign_table_where' => 'ORDER BY static_countries.cn_short_en',
            'itemsProcFunc' => 'SJBR\\StaticInfoTables\\Hook\\Backend\\Form\\ElementRenderingHelper->translateCountriesSelector',
            'size' => 1,
            'minitems' => 0,
            'maxitems' => 1,
            'default' => 0,
        ]
    ];
    $tx_cal_location['columns']['country_zone']['config']['itemsProcFunc'] = 'SJBR\\StaticInfoTables\\Hook\\Backend\\Form\\FormDataProvider\\TcaSelectItemsProcessor->translateCountryZonesSelector';
    $tx_cal_location['columns']['country_zone']['config']['wizards']['suggest']['default']['receiverClass'] = SuggestReceiver::class;
    $tx_cal_location['columns']['country']['config']['itemsProcFunc'] = 'SJBR\\StaticInfoTables\\Hook\\Backend\\Form\\FormDataProvider\\TcaSelectItemsProcessor->translateCountriesSelector';
    $tx_cal_location['columns']['country']['config']['wizards']['suggest']['default']['receiverClass'] = SuggestReceiver::class;
}

return $tx_cal_location;
