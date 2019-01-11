<?php
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
        'config' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::getFileFieldTCAConfig('attachment', [
            'maxitems' => 5,
            'foreign_types' => [
                '0' => [
                    'showitem' => '
                        --palette--;LLL:EXT:core/Resources/Private/Language/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
                        --palette--;;filePalette'
                ],
                \TYPO3\CMS\Core\Resource\File::FILETYPE_TEXT => [
                    'showitem' => '
                        --palette--;LLL:EXT:core/Resources/Private/Language/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
                        --palette--;;filePalette'
                ],
                \TYPO3\CMS\Core\Resource\File::FILETYPE_IMAGE => [
                    'showitem' => '
                        --palette--;LLL:EXT:core/Resources/Private/Language/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
                        --palette--;;filePalette'
                ],
                \TYPO3\CMS\Core\Resource\File::FILETYPE_AUDIO => [
                    'showitem' => '
                        --palette--;LLL:EXT:core/Resources/Private/Language/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
                        --palette--;;filePalette'
                ],
                \TYPO3\CMS\Core\Resource\File::FILETYPE_VIDEO => [
                    'showitem' => '
                        --palette--;LLL:EXT:core/Resources/Private/Language/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
                        --palette--;;filePalette'
                ],
                \TYPO3\CMS\Core\Resource\File::FILETYPE_APPLICATION => [
                    'showitem' => '
                        --palette--;LLL:EXT:core/Resources/Private/Language/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
                        --palette--;;filePalette'
                ]
            ],
            'default' => ''
        ])
    ],
];

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('tx_cal_event', $tempColumns);
