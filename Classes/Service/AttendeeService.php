<?php

/*
 * This file is part of the web-tp3/cal.
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

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

/**
 * Base model for the category.
 * Provides basic model functionality that other
 * models can use or override by extending the class.
 *
 */
class AttendeeService extends \TYPO3\CMS\Cal\Service\BaseService
{

    /**
     * Looks for an attendee with a given uid on a certain pid-list
     *
     * @param int $uid
     *        	to search for
     * @param string $pidList
     *        	to search in
     * @return array array ($row)
     */
    public function find($uid, $pidList)
    {
        $foundAttendees = [];
        $select = '*';
        $table = 'tx_cal_attendee';
        $where = 'uid = ' . $uid . ' ' . $this->cObj->enableFields('tx_cal_attendee');
        if ($pidList) {
            $where .= ' AND pid IN (' . $pidList . ')';
        }
        $result = $GLOBALS ['TYPO3_DB']->exec_SELECTquery($select, $table, $where);
        if ($result) {
            while ($row = $GLOBALS ['TYPO3_DB']->sql_fetch_assoc($result)) {
                $foundAttendees [] = $this->createAttendee($row);
            }
            $GLOBALS ['TYPO3_DB']->sql_free_result($result);
        }
        if ($foundAttendees [0]) {
            return $foundAttendees [0];
        }
        return 'none';
    }

