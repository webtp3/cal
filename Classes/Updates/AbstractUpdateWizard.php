<?php

namespace TYPO3\CMS\Cal\Updates;

use RuntimeException;
use TYPO3\CMS\Core\Resource\FileRepository;
use TYPO3\CMS\Core\Resource\Index\FileIndexRepository;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Resource\ResourceStorage;
use TYPO3\CMS\Core\Resource\StorageRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Install\Updates\AbstractUpdate;

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
 * Basic upgrade wizard which goes through all files referenced in the {defined} field
 * and creates sys_file records as well as sys_file_reference records for the individual usages.
 * @deprecated since ext:cal v2, will be removed in ext:cal v3
 */
abstract class AbstractUpdateWizard extends AbstractUpdate
{
    const FOLDER_ContentUploads = '_migrated/cal_uploads';

    /**
     * @var string
     */
    protected $targetDirectory;

    /**
     * @var ResourceFactory
     */
    protected $fileFactory;

    /**
     * @var FileIndexRepository
     */
    protected $fileIndexRepository;

    /**
     * @var FileRepository
     */
    protected $fileRepository;

    /**
     * @var ResourceStorage
     */
    protected $storage;

    /**
     * Initialize all required repository and factory objects.
     *
     * @throws RuntimeException
     */
    protected function init()
    {
        $fileadminDirectory = rtrim($GLOBALS['TYPO3_CONF_VARS']['BE']['fileadminDir'], '/') . '/';
        /** @var $storageRepository StorageRepository */
        $storageRepository = GeneralUtility::makeInstance(StorageRepository::class);
        $storages = $storageRepository->findAll();
        foreach ($storages as $storage) {
            $storageRecord = $storage->getStorageRecord();
            $configuration = $storage->getConfiguration();
            $isLocalDriver = $storageRecord['driver'] === 'Local';
            $isOnFileadmin = !empty($configuration['basePath']) && GeneralUtility::isFirstPartOfStr(
                $configuration['basePath'],
                $fileadminDirectory
                );
            if ($isLocalDriver && $isOnFileadmin) {
                $this->storage = $storage;
                break;
            }
        }
        if (!isset($this->storage)) {
            throw new RuntimeException('Local default storage could not be initialized - might be due to missing sys_file* tables.');
        }
        $this->fileFactory = GeneralUtility::makeInstance(ResourceFactory::class);
        $this->fileIndexRepository = GeneralUtility::makeInstance(FileIndexRepository::class);
        $this->targetDirectory = PATH_site . $fileadminDirectory . self::FOLDER_ContentUploads . '/';
    }

    /**
     * @return mixed
     */
    abstract protected function getMigrationDescription();

    /**
     * Checks if an update is needed
     *
     * @param string &$description The description for the update
     * @return bool TRUE if an update is needed, FALSE otherwise
     */
    public function checkForUpdate(&$description)
    {
        trigger_error('As \TYPO3\CMS\Install\Updates\AbstractUpdate will be removed in TYPO3 v10.0, this wizard will go as well with v3.0 of ext:cal. Affected class: ' . get_class($this), E_USER_DEPRECATED);

        $updateNeeded = false;
        // Fetch records where the field media does not contain a plain integer value
        // * check whether media field is not empty
        // * then check whether media field does not contain a reference count (= not integer)
        $mapping = $this->getTableColumnMapping();
        $sql = $GLOBALS['TYPO3_DB']->SELECTquery(
            'COUNT(' . $mapping['mapFieldNames']['uid'] . ')',
            $mapping['mapTableName'],
            '1=1'
        );
        $whereClause = $this->getDbalCompliantUpdateWhereClause();
        $sql = str_replace('WHERE 1=1', $whereClause, $sql);
        $resultSet = $GLOBALS['TYPO3_DB']->sql_query($sql);
        $notMigratedRowsCount = 0;
        if ($resultSet !== false) {
            list($notMigratedRowsCount) = $GLOBALS['TYPO3_DB']->sql_fetch_row($resultSet);
            $notMigratedRowsCount = (int)$notMigratedRowsCount;
            $GLOBALS['TYPO3_DB']->sql_free_result($resultSet);
        }
        if ($notMigratedRowsCount > 0) {
            $description = $this->getMigrationDescription();
            $updateNeeded = true;
        }
        return $updateNeeded;
    }

