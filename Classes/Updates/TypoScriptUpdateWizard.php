<?php

/*
 * This file is part of the web-tp3/cal.
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace TYPO3\CMS\Cal\Updates;

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
 * Update wizard after move of typoscript templates from EXT:cal/static/ to EXT:cal/Configuration/TypoScript/
 * @deprecated since ext:cal v2, will be removed in ext:cal v3
 */
class TypoScriptUpdateWizard extends AbstractUpdate
{

    /**
     * @var string
     */
    protected $title = 'Migrate static_include_file relations of the cal extension';

    /**
     * Returns the migration description
     * @return string The description
     */
    protected function getMigrationDescription()
    {
        return 'Found old references to EXT:cal/static/. This wizard will replace EXT:cal/static/ references to the new EXT:cal/Configuration/TypoScript/ folder.';
    }

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
        $sql = $GLOBALS['TYPO3_DB']->SELECTquery(
            'COUNT(*)',
            'sys_template',
            'include_static_file like \'%XT:cal/static/%\''
        );
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
     * Performs the database update.
     *
     * @param array &$dbQueries Queries done in this update
     * @param mixed &$customMessages Custom messages
     * @return bool TRUE on success, FALSE on error
     */
    public function performUpdate(array &$dbQueries, &$customMessages)
    {
        trigger_error('As \TYPO3\CMS\Install\Updates\AbstractUpdate will be removed in TYPO3 v10.0, this wizard will go as well with v3.0 of ext:cal. Affected class: ' . get_class($this), E_USER_DEPRECATED);

        $sql = 'UPDATE sys_template	SET include_static_file = replace(include_static_file,\'XT:cal/static/\',\'XT:cal/Configuration/TypoScript/\') WHERE include_static_file like \'%XT:cal/static/%\'';
        $GLOBALS['TYPO3_DB']->sql_query($sql);
        return true;
    }
}
