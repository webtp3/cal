<?php
declare(strict_types = 1);

namespace TYPO3\CMS\Cal\Domain\Repository;

/**
 * This file is part of the TYPO3 extension Calendar Base (cal).
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 extension Calendar Base (cal) project - inspiring people to share!
 */
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class DoctrineRepository
 */
class DoctrineRepository
{

    /**
     * @var string
     */
    protected $table = '';

    /**
     * @return QueryBuilder
     */
    protected function getQueryBuilder(): QueryBuilder
    {
        return GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionForTable($this->table)
            ->createQueryBuilder();
    }
}
