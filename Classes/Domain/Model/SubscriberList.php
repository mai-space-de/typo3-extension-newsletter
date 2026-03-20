<?php
declare(strict_types=1);
namespace MaiSpace\Newsletter\Domain\Model;

use TYPO3\CMS\Extbase\Annotation\ORM\Lazy;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

class SubscriberList extends AbstractEntity
{
    protected string $name = '';
    protected string $description = '';
    protected string $interestTag = '';

    #[Lazy]
    protected ?ObjectStorage $subscribers = null;

    public function __construct()
    {
        $this->subscribers = new ObjectStorage();
    }

    public function getName(): string { return $this->name; }
    public function setName(string $name): void { $this->name = $name; }

    public function getDescription(): string { return $this->description; }
    public function setDescription(string $description): void { $this->description = $description; }

    public function getInterestTag(): string { return $this->interestTag; }
    public function setInterestTag(string $interestTag): void { $this->interestTag = $interestTag; }

    public function getSubscribers(): ?ObjectStorage { return $this->subscribers; }
    public function setSubscribers(ObjectStorage $subscribers): void { $this->subscribers = $subscribers; }

    public function addSubscriber(Subscriber $subscriber): void
    {
        $this->subscribers?->attach($subscriber);
    }

    public function removeSubscriber(Subscriber $subscriber): void
    {
        $this->subscribers?->detach($subscriber);
    }
}
