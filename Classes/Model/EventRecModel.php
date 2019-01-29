<?php

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
use TYPO3\CMS\Cal\Utility\Functions;
use TYPO3\CMS\Cal\Utility\Registry;

/**
 * A concrete model for the calendar.
 */
class EventRecModel extends Model
{
    /**
     * @var EventModel
     */
    public $parentEvent;

    /**
     * @var CalDate
     */
    public $start;

    /**
     * @var CalDate
     */
    public $end;

    /**
     * @var array
     */
    public $myMarkerCache = [];

    /**
     * EventRecModel constructor.
     * @param EventModel $event
     * @param $start
     * @param $end
     */
    public function __construct($event, $start, $end)
    {
        parent::__construct($event->serviceKey);
        $this->parentEvent = &$event;
        $this->setStart($start);
        $this->setEnd($end);
        $this->row = &$event->row;
    }

    /**
     * @param $piVars
     */
    public function updateWithPiVars(&$piVars)
    {
        $this->parentEvent->updateWithPIVars($piVars);
        $this->parentEvent->markerCache = [];
    }

    /**
     * @return mixed
     */
    public function cloneEvent()
    {
        return $this->parentEvent->cloneEvent();
    }

    /**
     * Gets the location of the event.
     * Location does not exist in the default
     * model, only in calexampl3.
     *
     * @return string location.
     */
    public function getLocation(): string
    {
        return $this->parentEvent->getLocation();
    }

    /**
     * @return string
     */
    public function getOrganizer(): string
    {
        return $this->parentEvent->getOrganizer();
    }

    /**
     * @return int
     */
    public function getLocationId(): int
    {
        return $this->parentEvent->getLocationId();
    }

    /**
     * @return int
     */
    public function getOrganizerId(): int
    {
        return $this->parentEvent->getOrganizerId();
    }

    /**
     * Gets the teaser of the event.
     *
     *
     * @return string teaser.
     */
    public function getTeaser(): string
    {
        return $this->parentEvent->getTeaser();
    }

    /**
     * @param $view
     * @return mixed
     */
    public function getLocationLink($view)
    {
        return $this->parentEvent->getLocationLink($view);
    }

    /**
     * @param $view
     * @return string
     */
    public function getOrganizerLink($view): string
    {
        return $this->parentEvent->getOrganizerLink($view);
    }

    /**
     * Returns the headerstyle name
     */
    public function getHeaderStyle(): string
    {
        return $this->parentEvent->getHeaderStyle();
    }

    /**
     * Returns the bodystyle name
     */
    public function getBodyStyle(): string
    {
        return $this->parentEvent->getBodyStyle();
    }

    /**
     * Gets the createUserId of the event.
     *
     * @return string create user id.
     */
    public function getCreateUserId(): string
    {
        return $this->parentEvent->getCreateUserId();
    }

    /**
     * @return string
     */
    public function getTimezone(): string
    {
        return $this->parentEvent->getTimezone();
    }

    /**
     * @return string
     */
    public function renderEventForOrganizer(): string
    {
        return $this->renderEventFor('ORGANIZER');
    }

    /**
     * @return string
     */
    public function renderEventForLocation(): string
    {
        return $this->renderEventFor('LOCATION');
    }

    /**
     * @return string
     */
    public function renderEventForDay(): string
    {
        return $this->renderEventFor('DAY');
    }

    /**
     * @return string
     */
    public function renderEventForWeek(): string
    {
        return $this->renderEventFor('WEEK');
    }

    /**
     * @return string
     */
    public function renderEventForAllDay(): string
    {
        return $this->renderEventFor('ALLDAY');
    }

    /**
     * @return string
     */
    public function renderEventForMonth(): string
    {
        if ($this->isAllDay()) {
            return $this->renderEventFor('MONTH_ALLDAY');
        }
        return $this->renderEventFor('MONTH');
    }

    /**
     * @return string
     */
    public function renderEventForMiniMonth(): string
    {
        if ($this->isAllDay()) {
            return $this->renderEventFor('MONTH_MINI_ALLDAY');
        }
        return $this->renderEventFor('MONTH_MINI');
    }

    /**
     * @return string
     */
    public function renderEventForYear(): string
    {
        return $this->renderEventFor('year');
    }

    /**
     * @return string
     */
    public function renderEvent(): string
    {
        return $this->fillTemplate('###TEMPLATE_PHPICALENDAR_EVENT###');
    }

