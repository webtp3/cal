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
use TYPO3\CMS\Cal\Model\CalendarModel;
use TYPO3\CMS\Cal\Utility\Functions;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * A service which renders a form to create / edit a phpicalendar event.
 */
class CreateCalendarView extends FeEditingBaseView
{
    /**
     * Draws a create calendar form.
     *
     * @param string Comma separated list of pids.
     * @param CalendarModel A location or organizer object to be updated
     * @return string HTML output.
     */
    public function drawCreateCalendar($pidList, $object): string
    {
        $this->objectString = 'calendar';
        if (is_object($object)) {
            $this->conf['view'] = 'edit_' . $this->objectString;
        } else {
            $this->conf['view'] = 'create_' . $this->objectString;
            unset($this->controller->piVars['uid']);
        }

        $allRequiredFieldsAreFilled = $this->checkRequiredFields($requiredFieldsSims);

        $sims = [];
        $rems = [];
        $wrapped = [];

        // If an event has been passed on the form is a edit form
        if (is_object($object) && $object->isUserAllowedToEdit()) {
            $this->isEditMode = true;
            $this->object = $object;
        } else {
            $a = [];
            $this->object = new CalendarModel($a, '');
            $allValues = array_merge($this->getDefaultValues(), $this->controller->piVars);
            $this->object->updateWithPIVars($allValues);
        }

        $constrainFieldSims = [];
        $noComplains = $this->checkContrains($constrainFieldSims);

        if ($allRequiredFieldsAreFilled && $noComplains) {
            $this->conf['lastview'] = $this->controller->extendLastView();

            $this->conf['view'] = 'confirm_' . $this->objectString;
            return $this->controller->confirmCalendar();
        }

        // Needed for translation options:
        $this->serviceName = 'cal_' . $this->objectString . '_model';
        $this->table = 'tx_cal_' . $this->objectString;

        $page = Functions::getContent($this->conf['view.']['create_calendar.']['template']);
        if ($page === '') {
            return '<h3>calendar: no create calendar template file found:</h3>' . $this->conf['view.']['create_calendar.']['template'];
        }

        if (is_object($object) && !$object->isUserAllowedToEdit()) {
            return $this->controller->pi_getLL('l_not_allowed_edit') . $this->objectString;
        }
        if (!is_object($object) && !$this->rightsObj->isAllowedTo('create', $this->objectString, '')) {
            return $this->controller->pi_getLL('l_not_allowed_create') . $this->objectString;
        }

        $this->getTemplateSubpartMarker($page, $sims, $rems, $wrapped);

        $page = Functions::substituteMarkerArrayNotCached($page, [], $rems, $wrapped);
        $page = Functions::substituteMarkerArrayNotCached($page, $sims, [], []);

        $sims = [];
        $rems = [];
        $wrapped = [];

        $this->getTemplateSingleMarker($page, $sims, $rems, $this->conf['view']);
        $sims['###ACTION_URL###'] = htmlspecialchars($this->controller->pi_linkTP_keepPIvars_url([
            'formCheck' => '1'
        ]));
        $page = Functions::substituteMarkerArrayNotCached($page, [], $rems, $wrapped);
        $page = Functions::substituteMarkerArrayNotCached($page, $sims, [], []);
        return Functions::substituteMarkerArrayNotCached($page, $requiredFieldsSims, [], []);
    }

