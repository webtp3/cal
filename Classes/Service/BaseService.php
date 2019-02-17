<?php

namespace TYPO3\CMS\Cal\Service;

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
use RuntimeException;
use TYPO3\CMS\Cal\Controller\Controller;
use TYPO3\CMS\Cal\Controller\ModelController;
use TYPO3\CMS\Cal\Model\Model;
use TYPO3\CMS\Cal\Utility\Functions;
use TYPO3\CMS\Cal\Utility\Registry;
use TYPO3\CMS\Core\Resource\Index\FileIndexRepository;
use TYPO3\CMS\Core\Resource\StorageRepository;
use TYPO3\CMS\Core\Service\AbstractService;
use TYPO3\CMS\Core\Utility\File\BasicFileUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class BaseService
 */
abstract class BaseService extends AbstractService
{
    public $cObj; // The backReference to the mother cObj object set at call time
    /**
     * The rights service object
     *
     * @var RightsService
     */
    public $rightsObj;
    /**
     * The model controller object
     *
     * @var ModelController
     */
    public $modelObj;

    /**
     * The main controller object
     *
     * @var Controller
     */
    public $controller;
    public $conf;
    public $prefixId = 'tx_cal_controller';

    /**
     * The calendar service object
     *
     * @var CalendarService
     */
    public $calendarService;

    /**
     * The category service object
     *
     * @var SysCategoryService
     */
    public $categoryService;

    /**
     * The event service object
     *
     * @var EventService
     */
    public $eventService;

    /**
     * The location service object
     *
     * @var LocationService
     */
    public $locationService;

    /**
     * The locationAddress service object
     *
     * @var LocationAddressService
     */
    public $locationAddressService;

    /**
     * The organizer service object
     *
     * @var OrganizerService
     */
    public $organizerService;

    /**
     * The organizerAddress service object
     *
     * @var OrganizerAddressService
     */
    public $organizerAddressService;

    public $fileFunc;
    public $extConf;