    /**
     * @param string $subpartSuffix
     * @return string
     */
    public function renderEventForList($subpartSuffix = 'LIST_ODD'): string
    {
        return $this->renderEventFor($subpartSuffix);
    }

    /**
     * @param $viewType
     * @param string $subpartSuffix
     * @return string
     */
    public function renderEventFor($viewType, $subpartSuffix = ''): string
    {
        if ((int)$this->parentEvent->conf['view.']['freeAndBusy.']['enable'] === 1) {
            $viewType .= '_FNB';
        }
        if (substr(
                $viewType,
                -6
            ) !== 'ALLDAY' && ($this->isAllDay() || $this->getStart()->format('%Y%m%d') !== $this->getEnd()->format('%Y%m%d'))) {
            $subpartSuffix .= 'ALLDAY';
        }
        $hookObjectsArr = Functions::getHookObjectsArray(
            'tx_cal_phpicalendar_rec_model',
            'eventModelClass',
            'model'
        );

        foreach ($hookObjectsArr as $hookObj) {
            if (method_exists($hookObj, 'preFillTemplate')) {
                $hookObj->preFillTemplate($this, $viewType, $subpartSuffix);
            }
        }
        return $this->fillTemplate('###TEMPLATE_PHPICALENDAR_EVENT_' . strtoupper($viewType) . ($subpartSuffix ? '_' : '') . $subpartSuffix . '###');
    }

    /**
     * @return string
     */
    public function renderEventPreview(): string
    {
        $this->parentEvent->isPreview = true;
        return $this->fillTemplate('###TEMPLATE_PHPICALENDAR_EVENT_PREVIEW###');
    }

    /**
     * @return string
     */
    public function renderTomorrowsEvent(): string
    {
        $this->parentEvent->isTomorrow = true;
        return $this->fillTemplate('###TEMPLATE_PHPICALENDAR_EVENT_TOMORROW###');
    }

    /**
     * @param $subpartMarker
     * @return string
     */
    public function fillTemplate($subpartMarker): string
    {
        $templatePath = $this->parentEvent->conf['view.']['event.']['eventModelTemplate'];

        $page = Functions::getContent($templatePath);

        if ($page === '') {
            return '<h3>calendar: no event model template file found:</h3>' . $templatePath;
        }
        $page = $this->markerBasedTemplateService->getSubpart($page, $subpartMarker);
        if (!$page) {
            return 'could not find the >' . str_replace(
                    '###',
                    '',
                    $subpartMarker
                ) . '< subpart-marker in ' . $templatePath;
        }
        $rems = [];
        $sims = [];
        $wrapped = [];
        $this->getMarker(
            $page,
            $sims,
            $rems,
            $wrapped,
            $this->parentEvent->conf['alternateRenderingView'] ?: ''
        );
        return $this->parentEvent->finish(Functions::substituteMarkerArrayNotCached(
            $page,
            $sims,
            $rems,
            $wrapped
        ));
    }

    /**
     * @param $template
     * @param $sims
     * @param $rems
     * @param $wrapped
     * @param $view
     * @return mixed
     */
    public function getSubscriptionMarker(& $template, & $sims, & $rems, &$wrapped, $view)
    {
        return $this->parentEvent->getSubscriptionMarker($template, $sims, $rems, $wrapped, $view);
    }

