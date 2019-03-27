<?php

/*
 * This file is part of the web-tp3/cal.
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace TYPO3\CMS\Cal\Service;

use RuntimeException;
use TYPO3\CMS\Cal\Model\CategoryModel;
use TYPO3\CMS\Cal\TreeProvider\TreeView;
use TYPO3\CMS\Core\Utility\GeneralUtility;

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
 * @deprecated since ext:cal v2, will be removed in ext:cal v3
 */
class CategoryService extends BaseService
{
    public $categoryArrayByEventUid = [];
    public $categoryArrayByCalendarUid;
    public $categoryArrayByUid = [];
    public $allCateogryIdsByParentId;
    public $categoryArrayCached = [];
    public static $categoryToFilter;

    public function __construct()
    {
        parent::__construct();
        $this->rightsObj =  $this->objectManager->get(RightsService::class);
        trigger_error('Deprecated since ext:cal v2, will be removed in ext:cal v3. Affected class: ' . get_class($this), E_USER_DEPRECATED);
    }

    /**
     * Looks for a category with a given uid on a certain pid-list
     *
     * @param int $uid
     *            to search for
     * @param string $pidList
     *            to search in
     * @return array array ($row)
     */
    public function find($uid, $pidList): array
    {
        trigger_error('Deprecated since ext:cal v2, will be removed in ext:cal v3. Affected class: ' . get_class($this), E_USER_DEPRECATED);

        $categoryIds = [];
        $this->getCategoryArray($pidList, $categoryIds, true);
        return $this->categoryArrayByUid[$uid];
    }

    /**
     * Looks for all categorys on a certain pid-list
     *
     * @param string $pidList
     *            to search in
     * @param $categoryArrayToBeFilled
     */
    public function findAll($pidList, &$categoryArrayToBeFilled)
    {
        trigger_error('Deprecated since ext:cal v2, will be removed in ext:cal v3. Affected class: ' . get_class($this), E_USER_DEPRECATED);

        $this->getCategoryArray($pidList, $categoryArrayToBeFilled, true);
    }

    /**
     * @param $uid
     * @return array
     */
    public function updateCategory($uid): array
    {
        trigger_error('Deprecated since ext:cal v2, will be removed in ext:cal v3. Affected class: ' . get_class($this), E_USER_DEPRECATED);

        $insertFields = [
            'tstamp' => time()
        ];
        // TODO: Check if all values are correct
        $this->searchForAdditionalFieldsToAddFromPostData($insertFields, 'category', false);
        $this->retrievePostData($insertFields);
        $uid = $this->checkUidForLanguageOverlay($uid, 'tx_cal_category');
        // Creating DB records
        $table = 'tx_cal_category';
        $where = 'uid = ' . $uid;

        $GLOBALS['TYPO3_DB']->exec_UPDATEquery($table, $where, $insertFields);

        $this->unsetPiVars();
        return $this->find($uid, $this->conf['pidList']);
    }

    /**
     * @param $uid
     */
    public function removeCategory($uid)
    {
        trigger_error('Deprecated since ext:cal v2, will be removed in ext:cal v3. Affected class: ' . get_class($this), E_USER_DEPRECATED);

        if ($this->rightsObj->isAllowedToDeleteCategory()) {
            // 'delete' the category object
            $updateFields = [
                'tstamp' => time(),
                'deleted' => 1
            ];
            $table = 'tx_cal_category';
            $where = 'uid = ' . $uid;
            $GLOBALS['TYPO3_DB']->exec_UPDATEquery($table, $where, $updateFields);

            $this->unsetPiVars();
        }
    }

