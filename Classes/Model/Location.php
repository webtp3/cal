<?php

namespace TYPO3\CMS\Cal\Model;

use TYPO3\CMS\Cal\Utility\Registry;

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
 * Base model for the calendar location.
 * Provides basic model functionality that other
 * models can use or override by extending the class.
 */
class Location extends LocationModel
{

    /**
     * Constructor
     *
     * @param array $row
     * @param string $pidList
     */
    public function __construct($row, $pidList)
    {
        $this->setObjectType('location');
        $this->setType('tx_cal_location');
        parent::__construct($this->controller, $this->getType());
        $this->createLocation($row);
        $this->templatePath = $this->conf['view.']['location.']['locationModelTemplate'];
    }

    /**
     * @return string
     */
    public function renderLocation(): string
    {
        return $this->fillTemplate('###TEMPLATE_LOCATION_LOCATION###');
    }

    /**
     * @return string
     */
    public function renderOrganizer(): string
    {
        return $this->fillTemplate('###TEMPLATE_ORGANIZER_ORGANIZER###');
    }

    /**
     * @param string $feUserUid
     * @param array $feGroupsArray
     * @return bool
     */
    public function isUserAllowedToEdit($feUserUid = '', $feGroupsArray = []): bool
    {
        $rightsObj = &Registry::Registry('basic', 'rightscontroller');
        if (!$rightsObj->isViewEnabled('edit_location')) {
            return false;
        }
        if ($rightsObj->isCalAdmin()) {
            return true;
        }

        if ($feUserUid === '') {
            $feUserUid = $rightsObj->getUserId();
        }
        if (empty($feGroupsArray)) {
            $feGroupsArray = $rightsObj->getUserGroups();
        }

        $isSharedUser = $this->isSharedUser($feUserUid, $feGroupsArray);
        $isAllowedToEditLocations = $rightsObj->isAllowedToEditLocation();
        $isAllowedToEditOwnLocationsOnly = $rightsObj->isAllowedToEditOnlyOwnLocation();

        if ($isAllowedToEditOwnLocationsOnly) {
            return $isSharedUser;
        }
        return $isAllowedToEditLocations;
    }

    /**
     * @param string $feUserUid
     * @param array $feGroupsArray
     * @return bool
     */
    public function isUserAllowedToDelete($feUserUid = '', $feGroupsArray = []): bool
    {
        $rightsObj = &Registry::Registry('basic', 'rightscontroller');
        if (!$rightsObj->isViewEnabled('delete_location')) {
            return false;
        }
        if ($rightsObj->isCalAdmin()) {
            return true;
        }

        if ($feUserUid === '') {
            $feUserUid = $rightsObj->getUserId();
        }
        if (empty($feGroupsArray)) {
            $feGroupsArray = $rightsObj->getUserGroups();
        }
        $isSharedUser = $this->isSharedUser($feUserUid, $feGroupsArray);
        $isAllowedToDeleteLocation = $rightsObj->isAllowedToDeleteLocation();
        $isAllowedToDeleteOwnLocationsOnly = $rightsObj->isAllowedToDeleteOnlyOwnLocation();

        if ($isAllowedToDeleteOwnLocationsOnly) {
            return $isSharedUser;
        }
        return $isAllowedToDeleteLocation;
    }

    /**
     * @param $template
     * @param $sims
     * @param $rems
     * @param $wrapped
     * @param $view
     * @return string
     */
    public function getEditLinkMarker(& $template, & $sims, & $rems, & $wrapped, $view): string
    {
        $editlink = '';
        if ($this->isUserAllowedToEdit()) {
            $this->initLocalCObject($this->getValuesAsArray());

            $this->local_cObj->setCurrentVal($this->controller->pi_getLL('l_edit_location'));
            $this->controller->getParametersForTyposcriptLink(
                $this->local_cObj->data,
                [
                    'view' => 'edit_location',
                    'type' => $this->getType(),
                    'uid' => $this->getUid()
                ],
                $this->conf['cache'],
                $this->conf['clear_anyway'],
                $this->conf['view.']['calendar.']['editLocationViewPid']
            );
            $editlink = $this->local_cObj->cObjGetSingle(
                $this->conf['view.'][$view . '.']['location.']['editLink'],
                $this->conf['view.'][$view . '.']['location.']['editLink.']
            );
        }
        if ($this->isUserAllowedToDelete()) {
            $this->initLocalCObject($this->getValuesAsArray());

            $this->local_cObj->setCurrentVal($this->controller->pi_getLL('l_delete_location'));
            $this->controller->getParametersForTyposcriptLink(
                $this->local_cObj->data,
                [
                    'view' => 'delete_location',
                    'type' => $this->getType(),
                    'uid' => $this->getUid()
                ],
                $this->conf['cache'],
                $this->conf['clear_anyway'],
                $this->conf['view.']['location.']['deleteLocationViewPid']
            );
            $editlink .= $this->local_cObj->cObjGetSingle(
                $this->conf['view.'][$view . '.']['location.']['deleteLink'],
                $this->conf['view.'][$view . '.']['location.']['deleteLink.']
            );
        }
        return $editlink;
    }

    /**
     * @param $viewType
     * @param string $subpartSuffix
     * @return string
     */
    public function renderLocationFor($viewType, $subpartSuffix = ''): string
    {
        return $this->fillTemplate('###TEMPLATE_LOCATION_' . strtoupper($viewType) . ($subpartSuffix ? '_' : '') . $subpartSuffix . '###');
    }

    /**
     * @param $viewType
     * @param string $subpartSuffix
     * @return string
     */
    public function renderOrganizerFor($viewType, $subpartSuffix = ''): string
    {
        return $this->fillTemplate('###TEMPLATE_ORGANIZER_' . strtoupper($viewType) . ($subpartSuffix ? '_' : '') . $subpartSuffix . '###');
    }
}
