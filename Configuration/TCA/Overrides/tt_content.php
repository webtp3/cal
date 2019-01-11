<?php
if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

$extensionName = \TYPO3\CMS\Core\Utility\GeneralUtility::underscoredToUpperCamelCase('cal');
$pluginSignature = strtolower($extensionName) . '_controller';

/***************
 * Plugin
 */
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPlugin(
    [
        'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tt_content.list_type',
        'cal_controller'
    ],
    'list_type',
    'cal'
);

$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist'][$pluginSignature] = 'layout,select_key';
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist'][$pluginSignature] = 'pi_flexform';

$extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['cal']);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue(
    $pluginSignature,
    'FILE:EXT:' . 'cal/Configuration/FlexForms/flexform_cal_sys_category.xml'
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
