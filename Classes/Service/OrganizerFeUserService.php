<?php

/*
 * This file is part of the web-tp3/cal.
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace TYPO3\CMS\Cal\Service;

use RuntimeException;
use TYPO3\CMS\Cal\Model\OrganizerFeUser;
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
 * @deprecated since ext:cal v2, will be removed in ext:cal v3
 */
class OrganizerFeUserService extends BaseService
{
    public $keyId = 'tx_feuser';
    public $tableId = 'fe_users';

    /**
     * Looks for an organizer with a given uid on a certain pid-list
     * @param int $uid
     * @param string $pidList
     * @return OrganizerFeUser
     */
    public function find($uid, $pidList)
    {
        trigger_error('Deprecated since ext:cal v2, will be removed in ext:cal v3.', E_USER_DEPRECATED);

        if ($pidList === '') {
            $result = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
                '*',
                'fe_users',
                'uid=' . $uid . ' ' . $this->cObj->enableFields('fe_users')
            );
        } else {
            $result = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
                '*',
                'fe_users',
                ' pid IN (' . $pidList . ') AND uid=' . $uid . ' ' . $this->cObj->enableFields('fe_users')
            );
        }
        if ($result) {
            $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result);
            $GLOBALS['TYPO3_DB']->sql_free_result($result);
            return new OrganizerFeUser($row, $pidList);
        }
    }

    /**
     * Looks for an organizer with a given uid on a certain pid-list
     *
     * @param string $pidList
     * @return array \TYPO3\CMS\Cal\Model\OrganizerFeUser
     */
    public function findAll($pidList): array
    {
        trigger_error('Deprecated since ext:cal v2, will be removed in ext:cal v3.', E_USER_DEPRECATED);

        $organizer = [];
        $orderBy = Functions::getOrderBy('fe_users');
        if ($pidList === '') {
            $result = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
                '*',
                'fe_users',
                ' 1 = 1 ' . $this->cObj->enableFields('fe_users'),
                '',
                $orderBy
            );
        } else {
            $result = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
                '*',
                'fe_users',
                ' pid IN (' . $pidList . ') ' . $this->cObj->enableFields('fe_users'),
                '',
                $orderBy
            );
        }
        if ($result) {
            while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
                $organizer[] = new OrganizerFeUser($row, $pidList);
            }
            $GLOBALS['TYPO3_DB']->sql_free_result($result);
        }
        return $organizer;
    }

    /**
     * Search for organizer
     *
     * @param string $pidList
     * @param string $searchword
     * @return array \TYPO3\CMS\Cal\Model\OrganizerFeUser
     */
    public function search($pidList, $searchword): array
    {
        trigger_error('Deprecated since ext:cal v2, will be removed in ext:cal v3.', E_USER_DEPRECATED);

        return $this->getOrganizerFromTable($pidList, $this->searchWhere($searchword));
    }

    /**
     * Generates the sql query and builds organizer objects out of the result rows
     *
     * @param string $pidList
     * @param string $additionalWhere
     * @return array \TYPO3\CMS\Cal\Model\OrganizerFeUser
     */
    private function getOrganizerFromTable($pidList, $additionalWhere = ''): array
    {
        trigger_error('Deprecated since ext:cal v2, will be removed in ext:cal v3.', E_USER_DEPRECATED);

        $organizers = [];
        if ($pidList !== '') {
            $additionalWhere .= ' AND ' . $this->tableId . '.pid IN (' . $pidList . ')';
        }
        $select = $this->tableId . '.*';
        $table = $this->tableId;
        $where = '1=1 ' . $additionalWhere . $this->cObj->enableFields($this->tableId);
        $groupBy = '';
        $orderBy = Functions::getOrderBy($this->tableId);
        $limit = '';

        $hookObjectsArr = Functions::getHookObjectsArray(
            'tx_cal_organizer_feuser_service',
            'organizerServiceClass',
            'service'
        );

        foreach ($hookObjectsArr as $hookObj) {
            if (method_exists($hookObj, 'preGetOrganizerFromTableExec')) {
                $hookObj->preGetOrganizerFromTableExec($this, $select, $table, $where, $groupBy, $orderBy, $limit);
            }
        }

        $result = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select, $table, $where, $groupBy, $orderBy, $limit);

        if ($result) {
            while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
                $organizers[] = new OrganizerFeUser($row, $pidList);
            }
            $GLOBALS['TYPO3_DB']->sql_free_result($result);
        }
        return $organizers;
    }

    /**
     * Generates a search where clause.
     *
     * @param string $sw :
     * @return string
     */
    public function searchWhere($sw): string
    {
        trigger_error('Deprecated since ext:cal v2, will be removed in ext:cal v3.', E_USER_DEPRECATED);

        $where = $this->cObj->searchWhere($sw, $this->conf['view.']['search.']['searchOrganizerFieldList'], 'fe_users');
        return $where;
    }

    /**
     * Updates the organizer with the given $uid with the post data
     * @param int $uid
     * @return OrganizerFeUser
     */
    public function updateOrganizer($uid): OrganizerFeUser
    {
        trigger_error('Deprecated since ext:cal v2, will be removed in ext:cal v3.', E_USER_DEPRECATED);

        $insertFields = [
            'tstamp' => time()
        ];
        // TODO: Check if all values are correct

        $this->retrievePostData($insertFields);
        $uid = self::checkUidForLanguageOverlay($uid, 'fe_users');
        // Creating DB records
        $table = 'fe_users';
        $where = 'uid = ' . $uid;
        $GLOBALS['TYPO3_DB']->exec_UPDATEquery($table, $where, $insertFields);
        return $this->find($uid, $this->conf['pidList']);
    }

    /**
     * Removes the organizer with the $uid
     * @param int $uid
     */
    public function removeOrganizer($uid)
    {
        if ($this->rightsObj->isAllowedToDeleteOrganizer()) {
            $updateFields = [
                'tstamp' => time(),
                'deleted' => 1
            ];
            $table = 'fe_users';
            $where = 'uid = ' . $uid;
            $GLOBALS['TYPO3_DB']->exec_UPDATEquery($table, $where, $updateFields);
        }
    }

    /**
     * Adds the attribute and provided post data to the array
     * @param array $insertFields
     */
    private function retrievePostData(&$insertFields)
    {
        trigger_error('Deprecated since ext:cal v2, will be removed in ext:cal v3.', E_USER_DEPRECATED);

        $hidden = 0;
        if ($this->controller->piVars['hidden'] === 'true' && ($this->rightsObj->isAllowedToEditOrganizerHidden() || $this->rightsObj->isAllowedToCreateOrganizerHidden())) {
            $hidden = 1;
        }
        $insertFields['hidden'] = $hidden;

        if ($this->rightsObj->isAllowedToEditOrganizerName() || $this->rightsObj->isAllowedToCreateOrganizerName()) {
            $insertFields['name'] = strip_tags($this->controller->piVars['name']);
        }

        if ($this->rightsObj->isAllowedToEditOrganizerDescription() || $this->rightsObj->isAllowedToCreateOrganizerDescription()) {
            $insertFields['title'] = htmlspecialchars($this->controller->piVars['title'], $this->conf);
        }

        if ($this->rightsObj->isAllowedToEditOrganizerStreet() || $this->rightsObj->isAllowedToCreateOrganizerStreet()) {
            $insertFields['address'] = strip_tags($this->controller->piVars['address']);
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
            $insertFields['www'] = strip_tags($this->controller->piVars['www']);
        }
    }

    /**
     * Saves an organizer at the page with the id $pid
     * @param int $pid
     * @return OrganizerFeUser
     */
    public function saveOrganizer($pid): OrganizerFeUser
    {
        trigger_error('Deprecated since ext:cal v2, will be removed in ext:cal v3.', E_USER_DEPRECATED);

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
            $insertFields['title'] = htmlspecialchars($this->controller->piVars['title']);
        }
        if ($this->controller->piVars['street'] !== '') {
            $insertFields['address'] = strip_tags($this->controller->piVars['address']);
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
            $insertFields['www'] = strip_tags($this->controller->piVars['www']);
        }

        // Creating DB records
        $insertFields['cruser_id'] = $this->rightsObj->getUserId();
        $uid = $this->_saveOrganizer($insertFields);
        return $this->find($uid, $this->conf['pidList']);
    }

    /**
     * Does the database save
     * @param array $insertFields
     * @throws RuntimeException
     * @return int the uid of the saved organizer
     */
    private function _saveOrganizer(&$insertFields): int
    {
        trigger_error('Deprecated since ext:cal v2, will be removed in ext:cal v3.', E_USER_DEPRECATED);

        $table = 'fe_users';
        $result = $GLOBALS['TYPO3_DB']->exec_INSERTquery($table, $insertFields);
        if (false === $result) {
            throw new RuntimeException(
                'Could not write ' . $table . ' record to database: ' . $GLOBALS['TYPO3_DB']->sql_error(),
                1431458155
            );
        }
        $uid = $GLOBALS['TYPO3_DB']->sql_insert_id();
        return $uid;
    }

    /**
     * Checks if this service is allowed to be processed
     * @return bool
     */
    public function isAllowedService(): bool
    {
        trigger_error('Deprecated since ext:cal v2, will be removed in ext:cal v3.', E_USER_DEPRECATED);

        $confArr = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['cal']);
        $useOrganizerStructure = ($confArr['useOrganizerStructure'] ?: 'tx_cal_location');
        return $useOrganizerStructure === $this->keyId;
    }

    /**
     * Creates a translation overlay record for a given organizer with the uid
     * @param int $uid
     * @param int $overlay
     * @deprecated since ext:cal v2, will be removed in ext:cal v3
     */
    public function createTranslation($uid, $overlay)
    {
        trigger_error('Deprecated since ext:cal v2, will be removed in ext:cal v3.', E_USER_DEPRECATED);

        $table = 'fe_users';
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
