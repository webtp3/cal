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

/**
 * Class UserGroupMMRepository
 */
class UserGroupMMRepository extends DoctrineRepository
{
    /**
     * @var string
     */
    protected $table = 'tx_cal_calendar_user_group_mm';

    /**
     * @param int $groupUid
     * @return array
     */
    public function findAllByCalendarUid(int $groupUid): array
    {
        $queryBuilder = $this->getQueryBuilder();
        return $queryBuilder
            ->select('user_group_mm.*')
            ->from($this->table, 'user_group_mm')
            ->join('user_group_mm', 'fe_groups', 'fe_groups', 'user_group_mm.uid_foreign = fe_groups.uid')
            ->join('user_group_mm', 'fe_users', 'fe_users', 'user_group_mm.uid_foreign = fe_users.uid')
            ->join('user_group_mm', 'tx_cal_calendar', 'calendar', 'user_group_mm.uid_local = tx_cal_calendar.uid')
            ->where(
                $queryBuilder
                    ->expr()
                    ->eq(
                        'user_group_mm.uid_local',
                        $queryBuilder
                            ->createNamedParameter($groupUid, \PDO::PARAM_INT)
                    )
            )
            ->execute()
            ->fetchAll();
    }

    /**
     * @return array
     */
    public function findAll(): array
    {
        $queryBuilder = $this->getQueryBuilder();
        return $queryBuilder
            ->select('user_group_mm.*')
            ->from($this->table, 'user_group_mm')
            ->join('user_group_mm', 'fe_groups', 'fe_groups', 'user_group_mm.uid_foreign = fe_groups.uid')
            ->join('user_group_mm', 'fe_users', 'fe_users', 'user_group_mm.uid_foreign = fe_users.uid')
            ->join('user_group_mm', 'tx_cal_calendar', 'calendar', 'user_group_mm.uid_local = tx_cal_calendar.uid')
            ->execute()
            ->fetchAll();
    }

    /**
     * @param int $groupUid
     * @return \Doctrine\DBAL\Driver\Statement|int
     */
    public function deleteByCalendarUid(int $groupUid)
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
                            ->createNamedParameter($groupUid, \PDO::PARAM_INT)
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
