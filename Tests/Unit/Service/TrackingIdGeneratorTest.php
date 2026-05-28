<?php

declare(strict_types=1);

namespace Maispace\MaiNewsletter\Tests\Unit\Service;

use Maispace\MaiNewsletter\Service\TrackingIdGenerator;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use TYPO3\CMS\Core\Crypto\Random;

final class TrackingIdGeneratorTest extends TestCase
{
    private Random&MockObject $random;
    private TrackingIdGenerator $subject;

    protected function setUp(): void
    {
        $this->random = $this->createMock(Random::class);
        $this->subject = new TrackingIdGenerator($this->random);
    }

    #[Test]
    public function generateReturnsIdWithCampaignUidPrefix(): void
    {
        $this->random->method('generateRandomHexString')->willReturn('aabbccddeeff0011');

        $id = $this->subject->generate(42);

        self::assertStringStartsWith('42-', $id);
    }

    #[Test]
    public function generateIncludesRandomHexPart(): void
    {
        $this->random->method('generateRandomHexString')->willReturn('aabbccddeeff0011');

        $id = $this->subject->generate(1);

        self::assertSame('1-aabbccddeeff0011', $id);
    }

    #[Test]
    public function generateTracksIssuedId(): void
    {
        $this->random->method('generateRandomHexString')->willReturn('aabbccddeeff0011');

        $id = $this->subject->generate(5);

        self::assertTrue($this->subject->isIssued($id));
    }

    #[Test]
    public function issuedCountIncreasesPerGenerate(): void
    {
        $this->random->method('generateRandomHexString')->willReturnOnConsecutiveCalls(
            'aaaa0000bbbb1111',
            'cccc2222dddd3333',
        );

        $this->subject->generate(1);
        $this->subject->generate(1);

        self::assertSame(2, $this->subject->issuedCount());
    }

    #[Test]
    public function generateRetriesOnCollisionAndReturnsUniqueId(): void
    {
        $duplicate = 'aaaa0000bbbb1111';
        $unique = 'cccc2222dddd3333';

        $this->random->method('generateRandomHexString')->willReturnOnConsecutiveCalls(
            $duplicate,
            $duplicate,
            $unique,
        );

        $first = $this->subject->generate(1);
        $second = $this->subject->generate(1);

        self::assertNotSame($first, $second);
        self::assertStringEndsWith($unique, $second);
    }

    #[Test]
    public function generateThrowsAfterMaxAttemptsWithAllCollisions(): void
    {
        $this->random->method('generateRandomHexString')->willReturn('aaaa0000bbbb1111');

        $this->subject->generate(1);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/could not generate a unique ID for campaign 1/');

        for ($i = 0; $i < 10; $i++) {
            $this->subject->generate(1);
        }
    }

    #[Test]
    public function isIssuedReturnsFalseForUnknownId(): void
    {
        self::assertFalse($this->subject->isIssued('99-unknown'));
    }

    #[Test]
    public function resetClearsIssuedRegistry(): void
    {
        $this->random->method('generateRandomHexString')->willReturn('aabbccddeeff0011');
        $this->subject->generate(1);

        $this->subject->reset();

        self::assertSame(0, $this->subject->issuedCount());
    }

    #[Test]
    public function resetAllowsReusingPreviouslyIssuedId(): void
    {
        $this->random->method('generateRandomHexString')->willReturn('aabbccddeeff0011');

        $first = $this->subject->generate(1);
        $this->subject->reset();
        $second = $this->subject->generate(1);

        self::assertSame($first, $second);
    }

    #[Test]
    public function differentCampaignUidsProduceDifferentIds(): void
    {
        $this->random->method('generateRandomHexString')->willReturn('aabbccddeeff0011');

        $idA = $this->subject->generate(1);
        $idB = $this->subject->generate(2);

        self::assertNotSame($idA, $idB);
    }
}
