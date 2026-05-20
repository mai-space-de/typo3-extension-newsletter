<?php

declare(strict_types=1);

namespace Maispace\MaiNewsletter\Domain\Repository;

use Maispace\MaiNewsletter\Domain\Model\Subscriber;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;

/**
 * @extends Repository<Subscriber>
 */
class SubscriberRepository extends Repository
{
    /**
     * @var array<non-empty-string, 'ASC'|'DESC'>
     */
    protected $defaultOrderings = [
        'crdate' => QueryInterface::ORDER_DESCENDING,
    ];

    public function findByEmailAndSite(string $email, string $site): ?Subscriber
    {
        $query = $this->createQuery();
        $query->getQuerySettings()->setRespectStoragePage(false);
        $query->matching(
            $query->logicalAnd(
                $query->equals('email', $email),
                $query->equals('site', $site),
            ),
        );

        $result = $query->execute()->getFirst();

        return $result instanceof Subscriber ? $result : null;
    }

    public function findByToken(string $token): ?Subscriber
    {
        if ($token === '') {
            return null;
        }

        $query = $this->createQuery();
        $query->getQuerySettings()->setRespectStoragePage(false);
        $query->matching($query->equals('token', $token));

        $result = $query->execute()->getFirst();

        return $result instanceof Subscriber ? $result : null;
    }

    /**
     * @return QueryResultInterface<Subscriber>
     */
    public function findByStatus(string $status): QueryResultInterface
    {
        $query = $this->createQuery();
        $query->matching($query->equals('status', $status));

        return $query->execute();
    }

    /**
     * @return QueryResultInterface<Subscriber>
     */
    public function findSubscribed(): QueryResultInterface
    {
        return $this->findByStatus(Subscriber::STATUS_SUBSCRIBED);
    }
}
