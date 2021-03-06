<?php

/*
 * This file is part of the web-tp3/cal.
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace TYPO3\CMS\Cal\Controller;

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
class EventLinkHandler
{
    /**
     * @param $linktxt
     * @param $conf
     * @param $linkHandlerKeyword
     * @param $linkHandlerValue
     * @param $link_param
     * @param $pObj
     */
    public function main($linktxt, $conf, $linkHandlerKeyword, $linkHandlerValue, $link_param, & $pObj)
    {
        if ($linkHandlerKeyword !== 'calendar') {
            return;
        }

        $values = explode('|', $linkHandlerValue);
        $lconf = [];
        if ($values[1]) {
            $lconf['parameter'] = $values[1];
        }
        $lconf['additionalParams'] = '&tx_cal_controller[view]=event&tx_cal_controller[type]=tx_cal_phpicalendar&tx_cal_controller[uid]=' . $values[0];
        return $pObj->typoLink($linktxt, $lconf);
    }
}
