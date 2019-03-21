<?php

namespace TYPO3\CMS\Cal\Tests\Unit\Model;

/**
 * This file is part of the "news" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */
use CAG\CagTests\Core\Unit\UnitTestCase;
use TYPO3\CMS\Cal\Model\CalendarModel;

/**
 * Tests for domains model Cal
 *
 */
class CalendarModelTest extends UnitTestCase
{

    /**
     * @var Cal
     */
    protected $calModelInstance;

    /**
     * Set up framework
     *
     */
    protected function setUp()
    {
        // * @param string $serviceKey Service key, must be prefixed "tx_", "Tx_" or "user_"
        $this->calModelInstance = new CalendarModel('','tx_cal_calendar');
    }

    /**
     * Test if tests work fine
     * @test
     */
    public function dummyMethod() {
        $this->assertTrue(true);
    }

    /**
     * Test if title can be set
     *
     * @test
     */
    public function titleCanBeSet()
    {
        $title = 'Cal title';
        $this->calModelInstance->setTitle($title);
        $this->assertEquals($title, $this->calModelInstance->getTitle());
    }



    /**
     * Test if ActivateFreeAndBusy can be set
     *
     * @test
     */
    public function canSetActivateFreeAndBusy()
    {
        $this->calModelInstance->setActivateFreeAndBusy(1);
        $this->assertEquals(1, $this->calModelInstance->getActivateFreeAndBusy());
    }

    /**
     * Test if CalendarType can be set
     *
     * @test
     */
    public function canSetCalendarType()
    {
        $this->calModelInstance->setCalendarType(2);
        $this->assertEquals(2, $this->calModelInstance->getCalendarType());
    }

    /**
     * Test if ExtUrl can be set
     *
     * @test
     */
    public function canSetExtUrl()
    {
        $url = 'https://www.typotest.de';
        $this->calModelInstance->setExtUrl($url);
        $this->assertEquals($url, $this->calModelInstance->getExtUrl());
    }



    /**
     * Test if IcsFile can be set
     *
     * @test
     */

    public function canSetIcsFile(): string
    {
        $url = 'https://www.typotest.de';
        $this->calModelInstance->setIcsFile($url);
        $this->assertEquals($url, $this->calModelInstance->getIcsFile());
    }



}
