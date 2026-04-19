<?php

declare(strict_types=1);

namespace Maispace\MaiNewsletter\Service;

use Maispace\MaiNewsletter\Domain\Model\Subscriber;
use Maispace\MaiNewsletter\Domain\Repository\SubscriberRepository;
use TYPO3\CMS\Core\Crypto\Random;
use TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface;

class SubscriberService
{
    public function __construct(
        private readonly SubscriberRepository $subscriberRepository,
        private readonly PersistenceManagerInterface $persistenceManager,
        private readonly Random $random,
    ) {
    }

    public function optIn(string $email, string $site, int $storagePid, int $feUserUid = 0): Subscriber
    {
        $email = strtolower(trim($email));
        $site = trim($site);

        $subscriber = $this->subscriberRepository->findByEmailAndSite($email, $site);

        if ($subscriber !== null && $subscriber->isSubscribed()) {
            return $subscriber;
        }

        if ($subscriber === null) {
            $subscriber = new Subscriber();
            $subscriber->setPid($storagePid);
            $subscriber->setEmail($email);
            $subscriber->setSite($site);
        }

        $subscriber->setStatus(Subscriber::STATUS_PENDING);
        $subscriber->setToken($this->generateToken());
        $subscriber->setConfirmedAt(null);
        $subscriber->setUnsubscribedAt(null);
        $subscriber->setFeUser($feUserUid);

        if ($subscriber->getUid() === null) {
            $this->subscriberRepository->add($subscriber);
        } else {
            $this->subscriberRepository->update($subscriber);
        }

        $this->persistenceManager->persistAll();

        return $subscriber;
    }

    public function confirm(string $token): ?Subscriber
    {
        $subscriber = $this->subscriberRepository->findByToken($token);

        if ($subscriber === null || !$subscriber->isPending()) {
            return null;
        }

        $subscriber->setStatus(Subscriber::STATUS_SUBSCRIBED);
        $subscriber->setConfirmedAt(new \DateTimeImmutable());
        $subscriber->setToken($this->generateToken());

        $this->subscriberRepository->update($subscriber);
        $this->persistenceManager->persistAll();

        return $subscriber;
    }

    public function unsubscribe(string $token): ?Subscriber
    {
        $subscriber = $this->subscriberRepository->findByToken($token);

        if ($subscriber === null) {
            return null;
        }

        $subscriber->setStatus(Subscriber::STATUS_UNSUBSCRIBED);
        $subscriber->setUnsubscribedAt(new \DateTimeImmutable());
        $subscriber->setToken('');

        $this->subscriberRepository->update($subscriber);
        $this->persistenceManager->persistAll();

        return $subscriber;
    }

    private function generateToken(): string
    {
        return $this->random->generateRandomHexString(32);
    }
}
