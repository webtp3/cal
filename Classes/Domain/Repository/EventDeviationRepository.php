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
class EventDeviationRepository extends DoctrineRepository
{
    /**
     * @var string
     */
    protected $table = 'tx_cal_event_deviation';

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
     * @param EventIndexRepository $eventIndexRepository
     */
    public function injectEventIndexRepository(EventIndexRepository $eventIndexRepository)
    {
        $this->eventIndexRepository = $eventIndexRepository;
    }
}
