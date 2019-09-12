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
use TYPO3\CMS\Cal\Model\CategoryModel;
use TYPO3\CMS\Cal\Model\LocationModel;
use TYPO3\CMS\Cal\Model\Organizer;
use TYPO3\CMS\Cal\Utility\Functions;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * A concrete view for the calendar.
 * It is based on the phpicalendar project
 */
class AdminView extends BaseView
{
    /**
     * @return mixed
     */
    public function drawAdminPage()
    {
        $a = [];
        $rems = [];
        $this->_init($a);

        $this->checkAction();

        $page = Functions::getContent($this->conf['view.']['admin.']['adminTemplate']);
        if ($page === '') {
            return '<h3>calendar: no adminTemplate file found:</h3>' . $this->conf['view.']['admin.']['adminTemplate'];
        }
        $showCalendarForm = false;
        $showCategoryForm = false;
        $showEventForm = false;
        $showLocationForm = false;
        $showOrganizerForm = false;

        $this->initLocalCObject($this->getValuesAsArray());

        $createCalendarLink = '';
        if ($this->rightsObj->isAllowedTo('create', 'calendar') && $this->rightsObj->isViewEnabled('create_calendar')) {
            $this->local_cObj->setCurrentVal($this->controller->pi_getLL('l_create_calendar'));
            $this->controller->getParametersForTyposcriptLink(
                $this->local_cObj->data,
                [
                    'view' => 'create_calendar',
                    'type' => 'tx_cal_calendar'
                ],
                $this->conf['cache'],
                $this->conf['clear_anyway'],
                $this->conf['view.']['calendar.']['createCalendarViewPid']
            );
            $createCalendarLink .= $this->local_cObj->cObjGetSingle(
                $this->conf['view.']['calendar.']['calendar.']['addLink'],
                $this->conf['view.']['calendar.']['calendar.']['addLink.']
            );
            $showCalendarForm = true;
        } else {
            $rems['###CREATE_CALENDAR###'] = '';
        }
        if (!$this->rightsObj->isAllowedTo('edit', 'calendar') || !$this->rightsObj->isViewEnabled('edit_calendar')) {
            $rems['###EDIT_CALENDAR###'] = '';
        } else {
            $showCalendarForm = true;
        }
        if (!$this->rightsObj->isAllowedTo(
            'delete',
            'calendar'
            ) || !$this->rightsObj->isViewEnabled('delete_calendar')) {
            $rems['###DELETE_CALENDAR###'] = '';
        } else {
            $showCalendarForm = true;
        }
        if (!$this->rightsObj->isAllowedTo('delete', 'calendar') && !$this->rightsObj->isAllowedTo(
            'edit',
            'calendar'
            )) {
            $rems['###CHOOSE_CALENDAR###'] = '';
        }
        if (!$showCalendarForm) {
            $rems['###CALENDAR_FORM###'] = '';
        }
        $createCategoryLink = '';
        if ($this->rightsObj->isAllowedTo('create', 'category') && $this->rightsObj->isViewEnabled('create_category')) {
            $this->local_cObj->setCurrentVal($this->controller->pi_getLL('l_create_category'));
            $this->controller->getParametersForTyposcriptLink(
                $this->local_cObj->data,
                [
                    'view' => 'create_category',
                    'type' => 'sys_category'
                ],
                $this->conf['cache'],
                $this->conf['clear_anyway'],
                $this->conf['view.']['category.']['createCategoryViewPid']
            );
            $createCategoryLink .= $this->local_cObj->cObjGetSingle(
                $this->conf['view.']['category.']['category.']['addLink'],
                $this->conf['view.']['category.']['category.']['addLink.']
            );
            $showCategoryForm = true;
        } else {
            $rems['###CREATE_CATEGORY###'] = '';
        }
        if (!$this->rightsObj->isAllowedTo('edit', 'category') || !$this->rightsObj->isViewEnabled('edit_category')) {
            $rems['###EDIT_CATEGORY###'] = '';
        } else {
            $showCategoryForm = true;
        }
        if (!$this->rightsObj->isAllowedTo(
            'delete',
            'category'
            ) || !$this->rightsObj->isViewEnabled('delete_category')) {
            $rems['###DELETE_CATEGORY###'] = '';
        } else {
            $showCategoryForm = true;
        }
        if (!$this->rightsObj->isAllowedTo('delete', 'category') && !$this->rightsObj->isAllowedTo(
            'edit',
            'category'
            )) {
            $rems['###CHOOSE_CATEGORY###'] = '';
        }
        if (!$showCategoryForm) {
            $rems['###CATEGORY_FORM###'] = '';
        }
        $createOrganizerLink = '';
        if ($this->rightsObj->isAllowedTo(
            'create',
            'organizer'
            ) && $this->rightsObj->isViewEnabled('create_organizer')) {
            $this->local_cObj->setCurrentVal($this->controller->pi_getLL('l_create_organizer'));
            $this->controller->getParametersForTyposcriptLink(
                $this->local_cObj->data,
                [
                    'view' => 'create_organizer'
                ],
                $this->conf['cache'],
                $this->conf['clear_anyway'],
                $this->conf['view.']['organizer.']['createOrganizerViewPid']
            );
            $createOrganizerLink .= $this->local_cObj->cObjGetSingle(
                $this->conf['view.']['organizer.']['organizer.']['addLink'],
                $this->conf['view.']['organizer.']['organizer.']['addLink.']
            );
            $showOrganizerForm = true;
        } else {
            $rems['###CREATE_ORGANIZER###'] = '';
        }
        if (!$this->rightsObj->isAllowedTo('edit', 'organizer') || !$this->rightsObj->isViewEnabled('edit_organizer')) {
            $rems['###EDIT_ORGANIZER###'] = '';
        } else {
            $showOrganizerForm = true;
        }
        if (!$this->rightsObj->isAllowedTo(
            'delete',
            'organizer'
            ) || !$this->rightsObj->isViewEnabled('delete_organizer')) {
            $rems['###DELETE_ORGANIZER###'] = '';
        } else {
            $showOrganizerForm = true;
        }
        if (!$this->rightsObj->isAllowedTo('delete', 'organizer') && !$this->rightsObj->isAllowedTo(
            'edit',
            'organizer'
            )) {
            $rems['###CHOOSE_ORGANIZER###'] = '';
        }
        if (!$showOrganizerForm) {
            $rems['###ORGANIZER_FORM###'] = '';
        }
        $createLocationLink = '';
        if ($this->rightsObj->isAllowedTo('create', 'location') && $this->rightsObj->isViewEnabled('create_location')) {
            $this->local_cObj->setCurrentVal($this->controller->pi_getLL('l_create_location'));
            $this->controller->getParametersForTyposcriptLink(
                $this->local_cObj->data,
                [
                    'view' => 'create_location'
                ],
                $this->conf['cache'],
                $this->conf['clear_anyway'],
                $this->conf['view.']['location.']['createLocationViewPid']
            );
            $createLocationLink .= $this->local_cObj->cObjGetSingle(
                $this->conf['view.']['location.']['location.']['addLink'],
                $this->conf['view.']['location.']['location.']['addLink.']
            );
            $showLocationForm = true;
        } else {
            $rems['###CREATE_LOCATION###'] = '';
        }
        if (!$this->rightsObj->isAllowedTo('edit', 'location') || !$this->rightsObj->isViewEnabled('edit_location')) {
            $rems['###EDIT_LOCATION###'] = '';
        } else {
            $showLocationForm = true;
        }
        if (!$this->rightsObj->isAllowedTo(
            'delete',
            'location'
            ) || !$this->rightsObj->isViewEnabled('delete_location')) {
            $rems['###DELETE_LOCATION###'] = '';
        } else {
            $showLocationForm = true;
        }
        if (!$this->rightsObj->isAllowedTo('delete', 'location') && !$this->rightsObj->isAllowedTo(
            'edit',
            'location'
            )) {
            $rems['###CHOOSE_LOCATION###'] = '';
        }
        if (!$showLocationForm) {
            $rems['###LOCATION_FORM###'] = '';
        }
        $createEventLink = '';
        if ($this->rightsObj->isAllowedTo('create', 'event') && $this->rightsObj->isViewEnabled('create_event')) {
            $this->local_cObj->setCurrentVal($this->controller->pi_getLL('l_create_event'));
            $this->controller->getParametersForTyposcriptLink($this->local_cObj->data, [
                'view' => 'create_event'
            ], $this->conf['cache'], $this->conf['clear_anyway'], $this->conf['view.']['event.']['createEventViewPid']);
            $createEventLink .= $this->local_cObj->cObjGetSingle(
                $this->conf['view.']['event.']['event.']['addLink'],
                $this->conf['view.']['event.']['event.']['addLink.']
            );
            $showEventForm = true;
        } else {
            $rems['###CREATE_EVENT###'] = '';
        }
        if (!$this->rightsObj->isAllowedTo('edit', 'event') || !$this->rightsObj->isViewEnabled('edit_event')) {
            $rems['###EDIT_EVENT###'] = '';
        } else {
            $showEventForm = true;
        }
        if (!$this->rightsObj->isAllowedTo('delete', 'event') || !$this->rightsObj->isViewEnabled('delete_event')) {
            $rems['###DELETE_EVENT###'] = '';
        } else {
            $showEventForm = true;
        }
        if (!$this->rightsObj->isAllowedTo('delete', 'event') && !$this->rightsObj->isAllowedTo('edit', 'event')) {
            $rems['###CHOOSE_EVENT###'] = '';
        }
        if (!$showEventForm) {
            $rems['###EVENT_FORM###'] = '';
        }

        // CALENDAR
        $calendarArray = $this->modelObj->findAllCalendar('tx_cal_calendar', $this->conf['pidList']);
        $editCalendarOptions = '<option value="">' . $this->controller->pi_getLL('l_select') . '</option>';
        /** @var CalendarModel $calendar */
        foreach ($calendarArray['tx_cal_calendar'] as $calendar) {
            if ($calendar->isUserAllowedToEdit() || $calendar->isUserAllowedToDelete()) {
                $editCalendarOptions .= '<option value="' . $calendar->getUid() . '">' . $calendar->getTitle() . '</option>';
            }
        }
        $params = [
            'view' => 'edit_calendar',
            'type' => 'tx_cal_calendar'
        ];
        $editCalendarParams = '';
        foreach ($params as $key => $value) {
            $editCalendarParams .= '<input type="hidden" value="' . $value . '" id="calendar_' . $key . '" name="' . $this->prefixId . '[' . $key . ']"/>';
        }

        // CATEGORY
        $categoryArrays = $this->modelObj->findAllCategories(
            'cal_category_model',
            'sys_category',
            $this->conf['pidList']
        );

        $categoryArray = $categoryArrays['sys_category'][0][0];
        $editCategoryOptions = '<option value="">' . $this->controller->pi_getLL('l_select') . '</option>';
        /** @var CategoryModel $category */
        foreach ($categoryArray as $category) {
            if ($category->isUserAllowedToEdit() || $category->isUserAllowedToDelete()) {
                $editCategoryOptions .= '<option value="' . $category->getUid() . '" >' . $category->getTitle() . '</option>';
            }
        }
        $params = [
            'view' => 'edit_category',
            'type' => 'sys_category'
        ];
        $editCategoryParams = '';
        foreach ($params as $key => $value) {
            $editCategoryParams .= '<input type="hidden" value="' . $value . '" id="category_' . $key . '" name="' . $this->prefixId . '[' . $key . ']"/>';
        }

        // EVENT

        $confArr = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['cal']);
        $locationModel = ($confArr['useLocationStructure'] ?: 'tx_cal_location');
        $organizerModel = ($confArr['useOrganizerStructure'] ?: 'tx_cal_organizer');

