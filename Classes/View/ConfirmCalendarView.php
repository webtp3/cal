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

/**
 * A service which renders a form to create / edit a phpicalendar event.
 */
class ConfirmCalendarView extends FeEditingBaseView
{
    /**
     * Draws a create calendar form.
     *
     * @param string        Comma separated list of pids.
     * @param object        A location or organizer object to be updated
     * @return string HTML output.
     */
    public function drawConfirmCalendar(): string
    {
        $this->objectString = 'calendar';
        $this->isConfirm = true;
        unset($this->controller->piVars['formCheck']);
        $page = Functions::getContent($this->conf['view.']['confirm_calendar.']['template']);
        if ($page === '') {
            return '<h3>calendar: no create calendar template file found:</h3>' . $this->conf['view.']['confirm_calendar.']['template'];
        }

        $lastViewParams = $this->controller->shortenLastViewAndGetTargetViewParameters();

        if ($lastViewParams['view'] === 'edit_calendar') {
            $this->isEditMode = true;
        }

        $fakeArray = [];
        $this->object = new CalendarModel($fakeArray, '');
        $this->object->updateWithPIVars($this->controller->piVars);
        $rems = [];
        $sims = [];

        $sims['###UID###'] = $this->conf['uid'];
        $sims['###TYPE###'] = $this->conf['type'];
        $sims['###VIEW###'] = 'save_calendar';
        $sims['###LASTVIEW###'] = $this->controller->extendLastView();
        $sims['###L_CONFIRM_CALENDAR###'] = $this->controller->pi_getLL('l_confirm_calendar');
        $sims['###L_SAVE###'] = $this->controller->pi_getLL('l_save');
        $sims['###L_CANCEL###'] = $this->controller->pi_getLL('l_cancel');
        $sims['###ACTION_URL###'] = htmlspecialchars($this->controller->pi_linkTP_keepPIvars_url([
            'view' => 'save_calendar'
        ]));

        $this->getTemplateSubpartMarker($page, $sims, $rems, $this->conf['view']);
        $page = Functions::substituteMarkerArrayNotCached($page, [], $rems, []);
        $page = Functions::substituteMarkerArrayNotCached($page, $sims, [], []);

        $sims = [];
        $rems = [];
        $this->getTemplateSingleMarker($page, $sims, $rems, $this->conf['view']);
        $page = Functions::substituteMarkerArrayNotCached($page, [], $rems, []);
        $page = Functions::substituteMarkerArrayNotCached($page, $sims, [], []);
        return Functions::substituteMarkerArrayNotCached($page, $sims, [], []);
    }

    /**
     * @param $template
     * @param $sims
     * @param $rems
     */
    public function getTitleMarker(& $template, & $sims, & $rems)
    {
        $sims['###TITLE###'] = '';
        if ($this->isAllowed('title')) {
            $sims['###TITLE###'] = $this->applyStdWrap($this->object->getTitle(), 'title_stdWrap');
            $sims['###TITLE_VALUE###'] = htmlspecialchars($this->object->getTitle());
        }
    }

    /**
     * @param $template
     * @param $sims
     * @param $rems
     */
    public function getCalendarTypeMarker(& $template, & $sims, & $rems)
    {
        $sims['###CALENDARTYPE###'] = '';
        $calendarTypeArray = [
            $this->controller->pi_getLL('l_calendar_type0'),
            $this->controller->pi_getLL('l_calendar_exturl'),
            $this->controller->pi_getLL('l_calendar_icsfile')
        ];
        if ($this->isAllowed('calendarType')) {
            $sims['###CALENDARTYPE###'] = $this->applyStdWrap(
                $calendarTypeArray[$this->object->getCalendarType()],
                'calendarType_stdWrap'
            );
            $sims['###CALENDARTYPE_VALUE###'] = $this->object->getCalendarType();
        }
    }

    /**
     * @param $template
     * @param $sims
     * @param $rems
     */
    public function getExtUrlMarker(& $template, & $sims, & $rems)
    {
        $sims['###EXTURL###'] = '';
        if ($this->object->getCalendarType() == 1 && $this->isAllowed('calendarType')) {
            $sims['###EXTURL###'] = $this->applyStdWrap($this->object->getExtUrl(), 'exturl_stdWrap');
            $sims['###EXTURL_VALUE###'] = $this->object->getExtUrl();
        }
    }

    /**
     * @param $template
     * @param $sims
     * @param $rems
     */
    public function getRefreshMarker(& $template, & $sims, & $rems)
    {
        $sims['###REFRESH###'] = '';
        if ($this->object->getCalendarType() > 0 && $this->isAllowed('calendarType')) {
            $sims['###REFRESH###'] = $this->applyStdWrap($this->object->getRefresh(), 'refresh_stdWrap');
            $sims['###REFRESH_VALUE###'] = $this->object->getRefresh();
        }
    }

    /**
     * @param $template
     * @param $sims
     * @param $rems
     */
    public function getActivateFreeAndBusyMarker(& $template, & $sims, & $rems)
    {
        $sims['###ACTIVATE_FREEANDBUSY###'] = '';
        if ($this->isAllowed('activateFreeAndBusy')) {
            $activateFreeAndBusy = 'false';
            if ($this->object->isActivateFreeAndBusy()) {
                $activateFreeAndBusy = 'true';
            }
            $sims['###ACTIVATE_FREEANDBUSY###'] = $this->applyStdWrap(
                $activateFreeAndBusy,
                'activateFreeAndBusy_stdWrap'
            );
            $sims['###ACTIVATE_FREEANDBUSY_VALUE###'] = $activateFreeAndBusy === 'true' ? 1 : 0;
        }
    }

    /**
     * @param $template
     * @param $sims
     * @param $rems
     */
    public function getFreeAndBusyUserMarker(& $template, & $sims, & $rems)
    {
        $sims['###FREEANDBUSYUSER###'] = '';
        if (is_array($this->controller->piVars['freeAndBusyUser']) && $this->isAllowed('freeAndBusyUser')) {
            $displaylist = [];
            $idlist = [];
            foreach ($this->controller->piVars['freeAndBusyUser'] as $value) {
                preg_match('/(^[a-z])_([0-9]+)_(.*)/', $value, $idname);
                if ($idname[1] === 'u' || $idname[1] === 'g') {
                    $idlist[] = $idname[1] . '_' . $idname[2];
                    $displaylist[] = $idname[3];
                }
            }
            $sims['###FREEANDBUSYUSER###'] = $this->applyStdWrap(implode(',', $displaylist), 'freeAndBusyUser_stdWrap');
            $sims['###FREEANDBUSYUSER_VALUE###'] = htmlspecialchars(implode(',', $idlist));
        }
    }

    /**
     * @param $template
     * @param $sims
     * @param $rems
     */
    public function getOwnerMarker(& $template, & $sims, & $rems)
    {
        $sims['###OWNER###'] = '';
        if (is_array($this->controller->piVars['owner']) && $this->isAllowed('owner')) {
            $ownerdisplaylist = [];
            $ownerids = [];
            foreach ($this->controller->piVars['owner'] as $value) {
                preg_match('/(^[a-z])_([0-9]+)_(.*)/', $value, $idname);
                if ($idname[1] === 'u' || $idname[1] === 'g') {
                    $ownerids[] = $idname[1] . '_' . $idname[2];
                    $ownerdisplaylist[] = $idname[3];
                }
            }
            $sims['###OWNER###'] = $this->applyStdWrap(implode(',', $ownerdisplaylist), 'owner_stdWrap');
            $sims['###OWNER_VALUE###'] = htmlspecialchars(implode(',', $ownerids));
        }
    }
}
