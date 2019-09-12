<?php

namespace TYPO3\CMS\Cal\Service;

use RuntimeException;
use TYPO3\CMS\Cal\Model\OrganizerAddress;
use TYPO3\CMS\Cal\Utility\Functions;

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
class OrganizerAddressService extends BaseService
{
    public $keyId = 'tx_tt_address';
    public $tableId = 'tt_address';

    /**
     * Looks for an organizer with a given uid on a certain pid-list
     *
     * @param int $uid
     * @param string $pidList
     * @return object tx_cal_organizer_partner object
     */
    public function find($uid, $pidList)
    {
        $organizerArray = $this->getOrganizerFromTable($pidList, ' AND ' . $this->tableId . '.uid=' . $uid);
        return $organizerArray[0];
    }

    /**
     * Looks for an organizer with a given uid on a certain pid-list
     *
     * @param string $pidList
     * @return array tx_cal_organizer_partner object array
     */
    public function findAll($pidList): array
    {
        return $this->getOrganizerFromTable($pidList);
    }

    /**
     * Search for organizer
     *
     * @param string $pidList
     * @param string $searchword
     * @return array containing the organizer objects
     */
    public function search($pidList, $searchword): array
    {
        return $this->getOrganizerFromTable($pidList, $this->searchWhere($searchword));
    }

    /**
     * Generates the sql query and builds organizer objects out of the result rows
     *
     * @param string $pidList
     * @param string $additionalWhere
     * @return array containing the organizer objects
     */
    public function getOrganizerFromTable($pidList, $additionalWhere = ''): array
    {
        $organizers = [];
        if ($pidList !== '') {
            $additionalWhere .= ' AND ' . $this->tableId . '.pid IN (' . $pidList . ')';
        }
        $additionalWhere .= $this->getAdditionalWhereForLocalizationAndVersioning($this->tableId);
        $select = '*';
        $table = $this->tableId;
        $where = 'tx_cal_controller_isorganizer = 1 AND l18n_parent = 0 ' . $additionalWhere . $this->cObj->enableFields($this->tableId);
        $groupBy = '';
        $orderBy = Functions::getOrderBy($this->tableId);
        $limit = '';

        $hookObjectsArr = Functions::getHookObjectsArray(
            'tx_cal_organizer_address_service',
            'organizerServiceClass',
            'service'
        );

        foreach ($hookObjectsArr as $hookObj) {
            if (method_exists($hookObj, 'preGetOrganizerFromTableExec')) {
                $hookObj->preGetLocationFromTableExec($this, $select, $table, $where, $groupBy, $orderBy, $limit);
            }
        }

        $result = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select, $table, $where, $groupBy, $orderBy, $limit);

