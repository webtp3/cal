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
use RuntimeException;
use TYPO3\CMS\Cal\Model\CalendarDateTime;
use TYPO3\CMS\Cal\Model\EventModel;
use TYPO3\CMS\Cal\Utility\Functions;

/**
 * Class SubscriptionManagerView
 */
class SubscriptionManagerView extends BaseView
{

    /**
     * Main function to draw the subscription manager view.
     *
     * @return string output of the subscription manager.
     */
    public function drawSubscriptionManager(): string
    {
        $rems = [];
        $sims = [];
        $wrapped = [];

        $sims['###HEADING###'] = $this->controller->pi_getLL('l_manage_subscription');
        $sims['###STATUS###'] = '';
        $rems['###USER_LOGIN###'] = '';
        $rems['###SUBSCRIPTION_CONTAINER###'] = '';

        /* Get the subscription manager template */
        $page = Functions::getContent($this->conf['view.']['event.']['subscriptionManagerTemplate']);
        if ($page === '') {
            return '<h3>calendar: no event template file found:</h3>' . $this->conf['view.']['event.']['subscriptionManagerTemplate'];
        }

        $eventUID = strip_tags($this->controller->piVars['uid']);
        $email = strip_tags($this->controller->piVars['email']);
        $subscriptionHash = strip_tags($this->controller->piVars['sid']);
        /* If we have an event, email, and subscription id, try to subscribe or unsubscribe */
        if ($eventUID > 0 && $email && $subscriptionHash) {
            $event = $this->modelObj->findEvent(
                $eventUID,
                'tx_cal_phpicalendar',
                $this->conf['pidList'],
                true,
                true,
                false,
                true,
                false
            );

            if (is_object($event)) {
                unset($this->controller->piVars['monitor'], $this->controller->piVars['email'], $this->controller->piVars['sid']);
                switch ($this->conf['monitor']) {
                    case 'stop': /* Unsubscribe a user */
                        if ($this->unsubscribe($email, $event, $subscriptionHash)) {
                            $sims['###STATUS###'] = sprintf(
                                $this->controller->pi_getLL('l_monitor_event_unsubscribe_successful'),
                                $event->getTitle()
                            );
                        } else {
                            /* No user to unsubscribe. Output a message here? */
                            $sims['###STATUS###'] = sprintf(
                                $this->controller->pi_getLL('l_monitor_event_unsubscribe_error'),
                                $event->getTitle()
                            );
                        }

                        break;
                    case 'start': /* Subscribe a user */
                        if ($this->subscribe($email, $event, $subscriptionHash)) {
                            $sims['###STATUS###'] = sprintf(
                                $this->controller->pi_getLL('l_monitor_event_subscribe_successful'),
                                $event->getTitle()
                            );
                        } else {
                            /* No user to subscribe. Output a message here? */
                            $sims['###STATUS###'] = sprintf(
                                $this->controller->pi_getLL('l_monitor_event_subscribe_error'),
                                $event->getTitle()
                            );
                        }
                        break;
                }
            } else {
                $noeventmessage = $this->conf['monitor'] === 'stop' ? 'l_monitor_event_unsubscribe_noevent' : 'l_monitor_event_subscribe_noevent';
                $sims['###STATUS###'] = sprintf($this->controller->pi_getLL($noeventmessage));
            }
        } elseif ($this->conf['subscribeFeUser'] && $this->rightsObj->isLoggedIn()) {
            $events = $this->subscriptionRepository->findEventsBySubscribersUids($this->rightsObj->getUserId());

            /* Save to temporary variables */
            $remUid = $this->conf['uid'];
            $remType = $this->conf['type'];
            $eventList = [];
            foreach ($events as $event) {
                $local_rems = [];
                $local_sims = [];
                $local_wrapped = [];
                $subscriptionContainer = $this->markerBasedTemplateService->getSubpart(
                    $page,
                    '###SUBSCRIPTION_CONTAINER###'
                );
                $event = $this->modelObj->findEvent(
                    $event['uid'],
                    'tx_cal_phpicalendar',
                    $this->conf['pidList'],
                    true,
                    true,
                    false,
                    true,
                    false
                );
                $this->conf['uid'] = $event['uid'];
                $this->conf['type'] = $event->getType();
                $event->getMarker($subscriptionContainer, $local_sims, $local_rems, $local_wrapped);
                $eventList[] = '<li>' . Functions::substituteMarkerArrayNotCached(
                    $subscriptionContainer,
                    $local_sims,
                    $local_rems,
                    $local_wrapped
                    ) . '</li>';
            }

            /* Restore from temporary variables */
            $this->conf['uid'] = $remUid;
            $this->conf['type'] = $remType;

            if (empty($eventList)) {
                $return = 'No events found.';
            } else {
                $return = '<ul>' . implode(chr(10), $eventList) . '</ul>';
            }

            $rems['###SUBSCRIPTION_CONTAINER###'] = $return;
        } else { /* Otherwise, request login or captcha validation */
            $sims['###STATUS###'] = 'You must be logged in to manage your event notifications.';
        }
        $page = Functions::substituteMarkerArrayNotCached($page, $sims, $rems, $wrapped);
        $rems = [];
        return $this->finish($page, $rems);
    }

