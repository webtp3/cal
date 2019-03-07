<?php

/*
 * This file is part of the web-tp3/cal.
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace TYPO3\CMS\Cal\Model;

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
class AttendeeModel extends BaseModel
{
    /**
     * @var int
     */
    public $eventUid = 0;

    /**
     * @var int
     */
    public $feUserId = 0;

    /**
     * @var string
     */
    public $email = '';

    /**
     * @var string
     */
    public $attendance = '';

    /**
     * @var int
     */
    public $status = 0;

    /**
     * @var string
     */
    public $name = '';

    /**
     * @var int
     */
    public $eventId = 0;

    /**
     * Constructor.
     * @param $row
     * @param $serviceKey
     */
    public function __construct($row, $serviceKey)
    {
        $this->setType('tx_cal_attendee');
        $this->setObjectType('attendee');
        parent::__construct($serviceKey);
        $this->init($row);
    }

    /**
     * @param $row
     */
    private function init(&$row)
    {
        $this->row = $row;
        if (isset($row['uid'])) {
            $this->setUid($row['uid']);
        }
        if (isset($row['event_id'])) {
            $this->setEventUid($row['event_id']);
        }
        if (isset($row['hidden'])) {
            $this->setHidden($row['hidden']);
        }
        if (isset($row['fe_user_id'])) {
            $this->setFeUserId($row['fe_user_id']);
        }
        if (isset($row['email'])) {
            $this->setEmail($row['email']);
        }
        if (isset($row['attendance'])) {
            $this->setAttendance($row['attendance']);
        }
        if (isset($row['status'])) {
            $this->setStatus($row['status']);
        }
        if (isset($row['name'])) {
            $this->setName($row['name']);
        }
    }

    /**
     * @param $uid
     */
    public function setEventUid($uid)
    {
        $this->eventUid = $uid;
    }

    /**
     * @return int
     */
    public function getEventUid(): int
    {
        return $this->eventUid;
    }

    /**
     * @param $uid
     */
    public function setFeUserId($uid)
    {
        $this->feUserId = $uid;
    }

    /**
     * @return int
     */
    public function getFeUserId(): int
    {
        return $this->feUserId;
    }

    /**
     * @param $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @param $attendance
     */
    public function setAttendance($attendance)
    {
        $this->attendance = $attendance;
    }

    /**
     * @return string
     */
    public function getAttendance(): string
    {
        return $this->attendance;
    }

    /**
     * @param $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return int
     */
    public function getStatus(): int
    {
        return $this->status;
    }

    /**
     * @param $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $feUserUid
     * @param array $feGroupsArray
     * @return bool
     */
    public function isUserAllowedToEdit($feUserUid = '', $feGroupsArray = []): bool
    {
        if (!$this->rightsObj->isViewEnabled('edit_attendee')) {
            return false;
        }
        if ($this->rightsObj->isCalAdmin()) {
            return true;
        }

        $isAttendee = false;
        if ($this->getFeUserId() === $this->rightsObj->getUserId()) {
            $isAttendee = true;
        }

        $isAllowedToEditAttendee = $this->rightsObj->isAllowedTo('edit', 'attendee');

        return $isAllowedToEditAttendee && $isAttendee;
    }

    /**
     * @param string $feUserUid
     * @param array $feGroupsArray
     * @return bool
     */
    public function isUserAllowedToDelete($feUserUid = '', $feGroupsArray = []): bool
    {
        if (!$this->rightsObj->isViewEnabled('delete_attendee')) {
            return false;
        }
        if ($this->rightsObj->isCalAdmin()) {
            return true;
        }

        $isAttendee = false;
        if ($this->getFeUserId() === $this->rightsObj->getUserId()) {
            $isAttendee = true;
        }

        $isAllowedToDeleteAttendee = $this->rightsObj->isAllowedTo('delete', 'attendee');

        return $isAllowedToDeleteAttendee && $isAttendee;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return 'Attendee ' . (is_object($this) ? 'object' : 'something') . ': ' . implode(',', $this->row);
    }
}
