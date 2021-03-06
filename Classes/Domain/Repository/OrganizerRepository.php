<?php
declare(strict_types = 1);

/*
 * This file is part of the web-tp3/cal.
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace TYPO3\CMS\Cal\Domain\Repository;

use TYPO3\CMS\Cal\Domain\Model\Organizer;

/**
 * This file is part of the TYPO3 extension Calendar Base (cal).
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 extension Calendar Base (cal) project - inspiring people to share!
 */

/**
 * Class OrganizerRepository
 */
class OrganizerRepository extends DoctrineRepository
{
    /**
     * @var string
     */
    protected $table = 'tx_cal_organizer';

    /**
     * @var string
     */
    protected $model = Organizer::class;
}
