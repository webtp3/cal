<?php

namespace TYPO3\CMS\Cal\Controller;

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
use TYPO3\CMS\Cal\Utility\Registry;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\TimeTracker\TimeTracker;
use TYPO3\CMS\Core\TypoScript\ExtendedTemplateService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\Page\PageRepository;

/**
 * API for calendar base (cal)
 */
class Api
{
    public $cObj;
    public $rightsObj;
    public $modelObj;
    public $viewObj;
    public $controller;
    public $conf;
    public $prefixId = 'tx_cal_controller';
    public $unsetTSFEOnDestruct = false;

    /**
     * Example:
     * require_once ('class.tx_cal_api.php');
     * $calAPI = new Api($this->cObj, &$conf);
     * $event = $calAPI->findEvent('2','tx_cal_phpicalendar');
     */
    public function tx_cal_api_with(&$cObj, &$conf)
    {
        $this->cObj = &$cObj;
        $this->conf = &$conf;
        if (!$GLOBALS['TCA']) {
            $GLOBALS['TSFE']->includeTCA();
        }

        $this->conf['useInternalCaching'] = 1;
        $this->conf['cachingEngine'] = 'cachingFramework';
        $this->conf['writeCachingInfoToDevlog'] = 0;

        $GLOBALS['TSFE']->settingLocale();

        $this->controller = GeneralUtility::makeInstance(Controller::class);
        $this->controller->cObj = &$this->cObj;
        $this->controller->conf = &$this->conf;

        $this->controller->setWeekStartDay();

        $this->controller->cleanPiVarParam($this->piVars);
        $this->controller->clearPiVarParams();
        $this->controller->getParamsFromSession();
        $this->controller->initCaching();
        $this->controller->initConfigs();

        Controller::initRegistry($this->controller);
        $this->rightsObj = &Registry::Registry('basic', 'rightscontroller');
        $this->rightsObj = GeneralUtility::makeInstanceService('cal_rights_model', 'rights');
        $this->rightsObj->setDefaultSaveToPage();

        $this->modelObj = &Registry::Registry('basic', 'modelcontroller');
        $this->modelObj = new ModelController();

        $this->viewObj = &Registry::Registry('basic', 'viewcontroller');
        $this->viewObj = GeneralUtility::makeInstance(ViewController::class);

        return $this;
    }

    /**
     * @param $pid
     * @param string $feUserObj
     * @return Api
     * @throws \TYPO3\CMS\Core\Error\Http\PageNotFoundException
     * @throws \TYPO3\CMS\Core\Error\Http\ServiceUnavailableException
     */
    public function tx_cal_api_without($pid, $feUserObj = '')
    {
        $cObj = new ContentObjectRenderer();

        $GLOBALS['TT'] = new TimeTracker();

        // ***********************************
        // Creating a fake $TSFE object
        // ***********************************
        $this->unsetTSFEOnDestruct = true;

        $GLOBALS['TSFE'] = GeneralUtility::makeInstance(
            Tsfe::class,
            $GLOBALS['TYPO3_CONF_VARS'],
            $pid,
            '0',
            1,
            '',
            '',
            '',
            ''
        );
        $GLOBALS['TSFE']->connectToDB();
        if ($feUserObj == '') {
            $GLOBALS['TSFE']->initFEuser();
        } else {
            $GLOBALS['TSFE']->fe_user = &$feUserObj;
        }

        $GLOBALS['TSFE']->fetch_the_id();
        $GLOBALS['TSFE']->getPageAndRootline();
        $GLOBALS['TSFE']->initTemplate();
        $GLOBALS['TSFE']->tmpl->getFileName_backPath = PATH_site;
        $GLOBALS['TSFE']->forceTemplateParsing = 1;
        $GLOBALS['TSFE']->getConfigArray();
        $GLOBALS['TSFE']->settingLocale();

        // we need to get the plugin setup to create correct source URLs
        $template = new ExtendedTemplateService(); // Defined global here!
        $template->tt_track = 0;
        // Do not log time-performance information
        $template->init();
        $sys_page = new PageRepository();
        $rootLine = $sys_page->getRootLine($pid);
        $template->runThroughTemplates($rootLine); // This generates the constants/config + hierarchy info for the template.
        $template->generateConfig(); //
        $conf = $template->setup['plugin.']['tx_cal_controller.'];

        // get the calendar plugin record where starting pages value is the same
        // as the pid
        $fields = '*';
        $tables = 'tt_content';
        $where = 'tt_content.list_type="cal_controller" AND tt_content.deleted=0 AND pid=' . $pid;

        list($tt_content_row) = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows($fields, $tables, $where);

        // if starting point didn't return any records, look for general records
        // storage page.
        if (!$tt_content_row) {
            $tables = 'tt_content LEFT JOIN pages ON tt_content.pid = pages.uid';
            $where = 'tt_content.list_type="cal_controller" AND tt_content.deleted=0 AND tt_content.pid=' . $pid;
            list($tt_content_row) = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows($fields, $tables, $where);
        }

        if ($tt_content_row['pages']) {
            // $conf['pages'] = $tt_content_row['pages'];
            $cObj->data = $tt_content_row;
        }
        if (TYPO3_MODE == 'BE') {
            if (VersionNumberUtility::convertVersionNumberToInteger(TYPO3_version) < 8000000) {
                $this->cleanUpPageRendererBackPath();
            }
        }
        return $this->tx_cal_api_with($cObj, $conf);
    }

