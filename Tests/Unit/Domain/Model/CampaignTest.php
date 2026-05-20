<?php

declare(strict_types=1);

namespace Maispace\MaiNewsletter\Tests\Unit\Domain\Model;

use Maispace\MaiNewsletter\Domain\Model\Campaign;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class CampaignTest extends TestCase
{
    // ── Status constants ─────────────────────────────────────────────────────

    #[Test]
    public function statusDraftConstantValue(): void
    {
        self::assertSame('draft', Campaign::STATUS_DRAFT);
    }

    #[Test]
    public function statusScheduledConstantValue(): void
    {
        self::assertSame('scheduled', Campaign::STATUS_SCHEDULED);
    }

    #[Test]
    public function statusSentConstantValue(): void
    {
        self::assertSame('sent', Campaign::STATUS_SENT);
    }

    // ── Default values ───────────────────────────────────────────────────────

    #[Test]
    public function defaultTitleIsEmptyString(): void
    {
        $campaign = new Campaign();
        self::assertSame('', $campaign->getTitle());
    }

    #[Test]
    public function defaultSubjectIsEmptyString(): void
    {
        $campaign = new Campaign();
        self::assertSame('', $campaign->getSubject());
    }

    #[Test]
    public function defaultBodyIsEmptyString(): void
    {
        $campaign = new Campaign();
        self::assertSame('', $campaign->getBody());
    }

    #[Test]
    public function defaultStatusIsDraft(): void
    {
        $campaign = new Campaign();
        self::assertSame(Campaign::STATUS_DRAFT, $campaign->getStatus());
    }

    #[Test]
    public function defaultScheduledAtIsNull(): void
    {
        $campaign = new Campaign();
        self::assertNull($campaign->getScheduledAt());
    }

    #[Test]
    public function defaultSentAtIsNull(): void
    {
        $campaign = new Campaign();
        self::assertNull($campaign->getSentAt());
    }

    #[Test]
    public function defaultRecipientCountIsZero(): void
    {
        $campaign = new Campaign();
        self::assertSame(0, $campaign->getRecipientCount());
    }

    // ── title getter / setter ─────────────────────────────────────────────────

    #[Test]
    public function setTitleStoresTheValue(): void
    {
        $campaign = new Campaign();
        $campaign->setTitle('Summer Newsletter');
        self::assertSame('Summer Newsletter', $campaign->getTitle());
    }

    #[Test]
    public function setTitleOverwritesPreviousValue(): void
    {
        $campaign = new Campaign();
        $campaign->setTitle('First title');
        $campaign->setTitle('Second title');
        self::assertSame('Second title', $campaign->getTitle());
    }

    #[Test]
    public function setTitleAcceptsEmptyString(): void
    {
        $campaign = new Campaign();
        $campaign->setTitle('Non-empty');
        $campaign->setTitle('');
        self::assertSame('', $campaign->getTitle());
    }

    // ── subject getter / setter ───────────────────────────────────────────────

    #[Test]
    public function setSubjectStoresTheValue(): void
    {
        $campaign = new Campaign();
        $campaign->setSubject('Our summer activities');
        self::assertSame('Our summer activities', $campaign->getSubject());
    }

    // ── body getter / setter ──────────────────────────────────────────────────

    #[Test]
    public function setBodyStoresTheValue(): void
    {
        $campaign = new Campaign();
        $campaign->setBody('<p>Newsletter content here.</p>');
        self::assertSame('<p>Newsletter content here.</p>', $campaign->getBody());
    }

    // ── status getter / setter ────────────────────────────────────────────────

    #[Test]
    public function setStatusStoresTheValue(): void
    {
        $campaign = new Campaign();
        $campaign->setStatus(Campaign::STATUS_SCHEDULED);
        self::assertSame(Campaign::STATUS_SCHEDULED, $campaign->getStatus());
    }

    #[Test]
    public function setStatusCanTransitionToSent(): void
    {
        $campaign = new Campaign();
        $campaign->setStatus(Campaign::STATUS_SENT);
        self::assertSame(Campaign::STATUS_SENT, $campaign->getStatus());
    }

    // ── scheduledAt getter / setter ───────────────────────────────────────────

    #[Test]
    public function setScheduledAtStoresDateTimeImmutable(): void
    {
        $campaign = new Campaign();
        $date = new \DateTimeImmutable('2025-06-15 10:00:00');
        $campaign->setScheduledAt($date);
        self::assertSame($date, $campaign->getScheduledAt());
    }

    #[Test]
    public function setScheduledAtAcceptsNull(): void
    {
        $campaign = new Campaign();
        $campaign->setScheduledAt(new \DateTimeImmutable());
        $campaign->setScheduledAt(null);
        self::assertNull($campaign->getScheduledAt());
    }

    // ── sentAt getter / setter ────────────────────────────────────────────────

    #[Test]
    public function setSentAtStoresDateTimeImmutable(): void
    {
        $campaign = new Campaign();
        $date = new \DateTimeImmutable('2025-06-15 12:30:00');
        $campaign->setSentAt($date);
        self::assertSame($date, $campaign->getSentAt());
    }

    #[Test]
    public function setSentAtAcceptsNull(): void
    {
        $campaign = new Campaign();
        $campaign->setSentAt(new \DateTimeImmutable());
        $campaign->setSentAt(null);
        self::assertNull($campaign->getSentAt());
    }

    // ── recipientCount getter / setter ────────────────────────────────────────

    #[Test]
    public function setRecipientCountStoresTheValue(): void
    {
        $campaign = new Campaign();
        $campaign->setRecipientCount(250);
        self::assertSame(250, $campaign->getRecipientCount());
    }

    #[Test]
    public function setRecipientCountAcceptsZero(): void
    {
        $campaign = new Campaign();
        $campaign->setRecipientCount(100);
        $campaign->setRecipientCount(0);
        self::assertSame(0, $campaign->getRecipientCount());
    }

    // ── Instance isolation ────────────────────────────────────────────────────

    #[Test]
    public function twoCampaignInstancesAreIndependent(): void
    {
        $campaign1 = new Campaign();
        $campaign2 = new Campaign();
        $campaign1->setTitle('Campaign A');
        self::assertSame('', $campaign2->getTitle());
    }
}
