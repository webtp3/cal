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
use CAG\CagTests\Core\Unit\UnitTestCase;
use TYPO3\CMS\Cal\Model\CalDate;

/**
 * Tests for domains model Cal
 *
 */
class CalDateModelTest extends UnitTestCase
{

    /**
     * @var CalDate
     */
    protected $calDateInstance;

    /**
     * Set up framework
     *
     */
    protected function setUp()
    {
        /**
         * CalDate constructor.
         * @param $row
         * @param $isException
         * @param $serviceKey
         */
        //DateTime::__construct(): Failed to parse time string (1541622600) at position 8 (0): Unexpected character
        //1541622600
        //$dateTime = new CalDate($value);
        $this->calDateInstance = new CalDate();
    }

    /**
     * Test if tests work fine
     * @test
     */
    public function dummyMethod()
    {
        $this->assertTrue(true);
    }

    /**
     * Test if title can be set
     *
     * @test
     */
    public function canGetTimeFromTimestamp()
    {
        $ts = '1541622600';
        $this->calDateInstance->setTimestamp($ts);
        $this->assertEquals(2018, $this->calDateInstance->getYear());
    }

//
}
