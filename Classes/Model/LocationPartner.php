<?php

namespace TYPO3\CMS\Cal\Model;

use tx_partner_main;
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

/**
 * Base model for the calendar location.
 * Provides basic model functionality that other
 * models can use or override by extending the class.
 */
class LocationPartner extends Location
{
    private $partner;

    /**
     * Constructor
     *
     * @param int $uid
     *            to search for
     * @param string $pidList
     *            to search in
     */
    public function __construct($uid, $pidList)
    {
        require_once ExtensionManagementUtility::extPath('partner') . 'api/class.tx_partner_main.php';
        require_once ExtensionManagementUtility::extPath('partner') . 'api/class.tx_partner_div.php';

        $this->partner = new tx_partner_main();
        $this->partner->getPartner($uid);
        $this->partner->getContactInfo($this->conf['view.']['location.']['contactInfoType']);

        $this->Location($this->partner->data, $this->getType());

        $this->setType('tx_partner_main');
        $this->setObjectType('location');
        $this->templatePath = $this->conf['view.']['location.']['locationModelTemplate4partner'];
    }

    /**
     * @return mixed|string
     */
    public function getName()
    {
        $partnername = '';
        switch ($this->partner->data['type']) {
            case 0:
                $partnername = $this->partner->data['first_name'] . ' ' . $this->partner->data['last_name'];
                break;
            case 1:
                $partnername = $this->partner->data['org_name'];
                break;
            default:
                $partnername = $this->partner->data['label'];
        }
        return $partnername;
    }

    /**
     * @return mixed
     */
    public function getFirstName()
    {
        return $this->partner->data['first_name'];
    }

    /**
     * @param $t
     */
    public function setFirstName($t)
    {
        $this->partner->data['first_name'] = $t;
    }

    /**
     * @return mixed
     */
    public function getMiddleName()
    {
        return $this->partner->data['middle_name'];
    }

    /**
     * @param $t
     */
    public function setMiddleName($t)
    {
        $this->partner->data['middle_name'] = $t;
    }

    /**
     * @return mixed
     */
    public function getLastName()
    {
        return $this->partner->data['last_name'];
    }

    /**
     * @param $t
     */
    public function setLastName($t)
    {
        $this->partner->data['last_name'] = $t;
    }

    /**
     * @return mixed
     */
    public function getStreetNumber()
    {
        return $this->partner->data['street_number'];
    }

    /**
     * @param $t
     */
    public function setStreetNumber($t)
    {
        $this->partner->data['street_number'] = $t;
    }

    /**
     * @return mixed
     */
    public function getZip()
    {
        return $this->partner->data['postal_code'];
    }

    /**
     * @param $t
     */
    public function setZip($t)
    {
        $this->partner->data['postal_code'] = $t;
    }

    /**
     * @param $subpartMarker
     * @return string|processed
     */
    public function fillTemplate($subpartMarker)
    {
        $GLOBALS['LANG']->includeLLFile('EXT:partner/locallang.php');
        return parent::fillTemplate($subpartMarker);
    }
}
