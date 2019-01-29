<?php

namespace TYPO3\CMS\Cal\View;

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
use TYPO3\CMS\Cal\Model\LocationModel;
use TYPO3\CMS\Cal\Utility\Functions;

/**
 * A service which renders a form to create / edit a location or organizer.
 */
class DeleteLocationOrganizerView extends FeEditingBaseView
{
    public $isLocation = true;
    public $objectString = 'location';

    /**
     * Draws a delete form for a location or an organizer.
     *
     * @param bool True if a location should be deleted
     * @param LocationModel        The object to be deleted
     * @param object        The cObject of the mother-class.
     * @param object        The rights object.
     * @return string HTML output.
     */
    public function drawDeleteLocationOrOrganizer($isLocation, &$object): string
    {
        $page = Functions::getContent($this->conf['view.']['delete_location.']['template']);
        if ($page === '') {
            return '<h3>category: no delete location template file found:</h3>' . $this->conf['view.']['delete_location.']['template'];
        }

        $this->isLocation = $isLocation;
        $this->object = $object;
        if ($isLocation) {
            $this->objectString = 'location';
        } else {
            $this->objectString = 'organizer';
        }

        $rems = [];
        $sims = [];
        $wrapped = [];

        $sims['###UID###'] = $this->conf['uid'];
        $sims['###TYPE###'] = $this->conf['type'];
        $sims['###VIEW###'] = 'remove_' . $this->objectString;
        $sims['###LASTVIEW###'] = $this->controller->extendLastView();
        $sims['###L_DELETE_LOCATION###'] = $this->controller->pi_getLL('l_delete_' . $this->objectString);
        $sims['###L_DELETE###'] = $this->controller->pi_getLL('l_delete');
        $sims['###L_CANCEL###'] = $this->controller->pi_getLL('l_cancel');
        $sims['###ACTION_URL###'] = htmlspecialchars($this->controller->pi_linkTP_keepPIvars_url([
            'view' => 'remove_' . $this->objectString
        ]));
        $this->getTemplateSubpartMarker($page, $sims, $rems, $wrapped);
        $page = Functions::substituteMarkerArrayNotCached($page, [], $rems, []);
        $page = Functions::substituteMarkerArrayNotCached($page, $sims, [], []);
        $sims = [];
        $rems = [];
        $wrapped = [];
        $this->object->getMarker($page, $sims, $rems, $wrapped);

        return Functions::substituteMarkerArrayNotCached($page, $sims, $rems, $wrapped);
    }
}
