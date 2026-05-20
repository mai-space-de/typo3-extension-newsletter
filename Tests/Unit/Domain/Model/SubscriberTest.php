<?php

declare(strict_types=1);

namespace Maispace\MaiNewsletter\Tests\Unit\Domain\Model;

use Maispace\MaiNewsletter\Domain\Model\Subscriber;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class SubscriberTest extends TestCase
{
    // ── Status constants ─────────────────────────────────────────────────────

    #[Test]
    public function statusPendingConstantValue(): void
    {
        self::assertSame('pending', Subscriber::STATUS_PENDING);
    }

    #[Test]
    public function statusSubscribedConstantValue(): void
    {
        self::assertSame('subscribed', Subscriber::STATUS_SUBSCRIBED);
    }

    #[Test]
    public function statusUnsubscribedConstantValue(): void
    {
        self::assertSame('unsubscribed', Subscriber::STATUS_UNSUBSCRIBED);
    }

    // ── Default values ───────────────────────────────────────────────────────

    #[Test]
    public function defaultEmailIsEmptyString(): void
    {
        $subscriber = new Subscriber();
        self::assertSame('', $subscriber->getEmail());
    }

    #[Test]
    public function defaultStatusIsPending(): void
    {
        $subscriber = new Subscriber();
        self::assertSame(Subscriber::STATUS_PENDING, $subscriber->getStatus());
    }

    #[Test]
    public function defaultTokenIsEmptyString(): void
    {
        $subscriber = new Subscriber();
        self::assertSame('', $subscriber->getToken());
    }

    #[Test]
    public function defaultConfirmedAtIsNull(): void
    {
        $subscriber = new Subscriber();
        self::assertNull($subscriber->getConfirmedAt());
    }

    #[Test]
    public function defaultUnsubscribedAtIsNull(): void
    {
        $subscriber = new Subscriber();
        self::assertNull($subscriber->getUnsubscribedAt());
    }

    #[Test]
    public function defaultSiteIsEmptyString(): void
    {
        $subscriber = new Subscriber();
        self::assertSame('', $subscriber->getSite());
    }

    #[Test]
    public function defaultFeUserIsZero(): void
    {
        $subscriber = new Subscriber();
        self::assertSame(0, $subscriber->getFeUser());
    }

    // ── Status helper methods ─────────────────────────────────────────────────

    #[Test]
    public function isPendingReturnsTrueByDefault(): void
    {
        $subscriber = new Subscriber();
        self::assertTrue($subscriber->isPending());
    }

    #[Test]
    public function isSubscribedReturnsFalseByDefault(): void
    {
        $subscriber = new Subscriber();
        self::assertFalse($subscriber->isSubscribed());
    }

    #[Test]
    public function isPendingReturnsFalseWhenSubscribed(): void
    {
        $subscriber = new Subscriber();
        $subscriber->setStatus(Subscriber::STATUS_SUBSCRIBED);
        self::assertFalse($subscriber->isPending());
    }

    #[Test]
    public function isSubscribedReturnsTrueWhenStatusIsSubscribed(): void
    {
        $subscriber = new Subscriber();
        $subscriber->setStatus(Subscriber::STATUS_SUBSCRIBED);
        self::assertTrue($subscriber->isSubscribed());
    }

    #[Test]
    public function isSubscribedReturnsFalseWhenUnsubscribed(): void
    {
        $subscriber = new Subscriber();
        $subscriber->setStatus(Subscriber::STATUS_UNSUBSCRIBED);
        self::assertFalse($subscriber->isSubscribed());
    }

    #[Test]
    public function isPendingReturnsFalseWhenUnsubscribed(): void
    {
        $subscriber = new Subscriber();
        $subscriber->setStatus(Subscriber::STATUS_UNSUBSCRIBED);
        self::assertFalse($subscriber->isPending());
    }

    // ── email getter / setter ─────────────────────────────────────────────────

    #[Test]
    public function setEmailStoresTheValue(): void
    {
        $subscriber = new Subscriber();
        $subscriber->setEmail('test@example.com');
        self::assertSame('test@example.com', $subscriber->getEmail());
    }

    #[Test]
    public function setEmailOverwritesPreviousValue(): void
    {
        $subscriber = new Subscriber();
        $subscriber->setEmail('first@example.com');
        $subscriber->setEmail('second@example.com');
        self::assertSame('second@example.com', $subscriber->getEmail());
    }

    // ── status getter / setter ───────────────────────────────────────────────

    #[Test]
    public function setStatusStoresTheValue(): void
    {
        $subscriber = new Subscriber();
        $subscriber->setStatus(Subscriber::STATUS_SUBSCRIBED);
        self::assertSame(Subscriber::STATUS_SUBSCRIBED, $subscriber->getStatus());
    }

    // ── token getter / setter ────────────────────────────────────────────────

    #[Test]
    public function setTokenStoresTheValue(): void
    {
        $subscriber = new Subscriber();
        $subscriber->setToken('abc123def456');
        self::assertSame('abc123def456', $subscriber->getToken());
    }

    #[Test]
    public function setTokenAcceptsEmptyString(): void
    {
        $subscriber = new Subscriber();
        $subscriber->setToken('some-token');
        $subscriber->setToken('');
        self::assertSame('', $subscriber->getToken());
    }

    // ── confirmedAt getter / setter ──────────────────────────────────────────

    #[Test]
    public function setConfirmedAtStoresDateTimeImmutable(): void
    {
        $subscriber = new Subscriber();
        $now = new \DateTimeImmutable();
        $subscriber->setConfirmedAt($now);
        self::assertSame($now, $subscriber->getConfirmedAt());
    }

    #[Test]
    public function setConfirmedAtAcceptsNull(): void
    {
        $subscriber = new Subscriber();
        $subscriber->setConfirmedAt(new \DateTimeImmutable());
        $subscriber->setConfirmedAt(null);
        self::assertNull($subscriber->getConfirmedAt());
    }

    // ── unsubscribedAt getter / setter ───────────────────────────────────────

    #[Test]
    public function setUnsubscribedAtStoresDateTimeImmutable(): void
    {
        $subscriber = new Subscriber();
        $date = new \DateTimeImmutable('2025-01-15');
        $subscriber->setUnsubscribedAt($date);
        self::assertSame($date, $subscriber->getUnsubscribedAt());
    }

    #[Test]
    public function setUnsubscribedAtAcceptsNull(): void
    {
        $subscriber = new Subscriber();
        $subscriber->setUnsubscribedAt(new \DateTimeImmutable());
        $subscriber->setUnsubscribedAt(null);
        self::assertNull($subscriber->getUnsubscribedAt());
    }

    // ── site getter / setter ─────────────────────────────────────────────────

    #[Test]
    public function setSiteStoresTheValue(): void
    {
        $subscriber = new Subscriber();
        $subscriber->setSite('bgm-pulheim');
        self::assertSame('bgm-pulheim', $subscriber->getSite());
    }

    // ── feUser getter / setter ───────────────────────────────────────────────

    #[Test]
    public function setFeUserStoresTheValue(): void
    {
        $subscriber = new Subscriber();
        $subscriber->setFeUser(42);
        self::assertSame(42, $subscriber->getFeUser());
    }

    // ── Instance isolation ────────────────────────────────────────────────────

    #[Test]
    public function twoSubscriberInstancesAreIndependent(): void
    {
        $subscriber1 = new Subscriber();
        $subscriber2 = new Subscriber();
        $subscriber1->setEmail('one@example.com');
        self::assertSame('', $subscriber2->getEmail());
    }
}
