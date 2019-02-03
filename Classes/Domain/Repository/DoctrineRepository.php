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
    public function getQueryBuilder(): QueryBuilder
    {
        return GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionForTable($this->table)
            ->createQueryBuilder();
    }

    /**
     * @return array
     */
    public function findAll(): array
    {
        $queryBuilder = $this->getQueryBuilder();
        return $queryBuilder
            ->select('*')
            ->from($this->table)
            ->execute()
            ->fetchAll();
    }

    /**
     * @param int $uid
     * @return array
     */
    public function findOneByUid(int $uid): array
    {
        $queryBuilder = $this->getQueryBuilder();
        return $queryBuilder
            ->select('*')
            ->from($this->table)
            ->where($queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($uid, \PDO::PARAM_INT)))
            ->execute()
            ->fetch();
    }

    /**
     * @param int $uid
     * @param array $values
     * @return int
     */
    public function updateByUid(int $uid, array $values): int
    {
        $queryBuilder = $this->getQueryBuilder();
        return $queryBuilder
            ->update($this->table)
            ->where($queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($uid, \PDO::PARAM_INT)))
            ->values($values)
            ->execute();
    }
}
