<?php

/*
 * This file is part of the web-tp3/cal.
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

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
use TYPO3\CMS\Cal\Controller\Controller;
use TYPO3\CMS\Cal\Model\CalendarDateTime;
use TYPO3\CMS\Cal\Model\CategoryModel;
use TYPO3\CMS\Cal\Utility\Functions;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * A concrete view for the calendar.
 * It is based on the phpicalendar project
 */
class SearchViews extends ListView
{
    public $confArr = [];
    public $page;
    public $objectType;

    public function __construct()
    {
        parent::__construct();
        $this->confArr =  is_array($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['cal']) ? $GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['cal'] : unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['cal']);
    }

    /**
     * Draws a single event.
     *
     * @param $master_array
     * @param $getdate
     * @return string HTML output.
     */
    public function drawSearch(&$master_array, $getdate): string
    {
        $this->_init($master_array);

        $page = Functions::getContent($this->conf['view.']['other.']['searchBoxTemplate']);
        if ($page === '') {
            return '<h3>calendar: no template file found:</h3>' . $this->conf['view.']['other.']['searchBoxTemplate'];
        }
        $rems = [];
        return $this->finish($page, $rems);
    }

    /**
     * Draws a search result view.
     *
     * @param $master_array
     * @param $starttime
     * @param $endtime
     * @param $searchword
     * @param string $locationIds
     * @param string $organizerIds
     * @return string HTML output.
     */
    public function drawSearchAllResult(
        &$master_array,
        $starttime,
        $endtime,
        $searchword,
        $locationIds = '',
        $organizerIds = ''
    ): string {
        $page = Functions::getContent($this->conf['view.']['search.']['searchResultAllTemplate']);
        if ($page === '') {
            return '<h3>calendar: no search result template file found:</h3>' . $this->conf['view.']['search.']['searchResultAllTemplate'];
        }

        $sims = [];

        if (array_key_exists('phpicalendar_event', $master_array)) {
            $sims['SEARCHEVENTRESULTS'] = $this->drawSearchEventResult(
                $master_array['phpicalendar_event'],
                $starttime,
                $endtime,
                $searchword,
                $locationIds,
                $organizerIds
            );
        }
        $this->initLocalCObject();
        $this->local_cObj->setCurrentVal($sims['SEARCHEVENTRESULTS']);
        $sims['SEARCHEVENTRESULTS'] = $this->local_cObj->cObjGetSingle(
            $this->conf['view.']['search_all.']['searchEvent'],
            $this->conf['view.']['search_all.']['searchEvent.']
        );

        $this->objectsInList = [];
        if (array_key_exists('location', $master_array)) {
            $sims['SEARCHLOCATIONRESULTS'] = $this->drawSearchLocationResult($master_array['location'], $searchword);
        }
        $this->initLocalCObject();
        $this->local_cObj->setCurrentVal($sims['SEARCHLOCATIONRESULTS']);
        $sims['SEARCHLOCATIONRESULTS'] = $this->local_cObj->cObjGetSingle(
            $this->conf['view.']['search_all.']['searchLocation'],
            $this->conf['view.']['search_all.']['searchLocation.']
        );

        $this->objectsInList = [];
        if (array_key_exists('organizer', $master_array)) {
            $sims['SEARCHORGANIZERRESULTS'] = $this->drawSearchOrganizerResult($master_array['organizer'], $searchword);
        }
        $this->initLocalCObject();
        $this->local_cObj->setCurrentVal($sims['SEARCHORGANIZERRESULTS']);
        $sims['SEARCHORGANIZERRESULTS'] = $this->local_cObj->cObjGetSingle(
            $this->conf['view.']['search_all.']['searchOrganizer'],
            $this->conf['view.']['search_all.']['searchOrganizer.']
        );

        $page = Controller::replace_tags($sims, $page);
        $rems = [];
        return $this->finish($page, $rems);
    }

    /**
     * @param $page
     * @param $sims
     * @param $rems
     * @param $wrapped
     */
    public function getSearchActionUrlMarker(&$page, &$sims, &$rems, &$wrapped)
    {
        $this->initLocalCObject();
        $this->controller->getParametersForTyposcriptLink($this->local_cObj->data, [], $this->conf['cache'], true);
        $sims['###SEARCH_ACTION_URL###'] = $this->local_cObj->cObjGetSingle(
            $this->conf['view.']['search.']['searchLinkUrl'],
            $this->conf['view.']['search.']['searchLinkUrl.']
        );
    }

    /**
     * @param $page
     * @param $sims
     * @param $rems
     * @param $view
     */
    public function getCategoryIdsMarker(&$page, &$sims, &$rems, $view)
    {
        $sims['###CATEGORY_IDS###'] = '<option value="">' . $this->controller->pi_getLL('l_all_category') . '</option>';
        $catArrayArray = $this->modelObj->findAllCategories(
            'cal_category_model',
            'sys_category',
            $this->conf['pidList']
        );

        $rememberUid = [];
        $ids = [];
        if ($this->controller->piVars['submit'] && $this->controller->piVars['category']) {
            $ids = $this->controller->piVars['category'];
        }

        foreach ($catArrayArray as $categoryArrayFromService) {
            /** @var CategoryModel $category */
            foreach ($categoryArrayFromService[0][0] as $category) {
                $uid = $category->getUid();
                if (!in_array($uid, $rememberUid, true)) {
                    if (in_array($uid, $ids, true)) {
                        $sims['###CATEGORY_IDS###'] .= '<option value="' . $uid . '" selected="selected">' . $category->getTitle() . '</option>';
                    } else {
                        $sims['###CATEGORY_IDS###'] .= '<option value="' . $uid . '" >' . $category->getTitle() . '</option>';
                    }
                    $rememberUid[] = $uid;
                }
            }
        }
    }

    /**
     * @param $page
     * @param $sims
     * @param $rems
     * @param $view
     */
    public function getLocationIdsMarker(&$page, &$sims, &$rems, $view)
    {
        $sims['###LOCATION_IDS###'] = '<option  value="">' . $this->controller->pi_getLL('l_all_location') . '</option>';
        $locationArray = $this->modelObj->findAllLocations(
            $this->extConf['useLocationStructure'] ?: 'tx_cal_location',
            $this->conf['pidList']
        );

        $locationIdArray = [];
        if ($this->controller->piVars['submit'] && $this->controller->piVars['location_ids']) {
            $locationIdArray = $this->controller->piVars['location_ids'];
        }

        if (is_array($locationArray)) {
            foreach ($locationArray as $location) {
                if (in_array($location->getUid(), $locationIdArray, true)) {
                    $sims['###LOCATION_IDS###'] .= '<option value="' . $location->getUid() . '" selected="selected">' . $location->getName() . '</option>';
                } else {
                    $sims['###LOCATION_IDS###'] .= '<option value="' . $location->getUid() . '">' . $location->getName() . '</option>';
                }
            }
        }
    }

    /**
     * @param $page
     * @param $sims
     * @param $rems
     * @param $view
     */
    public function getOrganizerIdsMarker(&$page, &$sims, &$rems, $view)
    {
        $sims['###ORGANIZER_IDS###'] = '<option  value="">' . $this->controller->pi_getLL('l_all_organizer') . '</option>';
        $organizerArray = $this->modelObj->findAllOrganizer(
            $this->extConf['useOrganizerStructure'] ?: 'tx_cal_organizer',
            $this->conf['pidList']
        );

        $organizerIdArray = [];
        if ($organizerIds !== '') {
            $organizerIdArray = GeneralUtility::intExplode(',', $organizerIds);
        }

        if (is_array($organizerArray)) {
            foreach ($organizerArray as $organizer) {
                if (in_array($organizer->getUid(), $organizerIdArray, true)) {
                    $sims['###ORGANIZER_IDS###'] .= '<option value="' . $organizer->getUid() . '" selected="selected">' . $organizer->getName() . '</option>';
                } else {
                    $sims['###ORGANIZER_IDS###'] .= '<option value="' . $organizer->getUid() . '">' . $organizer->getName() . '</option>';
                }
            }
        }
    }

    /**
     * @param $page
     * @param $sims
     * @param $rems
     * @param $view
     */
    public function getStartAndEnd(&$page, &$sims, &$rems, $view)
    {
        $outputFormat = Functions::getFormatStringFromConf($this->conf);
        if (!$this->controller->piVars['submit']) {
            $date = $this->controller->getListViewTime($this->conf['view.']['search.']['defaultValues.']['start_day']);
            $sims['###EVENT_START_DAY###'] = $date->format($outputFormat);
            $date = $this->controller->getListViewTime($this->conf['view.']['search.']['defaultValues.']['end_day']);
            $sims['###EVENT_END_DAY###'] = $date->format($outputFormat);
        } else {
            if (intval($this->controller->piVars['start_day']) === 0) {
                $sims['###EVENT_START_DAY###'] = $this->starttime->format($outputFormat);
            } else {
                $sims['###EVENT_START_DAY###'] = htmlspecialchars(strip_tags($this->controller->piVars['start_day']));
            }

            if (intval($this->controller->piVars['end_day']) === 0) {
                $sims['###EVENT_END_DAY###'] = $this->endtime->format($outputFormat);
            } else {
                $sims['###EVENT_END_DAY###'] = htmlspecialchars(strip_tags($this->controller->piVars['end_day']));
            }
        }
    }

    /**
     * @param $page
     * @param $sims
     * @param $rems
     * @param $view
     */
    public function getEventStartDayMarker(&$page, &$sims, &$rems, $view)
    {
        if (!$sims['###EVENT_START_DAY###']) {
            $this->getStartAndEnd($page, $sims, $rems, $view);
        }
    }

    /**
     * @param $page
     * @param $sims
     * @param $rems
     * @param $view
     */
    public function getEventEndDayMarker(&$page, &$sims, &$rems, $view)
    {
        if (!$sims['###EVENT_END_DAY###']) {
            $this->getStartAndEnd($page, $sims, $rems, $view);
        }
    }

    /**
     * Draws a search result view.
     *
     * @param $master_array
     * @param $starttime
     * @param $endtime
     * @param $searchword
     * @param string $locationIds
     * @param string $organizerIds
     * @return string HTML output.
     */
    public function drawSearchEventResult(
        &$master_array,
        $starttime,
        $endtime,
        $searchword,
        $locationIds = '',
        $organizerIds = ''
    ): string {
        $this->objectType = 'event';
        $this->_init($master_array);
        return $this->drawList($master_array, '', $starttime, $endtime);
    }

    /**
     * Draws a search result view.
     *
     * @param $master_array
     * @param $searchword
     * @return string HTML output.
     */
    public function drawSearchLocationResult(&$master_array, $searchword): string
    {
        return $this->drawSearchOrganizerResult($master_array, $searchword, 'location');
    }

    /**
     * Draws a search result view.
     *
     * @param array $master_array
     * @param string $searchword
     * @param string $objectType
     * @return string HTML output.
     */
    public function drawSearchOrganizerResult(&$master_array, $searchword, $objectType = 'organizer'): string
    {
        $this->objectType = $objectType;
        $this->_init($master_array);

        if (count($master_array, 1) === 2) {        // only one object element in the array
            if ($this->objectType === 'organizer') {
                return $this->drawOrganizer(array_pop(array_pop($master_array)), $this->conf['getdate']);
            }
            if ($this->objectType === 'location') {
                return $this->drawLocation(array_pop(array_pop($master_array)), $this->conf['getdate']);
            }
        }

        $starttime = new CalendarDateTime();
        $endtime = new CalendarDateTime();
        return $this->drawList($master_array, '', $starttime, $endtime);
    }

    /**
     * @param $page
     */
    public function initTemplate(&$page)
    {
        if ($page === '') {
            $page = Functions::getContent($this->conf['view.']['search.']['searchResult' . ucwords($this->objectType) . 'Template']);
            if ($page === '') {
                $this->error = true;
                $this->errorMessage = 'No search ' . $this->objectType . ' result template file found for "view.search.searchResult' . ucwords($this->objectType) . 'Template" at >' . $this->conf['view.']['search.']['searchResult' . ucwords($this->objectType) . 'Template'] . '<';
                $this->suggestMessage = 'Please make sure the path is correct and that you included the static template and double-check the path using the Typoscript Object Browser.';
                return;
            }
        }
        if ($this->conf['view'] === 'search_all') {
            $rems['###SEARCHFORM###'] = '';
            $page = Functions::substituteMarkerArrayNotCached($page, [], $rems, []);
        }
        $this->page = $page;
    }

    /**
     * @param $page
     * @return string
     */
    public function getListSubpart($page): string
    {
        $listTemplate = $this->markerBasedTemplateService->getSubpart($page, '###LIST_TEMPLATE###');
        if ($listTemplate === '') {
            $this->error = true;
            $this->errorMessage = 'No ###LIST_TEMPLATE### subpart found in "view.search.searchResult' . ucwords($this->objectType) . 'Template" at >' . $this->conf['view.']['search.']['searchResult' . ucwords($this->objectType) . 'Template'] . '<';
            $this->suggestMessage = 'Please include a ###LIST_TEMPLATE### subpart.';
            return null;
        }
        return $listTemplate;
    }

    /**
     * @param $master_array
     * @param $sims
     * @param $rems
     * @return string
     */
    public function processObjects(&$master_array, &$sims, &$rems): string
    {
        if ($this->objectType === 'event') {
            return parent::processObjects($master_array, $sims, $rems);
        }
        // clear the register
        $GLOBALS['TSFE']->register['cal_list_firstevent'] = 0;
        $GLOBALS['TSFE']->register['cal_list_lastevent'] = 0;
        $GLOBALS['TSFE']->register['cal_list_events_total'] = 0;
        $GLOBALS['TSFE']->register['cal_list_eventcounter'] = 0;
        $GLOBALS['TSFE']->register['cal_list_days_total'] = 0;

        $middle = '';

        // only proceed if the master_array is not empty
        if (count($master_array)) {
            $this->count = 0;
            $this->eventCounter = [];
            $this->listStartOffsetCounter = 0;
            $this->listStartOffset = intval($this->conf['view.'][$this->conf['view'] . '.']['listStartOffset']);

            if ($this->conf['view.'][$this->conf['view'] . '.']['pageBrowser.']['usePageBrowser']) {
                $this->offset = intval($this->controller->piVars[$this->pointerName]);
                $this->recordsPerPage = intval($this->conf['view.'][$this->conf['view'] . '.']['pageBrowser.']['recordsPerPage']);
            }

            $this->walkThroughMasterArray($master_array, $reverse, $firstEventDate);

            if ($this->count) {
                $GLOBALS['TSFE']->register['cal_list_events_total'] = $this->count;
                // reference the array with all event counts in the TYPO3 register for usage from within hooks or whatever
                $GLOBALS['TSFE']->register['cal_list_eventcounter'] = &$this->eventCounter;
            }
            if ($days = count($this->objectsInList)) {
                $GLOBALS['TSFE']->register['cal_list_days_total'] = $days;
            }

            // start rendering the organizer
            if ($this->count > 0 && count($this->objectsInList)) {
                $times = array_keys($this->objectsInList);
                $listItemCount = 0;
                $alternationCount = 0;
                $pageItemCount = $this->recordsPerPage * $this->offset;

                // prepare alternating layouts
                $alternatingLayoutConfig = $this->conf['view.'][$this->conf['view'] . '.']['alternatingLayoutMarkers.'];
                if (is_array($alternatingLayoutConfig) && count($alternatingLayoutConfig)) {
                    $alternatingLayouts = [];
                    $layout_keys = array_keys($alternatingLayoutConfig);
                    foreach ($layout_keys as $key) {
                        if (substr($key, strlen($key) - 1) !== '.') {
                            $suffix = $this->cObj->stdWrap(
                                $alternatingLayoutConfig[$key],
                                $alternatingLayoutConfig[$key . '.']
                            );
                            if ($suffix) {
                                $alternatingLayouts[] = $suffix;
                            }
                        }
                    }
                } else {
                    $alternatingLayouts = [
                        'ODD',
                        'EVEN'
                    ];
                }

                // Hook: get hook objects for drawList
                $hookObjectsArr = Functions::getHookObjectsArray(
                    'tx_cal_searchview',
                    'drawList',
                    'view'
                );

                if ($reverse) {
                    arsort($times);
                } else {
                    asort($times);
                }

                foreach ($times as $cal_time) {
                    $object = &$this->objectsInList[$cal_time];

                    // Hook: innerObjectWrapper
                    if (count($hookObjectsArr)) {
                        // use referenced hook objects, so that hook objects can store variables among different hook calls internally and don't have to mess with globals or registers
                        $hookObjectKeys = array_keys($hookObjectsArr);
                        foreach ($hookObjectKeys as $hookObjKey) {
                            $hookObj = &$hookObjectsArr[$hookObjKey];
                            if (method_exists($hookObj, 'innerObjectWrapper')) {
                                $hookObj->innerObjectWrapper($this, $middle, $object);
                            }
                        }
                    }

                    $listItemCount++;
                    $totalListCount = $listItemCount + $pageItemCount;
                    $GLOBALS['TSFE']->register['cal_event_list_num'] = $listItemCount;
                    $GLOBALS['TSFE']->register['cal_event_list_num_total'] = $totalListCount;

                    $layoutNum = $alternationCount % count($alternatingLayouts);
                    $layoutSuffix = $alternatingLayouts[$layoutNum];
                    $functionName = 'render' . ucwords($this->objectType) . 'For';
                    $objectText = $object->$functionName(strtoupper($this->conf['view']), $layoutSuffix);

                    $allowFurtherGrouping = true;
                    // Hook: prepareOuterObjectWrapper
                    if (count($hookObjectsArr)) {
                        // use referenced hook objects, so that hook objects can store variables among different hook calls internally and don't have to mess with globals or registers
                        $hookObjectKeys = array_keys($hookObjectsArr);
                        foreach ($hookObjectKeys as $hookObjKey) {
                            $hookObj = &$hookObjectsArr[$hookObjKey];
                            if (method_exists($hookObj, 'prepareOuterObjectWrapper')) {
                                $hookObj->prepareOuterObjectWrapper($this, $middle, $object, $allowFurtherGrouping);
                            }
                        }
                    }

                    $alternationCount++;
                    $middle .= $objectText;
                }

                $allowFurtherGrouping = true;

                // Hook: applyOuterObjectWrapper
                if (count($hookObjectsArr)) {
                    // use referenced hook objects, so that hook objects can store variables among different hook calls internally and don't have to mess with globals or registers
                    $hookObjectKeys = array_keys($hookObjectsArr);
                    foreach ($hookObjectKeys as $hookObjKey) {
                        $hookObj = &$hookObjectsArr[$hookObjKey];
                        if (method_exists($hookObj, 'applyOuterObjectWrapper')) {
                            $hookObj->applyOuterObjectWrapper($this, $middle, $object, $allowFurtherGrouping);
                        }
                    }
                }
            }
        }
        return $middle;
    }

    /**
     * @param $master_array
     * @param $reverse
     * @param $firstEventDate
     */
    public function walkThroughMasterArray(&$master_array, $reverse, &$firstEventDate)
    {
        if ($this->objectType === 'event') {
            return parent::walkThroughMasterArray($master_array, $reverse, $firstEventDate);
        }
        if (is_array($master_array)) {
            foreach ($master_array as $a => $b) {
                if (is_array($b)) {
                    foreach ($b as $id => $object) {
                        $this->processObject($object, $id, $firstEventDate);
                    }
                }
            }
        }
    }

    /**
     * @param $object
     * @param $id
     * @param $firstEventDate
     * @return bool
     */
    public function processObject(&$object, &$id, &$firstEventDate): bool
    {
        $finished = '';
        if ($this->objectType === 'event') {
            return parent::processObject($object, $id, $firstEventDate);
        }
        // Pagebrowser
        if ($this->conf['view.'][$this->conf['view'] . '.']['pageBrowser.']['usePageBrowser']) {
            $this->eventCounter['count']['total']++;
            if ($this->count < $this->recordsPerPage * $this->offset) {
                $this->eventCounter['count']['previousPages']++;
            } elseif ($this->count > $this->recordsPerPage * $this->offset + $this->recordsPerPage - 1) {
                $this->eventCounter['count']['nextPages']++;
            } else {
                $this->eventCounter['count']['currentPage']++;
            }

            if ($this->count < $this->recordsPerPage * $this->offset || $this->count > $this->recordsPerPage * $this->offset + $this->recordsPerPage - 1) {
                $this->count++;
                if ($this->count === intval($this->conf['view.']['list.']['maxEvents'])) {
                    $finished = true;
                }
                return $finished;
            }
        }
        $this->objectsInList[] = $object;
        $this->count++;
    }

    /**
     * @param $page
     * @param $sims
     * @param $rems
     * @param $view
     */
    public function getSearchAllLinkMarker(&$page, &$sims, &$rems, $view)
    {
        $sims['###SEARCH_ALL_LINK###'] = '';
        if ($this->conf['view'] !== 'search_all' && $this->rightsObj->isViewEnabled('search_all')) {
            $this->initLocalCObject();
            $this->local_cObj->setCurrentVal($this->controller->pi_getLL('l_search_everything'));
            $this->controller->getParametersForTyposcriptLink($this->local_cObj->data, [
                'view' => 'search_all'
            ], $this->conf['cache'], $this->conf['clear_anyway'], $this->conf['page_id']);
            $sims['###SEARCH_ALL_LINK###'] = $this->local_cObj->cObjGetSingle(
                $this->conf['view.']['search.']['searchAllLink'],
                $this->conf['view.']['search.']['searchAllLink.']
            );
        }
    }
}