    /**
     * @param $template
     * @param $sims
     * @param $rems
     * @param $wrapped
     * @param $view
     */
    public function getStartAndEndMarker(& $template, & $sims, & $rems, & $wrapped, $view)
    {
        $this->parentEvent->initLocalCObject();
        array_merge($this->parentEvent->local_cObj->data, $this->getAdditionalValuesAsArray());
        $eventStart = $this->getStart();
        $eventEnd = $this->getEnd();
        if ($eventStart->equals($eventEnd)) {
            $sims['###STARTTIME_LABEL###'] = '';
            $sims['###ENDTIME_LABEL###'] = '';
            $sims['###STARTTIME###'] = '';
            $sims['###ENDTIME###'] = '';
            $this->parentEvent->local_cObj->setCurrentVal($eventStart->format($this->parentEvent->conf['view.'][$view . '.']['event.']['dateFormat']));
            $sims['###STARTDATE###'] = $this->parentEvent->local_cObj->cObjGetSingle(
                $this->parentEvent->conf['view.'][$view . '.']['event.']['startdate'],
                $this->parentEvent->conf['view.'][$view . '.']['event.']['startdate.']
            );
            $sims['###STARTDATE_LABEL###'] = $this->controller->pi_getLL('l_event_allday');
            if ((int)$this->parentEvent->conf['view.'][$view . '.']['event.']['dontShowEndDateIfEqualsStartDateAllday'] === 1) {
                $sims['###ENDDATE###'] = '';
                $sims['###ENDDATE_LABEL###'] = '';
            } else {
                $this->parentEvent->local_cObj->setCurrentVal($eventEnd->format($this->parentEvent->conf['view.'][$view . '.']['event.']['dateFormat']));
                $sims['###ENDDATE###'] = $this->parentEvent->local_cObj->cObjGetSingle(
                    $this->parentEvent->conf['view.'][$view . '.']['event.']['enddate'],
                    $this->parentEvent->conf['view.'][$view . '.']['event.']['enddate.']
                );
                $sims['###ENDDATE_LABEL###'] = $this->controller->pi_getLL('l_event_enddate');
            }
        } else {
            if ($this->isAllDay()) {
                $sims['###STARTTIME_LABEL###'] = '';
                $sims['###STARTTIME###'] = '';
            } else {
                $sims['###STARTTIME_LABEL###'] = $this->controller->pi_getLL('l_event_starttime');
                $this->parentEvent->local_cObj->setCurrentVal($eventStart->format($this->parentEvent->conf['view.'][$view . '.']['event.']['timeFormat']));
                $sims['###STARTTIME###'] = $this->parentEvent->local_cObj->cObjGetSingle(
                    $this->parentEvent->conf['view.'][$view . '.']['event.']['starttime'],
                    $this->parentEvent->conf['view.'][$view . '.']['event.']['starttime.']
                );
            }
            if ($this->isAllDay()) {
                $sims['###ENDTIME_LABEL###'] = '';
                $sims['###ENDTIME###'] = '';
            } else {
                $sims['###ENDTIME_LABEL###'] = $this->controller->pi_getLL('l_event_endtime');
                $this->parentEvent->local_cObj->setCurrentVal($eventEnd->format($this->parentEvent->conf['view.'][$view . '.']['event.']['timeFormat']));
                $sims['###ENDTIME###'] = $this->parentEvent->local_cObj->cObjGetSingle(
                    $this->parentEvent->conf['view.'][$view . '.']['event.']['endtime'],
                    $this->parentEvent->conf['view.'][$view . '.']['event.']['endtime.']
                );
            }

            $this->parentEvent->local_cObj->setCurrentVal($eventStart->format($this->parentEvent->conf['view.'][$view . '.']['event.']['dateFormat']));
            $sims['###STARTDATE###'] = $this->parentEvent->local_cObj->cObjGetSingle(
                $this->parentEvent->conf['view.'][$view . '.']['event.']['startdate'],
                $this->parentEvent->conf['view.'][$view . '.']['event.']['startdate.']
            );
            if ($this->parentEvent->conf['view.'][$view . '.']['event.']['dontShowEndDateIfEqualsStartDate'] && $eventEnd->format('%Y%m%d') === $eventStart->format('%Y%m%d')) {
                $sims['###STARTDATE_LABEL###'] = $this->controller->pi_getLL('l_date');
                $sims['###ENDDATE_LABEL###'] = '';
                $sims['###ENDDATE###'] = '';
            } else {
                $sims['###STARTDATE_LABEL###'] = $this->controller->pi_getLL('l_event_startdate');
                $sims['###ENDDATE_LABEL###'] = $this->controller->pi_getLL('l_event_enddate');
                $this->parentEvent->local_cObj->setCurrentVal($eventEnd->format($this->conf['view.'][$view . '.']['event.']['dateFormat']));
                $sims['###ENDDATE###'] = $this->parentEvent->local_cObj->cObjGetSingle(
                    $this->parentEvent->conf['view.'][$view . '.']['event.']['enddate'],
                    $this->parentEvent->conf['view.'][$view . '.']['event.']['enddate.']
                );
            }
        }
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->parentEvent->getTitle();
    }

    /**
     * @param $template
     * @param $sims
     * @param $rems
     * @param $wrapped
     * @param $view
     */
    public function getTitleMarker(& $template, & $sims, & $rems, & $wrapped, $view)
    {
        $this->parentEvent->getTitleMarker($template, $sims, $rems, $wrapped, $view);
    }

    /**
     * @param $template
     * @param $sims
     * @param $rems
     * @param $wrapped
     * @param $view
     * @return mixed
     */
    public function getTitleFnbMarker(& $template, & $sims, & $rems, & $wrapped, $view)
    {
        return $this->parentEvent->getTitleFnbMarker($template, $sims, $rems, $wrapped, $view);
    }

