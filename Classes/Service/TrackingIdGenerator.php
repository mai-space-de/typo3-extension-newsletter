<?php

declare(strict_types=1);

namespace Maispace\MaiNewsletter\Service;

use TYPO3\CMS\Core\Crypto\Random;

final class TrackingIdGenerator implements TrackingIdGeneratorInterface
{
    private const TOKEN_BYTES = 16;

    private const MAX_ATTEMPTS = 10;

    private array $issued = [];

    public function __construct(
        private readonly Random $random,
    ) {}

    /**
     * @throws \RuntimeException on MAX_ATTEMPTS consecutive collisions (PRNG entropy failure)
     */
    public function generate(int $campaignUid): string
    {
        for ($attempt = 0; $attempt < self::MAX_ATTEMPTS; $attempt++) {
            $id = sprintf('%d-%s', $campaignUid, $this->random->generateRandomHexString(self::TOKEN_BYTES));

            if (!isset($this->issued[$id])) {
                $this->issued[$id] = true;

                return $id;
            }
        }

        throw new \RuntimeException(
            sprintf(
                'TrackingIdGenerator: could not generate a unique ID for campaign %d after %d attempts.',
                $campaignUid,
                self::MAX_ATTEMPTS,
            ),
            1_748_470_800,
        );
    }

    public function issuedCount(): int
    {
        return count($this->issued);
    }

    public function isIssued(string $id): bool
    {
        return isset($this->issued[$id]);
    }

    public function reset(): void
    {
        $this->issued = [];
    }
}
