<?php

/*
 * This file is part of the web-tp3/cal.
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

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
 * Base model for the calendar organizer.
 * Provides basic model functionality that other
 * models can use or override by extending the class.
 */
class Organizer extends LocationModel
{

    /**
     * Constructor
     *
     * @param array $row
     * @param string $pidList
     * @deprecated since ext:cal v2, will be removed in ext:cal v3
     */
    public function __construct($row, $pidList)
    {
        trigger_error('Deprecated since ext:cal v2, will be removed in ext:cal v3.', E_USER_DEPRECATED);

        $this->setObjectType('organizer');
        $this->setType('tx_cal_organizer');
        parent::__construct($this->getType());
        $this->createOrganizer($row);
        $this->templatePath = $this->conf['view.']['organizer.']['organizerModelTemplate'];
    }

    /**
     * @param $row
     * @deprecated since ext:cal v2, will be removed in ext:cal v3
     */
    public function createOrganizer($row)
    {
        trigger_error('Deprecated since ext:cal v2, will be removed in ext:cal v3.', E_USER_DEPRECATED);

        $this->createLocation($row);
    }

    /**
     * @return string
     * @deprecated since ext:cal v2, will be removed in ext:cal v3
     */
    public function renderOrganizer(): string
    {
        trigger_error('Deprecated since ext:cal v2, will be removed in ext:cal v3.', E_USER_DEPRECATED);

        return $this->fillTemplate('###TEMPLATE_ORGANIZER_ORGANIZER###');
    }

    /**
     * @param string $feUserUid
     * @param array $feGroupsArray
     * @return bool
     * @deprecated since ext:cal v2, will be removed in ext:cal v3
     */
    public function isUserAllowedToEdit($feUserUid = '', $feGroupsArray = []): bool
    {
        trigger_error('Deprecated since ext:cal v2, will be removed in ext:cal v3.', E_USER_DEPRECATED);

        $rightsObj = &Registry::Registry('basic', 'rightscontroller');
        if (!$rightsObj->isViewEnabled('edit_organizer')) {
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
        $isAllowedToEditOrganizers = $rightsObj->isAllowedToEditOrganizer();
        $isAllowedToEditOwnOrganizersOnly = $rightsObj->isAllowedToEditOnlyOwnOrganizer();

        if ($isAllowedToEditOwnOrganizersOnly) {
            return $isSharedUser;
        }
        return $isAllowedToEditOrganizers;
    }

    /**
     * @param string $feUserUid
     * @param array $feGroupsArray
     * @return bool
     * @deprecated since ext:cal v2, will be removed in ext:cal v3
     */
    public function isUserAllowedToDelete($feUserUid = '', $feGroupsArray = []): bool
    {
        trigger_error('Deprecated since ext:cal v2, will be removed in ext:cal v3.', E_USER_DEPRECATED);

        $rightsObj = &Registry::Registry('basic', 'rightscontroller');
        if (!$rightsObj->isViewEnabled('delete_organizer')) {
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
        $isAllowedToDeleteOrganizers = $rightsObj->isAllowedToDeleteOrganizer();
        $isAllowedToDeleteOwnOrganizersOnly = $rightsObj->isAllowedToDeleteOnlyOwnOrganizer();

        if ($isAllowedToDeleteOwnOrganizersOnly) {
            return $isSharedUser;
        }
        return $isAllowedToDeleteOrganizers;
    }

    /**
     * @param $template
     * @param $sims
     * @param $rems
     * @param $view
     * @return string
     * @deprecated since ext:cal v2, will be removed in ext:cal v3
     */
    public function getEditLink(& $template, & $sims, & $rems, $view): string
    {
        trigger_error('Deprecated since ext:cal v2, will be removed in ext:cal v3.', E_USER_DEPRECATED);

        $editlink = '';
        if ($this->isUserAllowedToEdit()) {
            $this->initLocalCObject($this->getValuesAsArray());
            $this->local_cObj->setCurrentVal($this->controller->pi_getLL('l_edit_organizer'));
            $this->controller->getParametersForTyposcriptLink(
                $this->local_cObj->data,
                [
                    'view' => 'edit_organizer',
                    'type' => $this->getType(),
                    'uid' => $this->getUid()
                ],
                $this->conf['cache'],
                $this->conf['clear_anyway'],
                $this->conf['view.']['calendar.']['editOrganizerViewPid']
            );
            $editlink = $this->local_cObj->cObjGetSingle(
                $this->conf['view.'][$view . '.']['organizer.']['editLink'],
                $this->conf['view.'][$view . '.']['organizer.']['editLink.']
            );
        }
        if ($this->isUserAllowedToDelete()) {
            $this->initLocalCObject($this->getValuesAsArray());

            $this->local_cObj->setCurrentVal($this->controller->pi_getLL('l_delete_organizer'));
            $this->controller->getParametersForTyposcriptLink(
                $this->local_cObj->data,
                [
                    'view' => 'delete_organizer',
                    'type' => $this->getType(),
                    'uid' => $this->getUid()
                ],
                $this->conf['cache'],
                $this->conf['clear_anyway'],
                $this->conf['view.']['organizer.']['deleteOrganizerViewPid']
            );
            $editlink .= $this->local_cObj->cObjGetSingle(
                $this->conf['view.'][$view . '.']['organizer.']['deleteLink'],
                $this->conf['view.'][$view . '.']['organizer.']['deleteLink.']
            );
        }
        return $editlink;
    }

    /**
     * @param $viewType
     * @param string $subpartSuffix
     * @return string
     * @deprecated since ext:cal v2, will be removed in ext:cal v3
     */
    public function renderOrganizerFor($viewType, $subpartSuffix = ''): string
    {
        trigger_error('Deprecated since ext:cal v2, will be removed in ext:cal v3.', E_USER_DEPRECATED);

        return $this->fillTemplate('###TEMPLATE_ORGANIZER_' . strtoupper($viewType) . ($subpartSuffix ? '_' : '') . $subpartSuffix . '###');
    }
}
