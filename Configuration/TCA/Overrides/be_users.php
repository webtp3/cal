<?php

/*
 * This file is part of the web-tp3/cal.
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

defined('TYPO3_MODE') or die();

// Define the TCA for a checkbox and calendar-/category selector to enable access control.
$tempColumns = [
    'tx_cal_enable_accesscontroll' => [
        'exclude' => 1,
        'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xlf:tx_cal_enable_accesscontroll',
        'onChange' => 'reload',
        'config' => [
            'type' => 'check',
            'default' => 0
        ]
    ],
    'tx_cal_calendar' => [
        'exclude' => 1,
        'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xlf:tx_cal_calendar_accesscontroll',
        'displayCond' => 'FIELD:tx_cal_enable_accesscontroll:REQ:true',
        'config' => [
            'renderType' => 'selectMultipleSideBySide',
            'type' => 'select',
            'size' => 10,
            'minitems' => 0,
            'maxitems' => 100,
            'autoSizeMax' => 20,
            'itemListStyle' => 'height:130px;',
            'foreign_table' => 'tx_cal_calendar'
        ]
    ],
    'tx_cal_category' => [
        'exclude' => 1,
        'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xlf:tx_cal_category_accesscontroll',
        'displayCond' => 'FIELD:tx_cal_enable_accesscontroll:REQ:true',
        'config' => [
            'renderType' => 'selectMultipleSideBySide',
            'type' => 'select',
            'form_type' => 'user',
            'userFunc' => 'TYPO3\CMS\Cal\TreeProvider\TreeView->displayCategoryTree',
            'treeView' => 1,
            'size' => 20,
            'minitems' => 0,
            'maxitems' => 100,
            'autoSizeMax' => 20,
            'itemListStyle' => 'height:270px;',
            'foreign_table' => 'sys_category'
        ]
    ]
];

// Add the checkbox and the calendar-/category selector for backend users.
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('be_users', $tempColumns);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('be_users', 'tx_cal_enable_accesscontroll', '0');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('be_users', 'tx_cal_calendar', '0');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('be_users', 'tx_cal_category', '0');
