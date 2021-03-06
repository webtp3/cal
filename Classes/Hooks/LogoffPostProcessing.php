<?php

/*
 * This file is part of the web-tp3/cal.
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace TYPO3\CMS\Cal\Hooks;

use TYPO3\CMS\Cal\Utility\Functions;
use TYPO3\CMS\Core\Utility\GeneralUtility;

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
class LogoffPostProcessing
{
    /**
     * @param $params
     * @param $pObj
     */
    public function clearSessionApiAfterLogin($params, &$pObj)
    {
        if ($_COOKIE['fe_typo_user']) {
            session_id($_COOKIE['fe_typo_user']);
            session_start();
            if (!is_array($_SESSION)) {
                $_SESSION = [];
            }

            $sessionEntries = array_keys($_SESSION);
            foreach ($sessionEntries as $key) {
                if (Functions::beginsWith($key, 'cal_api')) {
                    unset($_SESSION[$key]);
                }
            }
        }
    }

    /**
     * @param $_params
     * @param $pObj
     */
    public function clearSessionApiAfterLogoff($_params, &$pObj)
    {
        if ($_COOKIE['fe_typo_user'] && GeneralUtility::_GP('logintype') === 'logout') {
            session_id($_COOKIE['fe_typo_user']);
            session_start();

            if ((int)$_SESSION['cal_api_logoff'] !== 1) {
                if (is_array($_SESSION)) {
                    $sessionEntries = array_keys($_SESSION);
                    foreach ($sessionEntries as $key) {
                        if (Functions::beginsWith($key, 'cal_api')) {
                            unset($_SESSION[$key]);
                        }
                    }
                }
                $_SESSION['cal_api_logoff'] = 1;
            }
        }
    }
}
