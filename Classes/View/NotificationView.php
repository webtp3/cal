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
use TYPO3\CMS\Cal\Model\CategoryModel;
use TYPO3\CMS\Cal\Model\EventModel;
use TYPO3\CMS\Cal\Service\EventService;
use TYPO3\CMS\Cal\Utility\Functions;
use TYPO3\CMS\Cal\Utility\Registry;
use TYPO3\CMS\Core\Mail\MailMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class NotificationView
 */
class NotificationView extends BaseView
{
    /**
     * @var MailMessage
     */
    public $mailer;

    /**
     * @var string
     */
    public $baseUrl = '';

    /**
     * @param $oldEventDataArray
     * @param $newEventDataArray
     */
    public function notifyOfChanges($oldEventDataArray, $newEventDataArray)
    {
        unset($oldEventDataArray['starttime'], $oldEventDataArray['endtime'], $newEventDataArray['starttime'], $newEventDataArray['endtime']);

        $pidArray = GeneralUtility::trimExplode(',', $this->conf['pidList'], 1);
        if (!in_array($oldEventDataArray['pid'], $pidArray, true)) {
            GeneralUtility::sysLog(
                'Event PID (' . $oldEventDataArray['pid'] . ') is outside the configured pidList (' . $this->conf['pidList'] . ') so notifications cannot be sent.',
                'cal',
                2
            );
            return;
        }
        $eventDataArray = array_merge($oldEventDataArray, $newEventDataArray);
        $event_old = $this->modelObj->findEvent(
            $oldEventDataArray['uid'],
            'tx_cal_phpicalendar',
            $this->conf['pidList'],
            true,
            true,
            false,
            true,
            false
        );
        $event_new = $this->modelObj->findEvent(
            $oldEventDataArray['uid'],
            'tx_cal_phpicalendar',
            $this->conf['pidList'],
            true,
            true,
            false,
            true,
            false
        );

        // Make sure we have an old event and new event before notifying.
        if (is_object($event_old) && is_object($event_new)) {
            $event_old->updateWithPIVars($oldEventDataArray);
            $event_new->updateWithPIVars($eventDataArray);

            $this->startMailer();

            $subscribers = $this->subscriptionRepository->findSubscribingUsersByEventUid($oldEventDataArray['uid']);
            foreach ($subscribers as $subscriber) {
                if ($subscriber['email'] !== '' && GeneralUtility::validEmail($subscriber['email'])) {
                    $template = $this->conf['view.']['event.']['notify.'][$subscriber['uid'] . '.']['onChangeTemplate'];
                    if (!$template) {
                        $template = $this->conf['view.']['event.']['notify.']['all.']['onChangeTemplate'];
                    }
                    $titleText = $this->conf['view.']['event.']['notify.'][$subscriber['uid'] . '.']['onChangeEmailTitle'];
                    if (!$titleText) {
                        $titleText = $this->conf['view.']['event.']['notify.']['all.']['onChangeEmailTitle'];
                    }

                    $unsubscribeLink = $this->baseUrl . $this->controller->pi_getPageLink(
                        $this->conf['view.']['event.']['notify.']['subscriptionViewPid'],
                        '',
                        [
                                'tx_cal_controller[view]' => 'subscription',
                                'tx_cal_controller[email]' => $subscriber['email'],
                                'tx_cal_controller[uid]' => $event_old->getUid(),
                                'tx_cal_controller[monitor]' => 'stop',
                                'tx_cal_controller[sid]' => md5($event_old->getUid() . $subscriber['email'] . $subscriber['crdate'])
                            ]
                        );
                    $this->sendNotificationOfChanges(
                        $event_old,
                        $event_new,
                        $subscriber['email'],
                        $template,
                        $titleText,
                        $unsubscribeLink
                    );
                }
            }

            $subscribers = $this->subscriptionRepository->findUnknownSubscribingUsersByEventUid($oldEventDataArray['uid']);
            foreach ($subscribers as $subscriber) {
                if ($subscriber['email'] !== '' && GeneralUtility::validEmail($subscriber['email'])) {
                    $template = $this->conf['view.']['event.']['notify.']['all.']['onChangeTemplate'];
                    $titleText = $this->conf['view.']['event.']['notify.']['all.']['onChangeEmailTitle'];
                    $unsubscribeLink = $this->baseUrl . $this->controller->pi_getPageLink(
                        $this->conf['view.']['event.']['notify.']['subscriptionViewPid'],
                        '',
                        [
                                'tx_cal_controller[view]' => 'subscription',
                                'tx_cal_controller[email]' => $subscriber['email'],
                                'tx_cal_controller[uid]' => $event_old->getUid(),
                                'tx_cal_controller[monitor]' => 'stop',
                                'tx_cal_controller[sid]' => md5($event_old->getUid() . $subscriber['email'] . $subscriber['crdate'])
                            ]
                        );
                    $this->sendNotificationOfChanges(
                        $event_old,
                        $event_new,
                        $subscriber['email'],
                        $template,
                        $titleText,
                        $unsubscribeLink
                    );
                }
            }

            /** @var CategoryModel $category */
            foreach ($event_new->getCategories() as $category) {
                if (is_object($category)) {
                    foreach ($category->getNotificationEmails() as $emailAddress) {
                        if ($emailAddress !== '' && GeneralUtility::validEmail($emailAddress)) {
                            $template = $this->conf['view.']['category.']['notify.'][$category->getUid() . '.']['onChangeTemplate'];
                            if (!$template) {
                                $template = $this->conf['view.']['category.']['notify.']['all.']['onChangeTemplate'];
                            }
                            $titleText = $this->conf['view.']['category.']['notify.'][$category->getUid() . '.']['onChangeEmailTitle'];
                            if (!$titleText) {
                                $titleText = $this->conf['view.']['category.']['notify.']['all.']['onChangeEmailTitle'];
                            }
                            $unsubscribeLink = '';
                            $this->sendNotificationOfChanges(
                                $event_old,
                                $event_new,
                                $emailAddress,
                                $template,
                                $titleText,
                                $unsubscribeLink
                            );
                        }
                    }
                }
            }

            $subType = 'getGroupsFE';
            $groups = [];
            $serviceObj = GeneralUtility::makeInstanceService('auth', $subType);
            if ($serviceObj === null) {
                return;
            }

            $groupUids = $this->subscriptionRepository->findSubscribingGroupsByEventUid($oldEventDataArray['uid']);
            foreach ($groupUids as $groupUid) {
                $serviceObj->getSubGroups($groupUid['uid_foreign'], '', $groups);

                $select = 'DISTINCT fe_users.email';
                $table = 'fe_groups, fe_users';
                $where = 'fe_groups.uid IN (' . implode(',', $groups) . ') 
						AND FIND_IN_SET(fe_groups.uid, fe_users.usergroup)
						AND fe_users.email != \'\' 
						AND fe_groups.deleted = 0 
						AND fe_groups.hidden = 0 
						AND fe_users.disable = 0
						AND fe_users.deleted = 0';
                $result2 = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select, $table, $where);
                while ($row2 = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result2)) {
                    if ($row2['email'] !== '' && GeneralUtility::validEmail($row2['email'])) {
                        $template = $this->conf['view.']['event.']['notify.'][$row2['uid'] . '.']['onChangeTemplate'];
                        if (!$template) {
                            $template = $this->conf['view.']['event.']['notify.']['all.']['onChangeTemplate'];
                        }
                        $titleText = $this->conf['view.']['event.']['notify.'][$row2['uid'] . '.']['onChangeEmailTitle'];
                        if (!$titleText) {
                            $titleText = $this->conf['view.']['event.']['notify.']['all.']['onChangeEmailTitle'];
                        }

                        $unsubscribeLink = $this->baseUrl . $this->controller->pi_getPageLink(
                            $this->conf['view.']['event.']['notify.']['subscriptionViewPid'],
                            '',
                            [
                                    'tx_cal_controller[view]' => 'subscription',
                                    'tx_cal_controller[email]' => $row2['email'],
                                    'tx_cal_controller[uid]' => $event_old->getUid(),
                                    'tx_cal_controller[monitor]' => 'stop',
                                    'tx_cal_controller[sid]' => md5($event_old->getUid() . $row2['email'] . $row2['crdate'])
                                ]
                            );
                        $this->sendNotificationOfChanges(
                            $event_old,
                            $event_new,
                            $row2['email'],
                            $template,
                            $titleText,
                            $unsubscribeLink
                        );
                    }
                }
                $GLOBALS['TYPO3_DB']->sql_free_result($result2);
            }
        }
    }

    /**
     * @param EventModel $event_old
     * @param EventModel $event_new
     * @param $email
     * @param $templatePath
     * @param $titleText
     * @param $unsubscribeLink
     * @param string $acceptLink
     * @param string $declineLink
     */
    public function sendNotificationOfChanges(
        &$event_old,
        &$event_new,
        $email,
        $templatePath,
        $titleText,
        $unsubscribeLink,
        $acceptLink = '',
        $declineLink = ''
    ) {
        $absFile = GeneralUtility::getFileAbsFileName($templatePath);
        $template = GeneralUtility::getUrl($absFile);
        $htmlTemplate = $this->markerBasedTemplateService->getSubpart($template, '###HTML###');
        $oldEventHTMLSubpart = $this->markerBasedTemplateService->getSubpart($htmlTemplate, '###OLD_EVENT###');
        $newEventHTMLSubpart = $this->markerBasedTemplateService->getSubpart($htmlTemplate, '###NEW_EVENT###');

        $plainTemplate = $this->markerBasedTemplateService->getSubpart($template, '###PLAIN###');
        $oldEventPlainSubpart = $this->markerBasedTemplateService->getSubpart($plainTemplate, '###OLD_EVENT###');
        $newEventPlainSubpart = $this->markerBasedTemplateService->getSubpart($plainTemplate, '###NEW_EVENT###');

        $this->fillTemplate($event_old, $oldEventHTMLSubpart, $oldEventPlainSubpart);
        $this->fillTemplate($event_new, $newEventHTMLSubpart, $newEventPlainSubpart);

        $switch = [];
        $switch['###UNSUBSCRIBE_LINK###'] = $unsubscribeLink;
        $switch['###ACCEPT_LINK###'] = $acceptLink;
        $switch['###DECLINE_LINK###'] = $declineLink;

        $switch['###CURRENT_USER###'] = $this->getModifyingUser($template);

        $htmlTemplate = Functions::substituteMarkerArrayNotCached($htmlTemplate, $switch, [
            '###OLD_EVENT###' => $oldEventHTMLSubpart,
            '###NEW_EVENT###' => $newEventHTMLSubpart
        ], []);
        $plainTemplate = Functions::substituteMarkerArrayNotCached($plainTemplate, $switch, [
            '###OLD_EVENT###' => $oldEventPlainSubpart,
            '###NEW_EVENT###' => $newEventPlainSubpart
        ], []);

        $plainTemplate = $event_new->finish($plainTemplate);
        $htmlTemplate = $event_new->finish($htmlTemplate);

        $switch = [];
        $rems = [];
        $wrapped = [];
        $event_new->getMarker($titleText, $switch, $rems, $wrapped, 'title');
        $this->mailer->setSubject(Functions::substituteMarkerArrayNotCached(
            $titleText,
            $switch,
            $rems,
            $wrapped
        ));
        $this->sendEmail($email, $htmlTemplate, $plainTemplate);
    }

    /**
     * Get the (configurable) details of the currently logged in user for the notification-mail.
     * The detailed info of the currently logged in user is retrieved from the template (notifyOnCreate.tmpl, notifyOnChange.tmpl or notifyOnDelete.tmpl)
     * with the tag ###CURRENT_USER###. The structure of the info is given between ###CURRENT_USER_SUBPART###. Every field of the 'fe_users' record can
     * be used by converting the field-name to uppercase and putting it between '###', e.g. first_name --> ###FIRST_NAME###.
     * The fields can be wrapped by specifying tx_cal_controller.view.event.notify.currentUser.<field-name>_stdWrap { dataWrap = ... }, e.g.
     * tx_cal_controller.view.event.notify.currentUser.first_name_stdWrap { dataWrap = Firstname: | }
     *
     * @param $template
     * @return string
     */
    public function getModifyingUser($template): string
    {
        $modifyingUser = '';
        $currentUserSubpart = $this->markerBasedTemplateService->getSubpart($template, '###CURRENT_USER_SUBPART###');

        if (TYPO3_MODE === 'FE') {
            $feUser = $GLOBALS['TSFE']->fe_user->user;
            $sims = [];
            foreach ($feUser as $index => $value) {
                $wrappedValue = $this->cObj->stdWrap(
                    $value,
                    $this->conf['view.']['event.']['notify.']['currentUser.'][strtolower($index) . '_stdWrap.']
                );
                $sims['###' . strtoupper($index) . '###'] = $wrappedValue;
            }
            $modifyingUser = $this->cObj->substituteMarkerArray($currentUserSubpart, $sims);
        }
        return $modifyingUser;
    }

    /**
     * @param EventModel $event
     * @param $eventHTMLSubpart
     * @param $eventPlainSubpart
     */
    public function fillTemplate(&$event, &$eventHTMLSubpart, &$eventPlainSubpart)
    {
        $switch = [];
        $rems = [];
        $wrapped = [];
        $event->getMarker($eventHTMLSubpart, $switch, $rems, $wrapped, 'notification');
        $eventHTMLSubpart = Functions::substituteMarkerArrayNotCached(
            $eventHTMLSubpart,
            $switch,
            $rems,
            $wrapped
        );

        $switch = [];
        $rems = [];
        $wrapped = [];
        $event->getMarker($eventPlainSubpart, $switch, $rems, $wrapped, 'notification');
        $eventPlainSubpart = Functions::substituteMarkerArrayNotCached(
            $eventPlainSubpart,
            $switch,
            $rems,
            $wrapped
        );
    }

    /**
     * @param $newEventDataArray
     * @param int $forceDeletionMode
     */
    public function notify(&$newEventDataArray, $forceDeletionMode = 0)
    {
        $event = $this->modelObj->findEvent(
            $newEventDataArray['uid'],
            'tx_cal_phpicalendar',
            $this->conf['pidList'],
            true,
            true,
            false,
            true,
            false
        );

        if (is_object($event)) {
            $this->startMailer();
            $subscribers = $this->subscriptionRepository->findSubscribingUsersByEventUid($newEventDataArray['uid']);
            foreach ($subscribers as $subscriber) {
                if ($subscriber['email'] !== '' && GeneralUtility::validEmail($subscriber['email'])) {
                    if (($newEventDataArray['deleted'] + $forceDeletionMode) > 0) {
                        $template = $this->conf['view.']['event.']['notify.']['fe_users_' . $subscriber['uid'] . '.']['onDeleteTemplate'];
                        if (!$template) {
                            $template = $this->conf['view.']['event.']['notify.']['all.']['onDeleteTemplate'];
                        }
                        $titleText = $this->conf['view.']['event.']['notify.']['fe_users_' . $subscriber['uid'] . '.']['onDeleteEmailTitle'];
                        if (!$titleText) {
                            $titleText = $this->conf['view.']['event.']['notify.']['all.']['onDeleteEmailTitle'];
                        }
                    } else {
                        $template = $this->conf['view.']['event.']['notify.']['fe_users_' . $subscriber['uid'] . '.']['onCreateTemplate'];
                        if (!$template) {
                            $template = $this->conf['view.']['event.']['notify.']['all.']['onCreateTemplate'];
                        }
                        $titleText = $this->conf['view.']['event.']['notify.']['fe_users_' . $subscriber['uid'] . '.']['onCreateEmailTitle'];
                        if (!$titleText) {
                            $titleText = $this->conf['view.']['event.']['notify.']['all.']['onCreateEmailTitle'];
                        }
                    }

                    $unsubscribeLink = $this->baseUrl . $this->controller->pi_getPageLink(
                        $this->conf['view.']['event.']['notify.']['subscriptionViewPid'],
                        '',
                        [
                                'tx_cal_controller[view]' => 'subscription',
                                'tx_cal_controller[email]' => $subscriber['email'],
                                'tx_cal_controller[uid]' => $event->getUid(),
                                'tx_cal_controller[monitor]' => 'stop',
                                'tx_cal_controller[sid]' => md5($event->getUid() . $subscriber['email'] . $subscriber['crdate'])
                            ]
                        );
                    $this->sendNotification($event, $subscriber['email'], $template, $titleText, $unsubscribeLink);
                }
            }

            $subscribers = $this->subscriptionRepository->findUnknownSubscribingUsersByEventUid($event->getUid());
            foreach ($subscribers as $subscriber) {
                if ($subscriber['email'] !== '' && GeneralUtility::validEmail($subscriber['email'])) {
                    $template = $this->conf['view.']['event.']['notify.']['all.']['onCreateTemplate'];
                    $titleText = $this->conf['view.']['event.']['notify.']['all.']['onCreateEmailTitle'];
                    if (($newEventDataArray['deleted'] + $forceDeletionMode) > 0) {
                        $template = $this->conf['view.']['event.']['notify.']['all.']['onDeleteTemplate'];
                        $titleText = $this->conf['view.']['event.']['notify.']['all.']['onDeleteEmailTitle'];
                    }
                    $unsubscribeLink = GeneralUtility::getIndpEnv('TYPO3_SITE_URL') . $this->controller->pi_getPageLink(
                        $this->conf['view.']['event.']['notify.']['subscriptionViewPid'],
                        '',
                        [
                                'tx_cal_controller[view]' => 'subscription',
                                'tx_cal_controller[email]' => $subscriber['email'],
                                'tx_cal_controller[uid]' => $event->getUid(),
                                'tx_cal_controller[monitor]' => 'stop',
                                'tx_cal_controller[sid]' => md5($event->getUid() . $subscriber['email'] . $subscriber['crdate'])
                            ]
                        );
                    $this->sendNotification($event, $subscriber['email'], $template, $titleText, $unsubscribeLink);
                }
            }

            /** @var CategoryModel $category */
            foreach ($event->getCategories() as $category) {
                foreach ($category->getNotificationEmails() as $emailAddress) {
                    if ($emailAddress !== '' && GeneralUtility::validEmail($emailAddress)) {
                        if (($newEventDataArray['deleted'] + $forceDeletionMode) > 0) {
                            $template = $this->conf['view.']['event.']['notify.'][$category->getUid() . '.']['onDeleteTemplate'];
                            if (!$template) {
                                $template = $this->conf['view.']['event.']['notify.']['all.']['onDeleteTemplate'];
                            }
                            $titleText = $this->conf['view.']['event.']['notify.'][$category->getUid() . '.']['onDeleteEmailTitle'];
                            if (!$titleText) {
                                $titleText = $this->conf['view.']['event.']['notify.']['all.']['onDeleteEmailTitle'];
                            }
                        } else {
                            $template = $this->conf['view.']['event.']['notify.'][$category->getUid() . '.']['onCreateTemplate'];
                            if (!$template) {
                                $template = $this->conf['view.']['event.']['notify.']['all.']['onCreateTemplate'];
                            }
                            $titleText = $this->conf['view.']['event.']['notify.'][$category->getUid() . '.']['onCreateEmailTitle'];
                            if (!$titleText) {
                                $titleText = $this->conf['view.']['event.']['notify.']['all.']['onCreateEmailTitle'];
                            }
                        }
                        $unsubscribeLink = '';
                        $this->sendNotification($event, $emailAddress, $template, $titleText, $unsubscribeLink);
                    }
                }
            }

            $subType = 'getGroupsFE';
            $groups = [];
            $serviceObj = GeneralUtility::makeInstanceService('auth', $subType);
            $groupUids = $this->subscriptionRepository->findSubscribingGroupsByEventUid($event->getUid());
            foreach ($groupUids as $groupUid) {
                $serviceObj->getSubGroups($groupUid['uid_local'], '', $groups);

                $select = 'DISTINCT fe_users.email';
                $table = 'fe_groups, fe_users';
                $where = 'fe_groups.uid IN (' . implode(',', $groups) . ') 
						AND FIND_IN_SET(fe_groups.uid, fe_users.usergroup)
						AND fe_users.email != \'\' 
						AND fe_groups.deleted = 0 
						AND fe_groups.hidden = 0 
						AND fe_users.disable = 0
						AND fe_users.deleted = 0';
                $result2 = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select, $table, $where);
                while ($row2 = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result2)) {
                    if ($row2['email'] !== '' && GeneralUtility::validEmail($row2['email'])) {
                        if (($newEventDataArray['deleted'] + $forceDeletionMode) > 0) {
                            $template = $this->conf['view.']['event.']['notify.']['fe_groups_' . $row2['uid'] . '.']['onDeleteTemplate'];
                            if (!$template) {
                                $template = $this->conf['view.']['event.']['notify.']['all.']['onDeleteTemplate'];
                            }
                            $titleText = $this->conf['view.']['event.']['notify.']['fe_groups_' . $row2['uid'] . '.']['onDeleteEmailTitle'];
                            if (!$titleText) {
                                $titleText = $this->conf['view.']['event.']['notify.']['all.']['onDeleteEmailTitle'];
                            }
                        } else {
                            $template = $this->conf['view.']['event.']['notify.']['fe_groups_' . $row2['uid'] . '.']['onCreateTemplate'];
                            if (!$template) {
                                $template = $this->conf['view.']['event.']['notify.']['all.']['onCreateTemplate'];
                            }
                            $titleText = $this->conf['view.']['event.']['notify.']['fe_groups_' . $row2['uid'] . '.']['onCreateEmailTitle'];
                            if (!$titleText) {
                                $titleText = $this->conf['view.']['event.']['notify.']['all.']['onCreateEmailTitle'];
                            }
                        }

                        $unsubscribeLink = $this->baseUrl . $this->controller->pi_getPageLink(
                            $this->conf['view.']['event.']['notify.']['subscriptionViewPid'],
                            '',
                            [
                                    'tx_cal_controller[view]' => 'subscription',
                                    'tx_cal_controller[email]' => $row2['email'],
                                    'tx_cal_controller[uid]' => $event->getUid(),
                                    'tx_cal_controller[monitor]' => 'stop',
                                    'tx_cal_controller[sid]' => md5($event->getUid() . $row2['email'] . $row2['crdate'])
                                ]
                            );
                        $this->sendNotification($event, $row2['email'], $template, $titleText, $unsubscribeLink);
                    }
                }
                $GLOBALS['TYPO3_DB']->sql_free_result($result2);
            }
        }
    }

    /**
     * @param EventModel $event
     * @param $email
     * @param $templatePath
     * @param $titleText
     * @param $unsubscribeLink
     * @param string $acceptLink
     * @param string $declineLink
     * @param string $ics
     */
    public function sendNotification(
        &$event,
        $email,
        $templatePath,
        $titleText,
        $unsubscribeLink,
        $acceptLink = '',
        $declineLink = '',
        $ics = ''
    ) {
        $absFile = GeneralUtility::getFileAbsFileName($templatePath);
        $template = GeneralUtility::getUrl($absFile);
        $htmlTemplate = $this->markerBasedTemplateService->getSubpart($template, '###HTML###');
        $plainTemplate = $this->markerBasedTemplateService->getSubpart($template, '###PLAIN###');

        $switch = [];
        $rems = [];
        $wrapped = [];
        $event->getMarker($htmlTemplate, $switch, $rems, $wrapped, 'notification');

        $switch['###CURRENT_USER###'] = $this->getModifyingUser($template);

        $switch['###UNSUBSCRIBE_LINK###'] = $unsubscribeLink;
        $switch['###ACCEPT_LINK###'] = $acceptLink;
        $switch['###DECLINE_LINK###'] = $declineLink;
        $htmlTemplate = Functions::substituteMarkerArrayNotCached(
            $htmlTemplate,
            $switch,
            $rems,
            $wrapped
        );

        $switch = [];
        $rems = [];
        $wrapped = [];
        $event->getMarker($plainTemplate, $switch, $rems, $wrapped, 'notification');
        $switch['###UNSUBSCRIBE_LINK###'] = $unsubscribeLink;
        $switch['###ACCEPT_LINK###'] = $acceptLink;
        $switch['###DECLINE_LINK###'] = $declineLink;
        $plainTemplate = Functions::substituteMarkerArrayNotCached(
            $plainTemplate,
            $switch,
            $rems,
            $wrapped
        );

        $plainTemplate = $event->finish($plainTemplate);
        $htmlTemplate = $event->finish($htmlTemplate);

        $switch = [];
        $rems = [];
        $wrapped = [];
        $event->getMarker($titleText, $switch, $rems, $wrapped, 'title');

        $this->mailer->setSubject(Functions::substituteMarkerArrayNotCached(
            $titleText,
            $switch,
            $rems,
            $wrapped
        ));

        $this->sendEmail($email, $htmlTemplate, $plainTemplate);
    }

    /**
     * @param $oldEventDataArray
     * @param array $newEventDataArray
     */
    public function invite($oldEventDataArray, $newEventDataArray = [])
    {
        unset($oldEventDataArray['starttime'], $oldEventDataArray['endtime'], $newEventDataArray['starttime'], $newEventDataArray['endtime']);

        $event_new = $event_old = $this->modelObj->findEvent(
            $oldEventDataArray['uid'],
            'tx_cal_phpicalendar',
            $this->conf['pidList'],
            false,
            false,
            false,
            true,
            false
        );
        // no need for executing the same query twice, is it?
        // event_new = $this->modelObj->findEvent($oldEventDataArray['uid'],'tx_cal_phpicalendar', $this->conf['pidList'], false, false, false, true, false);
        if (count($newEventDataArray) > 0) {
            $event_new->updateWithPIVars(array_merge($oldEventDataArray, $newEventDataArray));
        }

        $this->startMailer();

        $modelObj = &Registry::Registry('basic', 'modelcontroller');
        $globalAttendeeArray = $modelObj->findEventAttendees($event_new->getUid());

        /** @var EventService $eventService */
        $eventService = $modelObj->getServiceObjByKey('cal_event_model', 'event', $event_new->getType());

        $this->setChairmanAsMailer($globalAttendeeArray);
        $template = $this->conf['view.']['event.']['meeting.']['onChangeTemplate'];
        $viewObj = &Registry::Registry('basic', 'viewcontroller');
        $eventArray = [
            $event_new
        ];

        foreach ($globalAttendeeArray as $serviceType => $attendeeArray) {
            /**
             * @var int $uid
             * @var AttendeeModel $attendee
             */
            foreach ($attendeeArray as $uid => $attendee) {
                if ($attendee->getFeUserId()) {
                    $eventService->updateAttendees($event_new->getUid());
                }
                if ($attendee->getEmail()) {
                    $conf = [];
                    $conf['parameter'] = $this->conf['view.']['event.']['meeting.']['statusViewPid'];
                    $conf['forceAbsoluteUrl'] = 1;
                    $urlParameters = [
                        'tx_cal_controller[view]' => 'meeting',
                        'tx_cal_controller[attendee]' => $attendee->getUid(),
                        'tx_cal_controller[uid]' => $event_old->getUid(),
                        'tx_cal_controller[status]' => 'accept',
                        'tx_cal_controller[sid]' => md5($event_old->getUid() . $attendee->getEmail() . $attendee->getCrdate())
                    ];
                    $conf['additionalParams'] .= GeneralUtility::implodeArrayForUrl('', $urlParameters);
                    $this->controller->cObj->typoLink('', $conf);
                    $acceptLink = $this->controller->cObj->lastTypoLinkUrl;

                    $urlParameters = [
                        'tx_cal_controller[view]' => 'meeting',
                        'tx_cal_controller[attendee]' => $attendee->getUid(),
                        'tx_cal_controller[uid]' => $event_old->getUid(),
                        'tx_cal_controller[status]' => 'decline',
                        'tx_cal_controller[sid]' => md5($event_old->getUid() . $attendee->getEmail() . $attendee->getCrdate())
                    ];
                    $conf['additionalParams'] .= GeneralUtility::implodeArrayForUrl('', $urlParameters);
                    $this->controller->cObj->typoLink('', $conf);
                    $declineLink = $this->controller->cObj->lastTypoLinkUrl;

                    $ics = $viewObj->drawIcs($eventArray, $this->conf['getdate'], false, $attendee->getEmail());

                    $title = $event_new->getTitle() . '.ics';
                    $title = strtr($title, [
                        ' ' => '',
                        ',' => '_'
                    ]);
                    $icsAttachmentFile = $this->createTempIcsFile($ics, $title);
                    $this->mailer->addAttachment($icsAttachmentFile);

                    if (count($newEventDataArray) > 0) {
                        $this->sendNotificationOfChanges(
                            $event_old,
                            $event_new,
                            $attendee->getEmail(),
                            $template,
                            '###TITLE###',
                            '',
                            $acceptLink,
                            $declineLink
                        );
                    } else {
                        $this->sendNotification(
                            $event_old,
                            $attendee->getEmail(),
                            $template,
                            '###TITLE###',
                            '',
                            $acceptLink,
                            $declineLink
                        );
                    }
                    unlink($icsAttachmentFile);
                }
            }
        }
    }

    /**
     * @param $globalAttendeeArray
     */
    public function setChairmanAsMailer(&$globalAttendeeArray)
    {
        foreach (array_keys($globalAttendeeArray) as $serviceType) {
            foreach (array_keys($globalAttendeeArray[$serviceType]) as $uid) {
                /** @var AttendeeModel $attendee */
                $attendee = &$globalAttendeeArray[$serviceType][$uid];
                if ($attendee->getAttendance() === 'CHAIR') {
                    $this->mailer->setFrom([
                        $attendee->getEmail() => $attendee->getName()
                    ]);
                    $this->mailer->setReplyTo([
                        $attendee->getEmail() => $attendee->getName()
                    ]);

                    // do not invite the chairman
                    unset($globalAttendeeArray[$serviceType][$uid]);
                    break;
                }
            }
        }
    }

    public function startMailer()
    {
        $this->mailer = $mail = new MailMessage();

        if (GeneralUtility::validEmail($this->conf['view.']['event.']['notify.']['emailAddress'])) {
            $this->mailer->setFrom([
                $this->conf['view.']['event.']['notify.']['emailAddress'] => $this->conf['view.']['event.']['notify.']['fromName']
            ]);
        }

        if (GeneralUtility::validEmail($this->conf['view.']['event.']['notify.']['emailReplyAddress'])) {
            $this->mailer->setReplyTo([
                $this->conf['view.']['event.']['notify.']['emailReplyAddress'] => $this->conf['view.']['event.']['notify.']['replyToName']
            ]);
        }
        $this->mailer->getHeaders()->addTextHeader(
            'Organization',
            $this->conf['view.']['event.']['notify.']['organisation']
        );
    }

    /**
     * @param $email
     * @param $htmlTemplate
     * @param $plainTemplate
     */
    public function sendEmail($email, $htmlTemplate, $plainTemplate)
    {
        $this->controller->finish($htmlTemplate);
        $this->controller->finish($plainTemplate);
        $plainTemplate = str_replace('&nbsp;', ' ', strip_tags($plainTemplate));

        $this->mailer->theParts['html']['content'] = $htmlTemplate;
        $this->mailer->theParts['html']['path'] = '';
        $this->mailer->extractMediaLinks();
        $this->mailer->extractHyperLinks();
        $this->mailer->fetchHTMLMedia();
        $this->mailer->substMediaNamesInHTML(0); // 0 = relative
        $this->mailer->substHREFsInHTML();

        $this->mailer->setHTML($this->mailer->encodeMsg($this->mailer->theParts['html']['content']));

        $this->mailer->substHREFsInHTML();

        $this->mailer->setPlain(strip_tags($plainTemplate));
        $this->mailer->setHeaders();
        $this->mailer->setContent();
        $this->mailer->setRecipient($email);
        $this->mailer->sendtheMail();
    }

    /**
     * @param $content
     * @param $filename
     * @return string
     */
    public function createTempIcsFile($content, $filename): string
    {
        $theDestFile = GeneralUtility::getFileAbsFileName('uploads/tx_cal/' . $filename);
        $fh = fopen($theDestFile, 'w');
        fwrite($fh, $content);
        fclose($fh);
        return $theDestFile;
    }
}
