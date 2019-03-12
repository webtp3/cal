<?php

/*
 * This file is part of the web-tp3/cal.
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

use TYPO3\CMS\Core\Utility\GeneralUtility;

if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}
$_EXTKEY = $GLOBALS['_EXTKEY'] = 'cal';
$extensionName = \TYPO3\CMS\Core\Utility\GeneralUtility::underscoredToUpperCamelCase($_EXTKEY);
$pluginSignature = strtolower($extensionName) . '_controller';

/***************
 * Plugin
 */
//\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPlugin([
//        'LLL:EXT:cal/Resources/Private/Language/locallang_db.xlf:tt_content.list_type',
//        $_EXTKEY . '_controller'
//], 'list_type');

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    'Cal',
    'Controller',
    'tp3 typo3 Calendar - ext:cal'
);

$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist'][$pluginSignature] = 'layout,select_key';
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist'][$pluginSignature] = 'pi_flexform';

$extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['cal']);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue(
    $pluginSignature,
    'FILE:EXT:' . 'cal/Configuration/FlexForms/flexform_cal_sys_category.xlf'
);

/***************
 * Default TypoScript
 */
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(
    'cal',
    'Configuration/TypoScript/ts/',
    'Classic CSS-based template'
);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(
    'cal',
    'Configuration/TypoScript/ts_standard/',
    'Standard CSS-based template'
);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(
    'cal',
    'Configuration/TypoScript/ajax/',
    'AJAX-based template (Experimental!)'
);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(
    'cal',
    'Configuration/TypoScript/css/',
    'Classic CSS styles'
);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(
    'cal',
    'Configuration/TypoScript/css_standard/',
    'Standard CSS styles'
);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(
    'cal',
    'Configuration/TypoScript/rss_feed/',
    'News-feed (RSS,RDF,ATOM)'
);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(
    'cal',
    'Configuration/TypoScript/ics/',
    'ICS Export'
);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(
    'cal',
    'Configuration/TypoScript/fe-editing/',
    'Fe-Editing'
);
