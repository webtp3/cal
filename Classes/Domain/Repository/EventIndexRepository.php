<?php
declare(strict_types = 1);

/*
 * This file is part of the web-tp3/cal.
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace TYPO3\CMS\Cal\Domain\Repository;

use TYPO3\CMS\Cal\Model\CalDate;

/**
 * This file is part of the TYPO3 extension Calendar Base (cal).
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 extension Calendar Base (cal) project - inspiring people to share!
 */

/**
 * Class EventIndexRepository
 */
class EventIndexRepository extends DoctrineRepository
{
    /**
     * @var string
     */
    protected $table = 'tx_cal_index';

    /**
     * @param CalendarDateTime $starttime
     * @param CalendarDateTime $endtime
     * @return array
     */
    public function findRecurringEvents( $starttime, $endtime): array
    {
        $queryBuilder = $this->getQueryBuilder();
        return $queryBuilder
            ->select('*')
            ->from($this->table)
            ->where(
                $queryBuilder->expr()->orX(
                    $queryBuilder->expr()->andX(
                        $queryBuilder->expr()->gte('start_datetime', $starttime->format('YmdHi')),
                        $queryBuilder->expr()->lte('start_datetime', $endtime->format('YmdHi'))
                    ),
                    $queryBuilder->expr()->andX(
                        $queryBuilder->expr()->lt('start_datetime', $starttime->format('YmdHi')),
                        $queryBuilder->expr()->gt('end_datetime', $starttime->format('YmdHi'))
                    ),
                    $queryBuilder->expr()->andX(
                        $queryBuilder->expr()->lt('start_datetime', $endtime->format('YmdHi')),
                        $queryBuilder->expr()->gt('end_datetime', $endtime->format('YmdHi'))
                    )
                )
            )
            ->groupBy('event_uid')
            ->execute()
            ->fetchAll();
    }
}