    public function __construct()
    {
        $this->controller = &Registry::Registry('basic', 'controller');
        $this->conf = &Registry::Registry('basic', 'conf');
        $this->rightsObj = &Registry::Registry('basic', 'rightscontroller');
        $this->cObj = &Registry::Registry('basic', 'cobj');
        $this->modelObj = &Registry::Registry('basic', 'modelcontroller');
        $this->extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['cal']);
        $this->extConf['categoryService'] = 'sys_category';
    }

    /**
     * @param $mm_table
     * @param $idArray
     * @param $uid
     * @param $tablename
     * @param array $additionalParams
     * @param bool $switchUidLocalForeign
     */
    protected static function insertIdsIntoTableWithMMRelation(
        $mm_table,
        $idArray,
        $uid,
        $tablename,
        $additionalParams = [],
        $switchUidLocalForeign = false
    ) {
        $uid_local = 'uid_local';
        $uid_foreign = 'uid_foreign';
        if ($switchUidLocalForeign) {
            $uid_local = 'uid_foreign';
            $uid_foreign = 'uid_local';
        }
        foreach ($idArray as $key => $foreignid) {
            if (is_numeric($foreignid)) {
                $insertFields = array_merge([
                    $uid_local => $uid,
                    $uid_foreign => $foreignid,
                    'tablenames' => $tablename,
                    'sorting' => $key + 1
                ], $additionalParams);
                $result = $GLOBALS['TYPO3_DB']->exec_INSERTquery($mm_table, $insertFields);
                if (false === $result) {
                    throw new RuntimeException(
                        'Could not write ' . $mm_table . ' record to database: ' . $GLOBALS['TYPO3_DB']->sql_error(),
                        1431458138
                    );
                }
            }
        }
    }

    /**
     * @param $allIds
     * @param $userArray
     * @param $groupArray
     */
    protected static function splitUserAndGroupIds($allIds, &$userArray, &$groupArray)
    {
        foreach ($allIds as $value) {
            preg_match('/(^[ug])_(.*)/', $value, $idname);
            if ($idname[1] === 'u') {
                $userArray[] = $idname[2];
            } elseif ($idname[1] === 'g') {
                $groupArray[] = $idname[2];
            }
        }
    }

    /**
     * @param Model $event
     * @param $insertFields
     */
    protected static function _notifyOfChanges(&$event, &$insertFields)
    {
        $valueArray = $event->getValuesAsArray();
        $notificationService = &Functions::getNotificationService();
        $notificationService->notifyOfChanges($valueArray, $insertFields);
        self::_scheduleReminder($event->getUid());
    }

    /**
     * @param $insertFields
     */
    protected static function _notify(&$insertFields)
    {
        $notificationService = &Functions::getNotificationService();
        $notificationService->notify($insertFields);
    }

    /**
     * @param Model $event
     */
    protected function _invite(&$event)
    {
        $notificationService = &Functions::getNotificationService();
        $oldView = $this->conf['view'];
        $this->conf['view'] = 'ics';
        $eventValues = [];
        $eventValues['uid'] = $event->getUid();
        $notificationService->invite($eventValues, $eventValues);
        $this->conf['view'] = $oldView;
    }

    /**
     * @param $eventUid
     */
    protected static function _scheduleReminder($eventUid)
    {
        $reminderService = &Functions::getReminderService();
        $reminderService->scheduleReminder($eventUid);
    }

    /**
     * @param $uid
     */
    protected static function stopReminder($uid)
    {
        $reminderService = &Functions::getReminderService();
        $reminderService->deleteReminderForEvent($uid);
    }

    /**
     * @param $insertFields
     * @param $object
     * @param bool $isSave
     */
    protected function searchForAdditionalFieldsToAddFromPostData(&$insertFields, $object, $isSave = true)
    {
        $fields = GeneralUtility::trimExplode(
            ',',
            $this->conf['rights.'][$isSave ? 'create.' : 'edit.'][$object . '.']['additionalFields'],
            1
        );
        foreach ($fields as $field) {
            if (($isSave && $this->rightsObj->isAllowedTo(
                'create',
                $object,
                $field
                    )) || (!$isSave && $this->rightsObj->isAllowedTo('edit', $object, $field))) {
                if ($this->conf['view.'][$this->conf['view'] . '.']['additional_fields.'][$field . '_stdWrap.']) {
                    $insertFields[$field] = $this->cObj->stdWrap(
                        $this->controller->piVars[$field],
                        $this->conf['view.'][$this->conf['view'] . '.']['additional_fields.'][$field . '_stdWrap.']
                    );
                } else {
                    $insertFields[$field] = $this->controller->piVars[$field];
                }
            }
        }
    }

    /**
     * @param $objectType
     * @param $type
     * @param $insertFields
     * @param $uid
     */
    protected function checkOnNewOrDeletableFiles($objectType, $type, &$insertFields, $uid)
    {
        if ($this->conf['view.']['enableAjax'] || (int)$this->conf['view.']['dontShowConfirmView'] === 1) {
            $insertFields[$type] = [];
            if (is_array($_FILES[$this->prefixId]['name'][$type])) {
                $files = [];
                if ($this->controller->piVars[$type]) {
                    $files = $this->controller->piVars[$type];
                }

                if (!$this->fileFunc) {
                    $this->fileFunc = new BasicFileUtility();
                }
                $allowedExt = [];
                $denyExt = [];
                if ($type === 'file') {
                    $allowedExt = explode(',', $GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext']);
                } elseif ($type === 'attachment') {
                    $allowedExt = ['*'];
                    $denyExt = explode(',', PHP_EXTENSIONS_DEFAULT);
                }
                $removeFiles = $this->controller->piVars['remove_' . $type] ?: [];

                foreach ($_FILES[$this->prefixId]['name'][$type] as $id => $filename) {
                    if ($_FILES[$this->prefixId]['error'][$type][$id]) {
                        continue;
                    }
                    $theFile = GeneralUtility::upload_to_tempfile($_FILES[$this->prefixId]['tmp_name'][$type][$id]);
                    $fI = GeneralUtility::split_fileref($filename);
                    if (in_array($fI['fileext'], $denyExt, true)) {
                        continue;
                    }
                    if ($type === 'image' && !empty($allowedExt) && !in_array($fI['fileext'], $allowedExt, true)) {
                        continue;
                    }
                    $theDestFile = $this->fileFunc->getUniqueName(
                        $this->fileFunc->cleanFileName($fI['file']),
                        ''
                    );
                    GeneralUtility::upload_copy_move($theFile, $theDestFile);
                    $insertFields[$type][] = basename($theDestFile);
                }

                foreach ($files as $file) {
                    if (in_array($file, $removeFiles, true)) {
                        unlink('typo3temp/' . $file);
                    }
                }
            }
            $insertFields[$type] = implode(',', $insertFields[$type]);
        } else {
            $insertFields[$type] = $this->controller->piVars[$type];
            $this->checkOnTempFile($type, $insertFields, $objectType, $uid);
        }

        $removeFiles = $this->controller->piVars['remove_' . $type] ?: [];
        if (!empty($removeFiles)) {
            $where = 'uid_foreign = ' . $uid . ' AND  tablenames=\'' . $objectType . '\' AND fieldname=\'' . $type . '\' AND uid in (' . implode(
                ',',
                array_values($removeFiles)
                ) . ')';
            $result = $GLOBALS['TYPO3_DB']->exec_DELETEquery('sys_file_reference', $where);
            if (false === $result) {
                throw new RuntimeException(
                    'Could not write sys_file_reference record to database: ' . $GLOBALS['TYPO3_DB']->sql_error(),
                    1431458138
                );
            }
        }
    }

    /**
     * @param $type
     * @param $insertFields
     * @param $objectType
     * @param $uid
     */
    protected function checkOnTempFile($type, &$insertFields, $objectType, $uid)
    {
        $fileadminDirectory = rtrim($GLOBALS['TYPO3_CONF_VARS']['BE']['fileadminDir'], '/') . '/';
        /** @var $storageRepository StorageRepository */
        $storageRepository = GeneralUtility::makeInstance(StorageRepository::class);
        $storages = $storageRepository->findAll();
        foreach ($storages as $tmpStorage) {
            $storageRecord = $tmpStorage->getStorageRecord();
            $configuration = $tmpStorage->getConfiguration();
            $isLocalDriver = $storageRecord['driver'] === 'Local';
            $isOnFileadmin = !empty($configuration['basePath']) && GeneralUtility::isFirstPartOfStr(
                $configuration['basePath'],
                $fileadminDirectory
                );
            if ($isLocalDriver && $isOnFileadmin) {
                $storage = $tmpStorage;
                break;
            }
        }
        if (!isset($storage)) {
            throw new RuntimeException('Local default storage could not be initialized - might be due to missing sys_file* tables.');
        }
        $fileIndexRepository = GeneralUtility::makeInstance(FileIndexRepository::class);
        $targetDirectory = PATH_site . $fileadminDirectory . 'user_upload/';
        if (is_array($insertFields[$type])) {
            foreach ($insertFields[$type] as $file) {
                $this->_checkOnTempFile(
                    $storage,
                    $fileIndexRepository,
                    $targetDirectory,
                    $type,
                    $insertFields,
                    $objectType,
                    $file,
                    $uid
                );
            }
        } else {
            $this->_checkOnTempFile(
                $storage,
                $fileIndexRepository,
                $targetDirectory,
                $type,
                $insertFields,
                $objectType,
                $insertFields[$type],
                $uid
            );
        }
        $count = $GLOBALS['TYPO3_DB']->exec_SELECTcountRows(
            'uid',
            'sys_file_reference',
            'uid_foreign = ' . $uid . ' AND tablenames = \'' . $objectType . '\' AND fieldname = \'' . $type . '\''
        );
        $result = $GLOBALS['TYPO3_DB']->exec_UPDATEquery($objectType, 'uid = ' . $uid, [$type => $count]);
        if (false === $result) {
            throw new RuntimeException(
                'Could not write sys_file_reference record to database: ' . $GLOBALS['TYPO3_DB']->sql_error(),
                1431458138
            );
        }
        unset($insertFields[$type]);
    }

    /**
     * @param $storage
     * @param $fileIndexRepository
     * @param $targetDirectory
     * @param $type
     * @param $insertFields
     * @param $objectType
     * @param $fileOrig
     * @param $uid
     */
    private function _checkOnTempFile(
        &$storage,
        &$fileIndexRepository,
        $targetDirectory,
        $type,
        &$insertFields,
        $objectType,
        $fileOrig,
        $uid
    ) {
        if ($fileOrig === '') {
            return;
        }
        $fileObject = null;
        if (strpos($fileOrig, '__NEW__') === 0) {
            $file = substr($fileOrig, 7);
            if (file_exists(PATH_site . 'typo3temp/' . $file)) {
                GeneralUtility::upload_copy_move(
                    PATH_site . 'typo3temp/' . $file,
                    $targetDirectory . $file
                );
                $fileObject = $storage->getFile('user_upload/' . $file);

                $fileIndexRepository->add($fileObject);
                $dataArray = [
                    'uid_local' => $fileObject->getUid(),
                    'tablenames' => $objectType,
                    'uid_foreign' => $uid,
                    // the sys_file_reference record should always placed on the same page
                    // as the record to link to, see issue #46497
                    'pid' => $insertFields['pid'],
                    'fieldname' => $type,
                    'sorting_foreign' => 0
                ];
                foreach ($this->controller->piVars[$type] as $id => $image) {
                    if ($image === $fileOrig) {
                        if (isset($this->controller->piVars[$type . '_caption'][$id])) {
                            $dataArray['description'] = $this->controller->piVars[$type . '_caption'][$id];
                        }
                        if (isset($this->controller->piVars[$type . '_title'][$id])) {
                            $dataArray['title'] = $this->controller->piVars[$type . '_title'][$id];
                        }
                        break;
                    }
                }

                $result = $GLOBALS['TYPO3_DB']->exec_INSERTquery('sys_file_reference', $dataArray);
                if (false === $result) {
                    throw new RuntimeException(
                        'Could not write sys_file_reference record to database: ' . $GLOBALS['TYPO3_DB']->sql_error(),
                        1431458138
                    );
                }
                unlink(PATH_site . 'typo3temp/' . $file);
            }
        } else {
            $dataArray = [];
            foreach ($this->controller->piVars[$type] as $id => $image) {
                if ($image === $fileOrig) {
                    if (isset($this->controller->piVars[$type . '_caption'][$id])) {
                        $dataArray['description'] = $this->controller->piVars[$type . '_caption'][$id];
                    }
                    if (isset($this->controller->piVars[$type . '_title'][$id])) {
                        $dataArray['title'] = $this->controller->piVars[$type . '_title'][$id];
                    }
                    break;
                }
            }
            if (!empty($dataArray)) {
                $result = $GLOBALS['TYPO3_DB']->exec_UPDATEquery('sys_file_reference', 'uid=' . $fileOrig, $dataArray);
                if (false === $result) {
                    throw new RuntimeException(
                        'Could not write sys_file_reference record to database: ' . $GLOBALS['TYPO3_DB']->sql_error(),
                        1431458138
                    );
                }
            }
        }
    }

    /**
     * @param $table
     * @return string
     */
    protected function getAdditionalWhereForLocalizationAndVersioning($table): string
    {
        $localizationPrefix = 'l18n';
        $selectConf = [];
        if ('sys_category' === $table) {
            $localizationPrefix = 'l10n';
        }
        if ($GLOBALS['TSFE']->sys_language_mode === 'strict' && $GLOBALS['TSFE']->sys_language_content) {
            // sys_language_mode == 'strict': If a certain language is requested, select only news-records from the default language which have a translation. The translated articles will be overlayed later in the list or single function.

            $querryArray = $this->cObj->getQuery($table, [
                'selectFields' => $table . '.' . $localizationPrefix . '_parent',
                'where' => $table . '.sys_language_uid = ' . $GLOBALS['TSFE']->sys_language_content,
                'pidInList' => $this->conf['pidList']
            ], true);

            $tmpres = $GLOBALS['TYPO3_DB']->exec_SELECT_queryArray($querryArray);

            $strictUids = [];

            while ($tmprow = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($tmpres)) {
                $strictUids[] = $tmprow[$localizationPrefix . '_parent'];
            }
            $GLOBALS['TYPO3_DB']->sql_free_result($tmpres);

            $strStrictUids = implode(',', $strictUids);
            $selectConf['where'] .= '(' . $table . '.uid IN (' . ($strStrictUids ?: 0) . ') OR ' . $table . '.sys_language_uid=-1)'; // sys_language_uid=-1 =[all languages]
        } else {
            // sys_language_mode != 'strict': If a certain language is requested, select only news-records in the default language. The translated articles (if they exist) will be overlayed later in the list or single function.
            $selectConf['where'] .= $table . '.sys_language_uid IN (0,-1)';
        }

        if ($this->conf['showRecordsWithoutDefaultTranslation']) {
            $selectConf['where'] = ' (' . $selectConf['where'] . ' OR (' . $table . '.sys_language_uid=' . $GLOBALS['TSFE']->sys_language_content . ' AND NOT ' . $table . '.' . $localizationPrefix . '_parent))';
        }

        // filter Workspaces preview.
        // Since "enablefields" is ignored in workspace previews it's required to filter out news manually which are not visible in the live version AND the selected workspace.
        if ($GLOBALS['TSFE']->sys_page->versioningPreview) {
            // execute the complete query
            $wsSelectconf = $selectConf;
            $wsSelectconf['selectFields'] = 'uid,pid,tstamp,crdate,deleted,hidden,sys_language_uid,' . $localizationPrefix . '_parent,' . $localizationPrefix . '_diffsource,t3ver_oid,t3ver_id,t3ver_label,t3ver_wsid,t3ver_state,t3ver_stage,t3ver_count,t3ver_tstamp,t3_origuid';
            $wsRes = $this->cObj->exec_getQuery($table, $wsSelectconf);
            $tmpWSRes = $GLOBALS['TYPO3_DB']->exec_SELECT_queryArray($wsRes);
            $removeUids = [];
            while ($wsRow = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($tmpWSRes)) {
                $orgUid = $wsRow['uid'];
                $GLOBALS['TSFE']->sys_page->versionOL($table, $wsRow);
                if (!$wsRow['uid']) { // if versionOL returns nothing the record is not visible in the selected Workspace
                    $removeUids[] = $orgUid;
                }
            }
            $GLOBALS['TYPO3_DB']->sql_free_result($tmpWSRes);

            $removeUidList = implode(',', array_unique($removeUids));

            // add list of not visible uids to the whereclause
            if ($removeUidList) {
                $selectConf['where'] .= ' AND ' . $table . '.uid NOT IN (' . $removeUidList . ')';
            }
        }
        return ' AND ' . $selectConf['where'];
    }

    /**
     * @param $uid
     * @param $table
     * @return mixed
     */
    protected static function checkUidForLanguageOverlay($uid, $table): int
    {
        $select = $table . '.*';
        $where = $table . '.uid = ' . $uid;
        $result = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select, $table, $where);
        if ($result) {
            $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result);
            if ($GLOBALS['TSFE']->sys_language_content) {
                $row = $GLOBALS['TSFE']->sys_page->getRecordOverlay(
                    $table,
                    $row,
                    $GLOBALS['TSFE']->sys_language_content,
                    $GLOBALS['TSFE']->sys_language_contentOL
                );
            }
            if ($GLOBALS['TSFE']->sys_page->versioningPreview == true) {
                // get workspaces Overlay
                $GLOBALS['TSFE']->sys_page->versionOL($table, $row);
            }
            if ($row['_LOCALIZED_UID']) {
                $uid = $row['_LOCALIZED_UID'];
            }
            return $uid;
        }
        $GLOBALS['TYPO3_DB']->sql_free_result($result);

        return (int)$uid;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return get_class($this);
    }
}