    /**
     * @param $template
     * @param $sims
     * @param $rems
     * @param $wrapped
     * @param $view
     * @return mixed
     */
    public function getOrganizerMarker(& $template, & $sims, & $rems, & $wrapped, $view)
    {
        return $this->parentEvent->getOrganizerMarker($template, $sims, $rems, $wrapped, $view);
    }

    /**
     * @param $template
     * @param $sims
     * @param $rems
     * @param $wrapped
     * @param $view
     * @return mixed
     */
    public function getLocationMarker(& $template, & $sims, & $rems, & $wrapped, $view)
    {
        return $this->parentEvent->getLocationMarker($template, $sims, $rems, $wrapped, $view);
    }

    /**
     * @param $template
     * @param $sims
     * @param $rems
     * @param $wrapped
     * @param $view
     * @return mixed
     */
    public function getTeaserMarker(& $template, & $sims, & $rems, & $wrapped, $view)
    {
        return $this->parentEvent->getTeaserMarker($template, $sims, $rems, $wrapped, $view);
    }

    /**
     * @param $template
     * @param $sims
     * @param $rems
     * @param $wrapped
     * @param $view
     * @return mixed
     */
    public function getIcsLinkMarker(& $template, & $sims, & $rems, & $wrapped, $view)
    {
        return $this->parentEvent->getIcsLinkMarker($template, $sims, $rems, $wrapped, $view);
    }

    /**
     * @param $template
     * @param $sims
     * @param $rems
     * @param $wrapped
     * @param $view
     * @return mixed
     */
    public function getCategoryMarker(& $template, & $sims, & $rems, & $wrapped, $view)
    {
        return $this->parentEvent->getCategoryMarker($template, $sims, $rems, $wrapped, $view);
    }

    /**
     * @param $template
     * @param $sims
     * @param $rems
     * @param $wrapped
     * @param $view
     * @return mixed
     */
    public function getCategoryLinkMarker(& $template, & $sims, & $rems, & $wrapped, $view)
    {
        return $this->parentEvent->getCategoryLinkMarker($template, $sims, $rems, $wrapped, $view);
    }

    /**
     * @param $template
     * @param $sims
     * @param $rems
     * @param $wrapped
     * @param $view
     * @return mixed
     */
    public function getCategoryIconMarker(& $template, & $sims, & $rems, & $wrapped, $view)
    {
        return $this->parentEvent->getCategoryIconMarker($template, $sims, $rems, $wrapped, $view);
    }

    /**
     * @param $template
     * @param $sims
     * @param $rems
     * @param $wrapped
     * @param $view
     */
    public function getHeaderstyleMarker(& $template, & $sims, & $rems, & $wrapped, $view)
    {
        $sims['###HEADERSTYLE###'] = $this->parentEvent->getHeaderStyle();
    }

    /**
     * @param $template
     * @param $sims
     * @param $rems
     * @param $wrapped
     * @param $view
     */
    public function getBodystyleMarker(& $template, & $sims, & $rems, & $wrapped, $view)
    {
        $sims['###BODYSTYLE###'] = $this->parentEvent->getBodyStyle();
    }

    /**
     * @param $template
     * @param $sims
     * @param $rems
     * @param $wrapped
     * @param $view
     */
    public function getMapMarker(& $template, & $sims, & $rems, & $wrapped, $view)
    {
        $this->parentEvent->getMapMarker($template, $sims, $rems, $wrapped, $view);
    }

    /**
     * @param $template
     * @param $sims
     * @param $rems
     * @param $wrapped
     * @param $view
     */
    public function getAttachmentMarker(& $template, & $sims, & $rems, & $wrapped, $view)
    {
        $this->parentEvent->getAttachmentMarker($template, $sims, $rems, $wrapped, $view);
    }

    /**
     * @param $template
     * @param $sims
     * @param $rems
     * @param $wrapped
     * @param $view
     * @return mixed
     */
    public function getAttachmentUrlMarker(& $template, & $sims, & $rems, & $wrapped, $view)
    {
        return $this->parentEvent->getAttachmentUrlMarker($template, $sims, $rems, $wrapped, $view);
    }

    /**
     * @param $template
     * @param $sims
     * @param $rems
     * @param $wrapped
     * @param $view
     */
    public function getEventLinkMarker(& $template, & $sims, & $rems, & $wrapped, $view)
    {
        $eventStart = $this->getStart();
        $wrapped['###EVENT_LINK###'] = explode(
            '$5&xs2',
            $this->parentEvent->getLinkToEvent('$5&xs2', $view, $eventStart->format('%Y%m%d'))
        );
    }

