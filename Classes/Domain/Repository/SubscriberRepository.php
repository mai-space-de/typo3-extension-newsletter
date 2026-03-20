<?php
declare(strict_types=1);
namespace Maispace\MaiNewsletter\Domain\Repository;

use Maispace\MaiNewsletter\Domain\Model\Subscriber;
use TYPO3\CMS\Extbase\Persistence\Repository;

class SubscriberRepository extends Repository
{
    public function findByToken(string $token): ?Subscriber
    {
        $query = $this->createQuery();
        $query->getQuerySettings()->setRespectStoragePage(false);
        $query->matching($query->equals('token', $token));
        $result = $query->execute()->getFirst();
        return $result instanceof Subscriber ? $result : null;
    }

    public function findOneByEmail(string $email): ?Subscriber
    {
        $query = $this->createQuery();
        $query->getQuerySettings()->setRespectStoragePage(false);
        $query->matching($query->equals('email', $email));
        $result = $query->execute()->getFirst();
        return $result instanceof Subscriber ? $result : null;
    }

    public function findConfirmedSubscribers(): array
    {
        $query = $this->createQuery();
        $query->matching($query->equals('confirmed', true));
        return $query->execute()->toArray();
    }
}