        // LOCATION
        $locationArray = $this->modelObj->findAllLocations($locationModel, $this->conf['pidList']);
        $editLocationOptions = '<option value="">' . $this->controller->pi_getLL('l_select') . '</option>';

        /** @var LocationModel $location */
        foreach ($locationArray as $location) {
            if ($location->isUserAllowedToEdit()) {
                $editLocationOptions .= '<option value="' . $location->getUid() . '" >' . $location->getName() . '</option>';
            }
        }
        $params = [
            'view' => 'edit_location',
            'type' => $locationModel
        ];
        $editLocationParams = '';
        foreach ($params as $key => $value) {
            $editLocationParams .= '<input type="hidden" value="' . $value . '" id="location_' . $key . '" name="' . $this->prefixId . '[' . $key . ']"/>';
        }

        // ORGANIZER
        $organizerArray = $this->modelObj->findAllOrganizer($organizerModel, $this->conf['pidList']);
        $editOrganizerOptions = '<option value="">' . $this->controller->pi_getLL('l_select') . '</option>';
        /** @var Organizer $organizer */
        foreach ($organizerArray as $organizer) {
            if ($organizer->isUserAllowedToEdit()) {
                $editOrganizerOptions .= '<option value="' . $organizer->getUid() . '" >' . $organizer->getName() . '</option>';
            }
        }
        $params = [
            'view' => 'edit_organizer',
            'type' => $organizerModel
        ];
        $editOrganizerParams = '';
        foreach ($params as $key => $value) {
            $editOrganizerParams .= '<input type="hidden" value="' . $value . '" id="organizer_' . $key . '" name="' . $this->prefixId . '[' . $key . ']"/>';
        }

