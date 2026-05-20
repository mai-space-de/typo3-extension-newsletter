<?php

declare(strict_types=1);

namespace Maispace\MaiNewsletter\Tests\Unit\Service;

use Maispace\MaiNewsletter\Domain\Model\Subscriber;
use Maispace\MaiNewsletter\Domain\Repository\SubscriberRepository;
use Maispace\MaiNewsletter\Service\SubscriberService;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use TYPO3\CMS\Core\Crypto\Random;
use TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface;

final class SubscriberServiceTest extends TestCase
{
    private SubscriberRepository&MockObject $subscriberRepository;
    private PersistenceManagerInterface&MockObject $persistenceManager;
    private Random&MockObject $random;
    private SubscriberService $subject;

    protected function setUp(): void
    {
        $this->subscriberRepository = $this->createMock(SubscriberRepository::class);
        $this->persistenceManager = $this->createMock(PersistenceManagerInterface::class);
        $this->random = $this->createMock(Random::class);

        $this->random->method('generateRandomHexString')
            ->willReturn('aabbccddeeff00112233445566778899');

        $this->subject = new SubscriberService(
            $this->subscriberRepository,
            $this->persistenceManager,
            $this->random,
        );
    }

    // ── optIn — new subscriber ────────────────────────────────────────────────

    #[Test]
    public function optInCreatesNewSubscriberWhenNoneExists(): void
    {
        $this->subscriberRepository->method('findByEmailAndSite')->willReturn(null);
        $this->subscriberRepository->expects(self::once())->method('add');
        $this->persistenceManager->expects(self::once())->method('persistAll');

        $subscriber = $this->subject->optIn('new@example.com', 'bgm-pulheim', 5);

        self::assertInstanceOf(Subscriber::class, $subscriber);
    }

    #[Test]
    public function optInNormalisesEmailToLowerCase(): void
    {
        $this->subscriberRepository->method('findByEmailAndSite')->willReturn(null);
        $this->subscriberRepository->method('add');
        $this->persistenceManager->method('persistAll');

        $subscriber = $this->subject->optIn('USER@EXAMPLE.COM', 'bgm-pulheim', 5);

        self::assertSame('user@example.com', $subscriber->getEmail());
    }

    #[Test]
    public function optInTrimsWhitespaceFromEmail(): void
    {
        $this->subscriberRepository->method('findByEmailAndSite')->willReturn(null);
        $this->subscriberRepository->method('add');
        $this->persistenceManager->method('persistAll');

        $subscriber = $this->subject->optIn('  user@example.com  ', 'bgm-pulheim', 5);

        self::assertSame('user@example.com', $subscriber->getEmail());
    }

    #[Test]
    public function optInSetsStatusToPending(): void
    {
        $this->subscriberRepository->method('findByEmailAndSite')->willReturn(null);
        $this->subscriberRepository->method('add');
        $this->persistenceManager->method('persistAll');

        $subscriber = $this->subject->optIn('new@example.com', 'bgm-pulheim', 5);

        self::assertSame(Subscriber::STATUS_PENDING, $subscriber->getStatus());
    }

    #[Test]
    public function optInAssignsToken(): void
    {
        $this->subscriberRepository->method('findByEmailAndSite')->willReturn(null);
        $this->subscriberRepository->method('add');
        $this->persistenceManager->method('persistAll');

        $subscriber = $this->subject->optIn('new@example.com', 'bgm-pulheim', 5);

        self::assertNotSame('', $subscriber->getToken());
    }

    #[Test]
    public function optInSetsStoragePid(): void
    {
        $this->subscriberRepository->method('findByEmailAndSite')->willReturn(null);
        $this->subscriberRepository->method('add');
        $this->persistenceManager->method('persistAll');

        $subscriber = $this->subject->optIn('new@example.com', 'bgm-pulheim', 42);

        self::assertSame(42, $subscriber->getPid());
    }

