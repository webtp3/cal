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
 * Upgrade wizard which goes through all files referenced in the tx_cal_event.image filed
 * and creates sys_file records as well as sys_file_reference records for the individual usages.
 * @deprecated since ext:cal v2, will be removed in ext:cal v3
 */
abstract class AbstractImagesUpdateWizard extends AbstractUpdateWizard
{

    /**
     * Returns the table column names
     * @return array:string Array containing the table column names
     */
    protected function getColumnNameArray()
    {
        return ['uid', 'pid', 'image', 'imagecaption', 'imagetitletext'];
    }

    /**
     * @return string
     */
    protected function getColumnName()
    {
        return 'image';
    }

    /**
     * Processes the actual transformation from CSV to sys_file_references
     *
     * @param array $record
     */
    protected function migrateRecord(array $record)
    {
        $collections = [];

        $files = GeneralUtility::trimExplode(',', $record['image'], true);
        $descriptions = GeneralUtility::trimExplode('
', $record['imagecaption']);
        $titleText = GeneralUtility::trimExplode('
', $record['imagetitletext']);
        $i = 0;
        foreach ($files as $file) {
            if (file_exists(PATH_site . 'uploads/tx_cal/pics/' . $file)) {
                GeneralUtility::upload_copy_move(
                    PATH_site . 'uploads/tx_cal/pics/' . $file,
                    $this->targetDirectory . $file
                );
                $fileObject = $this->storage->getFile(self::FOLDER_ContentUploads . '/' . $file);
                $this->fileIndexRepository->add($fileObject);
                $dataArray = [
                    'uid_local' => $fileObject->getUid(),
                    'tablenames' => $this->getRecordTableName(),
                    'uid_foreign' => $record['uid'],
                    // the sys_file_reference record should always placed on the same page
                    // as the record to link to, see issue #46497
                    'pid' => $record['pid'],
                    'fieldname' => 'image',
                    'sorting_foreign' => $i
                ];
                if (isset($descriptions[$i])) {
                    $dataArray['description'] = $descriptions[$i];
                }
                if (isset($titleText[$i])) {
                    $dataArray['alternative'] = $titleText[$i];
                }
                $GLOBALS['TYPO3_DB']->exec_INSERTquery('sys_file_reference', $dataArray);
                unlink(PATH_site . 'uploads/tx_cal/pics/' . $file);
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
        $GLOBALS['TYPO3_DB']->exec_UPDATEquery($this->getRecordTableName(), 'uid = ' . $record['uid'], [
            'image' => $fileCount,
            'imagecaption' => '',
            'imagetitletext' => ''
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
            'mapTableName' => $this->getRecordTableName(),
            'mapFieldNames' => [
                'uid' => 'uid',
                'pid' => 'pid',
                'image' => 'image',
                'imagecaption' => 'imagecaption',
                'imagetitletext' => 'imagetitletext',
            ]
        ];

        if ($GLOBALS['TYPO3_DB'] instanceof DatabaseConnection) {
            if (!empty($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['dbal']['mapping'][$this->getRecordTableName()])) {
                $mapping = array_merge_recursive(
                    $mapping,
                    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['dbal']['mapping'][$this->getRecordTableName()]
                );
            }
        }

        return $mapping;
    }
}
