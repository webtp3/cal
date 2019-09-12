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
use TYPO3\CMS\Cal\Model\AttendeeModel;
use TYPO3\CMS\Cal\Model\EventModel;
use TYPO3\CMS\Cal\Utility\Functions;

/**
 * Class MeetingManagerView
 */
class MeetingManagerView extends BaseView
{
    /**
     * Main function to draw the meeting manager view.
     *
     * @return string output of the meeting manager.
     */
    public function drawMeetingManager(): string
    {
        $rems = [];
        $sims = [];
        $wrapped = [];

        $sims['###HEADING###'] = $this->controller->pi_getLL('l_manage_meeting');
        $sims['###STATUS###'] = '';
        $rems['###USER_LOGIN###'] = '';
        $rems['###MEETING_CONTAINER###'] = '';

        /* Get the meeting manager template */
        $page = Functions::getContent($this->conf['view.']['event.']['meeting.']['managerTemplate']);
        if ($page === '') {
            return '<h3>calendar: no meeting manager template file found:</h3>' . $this->conf['view.']['meeting.']['managerTemplate'];
        }

        $eventUID = $this->conf['uid'];
        $attendeeUid = intval($this->controller->piVars['attendee']);
        $meetingHash = strip_tags($this->controller->piVars['sid']);
        $attendeeStatus = strip_tags($this->controller->piVars['status']);

        /* If we have an event, email, and meeting id, try to subscribe or unsubscribe */
        if ($eventUID > 0 && $attendeeUid && $attendeeStatus && $meetingHash) {
            $event = $this->modelObj->findEvent(
                $eventUID,
                'tx_cal_phpicalendar',
                $this->conf['pidList'],
                false,
                false,
                false,
                true,
                true
            );

            unset($this->controller->piVars['monitor'], $this->controller->piVars['attendee'], $this->controller->piVars['sid']);
            switch ($attendeeStatus) {
                case 'accept': /* user comes to the meeting */
                    if ($this->changeStatus($attendeeUid, $event, $meetingHash, 'ACCEPTED')) {
                        $sims['###STATUS###'] = sprintf(
                            $this->controller->pi_getLL('l_meeting_accepted'),
                            $event->getTitle()
                        );
                    } else {
                        /* No user to unsubscribe. Output a message here? */
                        $sims['###STATUS###'] = sprintf(
                            $this->controller->pi_getLL('l_meeting_update_error'),
                            $event->getTitle()
                        );
                    }

                    break;
                case 'decline': /* user does not come to the meeting */
                    if ($this->changeStatus($attendeeUid, $event, $meetingHash, 'DECLINE')) {
                        $sims['###STATUS###'] = sprintf(
                            $this->controller->pi_getLL('l_meeting_declined'),
                            $event->getTitle()
                        );
                    } else {
                        /* No user to subscribe. Output a message here? */
                        $sims['###STATUS###'] = sprintf(
                            $this->controller->pi_getLL('l_meeting_update_error'),
                            $event->getTitle()
                        );
                    }
                    break;
            }
        } else {
            $sims['###STATUS###'] = $this->controller->pi_getLL('l_meeting_error');
        }
        $page = Functions::substituteMarkerArrayNotCached($page, $sims, $rems, $wrapped);
        $rems = [];
        return $this->finish($page, $rems);
    }

    /**
     * Attempts to change the status of a meeting participant of
     * a particular event if the meeting hash matches.
     *
     * @param $attendeeUid
     * @param EventModel $event Event object.
     * @param $meetingHash
     * @param $status
     * @return string status to set the attendee to.
     */
    public function changeStatus($attendeeUid, $event, $meetingHash, $status): string
    {
        $attendeeArray = $event->getAttendees();

        if ($attendeeArray['tx_cal_attendee'][$attendeeUid]) {
            /** @var AttendeeModel $attendeeObject */
            $attendeeObject = $attendeeArray['tx_cal_attendee'][$attendeeUid];
            $md5 = md5($event->getUid() . $attendeeObject->getEmail() . $attendeeObject->getCrdate());
            if ($md5 === $meetingHash) {
                $table = 'tx_cal_attendee';
                $where = 'uid = ' . $attendeeUid;
                $GLOBALS['TYPO3_DB']->exec_UPDATEquery($table, $where, [
                    'status' => $status
                ]);
                return true;
            }
        }
        return false;
    }
}