    #[Test]
    public function optInAssociatesFeUserWhenProvided(): void
    {
        $this->subscriberRepository->method('findByEmailAndSite')->willReturn(null);
        $this->subscriberRepository->method('add');
        $this->persistenceManager->method('persistAll');

        $subscriber = $this->subject->optIn('new@example.com', 'bgm-pulheim', 5, 77);

        self::assertSame(77, $subscriber->getFeUser());
    }

    // ── optIn — already subscribed ────────────────────────────────────────────

    #[Test]
    public function optInReturnsExistingSubscriberWhenAlreadySubscribed(): void
    {
        $existing = new Subscriber();
        $existing->setEmail('already@example.com');
        $existing->setStatus(Subscriber::STATUS_SUBSCRIBED);

        $this->subscriberRepository->method('findByEmailAndSite')->willReturn($existing);
        $this->subscriberRepository->expects(self::never())->method('add');

        $result = $this->subject->optIn('already@example.com', 'bgm-pulheim', 5);

        self::assertSame($existing, $result);
    }

    // ── optIn — re-opt-in of pending / unsubscribed ───────────────────────────

    #[Test]
    public function optInUpdatesExistingPendingSubscriber(): void
    {
        $existing = new Subscriber();
        $existing->setEmail('pending@example.com');
        $existing->setStatus(Subscriber::STATUS_PENDING);
        // Simulate a persisted record by assigning a UID via the internal property setter.
        $existing->_setProperty('uid', 7);

        $this->subscriberRepository->method('findByEmailAndSite')->willReturn($existing);
        $this->subscriberRepository->expects(self::never())->method('add');
        $this->subscriberRepository->expects(self::once())->method('update');
        $this->persistenceManager->expects(self::once())->method('persistAll');

        $this->subject->optIn('pending@example.com', 'bgm-pulheim', 5);
    }

    #[Test]
    public function optInResetsConfirmedAtForResubscribingSubscriber(): void
    {
        $existing = new Subscriber();
        $existing->setStatus(Subscriber::STATUS_UNSUBSCRIBED);
        $existing->setConfirmedAt(new \DateTimeImmutable('2024-01-01'));
        // Simulate a persisted record so the update branch is taken.
        $existing->_setProperty('uid', 3);

        $this->subscriberRepository->method('findByEmailAndSite')->willReturn($existing);
        $this->subscriberRepository->method('update');
        $this->persistenceManager->method('persistAll');

        $this->subject->optIn('old@example.com', 'bgm-pulheim', 5);

        self::assertNull($existing->getConfirmedAt());
    }

    // ── confirm ───────────────────────────────────────────────────────────────

    #[Test]
    public function confirmReturnsNullForUnknownToken(): void
    {
        $this->subscriberRepository->method('findByToken')->willReturn(null);

        $result = $this->subject->confirm('unknown-token');

        self::assertNull($result);
    }

    #[Test]
    public function confirmReturnsNullWhenSubscriberIsAlreadySubscribed(): void
    {
        $existing = new Subscriber();
        $existing->setStatus(Subscriber::STATUS_SUBSCRIBED);

        $this->subscriberRepository->method('findByToken')->willReturn($existing);

        $result = $this->subject->confirm('some-token');

        self::assertNull($result);
    }

    #[Test]
    public function confirmChangesStatusToSubscribed(): void
    {
        $subscriber = new Subscriber();
        $subscriber->setStatus(Subscriber::STATUS_PENDING);
        $subscriber->setToken('valid-token');

        $this->subscriberRepository->method('findByToken')->willReturn($subscriber);
        $this->subscriberRepository->expects(self::once())->method('update');
        $this->persistenceManager->expects(self::once())->method('persistAll');

        $this->subject->confirm('valid-token');

        self::assertSame(Subscriber::STATUS_SUBSCRIBED, $subscriber->getStatus());
    }

