<?php

namespace TYPO3\CMS\Cal\Model;

use TYPO3\CMS\Cal\Utility\Registry;

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
class CategoryModel extends BaseModel
{
    public $row = [];
    public $parentUid = 0;
    public $calendarUid = 0;
    public $title = '';
    public $headerStyle = '';
    public $bodyStyle = '';
    public $sharedUserAllowed = false;
    public $categoryService;
    public $singlePid = 0;
    public $notificationEmails = [];
    public $icon = '';

    /**
     * Constructor.
     * @param $row
     * @param $serviceKey
     */
    public function __construct($row, $serviceKey)
    {
        $confArr = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['cal']);
        $this->setType($confArr['categoryService']);
        $this->setObjectType('category');
        parent::__construct($serviceKey);
        if (is_array($row) && !empty($row)) {
            $this->init($row);
        }
    }

    /**
     * @param $row
     */
    public function init(&$row)
    {
        $this->row = $row;
        $this->setUid($row['uid']);
        $this->setParentUid($row['parent_category']);
        if ($row['title']) {
            $this->setTitle($row['title']);
        } else {
            $this->setTitle('[No Title]');
        }
        $this->setHidden($row['hidden']);
        $this->setHeaderStyle($row['headerstyle']);
        $this->setBodyStyle($row['bodystyle']);
        $this->setSharedUserAllowed($row['shared_user_allowed']);
        $this->setCalendarUid($row['calendar_id']);
        $this->setSinglePid($row['single_pid']);
        $this->setNotificationEmails(\TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode(
            ',',
            $row['notification_emails'],
            1
        ));
        $this->setIcon($row['icon']);
    }

    /**
     * @param $uid
     */
    public function setParentUid($uid)
    {
        $this->parentUid = $uid;
    }

    /**
     * @return int
     */
    public function getParentUid()
    {
        return $this->parentUid;
    }

    /**
     * @param $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param $headerStyle
     */
    public function setHeaderStyle($headerStyle)
    {
        $this->headerStyle = $headerStyle;
    }

    /**
     * @return string
     */
    public function getHeaderStyle()
    {
        return $this->headerStyle;
    }

    /**
     * @param $bodyStyle
     */
    public function setBodyStyle($bodyStyle)
    {
        $this->bodyStyle = $bodyStyle;
    }

    /**
     * @return string
     */
    public function getBodyStyle()
    {
        return $this->bodyStyle;
    }

    /**
     * @param $boolean
     */
    public function setSharedUserAllowed($boolean)
    {
        $this->sharedUserAllowed = $boolean;
    }

    /**
     * @return bool
     */
    public function isSharedUserAllowed()
    {
        return $this->sharedUserAllowed;
    }

    /**
     * @param $uid
     */
    public function setCalendarUid($uid)
    {
        $this->calendarUid = $uid;
    }

    /**
     * @return int
     */
    public function getCalendarUid()
    {
        return $this->calendarUid;
    }

    /**
     * @param $template
     * @param $sims
     * @param $rems
     * @param $view
     */
    public function getHeaderstyleMarker(& $template, & $sims, & $rems, $view)
    {
        $sims['###HEADERSTYLE###'] = $this->getHeaderStyle();
    }

    /**
     * @param $template
     * @param $sims
     * @param $rems
     * @param $view
     */
    public function getBodystyleMarker(& $template, & $sims, & $rems, $view)
    {
        $sims['###BODYSTYLE###'] = $this->getBodyStyle();
    }

    /**
     * @return int
     */
    public function getSinglePid()
    {
        return $this->singlePid;
    }

    /**
     * @param $singlePid
     */
    public function setSinglePid($singlePid)
    {
        $this->singlePid = $singlePid;
    }

    /**
     * @return array
     */
    public function getNotificationEmails()
    {
        return $this->notificationEmails;
    }

    /**
     * @param $emailArray
     */
    public function setNotificationEmails($emailArray)
    {
        if (is_array($emailArray)) {
            $this->notificationEmails = $emailArray;
        }
    }

    /**
     * @return string
     */
    public function getIcon()
    {
        return $this->icon;
    }

    /**
     * @param $icon
     */
    public function setIcon($icon)
    {
        $this->icon = $icon;
    }

    /**
     * @param string $feUserUid
     * @param array $feGroupsArray
     * @return bool
     */
    public function isUserAllowedToEdit($feUserUid = '', $feGroupsArray = [])
    {
        $rightsObj = &Registry::Registry('basic', 'rightsController');
        if (!$rightsObj->isViewEnabled('edit_category')) {
            return false;
        }
        if ($rightsObj->isCalAdmin()) {
            return true;
        }

        if ($feUserUid == '') {
            $feUserUid = $rightsObj->getUserId();
        }
        if (empty($feGroupsArray)) {
            $feGroupsArray = $rightsObj->getUserGroups();
        }

        $isCategoryOwner = false;
        $isAllowedToEditPublicCategory = false;
        if ($this->getCalendarUid()) {
            $modelObj = &Registry::Registry('basic', 'modelcontroller');
            $calendar = $modelObj->findCalendar($this->getCalendarUid(), 'tx_cal_calendar', $this->conf['pidList']);
            $isCategoryOwner = $calendar->isCalendarOwner($feUserUid, $feGroupsArray);
            if ($calendar->isPublic()) {
                $isAllowedToEditPublicCategory = $rightsObj->isAllowedToEditPublicCategory();
            }
        }

        $isAllowedToEditCategory = $rightsObj->isAllowedToEditCategory();
        $isAllowedToEditOwnCategoryOnly = $rightsObj->isAllowedToEditOnlyOwnCategory();
        $isAllowedToEditGeneralCategory = $rightsObj->isAllowedToEditGeneralCategory();

        if ($isAllowedToEditOwnCategoryOnly) {
            return $isCategoryOwner;
        }
        return $isAllowedToEditCategory && ($isCategoryOwner || ($this->getCalendarUid() == 0 && $isAllowedToEditGeneralCategory) || $isAllowedToEditPublicCategory);
    }

    /**
     * @param string $feUserUid
     * @param array $feGroupsArray
     * @return bool
     */
    public function isUserAllowedToDelete($feUserUid = '', $feGroupsArray = [])
    {
        $rightsObj = &Registry::Registry('basic', 'rightsController');
        if (!$rightsObj->isViewEnabled('delete_category')) {
            return false;
        }
        if ($rightsObj->isCalAdmin()) {
            return true;
        }

        if ($feUserUid == '') {
            $feUserUid = $rightsObj->getUserId();
        }
        if (empty($feGroupsArray)) {
            $feGroupsArray = $rightsObj->getUserGroups();
        }
        $isCategoryOwner = false;
        $isAllowedToDeletePublicCategory = false;
        if ($this->getCalendarUid()) {
            $modelObj = &Registry::Registry('basic', 'modelcontroller');
            $calendar = $modelObj->findCalendar($this->getCalendarUid(), 'tx_cal_calendar', $this->conf['pidList']);
            $isCategoryOwner = $calendar->isCalendarOwner($feUserUid, $feGroupsArray);
            if ($calendar->isPublic()) {
                $isAllowedToDeletePublicCategory = $rightsObj->isAllowedToDeletePublicCategory();
            }
        }

        $isAllowedToDeleteCategory = $rightsObj->isAllowedToDeleteCategory();
        $isAllowedToDeleteOwnCategoryOnly = $rightsObj->isAllowedToDeleteOnlyOwnCategory();
        $isAllowedToDeleteGeneralCategory = $rightsObj->isAllowedToDeleteGeneralCategory();

        if ($isAllowedToDeleteOwnCategoryOnly) {
            return $isCategoryOwner;
        }
        return $isAllowedToDeleteCategory && ($isCategoryOwner || ($this->getCalendarUid() == 0 && $isAllowedToDeleteGeneralCategory) || $isAllowedToDeletePublicCategory);
    }

    /**
     * @return mixed
     */
    public function getCalendarObject()
    {
        if (!$this->calendarObject) {
            $modelObj = &Registry::Registry('basic', 'modelcontroller');
            $this->calendarObject = $modelObj->findCalendar($this->getCalendarUid());
        }

        return $this->calendarObject;
    }

    /**
     * @param $template
     * @param $sims
     * @param $rems
     * @param $view
     * @return string
     */
    public function getEditLink(& $template, & $sims, & $rems, $view)
    {
        $editlink = '';
        if ($this->isUserAllowedToEdit()) {
            $this->initLocalCObject($this->getValuesAsArray());

            $this->local_cObj->setCurrentVal($this->controller->pi_getLL('l_edit_category'));
            $this->controller->getParametersForTyposcriptLink(
                $this->local_cObj->data,
                [
                    'view' => 'edit_category',
                    'type' => $this->getType(),
                    'uid' => $this->getUid()
                ],
                $this->conf['cache'],
                $this->conf['clear_anyway'],
                $this->conf['view.']['calendar.']['editCategoryViewPid']
            );
            $editlink = $this->local_cObj->cObjGetSingle(
                $this->conf['view.'][$view . '.']['category.']['editLink'],
                $this->conf['view.'][$view . '.']['category.']['editLink.']
            );
        }
        if ($this->isUserAllowedToDelete()) {
            $this->initLocalCObject($this->getValuesAsArray());

            $this->local_cObj->setCurrentVal($this->controller->pi_getLL('l_delete_category'));
            $this->controller->getParametersForTyposcriptLink(
                $this->local_cObj->data,
                [
                    'view' => 'delete_category',
                    'type' => $this->getType(),
                    'uid' => $this->getUid()
                ],
                $this->conf['cache'],
                $this->conf['clear_anyway'],
                $this->conf['view.']['category.']['deleteCategoryViewPid']
            );
            $editlink .= $this->local_cObj->cObjGetSingle(
                $this->conf['view.'][$view . '.']['category.']['deleteLink'],
                $this->conf['view.'][$view . '.']['category.']['deleteLink.']
            );
        }
        return $editlink;
    }

    /**
     * @param $piVars
     */
    public function updateWithPIVars(&$piVars)
    {
        // cObj = &\TYPO3\CMS\Cal\Utility\Registry::Registry('basic','cobj');
        $modelObj = &Registry::Registry('basic', 'modelController');
        // controller = &\TYPO3\CMS\Cal\Utility\Registry::Registry('basic','controller');

        foreach ($piVars as $key => $value) {
            switch ($key) {
                case 'uid':
                    $this->setUid(intval($piVars['uid']));
                    unset($piVars['uid']);
                    break;
                case 'hidden':
                    $this->setHidden(intval($piVars['hidden']));
                    unset($piVars['hidden']);
                    break;
                case 'title':
                    $this->setTitle(strip_tags($piVars['title']));
                    unset($piVars['title']);
                    break;
                case 'calendar_id':
                    $this->setCalendarUid(strip_tags($piVars['calendar_id'], []));
                    unset($piVars['calendar_id']);
                    break;
                case 'headerstyle':
                    $this->setHeaderStyle(strip_tags($piVars['headerstyle']));
                    unset($piVars['headerstyle']);
                    break;
                case 'bodystyle':
                    $this->setBodystyle(strip_tags($piVars['bodystyle']));
                    unset($piVars['bodystyle']);
                    break;
                case 'parent_category':
                    $this->setParentUid(intval($piVars['parent_category']));
                    unset($piVars['parentCategory']);
                    break;
                case 'shared_user_allowed':
                    $this->setSharedUserAllowed(intval($piVars['shared_user_allowed']));
                    unset($piVars['shared_user_allowed']);
                    break;
            }
        }
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return 'Category ' . (is_object($this) ? 'object' : 'something') . ': ' . implode(',', $this->row);
    }
}
