<?php

namespace TYPO3\CMS\Cal\Service;

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
use RuntimeException;
use TYPO3\CMS\Cal\Model\Location;
use TYPO3\CMS\Cal\Utility\Functions;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Base model for the calendar organizer.
 * Provides basic model functionality that other
 * models can use or override by extending the class.
 */
class LocationService extends BaseService
{
    public $keyId = 'tx_cal_location';

    /**
     * Looks for an organizer with a given uid on a certain pid-list
     *
     * @param int $uid
     *            to search for
     * @param string $pidList
     *            to search in
     * @return Location
     */
    public function find($uid, $pidList): Location
    {
        $locationArray = $this->getLocationFromTable($pidList, ' AND tx_cal_location.uid=' . $uid);
        return $locationArray[0];
    }

    /**
     * Looks for an organizer with a given uid on a certain pid-list
     *
     * @param string $pidList
     * @return array
     */
    public function findAll($pidList): array
    {
        return $this->getLocationFromTable($pidList);
    }

    /**
     * Search for locations
     *
     * @param string $pidList
     * @param string $searchword
     * @return array containing the location objects
     */
    public function search($pidList, $searchword): array
    {
        if (!$this->isAllowedService()) {
            return [];
        }
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
        $orderBy = Functions::getOrderBy('tx_cal_location');
        if ($pidList !== '') {
            $additionalWhere .= ' AND tx_cal_location.pid IN (' . $pidList . ')';
        }
        $additionalWhere .= $this->getAdditionalWhereForLocalizationAndVersioning('tx_cal_location');
        $table = 'tx_cal_location';
        $select = '*';
        $where = ' l18n_parent = 0 ' . $additionalWhere . $this->cObj->enableFields('tx_cal_location');
        $groupBy = '';
        $limit = '';

        $hookObjectsArr = Functions::getHookObjectsArray(
            'tx_cal_location_service',
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
                if ($GLOBALS['TSFE']->sys_language_content) {
                    $row = $GLOBALS['TSFE']->sys_page->getRecordOverlay(
                        'tx_cal_location',
                        $row,
                        $GLOBALS['TSFE']->sys_language_content,
                        $GLOBALS['TSFE']->sys_language_contentOL
                    );
                }
                if ($GLOBALS['TSFE']->sys_page->versioningPreview === true) {
                    // get workspaces Overlay
                    $GLOBALS['TSFE']->sys_page->versionOL('tx_cal_location', $row);
                }

                $lastLocation = new Location($row, $pidList);

                $select = 'uid_foreign,tablenames';
                $table = 'tx_cal_location_shared_user_mm';
                $where = 'uid_local = ' . $row['uid'];

                $sharedUserResult = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select, $table, $where);
                if ($sharedUserResult) {
                    while ($sharedUserRow = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($sharedUserResult)) {
                        if ($sharedUserRow['tablenames'] === 'fe_users') {
                            $lastLocation->addSharedUser($sharedUserRow['uid_foreign']);
                        } elseif ($sharedUserRow['tablenames'] === 'fe_groups') {
                            $lastLocation->addSharedGroup($sharedUserRow['uid_foreign']);
                        }
                    }
                    $GLOBALS['TYPO3_DB']->sql_free_result($sharedUserResult);
                }
                $locations[] = $lastLocation;
            }
            $GLOBALS['TYPO3_DB']->sql_free_result($result);
        }
        return $locations;
    }

    /**
     * @param $location
     * @param $event_uid
     */
    public function _addEventLinkToLocation(&$location, $event_uid)
    {
    }

    /**
     * Generates a search where clause.
     *
     * @param string $sw :
     * @return string
     */
    public function searchWhere($sw): string
    {
        if (!$this->isAllowedService()) {
            $where = '';
        } else {
            $where = $this->cObj->searchWhere(
                $sw,
                $this->conf['view.']['search.']['searchLocationFieldList'],
                'tx_cal_location'
            );
        }
        return $where;
    }

    /**
     * @param $uid
     * @return Location
     */
    public function updateLocation($uid): Location
    {
        $insertFields = [
            'tstamp' => time()
        ];
        // TODO: Check if all values are correct
        $this->searchForAdditionalFieldsToAddFromPostData($insertFields, 'location', false);
        $this->retrievePostData($insertFields);

        if ($this->rightsObj->isAllowedTo('edit', 'location', 'image')) {
            $this->checkOnNewOrDeletableFiles('tx_cal_location', 'image', $insertFields, $uid);
        }

        $sharedGroups = [];
        $sharedUsers = [];
        $values = $this->controller->piVars['shared_ids'];
        if (!is_array($this->controller->piVars['shared_ids'])) {
            $values = GeneralUtility::trimExplode(',', $this->controller->piVars['shared_ids'], 1);
        }
        foreach ($values as $entry) {
            preg_match('/(^[a-z])_([0-9]+)/', $entry, $idname);
            if ($idname[1] === 'u') {
                $sharedUsers[] = $idname[2];
            } elseif ($idname[1] === 'g') {
                $sharedGroups[] = $idname[2];
            }
        }
        if ($this->rightsObj->isAllowedTo('edit', 'location', 'shared')) {
            $GLOBALS['TYPO3_DB']->exec_DELETEquery('tx_cal_location_shared_user_mm', 'uid_local =' . $uid);
            self::insertIdsIntoTableWithMMRelation(
                'tx_cal_location_shared_user_mm',
                array_unique($sharedUsers),
                $uid,
                'fe_users'
            );
            self::insertIdsIntoTableWithMMRelation(
                'tx_cal_location_shared_user_mm',
                array_unique($sharedGroups),
                $uid,
                'fe_groups'
            );
            if (count($sharedUsers) > 0 || count($sharedGroups) > 0) {
                $insertFields['shared_user_cnt'] = 1;
            } else {
                $insertFields['shared_user_cnt'] = 0;
            }
        } else {
            $userIdArray = GeneralUtility::trimExplode(
                ',',
                $this->conf['rights.']['edit.']['location.']['fields.']['shared.']['defaultUser'],
                1
            );
            if ($this->conf['rights.']['edit.']['location.']['addFeUserToShared']) {
                $userIdArray[] = $this->rightsObj->getUserId();
            }

            $groupIdArray = GeneralUtility::trimExplode(
                ',',
                $this->conf['rights.']['edit.']['location.']['fields.']['shared.']['defaultGroup'],
                1
            );
            if ($this->conf['rights.']['edit.']['location.']['addFeGroupToShared']) {
                $groupIdArray = $this->rightsObj->getUserGroups();
                $ignore = GeneralUtility::trimExplode(
                    ',',
                    $this->conf['rights.']['edit.']['location.']['addFeGroupToShared.']['ignore'],
                    1
                );
                $groupIdArray = array_diff($groupIdArray, $ignore);
            }
            if (!empty($userIdArray) || !empty($groupIdArray)) {
                $GLOBALS['TYPO3_DB']->exec_DELETEquery('tx_cal_location_shared_user_mm', 'uid_local =' . $uid);
                self::insertIdsIntoTableWithMMRelation(
                    'tx_cal_location_shared_user_mm',
                    array_unique($userIdArray),
                    $uid,
                    'fe_users'
                );
                self::insertIdsIntoTableWithMMRelation(
                    'tx_cal_location_shared_user_mm',
                    array_unique($groupIdArray),
                    $uid,
                    'fe_groups'
                );
            }
            if (count($userIdArray) > 0 || count($groupIdArray) > 0) {
                $insertFields['shared_user_cnt'] = 1;
            } else {
                $insertFields['shared_user_cnt'] = 0;
            }
        }

        $uid = self::checkUidForLanguageOverlay($uid, 'tx_cal_location');
        // Creating DB records
        $table = 'tx_cal_location';
        $where = 'uid = ' . $uid;
        $GLOBALS['TYPO3_DB']->exec_UPDATEquery($table, $where, $insertFields);
        $this->unsetPiVars();
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
            $table = 'tx_cal_location';
            $where = 'uid = ' . $uid;
            $GLOBALS['TYPO3_DB']->exec_UPDATEquery($table, $where, $updateFields);
        }
        $this->unsetPiVars();
    }

    /**
     * @param $insertFields
     */
    public function retrievePostData(&$insertFields)
    {
        $hidden = 0;
        if (
            !$this->rightsObj->isAllowedTo('create', 'location', 'hidden')
            && $this->conf['rights.']['create.']['location.']['fields.']['hidden.']['default']
        ) {
            $hidden = $this->conf['rights.']['create.']['location.']['fields.']['hidden.']['default'];
        } elseif (
            !$this->rightsObj->isAllowedTo('edit', 'location', 'hidden')
            && $this->conf['rights.']['edit.']['location.']['fields.']['hidden.']['default']
        ) {
            $hidden = $this->conf['rights.']['location.']['event.']['fields.']['hidden.']['default'];
        } elseif ($this->controller->piVars['hidden'] === 'true' && ($this->rightsObj->isAllowedTo(
            'edit',
            'location',
            'hidden'
                ) || $this->rightsObj->isAllowedTo('create', 'location', 'hidden'))) {
            $hidden = 1;
        }
        $insertFields['hidden'] = $hidden;

        if ($this->rightsObj->isAllowedToEditLocationName() || $this->rightsObj->isAllowedToCreateLocationName()) {
            $insertFields['name'] = strip_tags($this->controller->piVars['name']);
        }

        if ($this->rightsObj->isAllowedToEditLocationDescription() || $this->rightsObj->isAllowedToCreateLocationDescription()) {
            $insertFields['description'] = htmlspecialchars(
                $this->controller->piVars['description'],
                $this->conf
            );
        }

        if ($this->rightsObj->isAllowedToEditLocationStreet() || $this->rightsObj->isAllowedToCreateLocationStreet()) {
            $insertFields['street'] = strip_tags($this->controller->piVars['street']);
        }

        if ($this->rightsObj->isAllowedToEditLocationZip() || $this->rightsObj->isAllowedToCreateLocationZip()) {
            $insertFields['zip'] = strip_tags($this->controller->piVars['zip']);
        }

        if ($this->rightsObj->isAllowedToEditLocationCity() || $this->rightsObj->isAllowedToCreateLocationCity()) {
            $insertFields['city'] = strip_tags($this->controller->piVars['city']);
        }

        if ($this->rightsObj->isAllowedToEditLocationCountryZone() || $this->rightsObj->isAllowedToCreateLocationCountryZone()) {
            $insertFields['country_zone'] = strip_tags($this->controller->piVars['countryzone']);
        }

        if ($this->rightsObj->isAllowedToEditLocationCountry() || $this->rightsObj->isAllowedToCreateLocationCountry()) {
            $insertFields['country'] = strip_tags($this->controller->piVars['country']);
        }

        if ($this->rightsObj->isAllowedToEditLocationPhone() || $this->rightsObj->isAllowedToCreateLocationPhone()) {
            $insertFields['phone'] = strip_tags($this->controller->piVars['phone']);
        }

        if ($this->rightsObj->isAllowedTo('edit', 'location', 'fax') || $this->rightsObj->isAllowedTo(
            'create',
            'location',
            'fax'
            )) {
            $insertFields['fax'] = strip_tags($this->controller->piVars['fax']);
        }

        if ($this->rightsObj->isAllowedToEditLocationEmail() || $this->rightsObj->isAllowedToCreateLocationEmail()) {
            $insertFields['email'] = strip_tags($this->controller->piVars['email']);
        }

        if ($this->rightsObj->isAllowedTo('edit', 'location', 'link') || $this->rightsObj->isAllowedTo(
            'create',
            'location',
            'link'
            )) {
            $insertFields['link'] = strip_tags($this->controller->piVars['link']);
        }
    }

    /**
     * @param $pid
     * @return Location
     */
    public function saveLocation($pid): Location
    {
        $crdate = time();
        $insertFields = [
            'pid' => $pid,
            'tstamp' => $crdate,
            'crdate' => $crdate
        ];
        // TODO: Check if all values are correct
        $this->searchForAdditionalFieldsToAddFromPostData($insertFields, 'location');
        $this->retrievePostData($insertFields);
        // Creating DB records
        $insertFields['cruser_id'] = $this->rightsObj->getUserId();

        $uid = $this->_saveLocation($insertFields);
        $this->unsetPiVars();
        return $this->find($uid, $this->conf['pidList']);
    }

    /**
     * @param $insertFields
     * @return mixed
     */
    public function _saveLocation(&$insertFields)
    {
        $table = 'tx_cal_location';
        $result = $GLOBALS['TYPO3_DB']->exec_INSERTquery($table, $insertFields);
        if (false === $result) {
            throw new RuntimeException(
                'Could not write ' . $table . ' record to database: ' . $GLOBALS['TYPO3_DB']->sql_error(),
                1431458153
            );
        }
        $uid = $GLOBALS['TYPO3_DB']->sql_insert_id();

        if ($this->rightsObj->isAllowedTo('create', 'location', 'image')) {
            $this->checkOnNewOrDeletableFiles('tx_cal_location', 'image', $insertFields, $uid);
        }

        $sharedGroups = [];
        $sharedUsers = [];
        $values = $this->controller->piVars['shared_ids'];
        if (!is_array($this->controller->piVars['shared_ids'])) {
            $values = GeneralUtility::trimExplode(',', $this->controller->piVars['shared_ids'], 1);
        }
        foreach ($values as $entry) {
            preg_match('/(^[a-z])_([0-9]+)/', $entry, $idname);
            if ($idname[1] === 'u') {
                $sharedUsers[] = $idname[2];
            } elseif ($idname[1] === 'g') {
                $sharedGroups[] = $idname[2];
            }
        }

        if ($this->rightsObj->isAllowedTo('create', 'location', 'shared')) {
            if ($this->conf['rights.']['create.']['location.']['addFeUserToShared']) {
                $sharedUsers[] = $this->rightsObj->getUserId();
            }
            if (count($sharedUsers) > 0 && (int)$sharedUsers[0] !== 0) {
                self::insertIdsIntoTableWithMMRelation(
                    'tx_cal_location_shared_user_mm',
                    array_unique($sharedUsers),
                    $uid,
                    'fe_users'
                );
            }
            $ignore = GeneralUtility::trimExplode(
                ',',
                $this->conf['rights.']['create.']['location.']['addFeGroupToShared.']['ignore'],
                1
            );
            $groupArray = array_diff($sharedGroups, $ignore);
            if (count($groupArray) > 0 && (int)$groupArray[0] !== 0) {
                self::insertIdsIntoTableWithMMRelation(
                    'tx_cal_location_shared_user_mm',
                    array_unique($groupArray),
                    $uid,
                    'fe_groups'
                );
            }
            if (count($sharedUsers) > 0 || count($groupArray) > 0) {
                $insertFields['shared_user_cnt'] = 1;
            } else {
                $insertFields['shared_user_cnt'] = 0;
            }
        } else {
            $idArray = [];
            if ($this->conf['rights.']['create.']['location.']['fields.']['shared.']['defaultUser'] !== '') {
                $idArray = explode(
                    ',',
                    $this->conf['rights.']['create.']['location.']['fields.']['shared.']['defaultUser']
                );
            }
            if ($this->conf['rights.']['create.']['location.']['addFeUserToShared']) {
                $idArray[] = $this->rightsObj->getUserId();
            }

            if (count($idArray) > 0 && (int)$idArray[0] !== 0) {
                self::insertIdsIntoTableWithMMRelation(
                    'tx_cal_location_shared_user_mm',
                    array_unique($idArray),
                    $uid,
                    'fe_users'
                );
            }

            $groupArray = [];
            if ($this->conf['rights.']['create.']['location.']['fields.']['shared.']['defaultGroup'] !== '') {
                $groupArray = GeneralUtility::trimExplode(
                    ',',
                    $this->conf['rights.']['create.']['location.']['fields.']['shared.']['defaultGroup'],
                    1
                );
                if ($this->conf['rights.']['create.']['location.']['addFeGroupToShared']) {
                    $idArray = $this->rightsObj->getUserGroups();
                    $ignore = GeneralUtility::trimExplode(
                        ',',
                        $this->conf['rights.']['create.']['location.']['addFeGroupToShared.']['ignore'],
                        1
                    );
                    $groupArray = array_diff($idArray, $ignore);
                }
                self::insertIdsIntoTableWithMMRelation(
                    'tx_cal_location_shared_user_mm',
                    array_unique($groupArray),
                    $uid,
                    'fe_groups'
                );
            }
            if (count($idArray) > 0 || count($groupArray) > 0) {
                $insertFields['shared_user_cnt'] = 1;
            } else {
                $insertFields['shared_user_cnt'] = 0;
            }
        }
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
       //trigger_error('Deprecated since ext:cal v2, will be removed in ext:cal v3.', E_USER_DEPRECATED);

        $table = 'tx_cal_location';
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

    public function unsetPiVars()
    {
        unset($this->controller->piVars['hidden'], $this->controller->piVars['_TRANSFORM_description'], $this->controller->piVars['uid'], $this->controller->piVars['type'], $this->controller->piVars['formCheck'], $this->controller->piVars['name'], $this->controller->piVars['description'], $this->controller->piVars['street'], $this->controller->piVars['zip'], $this->controller->piVars['city'], $this->controller->piVars['country'], $this->controller->piVars['countryzone'], $this->controller->piVars['phone'], $this->controller->piVars['email'], $this->controller->piVars['link'], $this->controller->piVars['image'], $this->controller->piVars['image_caption'], $this->controller->piVars['image_title'], $this->controller->piVars['image_alt']);
    }
}
