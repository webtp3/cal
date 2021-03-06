<?php

/*
 * This file is part of the web-tp3/cal.
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace TYPO3\CMS\Cal\Hooks;

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
use TYPO3\CMS\Core\Utility\GeneralUtility;

class WecMap
{

    /**
     * Hook to post process map markers for Calendar Base locations.
     *
     * @param
     *        	array		Main parameters. 'table' contains the table name,
     *        	'data' contains the current row, and 'markerObj'
     *        	contains the marker object
     */
    public function getMarkerContent(&$params)
    {
        $table = $params ['table'];
        $data = $params ['data'];
        $markerObj = $params ['markerObj'];

        $locationStructure = $this->confArr ['useLocationStructure'] ? $this->confArr ['useLocationStructure'] : 'tt_address';

        if ($table == $locationStructure && is_object($markerObj)) {
            $tx_cal_api = GeneralUtility::makeInstance('TYPO3\\CMS\\Cal\\Controller\\Api');

            $cObj = new \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer();
            $conf = $GLOBALS ['TSFE']->tmpl->setup ['plugin.'] ['tx_cal_controller.'];
            $conf ['view.'] ['allowedViews'] = 'location';

            $tx_cal_api = &$tx_cal_api->tx_cal_api_with($cObj, $conf);
            $tx_cal_api->modelObj = GeneralUtility::makeInstance(\TYPO3\CMS\Cal\Controller\ModelController::class);

            $location = $tx_cal_api->modelObj->findLocation($data ['uid'], 'tx_tt_address', $data ['pid']);

            if (is_object($location)) {
                $events =  $tx_cal_api->controller->findRelatedEvents('location', ' AND location_id = ' . $location->getUid());
                // $events = array_slice((array) $location->getEventLinks(), 0, 8);
                $eventsHTMLArray = [];

                foreach ($events as $eventTimeArray) {
                    foreach ($eventTimeArray as $eventArray) {
                        foreach ($eventArray as $event) {
                            $eventsHTMLArray [] = $event->getLinkToEvent($event->getTitle(), 'loaction', $event->getStart()->format('Ymd'));
                        }
                    }
                }

                $tabLabel = '%%%events%%%';
                $tx_cal_api->controller->translateLanguageMarker($tabLabel);

                $eventsHTMLArray = array_slice($eventsHTMLArray, 0, 8);
                $eventsHTML = $this->stripNL(implode('', $eventsHTMLArray));
                $markerObj->addTab('+' . $tabLabel, '', $eventsHTML);
            }
        }
    }

    /**
     * strip newlines
     *
     * @access private
     * @param
     *        	string		The input string to filtered.
     * @return string converted string.
     */
    public function stripNL($input)
    {
        $order = [
                "\r\n",
                "\n",
                "\r"
        ];
        $replace = '';
        $newstr = str_replace($order, $replace, $input);

        return $newstr;
    }
}
