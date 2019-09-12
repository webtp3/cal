<?php

namespace TYPO3\CMS\Cal\Ajax;

/**
 * This file is part of the TYPO3 extension Calendar Base (cal).
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 extension Calendar Base (cal) project - inspiring people to share!
 */

// Exit, if script is called directly (must be included via eID in index_ts.php)
use TYPO3\CMS\Cal\Controller\Api;
use TYPO3\CMS\Cal\Utility\Functions;
use TYPO3\CMS\Cal\Utility\Registry;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\Utility\EidUtility;

if (!defined('PATH_typo3conf')) {
    die('Could not access this script directly!');
}

if ($_COOKIE['fe_typo_user']) {
    session_id($_COOKIE['fe_typo_user']);
    session_start();
}
// Initialize FE user object:
$feUserObj = EidUtility::initFeUser();
// Connect to database:
EidUtility::connectDB();
$controllerPiVarsGET = GeneralUtility::_GET('tx_cal_controller');
$controllerPiVarsPOST = GeneralUtility::_POST('tx_cal_controller');
$controllerPiVars = [];
if (is_array($controllerPiVarsPOST) && is_array($controllerPiVarsGET)) {
    $controllerPiVars = array_merge($controllerPiVarsPOST, $controllerPiVarsGET);
} elseif (is_array($controllerPiVarsPOST)) {
    $controllerPiVars = $controllerPiVarsPOST;
} elseif (is_array($controllerPiVarsGET)) {
    $controllerPiVars = $controllerPiVarsGET;
}

$pid = intval($controllerPiVars['pid']);
$view = $controllerPiVars['view'];

/** @var Api $calAPI */
$calAPI = GeneralUtility::makeInstance(Api::class);
if (is_array($_SESSION['cal_api_' . $pid . '_conf'])) {
    $cObj = new ContentObjectRenderer();
    $GLOBALS['TSFE'] = &$_SESSION['cal_api_' . $pid . '_tsfe'];
    $GLOBALS['TCA'] = &$_SESSION['cal_api_' . $pid . '_tca'];
    $calAPI = $calAPI->tx_cal_api_with($cObj, $_SESSION['cal_api_' . $pid . '_conf']);
} else {
    $calAPI = $calAPI->tx_cal_api_without($pid, $feUserObj);
    $_SESSION['cal_api_' . $pid . '_conf'] = $calAPI->conf;
    $_SESSION['cal_api_' . $pid . '_tsfe'] = $GLOBALS['TSFE'];
    $_SESSION['cal_api_' . $pid . '_tca'] = $GLOBALS['TCA'];
}

if ($controllerPiVars['translations']) {
    if ($calAPI->controller->conf['language']) {
        $calAPI->controller->LLkey = $calAPI->controller->conf['language'];
    }
    $tempScriptRelPath = $calAPI->controller->scriptRelPath;
    $calAPI->controller->scriptRelPath = $calAPI->controller->locallangPath;
    $calAPI->controller->pi_loadLL();
    $calAPI->controller->scriptRelPath = $tempScriptRelPath;

    switch ($controllerPiVars['translations']) {
        case 'day':
        case 'month':
            $returnValue = [
                'timeSeparator' => ' ' . $calAPI->controller->pi_getLL('l_to') . ' ',
                'newEventText' => $calAPI->controller->pi_getLL('l_new_event'),
                'shortMonths' => Functions::getMonthNames('%b'),
                'longMonths' => Functions::getMonthNames('%B'),
                'shortDays' => Functions::getWeekdayNames('%a'),
                'longDays' => Functions::getWeekdayNames('%A'),
                'buttonText' => [
                    'today' => $calAPI->controller->pi_getLL('l_today'),
                    'lastWeek' => $calAPI->controller->pi_getLL('l_prev'),
                    'nextWeek' => $calAPI->controller->pi_getLL('l_next'),
                    'create' => $calAPI->controller->pi_getLL('l_create'),
                    'edit' => $calAPI->controller->pi_getLL('l_edit'),
                    'deleteText' => $calAPI->controller->pi_getLL('l_delete'),
                    'save' => $calAPI->controller->pi_getLL('l_save'),
                    'cancel' => $calAPI->controller->pi_getLL('l_cancel')
                ]
            ];
            $ajax_return_data = json_encode($returnValue);
            $htmlheader_contenttype = 'Content-Type: application/json';
            break;
    }
} elseif (is_array($controllerPiVars['translate'])) {
    $tempScriptRelPath = $calAPI->controller->scriptRelPath;
    $calAPI->controller->scriptRelPath = $calAPI->controller->locallangPath;
    $calAPI->controller->pi_loadLL();
    $calAPI->controller->scriptRelPath = $tempScriptRelPath;
    $translationArray = [];
    foreach ($controllerPiVars['translate'] as $value) {
        $translationArray[$value] = $calAPI->controller->pi_getLL('l_' . strtolower($value));
    }
    $ajax_return_data = json_encode($translationArray);
    $htmlheader_contenttype = 'Content-Type: application/json';
} else {
    $rightsObj = &Registry::Registry('basic', 'rightscontroller');
    $checkedView = $rightsObj->checkView($view);
    $error = true;

    if ($checkedView == $view) {
        $return = $calAPI->controller->getContent(false);
        echo $return;
        exit();
    }
    $res = 'You do not have the proper rights!' . $checkedView . '=' . $view;

    $ajax_return_data = GeneralUtility::array2xml([
        'error' => $error,
        'response' => $res
    ]);
    $htmlheader_contenttype = 'Content-Type: text/xml';
}
header('Expires: ' . gmdate('D, d M Y H:i:s') . ' GMT');
// gmdate is ok.
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . 'GMT');
header('Cache-Control: no-cache, must-revalidate');
header('Pragma: no-cache');
header('Content-Length: ' . strlen($ajax_return_data));
header($htmlheader_contenttype);

echo $ajax_return_data;
exit();