    /**
     * Looks for all attendees on a certain pid-list
     *
     * @param string $pidList
     *        	to search in
     * @return array array of array (array of $rows)
     */
    public function findAll($pidList)
    {
        $foundAttendees = [];
        $select = '*';
        $table = 'tx_cal_attendee';
        $where = '1=1 ' . $this->cObj->enableFields('tx_cal_attendee');
        if ($pidList) {
            $where .= ' AND pid IN (' . $pidList . ')';
        }
        $result = $GLOBALS ['TYPO3_DB']->exec_SELECTquery($select, $table, $where);
        if ($result) {
            while ($row = $GLOBALS ['TYPO3_DB']->sql_fetch_assoc($result)) {
                $foundAttendees [$row ['uid']] = $this->createAttendee($row);
            }
            $GLOBALS ['TYPO3_DB']->sql_free_result($result);
        }
        return $foundAttendees;
    }
    public function updateAttendee($uid)
    {
        $insertFields = [
                'tstamp' => time()
        ];
        // TODO: Check if all values are correct
        $this->searchForAdditionalFieldsToAddFromPostData($insertFields, 'attendee', false);
        $this->retrievePostData($insertFields);

        $this->_updateAttendee($uid, $insertFields);
        return $this->find($uid, $this->conf ['pidList']);
    }
    public function _updateAttendee($uid, &$insertFields)
    {

        // Updating DB records
        $table = 'tx_cal_attendee';
        $where = 'uid = ' . $uid;

        $result = $GLOBALS ['TYPO3_DB']->exec_UPDATEquery($table, $where, $insertFields);

        $this->unsetPiVars();
    }
    public function removeAttendee($uid)
    {
        if ($this->rightsObj->isAllowedToDeleteCategory()) {
            // 'delete' the attendee object
            $updateFields = [
                    'tstamp' => time(),
                    'deleted' => 1
            ];
            $table = 'tx_cal_attendee';
            $where = 'uid = ' . $uid;
            $result = $GLOBALS ['TYPO3_DB']->exec_UPDATEquery($table, $where, $updateFields);

            $this->unsetPiVars();
        }
    }
    public function retrievePostData(&$insertFields)
    {
        $hidden = 0;
        if ($this->controller->piVars ['hidden'] == 'true' && ($this->rightsObj->isAllowedTo('edit', 'attendee', 'hidden') || $this->rightsObj->isAllowedTo('create', 'attendee', 'hidden'))) {
            $hidden = 1;
        }
        $insertFields ['hidden'] = $hidden;

        if ($this->rightsObj->isAllowedTo('edit', 'attendee', 'fe_user_id') || $this->rightsObj->isAllowedTo('create', 'attendee', 'fe_user_id')) {
            $insertFields ['fe_user_id'] = strip_tags($this->controller->piVars ['fe_user_id']);
        }

        if ($this->rightsObj->isAllowedTo('edit', 'attendee', 'email') || $this->rightsObj->isAllowedTo('create', 'attendee', 'email')) {
            $insertFields ['email'] = intval($this->controller->piVars ['email']);
        }

        if ($this->rightsObj->isAllowedTo('edit', 'attendee', 'attendance') || $this->rightsObj->isAllowedTo('create', 'attendee', 'attendance')) {
            $insertFields ['attendance'] = intval($this->controller->piVars ['attendance']);
        }

        if ($this->rightsObj->isAllowedTo('edit', 'attendee', 'status') || $this->rightsObj->isAllowedTo('create', 'attendee', 'status')) {
            $insertFields ['status'] = strip_tags($this->controller->piVars ['status']);
        }
    }
    public function saveAttendee($pid)
    {
        $crdate = time();
        $insertFields = [
                'pid' => $this->conf ['rights.'] ['create.'] ['attendee.'] ['saveAttendeeToPid'] ? $this->conf ['rights.'] ['create.'] ['attendee.'] ['saveAttendeeToPid'] : $pid,
                'tstamp' => $crdate,
                'crdate' => $crdate
        ];
        $this->searchForAdditionalFieldsToAddFromPostData($insertFields, 'attendee');
        $this->retrievePostData($insertFields);

        // Creating DB records
        $insertFields ['cruser_id'] = $this->rightsObj->getUserId();
        $uid = $this->_saveAttendee($insertFields);
        $this->unsetPiVars();
        return $this->find($uid, $this->conf ['pidList']);
    }
    public function _saveAttendee(&$insertFields)
    {
        $table = 'tx_cal_attendee';
        $result = $GLOBALS ['TYPO3_DB']->exec_INSERTquery($table, $insertFields);
        if (false === $result) {
            throw new \RuntimeException('Could not write ' . $table . ' record to database: ' . $GLOBALS ['TYPO3_DB']->sql_error(), 1431458138);
        }
        $uid = $GLOBALS ['TYPO3_DB']->sql_insert_id();
        return $uid;
    }
    public function getAttendeeEventSearchString($eventUid)
    {
        return ' AND tx_cal_attendee.event_id = ' . $eventUid;
    }
    public function createAttendee($row)
    {
        $attendee = new \TYPO3\CMS\Cal\Model\AttendeeModel($row, $this->getServiceKey());
        return $attendee;
    }
    public function findEventAttendees($eventUid)
    {
        $foundAttendees = [];
        // selecting attendees NOT attached to a fe_user
        $select = 'tx_cal_attendee.*, tx_cal_attendee.email AS the_email';
        $table = 'tx_cal_attendee';
        $where = 'tx_cal_attendee.fe_user_id = 0 ' . $this->cObj->enableFields('tx_cal_attendee') . $this->getAttendeeEventSearchString($eventUid);
        $result = $GLOBALS ['TYPO3_DB']->exec_SELECTquery($select, $table, $where);
        if ($result) {
            while ($row = $GLOBALS ['TYPO3_DB']->sql_fetch_assoc($result)) {
                $row ['email'] = $row ['the_email'];
                $foundAttendees [$row ['uid']] = $this->createAttendee($row);
            }
            $GLOBALS ['TYPO3_DB']->sql_free_result($result);
        }

        // selecting attendees attached to a fe_user
        $select = 'tx_cal_attendee.*, fe_users.email AS the_email, fe_users.name AS name';
        $table = 'fe_users, tx_cal_attendee';
        $where = 'fe_users.uid = tx_cal_attendee.fe_user_id' . $this->getAttendeeEventSearchString($eventUid) . $this->cObj->enableFields('tx_cal_attendee') . $this->cObj->enableFields('fe_users');
        $result = $GLOBALS ['TYPO3_DB']->exec_SELECTquery($select, $table, $where);
        if ($result) {
            while ($row = $GLOBALS ['TYPO3_DB']->sql_fetch_assoc($result)) {
                $row ['email'] = $row ['the_email'];
                $foundAttendees [$row ['uid']] = $this->createAttendee($row);
            }
            $GLOBALS ['TYPO3_DB']->sql_Free_result($result);
        }

        return $foundAttendees;
    }
    public function unsetPiVars()
    {
        unset($this->controller->piVars ['hidden']);
        unset($this->controller->piVars ['uid']);
        unset($this->controller->piVars ['type']);
        unset($this->controller->piVars ['email']);
        unset($this->controller->piVars ['fe_user_id']);
        unset($this->controller->piVars ['attendance']);
        unset($this->controller->piVars ['status']);
    }
}
