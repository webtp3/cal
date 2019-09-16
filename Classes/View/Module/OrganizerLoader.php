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
use TYPO3\CMS\Cal\Utility\Registry;

/**
 * Class OrganizerLoader
 */
class OrganizerLoader extends AbstractModul
{

    /**
     * The function adds organizer markers into the event template
     *
     * @param object $moduleCaller Instance of the event model (phpicalendar_model)
     * @param bool $onlyMarker
     * @return array|mixed|string
     */
    public function start(&$moduleCaller, $onlyMarker = false)
    {
        if ($moduleCaller->getOrganizerId() > 0) {
            $this->modelObj = &Registry::Registry('basic', 'modelcontroller');
            $this->cObj = &Registry::Registry('basic', 'cobj');

            $moduleCaller->confArr = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['cal']);
            $useOrganizerStructure = ($moduleCaller->confArr['useOrganizerStructure'] ?: 'tx_cal_organizer');
            $organizer = $this->modelObj->findOrganizer($moduleCaller->getOrganizerId(), $useOrganizerStructure);

            if (is_object($organizer)) {
                $page = Functions::getContent($moduleCaller->conf['module.']['organizerloader.']['template']);
                if ($page === '') {
                    return '<h3>module organizerloader: no template file found:</h3>' . $moduleCaller->conf['module.']['organizerloader.']['template'];
                }
                $sims = [];
                $rems = [];
                $wrapped = [];
                $organizer->getMarker($page, $sims, $rems, $wrapped);
                if ($onlyMarker) {
                    return $sims;
                }
                return Functions::substituteMarkerArrayNotCached($page, $sims, $rems, []);
            }
        }
        return '';
    }
}
