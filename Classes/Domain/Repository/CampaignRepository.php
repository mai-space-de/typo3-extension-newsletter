<?php

declare(strict_types=1);

namespace Maispace\MaiNewsletter\Domain\Repository;

use Maispace\MaiNewsletter\Domain\Model\Campaign;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;

/**
 * @extends Repository<Campaign>
 */
class CampaignRepository extends Repository
{
    /**
     * @var array<non-empty-string, 'ASC'|'DESC'>
     */
    protected $defaultOrderings = [
        'crdate' => QueryInterface::ORDER_DESCENDING,
    ];

    /**
     * @return QueryResultInterface<Campaign>
     */
    public function findByStatus(string $status): QueryResultInterface
    {
        $query = $this->createQuery();
        $query->matching($query->equals('status', $status));

        return $query->execute();
    }

    /**
     * @return QueryResultInterface<Campaign>
     */
    public function findDue(\DateTimeImmutable $now): QueryResultInterface
    {
        $query = $this->createQuery();
        $query->matching(
            $query->logicalAnd(
                $query->equals('status', Campaign::STATUS_SCHEDULED),
                $query->lessThanOrEqual('scheduledAt', $now->getTimestamp()),
            ),
        );

        return $query->execute();
    }
}
