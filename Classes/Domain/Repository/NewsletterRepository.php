<?php
declare(strict_types=1);
namespace MaiSpace\Newsletter\Domain\Repository;

use MaiSpace\Newsletter\Domain\Model\Newsletter;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;

class NewsletterRepository extends Repository
{
    protected $defaultOrderings = ['crdate' => QueryInterface::ORDER_DESCENDING];

    public function findByStatus(int $status): array
    {
        $query = $this->createQuery();
        $query->matching($query->equals('status', $status));
        return $query->execute()->toArray();
    }

    public function findScheduledForSending(): array
    {
        $query = $this->createQuery();
        $now = new \DateTime();
        $query->matching(
            $query->logicalAnd(
                $query->equals('status', Newsletter::STATUS_SCHEDULED),
                $query->lessThanOrEqual('scheduledAt', $now)
            )
        );
        return $query->execute()->toArray();
    }
}
