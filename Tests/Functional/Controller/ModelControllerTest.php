<?php

/*
 * This file is part of the web-tp3/cal.
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace TYPO3\CMS\Cal\Tests\Functional\Controller;

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
use TYPO3\CMS\Cal\Controller\ModelController;


/**
 * ModelController for calendar base (cal)
 */
class ModelControllerTest extends \CAG\CagTests\Core\Functional\FunctionalTestCase
{


    /** @var \TYPO3\CMS\Extbase\Object\ObjectManagerInterface The object manager */
    protected $objectManager;

    /** @var  ModelController */
    protected $modelController;

    protected $testExtensionsToLoad = ['typo3conf/ext/cal'];

    public function setUp()
    {
        parent::setUp();
        $success = true;
        $this->objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
        $this->importDataSet(__DIR__ . '/../Fixtures/tx_cal_event.xml');
        $this->modelController = $this->objectManager->get(ModelController::class);
        //$this->setUpFrontendRootPage(1, ['EXT:cal/Configuration/TypoScript/']);


    }

    /**
     * Test if tests work fine
     * @test
     */
    public function dummyMethod() {
        $this->assertTrue(true);
    }

    /**
     * Test find external calendar uid or pid-list
     * @test
     *
     */
    public function canFindEvent(): array
    {
        /*
      * @param int $uid
      *            to search for
      * @param string $pidList
      *            to search in
      * @return array array ($row)
     */

        $c =  $this->modelController->findEvent(2,'tx_cal_phpicalendar','1');
        $this->assertEquals(1, $c["uid"]);
    }

    /**
     * Test find external calendar uid or pid-list
     * @test
     *
     */
    public function canFindAllCalendarFromPid(): array
    {

        $c =  $this->modelController->findAllCalendar('tx_cal_phpicalendar','1');
        $this->assertEquals(1, $c["uid"]);
    }

    /**
     * Test find external calendar uid or pid-list
     * @test
     *
     */
    public function canFindAllCalendar(): array
    {

        $c =  $this->modelController->findAllCalendar('tx_cal_phpicalendar','');
        $this->assertEquals(1, $c["uid"]);
    }
    /**
     * Test create save and find the event
     * @test
     *
     */
    public function canCreateAndSaveAndFindEvent(): array
    {


        $evt =  $this->modelController->createEvent('tx_cal_phpicalendar');
        $evt->setPid(1);
        $evt->setUid(111);
        $type = "tx_cal_phpicalendar";
        $evt->setType($type);
        $title = "testtype event";
        $evt->setTitle($title);
        $this->modelController->saveEvent('','tx_cal_phpicalendar',1);
        $this->assertEquals($evt, $this->modelController->findEvent(111, $type, [1]));
    }
}
