<?php

declare(strict_types=1);

namespace Maispace\MaiNewsletter\Tests\Unit\Domain\Repository;

use Maispace\MaiNewsletter\Domain\Repository\CampaignRepository;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;

final class CampaignRepositoryTest extends TestCase
{
    #[Test]
    public function repositoryExtendsTYPO3BaseRepository(): void
    {
        self::assertTrue(
            is_subclass_of(CampaignRepository::class, Repository::class),
            CampaignRepository::class . ' must extend ' . Repository::class,
        );
    }

    #[Test]
    public function defaultOrderingsSortByCreationDateDescending(): void
    {
        $reflection = new \ReflectionClass(CampaignRepository::class);
        $defaults = $reflection->getDefaultProperties();

        self::assertArrayHasKey('defaultOrderings', $defaults);
        self::assertIsArray($defaults['defaultOrderings']);
        self::assertArrayHasKey('crdate', $defaults['defaultOrderings']);
        self::assertSame(QueryInterface::ORDER_DESCENDING, $defaults['defaultOrderings']['crdate']);
    }

    #[Test]
    public function defaultOrderingsContainExactlyOneSortKey(): void
    {
        $reflection = new \ReflectionClass(CampaignRepository::class);
        $defaults = $reflection->getDefaultProperties();

        self::assertCount(1, $defaults['defaultOrderings']);
    }
}
