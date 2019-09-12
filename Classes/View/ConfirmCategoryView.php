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
 * A service which renders a form to confirm the category edit/create.
 */
class ConfirmCategoryView extends FeEditingBaseView
{
    /**
     * Draws a create category form.
     *
     * @param string        Comma separated list of pids.
     * @param object        A location or organizer object to be updated
     * @return string HTML output.
     */
    public function drawConfirmCategory(): string
    {
        $this->objectString = 'category';
        $this->isConfirm = true;
        unset($this->controller->piVars['formCheck']);
        $page = Functions::getContent($this->conf['view.']['confirm_category.']['template']);
        if ($page === '') {
            return '<h3>category: no create category template file found:</h3>' . $this->conf['view.']['confirm_category.']['template'];
        }

        $a = [];
        $this->object = new CategoryModel($a, '');
        $this->object->updateWithPIVars($this->controller->piVars);

        $lastViewParams = $this->controller->shortenLastViewAndGetTargetViewParameters();

        if ($lastViewParams['view'] === 'edit_category') {
            $this->isEditMode = true;
        }

        $rems = [];
        $sims = [];
        $wrapped = [];
        $sims['###L_CONFIRM_CATEGORY###'] = $this->controller->pi_getLL('l_confirm_category');
        $sims['###UID###'] = $this->conf['uid'];
        $sims['###TYPE###'] = $this->conf['type'];
        $sims['###VIEW###'] = 'save_category';
        $sims['###L_SUBMIT###'] = $this->controller->pi_getLL('l_submit');
        $sims['###L_CANCEL###'] = $this->controller->pi_getLL('l_cancel');
        $sims['###ACTION_URL###'] = htmlspecialchars($this->controller->pi_linkTP_keepPIvars_url([
            'view' => 'save_category'
        ]));

        $this->getTemplateSubpartMarker($page, $sims, $rems, $wrapped);
        $page = Functions::substituteMarkerArrayNotCached($page, [], $rems, $wrapped);
        $page = Functions::substituteMarkerArrayNotCached($page, $sims, [], []);
        $sims = [];
        $rems = [];
        $wrapped = [];
        $this->getTemplateSingleMarker($page, $sims, $rems, $wrapped);
        $page = Functions::substituteMarkerArrayNotCached($page, [], $rems, $wrapped);

        $page = Functions::substituteMarkerArrayNotCached($page, $sims, [], []);
        return Functions::substituteMarkerArrayNotCached($page, $sims, [], []);
    }

    /**
     * @param $template
     * @param $sims
     * @param $rems
     */
    public function getCalendarIdMarker(& $template, & $sims, & $rems)
    {
        $sims['###CALENDAR_ID###'] = '';
        $sims['###CALENDAR_ID_VALUE###'] = '';
        if ($this->isAllowed('calendar_id') && $calendar = $this->object->getCalendarObject()) {
            $sims['###CALENDAR_ID###'] = $this->applyStdWrap($calendar->getTitle(), 'calendar_id_stdWrap');
            $sims['###CALENDAR_ID_VALUE###'] = htmlspecialchars($calendar->getUid());
        }
    }

    /**
     * @param $template
     * @param $sims
     * @param $rems
     */
    public function getHeaderstyleMarker(& $template, & $sims, & $rems)
    {
        $sims['###HEADERSTYLE###'] = '';
        $sims['###HEADERSTYLE_VALUE###'] = '';
        if ($this->isAllowed('headerstyle')) {
            $headerStyleValue = $this->object->getHeaderStyle();
            $sims['###HEADERSTYLE###'] = $this->applyStdWrap($headerStyleValue, 'headerstyle_stdWrap');
            $sims['###HEADERSTYLE_VALUE###'] = $headerStyleValue;
        }
    }

    /**
     * @param $template
     * @param $sims
     * @param $rems
     */
    public function getBodystyleMarker(& $template, & $sims, & $rems)
    {
        $sims['###BODYSTYLE###'] = '';
        $sims['###BODYSTYLE_VALUE###'] = '';
        if ($this->isAllowed('bodystyle')) {
            $bodyStyleValue = $this->object->getBodyStyle();
            $sims['###BODYSTYLE###'] = $this->applyStdWrap($bodyStyleValue, 'bodystyle_stdWrap');
            $sims['###BODYSTYLE_VALUE###'] = $bodyStyleValue;
        }
    }

    /**
     * @param $template
     * @param $sims
     * @param $rems
     */
    public function getParentCategoryMarker(& $template, &$sims, & $rems)
    {
        $sims['###PARENT_CATEGORY###'] = '';
        $sims['###PARENT_CATEGORY_VALUE###'] = '';
        if ($this->isAllowed('parent_category')) {
            $parentUid = $this->object->getParentUid();
            if ($parentUid) {
                /* Get parent category title */
                $category = $this->modelObj->findCategory($parentUid, 'sys_category', $this->conf['pidList']);
                $sims['###PARENT_CATEGORY###'] = $this->applyStdWrap($category->getTitle(), 'parent_category_stdWrap');
                $sims['###PARENT_CATEGORY_VALUE###'] = $parentUid;
            }
        }
    }

    /**
     * @param $template
     * @param $sims
     * @param $rems
     */
    public function getSharedUserAllowedMarker(& $template, & $sims, & $rems)
    {
        $sims['###SHARED_USER_ALLOWED###'] = '';
        $sims['###SHARED_USER_ALLOWED_VALUE###'] = '';
        if ($this->isAllowed('shared_user_allowed')) {
            if ($this->object->isSharedUserAllowed()) {
                $value = 1;
                $label = $this->controller->pi_getLL('l_true');
            } else {
                $value = 0;
                $label = $this->controller->pi_getLL('l_false');
            }

            $sims['###SHARED_USER_ALLOWED###'] = $this->applyStdWrap($label, 'shared_user_allowed_stdWrap');
            $sims['###SHARED_USER_ALLOWED_VALUE###'] = $value;
        }
    }
}
