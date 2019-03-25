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
 * Class AttendeeRepository
 */
class AttendeeRepository extends DoctrineRepository
{
    /**
     * @var string
     */
    protected $table = 'tx_cal_attendee';

    /**
     * @var EventIndexRepository
     */
    protected $eventIndexRepository;
}