    /**
     * @return mixed
     */
    abstract protected function getRecordTableName();

    /**
     * Performs the database update.
     *
     * @param array &$dbQueries Queries done in this update
     * @param mixed &$customMessages Custom messages
     * @return bool TRUE on success, FALSE on error
     */
    public function performUpdate(array &$dbQueries, &$customMessages)
    {
        trigger_error('As \TYPO3\CMS\Install\Updates\AbstractUpdate will be removed in TYPO3 v10.0, this wizard will go as well with v3.0 of ext:cal. Affected class: ' . get_class($this), E_USER_DEPRECATED);

        $this->init();
        $records = $this->getRecordsFromTable($this->getRecordTableName());
        $this->checkPrerequisites();
        foreach ($records as $singleRecord) {
            $this->migrateRecord($singleRecord);
        }
        return true;
    }

    /**
     * Ensures a new folder "fileadmin/cal_upload/" is available.
     */
    protected function checkPrerequisites()
    {
        if (!$this->storage->hasFolder(self::FOLDER_ContentUploads)) {
            $this->storage->createFolder(self::FOLDER_ContentUploads, $this->storage->getRootLevelFolder());
        }
    }

    /**
     * Processes the actual transformation from CSV to sys_file_references
     *
     * @param array $record
     */
    abstract protected function migrateRecord(array $record);

    /**
     * Removes the old fields from the database-record
     *
     * @param array $record
     * @param int $fileCount
     * @param array $collectionUids
     */
    abstract protected function cleanRecord(array $record, $fileCount, array $collectionUids);

    /**
     * @return mixed
     */
    abstract protected function getColumnNameArray();

    /**
     * Retrieve every record which needs to be processed
     *
     * @return array
     */
    protected function getRecordsFromTable()
    {
        $mapping = $this->getTableColumnMapping();
        $reverseFieldMapping = array_flip($mapping['mapFieldNames']);

        $fields = [];
        foreach ($this->getColumnNameArray() as $columnName) {
            $fields[] = $mapping['mapFieldNames'][$columnName];
        }
        $fields = implode(',', $fields);

        $sql = $GLOBALS['TYPO3_DB']->SELECTquery(
            $fields,
            $mapping['mapTableName'],
            '1=1'
        );
        $whereClause = $this->getDbalCompliantUpdateWhereClause();
        $sql = str_replace('WHERE 1=1', $whereClause, $sql);
        $resultSet = $GLOBALS['TYPO3_DB']->sql_query($sql);
        $records = [];
        if (!$GLOBALS['TYPO3_DB']->sql_error()) {
            while (($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($resultSet)) !== false) {
                // Mapping back column names to native TYPO3 names
                $record = [];
                foreach ($reverseFieldMapping as $columnName => $finalColumnName) {
                    $record[$finalColumnName] = $row[$columnName];
                }
                $records[] = $record;
            }
            $GLOBALS['TYPO3_DB']->sql_free_result($resultSet);
        }
        return $records;
    }

    /**
     * @return string The column name containing the relations
     */
    abstract protected function getColumnName();

    /**
     * Returns a DBAL-compliant where clause to be used for the update where clause.
     * We have DBAL-related code here because the SQL parser is not able to properly
     * parse this complex condition but we know that it is compatible with the DBMS
     * we support in TYPO3 Core.
     *
     * @return string
     */
    protected function getDbalCompliantUpdateWhereClause()
    {
        $mapping = $this->getTableColumnMapping();

        $where = sprintf(
            'WHERE %s <> \'\'',
            $mapping['mapFieldNames'][$this->getColumnName()]
            ) . ' AND ' . $mapping['mapFieldNames'][$this->getColumnName()] . ' <> \'0\' AND cast( ' . $mapping['mapFieldNames'][$this->getColumnName()] . ' AS decimal ) = 0';

        return $where;
    }

    /**
     * Returns the table and column mapping.
     *
     * @return array
     */
    abstract protected function getTableColumnMapping();
}