    /**
     * @param $insertFields
     */
    private function retrievePostData(&$insertFields)
    {
        trigger_error('Deprecated since ext:cal v2, will be removed in ext:cal v3. Affected class: ' . get_class($this), E_USER_DEPRECATED);

        $hidden = 0;
        if ($this->controller->piVars['hidden'] == '1' && ($this->rightsObj->isAllowedToEditCategoryHidden() || $this->rightsObj->isAllowedToCreateCategoryHidden())) {
            $hidden = 1;
        }
        $insertFields['hidden'] = $hidden;

        if ($this->rightsObj->isAllowedToEditCategoryTitle() || $this->rightsObj->isAllowedToCreateCategoryTitle()) {
            $insertFields['title'] = strip_tags($this->controller->piVars['title']);
        }

        if ($this->rightsObj->isAllowedToEditCategoryCalendar() || $this->rightsObj->isAllowedToCreateCategoryCalendar()) {
            $insertFields['calendar_id'] = intval($this->controller->piVars['calendar_id']);
        }

        if ($this->rightsObj->isAllowedToEditCategoryParent() || $this->rightsObj->isAllowedToCreateCategoryParent()) {
            $insertFields['parent_category'] = intval($this->controller->piVars['parent_category']);
        }

        if ($this->rightsObj->isAllowedToEditCategoryHeaderstyle() || $this->rightsObj->isAllowedToCreateCategoryHeaderStyle()) {
            $insertFields['headerstyle'] = strip_tags($this->controller->piVars['headerstyle']);
        }

        if ($this->rightsObj->isAllowedToEditCategoryBodystyle() || $this->rightsObj->isAllowedToCreateCategoryBodyStyle()) {
            $insertFields['bodystyle'] = strip_tags($this->controller->piVars['bodystyle']);
        }

        if ($this->rightsObj->isAllowedToEditCategorySharedUser() || $this->rightsObj->isAllowedToCreateCategorySharedUser()) {
            $insertFields['shared_user_allowed'] = intval($this->controller->piVars['shared_user_allowed']);
        }
    }

    /**
     * @param $pid
     * @return array
     */
    public function saveCategory($pid): array
    {
        trigger_error('Deprecated since ext:cal v2, will be removed in ext:cal v3. Affected class: ' . get_class($this), E_USER_DEPRECATED);

        $crdate = time();
        $insertFields = [
            'pid' => $this->conf['rights.']['create.']['calendar.']['saveCategoryToPid'] ?: $pid,
            'tstamp' => $crdate,
            'crdate' => $crdate
        ];
        $this->searchForAdditionalFieldsToAddFromPostData($insertFields, 'category');
        $this->retrievePostData($insertFields);

        // Creating DB records
        $insertFields['cruser_id'] = $this->rightsObj->getUserId();
        $uid = $this->_saveCategory($insertFields);
        $this->unsetPiVars();
        return $this->find($uid, $this->conf['pidList']);
    }

    /**
     * @param $insertFields
     * @return mixed
     */
    private function _saveCategory(&$insertFields)
    {
        trigger_error('Deprecated since ext:cal v2, will be removed in ext:cal v3. Affected class: ' . get_class($this), E_USER_DEPRECATED);

        $table = 'tx_cal_category';
        $result = $GLOBALS['TYPO3_DB']->exec_INSERTquery($table, $insertFields);
        if (false === $result) {
            throw new RuntimeException(
                'Could not write ' . $table . ' record to database: ' . $GLOBALS['TYPO3_DB']->sql_error(),
                1431458140
            );
        }
        $uid = $GLOBALS['TYPO3_DB']->sql_insert_id();
        return $uid;
    }

