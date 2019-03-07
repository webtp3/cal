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
 * Class EventSharedUserMMRepository
 */
class EventSharedUserMMRepository extends DoctrineRepository
{
    /**
     * @var string
     */
    protected $table = 'tx_cal_event_shared_user_mm';

    /**
     * @param int $eventUid
     * @return array
     */
    public function findSharedUidsByEventUid(int $eventUid): array
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
     * @return \Doctrine\DBAL\Driver\Statement|int
     */
    public function deleteByEventUid(int $eventUid)
    {
        $queryBuilder = $this->getQueryBuilder();
        return $queryBuilder
            ->delete($this->table)
            ->where(
                $queryBuilder
                    ->expr()
                    ->eq(
                        'uid_local',
                        $queryBuilder
                            ->createNamedParameter($eventUid, \PDO::PARAM_INT)
                    )
            )
            ->execute();
    }

    /**
     * @param array $idArray
     * @param int $uid
     * @param string $foreignTableName
     */
    public function insertIdsIntoTableWithMMRelation(array $idArray, int $uid, string $foreignTableName)
    {
        foreach ($idArray as $key => $foreign_id) {
            $queryBuilder = $this->getQueryBuilder();
            $queryBuilder
                ->insert($this->table)
                ->values([
                    'uid_local' => $uid,
                    'uid_foreign' => $foreign_id,
                    'tablenames' => $foreignTableName,
                    'sorting' => $key + 1
                ])
                ->execute();
        }
    }
}
