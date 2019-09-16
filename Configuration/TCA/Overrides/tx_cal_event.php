<?php

/*
 * This file is part of the web-tp3/cal.
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

defined('TYPO3_MODE') or die();

/**
 * For some reasons the definition in the main TCA is not respected.
 * E.g. the default definition only shows up when using an override file.
 * I assume some global "Gimme all fields called attachment and apply
 * some default settings." behaviour.
 * @TODO: Analyse the reason for this and fix it.
 */
$tempColumns = [
    'attachment' => [
        'exclude' => 1,
        'label' => 'LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:media',
        'config' => ExtensionManagementUtility::getFileFieldTCAConfig('attachment', [
            'maxitems' => 5,
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
];

ExtensionManagementUtility::addTCAcolumns('tx_cal_event', $tempColumns);