        $selfUrl = $this->controller->pi_linkTP_keepPIvars_url();
        $sims = [
            '###L_ADMINISTRATION_VIEW###' => $this->controller->pi_getLL('l_administration_view'),
            '###L_CREATE###' => $this->controller->pi_getLL('l_create'),
            '###L_EDIT###' => $this->controller->pi_getLL('l_edit'),
            '###L_DELETE###' => $this->controller->pi_getLL('l_delete'),
            '###L_EVENT_LABEL###' => $this->controller->pi_getLL('l_event'),
            '###CREATE_CALENDAR_LINK###' => $createCalendarLink,
            '###CREATE_CATEGORY_LINK###' => $createCategoryLink,
            '###CREATE_ORGANIZER_LINK###' => $createOrganizerLink,
            '###CREATE_LOCATION_LINK###' => $createLocationLink,
            '###CREATE_EVENT_LINK###' => $createEventLink,
            '###EDIT_EVENT_URL###' => $editEventLink .= $selfUrl,
            '###EDIT_EVENT_PARAMETER###' => $editEventParams,
            '###EDIT_EVENT_OPTIONS###' => $editEventOptions,
            '###DELETE_EVENT_URL###' => $deleteEventLink .= $selfUrl,
            '###DELETE_EVENT_PARAMETER###' => $deleteEventParams,
            '###DELETE_EVENT_OPTIONS###' => $editEventOptions,
            '###L_CALENDAR_LABEL###' => $this->controller->pi_getLL('l_calendar'),
            '###EDIT_CALENDAR_URL###' => $editCalendarLink .= $selfUrl,
            '###EDIT_CALENDAR_PARAMETER###' => $editCalendarParams,
            '###EDIT_CALENDAR_OPTIONS###' => $editCalendarOptions,
            '###DELETE_CALENDAR_URL###' => $deleteCalendarLink .= $selfUrl,
            '###DELETE_CALENDAR_PARAMETER###' => $deleteCalendarParams,
            '###DELETE_CALENDAR_OPTIONS###' => $editCalendarOptions,
            '###L_CATEGORY_LABEL###' => $this->controller->pi_getLL('l_category'),
            '###EDIT_CATEGORY_URL###' => $editCategoryLink .= $selfUrl,
            '###EDIT_CATEGORY_PARAMETER###' => $editCategoryParams,
            '###EDIT_CATEGORY_OPTIONS###' => $editCategoryOptions,
            '###DELETE_CATEGORY_URL###' => $deleteCategoryLink .= $selfUrl,
            '###DELETE_CATEGORY_PARAMETER###' => $deleteCategoryParams,
            '###DELETE_CATEGORY_OPTIONS###' => $editCategoryOptions,
            '###L_LOCATION_LABEL###' => $this->controller->pi_getLL('l_location'),
            '###EDIT_LOCATION_URL###' => $editLocationLink .= $selfUrl,
            '###EDIT_LOCATION_PARAMETER###' => $editLocationParams,
            '###EDIT_LOCATION_OPTIONS###' => $editLocationOptions,
            '###DELETE_LOCATION_URL###' => $deleteLocationLink .= $selfUrl,
            '###DELETE_LOCATION_PARAMETER###' => $deleteLocationParams,
            '###DELETE_LOCATION_OPTIONS###' => $editLocationOptions,
            '###L_ORGANIZER_LABEL###' => $this->controller->pi_getLL('l_organizer'),
            '###EDIT_ORGANIZER_URL###' => $editOrganizerLink .= $selfUrl,
            '###EDIT_ORGANIZER_PARAMETER###' => $editOrganizerParams,
            '###EDIT_ORGANIZER_OPTIONS###' => $editOrganizerOptions,
            '###DELETE_ORGANIZER_URL###' => $deleteOrganizerLink .= $selfUrl,
            '###DELETE_ORGANIZER_PARAMETER###' => $deleteOrganizerParams,
            '###DELETE_ORGANIZER_OPTIONS###' => $editOrganizerOptions
        ];

