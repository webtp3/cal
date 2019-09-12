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
use TYPO3\CMS\Cal\Model\CategoryModel;
use TYPO3\CMS\Cal\Utility\Functions;

/**
 * A service which renders a form to create / edit a phpicategory event.
 */
class DeleteCategoryView extends FeEditingBaseView
{
    /**
     * @var CategoryModel
     */
    public $category;

    /**
     * Draws a delete form for a calendar.
     *
     * @param bool True if a location should be deleted
     * @param CategoryModel        The object to be deleted
     * @param object        The cObject of the mother-class.
     * @param object        The rights object.
     * @return string HTML output.
     */
    public function drawDeleteCategory(&$category): string
    {
        $page = Functions::getContent($this->conf['view.']['delete_category.']['template']);
        if ($page === '') {
            return '<h3>category: no delete category template file found:</h3>' . $this->conf['view.']['delete_category.']['template'];
        }

        $this->category = $category;

        $rems = [];
        $sims = [];

        $sims['###UID###'] = $this->conf['uid'];
        $sims['###TYPE###'] = $this->conf['type'];
        $sims['###VIEW###'] = 'remove_category';
        $sims['###LASTVIEW###'] = $this->controller->extendLastView();
        $sims['###L_DELETE_CATEGORY###'] = $this->controller->pi_getLL('l_delete_category');
        $sims['###L_DELETE###'] = $this->controller->pi_getLL('l_delete');
        $sims['###L_CANCEL###'] = $this->controller->pi_getLL('l_cancel');
        $sims['###ACTION_URL###'] = htmlspecialchars($this->controller->pi_linkTP_keepPIvars_url([
            'view' => 'remove_category'
        ]));
        $this->getTemplateSubpartMarker($page, $sims, $rems);
        $page = Functions::substituteMarkerArrayNotCached($page, [], $rems, []);
        $page = Functions::substituteMarkerArrayNotCached($page, $sims, [], []);
        $sims = [];
        $rems = [];
        $this->getTemplateSingleMarker($page, $sims, $rems);
        $page = Functions::substituteMarkerArrayNotCached($page, [], $rems, []);

        $page = Functions::substituteMarkerArrayNotCached($page, $sims, [], []);
        return Functions::substituteMarkerArrayNotCached($page, $sims, [], []);
    }

    /**
     * @param $template
     * @param $sims
     * @param $rems
     * @param $view
     */
    public function getHiddenMarker(& $template, & $sims, & $rems, $view)
    {
        $sims['###HIDDEN###'] = $this->cObj->stdWrap(
            $this->category->isHidden() ? $this->controller->pi_getLL('l_true') : $this->controller->pi_getLL('l_false'),
            $this->conf['view.'][$this->conf['view'] . '.']['hidden_stdWrap.']
        );
    }

    /**
     * @param $template
     * @param $sims
     * @param $rems
     */
    public function getTitleMarker(& $template, & $sims, & $rems)
    {
        $sims['###TITLE###'] = $this->cObj->stdWrap(
            $this->category->getTitle(),
            $this->conf['view.'][$this->conf['view'] . '.']['title_stdWrap.']
        );
    }

    /**
     * @param $page
     * @param $sims
     * @param $rems
     * @param $view
     */
    public function getCalendarMarker(&$page, &$sims, &$rems, $view)
    {
        $calendarUid = $this->category->getCalendarUid();
        if ($calendarUid) {
            $calendar = $this->modelObj->findCalendar($calendarUid, 'tx_cal_calendar', $this->conf['pidList']);
            $calendarTitle = $calendar->getTitle();
            $sims['###CALENDAR###'] = $this->cObj->stdWrap(
                $calendarTitle,
                $this->conf['view.'][$this->conf['view'] . '.']['calendar_stdWrap.']
            );
        } else {
            $sims['###CALENDAR###'] = '';
        }
    }

    /**
     * @param $template
     * @param $sims
     * @param $rems
     */
    public function getHeaderStyleMarker(& $template, & $sims, & $rems)
    {
        $sims['###HEADERSTYLE###'] = $this->cObj->stdWrap(
            $this->category->getHeaderStyle(),
            $this->conf['view.'][$this->conf['view'] . '.']['headerStyle_stdWrap.']
        );
    }

    /**
     * @param $template
     * @param $sims
     * @param $rems
     */
    public function getBodyStyleMarker(& $template, & $sims, & $rems)
    {
        $sims['###BODYSTYLE###'] = $this->cObj->stdWrap(
            $this->category->getBodyStyle(),
            $this->conf['view.'][$this->conf['view'] . '.']['bodyStyle_stdWrap.']
        );
    }

    /**
     * @param $template
     * @param $sims
     * @param $rems
     */
    public function getParentCategoryMarker(& $template, &$sims, & $rems)
    {
        $parentUid = $this->category->getParentUid();

        if ($parentUid) {
            /* Get parent category title */
            $category = $this->modelObj->findCategory($parentUid, 'sys_category', $this->conf['pidList']);
            $parentCategory = $category->getTitle();
            $sims['###PARENT_CATEGORY###'] = $this->cObj->stdWrap(
                $parentCategory,
                $this->conf['view.'][$this->conf['view'] . '.']['parentCategory_stdWrap.']
            );
        } else {
            $sims['###PARENT_CATEGORY###'] = '';
        }
    }

    /**
     * @param $template
     * @param $sims
     * @param $rems
     */
    public function getSharedUserAllowedMarker(& $template, & $sims, & $rems)
    {
        $sims['###SHARED_USER_ALLOWED###'] = $this->cObj->stdWrap(
            $this->category->isSharedUserAllowed() ? $this->controller->pi_getLL('l_true') : $this->controller->pi_getLL('l_false'),
            $this->conf['view.'][$this->conf['view'] . '.']['sharedUserAllowed_stdWrap.']
        );
    }
}
