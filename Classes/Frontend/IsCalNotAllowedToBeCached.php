<?php

namespace TYPO3\CMS\Cal\Frontend;

use TYPO3\CMS\Core\Utility\GeneralUtility;

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
 * userFunc conditional to determine if we're in a frontend editing view based
 * on the POST variables.
 * Used to dynamically switch to a USER_INT for
 * frontend editing.
 *
 * @return bool if we're in a frontend editing view.
 * @todo Figure out a better way to define the frontend editing views.
 *       Current method isn't very maintainable.
 */
function IsCalNotAllowedToBeCached()
{
    $postVars = GeneralUtility::_GP('tx_cal_controller');
    $view = $postVars['view'];

    /* FRONTEND EDITING */
    $frontendEditingViews = [
        'admin',

        /* Event */
        'create_event',
        'edit_event',
        'confirm_event',
        'save_event',
        'save_exception_event',
        'delete_event',
        'delete_event_confirm',
        'remove_event',

        /* Calendar */
        'create_calendar',
        'edit_calendar',
        'confirm_calendar',
        'save_calendar',
        'delete_calendar',
        'remove_calendar',

        /* Category */
        'create_category',
        'edit_category',
        'confirm_category',
        'save_category',
        'delete_category',
        'remove_category',

        /* Location */
        'create_location',
        'edit_location',
        'confirm_location',
        'save_location',
        'delete_location',
        'remove_location',

        /* Organizer */
        'create_organizer',
        'edit_organizer',
        'confirm_organizer',
        'save_organizer',
        'delete_organizer',
        'remove_organizer'
    ];

    if (in_array($view, $frontendEditingViews, true)) {
        return true;
    }

    /* SEARCH */
    $searchViews = [
        'search_all',
        'search_event',
        'search_organizer',
        'search_location'
    ];
    if (in_array($view, $searchViews, true)) {
        return true;
    }

    if ($postVars['submit'] || $postVars['query'] || $postVars['category'] || $postVars['calendar']) {
        return true;
    }
    return false;
}