    /**
     * @param $pidList
     * @param $includePublic
     * @return string
     */
    public function getCategorySearchString($pidList, $includePublic): string
    {
        trigger_error('Deprecated since ext:cal v2, will be removed in ext:cal v3. Affected class: ' . get_class($this), E_USER_DEPRECATED);

        if ($this->conf['category'] != '') {
            $categorySearchString .= ' AND tx_cal_event_category_mm.uid_foreign IN (' . $this->conf['category'] . ')';
        }

        // Filter events by categories

        // Include categories
        if ($this->conf['view.']['categoryMode'] == 1 && self::$categoryToFilter) {
            // Query to select all blacklisted events
            $sql = 'SELECT uid_local FROM tx_cal_event_category_mm WHERE uid_foreign IN (' . self::$categoryToFilter . ')';
            // Add search substring with tx_cal_event.uid NOT IN
            $categorySearchString .= ' AND tx_cal_event.uid NOT IN (' . $sql . ')';
        }

        // Exclude categories
        if ($this->conf['view.']['categoryMode'] == 2 && self::$categoryToFilter) {
            // Query to select all blacklisted events
            $sql = 'SELECT uid_local FROM tx_cal_event_category_mm WHERE uid_foreign IN (' . self::$categoryToFilter . ')';
            // Add search substring with tx_cal_event.uid NOT IN
            $categorySearchString .= ' AND tx_cal_event.uid IN (' . $sql . ')';
        }

        // Minimum match
        if ($this->conf['view.']['categoryMode'] == 4 && self::$categoryToFilter) {
            $categorySearchString = '';
            $categories = explode(',', self::$categoryToFilter);
            for ($i = 0, $iMax = count($categories); $i < $iMax; $i++) {
                if ($i == 0) {
                    $categorySearchString .= ' AND tx_cal_event_category_mm.uid_foreign = "' . $categories[$i] . '" ';
                } else {
                    $categorySearchString .= ' AND (';

                    $categorySearchString .= '	SELECT
													tx_cal_event' . $i . '.uid
												FROM
													tx_cal_event_category_mm tx_cal_event_category_mm' . $i . '
													JOIN tx_cal_event tx_cal_event' . $i . ' ON tx_cal_event_category_mm' . $i . '.uid_local = tx_cal_event' . $i . '.uid
												WHERE
													tx_cal_event' . $i . '.uid = tx_cal_event.uid
													AND tx_cal_event_category_mm' . $i . '.uid_foreign = "' . $categories[$i] . '"
												GROUP BY
													tx_cal_event_category_mm' . $i . '.uid_foreign)';
                }
            }
        }

