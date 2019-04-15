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

/**
 * Class SubscriptionRepository
 */
class SubscriptionRepository extends DoctrineRepository
{
    /**
     * @var string
     */
    protected $table = 'tx_cal_fe_user_event_monitor_mm';

    /**
     * @param int $eventUid
     * @return array
     */
    public function findSubscribingUsersAndGroupsByEventUid(int $eventUid): array
    {
        $queryBuilder = $this->getQueryBuilder();
        return $queryBuilder
            ->select('uid_foreign', 'tablenames')
            ->from($this->table)
            ->where(
                $queryBuilder->expr()->eq(
                    'uid_local',
                    $queryBuilder->createNamedParameter($eventUid, \PDO::PARAM_INT)
                )
            )
            ->execute()
            ->fetchAll();
    }

    /**
     * @param int $eventUid
     * @return array
     */
    public function findSubscribingGroupsByEventUid(int $eventUid): array
    {
        $queryBuilder = $this->getQueryBuilder();
        return $queryBuilder
            ->select('uid_foreign', 'tablenames')
            ->from($this->table)
            ->where(
                $queryBuilder->expr()->eq('uid_local', $queryBuilder->createNamedParameter($eventUid, \PDO::PARAM_INT)),
                $queryBuilder->expr()->eq('tablenames', 'fe_groups')
            )
            ->execute()
            ->fetchAll();
    }

    /**
     * @param int $eventUid
     * @return array
     */
    public function findSubscribingUsersByEventUid(int $eventUid): array
    {
        $queryBuilder = $this->getQueryBuilder();
        return $queryBuilder
            ->select('fe_users.*')
            ->from($this->table, 'mm')
            ->where(
                $queryBuilder->expr()->eq('mm.uid_local', $queryBuilder->createNamedParameter($eventUid, \PDO::PARAM_INT)),
                $queryBuilder->expr()->eq('mm.tablenames', 'fe_users')
            )
            ->join('mm', 'fe_users', 'users', 'mm.uid_foreign = users.uid')
            ->execute()
            ->fetchAll();
    }

    /**
     * @param int $eventUid
     * @return array
     */
    public function findUnknownSubscribingUsersByEventUid(int $eventUid): array
    {
        $queryBuilder = $this->getQueryBuilder();
        return $queryBuilder
            ->select('tx_cal_unknown_users.*')
            ->from($this->table, 'mm')
            ->where(
                $queryBuilder->expr()->eq('mm.uid_local', $queryBuilder->createNamedParameter($eventUid, \PDO::PARAM_INT)),
                $queryBuilder->expr()->eq('mm.tablenames', 'tx_cal_unknown_users')
            )
            ->join('mm', 'tx_cal_unknown_users', 'users', 'mm.uid_foreign = users.uid')
            ->execute()
            ->fetchAll();
    }

    /**
     * @param int $eventUid
     * @return array
     */
    public function findByEventUid(int $eventUid): array
    {
        $queryBuilder = $this->getQueryBuilder();
        return $queryBuilder
            ->select('*')
            ->from($this->table)
            ->where(
                $queryBuilder->expr()->eq('uid_local', $queryBuilder->createNamedParameter($eventUid, \PDO::PARAM_INT))
            )
            ->execute()
            ->fetchAll();
    }

    /**
     * @param int $eventUid
     * @param int $userUid
     * @return array
     */
    public function findSubscriptionByEventUidAndSubscribingUserUid(int $eventUid, int $userUid): array
    {
        $queryBuilder = $this->getQueryBuilder();
        return $queryBuilder
            ->select('*')
            ->from($this->table)
            ->where(
                $queryBuilder->expr()->eq(
                    'uid_foreign',
                    $queryBuilder->createNamedParameter($userUid, \PDO::PARAM_INT)
                ),
                $queryBuilder->expr()->eq('uid_local', $queryBuilder->createNamedParameter($eventUid, \PDO::PARAM_INT))
            )
            ->execute()
            ->fetchAll();
    }

