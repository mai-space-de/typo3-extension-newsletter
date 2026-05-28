<?php

declare(strict_types=1);

namespace Maispace\MaiNewsletter\Service;

interface TrackingIdGeneratorInterface
{
    /**
     * @throws \RuntimeException on consecutive collisions (entropy failure)
     */
    public function generate(int $campaignUid): string;

    public function issuedCount(): int;

    public function isIssued(string $id): bool;

    public function reset(): void;
}
