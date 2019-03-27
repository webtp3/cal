<?php
declare(strict_types = 1);

/*
 * This file is part of the web-tp3/cal.
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace TYPO3\CMS\Cal\Domain\Repository;

use TYPO3\CMS\Cal\Service\RightsService;
use TYPO3\CMS\Cal\Utility\Registry;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This file is part of the TYPO3 extension Calendar Base (cal).
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 extension Calendar Base (cal) project - inspiring people to share!
 */

/**
 * Class CalendarRepository
 */
class CalendarRepository extends DoctrineRepository
{
    /**
     * @var string
     */
    protected $table = 'tx_cal_calendar';

    /**
     * @var UserGroupMMRepository
     */
    protected $userGroupMMRepository;

    /**
     * @param string $limitationList
     * @param $pidList
     * @param $includePublic
     * @param bool $includeData
     * @param bool $onlyPublic
     * @return array
     */
    public function getAccessibleCalendars($limitationList, $pidList, $includePublic, $includeData = false, $onlyPublic = false): array
    {
        $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $groupIds = '';
        $userId = '';
        $calendarIds = [];

        $queryBuilder = $this->getQueryBuilder();
        $queryBuilder
            ->select('*')
            ->from($this->table, 'calendar')
            ->groupBy('uid');

        // Lets see if the user is logged in
        /** @var RightsService $rightsObj */
        //$rightsObj = &Registry::Registry('basic', 'rightscontroller');
        $rightsObj = $this->objectManager->get(RightsService::class);
        if (!$onlyPublic && $rightsObj->isLoggedIn()) {
            $userId = $rightsObj->getUserId();
            $groupIds = implode(',', $rightsObj->getUserGroups());
        }

        if ($userId === '') {
            return [];
        }

        $userGroupMMs = $this->userGroupMMRepository->findAll();
        if (!empty($userGroupMMs)) {
            $userGroupUids = [];
            foreach ($userGroupMMs as $userGroupMM) {
                $userGroupUids[] = $userGroupMM['uid_local'];
            }
            $queryBuilder->andWhere(
                $queryBuilder->expr()->notIn('uid', array_unique($userGroupUids))
            );
        }

        if ($pidList !== '') {
            $queryBuilder->andWhere(
                $queryBuilder->expr()->in('pid', $pidList)
            );
        }

        if ($onlyPublic === false) {
            if ($userId) {
                $queryBuilder->join('calendar', 'tx_cal_calendar_user_group_mm', 'mm', 'mm.local_uid = calendar.uid');
                $queryBuilder->where(
                    $queryBuilder->expr()->orX(
                        $queryBuilder->expr()->andX(
                            $queryBuilder->expr()->in('mm.uid_foreign', $userId),
                            $queryBuilder->expr()->in('mm.tablenames', 'fe_users')
                        ),
                        $queryBuilder->expr()->andX(
                            $queryBuilder->expr()->in('mm.uid_foreign', $groupIds),
                            $queryBuilder->expr()->in('mm.tablenames', 'fe_groups')
                        )
                    )
                );
            }
            if ($limitationList !== '') {
                $queryBuilder->andWhere(
                    $queryBuilder->expr()->in('uid', $limitationList)
                );
            }
        }
        $calendars = $queryBuilder
            ->execute()
            ->fetchAll();

        foreach ($calendars as $calendar) {
            $calendarIds[] = $includeData ? $calendar : $calendar['uid'];
        }

        return $calendarIds;
    }

    /**
     * @param UserGroupMMRepository $userGroupMMRepository
     */
    public function injectUserGroupMMRepository(UserGroupMMRepository $userGroupMMRepository)
    {
        $this->userGroupMMRepository = $userGroupMMRepository;
    }
}
