<?php

namespace TYPO3\CMS\Cal\Updates;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Typo3DbLegacy\Database\DatabaseConnection;

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
 * Upgrade wizard which goes through all files referenced in the tx_cal_event.attachment filed
 * and creates sys_file records as well as sys_file_reference records for the individual usages.
 * @deprecated since ext:cal v2, will be removed in ext:cal v3
 */
class UploadsUpdateWizard extends AbstractUpdateWizard
{

    /**
     * @var string
     */
    protected $title = 'Migrate file relations of tx_cal_event "attachments"';

    /**
     * Returns the migration description
     * @return string The description
     */
    protected function getMigrationDescription()
    {
        return 'There are Content Elements of type "upload" which are referencing files that are not using ' . ' the File Abstraction Layer. This wizard will move the files to fileadmin/' . self::FOLDER_ContentUploads . ' and index them.';
    }

    /**
     * @return mixed|string
     */
    protected function getRecordTableName()
    {
        return 'tx_cal_event';
    }

    /**
     * Returns the table column names
     * @return array:string Array containing the table column names
     */
    protected function getColumnNameArray()
    {
        return ['uid', 'pid', 'attachment', 'attachmentcaption'];
    }

    /**
     * @return string
     */
    protected function getColumnName()
    {
        return 'attachment';
    }

    /**
     * Processes the actual transformation from CSV to sys_file_references
     *
     * @param array $record
     */
    protected function migrateRecord(array $record)
    {
        $collections = [];

        $files = GeneralUtility::trimExplode(',', $record['attachment'], true);
        $descriptions = GeneralUtility::trimExplode('
', $record['attachmentcaption']);
        $i = 0;
        foreach ($files as $file) {
            if (file_exists(PATH_site . 'uploads/tx_cal/media/' . $file)) {
                GeneralUtility::upload_copy_move(
                    PATH_site . 'uploads/tx_cal/media/' . $file,
                    $this->targetDirectory . $file
                );
                $fileObject = $this->storage->getFile(self::FOLDER_ContentUploads . '/' . $file);
                $this->fileIndexRepository->add($fileObject);
                $dataArray = [
                    'uid_local' => $fileObject->getUid(),
                    'tablenames' => 'tx_cal_event',
                    'uid_foreign' => $record['uid'],
                    // the sys_file_reference record should always placed on the same page
                    // as the record to link to, see issue #46497
                    'pid' => $record['pid'],
                    'fieldname' => 'attachment',
                    'sorting_foreign' => $i
                ];
                if (isset($descriptions[$i])) {
                    $dataArray['description'] = $descriptions[$i];
                }
                $GLOBALS['TYPO3_DB']->exec_INSERTquery('sys_file_reference', $dataArray);
                unlink(PATH_site . 'uploads/tx_cal/media/' . $file);
            }
            $i++;
        }
        $this->cleanRecord($record, $i, $collections);
    }

    /**
     * Removes the old fields from the database-record
     *
     * @param array $record
     * @param int $fileCount
     * @param array $collectionUids
     */
    protected function cleanRecord(array $record, $fileCount, array $collectionUids)
    {
        $GLOBALS['TYPO3_DB']->exec_UPDATEquery('tx_cal_event', 'uid = ' . $record['uid'], [
            'attachment' => $fileCount,
            'attachmentcaption' => ''
        ]);
    }

    /**
     * Returns the table and column mapping.
     *
     * @return array
     */
    protected function getTableColumnMapping()
    {
        $mapping = [
            'mapTableName' => 'tx_cal_event',
            'mapFieldNames' => [
                'uid' => 'uid',
                'pid' => 'pid',
                'attachment' => 'attachment',
                'attachmentcaption' => 'attachmentcaption',
            ]
        ];

        if ($GLOBALS['TYPO3_DB'] instanceof DatabaseConnection) {
            if (!empty($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['dbal']['mapping']['tx_cal_event'])) {
                $mapping = array_merge_recursive(
                    $mapping,
                    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['dbal']['mapping']['tx_cal_event']
                );
            }
        }

        return $mapping;
    }
}