    /**
     * @param $template
     * @param $sims
     * @param $rems
     * @param $wrapped
     * @param $view
     */
    public function getEventUrlMarker(& $template, & $sims, & $rems, & $wrapped, $view)
    {
        $eventStart = $this->getStart();
        $sims['###EVENT_URL###'] = htmlspecialchars($this->parentEvent->getLinkToEvent(
            '$5&xs2',
            $view,
            $eventStart->format('%Y%m%d'),
            true
        ));
    }

    /**
     * @param $template
     * @param $sims
     * @param $rems
     * @param $wrapped
     * @param $view
     */
    public function getAbsoluteEventLinkMarker(& $template, & $sims, & $rems, & $wrapped, $view)
    {
        $eventStart = $this->getStart();
        $wrapped['###ABSOLUTE_EVENT_LINK###'] = explode(
            '$5&xs2',
            $this->parentEvent->getLinkToEvent('$5&xs2', $view, $eventStart->format('%Y%m%d'))
        );
    }

    /**
     * @return mixed
     */
    public function getStartdate()
    {
        $start = $this->getStart();
        return $start->format(Functions::getFormatStringFromConf($this->parentEvent->conf));
    }

    /**
     * @return mixed
     */
    public function getEnddate()
    {
        $end = $this->getEnd();
        return $end->format(Functions::getFormatStringFromConf($this->parentEvent->conf));
    }

    /**
     * @param $template
     * @param $sims
     * @param $rems
     * @param $wrapped
     * @param $view
     */
    public function getEditLinkMarker(& $template, & $sims, & $rems, & $wrapped, $view)
    {
        $eventStart = $this->getStart();
        $sims['###EDIT_LINK###'] = '';

        if ($this->parentEvent->isUserAllowedToEdit()) {
            $linkConf = $this->parentEvent->getValuesAsArray();
            if ($this->conf['view.']['enableAjax']) {
                $temp = sprintf(
                    $this->parentEvent->conf['view.'][$view . '.']['event.']['editLinkOnClick'],
                    $this->parentEvent->getUid(),
                    $this->parentEvent->getType()
                );
                $linkConf['link_ATagParams'] = ' onclick="' . $temp . '"';
            }
            $linkConf['link_no_cache'] = 0;
            //$linkConf['link_useCacheHash'] = 0;
            $linkConf['link_additionalParams'] = '&tx_cal_controller[view]=edit_event&tx_cal_controller[type]=' . $this->parentEvent->getType() . '&tx_cal_controller[uid]=' . $this->parentEvent->getUid() . '&tx_cal_controller[getdate]=' . $eventStart->format('%Y%m%d') . '&tx_cal_controller[lastview]=' . $this->controller->extendLastView();
            $linkConf['link_section'] = 'default';
            $linkConf['link_parameter'] = $this->parentEvent->conf['view.']['event.']['editEventViewPid'] ?: $GLOBALS['TSFE']->id;

            $this->parentEvent->initLocalCObject($linkConf);
            $this->parentEvent->local_cObj->setCurrentVal($this->parentEvent->conf['view.'][$view . '.']['event.']['editIcon']);
            $sims['###EDIT_LINK###'] = $this->parentEvent->local_cObj->cObjGetSingle(
                $this->parentEvent->conf['view.'][$view . '.']['event.']['editLink'],
                $this->parentEvent->conf['view.'][$view . '.']['event.']['editLink.']
            );
        }
        if ($this->parentEvent->isUserAllowedToDelete()) {
            $linkConf = $this->parentEvent->getValuesAsArray();
            if ($this->parentEvent->conf['view.']['enableAjax']) {
                $temp = sprintf(
                    $this->parentEvent->conf['view.'][$view . '.']['event.']['deleteLinkOnClick'],
                    $this->parentEvent->getUid(),
                    $this->parentEvent->getType()
                );
                $linkConf['link_ATagParams'] = ' onclick="' . $temp . '"';
            }
            $linkConf['link_no_cache'] = 0;
            //$linkConf['link_useCacheHash'] = 0;
            $linkConf['link_additionalParams'] = '&tx_cal_controller[view]=delete_event&tx_cal_controller[type]=' . $this->parentEvent->getType() . '&tx_cal_controller[uid]=' . $this->parentEvent->getUid() . '&tx_cal_controller[getdate]=' . $eventStart->format('%Y%m%d') . '&tx_cal_controller[lastview]=' . $this->controller->extendLastView();
            $linkConf['link_section'] = 'default';
            $linkConf['link_parameter'] = $this->parentEvent->conf['view.']['event.']['deleteEventViewPid'] ?: $GLOBALS['TSFE']->id;

            $this->parentEvent->initLocalCObject($linkConf);
            $this->parentEvent->local_cObj->setCurrentVal($this->parentEvent->conf['view.'][$view . '.']['event.']['deleteIcon']);
            $sims['###EDIT_LINK###'] .= $this->parentEvent->local_cObj->cObjGetSingle(
                $this->parentEvent->conf['view.'][$view . '.']['event.']['deleteLink'],
                $this->parentEvent->conf['view.'][$view . '.']['event.']['deleteLink.']
            );
        }
    }

