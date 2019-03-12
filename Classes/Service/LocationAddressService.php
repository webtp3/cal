<?php

/*
 * This file is part of the web-tp3/cal.
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace TYPO3\CMS\Cal\Service;

use RuntimeException;
use TYPO3\CMS\Cal\Model\LocationAddress;
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
 * Base model for the calendar location.
 * Provides basic model functionality that other
 * models can use or override by extending the class.
 */
class LocationAddressService extends BaseService
{
    public $keyId = 'tx_tt_address';
    public $tableId = 'tt_address';

    /**
     * Looks for an location with a given uid on a certain pid-list
     *
     * @param int $uid
     * @param string $pidList
     * @return OrganizerPartnerService tx_cal_organizer_partner object
     */
    public function find($uid, $pidList): OrganizerPartnerService
    {
        $locationArray = $this->getLocationFromTable($pidList, ' AND ' . $this->tableId . '.uid=' . $uid);
        return $locationArray[0];
    }

    /**
     * Looks for an location with a given uid on a certain pid-list
     *
     * @param string $pidList
     * @return array tx_cal_location_address object array
     */
    public function findAll($pidList): array
    {
        return $this->getLocationFromTable($pidList);
    }

    /**
     * Search for location
     *
     * @param string $pidList
     * @param string $searchword
     * @return array containing the location objects
     */
    public function search($pidList, $searchword): array
    {
        return $this->getLocationFromTable($pidList, $this->searchWhere($searchword));
    }

    /**
     * Generates the sql query and builds location objects out of the result rows
     *
     * @param string $pidList
     * @param string $additionalWhere
     * @return array containing the location objects
     */
    public function getLocationFromTable($pidList, $additionalWhere = ''): array
    {
        $locations = [];
        if ($pidList !== '') {
            $additionalWhere .= ' AND ' . $this->tableId . '.pid IN (' . $pidList . ')';
        }
        $additionalWhere .= $this->getAdditionalWhereForLocalizationAndVersioning($this->tableId);
        $select = '*';
        $table = $this->tableId;
        $where = 'tx_cal_controller_islocation = 1 AND l18n_parent = 0 ' . $additionalWhere . $this->cObj->enableFields($this->tableId);
        $groupBy = '';
        $orderBy = Functions::getOrderBy($this->tableId);
        $limit = '';

        $hookObjectsArr = Functions::getHookObjectsArray(
            'tx_cal_location_address_service',
            'locationServiceClass',
            'service'
        );

        foreach ($hookObjectsArr as $hookObj) {
            if (method_exists($hookObj, 'preGetLocationFromTableExec')) {
                $hookObj->preGetLocationFromTableExec($this, $select, $table, $where, $groupBy, $orderBy, $limit);
            }
        }

        $result = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select, $table, $where, $groupBy, $orderBy, $limit);

        if ($result) {
            while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
                $locations[] = new LocationAddress($row, $pidList);
            }
            $GLOBALS['TYPO3_DB']->sql_free_result($result);
        }
        return $locations;
    }

    /**
     * Generates a search where clause.
     *
     * @param string $sw :
     * @return string
     */
    public function searchWhere($sw): string
    {
        $where = $this->cObj->searchWhere(
            $sw,
            $this->conf['view.']['search.']['searchLocationFieldList'],
            'tt_address'
        );
        return $where;
    }

    /**
     * @param $uid
     * @return object
     */
    public function updateLocation($uid)
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
    public function removeLocation($uid)
    {
        if ($this->rightsObj->isAllowedToDeleteLocation()) {
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
        if ($this->controller->piVars['hidden'] === 'true' && ($this->rightsObj->isAllowedToEditLocationHidden() || $this->rightsObj->isAllowedToCreateLocationHidden())) {
            $hidden = 1;
        }
        $insertFields['hidden'] = $hidden;

        if ($this->rightsObj->isAllowedToEditLocationName() || $this->rightsObj->isAllowedToCreateLocationName()) {
            $insertFields['name'] = strip_tags($this->controller->piVars['name']);
        }

        if ($this->rightsObj->isAllowedToEditLocationDescription() || $this->rightsObj->isAllowedToCreateLocationDescription()) {
            $insertFields['description'] = htmlspecialchars($this->controller->piVars['description']);
        }

        if ($this->rightsObj->isAllowedToEditLocationStreet() || $this->rightsObj->isAllowedToCreateLocationStreet()) {
            $insertFields['address'] = strip_tags($this->controller->piVars['street']);
        }

        if ($this->rightsObj->isAllowedToEditLocationZip() || $this->rightsObj->isAllowedToCreateLocationZip()) {
            $insertFields['zip'] = strip_tags($this->controller->piVars['zip']);
        }

        if ($this->rightsObj->isAllowedToEditLocationCity() || $this->rightsObj->isAllowedToCreateLocationCity()) {
            $insertFields['city'] = strip_tags($this->controller->piVars['city']);
        }

        if ($this->rightsObj->isAllowedToEditLocationCountry() || $this->rightsObj->isAllowedToCreateLocationCountry()) {
            $insertFields['country'] = strip_tags($this->controller->piVars['country']);
        }

        if ($this->rightsObj->isAllowedToEditLocationPhone() || $this->rightsObj->isAllowedToCreateLocationPhone()) {
            $insertFields['phone'] = strip_tags($this->controller->piVars['phone']);
        }

        if ($this->rightsObj->isAllowedToEditLocationEmail() || $this->rightsObj->isAllowedToCreateLocationEmail()) {
            $insertFields['email'] = strip_tags($this->controller->piVars['email']);
        }

        if ($this->rightsObj->isAllowedToEditLocationImage() || $this->rightsObj->isAllowedToCreateLocationImage()) {
            $insertFields['image'] = strip_tags($this->controller->piVars['image']);
        }

        if ($this->rightsObj->isAllowedToEditLocationLink() || $this->rightsObj->isAllowedToCreateLocationLink()) {
            $insertFields['www'] = strip_tags($this->controller->piVars['link']);
        }
    }

    /**
     * @param $pid
     * @return object
     */
    public function saveLocation($pid)
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
        if ($this->controller->piVars['country'] !== '') {
            $insertFields['country'] = strip_tags($this->controller->piVars['country']);
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
        $uid = $this->_saveLocation($insertFields);
        return $this->find($uid, $this->conf['pidList']);
    }

    /**
     * @param $insertFields
     * @return mixed
     */
    public function _saveLocation(&$insertFields)
    {
        $table = 'tt_address';
        $result = $GLOBALS['TYPO3_DB']->exec_INSERTquery($table, $insertFields);
        if (false === $result) {
            throw new RuntimeException(
                'Could not write ' . $table . ' record to database: ' . $GLOBALS['TYPO3_DB']->sql_error(),
                1431458151
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
        $useLocationStructure = ($confArr['useLocationStructure'] ?: 'tx_cal_location');
        return $useLocationStructure === $this->keyId;
    }

    /**
     * @param $uid
     * @param $overlay
     * @deprecated since ext:cal v2, will be removed in ext:cal v3
     */
    public function createTranslation($uid, $overlay)
    {
        trigger_error('Deprecated since ext:cal v2, will be removed in ext:cal v3.', E_USER_DEPRECATED);

        $table = 'tt_address';
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
                $this->_saveLocation($row);
            }
            $GLOBALS['TYPO3_DB']->sql_free_result($result);
        }
    }
}
