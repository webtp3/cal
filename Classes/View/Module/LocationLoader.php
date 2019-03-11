<?php

/*
 * This file is part of the web-tp3/cal.
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace TYPO3\CMS\Cal\View\Module;

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
use TYPO3\CMS\Cal\Service\AbstractModul;
use TYPO3\CMS\Cal\Utility\Functions;

/**
 *
 */
class LocationLoader extends AbstractModul
{

    /**
     * The function adds location markers into the event template
     *
     * @param Object $moduleCaller
     *        	Instance of the event model (phpicalendar_model)
     */
    public function start(&$moduleCaller, $onlyMarker = false)
    {
        if ($moduleCaller->getLocationId() > 0) {
            $this->modelObj = &\TYPO3\CMS\Cal\Utility\Registry::Registry('basic', 'modelcontroller');
            $this->cObj = &\TYPO3\CMS\Cal\Utility\Registry::Registry('basic', 'cobj');

            $moduleCaller->confArr = unserialize($GLOBALS ['TYPO3_CONF_VARS'] ['EXT'] ['extConf'] ['cal']);
            $useLocationStructure = ($moduleCaller->confArr ['useLocationStructure'] ? $moduleCaller->confArr ['useLocationStructure'] : 'tx_cal_location');
            $location = $this->modelObj->findLocation($moduleCaller->getLocationId(), $useLocationStructure);
            if (is_object($location)) {
                $page = Functions::getContent($moduleCaller->conf ['module.'] ['locationloader.'] ['template']);
                if ($page == '') {
                    return '<h3>module locationloader: no template file found:</h3>' . $moduleCaller->conf ['module.'] ['locationloader.'] ['template'];
                }
                $sims = [];
                $rems = [];
                $wrapped = [];
                $location->getMarker($page, $sims, $rems, $wrapped);
                if ($onlyMarker) {
                    return $sims;
                }
                return \TYPO3\CMS\Cal\Utility\Functions::substituteMarkerArrayNotCached($page, $sims, $rems, []);
            }
        }
        return '';
    }
}