    /**
     * @param $template
     * @param $sims
     * @param $rems
     * @param $wrapped
     * @param $view
     */
    public function getMoreLinkMarker(& $template, & $sims, & $rems, & $wrapped, $view)
    {
        $this->parentEvent->getMoreLinkMarker($template, $sims, $rems, $wrapped, $view);
    }

    /**
     * @param $template
     * @param $sims
     * @param $rems
     * @param $wrapped
     * @param $view
     */
    public function getStartdateMarker(& $template, & $sims, & $rems, & $wrapped, $view)
    {
        $this->getStartAndEndMarker($template, $sims, $rems, $wrapped, $view);
    }

    /**
     * @param $template
     * @param $sims
     * @param $rems
     * @param $wrapped
     * @param $view
     */
    public function getEnddateMarker(& $template, & $sims, & $rems, & $wrapped, $view)
    {
        $this->getStartdateMarker($template, $sims, $rems, $wrapped, $view);
    }

    /**
     * @param $template
     * @param $sims
     * @param $rems
     * @param $wrapped
     * @param $view
     */
    public function getStarttimeMarker(& $template, & $sims, & $rems, & $wrapped, $view)
    {
        $this->getStartdateMarker($template, $sims, $rems, $wrapped, $view);
    }

    /**
     * @param $template
     * @param $sims
     * @param $rems
     * @param $wrapped
     * @param $view
     */
    public function getEndtimeMarker(& $template, & $sims, & $rems, & $wrapped, $view)
    {
        $this->getStartdateMarker($template, $sims, $rems, $wrapped, $view);
    }

    /**
     * @param $template
     * @param $sims
     * @param $rems
     * @param $wrapped
     * @param $view
     */
    public function getDescriptionStriptagsMarker(& $template, & $sims, & $rems, & $wrapped, $view)
    {
        $this->parentEvent->getDescriptionStriptagsMarker($template, $sims, $rems, $wrapped, $view);
    }

    /**
     * @param string $feUserUid
     * @param array $feGroupsArray
     * @return bool
     */
    public function isUserAllowedToEdit($feUserUid = '', $feGroupsArray = []): bool
    {
        $rightsObj = &Registry::Registry('basic', 'rightscontroller');
        if (!$rightsObj->isViewEnabled('edit_event')) {
            return false;
        }
        if ($rightsObj->isCalAdmin()) {
            return true;
        }
        $editOffset = $this->parentEvent->conf['rights.']['edit.']['event.']['timeOffset'] * 60;

        if ($feUserUid === '') {
            $feUserUid = $rightsObj->getUserId();
        }
        if (empty($feGroupsArray)) {
            $feGroupsArray = $rightsObj->getUserGroups();
        }
        $isEventOwner = $this->parentEvent->isEventOwner($feUserUid, $feGroupsArray);
        $isSharedUser = $this->parentEvent->isSharedUser($feUserUid, $feGroupsArray);
        if ($rightsObj->isAllowedToEditStartedEvent()) {
            $eventHasntStartedYet = true;
        } else {
            $temp = new CalDate();
            $temp->setTZbyID('UTC');
            $temp->addSeconds($editOffset);
            $eventStart = $this->getStart();
            $eventHasntStartedYet = $eventStart->after($temp);
        }
        $isAllowedToEditEvent = $rightsObj->isAllowedToEditEvent();
        $isAllowedToEditOwnEventsOnly = $rightsObj->isAllowedToEditOnlyOwnEvent();

        if ($isAllowedToEditOwnEventsOnly) {
            return ($isEventOwner || $isSharedUser) && $eventHasntStartedYet;
        }
        return $isAllowedToEditEvent && ($isEventOwner || $isSharedUser) && $eventHasntStartedYet;
    }

