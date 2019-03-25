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
 * Class UnknownUserRepository
 */
class UnknownUserRepository extends DoctrineRepository
{
    /**
     * @var string
     */
    protected $table = 'tx_cal_unknown_users';

    /**
     * @var EventIndexRepository
     */
    protected $eventIndexRepository;

    /**
     * @param string $email
     * @return array
     */
    public function findByEmailAddress(string $email): array
    {
        $queryBuilder = $this->getQueryBuilder();
        return $queryBuilder
            ->select('*')
            ->from($this->table)
            ->where(
                $queryBuilder->expr()->like('email', $queryBuilder->createNamedParameter($email))
            )
            ->execute()
            ->fetch();
    }
}
