<?php
declare(strict_types = 1);

/*
 * This file is part of the web-tp3/cal.
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace TYPO3\CMS\Cal\Domain\Repository;

use TYPO3\CMS\Cal\Model\CalendarDateTime;

/**
 * This file is part of the TYPO3 extension Calendar Base (cal).
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 extension Calendar Base (cal) project - inspiring people to share!
 */

/**
 * Class EventRepository
 */
class EventRepository extends DoctrineRepository
{
    /**
     * @var string
     */
    protected $table = 'tx_cal_event';

    /**
     * @var EventIndexRepository
     */
    protected $eventIndexRepository;

    /**
     * @param CalendarDateTime $starttime
     * @param CalendarDateTime $endtime
     * @return array
     */
    public function findUidsOfRecurringEvents(CalendarDateTime $starttime, CalendarDateTime $endtime): array
    {
        $eventUidArray = [];
        $recurringEvents = $this->eventIndexRepository->findRecurringEvents($starttime, $endtime);
        foreach ($recurringEvents as $recurringEvent) {
            $eventUidArray[] = $recurringEvent['event_uid'];
        }
        return $eventUidArray;
    }

    /**
     * @param CalendarDateTime $starttime
     * @param CalendarDateTime $endtime
     * @return array
     */
    public function findRecurringEvents(CalendarDateTime $starttime, CalendarDateTime $endtime, $settings = []): array
    {
        #todo include deviations
        $recurringEvents = $this->eventIndexRepository->findIndexEvents($starttime, $endtime);
//        foreach ($recurringEvents as $recurringEvent) {
//            $eventUidArray[] = $recurringEvent['event_uid'];
//        }
        return $recurringEvents;
    }
    /**
     * @param CalendarDateTime $starttime
     * @param CalendarDateTime $endtime
     * @return array
     */
    public function findAllWithin($starttime, $endtime): array
    {
        $queryBuilder = $this->getQueryBuilder();
        return $queryBuilder
            ->select('*')
            ->from($this->table)
            ->where(
                $queryBuilder->expr()->orX(
                    $queryBuilder->expr()->andX(
                        $queryBuilder->expr()->gte('start_date', $starttime->format('Ymd')),
                        $queryBuilder->expr()->lte('start_date', $endtime->format('Ymd'))
                    ),
                    $queryBuilder->expr()->andX(
                        $queryBuilder->expr()->lt('start_date', $starttime->format('Ymd')),
                        $queryBuilder->expr()->gt('end_date', $starttime->format('Ymd'))
                    ),
                    $queryBuilder->expr()->andX(
                        $queryBuilder->expr()->lt('start_date', $endtime->format('Ymd')),
                        $queryBuilder->expr()->gt('end_date', $endtime->format('Ymd'))
                    )
                )
            )
            ->execute()
            ->fetchAll();
    }
    /**
     * @param EventIndexRepository $eventIndexRepository
     */
    public function injectEventIndexRepository(EventIndexRepository $eventIndexRepository)
    {
        $this->eventIndexRepository = $eventIndexRepository;
    }
}
