<?php

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
use TYPO3\CMS\Cal\Model\Location;
use TYPO3\CMS\Cal\Model\Organizer;
use TYPO3\CMS\Cal\Utility\Functions;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

/**
 * A service which renders a form to create / edit an event location / organizer.
 */
class CreateLocationOrganizerView extends FeEditingBaseView
{
    public $isLocation;

    /**
     * Draws a create location or organizer form.
     *
     * @param
     *            boolean        True if a location should be confirmed
     * @param
     *            string        Comma separated list of pids.
     * @param
     *            object        A location or organizer object to be updated
     * @return string HTML output.
     */
    public function drawCreateLocationOrOrganizer($isLocation = true, $pidList, $object = '')
    {
        $this->isLocation = $isLocation;
        if ($isLocation) {
            $this->objectString = 'location';
        } else {
            $this->objectString = 'organizer';
        }
        if (is_object($object)) {
            $this->conf['view'] = 'edit_' . $this->objectString;
        } else {
            $this->conf['view'] = 'create_' . $this->objectString;
            unset($this->controller->piVars['uid']);
        }
        $requiredFieldSims = [];
        $allRequiredFieldsAreFilled = $this->checkRequiredFields($requiredFieldsSims);

        if ($allRequiredFieldsAreFilled) {
            $this->conf['lastview'] = $this->controller->extendLastView();

            $this->conf['view'] = 'confirm_' . $this->objectString;
            if ($isLocation) {
                return $this->controller->confirmLocation();
            }
            return $this->controller->confirmOrganizer();
        }

        // Needed for translation options:
        $this->serviceName = 'cal_' . $this->objectString . '_model';
        $this->table = 'tx_cal_' . $this->objectString;

        $page = Functions::getContent($this->conf['view.']['create_location.']['template']);
        if ($page == '') {
            return '<h3>calendar: no create location template file found:</h3>' . $this->conf['view.']['create_location.']['template'];
        }

        if (is_object($object) && !$object->isUserAllowedToEdit()) {
            return $this->controller->pi_getLL('l_not_allowed_edit') . $this->objectString;
        }
        if (!is_object($object) && !$this->rightsObj->isAllowedTo('create', $this->objectString, '')) {
            return $this->controller->pi_getLL('l_not_allowed_create') . $this->objectString;
        }

        // If an object has been passed on the form is a edit form
        if (is_object($object) && $object->isUserAllowedToEdit()) {
            $this->isEditMode = true;
            $this->object = $object;
        } else {
            if ($isLocation) {
                $this->object = new Location(null, '');
            } else {
                $this->object = new Organizer(null, '');
            }
            $allValues = array_merge($this->getDefaultValues(), $this->controller->piVars);
            $this->object->updateWithPIVars($allValues);
        }

        $sims = [];
        $rems = [];
        $wrapped = [];

        $sims['###TYPE###'] = $this->object->getType();
        $this->getTemplateSubpartMarker($page, $sims, $rems, $wrapped, $this->conf['view']);

        $page = Functions::substituteMarkerArrayNotCached($page, [], $rems, $wrapped);
        $page = Functions::substituteMarkerArrayNotCached($page, $sims, [], []);

        $sims = [];
        $rems = [];
        $wrapped = [];

        $sims['###L_CREATE_LOCATION###'] = $this->controller->pi_getLL('l_' . $this->conf['view']);
        $this->getTemplateSingleMarker($page, $sims, $rems, $this->conf['view']);
        $sims['###ACTION_URL###'] = htmlspecialchars($this->controller->pi_linkTP_keepPIvars_url([
            'view' => $this->conf['view'],
            'formCheck' => '1'
        ]));
        $page = Functions::substituteMarkerArrayNotCached($page, [], $rems, $wrapped);
        $page = Functions::substituteMarkerArrayNotCached($page, $sims, [], []);
        return Functions::substituteMarkerArrayNotCached($page, $requiredFieldsSims, [], []);
    }

    /**
     * @param $template
     * @param $sims
     * @param $rems
     * @param $view
     */
    public function getCountryMarker(& $template, & $sims, & $rems, $view)
    {
        // Initialise static info library
        $sims['###COUNTRY###'] = '';
        if ($this->isAllowed('country')) {
            if (ExtensionManagementUtility::isLoaded('static_info_tables')) {
                $staticInfo = Functions::makeInstance(PiBaseApi::class);
                $staticInfo->init();
                $sims['###COUNTRY###'] = $this->applyStdWrap($staticInfo->buildStaticInfoSelector(
                    'COUNTRIES',
                    'tx_cal_controller[country]',
                    '',
                    $this->object->getCountry()
                ), 'country_static_info_stdWrap');
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
     * @param $view
     */
    public function getCountryzoneMarker(& $template, & $sims, & $rems, $view)
    {
        // Initialise static info library
        $sims['###COUNTRYZONE###'] = '';
        if ($this->isAllowed('countryzone')) {
            if (ExtensionManagementUtility::isLoaded('static_info_tables')) {
                $staticInfo = Functions::makeInstance(PiBaseApi::class);
                $staticInfo->init();
                $sims['###COUNTRYZONE###'] = $this->applyStdWrap(
                    $staticInfo->buildStaticInfoSelector(
                        'SUBDIVISIONS',
                        'tx_cal_controller[countryzone]',
                        '',
                        $this->object->getCountryZone(),
                        $this->object->getCountry()
                    ),
                    'countryzone_static_info_stdWrap'
                );
            } else {
                $sims['###COUNTRYZONE###'] = $this->applyStdWrap(
                    $this->object->getCountryZone(),
                    'countryzone_stdWrap'
                );
                $sims['###COUNTRYZONE_VALUE###'] = $this->object->getCountryZone();
            }
        }
    }
}
