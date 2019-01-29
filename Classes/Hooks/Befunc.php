<?php

namespace TYPO3\CMS\Cal\Hooks;

use TYPO3\CMS\Cal\Model\CalDate;

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

/**
 * This hook extends the befunc class.
 * It changes the date values in the list view for tx_cal_event and tx_cal_exception_event
 */
class Befunc
{
    /**
     * @param $conf
     */
    public function preprocessvalue(&$conf)
    {
        if ($conf['tx_cal_event']) {
            unset($conf['eval']);
        }
    }

    /**
     * @param $conf
     * @return string
     */
    public function postprocessvalue(&$conf): string
    {
        if ($conf['colConf']['tx_cal_event']) {
            $value = new CalDate($conf['value'] . '000000');
            if ((int)$GLOBALS['TYPO3_CONF_VARS']['SYS']['USdateFormat'] === '1') {
                $conf['value'] = $value->format('%d.%m.%Y');
            } else {
                $conf['value'] = $value->format('%d-%m-%Y');
            }
        }
        return $conf['value'] ?? '';
    }
}
