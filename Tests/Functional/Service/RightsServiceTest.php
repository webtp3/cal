<?php

/*
 * This file is part of the web-tp3/cal.
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace TYPO3\CMS\Cal\Tests\Functional\Service;

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

use TYPO3\CMS\Cal\Service\RightsService;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class RightsServiceTest
 */
class RightsServiceTest extends \TYPO3\TestingFramework\Core\Functional\FunctionalTestCase
{


    /** @var \TYPO3\CMS\Extbase\Object\ObjectManagerInterface The object manager */
    protected $objectManager;

    /** @var  RightsService */
    protected $calService;

    protected $testExtensionsToLoad = ['typo3conf/ext/cal'];

    public function setUp()
    {
        parent::setUp();
        $success = true;
        $this->objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
        $this->importDataSet(__DIR__ . '/../Fixtures/tx_cal_calendar.xml');
        $this->importDataSet(__DIR__ . '/../Fixtures/tx_cal_event.xml');
        $this->calService = $this->objectManager->get(RightsService::class);

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
    public function canCheckIsLoggedIn()
    {

       $c =  $this->calService->isLoggedIn();
        $this->assertEquals(false, $c);
    }
    /**
     * Test find external calendar uid or pid-list
     *
     * @test
     */
    public function canCheckIsCalAdmin()
    {
        /*
            if(getenv('TYPO3_Test')
         */

        $c =  $this->calService->isCalAdmin();
        $this->assertEquals(getenv('TYPO3_Test'), $c);
    }



}
