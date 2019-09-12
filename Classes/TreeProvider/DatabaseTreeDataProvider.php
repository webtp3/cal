<?php
namespace TYPO3\CMS\Cal\TreeProvider;

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
use PDO;
use TYPO3\CMS\Backend\Tree\SortedTreeNodeCollection;
use TYPO3\CMS\Backend\Tree\TreeNode;
use TYPO3\CMS\Backend\Tree\TreeNodeCollection;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Tree\TableConfiguration\DatabaseTreeNode;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * TCA tree data provider which considers
 */
class DatabaseTreeDataProvider extends \TYPO3\CMS\Core\Tree\TableConfiguration\DatabaseTreeDataProvider
{

    /**
     * @var \TYPO3\CMS\Core\Authentication\BackendUserAuthentication
     */
    protected $backendUserAuthentication;

    protected $parentRow;

    protected $table;

    protected $field;

    protected $currentValue;

    protected $conf;

    protected $calConfiguration;

    /** @var ConnectionPool $connectionPool */
    protected $connectionPool;

    const CALENDAR_PREFIX = 'calendar_';
    const GLOBAL_PREFIX = 'global';

    /**
     * Required constructor
     *
     * @param array $configuration TCA configuration
     */
    public function __construct(array $configuration, $table, $field, $currentValue)
    {
        $this->table = $table;
        $this->field = $field;
        $this->conf = $configuration;
        $this->currentValue = $currentValue;
        $this->backendUserAuthentication = $GLOBALS['BE_USER'];
        $this->calConfiguration = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['cal']);
        $this->connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);
    }

    /**
     * Sets the list for selected nodes
     *
     * @param string $selectedList
     */
    public function setSelectedList($selectedList)
    {
        // During initialization the first set contains the parent object row.
        // Where as the second call really fills the correct values.
        if ($this->selectedList == '') {
            $this->parentRow = $selectedList;
        }
        $this->selectedList = $selectedList;
    }

    /**
     * Loads the tree data (all possible children)
     */
    protected function loadTreeData()
    {
        if ($this->calConfiguration['categoryService'] == 'sys_category') {
            parent::loadTreeData();
            return;
        }
        $this->treeData->setId($this->getRootUid());
        $this->treeData->setParentNode(null);
        $level = 1;

        if ($this->levelMaximum >= $level) {
            $childNodes = GeneralUtility::makeInstance(TreeNodeCollection::class);

            $this->appendGlobalCategories($level, $childNodes);
            $this->appendCalendarCategories($level, $childNodes);

            if ($childNodes !== null) {
                $this->treeData->setChildNodes($childNodes);
            }
        }
    }

    /**
     * @param $level
     * @param $parentChildNodes
     */
    protected function appendGlobalCategories($level, $parentChildNodes)
    {
        $node = GeneralUtility::makeInstance(TreeNode::class);
        $node->setId(self::GLOBAL_PREFIX);

        $childNodes = GeneralUtility::makeInstance(TreeNodeCollection::class);

        $where = 'l18n_parent = 0 and deleted = 0 and parent_category = 0 and calendar_id = 0';
        $this->appendCategories($level, $childNodes, $where);
        if ($childNodes !== null) {
            $node->setChildNodes($childNodes);
        }

        $parentChildNodes->append($node);
    }

    /**
     * @param $level
     * @param $childNodes
     */
    protected function appendCalendarCategories($level, $childNodes)
    {
        $calendarId = $this->currentValue['calendar_id'] ?? 0;
        if ($calendarId > 0) {
            $builder = $this->connectionPool->getQueryBuilderForTable('tx_cal_calendar');
            $calres = $builder->select('uid', 'title')->from('tx_cal_calendar')
                ->where($this->getCalendarWhere($calendarId))->execute();
            if ($calres) {
                while ($calrow = $calres->fetch(PDO::FETCH_ASSOC)) {
                    $node = GeneralUtility::makeInstance(TreeNode::class);
                    $node->setId(self::CALENDAR_PREFIX . $calrow['uid']);

                    if ($level < $this->levelMaximum) {
                        $where = 'l18n_parent = 0 and tx_cal_category.deleted = 0 and tx_cal_category.calendar_id = ' . $calrow['uid'];
                        $calendarChildNodes = GeneralUtility::makeInstance(TreeNodeCollection::class);

                        $this->appendCategories($level + 1, $calendarChildNodes, $where);
                        if ($calendarChildNodes !== null) {
                            $node->setChildNodes($calendarChildNodes);
                        }
                    }
                    $childNodes->append($node);
                }
            }
        }
    }

    /**
     * @param $level
     * @param $childNodes
     * @param $where
     */
    protected function appendCategories($level, $childNodes, $where)
    {
        $builder = $this->connectionPool->getQueryBuilderForTable('tx_cal_category');
        $categoryResult = $builder->select('uid', 'title')->from('tx_cal_category')
            ->where($where)->execute();
        $usedCategories = [];
        if ($categoryResult) {
            while (($categoryRow = $categoryResult->fetch(PDO::FETCH_ASSOC))) {
                $categoryNode = GeneralUtility::makeInstance(TreeNode::class);
                $categoryNode->setId($categoryRow['uid']);
                if ($level < $this->levelMaximum) {
                    $children = $this->getChildrenOf($categoryNode, $level + 1);
                    if ($children !== null) {
                        foreach ($children as $child) {
                            $usedCategories[$child->getId()] = true;
                        }
                        $categoryNode->setChildNodes($children);
                    }
                }
                if (!$usedCategories[$categoryRow['uid']]) {
                    $usedCategories[$categoryRow['uid']] = true;
                    $childNodes->append($categoryNode);
                }
            }
        }
    }

    /**
     * @param $calendarId
     * @return string
     */
    protected function getCalendarWhere($calendarId): string
    {
        $calWhere = 'l18n_parent = 0  AND tx_cal_calendar.uid = ' . $calendarId;

        if ((TYPO3_MODE == 'BE') || ($GLOBALS ['TSFE']->beUserLogin && $GLOBALS ['BE_USER']->extAdmEnabled)) {
            $calWhere .= BackendUtility::BEenableFields('tx_cal_calendar') . ' AND tx_cal_calendar.deleted = 0';
        }
        return $calWhere;
    }

    /**
     * Builds a complete node including children
     *
     * @param TreeNode|TreeNode $basicNode
     * @param DatabaseTreeNode|null $parent
     * @param int $level
     * @param bool $restriction
     * @return DatabaseTreeNode $node
     */
    protected function buildRepresentationForNode(TreeNode $basicNode, DatabaseTreeNode $parent = null, $level = 0, $restriction = false)
    {
        /** @var $node DatabaseTreeNode */
        $node = GeneralUtility::makeInstance(DatabaseTreeNode::class);
        $row = [];
        $node->setSelected(false);
        $node->setExpanded(true);
        $node->setSelectable(false);

        /** @var IconFactory $iconFactory */
        $iconFactory = GeneralUtility::makeInstance(IconFactory::class);

        if (strrpos($basicNode->getId(), self::CALENDAR_PREFIX, -strlen($basicNode->getId())) !== false) {
            $id = intval(substr($basicNode->getId(), strlen(self::CALENDAR_PREFIX)));
            $row = BackendUtility::getRecordWSOL('tx_cal_calendar', $id, '*', '', false);
            $icon = $iconFactory->getIconForRecord('tx_cal_calendar', $row, \TYPO3\CMS\Core\Imaging\Icon::SIZE_SMALL);
            $node->setIcon($icon);
            $node->setLabel($row['title']);
            $node->setSortValue($id);
        } elseif ($basicNode->getId() === self::GLOBAL_PREFIX) {
            $node->setLabel($GLOBALS['LANG']->sL('LLL:EXT:cal/Resources/Private/Language/locallang_db.xlf:tx_cal_category.global'));
            $node->setSortValue(0);
        } elseif ($basicNode->getId() == 0) {
            $node->setLabel($GLOBALS['LANG']->sL($GLOBALS['TCA'][$this->tableName]['ctrl']['title']));
        } else {
            $row = BackendUtility::getRecordWSOL($this->tableName, $basicNode->getId(), '*', '', false);

            if ($this->getLabelField() !== '') {
                $node->setLabel($row[$this->getLabelField()]);
            } else {
                $node->setLabel($basicNode->getId());
            }
            $node->setSelected(GeneralUtility::inList($this->getSelectedList(), $basicNode->getId()));
            $node->setExpanded($this->isExpanded($basicNode));
            $node->setLabel($node->getLabel());
            $icon = $iconFactory->getIconForRecord($this->tableName, $row, \TYPO3\CMS\Core\Imaging\Icon::SIZE_SMALL);
            $node->setIcon($icon);
            $node->setSelectable(!GeneralUtility::inList($this->getNonSelectableLevelList(), $level) && !in_array($basicNode->getId(), $this->getItemUnselectableList()));
            $node->setSortValue($this->nodeSortValues[$basicNode->getId()]);
        }

        $node->setId($basicNode->getId());

        // Break to force single category activation
        if ($parent != null && $level != 0 && $this->isSingleCategoryAclActivated() && !$this->isCategoryAllowed($node)) {
            return null;
        }

        $node->setParentNode($parent);
        if ($basicNode->hasChildNodes()) {

            /** @var SortedTreeNodeCollection $childNodes */
            $childNodes = GeneralUtility::makeInstance(SortedTreeNodeCollection::class);
            $foundSomeChild = false;
            foreach ($basicNode->getChildNodes() as $child) {
                // Change in custom TreeDataProvider by adding the if clause
                if ($restriction || $this->isCategoryAllowed($child)) {
                    $returnedChild = $this->buildRepresentationForNode($child, $node, $level + 1, $restriction);

                    if ($returnedChild !== null) {
                        $foundSomeChild = true;
                        $childNodes->append($returnedChild);
                    } else {
                        $node->setParentNode(null);
                        $node->setHasChildren(false);
                    }
                }
                // Change in custom TreeDataProvider end
            }

            if ($foundSomeChild) {
                $node->setHasChildren(true);
                $node->setChildNodes($childNodes);
            }
        }
        return $node;
    }

    /**
     * Check if given category is allowed by the access rights
     *
     * @param TreeNode $child
     * @return bool
     */
    protected function isCategoryAllowed($child): bool
    {
        if ($this->calConfiguration['categoryService'] == 'sys_category') {
            $mounts = $this->backendUserAuthentication->getCategoryMountPoints();
            if (empty($mounts)) {
                return true;
            }

            return in_array($child->getId(), $mounts);
        }
        if ($child->getId() === self::GLOBAL_PREFIX) {
            return true;
        }

        if ($GLOBALS ['BE_USER']->user ['admin']) {
            return true;
        }

        $be_userCategories = [];
        $be_userCalendars = [];

        if ($GLOBALS ['BE_USER']->user ['tx_cal_enable_accesscontroll']) {
            $be_userCategories = GeneralUtility::trimExplode(',', $GLOBALS ['BE_USER']->user ['tx_cal_category'], 1);
            $be_userCalendars = GeneralUtility::trimExplode(',', $GLOBALS ['BE_USER']->user ['tx_cal_calendar'], 1);
        } else {
            $allGroupsHaveEnableFalse = true;
            if (is_array($GLOBALS ['BE_USER']->userGroups)) {
                foreach ($GLOBALS ['BE_USER']->userGroups as $gid => $group) {
                    if ($group ['tx_cal_enable_accesscontroll']) {
                        $allGroupsHaveEnableFalse = false;
                        break;
                    }
                }
            }
            if ($allGroupsHaveEnableFalse) {
                return true;
            }
        }
        if (is_array($GLOBALS ['BE_USER']->userGroups)) {
            foreach ($GLOBALS ['BE_USER']->userGroups as $gid => $group) {
                if ($group ['tx_cal_enable_accesscontroll']) {
                    if ($group ['tx_cal_category']) {
                        $groupCategories = GeneralUtility::trimExplode(',', $group ['tx_cal_category'], 1);
                        $be_userCategories = array_merge($be_userCategories, $groupCategories);
                    }
                    if ($group ['tx_cal_calendar']) {
                        $groupCalendars = GeneralUtility::trimExplode(',', $group ['tx_cal_calendar'], 1);
                        $be_userCalendars = array_merge($be_userCalendars, $groupCalendars);
                    }
                }
            }
        }

        if (strrpos($child->getId(), self::CALENDAR_PREFIX, -strlen($child->getId())) !== false) {
            $allow = in_array(substr($child->getId(), strlen(self::CALENDAR_PREFIX)), $be_userCalendars);
        } else {
            $allow = in_array($child->getId(), $be_userCategories);
        }

        return $allow;
    }

    /**
     * @return bool
     */
    protected function isSingleCategoryAclActivated(): bool
    {
        return false;
    }
}
