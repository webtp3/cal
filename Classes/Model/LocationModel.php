<?php

/*
 * This file is part of the web-tp3/cal.
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace TYPO3\CMS\Cal\Model;

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
use TYPO3\CMS\Cal\Utility\Functions;
use TYPO3\CMS\Cal\Utility\Registry;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Base model for the calendar location.
 * Provides basic model functionality that other
 * models can use or override by extending the class.
 */
abstract class LocationModel extends BaseModel
{
    /**
     * @var string
     */
    public $name = '';

    /**
     * @var string
     */
    public $description = '';

    /**
     * @var string
     */
    public $street = '';

    /**
     * @var string
     */
    public $zip = '';

    /**
     * @var string
     */
    public $city = '';

    /**
     * @var string
     */
    public $countryzone = '';

    /**
     * @var string
     */
    public $country = '';

    /**
     * @var string
     */
    public $phone = '';

    /**
     * @var string
     */
    public $fax = '';

    /**
     * @var string
     */
    public $mobilephone = '';

    /**
     * @var string
     */
    public $email = '';

    /**
     * @var string
     */
    public $link = '';

    /**
     * @var string
     */
    public $longitude = '';

    /**
     * @var string
     */
    public $latitude = '';

    /**
     * @var array
     */
    public $eventLinks = [];

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param $t
     */
    public function setName($t)
    {
        $this->name = $t;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param $d
     */
    public function setDescription($d)
    {
        $this->description = $d;
    }

    /**
     * @return string
     */
    public function getStreet(): string
    {
        return $this->street;
    }

    /**
     * @param $t
     */
    public function setStreet($t)
    {
        $this->street = $t;
    }

    /**
     * @return string
     */
    public function getZip(): string
    {
        return $this->zip;
    }

    /**
     * @param $t
     */
    public function setZip($t)
    {
        $this->zip = $t;
    }

    /**
     * @return string
     */
    public function getCity(): string
    {
        return $this->city;
    }

    /**
     * @param $t
     */
    public function setCity($t)
    {
        $this->city = $t;
    }

    /**
     * @return string
     */
    public function getCountryZone(): string
    {
        return $this->countryzone;
    }

    /**
     * @param $t
     */
    public function setCountryZone($t)
    {
        if ($t) {
            $this->countryzone = $t;
        }
    }

    /**
     * @return string
     */
    public function getCountry(): string
    {
        return $this->country;
    }

    /**
     * @param $t
     */
    public function setCountry($t)
    {
        if ($t) {
            $this->country = $t;
        }
    }

    /**
     * @return string
     */
    public function getPhone(): string
    {
        return $this->phone;
    }

    /**
     * @param $t
     */
    public function setPhone($t)
    {
        $this->phone = $t;
    }

    /**
     * @return string
     */
    public function getMobilephone(): string
    {
        return $this->mobilephone;
    }

    /**
     * @param $t
     */
    public function setMobilephone($t)
    {
        $this->mobilephone = $t;
    }

    /**
     * @return string
     */
    public function getFax(): string
    {
        return $this->fax;
    }

    /**
     * @param $t
     */
    public function setFax($t)
    {
        $this->fax = $t;
    }

    /**
     * @return string
     */
    public function getLink(): string
    {
        return $this->link;
    }

    /**
     * @param $t
     */
    public function setLink($t)
    {
        $this->link = $t;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @param $t
     */
    public function setEmail($t)
    {
        $this->email = $t;
    }

    /**
     * @return string
     */
    public function getLongitude(): string
    {
        return $this->longitude;
    }

    /**
     * @return string
     */
    public function getLatitude(): string
    {
        return $this->latitude;
    }

    /**
     * @param $l
     */
    public function setLongitude($l)
    {
        $this->longitude = $l;
    }

    /**
     * @param $l
     */
    public function setLatitude($l)
    {
        $this->latitude = $l;
    }

    /**
     * @param $row
     */
    public function createLocation($row)
    {
        $this->row = $row;
        $this->setUid($row['uid']);
        $this->setName($row['name']);
        $this->setDescription($row['description']);
        $this->setStreet($row['street']);
        $this->setZip($row['zip']);
        $this->setCity($row['city']);
        $this->setCountryZone($row['country_zone']);
        $this->setCountry($row['country']);
        $this->setPhone($row['phone']);
        $this->setEmail($row['email']);
        $this->setImage(GeneralUtility::trimExplode(',', $row['image'], 1));
        $this->setLink($row['link']);
        $this->setLatitude($row['latitude']);
        $this->setLongitude($row['longitude']);
    }

    /**
     * @param $template
     * @param $sims
     * @param $rems
     * @param $wrapped
     * @param $view
     * @deprecated
     */
    public function getMapMarker(& $template, & $sims, & $rems, & $wrapped, $view)
    {
        $sims['###MAP###'] = 'The mapping abilities are currently unavailable.';
    }

    /**
     * @param $template
     * @param $sims
     * @param $rems
     * @param $wrapped
     * @param $view
     */
    public function getCountryMarker(& $template, & $sims, & $rems, & $wrapped, $view)
    {
        $this->initLocalCObject();
        if (ExtensionManagementUtility::isLoaded('static_info_tables')) {
            $staticInfo = Functions::makeInstance(PiBaseApi::class);
            $staticInfo->init();
            $current = LocalizationUtility::translate(
                ['uid' => $this->getCountry()],
                'static_countries',
                false
            );
            $this->local_cObj->setCurrentVal($current);
            $sims['###COUNTRY###'] = $this->local_cObj->cObjGetSingle(
                $this->conf['view.'][$this->conf['view'] . '.'][$this->getObjectType() . '.']['countryStaticInfo'],
                $this->conf['view.'][$this->conf['view'] . '.'][$this->getObjectType() . '.']['countryStaticInfo.']
            );
        } else {
            $current = $this->getCountry();
            $this->local_cObj->setCurrentVal($current);
            $sims['###COUNTRY###'] = $this->local_cObj->cObjGetSingle(
                $this->conf['view.'][$this->conf['view'] . '.'][$this->getObjectType() . '.']['country'],
                $this->conf['view.'][$this->conf['view'] . '.'][$this->getObjectType() . '.']['country.']
            );
        }
    }

    /**
     * @param $template
     * @param $sims
     * @param $rems
     * @param $wrapped
     * @param $view
     */
    public function getCountryZoneMarker(& $template, & $sims, & $rems, & $wrapped, $view)
    {
        $this->initLocalCObject();
        if (ExtensionManagementUtility::isLoaded('static_info_tables')) {
            $staticInfo = Functions::makeInstance(PiBaseApi::class);
            $staticInfo->init();
            $current = LocalizationUtility::translate(
                ['uid' => $this->getCountryZone()],
                'static_country_zones',
                false
            );
            $this->local_cObj->setCurrentVal($current);
            $sims['###COUNTRYZONE###'] = $this->local_cObj->cObjGetSingle(
                $this->conf['view.'][$this->conf['view'] . '.'][$this->getObjectType() . '.']['countryzoneStaticInfo'],
                $this->conf['view.'][$this->conf['view'] . '.'][$this->getObjectType() . '.']['countryzoneStaticInfo.']
            );
        } else {
            $current = $this->getCountryZone();
            $this->local_cObj->setCurrentVal($current);
            $sims['###COUNTRYZONE###'] = $this->local_cObj->cObjGetSingle(
                $this->conf['view.'][$this->conf['view'] . '.'][$this->getObjectType() . '.']['countryzone'],
                $this->conf['view.'][$this->conf['view'] . '.'][$this->getObjectType() . '.']['countryzone.']
            );
        }
    }

    /**
     * @param $template
     * @param $sims
     * @param $rems
     * @param $wrapped
     * @param $view
     */
    public function getLocationLinkMarker(& $template, & $sims, & $rems, & $wrapped, $view)
    {
        $wrapped['###LOCATION_LINK###'] = explode('$5&xs2', $this->getLinkToLocation('$5&xs2'));
    }

    /**
     * @param $template
     * @param $sims
     * @param $rems
     * @param $wrapped
     * @param $view
     */
    public function getOrganizerLinkMarker(& $template, & $sims, & $rems, & $wrapped, $view)
    {
        $wrapped['###ORGANIZER_LINK###'] = explode('$5&xs2', $this->getLinkToOrganizer('$5&xs2'));
    }

    /**
     * @param $template
     * @param $sims
     * @param $rems
     * @param $wrapped
     * @param $view
     */
    public function getEditLinkMarker(& $template, & $sims, & $rems, & $wrapped, $view)
    {
        $sims['###EDIT_LINK###'] = '';
        if ($this->isUserAllowedToEdit()) {
            $linkConf = $this->getValuesAsArray();
            if ($this->conf['view.']['enableAjax']) {
                $temp = sprintf(
                    $this->conf['view.'][$this->conf['view'] . '.'][$this->getObjectType() . '.']['editLinkOnClick'],
                    $this->getUid(),
                    $this->getType()
                );
                $linkConf['link_ATagParams'] = ' onclick="' . $temp . '"';
            }
            $this->initLocalCObject($linkConf);
            $this->controller->getParametersForTyposcriptLink(
                $this->local_cObj->data,
                [
                    'view' => 'edit_' . $this->getObjectType(),
                    'type' => $this->getType(),
                    'uid' => $this->getUid()
                ],
                $this->conf['cache'],
                $this->conf['clear_anyway'],
                $this->conf['view.'][$this->getObjectType() . '.']['edit' . ucwords($this->getObjectType()) . 'ViewPid']
            );
            $this->local_cObj->setCurrentVal($this->conf['view.'][$this->conf['view'] . '.'][$this->getObjectType() . '.']['editIcon']); // controller->pi_getLL('l_edit_'.$this->getObjectType())
            $sims['###EDIT_LINK###'] = $this->local_cObj->cObjGetSingle(
                $this->conf['view.'][$this->conf['view'] . '.'][$this->getObjectType() . '.']['editLink'],
                $this->conf['view.'][$this->conf['view'] . '.'][$this->getObjectType() . '.']['editLink.']
            );
        }
        if ($this->isUserAllowedToDelete()) {
            $linkConf = $this->getValuesAsArray();
            if ($this->conf['view.']['enableAjax']) {
                $temp = sprintf(
                    $this->conf['view.'][$this->conf['view'] . '.'][$this->getObjectType() . '.']['deleteLinkOnClick'],
                    $this->getUid(),
                    $this->getType()
                );
                $linkConf['link_ATagParams'] = ' onclick="' . $temp . '"';
            }
            $this->controller->getParametersForTyposcriptLink(
                $this->local_cObj->data,
                [
                    'view' => 'delete_' . $this->getObjectType(),
                    'type' => $this->getType(),
                    'uid' => $this->getUid()
                ],
                $this->conf['cache'],
                $this->conf['clear_anyway'],
                $this->conf['view.'][$this->getObjectType() . '.']['delete' . ucwords($this->getObjectType()) . 'ViewPid']
            );
            $this->initLocalCObject($linkConf);
            $this->local_cObj->setCurrentVal($this->conf['view.'][$this->conf['view'] . '.'][$this->getObjectType() . '.']['deleteIcon']); // controller->pi_getLL('l_delete_'.$this->getObjectType())
            $sims['###EDIT_LINK###'] .= $this->local_cObj->cObjGetSingle(
                $this->conf['view.'][$this->conf['view'] . '.'][$this->getObjectType() . '.']['deleteLink'],
                $this->conf['view.'][$this->conf['view'] . '.'][$this->getObjectType() . '.']['deleteLink.']
            );
        }
    }

    /**
     * @param $key
     * @param $link
     */
    public function addEventLink($key, $link)
    {
        $this->eventLinks[$key] = $link;
    }

    /**
     * @return array
     */
    public function getEventLinks(): array
    {
        return $this->eventLinks;
    }

    /**
     * @param $linktext
     * @return string
     */
    public function getLinkToOrganizer($linktext): string
    {
        return $this->getLinkToLocation($linktext);
    }

    /**
     * @param string $linktext
     * @return string
     */
    public function getLinkToLocation($linktext): string
    {
        if ($linktext === '') {
            $linktext = 'no title';
        }
        $rightsObj = &Registry::Registry('basic', 'rightscontroller');
        if ($rightsObj->isViewEnabled($this->conf['view.'][$this->getObjectType() . 'LinkTarget']) || $this->conf['view.'][$this->getObjectType() . '.'][$this->getObjectType() . 'ViewPid']) {
            return $this->controller->pi_linkTP_keepPIvars(
                $linktext,
                [
                    'view' => $this->getObjectType(),
                    'uid' => $this->getUid(),
                    'type' => $this->getType()
                ],
                $this->conf['cache'],
                $this->conf['clear_anyway'],
                $this->conf['view.'][$this->getObjectType() . '.'][$this->getObjectType() . 'ViewPid']
            );
        }
        return $linktext;
    }

    /**
     * @param $piVars
     */
    public function updateWithPIVars(&$piVars)
    {
        foreach ($piVars as $key => $value) {
            switch ($key) {
                case 'uid':
                    $this->setUid(intval($piVars['uid']));
                    unset($piVars['uid']);
                    break;
                case 'hidden':
                    $this->setHidden(intval($piVars['hidden']));
                    unset($piVars['hidden']);
                    break;
                case 'name':
                    $this->setName(strip_tags($piVars['name']));
                    unset($piVars['name']);
                    break;
                case 'description':
                    $this->setDescription(htmlspecialchars($piVars['description'], []));
                    unset($piVars['description']);
                    break;
                case 'street':
                    $this->setStreet(strip_tags($piVars['street']));
                    unset($piVars['street']);
                    break;
                case 'zip':
                    $this->setZip(strip_tags($piVars['zip']));
                    unset($piVars['zip']);
                    break;
                case 'city':
                    $this->setCity(strip_tags($piVars['city']));
                    unset($piVars['city']);
                    break;
                case 'phone':
                    $this->setPhone(strip_tags($piVars['phone']));
                    unset($piVars['phone']);
                    break;
                case 'fax':
                    $this->setFax(strip_tags($piVars['fax']));
                    unset($piVars['fax']);
                    break;
                case 'email':
                    $this->setEmail(strip_tags($piVars['email']));
                    unset($piVars['email']);
                    break;
                case 'image':
                    foreach ((array)$piVars['image'] as $image) {
                        $this->addImage(strip_tags($image));
                    }
                    unset($piVars['image']);
                    break;
                case 'country':
                    $this->setCountry(strip_tags($piVars['country']));
                    unset($piVars['country']);
                    break;
                case 'country_static_info':
                    $this->setCountry(strip_tags($piVars['country_static_info']));
                    unset($piVars['country_static_info']);
                    break;
                case 'countryzone':
                    $this->setCountryZone(strip_tags($piVars['countryzone']));
                    unset($piVars['countryzone']);
                    break;
                case 'countryzone_static_info':
                    $this->setCountryZone(strip_tags($piVars['countryzone_static_info']));
                    unset($piVars['countryzone_static_info']);
                    break;
                case 'link':
                    $this->setLink(strip_tags($piVars['link']));
                    unset($piVars['link']);
                    break;
                case 'longitude':
                    $this->setLongitude(strip_tags($piVars['longitude']));
                    unset($piVars['longitude']);
                    break;
                case 'latitude':
                    $this->setLatitude(strip_tags($piVars['latitude']));
                    unset($piVars['latitude']);
                    break;
                case 'shared':
                case 'shared_ids':
                    $this->setSharedGroups([]);
                    $this->setSharedUsers([]);
                    $values = $piVars[$key];
                    if (!is_array($piVars[$key])) {
                        $values = GeneralUtility::trimExplode(',', $piVars[$key], 1);
                    }
                    foreach ($values as $entry) {
                        preg_match('/(^[a-z])_([0-9]+)/', $entry, $idname);
                        if ($idname[1] === 'u') {
                            $this->addSharedUser($idname[2]);
                        } elseif ($idname[1] === 'g') {
                            $this->addSharedGroup($idname[2]);
                        }
                    }
                    break;
            }
        }
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return get_class($this) . ': ' . implode(', ', $this->row);
    }
}
