<?php
declare(strict_types = 1);

/*
 * This file is part of the web-tp3/cal.
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

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
use TYPO3\CMS\Core\Exception;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapper;
use TYPO3\CMS\Extbase\Persistence\Repository;

/**
 * Class DoctrineRepository
 */
class DoctrineRepository extends Repository
{

    /**
     * @var string
     */
    protected $table = '';

    /**
     * @var string
     */
    protected $model = '';

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

    /**
     * This function creates an extbase object from the database result.
     *
     * @param array $row
     * @return AbstractEntity
     */
    public function getObject(array $row): AbstractEntity
    {
        if ($this->model === '') {
            throw new Exception('No model is defined to handle objects from table ' . $this->table . '.', 1550607468);
        }
        return GeneralUtility::makeInstance(ObjectManager::class)
            ->get(DataMapper::class)
            ->map($this->model, [$row])[0];
    }
}
