<?php
declare(strict_types=1);
namespace MaiSpace\Newsletter\Domain\Model;

use TYPO3\CMS\Extbase\Annotation\ORM\Lazy;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

class Newsletter extends AbstractEntity
{
    public const STATUS_DRAFT = 0;
    public const STATUS_SCHEDULED = 1;
    public const STATUS_SENT = 2;

    protected string $subject = '';
    protected string $content = '';
    protected int $status = self::STATUS_DRAFT;
    protected ?\DateTime $scheduledAt = null;
    protected string $statistics = '';

    #[Lazy]
    protected ?ObjectStorage $targetLists = null;

    public function __construct()
    {
        $this->targetLists = new ObjectStorage();
    }

    public function getSubject(): string { return $this->subject; }
    public function setSubject(string $subject): void { $this->subject = $subject; }

    public function getContent(): string { return $this->content; }
    public function setContent(string $content): void { $this->content = $content; }

    public function getStatus(): int { return $this->status; }
    public function setStatus(int $status): void { $this->status = $status; }

    public function getScheduledAt(): ?\DateTime { return $this->scheduledAt; }
    public function setScheduledAt(?\DateTime $scheduledAt): void { $this->scheduledAt = $scheduledAt; }

    public function getStatistics(): array
    {
        if (empty($this->statistics)) {
            return [];
        }
        $decoded = json_decode($this->statistics, true);
        return is_array($decoded) ? $decoded : [];
    }
    public function setStatistics(array $statistics): void
    {
        $this->statistics = json_encode($statistics);
    }

    public function getTargetLists(): ?ObjectStorage { return $this->targetLists; }
    public function setTargetLists(ObjectStorage $targetLists): void { $this->targetLists = $targetLists; }
    public function addTargetList(SubscriberList $subscriberList): void
    {
        $this->targetLists?->attach($subscriberList);
    }
    public function removeTargetList(SubscriberList $subscriberList): void
    {
        $this->targetLists?->detach($subscriberList);
    }
}
