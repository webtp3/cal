<?php

namespace TYPO3\CMS\Cal\Service;

use JBartels\WecMap\Utility\Cache;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

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
class NearbyEventService extends EventService
{
    public function __construct()
    {
        parent::__construct();

        // Lets see if the user is logged in
        if ($this->rightsObj->isLoggedIn() && !$this->rightsObj->isCalAdmin() && ExtensionManagementUtility::isLoaded('wec_map') && $this->conf['view.']['calendar.']['nearbyDistance'] > 0 && class_exists('\JBartels\WecMap\Utility\Cache')) {
            $user = $GLOBALS['TSFE']->fe_user->user;

            /* Geocode the address */
            $latlong = Cache::lookup(
                $user['street'],
                $user['city'],
                $user['state'],
                $user['zip'],
                $user['country']
            );
            if (isset($latlong['long']) && isset($latlong['lat'])) {
                $this->internalAdditionTable = ',' . $this->conf['view.']['calendar.']['nearbyAdditionalTable'];
                $this->internalAdditionWhere = ' ' . str_replace([
                        '###LONGITUDE###',
                        '###LATITUDE###',
                        '###DISTANCE###'
                    ], [
                        $latlong['long'],
                        $latlong['lat'],
                        $this->conf['view.']['calendar.']['nearbyDistance']
                    ], $this->conf['view.']['calendar.']['nearbyAdditionalWhere']);
            } else {
                $this->internalAdditionWhere = ' AND 1=2';
            }
        } else {
            // not logged in -> we can't localize
            $this->internalAdditionWhere = ' AND 1=2';
        }
    }
}
