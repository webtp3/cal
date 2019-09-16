<?php

/*
 * This file is part of the web-tp3/cal.
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace TYPO3\CMS\Cal\View;

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
use SJBR\StaticInfoTables\PiBaseApi;
use SJBR\StaticInfoTables\Utility\LocalizationUtility;
use TYPO3\CMS\Cal\Model\Location;
use TYPO3\CMS\Cal\Model\Organizer;
use TYPO3\CMS\Cal\Utility\Functions;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * A service which renders a form to confirm the location/organizer create/edit.
 */
class ConfirmLocationOrganizerView extends FeEditingBaseView
{
    public $isLocation = true;

    /**
     * Draws a confirm form for a location or an organizer.
     *
     * @param bool True if a location should be confirmed
     * @param object        The cObject of the mother-class.
     * @param object        The rights object.
     * @return string HTML output.
     */
    public function drawConfirmLocationOrOrganizer($isLocation = true): string
    {
        $this->isLocation = $isLocation;
        $this->isConfirm = true;
        if ($isLocation) {
            $this->objectString = 'location';
        } else {
            $this->objectString = 'organizer';
        }

        $page = Functions::getContent($this->conf['view.']['confirm_location.']['template']);
        if ($page === '') {
            return '<h3>calendar: no confirm ' . $this->objectString . ' template file found:</h3>' . $this->conf['view.']['confirm_location.']['template'];
        }

        if ($isLocation) {
            $this->object = new Location(null, '');
        } else {
            $this->object = new Organizer(null, '');
        }
        $this->object->updateWithPIVars($this->controller->piVars);

        $lastViewParams = $this->controller->shortenLastViewAndGetTargetViewParameters();

        if (strpos($lastViewParams['view'], 'edit') === 0) {
            $this->isEditMode = true;
        }

        $rems = [];
        $sims = [];
        $wrapped = [];
        $sims['###UID###'] = $this->conf['uid'];
        $sims['###TYPE###'] = $this->conf['type'];
        $sims['###VIEW###'] = 'save_' . $this->objectString;
        $sims['###LASTVIEW###'] = $this->controller->extendLastView();
        $sims['###L_CONFIRM_LOCATION###'] = $this->controller->pi_getLL('l_confirm_' . $this->objectString);
        $sims['###L_SAVE###'] = $this->controller->pi_getLL('l_save');
        $sims['###L_CANCEL###'] = $this->controller->pi_getLL('l_cancel');
        $sims['###ACTION_URL###'] = htmlspecialchars($this->controller->pi_linkTP_keepPIvars_url([
            'view' => 'save_' . $this->objectString,
            'category' => null
        ]));

        $this->getTemplateSubpartMarker($page, $sims, $rems, $wrapped);
        $page = Functions::substituteMarkerArrayNotCached($page, [], $rems, $wrapped);
        $page = Functions::substituteMarkerArrayNotCached($page, $sims, [], []);
        $sims = [];
        $rems = [];
        $wrapped = [];
        $this->getTemplateSingleMarker($page, $sims, $rems, $wrapped);
        $page = Functions::substituteMarkerArrayNotCached($page, [], $rems, $wrapped);
        $page = Functions::substituteMarkerArrayNotCached($page, $sims, [], []);
        return Functions::substituteMarkerArrayNotCached($page, $sims, [], []);
    }

    /**
     * @param $template
     * @param $sims
     * @param $rems
     */
    public function getCountryMarker(& $template, & $sims, & $rems)
    {
        // Initialise static info library
        $sims['###COUNTRY###'] = '';
        $sims['###COUNTRY_VALUE###'] = '';
        if ($this->isAllowed('country')) {
            if (ExtensionManagementUtility::isLoaded('static_info_tables')) {
                $staticInfo = GeneralUtility::makeInstance(PiBaseApi::class);
                $staticInfo->init();
                $current = LocalizationUtility::translate(
                    ['uid' => $this->object->getCountry()],
                    'static_countries',
                    false
                );
                $sims['###COUNTRY###'] = $this->applyStdWrap($current, 'country_static_info_stdWrap');
                $sims['###COUNTRY_VALUE###'] = strip_tags($this->object->getCountry());
            } else {
                $sims['###COUNTRY###'] = $this->applyStdWrap($this->object->getCountry(), 'country_stdWrap');
                $sims['###COUNTRY_VALUE###'] = $this->object->getCountry();
            }
        }
    }

    /**
     * @param $template
     * @param $sims
     * @param $rems
     */
    public function getCountryzoneMarker(& $template, & $sims, & $rems)
    {
        // Initialise static info library
        $sims['###COUNTRYZONE###'] = '';
        $sims['###COUNTRYZONE_VALUE###'] = '';
        if ($this->isAllowed('countryzone')) {
            if (ExtensionManagementUtility::isLoaded('static_info_tables')) {
                $staticInfo = GeneralUtility::makeInstance(PiBaseApi::class);
                $staticInfo->init();
                $current = LocalizationUtility::translate(
                    ['uid' => $this->object->getCountryzone()],
                    'static_country_zones',
                    false
                );
                $sims['###COUNTRYZONE###'] = $this->applyStdWrap($current, 'countryzone_static_info_stdWrap');
                $sims['###COUNTRYZONE_VALUE###'] = $this->object->getCountryZone();
            } else {
                $sims['###COUNTRYZONE###'] = $this->applyStdWrap(
                    $this->object->getCountryZone(),
                    'countryzone_stdWrap'
                );
                $sims['###COUNTRYZONE_VALUE###'] = $this->object->getCountryZone();
            }
        }
    }

    /**
     * @param $template
     * @param $sims
     * @param $rems
     */
    public function getSharedMarker(& $template, & $sims, & $rems)
    {
        $sims['###SHARED###'] = '';
        if (is_array($this->controller->piVars['shared']) && $this->isAllowed('shared')) {
            $shareddisplaylist = [];
            $sharedids = [];
            foreach ($this->controller->piVars['shared'] as $value) {
                preg_match('/(^[a-z])_([0-9]+)_(.*)/', $value, $idname);
                if ($idname[1] === 'u' || $idname[1] === 'g') {
                    $sharedids[] = $idname[1] . '_' . $idname[2];
                    $shareddisplaylist[] = $idname[3];
                }
            }
            $sims['###SHARED###'] = $this->applyStdWrap(implode(',', $shareddisplaylist), 'shared_stdWrap');
            $sims['###SHARED_VALUE###'] = htmlspecialchars(implode(',', $sharedids));
        }
    }
}
