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
use TYPO3\CMS\Cal\Model\CategoryModel;
use TYPO3\CMS\Cal\Utility\Functions;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * A service which renders a form to create / edit a category.
 */
class CreateCategoryView extends FeEditingBaseView
{
    /**
     * Draws a create category form.
     *
     * @param string        Comma separated list of pids.
     * @param CategoryModel $category A location or organizer object to be updated
     * @return string HTML output.
     */
    public function drawCreateCategory($pidList, $category): string
    {
        $this->objectString = 'category';

        if (!$this->rightsObj->isAllowedToCreateGeneralCategory()) {
            $this->conf['rights.']['create.'][$this->objectString . '.']['fields.']['calendar_id.']['required'] = 1;
        }
        if (!$this->rightsObj->isAllowedToEditGeneralCategory()) {
            $this->conf['rights.']['edit.'][$this->objectString . '.']['fields.']['calendar_id.']['required'] = 1;
        }

        if (is_object($object)) {
            $this->conf['view'] = 'edit_' . $this->objectString;
        } else {
            $this->conf['view'] = 'create_' . $this->objectString;
            unset($this->controller->piVars['uid']);
        }

        $allRequiredFieldsAreFilled = $this->checkRequiredFields($requiredFieldsSims);

        if ($allRequiredFieldsAreFilled) {
            $this->conf['lastview'] = $this->controller->extendLastView();

            $this->conf['view'] = 'confirm_' . $this->objectString;
            return $this->controller->confirmCategory();
        }

        // Needed for translation options:
        $this->serviceName = 'cal_' . $this->objectString . '_model';
        $this->table = 'tx_cal_' . $this->objectString;

        $page = Functions::getContent($this->conf['view.']['create_category.']['template']);
        if ($page === '') {
            return '<h3>category: no create category template file found:</h3>' . $this->conf['view.']['create_category.']['template'];
        }
        if (is_object($object) && !$object->isUserAllowedToEdit()) {
            return $this->controller->pi_getLL('l_not_allowed_edit') . $this->objectString;
        }
        if (!is_object($object) && !$this->rightsObj->isAllowedTo('create', $this->objectString, '')) {
            return $this->controller->pi_getLL('l_not_allowed_create') . $this->objectString;
        }

        $sims = [];
        $rems = [];
        $wrapped = [];

        $sims['###TYPE###'] = 'tx_cal_' . $this->objectString;

        // If an event has been passed on the form is a edit form
        if (is_object($category) && $category->isUserAllowedToEdit()) {
            $this->isEditMode = true;
            $this->object = $category;
            $sims['###UID###'] = $this->object->getUid();
            $sims['###TYPE###'] = $this->object->getType();
            $sims['###L_EDIT_CATEGORY###'] = $this->controller->pi_getLL('l_edit_category');
        } else {
            $a = [];
            $this->object = new CategoryModel($a, '');
            $allValues = array_merge($this->getDefaultValues(), $this->controller->piVars);
            $this->object->updateWithPIVars($allValues);
        }

        $sims['###THIS_VIEW###'] = $this->conf['view'];

        $this->getTemplateSubpartMarker($page, $sims, $rems, $wrapped);

        $page = Functions::substituteMarkerArrayNotCached($page, [], $rems, $wrapped);
        $page = Functions::substituteMarkerArrayNotCached($page, $sims, [], []);

        $sims = [];
        $rems = [];

        $this->getTemplateSingleMarker($page, $sims, $rems, $this->conf['view']);
        $linkParams = [];
        $linkParams['formCheck'] = '1';

        $sims['###ACTION_URL###'] = htmlspecialchars($this->controller->pi_linkTP_keepPIvars_url($linkParams));
        $change_calendar_action_url = $this->controller->pi_linkTP_keepPIvars_url();
        $sims['###CHANGE_CALENDAR_ACTION_URL###'] = htmlspecialchars($change_calendar_action_url);
        $sims['###CHANGE_CALENDAR_ACTION_URL_JS###'] = $this->escapeForJS($change_calendar_action_url);

        $this->getTemplateSubpartMarker($page, $sims, $rems, $this->conf['view']);
        $page = Functions::substituteMarkerArrayNotCached($page, [], $rems, []);
        $page = Functions::substituteMarkerArrayNotCached($page, $sims, [], []);
        return Functions::substituteMarkerArrayNotCached($page, $requiredFieldsSims, [], []);
    }