    /**
     * @param string $feUserUid
     * @param array $feGroupsArray
     * @return bool
     */
    public function isUserAllowedToDelete($feUserUid = '', $feGroupsArray = []): bool
    {
        $rightsObj = &Registry::Registry('basic', 'rightscontroller');
        if (!$rightsObj->isViewEnabled('delete_event')) {
            return false;
        }
        if ($rightsObj->isCalAdmin()) {
            return true;
        }
        $deleteOffset = $this->parentEvent->conf['rights.']['delete.']['event.']['timeOffset'] * 60;
        if ($feUserUid === '') {
            $feUserUid = $rightsObj->getUserId();
        }
        if (empty($feGroupsArray)) {
            $feGroupsArray = $rightsObj->getUserGroups();
        }
        $isEventOwner = $this->parentEvent->isEventOwner($feUserUid, $feGroupsArray);
        $isSharedUser = $this->parentEvent->isSharedUser($feUserUid, $feGroupsArray);
        if ($rightsObj->isAllowedToDeleteStartedEvents()) {
            $eventHasntStartedYet = true;
        } else {
            $temp = new CalDate();
            $temp->setTZbyID('UTC');
            $temp->addSeconds($deleteOffset);
            $eventStart = $this->getStart();
            $eventHasntStartedYet = $eventStart->after($temp);
        }
        $isAllowedToDeleteEvents = $rightsObj->isAllowedToDeleteEvents();
        $isAllowedToDeleteOwnEventsOnly = $rightsObj->isAllowedToDeleteOnlyOwnEvents();

        if ($isAllowedToDeleteOwnEventsOnly) {
            return ($isEventOwner || $isSharedUser) && $eventHasntStartedYet;
        }
        return $isAllowedToDeleteEvents && ($isEventOwner || $isSharedUser) && $eventHasntStartedYet;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return 'Phpicalendar ' . (is_object($this) ? 'object' : 'something') . ': ' . implode(
                ',',
                $this->parentEvent->row
            );
    }

    /**
     * @return mixed
     */
    public function getAttendees()
    {
        return $this->parentEvent->getAttendees();
    }

    /**
     * @param $template
     * @param $sims
     * @param $rems
     * @param $wrapped
     * @param $view
     */
    public function getAttendeeMarker(& $template, & $sims, & $rems, & $wrapped, $view)
    {
        $this->parentEvent->getAttendeeMarker($template, $sims, $rems, $wrapped, $view);
    }

    /**
     * @param $linktext
     * @param $view
     * @param $date
     * @param bool $urlOnly
     * @return mixed
     */
    public function getLinkToEvent($linktext, $view, $date, $urlOnly = false)
    {
        return $this->parentEvent->getLinkToEvent($linktext, $view, $date, $urlOnly);
    }

    /**
     * @param $template
     * @param $sims
     * @param $rems
     * @param $wrapped
     * @param $view
     */
    public function getEventIdMarker(& $template, & $sims, & $rems, & $wrapped, $view)
    {
        $start = $this->getStart();
        $sims['###EVENT_ID###'] = $this->parentEvent->getType() . $this->parentEvent->getUid() . $start->format('%Y%m%d%H%M');
    }

    /**
     * @param $template
     * @param $sims
     * @param $rems
     * @param $wrapped
     * @param $view
     */
    public function getGuidMarker(& $template, & $sims, & $rems, & $wrapped, $view)
    {
        $this->parentEvent->getGuidMarker($template, $sims, $rems, $wrapped, $view);
    }

    /**
     * @param $template
     * @param $sims
     * @param $rems
     * @param $wrapped
     * @param $view
     */
    public function getDtstampMarker(& $template, & $sims, & $rems, & $wrapped, $view)
    {
        $this->parentEvent->getDtstampMarker($template, $sims, $rems, $wrapped, $view);
    }

    /**
     * @param $template
     * @param $sims
     * @param $rems
     * @param $wrapped
     * @param $view
     */
    public function getCruserNameMarker(& $template, & $sims, & $rems, & $wrapped, $view)
    {
        $this->parentEvent->getCruserNameMarker($template, $sims, $rems, $wrapped, $view);
    }

    /**
     * @param $template
     * @param $sims
     * @param $rems
     * @param $wrapped
     * @param $view
     */
    public function getCalendarTitleMarker(& $template, & $sims, & $rems, & $wrapped, $view)
    {
        $this->parentEvent->getCalendarTitleMarker($template, $sims, $rems, $wrapped, $view);
    }

