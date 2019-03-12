<?php

/*
 * This file is part of the web-tp3/cal.
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Database\Query\QueryHelper;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class BackendUtilityReplacementUtility
 */
class BackendUtilityReplacementUtility
{
    /**
     * REPLACES BU:getRecordRaw
     * THIS FUNCTION NEEDS TO BE REMOVED ASAP. PLEASE REFER TO
     * Deprecation: #80317 - Deprecate BackendUtilityReplacementUtility::getRawRecord()
     *
     *
     * Returns the first record found from $table with $where as WHERE clause
     * This function does NOT check if a record has the deleted flag set.
     * $table does NOT need to be configured in $GLOBALS['TCA']
     * The query used is simply this:
     * $query = 'SELECT ' . $fields . ' FROM ' . $table . ' WHERE ' . $where;
     *
     * @param string $table Table name (not necessarily in TCA)
     * @param string $where WHERE clause
     * @param string $fields $fields is a list of fields to select, default is '*'
     * @return array|bool First row found, if any, FALSE otherwise
     * @deprecated since TYPO3 CMS 8, will be removed in TYPO3 CMS 9.
     */
    public static function getRawRecord($table, $where = '', $fields = '*')
    {
        $queryBuilder = static::getQueryBuilderForTable($table);
        $queryBuilder->getRestrictions()->removeAll();

        $row = $queryBuilder
            ->select(...GeneralUtility::trimExplode(',', $fields, true))
            ->from($table)
            ->where(QueryHelper::stripLogicalOperatorPrefix($where))
            ->execute()
            ->fetch();

        return $row ?: false;
    }

    /**
     * @param string $table
     * @return QueryBuilder
     */
    protected static function getQueryBuilderForTable($table): QueryBuilder
    {
        return GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($table);
    }
}