        $page = Functions::substituteMarkerArrayNotCached($page, $sims, [], []);
        $page = Functions::substituteMarkerArrayNotCached($page, [], $rems, []);

        $a = [];
        return $this->finish($page, $a);
    }

    /**
     * @param $page
     * @param $sims
     * @param $rems
     * @param $wrapped
     */
    public function getCalendarSubscriptionMarker($page, &$sims, &$rems, &$wrapped)
    {
        $sims['###CALENDAR_SUBSCRIPTION###'] = '';
        if ($this->rightsObj->isLoggedIn() && $this->rightsObj->isAllowedTo('edit', 'calendarSubscription')) {
            $calendarIds = [];

            $deselectedCalendarIds = GeneralUtility::trimExplode(
                ',',
                $this->conf['view.']['calendar.']['subscription'],
                1
            );
            foreach ($deselectedCalendarIds as $calendarUid) {
                $calendarIds[] = $calendarUid;
                /** @var CalendarModel $calendar */
                $calendar = $this->modelObj->findCalendar($calendarUid, 'tx_cal_calendar', $this->conf['pidList']);
                if (is_object($calendar)) {
                    $sims['###CALENDAR_SUBSCRIPTION###'] .= '<input type="checkbox" value="' . $calendar->getUid() . '" name="tx_cal_controller[calendarSubscription][]">' . $calendar->getTitle() . '<br/>';
                }
            }

            $calendarArray = $this->modelObj->findAllCalendar('tx_cal_calendar');
            /** @var CalendarModel $calendar */
            foreach ($calendarArray['tx_cal_calendar'] as $calendar) {
                if (!in_array($calendar->getUid(), $calendarIds, true)) {
                    $calendarIds[] = $calendar->getUid();
                    $sims['###CALENDAR_SUBSCRIPTION###'] .= '<input type="checkbox" value="' . $calendar->getUid() . '" checked="checked" name="tx_cal_controller[calendarSubscription][]">' . $calendar->getTitle() . '<br/>';
                }
            }

            $sims['###CALENDAR_SUBSCRIPTION###'] .= '<input type="hidden" value="' . implode(
                ',',
                array_unique($calendarIds)
                ) . '" name="tx_cal_controller[calendarSubscriptionIds]"/>';
        } else {
            $rems['###CALENDAR_SUBSCRIPTION###'] = '';
        }
    }

    /**
     * @param $page
     * @param $sims
     * @param $rems
     * @param $wrapped
     */
    public function getCalendarSubscriptionUrlMarker($page, &$sims, &$rems, &$wrapped)
    {
        $sims['###CALENDAR_SUBSCRIPTION_URL###'] = $this->controller->pi_linkTP_keepPIvars_url([
            'view' => 'admin'
        ], 0);
    }

    private function checkAction()
    {
        if ($this->controller->piVars['adminAction'] === 'editCalendarSubscription') {
            $table = 'fe_users';
            $where = 'uid = ' . $this->rightsObj->getUserId();
            if ($this->controller->piVars['calendarSubscription'] !== '') {
                $ids = is_array($this->controller->piVars['calendarSubscription']) ? $this->controller->piVars['calendarSubscription'] : GeneralUtility::trimExplode(
                    ',',
                    $this->controller->piVars['calendarSubscription'],
                    1
                );
            } else {
                $ids = is_array($this->controller->piVars['calendarSubscription']) ? $this->controller->piVars['calendarSubscription'] : [];
            }
            $allIds = $this->controller->piVars['calendarSubscriptionIds'] ? GeneralUtility::trimExplode(
                ',',
                $this->controller->piVars['calendarSubscriptionIds'],
                1
            ) : [];
            $fields = [
                'tx_cal_calendar_subscription' => implode(',', array_diff($allIds, $ids))
            ];

            $GLOBALS['TYPO3_DB']->exec_UPDATEquery($table, $where, $fields);
            $this->conf['calendar'] = $this->conf['view.']['calendar'] = $this->conf['view.']['allowedCalendar'] = $this->conf['category'] = $this->conf['view.']['category'] = $this->conf['view.']['allowedCategory'] = '';
            $this->controller->checkCalendarAndCategory();
            unset($this->controller->piVars['calendarSubscription']);
            Functions::clearCache();
        }
    }
}
