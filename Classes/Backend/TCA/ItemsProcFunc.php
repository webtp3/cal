<?php

namespace TYPO3\CMS\Cal\Backend\TCA;

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
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Database\QueryGenerator;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ItemsProcFunc
{

    /**
     * Gets the items array of all available translations.
     *
     * @param
     *            array        The current config array.
     * @return array
     *
     * @todo Localize translation names. Probably not too critical since
     *       they're mostly English anyway but its easy to do.
     * @deprecated since ext:cal version 2.x. Will be removed in version 3.0.0
     */
    public function getDayTimes($config)
    {
        $interval = 60 * 30;
        $dayLength = 60 * 60 * 24;
        for ($time = 0; $time < $dayLength; $time += $interval) {
            // gmdate is ok, as long as $time just holds information about 24h.
            $label = gmdate($GLOBALS['TYPO3_CONF_VARS']['SYS']['hhmm'], $time);
            $value = gmdate('Hi', $time);
            $config['items'][] = [
                $label,
                $value
            ];
        }

        // Add an entry for the end of the day.
        $label = gmdate($GLOBALS['TYPO3_CONF_VARS']['SYS']['hhmm'], $dayLength - 1);
        $value = 2400;
        $config['items'][] = [
            $label,
            $value
        ];

        return $config;
    }

    /**
     * Gets the listing of users and groups.
     *
     * @param
     *            array        The current config array.
     * @return array
     * @deprecated since ext:cal version 2.x. Will be removed in version 3.0.0
     */
    public function getUsersAndGroups($config)
    {
        /* Add frontend groups */
        $table = 'fe_groups';
        $where = '1=1 ' . BackendUtility::BEenableFields($table) . BackendUtility::deleteClause($table);
        $res = $GLOBALS['TYPO3_DB']->exec_selectQuery('*', $table, $where, '', 'title');
        if ($res) {
            while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
                $label = BackendUtility::getRecordTitle($table, $row);
                $value = -1 * intval($row['uid']);
                $config['items'][] = [
                    $label,
                    $value
                ];
            }
            $GLOBALS['TYPO3_DB']->sql_free_result($res);
        }

        /* Add a divider */
        $config['items'][] = [
            '------',
            '--div--'
        ];

        /* Add frontend users */
        $table = 'fe_users';
        $where = '1=1 ' . BackendUtility::BEenableFields($table) . BackendUtility::deleteClause($table);
        $res = $GLOBALS['TYPO3_DB']->exec_selectQuery('*', $table, $where, '', 'name');
        if ($res) {
            while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
                $label = BackendUtility::getRecordTitle($table, $row);
                $value = $row['uid'];
                $config['items'][] = [
                    $label,
                    $value
                ];
            }
            $GLOBALS['TYPO3_DB']->sql_free_result($res);
        }
        return $config;
    }

    /**
     * General purpose function for fetching records from a given table using a combination of backend access control
     * settings and User TSConfig options.
     * Records are added to then added to the items array.
     *
     * @param
     *            array        Associate array with keys 'items', 'config', 'TSconfig', 'table', 'row', and 'field'.
     * @return none
     * @deprecated since ext:cal version 2.x. Will be removed in version 3.0.0
     */
    public function getRecords(&$params)
    {
        $table = $params['config']['itemsProcFunc_config']['table'];
        $where = $params['config']['itemsProcFunc_config']['where'];
        $groupBy = $params['config']['itemsProcFunc_config']['groupBy'];
        $orderBy = $params['config']['itemsProcFunc_config']['orderBy'];
        $limit = $params['config']['itemsProcFunc_config']['limit'];

        /* Get the records, with access restrictions and all that good stuff applied. */
        $res = self::getSQLResource($table, $where, $groupBy, $orderBy, $limit, $params['row']['pid']);

        /* Loop over all records, adding them to the items array */
        while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
            $label = BackendUtility::getRecordTitle($table, $row);
            $value = $row['uid'];
            $params['items'][] = [
                $label,
                $value
            ];
        }
    }

    /**
     * General purpose function for fetching records from a given table using a combination of backend access control
     * settings and User TSConfig options.
     * A SQL resource is returned.
     * exec_SELECTquery($select_fields,$from_table,$where_clause,$groupBy='',$orderBy='',$limit='')
     *
     * @param
     *            string        Name of the table.
     * @param
     *            string        Custom WHERE clause.
     * @param
     *            string        GROUP BY options.
     * @param
     *            string ORDER BY options.
     * @param
     *            string        LIMIT options.
     * @return object resource.
     * @deprecated since ext:cal version 2.x. Will be removed in version 3.0.0
     */
    public static function getSQLResource($table, $where = '', $groupBy = '', $orderBy = '', $limit = '', $pid = '')
    {
        /* Initialize the variables and config options */
        $be_userCategories = [
            0
        ];
        $be_userCalendars = [
            0
        ];
        $enableAccessControl = false;
        $accessControlWhere = '';
        $languageWhere = '';
        $limitViewOnlyToPidsWhere = '';

        // Load cache from BE User data
        $cache = $GLOBALS['BE_USER']->getSessionData('cal_itemsProcFunc');
        if (!$cache) {
            $cache = [];
        }

        if (!$GLOBALS['BE_USER']->user['admin']) {
            // Check if we can return something from cache
            if (is_array($cache[$GLOBALS['BE_USER']->user['uid']]) && $cache[$GLOBALS['BE_USER']->user['uid']]['pidlist']) {
                $pidlist = $cache[$GLOBALS['BE_USER']->user['uid']]['pidlist'];
            } else {
                $mounts = $GLOBALS['BE_USER']->returnWebmounts();
                $qG = new QueryGenerator();
                $pidlist = '';
                foreach ($mounts as $idx => $uid) {
                    $list = $qG->getTreeList($uid, 99, 0, $GLOBALS['BE_USER']->getPagePermsClause(1));
                    $pidlist .= ($pidlist == '' ? '' : ',') . $list;
                }
                $cache[$GLOBALS['BE_USER']->user['uid']]['pidlist'] = $pidlist;
                $GLOBALS['BE_USER']->setAndSaveSessionData('cal_itemsProcFunc', $cache);
            }
        }

        // Orders items from the current page first
        if ($pid) {
            $orderBy = $table . '.pid=' . $pid . ' DESC' . ($orderBy ? ',' . $orderBy : '');
        }

        if ($pidlist != '') {
            $limitViewOnlyToPidsWhere .= ' AND ' . $table . '.pid IN (' . $pidlist . ')';
        }

        /* If a languageField is available for the table, use it */
        if (array_key_exists('languageField', (array)$GLOBALS['TCA'][$table]['ctrl'])) {
            $languageField = $GLOBALS['TCA'][$table]['ctrl']['languageField'];
            $languageWhere = ' AND ' . $table . '.' . $languageField . ' IN (-1,0)';
        }

        /* Construct the query */
        $where = '1=1 ' . BackendUtility::BEenableFields($table) . BackendUtility::deleteClause($table) . $limitViewOnlyToPidsWhere . $accessControlWhere . $languageWhere . $where;
        $res = $GLOBALS['TYPO3_DB']->exec_selectQuery('*', $table, $where, $groupBy, $orderBy, $limit);

        return $res;
    }
}