    /**
     * @param int $eventUid
     * @param int $sharedUid
     * @param string $foreignTableName
     * @return array
     */
    public function findByEventUidAndSharedUidAndTable(int $eventUid, int $sharedUid, string $foreignTableName): array
    {
        $queryBuilder = $this->getQueryBuilder();
        return $queryBuilder
            ->select('*')
            ->from($this->table)
            ->where(
                $queryBuilder->expr()->eq('uid_foreign', $queryBuilder->createNamedParameter($sharedUid, \PDO::PARAM_INT)),
                $queryBuilder->expr()->eq('uid_local', $queryBuilder->createNamedParameter($eventUid, \PDO::PARAM_INT)),
                $queryBuilder->expr()->eq('tablenames', $queryBuilder->createNamedParameter($foreignTableName))
            )
            ->execute()
            ->fetchAll();
    }

    /**
     * @param array $subscribersUids
     * @return array
     */
    public function findEventsBySubscribersUids(array $subscribersUids): array
    {
        $queryBuilder = $this->getQueryBuilder();
        return $queryBuilder
            ->select('event.*')
            ->from($this->table, 'mm')
            ->where(
                $queryBuilder->expr()->in('mm.uid_foreign', $subscribersUids)
            )
            ->join('mm', 'tx_cal_event', 'event', 'mm.uid_local = event.uid')
            ->execute()
            ->fetchAll();
    }
    /**
     * @param int $eventUid
     * @param int $sharedUid
     * @param string $foreignTableName
     * @return \Doctrine\DBAL\Driver\Statement|int
     */
    public function deleteByEventUidAndSharedUidAndTable(int $eventUid, int $sharedUid, string $foreignTableName)
    {
        $queryBuilder = $this->getQueryBuilder();
        return $queryBuilder
            ->delete($this->table)
            ->where(
                $queryBuilder->expr()->eq('uid_local', $queryBuilder->createNamedParameter($eventUid, \PDO::PARAM_INT)),
                $queryBuilder->expr()->eq('uid_foreign', $queryBuilder->createNamedParameter($sharedUid, \PDO::PARAM_INT)),
                $queryBuilder->expr()->eq('tablenames', $queryBuilder->createNamedParameter($foreignTableName))
            )
            ->execute();
    }

    /**
     * @param int $eventUid
     * @return \Doctrine\DBAL\Driver\Statement|int
     */
    public function deleteByEventUid(int $eventUid)
    {
        $queryBuilder = $this->getQueryBuilder();
        return $queryBuilder
            ->delete($this->table)
            ->where(
                $queryBuilder->expr()->eq('uid_local', $queryBuilder->createNamedParameter($eventUid, \PDO::PARAM_INT))
            )
            ->execute();
    }

    /**
     * @param int $eventUid
     * @param string $foreignTableName
     * @return \Doctrine\DBAL\Driver\Statement|int
     */
    public function deleteByEventUidAndTable(int $eventUid, string $foreignTableName)
    {
        $queryBuilder = $this->getQueryBuilder();
        return $queryBuilder
            ->delete($this->table)
            ->where(
                $queryBuilder->expr()->eq('uid_local', $queryBuilder->createNamedParameter($eventUid, \PDO::PARAM_INT)),
                $queryBuilder->expr()->eq('tablenames', $queryBuilder->createNamedParameter($foreignTableName))
            )
            ->execute();
    }

    /**
     * @param array $values
     */
    public function insert(array $values)
    {
        $queryBuilder = $this->getQueryBuilder();
        $queryBuilder
            ->insert($this->table)
            ->values($values)
            ->execute();
    }

    /**
     * @param array $sharedUids
     * @param int $eventUid
     * @param string $foreignTableName
     */
    public function insertSubscription(array $sharedUids, int $eventUid, string $foreignTableName)
    {
        foreach ($sharedUids as $key => $foreign_id) {
            $queryBuilder = $this->getQueryBuilder();
            $queryBuilder
                ->insert($this->table)
                ->values([
                    'uid_local' => $eventUid,
                    'uid_foreign' => $foreign_id,
                    'tablenames' => $foreignTableName,
                    'sorting' => $key + 1
                ])
                ->execute();
        }
    }
}