    #[Test]
    public function confirmSetsConfirmedAt(): void
    {
        $subscriber = new Subscriber();
        $subscriber->setStatus(Subscriber::STATUS_PENDING);

        $this->subscriberRepository->method('findByToken')->willReturn($subscriber);
        $this->subscriberRepository->method('update');
        $this->persistenceManager->method('persistAll');

        $before = new \DateTimeImmutable();
        $this->subject->confirm('some-token');

        self::assertNotNull($subscriber->getConfirmedAt());
        self::assertGreaterThanOrEqual($before->getTimestamp(), $subscriber->getConfirmedAt()->getTimestamp());
    }

    #[Test]
    public function confirmRotatesToken(): void
    {
        $subscriber = new Subscriber();
        $subscriber->setStatus(Subscriber::STATUS_PENDING);
        $subscriber->setToken('old-token');

        $this->subscriberRepository->method('findByToken')->willReturn($subscriber);
        $this->subscriberRepository->method('update');
        $this->persistenceManager->method('persistAll');

        $this->subject->confirm('old-token');

        self::assertNotSame('old-token', $subscriber->getToken());
    }

    #[Test]
    public function confirmReturnsTheSubscriber(): void
    {
        $subscriber = new Subscriber();
        $subscriber->setStatus(Subscriber::STATUS_PENDING);

        $this->subscriberRepository->method('findByToken')->willReturn($subscriber);
        $this->subscriberRepository->method('update');
        $this->persistenceManager->method('persistAll');

        $result = $this->subject->confirm('some-token');

        self::assertSame($subscriber, $result);
    }

    // ── unsubscribe ───────────────────────────────────────────────────────────

    #[Test]
    public function unsubscribeReturnsNullForUnknownToken(): void
    {
        $this->subscriberRepository->method('findByToken')->willReturn(null);

        $result = $this->subject->unsubscribe('unknown-token');

        self::assertNull($result);
    }

    #[Test]
    public function unsubscribeChangesStatusToUnsubscribed(): void
    {
        $subscriber = new Subscriber();
        $subscriber->setStatus(Subscriber::STATUS_SUBSCRIBED);

        $this->subscriberRepository->method('findByToken')->willReturn($subscriber);
        $this->subscriberRepository->expects(self::once())->method('update');
        $this->persistenceManager->expects(self::once())->method('persistAll');

        $this->subject->unsubscribe('some-token');

        self::assertSame(Subscriber::STATUS_UNSUBSCRIBED, $subscriber->getStatus());
    }

    #[Test]
    public function unsubscribeSetsUnsubscribedAt(): void
    {
        $subscriber = new Subscriber();
        $subscriber->setStatus(Subscriber::STATUS_SUBSCRIBED);

        $this->subscriberRepository->method('findByToken')->willReturn($subscriber);
        $this->subscriberRepository->method('update');
        $this->persistenceManager->method('persistAll');

        $before = new \DateTimeImmutable();
        $this->subject->unsubscribe('some-token');

        self::assertNotNull($subscriber->getUnsubscribedAt());
        self::assertGreaterThanOrEqual($before->getTimestamp(), $subscriber->getUnsubscribedAt()->getTimestamp());
    }

    #[Test]
    public function unsubscribeClearsToken(): void
    {
        $subscriber = new Subscriber();
        $subscriber->setStatus(Subscriber::STATUS_SUBSCRIBED);
        $subscriber->setToken('active-token');

        $this->subscriberRepository->method('findByToken')->willReturn($subscriber);
        $this->subscriberRepository->method('update');
        $this->persistenceManager->method('persistAll');

        $this->subject->unsubscribe('active-token');

        self::assertSame('', $subscriber->getToken());
    }

    #[Test]
    public function unsubscribeReturnsTheSubscriber(): void
    {
        $subscriber = new Subscriber();
        $subscriber->setStatus(Subscriber::STATUS_SUBSCRIBED);

        $this->subscriberRepository->method('findByToken')->willReturn($subscriber);
        $this->subscriberRepository->method('update');
        $this->persistenceManager->method('persistAll');

        $result = $this->subject->unsubscribe('some-token');

        self::assertSame($subscriber, $result);
    }
}
