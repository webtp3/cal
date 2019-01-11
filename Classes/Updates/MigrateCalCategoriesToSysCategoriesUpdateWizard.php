<?php
declare(strict_types=1);

namespace TYPO3\CMS\Cal\Updates;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\HiddenRestriction;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Install\Updates\UpgradeWizardInterface;

/**
 * Class MigrateCalCategoriesToSysCategoriesUpdateWizard
 */
class MigrateCalCategoriesToSysCategoriesUpdateWizard implements UpgradeWizardInterface
{

    /**
     * Return the identifier for this wizard
     * This should be the same string as used in the ext_localconf class registration
     *
     * @return string
     */
    public function getIdentifier(): string
    {
        return __CLASS__;
    }

    /**
     * Return the speaking name of this wizard
     *
     * @return string
     */
    public function getTitle(): string
    {
        return 'Migrate cal categories to sys_cotegories';
    }

    /**
     * Return the description for this wizard
     *
     * @return string
     */
    public function getDescription(): string
    {
        return 'As the current version kicks out the old tree wizard (yay), we take the opportunity to finaly get our whole categorizatiom in order.';
    }

    /**
     * Execute the update
     *
     * Called when a wizard reports that an update is necessary
     *
     * @return bool
     */
    public function executeUpdate(): bool
    {
        $sysCategoryConnection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('sys_category');
        $sysCategoryMmConnection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('sys_category_record_mm');

        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tx_cal_category');
        $queryBuilder->getRestrictions()->removeByType(HiddenRestriction::class);
        $rows = $queryBuilder->select('*')->from('tx_cal_category')->execute()->fetchAll();

        $mappingArray = [];

        foreach ($rows as $row) {
            $row['parent'] = $row['parent_category'];
            $row['l10n_parent'] = $row['l18n_parent'];
            $row['l10n_diffsource'] = $row['l18n_diffsource'];
            $uid = $row['uid'];
            unset(
                $row['uid'],
                $row['parent_category'],
                $row['l18n_parent'],
                $row['l18n_diffsource'],
                $row['no_auto_pb']
            );
            $sysCategoryConnection
                ->insert(
                    'sys_category',
                    $row
                );

            $mappingArray[$uid] = $sysCategoryConnection->lastInsertId();
        }

        $newCategories = $sysCategoryConnection
            ->select(
                [
                    'uid',
                    'parent',
                    't3ver_oid',
                    't3ver_id',
                    't3ver_move_id',
                    't3_origuid',
                    'l10n_parent',
                ],
                'sys_category'
            )
            ->fetchAll();

        foreach ($newCategories as $newCategory) {
            $sysCategoryConnection->update('sys_category', ['parent' => $mappingArray[$newCategory['parent']]],
                ['uid' => $newCategory['uid']]);
            $sysCategoryConnection->update('sys_category', ['t3ver_oid' => $mappingArray[$newCategory['t3ver_oid']]],
                ['uid' => $newCategory['uid']]);
            $sysCategoryConnection->update('sys_category', ['t3ver_id' => $mappingArray[$newCategory['t3ver_id']]],
                ['uid' => $newCategory['uid']]);
            $sysCategoryConnection->update('sys_category',
                ['t3ver_move_id' => $mappingArray[$newCategory['t3ver_move_id']]], ['uid' => $newCategory['uid']]);
            $sysCategoryConnection->update('sys_category', ['t3_origuid' => $mappingArray[$newCategory['t3_origuid']]],
                ['uid' => $newCategory['uid']]);
            $sysCategoryConnection->update('sys_category',
                ['l10n_parent' => $mappingArray[$newCategory['l10n_parent']]], ['uid' => $newCategory['uid']]);
        }

        $calCategoryMmConnection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('sys_category');
        $rows = $calCategoryMmConnection
            ->select(
                ['*'],
                'tx_cal_event_category_mm'
            )
            ->fetchAll();

        foreach ($rows as $row) {
            $sysCategoryMmConnection
                ->insert(
                    'sys_category_record_mm',
                    [
                        'uid_local' => $mappingArray[$row['uid_foreign']],
                        'uid_foreign' => $mappingArray[$row['uid_local']],
                        'tablenames' => 'tx_cal_event',
                        'fieldname' => 'category_id',
                        'sorting' => 0,
                        'sorting_foreign' => $mappingArray[$row['sorting']],
                    ]
                );
        }

        $calCategoryConnection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('tx_cal_category');
        $calCategoryConnection
            ->update(
                'tx_cal_category',
                [
                    'deleted' => 1
                ],
                [
                    'deleted' => 0
                ]
            );

        return true;
    }

    /**
     * Is an update necessary?
     *
     * Is used to determine whether a wizard needs to be run.
     * Check if data for migration exists.
     *
     * @return bool
     */
    public function updateNecessary(): bool
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tx_cal_category');
        $queryBuilder->getRestrictions()->removeByType(HiddenRestriction::class);
        return (bool)$queryBuilder->select('*')->from('tx_cal_category')->execute()->rowCount();
    }

    /**
     * Returns an array of class names of Prerequisite classes
     *
     * This way a wizard can define dependencies like "database up-to-date" or
     * "reference index updated"
     *
     * @return string[]
     */
    public function getPrerequisites(): array
    {
        return [];
    }
}
