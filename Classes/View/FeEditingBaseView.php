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
use TYPO3\CMS\Cal\Model\EventModel;
use TYPO3\CMS\Cal\Utility\Functions;
use TYPO3\CMS\Cal\Utility\Registry;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\File\BasicFileUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * A service which serves as base for all fe-editing clases.
 */
class FeEditingBaseView extends BaseView
{
    /**
     * @var EventModel
     */
    public $object;

    /**
     * @var string
     */
    public $objectString = '';

    /**
     * @var bool
     */
    public $isEditMode = false;

    /**
     * @var bool
     */
    public $isConfirm = false;

    /**
     * @var string
     */
    public $serviceName = 'insertServiceName';

    /**
     * @var string
     */
    public $table = 'insertTableName';

    /**
     * @var array
     */
    public $lastPiVars = [];

    /**
     * @param $template
     * @param $sims
     * @param $rems
     * @param $view
     */
    protected function getTemplateSingleMarker(& $template, & $sims, & $rems, $view)
    {
        preg_match_all('!\###([A-Z0-9_-|]*)\###!is', $template, $match);
        $allSingleMarkers = array_unique($match[1]);
        foreach ($allSingleMarkers as $marker) {
            $required = '';
            if (preg_match('/.*_LABEL/', $marker)) {
                $sims['###' . $marker . '###'] = $this->controller->pi_getLL('l_' . $this->objectString . '_' . strtolower(substr(
                        $marker,
                        0,
                        -6
                    )));
                if ($sims['###' . $marker . '###'] === '') {
                    $sims['###' . $marker . '###'] = $this->controller->pi_getLL('l_' . strtolower(substr(
                            $marker,
                            0,
                            -6
                        )));
                }
                continue;
            }
            $funcFromMarker = 'get' . str_replace(
                    ' ',
                    '',
                    ucwords(str_replace('_', ' ', strtolower($marker)))
                ) . 'Marker';
            if (preg_match('/MODULE__([A-Z0-9_-])*/', $marker)) {
                $module = GeneralUtility::makeInstanceService(substr($marker, 8), 'module');
                if (is_object($module)) {
                    $sims['###' . $marker . '###'] = $module->start($this);
                }
            } elseif (method_exists($this, $funcFromMarker)) {
                $this->$funcFromMarker($template, $sims, $rems, $view);
            } else {
                $functionName = 'get' . ucwords(strtolower($marker));
                if ($this->isConfirm && $this->isAllowed(strtolower($marker))) {
                    if (method_exists($this->object, $functionName)) {
                        $value = $this->object->$functionName();
                    } else {
                        $value = $this->object->row[strtolower($marker)];
                    }

                    $sims['###' . $marker . '_VALUE###'] = $value;
                    $sims['###' . $marker . '###'] = $this->applyStdWrap(
                        $value,
                        strtolower($marker) . '_stdWrap'
                    );
                } elseif ($this->isEditMode && $this->isAllowed(strtolower($marker))) {
                    /**
                     * @fixme Quick fix for the RTE related Javascript.
                     *
                     * If the marker hasn't been defined already and the value for the marker isn't blank, set it. Otherwise, let the previous value stick.
                     */
                    if (!($sims['###' . $marker . '###'] && !$this->object->row[strtolower($marker)])) {
                        $sims['###' . $marker . '_VALUE###'] = $this->object->row[strtolower($marker)];
                        $sims['###' . $marker . '###'] = $this->applyStdWrap(
                            $this->object->row[strtolower($marker)],
                            strtolower($marker) . '_stdWrap'
                        );
                    }
                } elseif (!$this->isEditMode && $this->isAllowed(strtolower($marker))) {
                    $value = '';
                    if (method_exists($this->object, $functionName)) {
                        $value = $this->object->$functionName();
                    }
                    if ($value === '') {
                        if (!empty($this->conf['rights.']['create.'][$this->objectString . '.']['fields.'][strtolower($marker) . '.']['default'])) {
                            $value = $this->conf['rights.']['create.'][$this->objectString . '.']['fields.'][strtolower($marker) . '.']['default'];
                        } else {
                            $value = $this->object->row[strtolower($marker)];
                        }
                    }
                    /**
                     * @fixme Quick fix for the RTE related Javascript.
                     *
                     * If the marker hasn't been defined already and the value for the marker isn't blank, set it. Otherwise, let the previous value stick.
                     */
                    if (!($value === '' && $sims['###' . $marker . '###'])) {
                        $sims['###' . $marker . '###'] = $this->applyStdWrap(
                            $value,
                            strtolower($marker) . '_stdWrap'
                        );
                    }
                } else {
                    $sims['###' . $marker . '###'] = '';
                    $sims['###' . $marker . '_VALUE###'] = '';
                }
            }
            if (!$this->isConfirm && $this->conf['rights.'][($this->isEditMode ? 'edit' : 'create') . '.'][$this->objectString . '.']['fields.'][strtolower($marker) . '.']['required']) {
                $required = $this->conf['view.']['required'];
            }
            $sims['###' . $marker . '###'] = str_replace(
                '###REQUIRED###',
                $required,
                $sims['###' . $marker . '###']
            );
            break;
        }
    }