    /**
     * Attempts to unsubscribe an email address from a particular event if the
     * subscription hash matches.
     * Check both the fe_users table and the
     * tx_cal_unknown_users table.
     *
     * @param string        Email address to unsubscribe.
     * @param EventModel $event Event that email should be unsubscribed from.
     * @param string        Unique hash of email and event.
     * @return bool whether unsubscribe was successful.
     * @todo Should we always try to unsubscribe both fe users and unknown
     *       users or just try one and stop if successful?
     */
    public function unsubscribe($email, $event, $subscriptionHash): bool
    {
        $eventUID = $event->getUid();
        return $this->unsubscribeByTable(
            'fe_users',
            $email,
            $eventUID,
            $subscriptionHash
            ) || $this->unsubscribeByTable(
                'tx_cal_unknown_users',
                $email,
                $eventUID,
                $subscriptionHash
            );
    }

    /**
     * Attempts to unsubscribe an email address within a particular table from
     * a particular event if the subscription hash matches.
     *
     * @param string        Table to look up email address in.
     * @param string        Email address to unsubscribe.
     * @param object        Event that email should be unsubscribed from.
     * @param string        Unique hash of email and event.
     * @return bool whether unsubscribe was successful.
     */
    public function unsubscribeByTable($table, $email, $eventUID, $subscriptionHash): bool
    {
        $returnValue = false;

        switch ($table) {
            case 'fe_users':
                $elements = $this->subscriptionRepository->findSubscribingUsersByEventUid($eventUID);
                break;
            case 'fe_groups':
                $elements = $this->subscriptionRepository->findSubscribingGroupsByEventUid($eventUID);
                break;
            case 'tx_cal_unknown_users':
                $elements = $this->subscriptionRepository->findUnknownSubscribingUsersByEventUid($eventUID);
                break;
            default:
                $elements = [];
        }

        foreach ($elements as $element) {
            if (md5($eventUID . $element['email'] . $element['crdate']) === $subscriptionHash) {
                $this->subscriptionRepository->deleteByEventUidAndTable($eventUID, $table);
                $returnValue = true;
            }
        }
        return $returnValue;
    }

