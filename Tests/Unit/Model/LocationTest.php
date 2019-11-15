<?php

/*
 * This file is part of the web-tp3/cal.
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace TYPO3\CMS\Cal\Tests\Unit\Model;

/**
 * This file is part of the "news" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */
use Nimut\TestingFramework\TestCase\UnitTestCase;
use TYPO3\CMS\Cal\Model\EventModel;
use TYPO3\CMS\Cal\Model\Location;

/**
 * Tests for domains model News
 *
 */
class LocationTest extends UnitTestCase
{

    /**
     * @var Location
     */
    protected $calModelInstance;

    /**
     * Set up framework
     *
     */
    protected function setUp()
    {
        // * @param string $serviceKey Service key, must be prefixed "tx_", "Tx_" or "user_"
        $this->calModelInstance = new Location([],'0');
    }

    /**
     * Test if title can be set
     *
     * @test
     */
    public function titleCanBeSet()
    {
        $name = 'Cal Location Name';
        $this->calModelInstance->setName($name);
        $this->assertEquals($name, $this->calModelInstance->getName());
    }

    /**
     * Test setTstamp
     *
     * @test
     */
    public function canSetTstamp()
    {
        $date = date('U');
        $this->calModelInstance->setTstamp($date);
        $this->assertEquals($date, $this->calModelInstance->getTstamp());
    }

//    /**
//     * Test Create
//     *
//     * @test
//     */
//    public function canCreateLocation()
//    {
////        $this->setUid($row['uid']);
////        $this->setName($row['name']);
////        $this->setDescription($row['description']);
////        $this->setStreet($row['street']);
////        $this->setZip($row['zip']);
////        $this->setCity($row['city']);
////        $this->setCountryZone($row['country_zone']);
////        $this->setCountry($row['country']);
////        $this->setPhone($row['phone']);
////        $this->setEmail($row['email']);
////        $this->setImage(GeneralUtility::trimExplode(',', $row['image'], 1));
////        $this->setLink($row['link']);
////        $this->setLatitude($row['latitude']);
////        $this->setLongitude($row['longitude']);
////        $this->assertEquals($row, $this->calModelInstance->getTstamp());
//
//    }

}