    /**
     * @param $template
     * @param $sims
     * @param $rems
     * @param $wrapped
     */
    protected function getTemplateSubpartMarker(& $template, & $sims, & $rems, & $wrapped)
    {
        preg_match_all('!\<\!--[a-zA-Z0-9 ]*###([A-Z0-9_-|]*)\###[a-zA-Z0-9 ]*-->!is', $template, $match);

        $allMarkers = array_unique($match[1]);

        foreach ($allMarkers as $marker) {
            switch ($marker) {

                case 'FORM_START':
                    $this->getFormStartMarker($template, $sims, $rems, $wrapped);
                    break;
                case 'FORM_END':
                    $this->getFormEndMarker($template, $sims, $rems, $wrapped);
                    break;
                default:
                    if (preg_match('/MODULE__([A-Z0-9_-])*/', $marker)) {
                        $module = GeneralUtility::makeInstanceService(substr($marker, 8), 'module');
                        if (is_object($module)) {
                            $rems['###' . $marker . '###'] = $module->start($this);
                        }
                    }
                    break;
            }
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
        $rems['###FORM_START###'] = $this->markerBasedTemplateService->getSubpart($template, '###FORM_START###');
    }

    /**
     * @param $template
     * @param $sims
     * @param $rems
     * @param $wrapped
     */
    public function getFormEndMarker(& $template, & $sims, & $rems, & $wrapped)
    {
        $temp = $this->markerBasedTemplateService->getSubpart($template, '###FORM_END###');
        $temp_sims = [];
        $linkParams = $this->controller->shortenLastViewAndGetTargetViewParameters();
        // $linkParams = array_merge($this->lastPiVars,$linkParams);
        $preLinkParams = [];
        foreach ($linkParams as $key => $value) {
            $preLinkParams[$this->prefixId . '[' . $key . ']'] = $value;
        }
        $this->controller->pi_linkTP('', $preLinkParams, $this->conf['cache'], $linkParams['page_id']);
        $temp_sims['###BACK_LINK###'] = $this->cObj->lastTypoLinkUrl;
        $temp_sims['###L_CANCEL###'] = $this->controller->pi_getLL('l_cancel');
        $temp_sims['###L_SUBMIT###'] = $this->controller->pi_getLL('l_submit');
        $temp_sims['###L_SAVE###'] = $this->controller->pi_getLL('l_save');
        $temp_sims['###L_DELETE###'] = $this->controller->pi_getLL('l_delete');
        $rems['###FORM_END###'] = Functions::substituteMarkerArrayNotCached(
            $temp,
            $temp_sims,
            [],
            []
        );
    }

    /**
     * @param $template
     * @param $sims
     * @param $rems
     * @param $view
     */
    public function getHiddenMarker(& $template, & $sims, & $rems, $view)
    {
        $sims['###HIDDEN###'] = '';
        if ($this->isConfirm) {
            $sims['###HIDDEN_VALUE###'] = '';
            if ($this->isAllowed('hidden')) {
                if ($this->object->isHidden()) {
                    $value = 1;
                    $label = $this->controller->pi_getLL('l_true');
                } else {
                    $value = 0;
                    $label = $this->controller->pi_getLL('l_false');
                }
                $sims['###HIDDEN###'] = $this->applyStdWrap($label, 'hidden_stdWrap');
                $sims['###HIDDEN_VALUE###'] = $value;
            }
        } else {
            $sims['###HIDDEN###'] = '';
            if ($this->isEditMode && $this->rightsObj->isAllowedTo('edit', $this->objectString, 'hidden')) {
                $hidden = ' ';
                if ($this->object->isHidden()) {
                    $hidden = ' checked="checked" ';
                }
                $sims['###HIDDEN###'] = $this->cObj->stdWrap(
                    $hidden,
                    $this->conf['view.'][$this->conf['view'] . '.']['hidden_stdWrap.']
                );
            } elseif (!$this->isEditMode && $this->rightsObj->isAllowedTo('create', $this->objectString, 'hidden')) {
                $hidden = ' ';
                if ($this->controller->piVars['hidden'] || $this->conf['rights.']['create.'][$this->objectString . '.']['fields.']['hidden.']['default']) {
                    $hidden = ' checked="checked" ';
                }
                $sims['###HIDDEN###'] = $this->applyStdWrap($hidden, 'hidden_stdWrap');
            }
        }
    }

    /**
     * @param $template
     * @param $sims
     * @param $rems
     */
    public function getTitleMarker(& $template, & $sims, & $rems)
    {
        $sims['###TITLE###'] = '';
        $sims['###TITLE_VALUE###'] = '';
        if ($this->isAllowed('title')) {
            $title = strip_tags($this->object->getTitle());
            $sims['###TITLE###'] = $this->applyStdWrap($title, 'title_stdWrap');
            $sims['###TITLE_VALUE###'] = $title;
        }
    }

    /**
     * @param $template
     * @param $sims
     * @param $rems
     */
    public function getDescriptionMarker(& $template, & $sims, & $rems)
    {
        $sims['###DESCRIPTION###'] = '';
        if ($this->isAllowed('description')) {
            $sims['###DESCRIPTION###'] = $this->applyStdWrap($this->object->getDescription(), 'description_stdWrap');
            $sims['###DESCRIPTION_VALUE###'] = htmlspecialchars($this->object->getDescription());
        }
    }

    /**
     * @param $template
     * @param $sims
     * @param $rems
     * @return string
     */
    public function getCalendarIdMarker(& $template, & $sims, & $rems): string
    {
        $sims['###CALENDAR_ID###'] = '';
        if ($this->isAllowed('calendar_id')) {
            $calendarID = $this->object->getCalendarUid();
            $tempCal = $this->conf['calendar'];
            $this->conf['calendar'] = '';
            $calendarArray = $this->modelObj->findAllCalendar('tx_cal_calendar', $this->conf['pidList']);
            $this->conf['calendar'] = $tempCal;

            if (empty($calendarArray['tx_cal_calendar'])) {
                return '<h3>You have to create a calendar before you can create events</h3>';
            }

            $calendarSelect = '';
            if (!empty($calendarArray['tx_cal_calendar'])) {
                if ($this->objectString === 'category' && $this->rightsObj->isAllowedToCreateGeneralCategory()) {
                    $calendarSelect .= '<option value="0" >' . $this->controller->pi_getLL('l_global_category') . '</option>';
                } else {
                    $calendarSelect .= '<option value="" >' . $this->controller->pi_getLL('l_select') . '</option>';
                }
            }
            /** @var CalendarModel $calendar */
            foreach ($calendarArray['tx_cal_calendar'] as $calendar) {
                if ($this->objectString === 'calendar') {
                    if ($calendar->isUserAllowedToEdit() || $calendar->isUserAllowedToDelete()) {
                        if ($calendar->getUid() === $calendarID) {
                            $selected = 'selected="selected"';
                        } else {
                            $selected = '';
                        }
                        $calendarSelect .= '<option value="' . $calendar->getUid() . '" ' . $selected . '>' . $calendar->getTitle() . '</option>';
                    }
                } elseif ($this->objectString === 'event') {
                    if ($this->rightsObj->isAllowedToCreatePublicEvent() && !$calendar->isPublic()) {
                        if ($calendar->getUid() === $calendarID) {
                            $selected = 'selected="selected"';
                        } else {
                            $selected = '';
                        }
                        $calendarSelect .= '<option value="' . $calendar->getUid() . '" ' . $selected . '>' . $calendar->getTitle() . '</option>';
                    }
                } else {
                    if ($calendar->getUid() === $calendarID) {
                        $selected = 'selected="selected"';
                    } else {
                        $selected = '';
                    }
                    $calendarSelect .= '<option value="' . $calendar->getUid() . '" ' . $selected . '>' . $calendar->getTitle() . '</option>';
                }
            }
            /* Only in create */
            if (count($calendarArray['tx_cal_calendar']) === 1) {
                $this->conf['switch_calendar'] = $calendarArray['tx_cal_calendar'][0]->getUid();
            }

            $sims['###CALENDAR_ID###'] = $this->applyStdWrap($calendarSelect, 'calendar_id_stdWrap');
        }
        return '';
    }

    /**
     * @param $template
     * @param $sims
     * @param $rems
     */
    public function getImageMarker(& $template, & $sims, & $rems)
    {
        $this->getFileMarker('image', $template, $sims, $rems);
    }

    /**
     * @param $template
     * @param $sims
     * @param $rems
     */
    public function getAttachmentMarker(& $template, & $sims, & $rems)
    {
        $this->getFileMarker('attachment', $template, $sims, $rems);
    }

    /**
     * @param $template
     * @param $sims
     * @param $rems
     */
    public function getIcsFileMarker(& $template, & $sims, & $rems)
    {
        $this->getFileMarker('ics_file', $template, $sims, $rems);
    }

    /**
     * @param $marker
     * @param $template
     * @param $sims
     * @param $rems
     */
    protected function getFileMarker($marker, & $template, & $sims, & $rems)
    {
        $temp = '';
        if (!$this->isAllowed($marker)) {
            return;
        }

        $max = $GLOBALS['TCA']['tx_cal_' . $this->objectString]['columns'][$marker]['config']['maxitems'];
        $sims['###' . strtoupper($marker) . '###'] = '';
        $sims['###' . strtoupper($marker) . '_VALUE###'] = '';
        $sims['###' . strtoupper($marker) . '_CAPTION###'] = '';
        $sims['###' . strtoupper($marker) . '_CAPTION_VALUE###'] = '';

        if ($this->isConfirm) {
            $sims['###' . strtoupper($marker) . '###'] = '';

            $fileFunc = new BasicFileUtility();
            $allowedExt = [];
            $denyExt = [];
            if ($marker === 'image') {
                $allowedExt = explode(',', $GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext']);
            } elseif ($marker === 'attachment') {
                $allowedExt = ['*'];
                $denyExt = explode(',', PHP_EXTENSIONS_DEFAULT);
            }
            $i = 0;

            // new files
            if (is_array($_FILES[$this->prefixId]['name'][$marker])) {
                foreach ($_FILES[$this->prefixId]['name'][$marker] as $id => $filename) {
                    $iConf = $this->conf['view.'][$this->conf['view'] . '.'][strtolower($marker) . '_stdWrap.'];
                    if ($_FILES[$this->prefixId]['error'][$marker][$id]) {
                        continue;
                    }
                    $theFile = GeneralUtility::upload_to_tempfile($_FILES[$this->prefixId]['tmp_name'][$marker][$id]);
                    $fI = GeneralUtility::split_fileref($filename);
                    if (in_array($fI['fileext'], $denyExt, true)) {
                        continue;
                    }
                    if ($marker === 'image' && !in_array($fI['fileext'], $allowedExt, true)) {
                        continue;
                    }
                    $theDestFile = $fileFunc->getUniqueName($fileFunc->cleanFileName($fI['file']), 'typo3temp');
                    GeneralUtility::upload_copy_move($theFile, $theDestFile);
                    $iConf['file'] = $theDestFile;
                    $return = '__NEW__' . basename($theDestFile);

                    $temp_sims = [];
                    $temp_sims['###INDEX###'] = $id;
                    $temp_sims['###' . strtoupper($marker) . '_VALUE###'] = $return;
                    $temp = '';
                    if ($marker === 'image') {
                        $temp = $this->renderImage(
                            $iConf['file'],
                            $this->controller->piVars[$marker . '_caption'][$id],
                            $this->controller->piVars[$marker . '_title'][$id],
                            $marker,
                            true
                        );
                    } elseif ($marker === 'attachment' || $marker === 'ics_file') {
                        $temp = $this->renderFile(
                            $iConf['file'],
                            $this->controller->piVars[$marker . '_caption'][$id],
                            $this->controller->piVars[$marker . '_title'][$id],
                            $marker,
                            true
                        );
                    }
                    if ($this->isAllowed($marker . '_caption')) {
                        $temp .= $this->applyStdWrap(
                            $this->controller->piVars[$marker . '_caption'][$id],
                            $marker . '_caption_stdWrap'
                        );
                    }
                    if ($this->isAllowed($marker . '_title')) {
                        $temp .= $this->applyStdWrap(
                            $this->controller->piVars[$marker . '_title'][$id],
                            $marker . '_title_stdWrap'
                        );
                    }
                    $sims['###' . strtoupper($marker) . '###'] .= Functions::substituteMarkerArrayNotCached(
                        $temp,
                        $temp_sims,
                        [],
                        []
                    );

                    $i++;
                }
            }

            $removeFiles = $this->controller->piVars['remove_' . $marker] ?: [];
            $where = 'uid_foreign = ' . $this->conf['uid'] . ' AND  tablenames=\'tx_cal_' . $this->objectString . '\' AND fieldname=\'' . $marker . '\' AND deleted=0';
            if (!empty($removeFiles)) {
                $where .= ' AND uid not in (' . implode(',', array_values($removeFiles)) . ')';
            }

            $result = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'sys_file_reference', $where);
            while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
                if ($marker === 'image') {
                    $temp = $this->renderImage($row, $row['description'], $row['title'], $marker, false);
                } elseif ($marker === 'attachment' || $marker === 'ics_file') {
                    $temp = $this->renderFile($row, $row['description'], $row['title'], $marker, false);
                }
                $temp_sims = [];
                $temp_sims['###' . strtoupper($marker) . '_VALUE###'] = $row['uid'];

                foreach ($this->controller->piVars[$marker] as $index => $image) {
                    if ($image === $row['uid']) {
                        if (isset($this->controller->piVars[$marker . '_caption'][$index])) {
                            $row['description'] = $this->controller->piVars[$marker . '_caption'][$index];
                        }
                        if (isset($this->controller->piVars[$marker . '_title'][$index])) {
                            $row['title'] = $this->controller->piVars[$marker . '_title'][$index];
                        }
                        $temp_sims['###INDEX###'] = $index;
                        break;
                    }
                }
                if ($this->isAllowed($marker . '_caption')) {
                    $temp .= $this->applyStdWrap($row['description'], $marker . '_caption_stdWrap');
                }
                if ($this->isAllowed($marker . '_title')) {
                    $temp .= $this->applyStdWrap($row['title'], $marker . '_title_stdWrap');
                }
                $sims['###' . strtoupper($marker) . '###'] .= Functions::substituteMarkerArrayNotCached(
                    $temp,
                    $temp_sims,
                    [],
                    []
                );
            }
            $GLOBALS['TYPO3_DB']->sql_free_result($result);

            foreach ($removeFiles as $removeFile) {
                $sims['###' . strtoupper($marker) . '###'] .= '<input type="hidden" name="tx_cal_controller[remove_' . $marker . '][]" value="' . $removeFile . '">';
            }
        } elseif ($this->isEditMode && $this->rightsObj->isAllowedTo('edit', $this->objectString, $marker)) {
            $sims['###' . strtoupper($marker) . '###'] = '';
            $i = 0;
            $result = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
                '*',
                'sys_file_reference',
                'uid_foreign = ' . $this->conf['uid'] . ' AND  tablenames=\'tx_cal_' . $this->objectString . '\' AND fieldname=\'' . $marker . '\' AND deleted=0'
            );
            while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
                $temp_sims = [];
                $temp_sims['###' . strtoupper($marker) . '_VALUE###'] = $row['uid'];
                $temp = $this->cObj->stdWrap(
                    '',
                    $this->conf['view.'][$this->conf['view'] . '.'][strtolower($marker) . '_stdWrap.']
                );
                if ($marker === 'image') {
                    $temp_sims['###' . strtoupper($marker) . '_PREVIEW###'] = $this->renderImage(
                        $row,
                        $row['description'],
                        $row['title'],
                        $marker,
                        false
                    );
                } elseif ($marker === 'attachment' || $marker === 'ics_file') {
                    $temp_sims['###' . strtoupper($marker) . '_PREVIEW###'] = $this->renderFile(
                        $row,
                        $row['description'],
                        $row['title'],
                        $marker,
                        false
                    );
                }

                $temp = Functions::substituteMarkerArrayNotCached($temp, $temp_sims, [], []);
                if ($this->isAllowed($marker . '_caption')) {
                    $temp .= $this->applyStdWrap($row['description'], $marker . '_caption_stdWrap');
                }
                if ($this->isAllowed($marker . '_title')) {
                    $temp .= $this->applyStdWrap($row['title'], $marker . '_title_stdWrap');
                }
                $temp_sims['###INDEX###'] = $i;
                $sims['###' . strtoupper($marker) . '###'] .= Functions::substituteMarkerArrayNotCached(
                    $temp,
                    $temp_sims,
                    [],
                    []
                );
                $i++;
            }
            $GLOBALS['TYPO3_DB']->sql_free_result($result);
            $upload = '';
            for (; $i < $max; $i++) {
                $temp_sims = [];
                $upload .= $this->cObj->stdWrap(
                    '',
                    $this->conf['view.'][$this->conf['view'] . '.'][$marker . 'Upload_stdWrap.']
                );
                if ($this->isAllowed($marker . '_caption')) {
                    $upload .= $this->applyStdWrap('', $marker . '_caption_stdWrap');
                }
                if ($this->isAllowed($marker . '_title')) {
                    $upload .= $this->applyStdWrap('', $marker . '_title_stdWrap');
                }
                $temp_sims['###INDEX###'] = $i;
                $upload = Functions::substituteMarkerArrayNotCached(
                    $upload,
                    $temp_sims,
                    [],
                    []
                );
            }
            $sims['###' . strtoupper($marker) . '###'] .= $upload;
        } elseif (!$this->isEditMode && $this->rightsObj->isAllowedTo('create', $this->objectString, $marker)) {
            for ($i = 0; $i < $max; $i++) {
                $value = '';
                $upload = $this->cObj->stdWrap(
                    $value,
                    $this->conf['view.'][$this->conf['view'] . '.'][$marker . 'Upload_stdWrap.']
                );
                if ($this->isAllowed($marker . '_caption')) {
                    $upload .= $this->applyStdWrap('', $marker . '_caption_stdWrap');
                }
                if ($this->isAllowed($marker . '_title')) {
                    $upload .= $this->applyStdWrap('', $marker . '_title_stdWrap');
                }
                $temp_sims['###INDEX###'] = $i;
                $sims['###' . strtoupper($marker) . '###'] .= Functions::substituteMarkerArrayNotCached(
                    $upload,
                    $temp_sims,
                    [],
                    []
                );
            }
        }
    }

    /**
     * @param $template
     * @param $sims
     * @param $rems
     */
    protected function getTranslationOptionsMarker(& $template, & $sims, & $rems)
    {
        if ($this->isEditMode && $this->rightsObj->isViewEnabled('translation') && $this->rightsObj->isAllowedTo(
                'create',
                'translation'
            )) {
            $result = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
                'sys_language_uid',
                'pages_language_overlay',
                'pid = ' . $this->object->row['pid'] . $this->cObj->enableFields('pages_language_overlay'),
                '',
                'sys_language_uid ASC'
            );
            $langIds = [];
            $sims['###TRANSLATION_OPTIONS###'] = '';
            while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
                $langIds[] = $row['sys_language_uid'];
            }
            $GLOBALS['TYPO3_DB']->sql_free_result($result);
            $result = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
                'sys_language_uid',
                $this->table,
                'l18n_parent = ' . $this->object->getUid() . $this->cObj->enableFields($this->table),
                '',
                'sys_language_uid ASC'
            );
            $inUseLangIds = [];
            while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
                $inUseLangIds[] = $row['sys_language_uid'];
            }
            $GLOBALS['TYPO3_DB']->sql_free_result($result);

            $langIds = array_diff($langIds, $inUseLangIds);

            if (!empty($langIds)) {
                $sims['###TRANSLATION_OPTIONS###'] .= 'Create translation:';
                foreach ($langIds as $key => $langId) {
                    if ($langId > 0) {
                        $overrulePIvars = [
                            'view' => 'translation',
                            'overlay' => $langId,
                            'servicename' => $this->serviceName,
                            'subtype' => $this->objectString,
                            'uid' => $this->object->getUid(),
                            'type' => $this->object->getType()
                        ];
                        $piVars = (array)$this->piVars;
                        unset($piVars['DATA']);
                        ArrayUtility::mergeRecursiveWithOverrule($piVars, $overrulePIvars);
                        $overrulePIvars = $piVars;
                        $sims['###TRANSLATION_OPTIONS###'] .= ' ' . $this->controller->pi_linkTP($this->cObj->cObjGetSingle(
                                $this->conf['view.']['translation.']['languageMenu.'][$langId],
                                $this->conf['view.']['translation.']['languageMenu.'][$langId . '.']
                            ), [
                                $this->controller->prefixId => $overrulePIvars,
                                [
                                    'L' => $langId
                                ]
                            ]);
                    } else {
                        $sims['###TRANSLATION_OPTIONS###'] .= ' Please check your alternative language setup. This record points to the default language!';
                    }
                }
            }
        } else {
            $sims['###TRANSLATION_OPTIONS###'] = '';
        }
    }

    /**
     * @param $template
     * @param $sims
     * @param $rems
     */
    protected function getCurrentTranslationMarker(& $template, & $sims, & $rems)
    {
        if ($this->isEditMode && $this->rightsObj->isViewEnabled('translation')) {
            if ($this->object->row['sys_language_uid'] !== 0) {
                $sims['###CURRENT_TRANSLATION###'] = 'Current translation: ' . $this->cObj->cObjGetSingle(
                        $this->conf['view.']['translation.']['languageMenu.'][$this->object->row['sys_language_uid']],
                        $this->conf['view.']['translation.']['languageMenu.'][$this->object->row['sys_language_uid'] . '.']
                    ) . '<br/>';
            } else {
                $sims['###CURRENT_TRANSLATION###'] = 'Current translation: default<br/>';
            }
        } else {
            $sims['###CURRENT_TRANSLATION###'] = '';
        }
    }

    /**
     * @param $requiredFieldsSims
     * @return bool
     */
    protected function checkRequiredFields(&$requiredFieldsSims): bool
    {
        $allRequiredFieldsAreFilled = true;
        $viewParts = explode('_', $this->conf['view']);
        if (count($viewParts) === 2 && is_array($this->conf['rights.'][$viewParts[0] . '.'][$viewParts[1] . '.']['fields.']) > 0) {
            foreach ($this->conf['rights.'][$viewParts[0] . '.'][$viewParts[1] . '.']['fields.'] as $field => $fieldSetup) {
                $myField = str_replace('.', '', $field);
                if (is_array($fieldSetup) && $fieldSetup['required'] && $this->controller->piVars[$myField] === '' && $this->isAllowed($myField)) {
                    $allRequiredFieldsAreFilled = false;
                    $requiredFieldsSims['###' . strtoupper($myField) . '_REQUIRED###'] = $this->conf['view.']['required'];
                } else {
                    $requiredFieldsSims['###' . strtoupper($myField) . '_REQUIRED###'] = '';
                }
            }
        }
        if ($allRequiredFieldsAreFilled && !$this->controller->piVars['formCheck'] === '1') {
            $allRequiredFieldsAreFilled = false;
        }
        return $allRequiredFieldsAreFilled;
    }

    /**
     * @return array
     */
    protected function getDefaultValues(): array
    {
        $defaultValues = [];
        $viewParts = explode('_', $this->conf['view']);
        if (count($viewParts) === 2 && is_array($this->conf['rights.'][$viewParts[0] . '.'][$viewParts[1] . '.']['fields.']) > 0) {
            foreach ($this->conf['rights.'][$viewParts[0] . '.'][$viewParts[1] . '.']['fields.'] as $field => $fieldSetup) {
                $myField = str_replace('.', '', $field);
                if (!$this->controller->piVars[$myField] !== '' && $fieldSetup['default'] !== '') {
                    $defaultValues[$myField] = $fieldSetup['default'];
                }
            }
        }
        return $defaultValues;
    }

    /**
     * @param $constrainSims
     * @return bool
     */
    protected function checkContrains(&$constrainSims): bool
    {
        $noComplains = true;
        $viewParts = explode('_', $this->conf['view']);
        if (count($viewParts) === 2 && is_array($this->conf['rights.'][$viewParts[0] . '.'][$viewParts[1] . '.']['fields.']) > 0) {
            foreach ($this->conf['rights.'][$viewParts[0] . '.'][$viewParts[1] . '.']['fields.'] as $field => $fieldSetup) {
                $myField = str_replace('.', '', $field);
                if ($fieldSetup['constrain.'] !== '') {
                    $constrainSims['###' . strtoupper($myField) . '_CONSTRAIN###'] = $this->constrainParser(
                        $myField,
                        $fieldSetup['constrain.']
                    );
                    if ($constrainSims['###' . strtoupper($myField) . '_CONSTRAIN###'] !== '') {
                        $noComplains = false;
                    }
                }
            }
        }
        return $noComplains;
    }

    /**
     * @param $field
     * @param $constrainConfig
     * @return string
     */
    protected function constrainParser($field, $constrainConfig): string
    {
        $result = [];
        $rightsObj = &Registry::Registry('basic', 'rightscontroller');
        foreach ($constrainConfig as $rule) {
            $value = $this->ruleParser($field, $rule);
            if (
                ($value !== '')
                && ($field === 'start' || $field === 'end')
                && ($rule['rule'] === 'after')
                && ($rule['field'] === 'now')
                && $rightsObj->isAllowedToCreateEventInPast()
            ) {
                $value = '';
            }
            if ($value !== '') {
                $result[] = $value;
            }
        }
        return implode('<br/>', $result);
    }

    /**
     * @param $field
     * @param $rule
     * @return string
     */
    protected function ruleParser($field, $rule): string
    {
        $passedAny = [];
        $rules = GeneralUtility::trimExplode('|', $rule['rule'], 1);
        foreach ($rules as $rulePart) {
            if ($rule['conditionField']) {
                $field = $rule['conditionField'];
            }
            $failed = false;
            switch ($rulePart) {
                case 'before':
                case 'less':
                    {
                        $functionA = 'get' . ucwords($field);
                        if (method_exists($this->object, $functionA)) {
                            $a = $this->object->$functionA();
                            if ($rule['count']) {
                                $a = count($a);
                            }
                            if ($rule['field']) {
                                $functionB = 'get' . ucwords($rule['field']);
                                if (method_exists($this->object, $functionB)) {
                                    $b = $this->object->$functionB();
                                    if (is_object($a) && method_exists($a, $rulePart)) {
                                        $result = $a->compareTo($b);
                                        $failed = $result !== -1;
                                    } elseif (is_numeric($a) && is_numeric($b)) {
                                        $failed = $a >= $b;
                                    }
                                }
                            } elseif (isset($rule['value'])) {
                                $failed = $a >= $rule['value'];
                            }
                        }
                        break;
                    }
                case 'after':
                case 'greater':
                    {
                        $functionA = 'get' . ucwords($field);
                        if (method_exists($this->object, $functionA)) {
                            $a = $this->object->$functionA();
                            if ($rule['count']) {
                                $a = count($a);
                            }
                            if ($rule['field']) {
                                $functionB = 'get' . ucwords($rule['field']);
                                if (method_exists($this->object, $functionB)) {
                                    $b = $this->object->$functionB();

                                    if (is_object($a) && method_exists($a, $rulePart)) {
                                        $result = $a->compareTo($b);
                                        $failed = $result !== 1;
                                    } elseif (is_numeric($a) && is_numeric($b)) {
                                        $failed = $a <= $b;
                                    }
                                }
                            } elseif (isset($rule['value'])) {
                                $failed = $a <= $rule['value'];
                            }
                        }
                        break;
                    }
                case 'equals':
                    {
                        $functionA = 'get' . ucwords($field);
                        if (method_exists($this->object, $functionA)) {
                            $a = $this->object->$functionA();
                            if ($rule['count']) {
                                $a = count($a);
                            }
                            if ($rule['field']) {
                                $functionB = 'get' . ucwords($rule['field']);
                                if (method_exists($this->object, $functionB)) {
                                    $b = $this->object->$functionB();
                                    if (is_object($a) && method_exists($a, $rulePart)) {
                                        $result = $a->compareTo($b);
                                        $failed = $result !== 0;
                                    } else {
                                        $failed = $a !== $b;
                                    }
                                }
                            } elseif (isset($rule['value'])) {
                                $failed = $a !== $rule['value'];
                            }
                        }
                        break;
                    }
                case 'unequal':
                    {
                        $functionA = 'get' . ucwords($field);
                        if (method_exists($this->object, $functionA)) {
                            $a = $this->object->$functionA();
                            if ($rule['count']) {
                                $a = count($a);
                            }
                            if ($rule['field']) {
                                $functionB = 'get' . ucwords($rule['field']);
                                if (method_exists($this->object, $functionB)) {
                                    $b = $this->object->$functionB();

                                    if (is_object($a) && method_exists($a, $rulePart)) {
                                        $result = $a->compareTo($b);
                                        $failed = $result === 0;
                                    } elseif (is_numeric($a) && is_numeric($b)) {
                                        $failed = $a === $b;
                                    }
                                }
                            } elseif ($rule['value'] === null || isset($rule['value'])) {
                                $failed = $a === $rule['value'];
                            }
                        }
                        break;
                    }
                case 'regexp':
                    {
                        $functionA = 'get' . ucwords($field);
                        if (method_exists($this->object, $functionA)) {
                            $value = $this->object->$functionA();
                            if (is_string($value) && !preg_match($rule['regexp'], $value)) {
                                $failed = true;
                            }
                        }
                        break;
                    }
                case 'userfunc':
                    {
                        $functionA = 'get' . ucwords($field);
                        if (method_exists($this->object, $functionA)) {
                            $value = $this->object->$functionA();
                            $rule['parent'] = $this;
                            if (!$this->cObj->callUserFunction($rule['userFunc'], $rule, $value)) {
                                $failed = true;
                            }
                        }
                        break;
                    }
            }
            $passedAny[] = $failed ? 0 : 1;
        }
        if (array_sum($passedAny) === 0) {
            return $this->cObj->cObjGetSingle($rule['message'], $rule['message.']);
        }
        return '';
    }

    /**
     * @param $field
     * @return bool
     */
    public function isAllowed($field): bool
    {
        if ($this->isEditMode) {
            $action = 'edit';
        } else {
            $action = 'create';
        }
        if ((int)$this->conf['rights.'][$action . '.'][$this->objectString . '.']['fields.'][$field . '.']['displayCondition.'] !== '') {
            $displayCondition = $this->conf['rights.'][$action . '.'][$this->objectString . '.']['fields.'][$field . '.']['displayCondition.'];
            foreach ($displayCondition as $rule) {
                $value = $this->ruleParser($field, $rule);
                if ($value !== '') {
                    return false;
                }
            }
        }

        return $this->rightsObj->isAllowedTo($action, $this->objectString, $field);
    }

    /**
     * @param $value
     * @param $key
     * @return mixed
     */
    protected function applyStdWrap($value, $key)
    {
        $this->object->initLocalCObject();
        return $this->object->local_cObj->stdWrap($value, $this->conf['view.'][$this->conf['view'] . '.'][$key . '.']);
    }

    /**
     * @param $file
     * @param $caption
     * @param $title
     * @param $marker
     * @param bool $isTemp
     * @return mixed
     */
    protected function renderFile($file, $caption, $title, $marker, $isTemp = false)
    {
        if ($isTemp) {
            $this->conf['view.'][$this->conf['view'] . '.'][$this->objectString . '.'][$marker . '.']['value'] = $file;
        } else {
            // Render existing image -> $file is a sys_file_reference record
            $result = $GLOBALS['TYPO3_DB']->exec_SELECTgetSingleRow(
                'identifier',
                'sys_file',
                'uid = ' . $file['uid_local']
            );
            $this->conf['view.'][$this->conf['view'] . '.'][$this->objectString . '.'][$marker . '.']['value'] = 'fileadmin' . $result['identifier'];
        }
        return $this->cObj->cObjGetSingle(
            $this->conf['view.'][$this->conf['view'] . '.'][$this->objectString . '.'][$marker],
            $this->conf['view.'][$this->conf['view'] . '.'][$this->objectString . '.'][$marker . '.']
        );
    }

    /**
     * @param $file
     * @param $caption
     * @param $title
     * @param $marker
     * @param bool $isTemp
     * @return mixed
     */
    protected function renderImage($file, $caption, $title, $marker, $isTemp = false)
    {
        if ($isTemp) {
            $this->conf['view.'][$this->conf['view'] . '.'][$this->objectString . '.'][$marker . '.']['file'] = $file;
        } else {
            // Render existing image -> $file is a sys_file_reference record
            $result = $GLOBALS['TYPO3_DB']->exec_SELECTgetSingleRow(
                'identifier',
                'sys_file',
                'uid = ' . $file['uid_local']
            );
            $this->conf['view.'][$this->conf['view'] . '.'][$this->objectString . '.'][$marker . '.']['file'] = 'fileadmin' . $result['identifier'];
        }
        $this->conf['view.'][$this->conf['view'] . '.'][$this->objectString . '.'][$marker . '.']['titleText'] = $title;
        $this->conf['view.'][$this->conf['view'] . '.'][$this->objectString . '.'][$marker . '.']['wrap'] = '|<figcaption>' . $caption . '</figcaption>';
        return $this->cObj->cObjGetSingle(
            $this->conf['view.'][$this->conf['view'] . '.'][$this->objectString . '.'][$marker],
            $this->conf['view.'][$this->conf['view'] . '.'][$this->objectString . '.'][$marker . '.']
        );
    }

    /**
     * @param $template
     * @param $sims
     * @param $rems
     * @param $view
     */
    public function getTabbedMenuMarker($template, &$sims, &$rems, $view)
    {
        $tabbedMenuConf = $this->conf['view.'][$view . '.']['tabbedMenu.'];
        foreach ((array)$tabbedMenuConf as $id => $tab) {
            if ($tab['requiredFields'] !== '' && Functions::endsWith($id, '.')) {
                $requiredFields = GeneralUtility::trimExplode(',', $tab['requiredFields'], 1);
                $isAllowed = false;
                foreach ($requiredFields as $field) {
                    if ($this->isAllowed($field)) {
                        $isAllowed = true;
                        break;
                    }
                }
                if (!$isAllowed) {
                    unset($tabbedMenuConf[$id], $tabbedMenuConf[$id . '.']);
                }
            }
        }

        $sims['###TABBED_MENU###'] = $this->cObj->cObjGetSingle(
            $this->conf['view.'][$view . '.']['tabbedMenu'],
            $tabbedMenuConf
        );
    }

    /**
     * @param $template
     * @param $sims
     * @param $rems
     */
    public function getSharedMarker(& $template, & $sims, & $rems)
    {
        $sims['###SHARED###'] = '';
        if ($this->isAllowed('shared')) {
            $cal_shared_user = '';
            $allowedUsers = GeneralUtility::trimExplode(',', $this->conf['rights.']['allowedUsers'], 1);
            $selectedUsers = $this->object->getSharedUsers();
            if (empty($selectedUsers) && !$this->isEditMode) {
                $selectedUsers = GeneralUtility::trimExplode(
                    ',',
                    $this->conf['rights.']['create.']['event.']['fields.']['shared.']['defaultUser'],
                    1
                );
            }
            $selectedUsersList = implode(',', $selectedUsers);
            $result = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
                '*',
                'fe_users',
                'pid in (' . $this->conf['pidList'] . ')' . $this->cObj->enableFields('fe_users')
            );
            while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
                $name = $this->getFeUserDisplayName($row);
                if (!empty($allowedUsers) && GeneralUtility::inList(
                        $this->conf['rights.']['allowedUsers'],
                        $row['uid']
                    )) {
                    if (GeneralUtility::inList($selectedUsersList, $row['uid'])) {
                        $cal_shared_user .= '<input type="checkbox" value="u_' . $row['uid'] . '_' . $row['username'] . '" checked="checked" name="tx_cal_controller[shared][]" />' . $name . '<br />';
                    } else {
                        $cal_shared_user .= '<input type="checkbox" value="u_' . $row['uid'] . '_' . $row['username'] . '"  name="tx_cal_controller[shared][]"/>' . $name . '<br />';
                    }
                } elseif (empty($allowedUsers)) {
                    if (GeneralUtility::inList($selectedUsersList, $row['uid'])) {
                        $cal_shared_user .= '<input type="checkbox" value="u_' . $row['uid'] . '_' . $row['username'] . '" checked="checked" name="tx_cal_controller[shared][]" />' . $name . '<br />';
                    } else {
                        $cal_shared_user .= '<input type="checkbox" value="u_' . $row['uid'] . '_' . $row['username'] . '"  name="tx_cal_controller[shared][]"/>' . $name . '<br />';
                    }
                }
            }
            $GLOBALS['TYPO3_DB']->sql_free_result($result);
            $allowedGroups = GeneralUtility::trimExplode(',', $this->conf['rights.']['allowedGroups'], 1);
            $selectedGroups = $this->object->getSharedGroups();
            if (empty($selectedGroups) && !$this->isEditMode) {
                $selectedGroups = GeneralUtility::trimExplode(
                    ',',
                    $this->conf['rights.']['create.']['event.']['fields.']['shared.']['defaultGroup'],
                    1
                );
            }
            $selectedGroupsList = implode(',', $selectedGroups);
            $result = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
                '*',
                'fe_groups',
                'pid in (' . $this->conf['pidList'] . ')' . $this->cObj->enableFields('fe_groups')
            );
            while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
                $name = $this->getFeGroupDisplayName($row);
                if (!empty($allowedGroups) && GeneralUtility::inList(
                        $this->conf['rights.']['allowedGroups'],
                        $row['uid']
                    )) {
                    if (GeneralUtility::inList($selectedGroupsList, $row['uid'])) {
                        $cal_shared_user .= '<input type="checkbox" value="g_' . $row['uid'] . '_' . $row['title'] . '" checked="checked" name="tx_cal_controller[shared][]" />' . $name . '<br />';
                    } else {
                        $cal_shared_user .= '<input type="checkbox" value="g_' . $row['uid'] . '_' . $row['title'] . '"  name="tx_cal_controller[shared][]"/>' . $name . '<br />';
                    }
                } elseif (empty($allowedGroups)) {
                    if (GeneralUtility::inList($selectedGroupsList, $row['uid'])) {
                        $cal_shared_user .= '<input type="checkbox" value="g_' . $row['uid'] . '_' . $row['title'] . '" checked="checked" name="tx_cal_controller[shared][]" />' . $name . '<br />';
                    } else {
                        $cal_shared_user .= '<input type="checkbox" value="g_' . $row['uid'] . '_' . $row['title'] . '"  name="tx_cal_controller[shared][]"/>' . $name . '<br />';
                    }
                }
            }
            $GLOBALS['TYPO3_DB']->sql_free_result($result);
            $sims['###SHARED###'] = $this->applyStdWrap($cal_shared_user, 'shared_stdWrap');
        }
    }

    /**
     * @param $row
     * @param string $tsName
     * @return mixed
     */
    public function getFeUserDisplayName(&$row, $tsName = 'defaultFeUserDisplayName')
    {
        $this->initLocalCObject($row);
        return $this->local_cObj->cObjGetSingle(
            $this->conf['view.']['event.']['event.'][$tsName],
            $this->conf['view.']['event.']['event.'][$tsName . '.']
        );
    }

    /**
     * @param $row
     * @param string $tsName
     * @return mixed
     */
    public function getFeGroupDisplayName(&$row, $tsName = 'defaultFeGroupDisplayName')
    {
        return $this->getFeUserDisplayName($row, $tsName);
    }
}
