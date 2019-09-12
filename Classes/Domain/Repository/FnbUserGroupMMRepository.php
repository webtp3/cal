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
 * Class FnbUserGroupRepository
 */
class FnbUserGroupMMRepository extends UserGroupMMRepository
{
    /**
     * @var string
     */
    protected $table = 'tx_cal_calendar_fnb_user_group_mm';
}