    /**
     * Destructor to clean up when we're done with the API object.
     */
    public function __destruct()
    {
        // If we created our own TSFE object earlier, get rid of it so that we don't interfere with other scripts.
        if ($this->unsetTSFEOnDestruct) {
            unset($GLOBALS['TSFE']);
        }
    }

    /**
     * @param $uid
     * @param $type
     * @param string $pidList
     * @return mixed
     */
    public function findEvent($uid, $type, $pidList = '')
    {
        return $this->modelObj->findEvent($uid, $type, $pidList);
    }

    /**
     * @param $uid
     * @param $type
     * @param string $pidList
     * @return mixed
     */
    public function saveEvent($uid, $type, $pidList = '')
    {
        return $this->modelObj->saveEvent($uid, $type, $pidList);
    }

    /**
     * @param $uid
     * @param $type
     * @return mixed
     */
    public function removeEvent($uid, $type)
    {
        return $this->modelObj->removeEvent($uid, $type);
    }

    /**
     * @param $uid
     * @param $type
     * @param string $pidList
     * @return mixed
     */
    public function saveExceptionEvent($uid, $type, $pidList = '')
    {
        return $this->modelObj->saveExceptionEvent($uid, $type, $pidList);
    }

    /**
     * @param $uid
     * @param $type
     * @param string $pidList
     * @return mixed
     */
    public function findLocation($uid, $type, $pidList = '')
    {
        return $this->modelObj->findLocation($uid, $type, $pidList);
    }

    /**
     * @param string $type
     * @param string $pidList
     * @return mixed
     */
    public function findAllLocations($type = '', $pidList = '')
    {
        return $this->modelObj->findAllLocations($type, $pidList);
    }

    /**
     * @param $uid
     * @param $type
     * @param string $pidList
     * @return mixed
     */
    public function saveLocation($uid, $type, $pidList = '')
    {
        return $this->modelObj->saveLocation($uid, $type, $pidList);
    }

    /**
     * @param $uid
     * @param $type
     * @return mixed
     */
    public function removeLocation($uid, $type)
    {
        return $this->modelObj->removeLocation($uid, $type);
    }

    /**
     * @param $uid
     * @param $type
     * @param string $pidList
     * @return mixed
     */
    public function findOrganizer($uid, $type, $pidList = '')
    {
        return $this->modelObj->findOrganizer($uid, $type, $pidList);
    }

    /**
     * @param $uid
     * @param $type
     * @param string $pidList
     * @return mixed
     */
    public function findCalendar($uid, $type, $pidList = '')
    {
        return $this->modelObj->findCalendar($uid, $type, $pidList);
    }

    /**
     * @param string $type
     * @param string $pidList
     * @return mixed
     */
    public function findAllCalendar($type = '', $pidList = '')
    {
        return $this->modelObj->findAllCalendar($type, $pidList);
    }

    /**
     * @param string $type
     * @param string $pidList
     * @return mixed
     */
    public function findAllOrganizer($type = '', $pidList = '')
    {
        return $this->modelObj->findAllOrganizer($type, $pidList);
    }

    /**
     * @param $uid
     * @param $type
     * @param string $pidList
     * @return mixed
     */
    public function saveOrganizer($uid, $type, $pidList = '')
    {
        return $this->modelObj->saveOrganizer($uid, $type, $pidList);
    }

    /**
     * @param $uid
     * @param $type
     * @return mixed
     */
    public function removeOrganizer($uid, $type)
    {
        return $this->modelObj->removeOrganizer($uid, $type);
    }

    /**
     * @param $uid
     * @param $type
     * @param string $pidList
     * @return mixed
     */
    public function saveCalendar($uid, $type, $pidList = '')
    {
        return $this->modelObj->saveCalendar($uid, $type, $pidList);
    }

    /**
     * @param $uid
     * @param $type
     * @return mixed
     */
    public function removeCalendar($uid, $type)
    {
        return $this->modelObj->removeCalendar($uid, $type);
    }

    /**
     * @param $uid
     * @param $type
     * @param string $pidList
     * @return mixed
     */
    public function saveCategory($uid, $type, $pidList = '')
    {
        return $this->modelObj->saveCategory($uid, $type, $pidList);
    }

    /**
     * @param $uid
     * @param $type
     * @return mixed
     */
    public function removeCategory($uid, $type)
    {
        return $this->modelObj->removeCategory($uid, $type);
    }

    /**
     * @param $startTimestamp
     * @param $endTimestamp
     * @param string $type
     * @param string $pidList
     * @return mixed
     */
    public function findEventsWithin($startTimestamp, $endTimestamp, $type = '', $pidList = '')
    {
        return $this->modelObj->findAllWithin(
            'cal_event_model',
            $startTimestamp,
            $endTimestamp,
            $type,
            'event',
            $pidList
        );
    }