    /**
     * @param $template
     * @param $sims
     * @param $rems
     * @param $view
     */
    public function getHeaderstyleMarker(& $template, & $sims, & $rems, $view)
    {
        $sims['###HEADERSTYLE###'] = '';
        if ($this->isAllowed('headerstyle')) {
            $selectedStyle = $this->object->getHeaderStyle();
            $allowedStyles = GeneralUtility::trimExplode(
                ',',
                $this->conf['rights.']['edit.']['category.']['fields.']['headerstyle.']['available'],
                1
            );
            $headerStyle = '';

            /* If there are allowed styles, draw the selector */
            if (count($allowedStyles) > 0) {
                foreach ($allowedStyles as $style) {
                    if ($style === $selectedStyle) {
                        $headerStyle .= '<option value="' . $style . '" selected="selected" class="' . $style . '">' . $style . '</option>';
                    } else {
                        $headerStyle .= '<option value="' . $style . '" class="' . $style . '">' . $style . '</option>';
                    }
                }

                $sims['###HEADERSTYLE###'] = $this->applyStdWrap($headerStyle, 'headerstyle_stdWrap');
            }
        }
    }

    /**
     * @param $template
     * @param $sims
     * @param $rems
     * @param $view
     */
    public function getBodystyleMarker(& $template, & $sims, & $rems, $view)
    {
        $sims['###BODYSTYLE###'] = '';
        if ($this->isAllowed('bodystyle')) {
            $selectedStyle = $this->object->getBodyStyle();
            $allowedStyles = GeneralUtility::trimExplode(
                ',',
                $this->conf['rights.']['edit.']['category.']['fields.']['bodystyle.']['available'],
                1
            );
            $bodyStyle = '';

            /* If there are allowed styles, draw the selector */
            if (count($allowedStyles) > 0) {
                foreach ($allowedStyles as $style) {
                    if ($style === $selectedStyle) {
                        $bodyStyle .= '<option value="' . $style . '" selected="selected" class="' . $style . '">' . $style . '</option>';
                    } else {
                        $bodyStyle .= '<option value="' . $style . '" class="' . $style . '">' . $style . '</option>';
                    }
                }

                $sims['###BODYSTYLE###'] = $this->applyStdWrap($bodyStyle, 'bodystyle_stdWrap');
            }
        }
    }

    /**
     * @param $template
     * @param $sims
     * @param $rems
     * @param $view
     */
    public function getParentCategoryMarker(& $template, & $sims, & $rems, $view)
    {
        $sims['###PARENT_CATEGORY###'] = '';
        $isAllowed = $this->isAllowed('parent_category');

        if ($isAllowed && ($this->object->getCalendarUid() || $this->rightsObj->isAllowedToCreateGeneralCategory() || $this->rightsObj->isAllowedToEditGeneralCategory())) {
            $tempCalendarConf = $this->conf['calendar'];
            $tempCategoryConf = $this->conf['category'];
            $this->conf['calendar'] = $this->object->getCalendarUid();

            if ($this->controller->piVars['calendar_id'] === '0') {
                $this->conf['calendar'] = $this->conf['calendar_id'];
            } elseif ($this->conf['calendar_id']) {
                $this->conf['calendar'] = $this->conf['calendar_id'];
            }
            $this->conf['category'] = $this->object->getParentUID();
            if ((int)$this->conf['category'] === '0') {
                $this->conf['category'] = '-1';
            }
            $this->conf['view.']['edit_category.']['tree.']['calendar'] = $this->conf['calendar'];
            $this->conf['view.']['edit_category.']['tree.']['category'] = $this->conf['category'];

            $categoryArray = $this->modelObj->findAllCategories(
                'cal_category_model',
                'sys_category',
                $this->conf['pidList']
            );

            $sims['###PARENT_CATEGORY###'] = $this->applyStdWrap($this->getCategorySelectionTree(
                $this->conf['view.']['edit_category.']['tree.'],
                $categoryArray,
                true
            ), 'parent_category_stdWrap');

            $this->conf['calendar'] = $tempCalendarConf;
            if ($this->conf['category'] === 'a') {
                $this->conf['category'] = $tempCategoryConf;
            }
        }
    }

    /**
     * @param $template
     * @param $sims
     * @param $rems
     * @param $view
     */
    public function getSharedUserAllowedMarker(& $template, & $sims, & $rems, $view)
    {
        $sims['###SHARED_USER_ALLOWED###'] = '';
        if ($this->isAllowed('shared_user_allowed')) {
            $value = '';
            if ($this->conf['rights.']['edit.']['category.']['fields.']['shared_user_allowed.']['default']) {
                $value = 'checked';
            }
            $sims['###SHARED_USER_ALLOWED###'] = $this->applyStdWrap($value, 'shared_user_allowed_stdWrap');
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
        $temp_sims['###L_CREATE_CATEGORY###'] = $this->controller->pi_getLL('l_create_category');
        $temp_sims['###UID###'] = '';
        if ($this->isEditMode) {
            $temp_sims['###L_CREATE_CATEGORY###'] = $this->controller->pi_getLL('l_edit_category');
            $temp_sims['###UID###'] = $this->object->getUid();
        }
        $temp_sims['###TYPE###'] = 'sys_category';

        $rems['###FORM_START###'] = Functions::substituteMarkerArrayNotCached(
            $temp,
            $temp_sims,
            [],
            []
        );
    }
}