    /**
     * Attempts to subscribe an email address to a particular event if the
     * subscription hash matches.
     *
     * @param string        Email address to subscribe.
     * @param EventModel $event Event that email should be subscribed to.
     * @param string        Unique Hash of email and event.
     * @return bool whether subscribe was successful.
     * @todo Should we always try to subscribe as a frontend user first?
     */
    public function subscribe($email, $event, $subscriptionHash): bool
    {
        $md5 = md5($event->getUid() . $email . $event->getCrdate());
        $eventUID = $event->getUid();

        /* If the subscription hash matches, subscribe */
        if ($md5 === $subscriptionHash) {
            $user_uid = $this->getFrontendUserUid($email);
            $user_table = 'fe_users';
            /* If we didn't find a matching frontend user, try unknown users */
            if (!$user_uid) {
                $user_uid = $this->getUnknownUserUid($email);
                $user_table = 'tx_cal_unknown_users';
            }

            $result = $this->subscriptionRepository->findByEventUidAndSharedUidAndTable(
                $eventUID,
                $user_uid,
                $user_table
            );
            if (empty($result)) {
                $this->subscriptionRepository->insert(
                    [
                        'uid_local' => $eventUID,
                        'uid_foreign' => $user_uid,
                        'tablenames' => $user_table,
                        'sorting' => 1,
                        'offset' => $this->conf['view.']['event.']['remind.']['time'],
                        'pid' => $event->getPid()
                    ]
                );
            }

            $date = new CalendarDateTime();
            $date->setTZbyID('UTC');
            $reminderService = &Functions::getReminderService();
            $reminderService->scheduleReminder($eventUID);

            return true;
        }
        return false;
    }

    /**
     * Inserts an intermediate row for a many-to-many table.
     *
     * @param $mmTable
     * @param $uid_local
     * @param $uid_foreign
     * @param $table
     * @param $sorting
     * @param int $offset
     * @param int $eventPid
     * @return int whether a new row was inserted.
     * @deprecated since ext:cal v2, will be removed in ext:cal v3
     */
    public function insertMMRow($mmTable, $uid_local, $uid_foreign, $table, $sorting, $offset = 0, $eventPid = 0): int
    {
        trigger_error('Deprecated since ext:cal v2, will be removed in ext:cal v3. If you need it, copy it.', E_USER_DEPRECATED);

        $insertedRow = false;

        $result = $this->subscriptionRepository->findByEventUidAndSharedUidAndTable(
            $uid_local,
            $uid_foreign,
            $table
        );
        if (empty($result)) {
            $this->subscriptionRepository->insert(
                [
                    'uid_local' => $uid_local,
                    'uid_foreign' => $uid_foreign,
                    'tablenames' => $table,
                    'sorting' => $sorting,
                    'offset' => $offset,
                    'pid' => $eventPid
                ]
            );
            $insertedRow = true;
        }
        return $insertedRow;
    }

    /**
     * @param $email
     * @return int
     */
    public function getUnknownUserUid($email): int
    {
        $already_exists = false;
        $user_uid = 0;

        $table = 'tx_cal_unknown_users';
        $select = 'uid,crdate';
        $where = 'email = "' . $email . '"';

        $result = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select, $table, $where);
        if ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
            $already_exists = true;
            $user_uid = $row['uid'];
        }
        $GLOBALS['TYPO3_DB']->sql_free_result($result);

        if (!$already_exists) {
            $crdate = time();
            $fields_values = [
                'tstamp' => time(),
                'crdate' => $crdate,
                'email' => $email,
                'pid' => $this->conf['rights.']['create.']['event.']['saveEventToPid']
            ];
            $result = $GLOBALS['TYPO3_DB']->exec_INSERTquery($table, $fields_values);
            if (false === $result) {
                throw new RuntimeException(
                    'Could not write ' . $table . ' record to database: ' . $GLOBALS['TYPO3_DB']->sql_error(),
                    1431458162
                );
            }
            $user_uid = $GLOBALS['TYPO3_DB']->sql_insert_id();
        }

        return $user_uid;
    }

    /**
     * @param $email
     * @return bool
     */
    public function getFrontendUserUid($email): bool
    {
        $user_uid = false;

        $table = 'fe_users';
        $select = 'uid';
        $where = 'email = "' . $email . '"';

        $result = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select, $table, $where);
        if ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
            $user_uid = $row['uid'];
        }
        $GLOBALS['TYPO3_DB']->sql_free_result($result);

        return $user_uid;
    }
}