    /**
     * @param $timestamp
     * @param string $type
     * @param string $pidList
     * @return mixed
     */
    public function findEventsForDay($timestamp, $type = '', $pidList = '')
    {
        return $this->modelObj->findEventsForDay($timestamp, $type, $pidList);
    }

    /**
     * @param $timestamp
     * @param string $type
     * @param string $pidList
     * @return mixed
     */
    public function findEventsForWeek($timestamp, $type = '', $pidList = '')
    {
        return $this->modelObj->findEventsForWeek($timestamp, $type, $pidList);
    }

    /**
     * @param $timestamp
     * @param string $type
     * @param string $pidList
     * @return mixed
     */
    public function findEventsForMonth($timestamp, $type = '', $pidList = '')
    {
        return $this->modelObj->findEventsForMonth($timestamp, $type, $pidList);
    }

    /**
     * @param $timestamp
     * @param string $type
     * @param string $pidList
     * @return mixed
     */
    public function findEventsForYear($timestamp, $type = '', $pidList = '')
    {
        return $this->modelObj->findEventsForYear($timestamp, $type, $pidList);
    }

    /**
     * @param $timestamp
     * @param string $type
     * @param string $pidList
     * @return mixed
     */
    public function findEventsForList($timestamp, $type = '', $pidList = '')
    {
        return $this->modelObj->findEventsForList($timestamp, $type, $pidList);
    }

    /**
     * @param string $type
     * @param string $pidList
     * @return mixed
     */
    public function findCategoriesForList($type = '', $pidList = '')
    {
        return $this->modelObj->findCategoriesForList($type, $pidList);
    }

    /**
     * @param $timestamp
     * @param string $type
     * @param $pidList
     * @return mixed
     */
    public function findEventsForIcs($timestamp, $type = '', $pidList)
    {
        return $this->modelObj->findEventsForIcs($timestamp, $type, $pidList);
    }

    /**
     * @param string $type
     * @param string $pidList
     * @return mixed
     */
    public function searchEvents($type = '', $pidList = '')
    {
        return $this->modelObj->searchEvents($type, $pidList);
    }

    /**
     * @param string $type
     * @param string $pidList
     * @return mixed
     */
    public function searchLocation($type = '', $pidList = '')
    {
        return $this->modelObj->searchLocation($type, $pidList);
    }

    /**
     * @param string $type
     * @param string $pidList
     * @return mixed
     */
    public function searchOrganizer($type = '', $pidList = '')
    {
        return $this->modelObj->searchOrganizer($type, $pidList);
    }

    /**
     * @param $master_array
     * @param $getdate
     * @param bool $sendHeaders
     * @return mixed
     */
    public function drawIcs($master_array, $getdate, $sendHeaders = true)
    {
        return $this->viewObj->drawIcs($master_array, $getdate, $sendHeaders);
    }

    /*
     * !brief process the Typoscript array to final output @param string The Typoscrypt Object to process @param string The content between the tags to be merged with the TS Objected @return string Processed ooutput of the TS Note: Part of the code is taken from tsobj written by Jean-David Gadina (macmade@gadlab.net)
     */
    /**
     * @param $tsObjPath
     * @param $tag_content
     * @return string
     */
    public function __processTSObject($tsObjPath, $tag_content)
    {
        // Check for a non empty value
        if ($tsObjPath) {

            // Get complete TS template
            $tsObj = &$this->__TSTemplate->setup;

            // Get TS object hierarchy in template
            $tmplPath = explode('.', $tsObjPath);
            // Process TS object hierarchy
            $error = 0;
            for ($i = 0; $i < count($tmplPath); $i++) {

                // Try to get content type
                $cType = $tsObj[$tmplPath[$i]];

                // Try to get TS object configuration array
                $tsNewObj = $tsObj[$tmplPath[$i] . '.'];

                // Merge Configuration found in the tags with typoscript config
                if (count($tag_content)) {
                    $tsNewObj = $this->array_merge_recursive2($tsNewObj, $tag_content[$tsObjPath . '.']);
                }

                // Check object
                if (!$cType && !$tsNewObj) {
                    // Object doesn't exist
                    $error = 1;
                    break;
                }
            }

            // Check object and content type
            if ($error) {

                // Object not found
                return '<strong>Not Found</strong> (' . $tsObjPath . ')';
            }
            if ($this->cTypes[$cType]) {
                // Render Object
                $code = $this->__local_cObj->cObjGetSingle($cType, $tsNewObj);
            } else {

                // Invalid content type
                return '<strong>errors.invalid</strong> (' . $cType . ')';
            }

            // Return object
            return $code;
        }
    }

    /**
     * Returns current PageRenderer
     *
     * @return PageRenderer
     */
    protected function getPageRenderer()
    {
        return GeneralUtility::makeInstance(PageRenderer::class);
    }

    /**
     * Sets backPath of PageRenderer back to null (for Backend)
     * Fixes backpath in for backend. See forge #69319
     */
    protected function cleanUpPageRendererBackPath()
    {
        $this->getPageRenderer()->setBackPath(null);
    }
}