    /**
     * @return mixed
     */
    public function getNow()
    {
        return $this->parentEvent->getNow();
    }

    /**
     * @return mixed
     */
    public function getToday()
    {
        return $this->parentEvent->getToday();
    }

    /**
     * @param $template
     * @param $sims
     * @param $rems
     * @param $wrapped
     * @param $view
     */
    public function getImageMarker(& $template, & $sims, & $rems, & $wrapped, $view)
    {
        $this->parentEvent->getImageMarker($template, $sims, $rems, $wrapped, $view);
    }

    /**
     * @param $template
     * @param $sims
     * @param $rems
     * @param $wrapped
     * @param $view
     */
    public function getDescriptionMarker(& $template, & $sims, & $rems, & $wrapped, $view)
    {
        $this->parentEvent->getDescriptionMarker($template, $sims, $rems, $wrapped, $view);
    }

    /**
     * @param $template
     * @param $sims
     * @param $rems
     * @param $wrapped
     * @param $view
     */
    public function getHeadingMarker(& $template, & $sims, & $rems, & $wrapped, $view)
    {
        $this->parentEvent->getHeadingMarker($template, $sims, $rems, $wrapped, $view);
    }

    /**
     * @param $template
     * @param $sims
     * @param $rems
     * @param $wrapped
     * @param $view
     */
    public function getEditPanelMarker(& $template, & $sims, & $rems, & $wrapped, $view)
    {
        $this->parentEvent->getEditPanelMarker($template, $sims, $rems, $wrapped, $view);
    }

    /**
     * @param $template
     * @param $sims
     * @param $rems
     * @param $wrapped
     * @param $view
     */
    public function getTopMarker(& $template, & $sims, & $rems, & $wrapped, $view)
    {
        $this->parentEvent->getTopMarker($template, $sims, $rems, $wrapped, $view);
    }

    /**
     * @param $template
     * @param $sims
     * @param $rems
     * @param $wrapped
     * @param $view
     */
    public function getLengthMarker(& $template, & $sims, & $rems, & $wrapped, $view)
    {
        $this->parentEvent->getLengthMarker($template, $sims, $rems, $wrapped, $view);
    }

    /**
     * @return int
     */
    public function getUid(): int
    {
        return $this->parentEvent->getUid();
    }

    /**
     * @return int
     */
    public function isAllDay(): int
    {
        return $this->parentEvent->isAllDay();
    }

    /**
     * @return mixed
     */
    public function getEventOwner()
    {
        return $this->parentEvent->getEventOwner();
    }

    /**
     * @return int
     */
    public function getCalendarId(): int
    {
        return $this->parentEvent->getCalendarId();
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->parentEvent->getType();
    }

    /**
     * @return int
     */
    public function getEventType(): int
    {
        return $this->parentEvent->getEventType();
    }

    /**
     * @return int
     */
    public function getCount(): int
    {
        return $this->parentEvent->getCount();
    }

    /**
     * @return array
     */
    public function getValuesAsArray(): array
    {
        if ($this->initializingCacheValues) {
            return $this->parentEvent->row;
        }

        if (!count($this->cachedValueArray)) {
            // set locking variable
            $this->initializingCacheValues = true;
            $values = $this->parentEvent->getValuesAsArray();

            $additionalValues = $this->getAdditionalValuesAsArray();
            $mergedValues = array_merge($values, $additionalValues);

            // now cache the result to win some ms
            $this->cachedValueArray = $mergedValues;
            $this->initializingCacheValues = false;
        }
        return $this->cachedValueArray;
    }

    /**
     * @return array
     */
    public function getAdditionalValuesAsArray(): array
    {
        $values = parent::getAdditionalValuesAsArray();
        $values['start'] = $this->getStartAsTimestamp();
        $values['end'] = $this->getEndAsTimestamp();
        $values['parent_startdate'] = $this->parentEvent->start->format('%Y%m%d');
        $values['parent_enddate'] = $this->parentEvent->end->format('%Y%m%d');
        $values['parent_starttime'] = $this->parentEvent->start->getHour() * 60 + $this->parentEvent->start->getMinute();
        $values['parent_endtime'] = $this->parentEvent->end->getHour() * 60 + $this->parentEvent->end->getMinute();
        return $values;
    }

    /**
     * @return array
     */
    public function getCategories(): array
    {
        return $this->parentEvent->getCategories();
    }

    /**
     * @return CalDate
     */
    public function getUntil(): CalDate
    {
        return $this->parentEvent->getUntil();
    }
}