        return $categorySearchString;
    }

    /**
     * Search for categories
     * @param $pidList
     * @param $categoryArrayToBeFilled
     * @param bool $showPublicCategories
     */
    public function getCategoryArray($pidList, &$categoryArrayToBeFilled, $showPublicCategories = true)
    {
        trigger_error('Deprecated since ext:cal v2, will be removed in ext:cal v3. Affected class: ' . get_class($this), E_USER_DEPRECATED);

        if (!empty($this->categoryArrayCached[md5($this->conf['view.']['categoryMode'] . $this->conf['view.']['allowedCategories'])])) {
            $categoryArrayToBeFilled[] = $this->categoryArrayCached[md5($this->conf['view.']['categoryMode'] . $this->conf['view.']['allowedCategories'])];
            return;
        }

        $this->categoryArrayByUid = [];
        $this->categoryArrayByEventUid = [];
        $this->categoryArrayByCalendarUid = [];

        $additionalWhere = ' AND tx_cal_category.pid IN (' . $pidList . ')';

        // ompile category array
        $filterWhere = '';
        switch ($this->conf['view.']['categoryMode']) {
            case 0: // show all
                break;
            case 1: // show selected
            case 3:
                $allowedCategories = GeneralUtility::trimExplode(
                    ',',
                    $this->cObj->stdWrap($this->conf['view.']['category'], $this->conf['view.']['category.']),
                    1
                );
                if (!empty($allowedCategories)) {
                    $implodedAllowedCategories = implode(',', $allowedCategories);
                    $filterWhere = ' AND tx_cal_category.uid IN (' . $implodedAllowedCategories . ')';

                    $select = 'tx_cal_category.uid';
                    $table = 'tx_cal_category';
                    $groupby = '';
                    $orderby = '';
                    $where = 'tx_cal_category.uid NOT IN (' . $implodedAllowedCategories . ')' . ' AND tx_cal_category.pid IN (' . $pidList . ') ' . $this->cObj->enableFields('tx_cal_category');

                    $result = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select, $table, $where, $groupby, $orderby);
                    if ($result) {
                        $excludedCategories = [];
                        while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
                            $excludedCategories[] = $row['uid'];
                        }
                        $GLOBALS['TYPO3_DB']->sql_free_result($result);
                        self::$categoryToFilter = implode(',', $excludedCategories);
                    }
                }
                break;
            case 2: // exclude selected
                $allowedCategories = GeneralUtility::trimExplode(
                    ',',
                    $this->cObj->stdWrap($this->conf['view.']['category'], $this->conf['view.']['category.']),
                    1
                );
                if (!empty($allowedCategories)) {
                    $implodedAllowedCategories = implode(',', $allowedCategories);
                    $filterWhere = ' AND tx_cal_category.uid NOT IN (' . $implodedAllowedCategories . ')';
                    self::$categoryToFilter = $implodedAllowedCategories;
                }
                break;
            case 4: // minimum match
                $allowedCategories = GeneralUtility::trimExplode(
                    ',',
                    $this->cObj->stdWrap($this->conf['view.']['category'], $this->conf['view.']['category.']),
                    1
                );
                if (!empty($allowedCategories)) {
                    $implodedAllowedCategories = implode(',', $allowedCategories);
                    self::$categoryToFilter = $implodedAllowedCategories;
                }
                break;

        }

        if (!$this->rightsObj->isCalAdmin() && $this->conf['rights.'][$this->conf['view'] == 'create_event' ? 'create.' : 'edit.']['event.']['fields.']['category.']['allowedUids'] != '') {
            $filterWhere = ' AND tx_cal_category.uid IN (' . $this->conf['rights.'][$this->conf['view'] == 'create_event' ? 'create.' : 'edit.']['event.']['fields.']['category.']['allowedUids'] . ')';
        }

        $calendarService = &$this->modelObj->getServiceObjByKey('cal_calendar_model', 'calendar', 'tx_cal_calendar');
        $calendarSearchString = $calendarService->getCalendarSearchString(
            $pidList,
            $showPublicCategories,
            $this->conf['calendar'] ?: ''
        );
        // Select all categories for the given pids
        $select = 'tx_cal_category.*,tx_cal_calendar.title AS calendar_title,tx_cal_calendar.uid AS calendar_uid';
        $table = 'tx_cal_category LEFT JOIN tx_cal_calendar ON tx_cal_category.calendar_id=tx_cal_calendar.uid';
        $groupby = 'tx_cal_category.uid';
        $orderby = 'calendar_id,tx_cal_category.title ASC';
        $where = '1=1 ';
        $where .= $calendarSearchString;
        $where .= ' AND tx_cal_category.pid IN (' . $pidList . ') ' . $this->cObj->enableFields('tx_cal_category');
        $where .= $additionalWhere . $filterWhere;

        $where .= $this->getAdditionalWhereForLocalizationAndVersioning('tx_cal_category');

        $result = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select, $table, $where, $groupby, $orderby);
        $foundUids = [];
        $calendarUids = [];
        if ($result) {
            while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
                if ($GLOBALS['TSFE']->sys_language_content) {
                    $row = $GLOBALS['TSFE']->sys_page->getRecordOverlay(
                        'tx_cal_category',
                        $row,
                        $GLOBALS['TSFE']->sys_language_content,
                        $GLOBALS['TSFE']->sys_language_contentOL
                    );
                }
                if (!$row['uid']) {
                    continue;
                }
                if ($GLOBALS['TSFE']->sys_page->versioningPreview == true) {
                    // get workspaces Overlay
                    $GLOBALS['TSFE']->sys_page->versionOL('tx_cal_category', $row);
                }
                if (!$row['uid']) {
                    continue;
                }
                $category = $this->createCategory($row);
                $foundUids[] = $row['uid'];
                $calendarUids[] = $row['calendar_uid'];

                $this->categoryArrayByUid[$row['uid']] = $category;
                $this->categoryArrayByCalendarUid[$row['calendar_uid'] . '###' . $row['calendar_title'] . '###tx_cal_calendar'][] = $category->getUid();
            }
            $GLOBALS['TYPO3_DB']->sql_free_result($result);
        }

        $calendarService->getCalendarsWithoutCategory(
            $calendarSearchString,
            $calendarUids,
            $this->categoryArrayByCalendarUid
        );

        $additionalWhere = $filterWhere;
        // Select all global categories
        $select = 'tx_cal_category.*';
        $table = 'tx_cal_category';
        $groupby = 'tx_cal_category.uid';
        $orderby = 'tx_cal_category.title ASC';
        if (!empty($foundUids)) {
            $additionalWhere .= ' AND tx_cal_category.uid NOT IN (' . implode(',', $foundUids) . ')';
        }
        $where = 'tx_cal_category.calendar_id = 0' . $this->cObj->enableFields('tx_cal_category') . $additionalWhere;
        $where .= $this->getAdditionalWhereForLocalizationAndVersioning('tx_cal_category');

        $result = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select, $table, $where, $groupby, $orderby);
        if ($result) {
            while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
                if ($GLOBALS['TSFE']->sys_language_content) {
                    $row = $GLOBALS['TSFE']->sys_page->getRecordOverlay(
                        'tx_cal_category',
                        $row,
                        $GLOBALS['TSFE']->sys_language_content,
                        $GLOBALS['TSFE']->sys_language_contentOL
                    );
                }
                if (!$row['uid']) {
                    continue;
                }
                if ($GLOBALS['TSFE']->sys_page->versioningPreview == true) {
                    // get workspaces Overlay
                    $GLOBALS['TSFE']->sys_page->versionOL('tx_cal_category', $row);
                }
                if (!$row['uid']) {
                    continue;
                }

                $category = $this->createCategory($row);
                $this->categoryArrayByUid[$row['uid']] = $category;
                $this->categoryArrayByCalendarUid['0###' . $this->controller->pi_getLL('l_global_category')][] = $category->getUid();
            }
            $GLOBALS['TYPO3_DB']->sql_free_result($result);
        }

        // Map styles
        foreach ($this->categoryArrayByUid as $category) {
            $this->checkStyles($category);
        }

        // Map categories to events
        $select = 'tx_cal_event_category_mm.*';
        $table = 'tx_cal_event_category_mm';
        $groupby = '';
        $orderby = 'uid_local ASC, sorting ASC';
        $where = '';
        $result = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select, $table, $where, $groupby, $orderby);
        if ($result) {
            while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
                if ($this->categoryArrayByUid[$row['uid_foreign']]) {
                    $this->categoryArrayByEventUid[$row['uid_local']][] = $this->categoryArrayByUid[$row['uid_foreign']];
                }
            }
            $GLOBALS['TYPO3_DB']->sql_free_result($result);
        }

        if ($this->conf['view.']['freeAndBusy.']['enable']) {
            $select = 'tx_cal_category.*, tx_cal_calendar.title AS calendar_title';
            $where = 'tx_cal_category.shared_user_allowed = 1';
            $where .= $calendarService->getCalendarSearchString(
                $pidList,
                $showPublicCategories,
                $this->conf['view.']['calendar'] ?: ''
            );
            $where .= $this->cObj->enableFields('tx_cal_calendar') . $this->cObj->enableFields('tx_cal_category') . $this->cObj->enableFields('tx_cal_event');
            $where .= $this->getAdditionalWhereForLocalizationAndVersioning('tx_cal_category');
            $table = 'tx_cal_event LEFT JOIN tx_cal_event_shared_user_mm ON tx_cal_event.uid = tx_cal_event_shared_user_mm.uid_local ' . 'LEFT JOIN tx_cal_calendar ON tx_cal_event.calendar_id = tx_cal_calendar.uid ' . 'LEFT JOIN tx_cal_category ON tx_cal_calendar.uid = tx_cal_category.calendar_id';

            $result = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select, $table, $where, $groupby);
            if ($result) {
                while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
                    if ($GLOBALS['TSFE']->sys_language_content) {
                        $row = $GLOBALS['TSFE']->sys_page->getRecordOverlay(
                            'tx_cal_category',
                            $row,
                            $GLOBALS['TSFE']->sys_language_content,
                            $GLOBALS['TSFE']->sys_language_contentOL
                        );
                    }
                    if (!$row['uid']) {
                        continue;
                    }
                    if ($GLOBALS['TSFE']->sys_page->versioningPreview == true) {
                        // get workspaces Overlay
                        $GLOBALS['TSFE']->sys_page->versionOL('tx_cal_category', $row);
                    }
                    if (!$row['uid']) {
                        continue;
                    }

                    $category = $this->createCategory($row);
                    $this->categoryArrayByEventUid[$row['uid_local']][] = $category;
                    $this->categoryArrayByUid[$row['uid']] = $category;
                }
                $GLOBALS['TYPO3_DB']->sql_free_result($result);
            }
        }

        $categoryStringByUid = implode(',', array_keys($this->categoryArrayByUid));
        $categoryMultiArray = [
            $this->categoryArrayByUid,
            $this->categoryArrayByEventUid,
            $this->categoryArrayByCalendarUid
        ];
        if ($categoryStringByUid) {
            $this->categoryArrayCached[md5($this->conf['view.']['categoryMode'] . $categoryStringByUid)] = $categoryMultiArray;
        }
        $categoryArrayToBeFilled[] = $categoryMultiArray;
    }

    /**
     * @param $categoryArray
     */
    public function addChildCategories(&$categoryArray)
    {
        trigger_error('Deprecated since ext:cal v2, will be removed in ext:cal v3. Affected class: ' . get_class($this), E_USER_DEPRECATED);

        $calTreeView = new TreeView();

        $ids = [];
        $knownUids = [];
        foreach ($categoryArray as $category) {
            $ids = $calTreeView->checkChildIds($category->row, $this->getAllCategoryIdsByParentId());
            $knownUids[] = $category->getUid();
        }
        $stillNeededChildCategoryIds = array_diff($ids, $knownUids);
        if (!empty($stillNeededChildCategoryIds)) {
            $select = 'tx_cal_category.*';
            $table = 'tx_cal_category';
            $where = 'tx_cal_category.uid IN (' . implode(',', $stillNeededChildCategoryIds) . ')';
        }
    }

    /**
     * @return array
     */
    public function getAllCategoryIdsByParentId(): array
    {
        trigger_error('Deprecated since ext:cal v2, will be removed in ext:cal v3. Affected class: ' . get_class($this), E_USER_DEPRECATED);

        if ($this->allCateogryIdsByParentId == null) {
            $categories = [];
            $select = 'tx_cal_category.uid, tx_cal_category.parent_category';
            $table = 'tx_cal_category';
            $where = '1=1' . $this->cObj->enableFields('tx_cal_category');
            $groupby = '';

            $result = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select, $table, $where, $groupby);
            if ($result) {
                while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
                    $categories[$row['parent_category']] = $row['uid'];
                }
                $GLOBALS['TYPO3_DB']->sql_free_result($result);
            }
            $this->allCateogryIdsByParentId = $categories;
        }
        return $this->allCateogryIdsByParentId;
    }

    /**
     * @return array
     */
    public function getCategoriesForSharedUser(): array
    {
        trigger_error('Deprecated since ext:cal v2, will be removed in ext:cal v3. Affected class: ' . get_class($this), E_USER_DEPRECATED);

        $select = '*';
        $table = 'tx_cal_event LEFT JOIN tx_cal_event_shared_user_mm ON tx_cal_event.uid = tx_cal_event_shared_user_mm.uid_local ' . 'LEFT JOIN tx_cal_calendar ON tx_cal_event.calendar_id = tx_cal_calendar.uid ' . 'LEFT JOIN tx_cal_category ON tx_cal_calendar.uid = tx_cal_category.calendar_id';
        $where = 'tx_cal_category.shared_user_allowed = 1' . ' AND tx_cal_event_shared_user_mm.uid_foreign = ' . $this->rightsObj->getUserId() . $this->cObj->enableFields('tx_cal_calendar') . $this->cObj->enableFields('tx_cal_category') . $this->cObj->enableFields('tx_cal_event');

        $groupby = '';

        return $this->getCategoriesFromTable($select, $table, $where, $groupby);
    }

    /**
     * @param $select
     * @param $table
     * @param $where
     * @param string $groupby
     * @return array
     */
    public function getCategoriesFromTable($select, $table, $where, $groupby = ''): array
    {
        trigger_error('Deprecated since ext:cal v2, will be removed in ext:cal v3. Affected class: ' . get_class($this), E_USER_DEPRECATED);

        $categories = [];
        $result = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select, $table, $where, $groupby);
        if ($result) {
            while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
                if ($GLOBALS['TSFE']->sys_language_content) {
                    $row = $GLOBALS['TSFE']->sys_page->getRecordOverlay(
                        'tx_cal_category',
                        $row,
                        $GLOBALS['TSFE']->sys_language_content,
                        $GLOBALS['TSFE']->sys_language_contentOL
                    );
                }
                if (!$row['uid']) {
                    continue;
                }

                $GLOBALS['TSFE']->sys_page->versionOL('tx_cal_category', $row);
                $GLOBALS['TSFE']->sys_page->fixVersioningPid('tx_cal_category', $row);

                if (!$row['uid']) {
                    continue;
                }
                $categories[$row['uid']] = $row;
            }
            $GLOBALS['TYPO3_DB']->sql_free_result($result);
        }
        return $categories;
    }

    /**
     * @param $row
     * @return CategoryModel
     */
    public function createCategory($row): CategoryModel
    {
        trigger_error('Deprecated since ext:cal v2, will be removed in ext:cal v3. Affected class: ' . get_class($this), E_USER_DEPRECATED);

        return new CategoryModel($row, $this->getServiceKey());
    }

    /**
     * @param $eventUid
     * @return mixed
     */
    public function getCategoriesForEvent($eventUid)
    {
        trigger_error('Deprecated since ext:cal v2, will be removed in ext:cal v3. Affected class: ' . get_class($this), E_USER_DEPRECATED);

        if (count($this->categoryArrayByEventUid) == 0) {
            $cats = [];
            $this->findAll($this->conf['pidList'], $cats);
        }
        return $this->categoryArrayByEventUid[$eventUid];
    }

    /**
     * @param $category
     */
    public function checkStyles(&$category)
    {
        trigger_error('Deprecated since ext:cal v2, will be removed in ext:cal v3. Affected class: ' . get_class($this), E_USER_DEPRECATED);

        $headerStyle = $category->getHeaderStyle();
        if ($headerStyle == '') {
            $parentUid = $category->getParentUid();
            if ($parentUid == 0) {
                $category->setHeaderStyle($this->conf['view.']['category.']['category.']['defaultHeaderStyle']);
                $category->setBodyStyle($this->conf['view.']['category.']['category.']['defaultBodyStyle']);
            } else {
                if ($this->categoryArrayByUid[$parentUid]) {
                    $this->checkStyles($this->categoryArrayByUid[$parentUid]);
                    $category->setHeaderStyle($this->categoryArrayByUid[$parentUid]->getHeaderStyle());
                    $category->setBodyStyle($this->categoryArrayByUid[$parentUid]->getBodyStyle());
                } else {
                    $category->setHeaderStyle($this->conf['view.']['category.']['category.']['defaultHeaderStyle']);
                    $category->setBodyStyle($this->conf['view.']['category.']['category.']['defaultBodyStyle']);
                }
            }
        }
    }

    public function unsetPiVars()
    {
        trigger_error('Deprecated since ext:cal v2, will be removed in ext:cal v3. Affected class: ' . get_class($this), E_USER_DEPRECATED);

        unset($this->controller->piVars['hidden'], $this->controller->piVars['uid'], $this->controller->piVars['calendar'], $this->controller->piVars['type'], $this->controller->piVars['calendar_id'], $this->controller->piVars['category'], $this->controller->piVars['shared_user_allowed'], $this->controller->piVars['headerstyle'], $this->controller->piVars['bodystyle'], $this->controller->piVars['parent_category'], $this->controller->piVars['title']);
    }

    /**
     * @param $uid
     * @param $overlay
     * @deprecated since ext:cal v2, will be removed in ext:cal v3
     */
    public function createTranslation($uid, $overlay)
    {
        trigger_error('Deprecated since ext:cal v2, will be removed in ext:cal v3. Affected class: ' . get_class($this), E_USER_DEPRECATED);

        $table = 'tx_cal_category';
        $select = $table . '.*';
        $where = $table . '.uid = ' . $uid;

        $result = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select, $table, $where);
        if ($result) {
            while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
                unset($row['uid']);
                $crdate = time();
                $row['tstamp'] = $crdate;
                $row['crdate'] = $crdate;
                $row['l18n_parent'] = $uid;
                $row['sys_language_uid'] = $overlay;
                $this->_saveCategory($row);
                return;
            }
            $GLOBALS['TYPO3_DB']->sql_free_result($result);
        }
    }

    /**
     * @param $select
     * @param $table
     * @param $where
     * @param $groupBy
     * @param $orderBy
     */
    public function enhanceEventQuery(&$select, &$table, &$where, &$groupBy, &$orderBy)
    {
        trigger_error('Deprecated since ext:cal v2, will be removed in ext:cal v3. Affected class: ' . get_class($this), E_USER_DEPRECATED);

        $select .= ', tx_cal_event_category_mm.uid_foreign AS category_uid ';
        $table .= ' LEFT JOIN tx_cal_event_category_mm ON tx_cal_event_category_mm.uid_local = tx_cal_event.uid';
        $where .= $this->getCategorySearchString($this->conf['pidList'], true);
        $groupBy = 'tx_cal_event.uid';
        if ($this->conf['view.']['joinCategoryByAnd']) {
            $categoryArray = GeneralUtility::trimExplode(',', $this->conf['category'], 1);
            $groupBy .= ', tx_cal_event_category_mm.uid_foreign HAVING count(*) =' . count($categoryArray);
        }
        $orderBy .= ', tx_cal_event.uid,tx_cal_event_category_mm.sorting';

        if ($this->conf['view.'][$this->conf['view'] . '.']['event.']['additionalCategoryWhere']) {
            $where .= ' ' . $this->cObj->cObjGetSingle(
                $this->conf['view.'][$this->conf['view'] . '.']['event.']['additionalCategoryWhere'],
                $this->conf['view.'][$this->conf['view'] . '.']['event.']['additionalCategoryWhere.']
                );
        }
    }

    /**
     * @return array
     */
    public function getUidsOfEventsWithCategories(): array
    {
        trigger_error('Deprecated since ext:cal v2, will be removed in ext:cal v3. Affected class: ' . get_class($this), E_USER_DEPRECATED);

        $uidCollector = [];
        $select = 'tx_cal_event_category_mm.*, tx_cal_event.pid, tx_cal_event.uid';
        $table = 'tx_cal_event_category_mm LEFT JOIN tx_cal_event ON tx_cal_event.uid = tx_cal_event_category_mm.uid_local';
        $groupby = 'tx_cal_event_category_mm.uid_local';
        $orderby = '';
        $where = 'tx_cal_event.pid IN (' . $this->conf['pidList'] . ')';

        $result = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select, $table, $where, $groupby, $orderby);
        if ($result) {
            while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
                $uidCollector[] = $row['uid_local'];
            }
            $GLOBALS['TYPO3_DB']->sql_free_result($result);
        }
        return $uidCollector;
    }
}