        if ($result) {
            while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
                $organizers[] = new OrganizerAddress($row, $pidList);
            }
            $GLOBALS['TYPO3_DB']->sql_free_result($result);
        }
        return $organizers;
    }

    /**
     * Generates a search where clause.
     *
     * @param string $sw
     * @return string
     */
    public function searchWhere($sw): string
    {
        $where = $this->cObj->searchWhere(
            $sw,
            $this->conf['view.']['search.']['searchOrganizerFieldList'],
            'tt_address'
        );
        return $where;
    }

    /**
     * @param $uid
     * @return object
     */
    public function updateOrganizer($uid)
    {
        $insertFields = [
            'tstamp' => time()
        ];
        // TODO: Check if all values are correct

        $this->retrievePostData($insertFields);
        $uid = self::checkUidForLanguageOverlay($uid, 'tt_address');
        // Creating DB records
        $table = $this->tableId;
        $where = 'uid = ' . $uid;
        $GLOBALS['TYPO3_DB']->exec_UPDATEquery($table, $where, $insertFields);
        return $this->find($uid, $this->conf['pidList']);
    }

    /**
     * @param $uid
     */
    public function removeOrganizer($uid)
    {
        if ($this->rightsObj->isAllowedToDeleteOrganizer()) {
            $updateFields = [
                'tstamp' => time(),
                'deleted' => 1
            ];
            $table = $this->tableId;
            $where = 'uid = ' . $uid;
            $GLOBALS['TYPO3_DB']->exec_UPDATEquery($table, $where, $updateFields);
        }
    }

    /**
     * @param $insertFields
     */
    public function retrievePostData(&$insertFields)
    {
        $hidden = 0;
        if ($this->controller->piVars['hidden'] === 'true' && ($this->rightsObj->isAllowedToEditOrganizerHidden() || $this->rightsObj->isAllowedToCreateOrganizerHidden())) {
            $hidden = 1;
        }
        $insertFields['hidden'] = $hidden;

        if ($this->rightsObj->isAllowedToEditOrganizerName() || $this->rightsObj->isAllowedToCreateOrganizerName()) {
            $insertFields['name'] = strip_tags($this->controller->piVars['name']);
        }

        if ($this->rightsObj->isAllowedToEditOrganizerDescription() || $this->rightsObj->isAllowedToCreateOrganizerDescription()) {
            $insertFields['description'] = htmlspecialchars(
                $this->controller->piVars['description'],
                $this->conf
            );
        }

        if ($this->rightsObj->isAllowedToEditOrganizerStreet() || $this->rightsObj->isAllowedToCreateOrganizerStreet()) {
            $insertFields['address'] = strip_tags($this->controller->piVars['street']);
        }

        if ($this->rightsObj->isAllowedToEditOrganizerZip() || $this->rightsObj->isAllowedToCreateOrganizerZip()) {
            $insertFields['zip'] = strip_tags($this->controller->piVars['zip']);
        }

        if ($this->rightsObj->isAllowedToEditOrganizerCity() || $this->rightsObj->isAllowedToCreateOrganizerCity()) {
            $insertFields['city'] = strip_tags($this->controller->piVars['city']);
        }

        if ($this->rightsObj->isAllowedToEditOrganizerPhone() || $this->rightsObj->isAllowedToCreateOrganizerPhone()) {
            $insertFields['phone'] = strip_tags($this->controller->piVars['phone']);
        }

        if ($this->rightsObj->isAllowedToEditOrganizerEmail() || $this->rightsObj->isAllowedToCreateOrganizerEmail()) {
            $insertFields['email'] = strip_tags($this->controller->piVars['email']);
        }

        if ($this->rightsObj->isAllowedToEditOrganizerImage() || $this->rightsObj->isAllowedToCreateOrganizerImage()) {
            $insertFields['image'] = strip_tags($this->controller->piVars['image']);
        }

        if ($this->rightsObj->isAllowedToEditOrganizerLink() || $this->rightsObj->isAllowedToCreateOrganizerLink()) {
            $insertFields['www'] = strip_tags($this->controller->piVars['link']);
        }
    }

    /**
     * @param $pid
     * @return object
     */
    public function saveOrganizer($pid)
    {
        $crdate = time();
        $insertFields = [
            'pid' => $pid,
            'tstamp' => $crdate,
            'crdate' => $crdate
        ];
        // TODO: Check if all values are correct

        $hidden = 0;
        if ($this->controller->piVars['hidden'] === 'true') {
            $hidden = 1;
        }
        $insertFields['hidden'] = $hidden;
        if ($this->controller->piVars['name'] !== '') {
            $insertFields['name'] = strip_tags($this->controller->piVars['name']);
        }
        if ($this->controller->piVars['description'] !== '') {
            $insertFields['description'] = htmlspecialchars($this->controller->piVars['description']);
        }
        if ($this->controller->piVars['street'] !== '') {
            $insertFields['address'] = strip_tags($this->controller->piVars['street']);
        }
        if ($this->controller->piVars['zip'] !== '') {
            $insertFields['zip'] = strip_tags($this->controller->piVars['zip']);
        }
        if ($this->controller->piVars['city'] !== '') {
            $insertFields['city'] = strip_tags($this->controller->piVars['city']);
        }
        if ($this->controller->piVars['phone'] !== '') {
            $insertFields['phone'] = strip_tags($this->controller->piVars['phone']);
        }
        if ($this->controller->piVars['email'] !== '') {
            $insertFields['email'] = strip_tags($this->controller->piVars['email']);
        }
        if ($this->controller->piVars['image'] !== '') {
            $insertFields['image'] = strip_tags($this->controller->piVars['image']);
        }
        if ($this->controller->piVars['link'] !== '') {
            $insertFields['www'] = strip_tags($this->controller->piVars['link']);
        }

        // Creating DB records
        $insertFields['cruser_id'] = $this->rightsObj->getUserId();
        $uid = $this->_saveOrganizer($insertFields);
        return $this->find($uid, $this->conf['pidList']);
    }

    /**
     * @param $insertFields
     * @return mixed
     */
    public function _saveOrganizer(&$insertFields)
    {
        $table = $this->tableId;
        $result = $GLOBALS['TYPO3_DB']->exec_INSERTquery($table, $insertFields);
        if (false === $result) {
            throw new RuntimeException(
                'Could not write ' . $table . ' record to database: ' . $GLOBALS['TYPO3_DB']->sql_error(),
                1431458154
            );
        }
        $uid = $GLOBALS['TYPO3_DB']->sql_insert_id();
        return $uid;
    }

    /**
     * @return bool
     */
    public function isAllowedService(): bool
    {
        $confArr = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['cal']);
        $useOrganizerStructure = ($confArr['useOrganizerStructure'] ?: 'tx_cal_organizer');
        return $useOrganizerStructure === $this->keyId;
    }

    /**
     * @param $uid
     * @param $overlay
     * @deprecated since ext:cal v2, will be removed in ext:cal v3
     */
    public function createTranslation($uid, $overlay)
    {
       //trigger_error('Deprecated since ext:cal v2, will be removed in ext:cal v3.', E_USER_DEPRECATED);

        $table = $this->tableId;
        $select = $table . '.*';
        $where = $table . '.uid = ' . $uid;
        $result = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select, $table, $where);
        if ($result) {
            $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result);
            if (is_array($row)) {
                unset($row['uid']);
                $crdate = time();
                $row['tstamp'] = $crdate;
                $row['crdate'] = $crdate;
                $row['l18n_parent'] = $uid;
                $row['sys_language_uid'] = $overlay;
                $this->_saveOrganizer($row);
            }
            $GLOBALS['TYPO3_DB']->sql_free_result($result);
        }
    }
}
