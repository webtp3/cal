<?php

/*
 * This file is part of the web-tp3/cal.
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace TYPO3\CMS\Cal\Tests\Unit\Functional\ViewHelpers;

/**
 * This file is part of the "news" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */
use DateTime;
use TYPO3\CMS\Cal\Model\EventModel;
use Nimut\TestingFramework\MockObject\AccessibleMockObjectInterface;
use Nimut\TestingFramework\TestCase\ViewHelperBaseTestcase;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class TempStoreViewHelperTestTest
 *
 */
class TempStoreViewHelperTest extends ViewHelperBaseTestcase
{

    /** @var \PHPUnit_Framework_MockObject_MockObject|AccessibleMockObjectInterface|\TYPO3\CMS\Cal\ViewHelpers\TempStoreViewHelperTest */
    protected $mockedViewHelper;

    /** @var News */
    protected $calevent;

    protected $testExtensionsToLoad = ['typo3conf/ext/cal'];
    protected $coreExtensionsToLoad = ['extbase', 'fluid'];

    public function setUp()
    {
        parent::setUp();
        $this->mockedViewHelper = $this->getAccessibleMock('TYPO3\\CMS\\Cal\\ViewHelpers\\TempStoreViewHelperTest', ['dummy'], [], '', true, true, false);

        $this->calevent = new EventModel();
        $this->calevent->setPid(9);

        $this->importDataSet(__DIR__ . '/../Fixtures/tx_cal_events.xml');
    }

    /**
     * @test
     */
    public function canRender()
    {
        $this->setDate(1396035186);
        $actual = $this->mockedViewHelper->_call('render', 'tx_cal_events','', $this->calevent);

        $exp = [
            'prev' => $this->getRow(1)
        ];
        $this->assertEquals($exp, $actual);
    }

    /**
     * @test
     */
    public function nextNeighbourCanBeFound()
    {
        $this->setDate(1395516730);

        $actual = $this->mockedViewHelper->_call('getNeighbours', $this->calevent, '', 'datetime');

        $exp = [
            'next' => $this->getRow(102)
        ];
        $this->assertEquals($exp, $actual);
    }

    /**
     * @test
     */
    public function previousNeighbourCanBeFound()
    {
        $this->setDate(1396640035);
        $actual = $this->mockedViewHelper->_call('getNeighbours', $this->calevent, '', 'datetime');
        $exp = [
            'prev' => $this->getRow(105)
        ];
        $this->assertEquals($exp, $actual);
    }

    /**
     * @param int $timestamp
     */
    protected function setDate($timestamp)
    {
        $date = new DateTime();
        $date->setTimestamp($timestamp);
        $this->calevent->_setProperty('datetime', $date);
    }

    protected function getRow($id)
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('tx_news_domain_model_news');
        return $queryBuilder
            ->select('*')
            ->from('tx_news_domain_model_news')
            ->where(
                $queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($id, \PDO::PARAM_INT))
            )
            ->setMaxResults(1)
            ->execute()->fetch();
    }
}