    /**
     * @param $template
     * @param $sims
     * @param $rems
     * @param $view
     */
    public function getOwnerMarker(& $template, & $sims, & $rems, $view)
    {
        $sims['###OWNER###'] = '';
        if ($this->isAllowed('owner')) {
            $cal_owner_user = '';
            $allowedUsers = GeneralUtility::trimExplode(
                ',',
                $this->conf['rights.']['allowedUsers'],
                1
            );
            $selectedUsers = $this->object->getOwner('fe_users');
            $result = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
                '*',
                'fe_users',
                'pid in (' . $this->conf['pidList'] . ')'
            );
            if ($result) {
                while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
                    if (!empty($allowedUsers) && in_array($row['uid'], $allowedUsers, true)) {
                        if (in_array($row['uid'], $selectedUsers, true)) {
                            $cal_owner_user .= '<input type="checkbox" value="u_' . $row['uid'] . '_' . $row['username'] . '" checked="checked" name="tx_cal_controller[owner][]" />' . $row['username'] . '<br />';
                        } else {
                            $cal_owner_user .= '<input type="checkbox" value="u_' . $row['uid'] . '_' . $row['username'] . '"  name="tx_cal_controller[owner][]"/>' . $row['username'] . '<br />';
                        }
                    } elseif (empty($allowedUsers)) {
                        if (in_array($row['uid'], $selectedUsers, true)) {
                            $cal_owner_user .= '<input type="checkbox" value="u_' . $row['uid'] . '_' . $row['username'] . '" checked="checked" name="tx_cal_controller[owner][]" />' . $row['username'] . '<br />';
                        } else {
                            $cal_owner_user .= '<input type="checkbox" value="u_' . $row['uid'] . '_' . $row['username'] . '"  name="tx_cal_controller[owner][]"/>' . $row['username'] . '<br />';
                        }
                    }
                }
                $GLOBALS['TYPO3_DB']->sql_free_result($result);
            }
            $allowedGroups = GeneralUtility::trimExplode(
                ',',
                $this->conf['rights.']['allowedGroups'],
                1
            );
            $selectedGroups = $this->object->getOwner('fe_groups');
            $result = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
                '*',
                'fe_groups',
                'pid in (' . $this->conf['pidList'] . ')'
            );
            if ($result) {
                while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
                    if (!empty($allowedGroups) && in_array($row['uid'], $allowedGroups, true)) {
                        if (in_array($row['uid'], $selectedGroups, true)) {
                            $cal_owner_user .= '<input type="checkbox" value="g_' . $row['uid'] . '_' . $row['title'] . '" checked="checked" name="tx_cal_controller[owner][]" />' . $row['title'] . '<br />';
                        } else {
                            $cal_owner_user .= '<input type="checkbox" value="g_' . $row['uid'] . '_' . $row['title'] . '"  name="tx_cal_controller[owner][]"/>' . $row['title'] . '<br />';
                        }
                    } elseif (empty($allowedGroups)) {
                        if (in_array($row['uid'], $selectedGroups, true)) {
                            $cal_owner_user .= '<input type="checkbox" value="g_' . $row['uid'] . '_' . $row['title'] . '" checked="checked" name="tx_cal_controller[owner][]" />' . $row['title'] . '<br />';
                        } else {
                            $cal_owner_user .= '<input type="checkbox" value="g_' . $row['uid'] . '_' . $row['title'] . '"  name="tx_cal_controller[owner][]"/>' . $row['title'] . '<br />';
                        }
                    }
                }
                $GLOBALS['TYPO3_DB']->sql_free_result($result);
            }
            $sims['###OWNER###'] = $this->applyStdWrap($cal_owner_user, 'owner_stdWrap');
        }
    }

    /**
     * @param $template
     * @param $sims
     * @param $rems
     * @param $view
     */
    public function getActivateFreeAndBusyMarker(& $template, & $sims, & $rems, $view)
    {
        $sims['###ACTIVATE_FREEANDBUSY###'] = '';
        if ($this->isEditMode && $this->rightsObj->isAllowedTo('edit', $this->objectString, 'activateFreeAndBusy')) {
            $activate = '';
            if ($this->conf['rights.']['edit.'][$this->objectString . '.']['fields.']['activateFreeAndBusy.']['default'] || $this->object->isActivateFreeAndBusy()) {
                $activate = ' checked="checked" ';
            }
            $sims['###ACTIVATE_FREEANDBUSY###'] = $this->applyStdWrap($activate, 'activateFreeAndBusy_stdWrap');
        } elseif (!$this->isEditMode && $this->rightsObj->isAllowedTo(
            'create',
            $this->objectString,
            'activateFreeAndBusy'
            )) {
            $activate = '';
            if ($this->conf['rights.']['create.'][$this->objectString . '.']['fields.']['activateFreeAndBusy.']['default'] || $this->object->isActivateFreeAndBusy()) {
                $activate = ' checked="checked" ';
            }
            $sims['###ACTIVATE_FREEANDBUSY###'] = $this->applyStdWrap($activate, 'activateFreeAndBusy_stdWrap');
        }
    }

    /**
     * @param $template
     * @param $sims
     * @param $rems
     * @param $view
     */
    public function getFreeAndBusyUserMarker(& $template, & $sims, & $rems, $view)
    {
        $sims['###FREEANDBUSYUSER###'] = '';
        if ($this->isAllowed('freeAndBusyUser')) {
            $freeAndBusyUser = '';
            $allowedUsers = GeneralUtility::trimExplode(
                ',',
                $this->conf['rights.']['allowedUsers'],
                1
            );
            $selectedUsers = $this->object->getFreeAndBusyUser('fe_users');
            $result = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
                '*',
                'fe_users',
                'pid in (' . $this->conf['pidList'] . ')'
            );
            if ($result) {
                while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
                    if (!empty($allowedUsers) && in_array($row['uid'], $allowedUsers, true)) {
                        if (in_array($row['uid'], $selectedUsers, true)) {
                            $freeAndBusyUser .= '<input type="checkbox" value="u_' . $row['uid'] . '_' . $row['username'] . '" checked="checked" name="tx_cal_controller[freeAndBusyUser][]" />' . $row['username'] . '<br />';
                        } else {
                            $freeAndBusyUser .= '<input type="checkbox" value="u_' . $row['uid'] . '_' . $row['username'] . '"  name="tx_cal_controller[freeAndBusyUser][]"/>' . $row['username'] . '<br />';
                        }
                    } elseif (empty($allowedUsers)) {
                        if (in_array($row['uid'], $selectedUsers, true)) {
                            $freeAndBusyUser .= '<input type="checkbox" value="u_' . $row['uid'] . '_' . $row['username'] . '" checked="checked" name="tx_cal_controller[freeAndBusyUser][]" />' . $row['username'] . '<br />';
                        } else {
                            $freeAndBusyUser .= '<input type="checkbox" value="u_' . $row['uid'] . '_' . $row['username'] . '"  name="tx_cal_controller[freeAndBusyUser][]"/>' . $row['username'] . '<br />';
                        }
                    }
                }
                $GLOBALS['TYPO3_DB']->sql_free_result($result);
            }
            $sims['###FREEANDBUSYUSER###'] = $this->applyStdWrap($freeAndBusyUser, 'freeAndBusyUser_stdWrap');
        }
    }

    /**
     * @param $template
     * @param $sims
     * @param $rems
     * @param $view
     */
    public function getCalendarTypeMarker(& $template, & $sims, & $rems, $view)
    {
        $sims['###CALENDARTYPE###'] = '';
        $calendarTypeArray = [
            $this->controller->pi_getLL('l_calendar_type0'),
            $this->controller->pi_getLL('l_calendar_exturl'),
            $this->controller->pi_getLL('l_calendar_icsfile')
        ];
        if ($this->isAllowed('calendarType')) {
            $calendarType = '';
            foreach ($calendarTypeArray as $index => $title) {
                if ($this->object->getCalendarType() === $index) {
                    $calendarType .= '<option value="' . $index . '" selected="selected">' . $title . '</option>';
                } else {
                    $calendarType .= '<option value="' . $index . '">' . $title . '</option>';
                }
            }

            $sims['###CALENDARTYPE###'] = $this->applyStdWrap($calendarType, 'calendarType_stdWrap');
        }
    }

    /**
     * @param $template
     * @param $sims
     * @param $rems
     * @param $view
     */
    public function getExtUrlMarker(& $template, & $sims, & $rems, $view)
    {
        $sims['###EXTURL###'] = '';
        if ($this->isAllowed('exturl')) {
            $this->object->getExtUrlMarker($template, $sims, $rems, $view);
        }
    }

    /**
     * @param $template
     * @param $sims
     * @param $rems
     * @param $view
     */
    public function getRefreshMarker(& $template, & $sims, & $rems, $view)
    {
        $sims['###REFRESH###'] = '';
        if ($this->isAllowed('refresh')) {
            $this->object->getRefreshMarker($template, $sims, $rems, $view);
        }
    }

    /**
     * @param $template
     * @param $sims
     * @param $rems
     * @param $wrapped
     */
    public function getFormStartMarker(& $template, & $sims, & $rems, & $wrapped)
    {
        $temp = $this->markerBasedTemplateService->getSubpart($template, '###FORM_START###');
        $temp_sims = [];
        $temp_sims['###L_CREATE_CALENDAR###'] = $this->controller->pi_getLL('l_create_calendar');
        $temp_sims['###UID###'] = '';
        if ($this->isEditMode) {
            $temp_sims['###L_CREATE_CALENDAR###'] = $this->controller->pi_getLL('l_edit_calendar');
            $temp_sims['###UID###'] = $this->object->getUid();
        }
        $temp_sims['###TYPE###'] = 'tx_cal_calendar';

        $rems['###FORM_START###'] = Functions::substituteMarkerArrayNotCached(
            $temp,
            $temp_sims,
            [],
            []
        );
    }
}
