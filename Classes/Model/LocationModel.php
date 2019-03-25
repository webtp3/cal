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
use TYPO3\CMS\Cal\Utility\Registry;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Base model for the calendar location.
 * Provides basic model functionality that other
 * models can use or override by extending the class.
 * @deprecated since ext:cal v2, will be removed in ext:cal v3
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
     * @deprecated since ext:cal v2, will be removed in ext:cal v3
     */
    public function getName(): string
    {
        trigger_error('Deprecated since ext:cal v2, will be removed in ext:cal v3.', E_USER_DEPRECATED);

        return $this->name;
    }

    /**
     * @param $t
     * @deprecated since ext:cal v2, will be removed in ext:cal v3
     */
    public function setName($t)
    {
        trigger_error('Deprecated since ext:cal v2, will be removed in ext:cal v3.', E_USER_DEPRECATED);

        $this->name = $t;
    }

    /**
     * @return string
     * @deprecated since ext:cal v2, will be removed in ext:cal v3
     */
    public function getDescription(): string
    {
        trigger_error('Deprecated since ext:cal v2, will be removed in ext:cal v3.', E_USER_DEPRECATED);

        return $this->description;
    }

    /**
     * @param $d
     * @deprecated since ext:cal v2, will be removed in ext:cal v3
     */
    public function setDescription($d)
    {
        trigger_error('Deprecated since ext:cal v2, will be removed in ext:cal v3.', E_USER_DEPRECATED);

        $this->description = $d;
    }

    /**
     * @return string
     * @deprecated since ext:cal v2, will be removed in ext:cal v3
     */
    public function getStreet(): string
    {
        trigger_error('Deprecated since ext:cal v2, will be removed in ext:cal v3.', E_USER_DEPRECATED);

        return $this->street;
    }

    /**
     * @param $t
     * @deprecated since ext:cal v2, will be removed in ext:cal v3
     */
    public function setStreet($t)
    {
        trigger_error('Deprecated since ext:cal v2, will be removed in ext:cal v3.', E_USER_DEPRECATED);

        $this->street = $t;
    }

    /**
     * @return string
     * @deprecated since ext:cal v2, will be removed in ext:cal v3
     */
    public function getZip(): string
    {
        trigger_error('Deprecated since ext:cal v2, will be removed in ext:cal v3.', E_USER_DEPRECATED);

        return $this->zip;
    }

    /**
     * @param $t
     * @deprecated since ext:cal v2, will be removed in ext:cal v3
     */
    public function setZip($t)
    {
        trigger_error('Deprecated since ext:cal v2, will be removed in ext:cal v3.', E_USER_DEPRECATED);

        $this->zip = $t;
    }

    /**
     * @return string
     * @deprecated since ext:cal v2, will be removed in ext:cal v3
     */
    public function getCity(): string
    {
        trigger_error('Deprecated since ext:cal v2, will be removed in ext:cal v3.', E_USER_DEPRECATED);

        return $this->city;
    }

    /**
     * @param $t
     * @deprecated since ext:cal v2, will be removed in ext:cal v3
     */
    public function setCity($t)
    {
        trigger_error('Deprecated since ext:cal v2, will be removed in ext:cal v3.', E_USER_DEPRECATED);

        $this->city = $t;
    }

    /**
     * @return string
     * @deprecated since ext:cal v2, will be removed in ext:cal v3
     */
    public function getCountryZone(): string
    {
        trigger_error('Deprecated since ext:cal v2, will be removed in ext:cal v3.', E_USER_DEPRECATED);

        return $this->countryzone;
    }

    /**
     * @param $t
     * @deprecated since ext:cal v2, will be removed in ext:cal v3
     */
    public function setCountryZone($t)
    {
        trigger_error('Deprecated since ext:cal v2, will be removed in ext:cal v3.', E_USER_DEPRECATED);

        if ($t) {
            $this->countryzone = $t;
        }
    }

    /**
     * @return string
     * @deprecated since ext:cal v2, will be removed in ext:cal v3
     */
    public function getCountry(): string
    {
        trigger_error('Deprecated since ext:cal v2, will be removed in ext:cal v3.', E_USER_DEPRECATED);

        return $this->country;
    }

    /**
     * @param $t
     * @deprecated since ext:cal v2, will be removed in ext:cal v3
     */
    public function setCountry($t)
    {
        trigger_error('Deprecated since ext:cal v2, will be removed in ext:cal v3.', E_USER_DEPRECATED);

        if ($t) {
            $this->country = $t;
        }
    }

    /**
     * @return string
     * @deprecated since ext:cal v2, will be removed in ext:cal v3
     */
    public function getPhone(): string
    {
        trigger_error('Deprecated since ext:cal v2, will be removed in ext:cal v3.', E_USER_DEPRECATED);

        return $this->phone;
    }

    /**
     * @param $t
     * @deprecated since ext:cal v2, will be removed in ext:cal v3
     */
    public function setPhone($t)
    {
        trigger_error('Deprecated since ext:cal v2, will be removed in ext:cal v3.', E_USER_DEPRECATED);

        $this->phone = $t;
    }

    /**
     * @return string
     * @deprecated since ext:cal v2, will be removed in ext:cal v3
     */
    public function getMobilephone(): string
    {
        trigger_error('Deprecated since ext:cal v2, will be removed in ext:cal v3.', E_USER_DEPRECATED);

        return $this->mobilephone;
    }

    /**
     * @param $t
     * @deprecated since ext:cal v2, will be removed in ext:cal v3
     */
    public function setMobilephone($t)
    {
        trigger_error('Deprecated since ext:cal v2, will be removed in ext:cal v3.', E_USER_DEPRECATED);

        $this->mobilephone = $t;
    }

    /**
     * @return string
     * @deprecated since ext:cal v2, will be removed in ext:cal v3
     */
    public function getFax(): string
    {
        trigger_error('Deprecated since ext:cal v2, will be removed in ext:cal v3.', E_USER_DEPRECATED);

        return $this->fax;
    }

    /**
     * @param $t
     * @deprecated since ext:cal v2, will be removed in ext:cal v3
     */
    public function setFax($t)
    {
        trigger_error('Deprecated since ext:cal v2, will be removed in ext:cal v3.', E_USER_DEPRECATED);

        $this->fax = $t;
    }

    /**
     * @return string
     * @deprecated since ext:cal v2, will be removed in ext:cal v3
     */
    public function getLink(): string
    {
        trigger_error('Deprecated since ext:cal v2, will be removed in ext:cal v3.', E_USER_DEPRECATED);

        return $this->link;
    }

    /**
     * @param $t
     * @deprecated since ext:cal v2, will be removed in ext:cal v3
     */
    public function setLink($t)
    {
        trigger_error('Deprecated since ext:cal v2, will be removed in ext:cal v3.', E_USER_DEPRECATED);

        $this->link = $t;
    }

    /**
     * @return string
     * @deprecated since ext:cal v2, will be removed in ext:cal v3
     */
    public function getEmail(): string
    {
        trigger_error('Deprecated since ext:cal v2, will be removed in ext:cal v3.', E_USER_DEPRECATED);

        return $this->email;
    }

    /**
     * @param $t
     * @deprecated since ext:cal v2, will be removed in ext:cal v3
     */
    public function setEmail($t)
    {
        trigger_error('Deprecated since ext:cal v2, will be removed in ext:cal v3.', E_USER_DEPRECATED);

        $this->email = $t;
    }

    /**
     * @return string
     * @deprecated since ext:cal v2, will be removed in ext:cal v3
     */
    public function getLongitude(): string
    {
        trigger_error('Deprecated since ext:cal v2, will be removed in ext:cal v3.', E_USER_DEPRECATED);

        return $this->longitude;
    }

    /**
     * @return string
     * @deprecated since ext:cal v2, will be removed in ext:cal v3
     */
    public function getLatitude(): string
    {
        trigger_error('Deprecated since ext:cal v2, will be removed in ext:cal v3.', E_USER_DEPRECATED);

        return $this->latitude;
    }

    /**
     * @param $l
     * @deprecated since ext:cal v2, will be removed in ext:cal v3
     */
    public function setLongitude($l)
    {
        trigger_error('Deprecated since ext:cal v2, will be removed in ext:cal v3.', E_USER_DEPRECATED);

        $this->longitude = $l;
    }

    /**
     * @param $l
     * @deprecated since ext:cal v2, will be removed in ext:cal v3
     */
    public function setLatitude($l)
    {
        trigger_error('Deprecated since ext:cal v2, will be removed in ext:cal v3.', E_USER_DEPRECATED);

        $this->latitude = $l;
    }

    /**
     * @param $row
     * @deprecated since ext:cal v2, will be removed in ext:cal v3
     */
    public function createLocation($row)
    {
        trigger_error('Deprecated since ext:cal v2, will be removed in ext:cal v3.', E_USER_DEPRECATED);

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
     * @deprecated since ext:cal v2, will be removed in ext:cal v3
     */
    public function getMapMarker(& $template, & $sims, & $rems, & $wrapped, $view)
    {
        trigger_error('Deprecated since ext:cal v2, will be removed in ext:cal v3.', E_USER_DEPRECATED);

        $sims['###MAP###'] = 'The mapping abilities are currently unavailable.';
    }

    /**
     * @param $template
     * @param $sims
     * @param $rems
     * @param $wrapped
     * @param $view
     * @deprecated since ext:cal v2, will be removed in ext:cal v3
     */
    public function getCountryMarker(& $template, & $sims, & $rems, & $wrapped, $view)
    {
        trigger_error('Deprecated since ext:cal v2, will be removed in ext:cal v3.', E_USER_DEPRECATED);

        $this->initLocalCObject();
        if (ExtensionManagementUtility::isLoaded('static_info_tables')) {
            $staticInfo = GeneralUtility::makeInstance(PiBaseApi::class);
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
     * @deprecated since ext:cal v2, will be removed in ext:cal v3
     */
    public function getCountryZoneMarker(& $template, & $sims, & $rems, & $wrapped, $view)
    {
        trigger_error('Deprecated since ext:cal v2, will be removed in ext:cal v3.', E_USER_DEPRECATED);

        $this->initLocalCObject();
        if (ExtensionManagementUtility::isLoaded('static_info_tables')) {
            $staticInfo = GeneralUtility::makeInstance(PiBaseApi::class);
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
     * @deprecated since ext:cal v2, will be removed in ext:cal v3
     */
    public function getLocationLinkMarker(& $template, & $sims, & $rems, & $wrapped, $view)
    {
        trigger_error('Deprecated since ext:cal v2, will be removed in ext:cal v3.', E_USER_DEPRECATED);

        $wrapped['###LOCATION_LINK###'] = explode('$5&xs2', $this->getLinkToLocation('$5&xs2'));
    }

    /**
     * @param $template
     * @param $sims
     * @param $rems
     * @param $wrapped
     * @param $view
     * @deprecated since ext:cal v2, will be removed in ext:cal v3
     */
    public function getOrganizerLinkMarker(& $template, & $sims, & $rems, & $wrapped, $view)
    {
        trigger_error('Deprecated since ext:cal v2, will be removed in ext:cal v3.', E_USER_DEPRECATED);

        $wrapped['###ORGANIZER_LINK###'] = explode('$5&xs2', $this->getLinkToOrganizer('$5&xs2'));
    }

    /**
     * @param $template
     * @param $sims
     * @param $rems
     * @param $wrapped
     * @param $view
     * @deprecated since ext:cal v2, will be removed in ext:cal v3
     */
    public function getEditLinkMarker(& $template, & $sims, & $rems, & $wrapped, $view)
    {
        trigger_error('Deprecated since ext:cal v2, will be removed in ext:cal v3.', E_USER_DEPRECATED);

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
     * @deprecated since ext:cal v2, will be removed in ext:cal v3
     */
    public function addEventLink($key, $link)
    {
        trigger_error('Deprecated since ext:cal v2, will be removed in ext:cal v3.', E_USER_DEPRECATED);

        $this->eventLinks[$key] = $link;
    }

    /**
     * @return array
     * @deprecated since ext:cal v2, will be removed in ext:cal v3
     */
    public function getEventLinks(): array
    {
        trigger_error('Deprecated since ext:cal v2, will be removed in ext:cal v3.', E_USER_DEPRECATED);

        return $this->eventLinks;
    }

    /**
     * @param $linktext
     * @return string
     * @deprecated since ext:cal v2, will be removed in ext:cal v3
     */
    public function getLinkToOrganizer($linktext): string
    {
        trigger_error('Deprecated since ext:cal v2, will be removed in ext:cal v3.', E_USER_DEPRECATED);

        return $this->getLinkToLocation($linktext);
    }

    /**
     * @param string $linktext
     * @return string
     * @deprecated since ext:cal v2, will be removed in ext:cal v3
     */
    public function getLinkToLocation($linktext): string
    {
        trigger_error('Deprecated since ext:cal v2, will be removed in ext:cal v3.', E_USER_DEPRECATED);

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
     * @deprecated since ext:cal v2, will be removed in ext:cal v3
     */
    public function updateWithPIVars(&$piVars)
    {
        trigger_error('Deprecated since ext:cal v2, will be removed in ext:cal v3.', E_USER_DEPRECATED);

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
     * @deprecated since ext:cal v2, will be removed in ext:cal v3
     */
    public function __toString()
    {
        trigger_error('Deprecated since ext:cal v2, will be removed in ext:cal v3.', E_USER_DEPRECATED);

        return get_class($this) . ': ' . implode(', ', $this->row);
    }
}
