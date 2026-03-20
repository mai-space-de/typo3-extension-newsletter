<?php
declare(strict_types=1);
namespace Maispace\MaiNewsletter\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

class Subscriber extends AbstractEntity
{
    protected string $email = '';
    protected int $feUserUid = 0;
    protected string $interestTags = '';
    protected string $token = '';
    protected bool $confirmed = false;
    protected ?\DateTime $confirmedAt = null;
    protected ?\DateTime $deletedAt = null;

    public function getEmail(): string { return $this->email; }
    public function setEmail(string $email): void { $this->email = $email; }

    public function getFeUserUid(): int { return $this->feUserUid; }
    public function setFeUserUid(int $feUserUid): void { $this->feUserUid = $feUserUid; }

    public function getInterestTags(): string { return $this->interestTags; }
    public function setInterestTags(string $interestTags): void { $this->interestTags = $interestTags; }

    public function getToken(): string { return $this->token; }
    public function setToken(string $token): void { $this->token = $token; }

    public function isConfirmed(): bool { return $this->confirmed; }
    public function setConfirmed(bool $confirmed): void { $this->confirmed = $confirmed; }

    public function getConfirmedAt(): ?\DateTime { return $this->confirmedAt; }
    public function setConfirmedAt(?\DateTime $confirmedAt): void { $this->confirmedAt = $confirmedAt; }

    public function getDeletedAt(): ?\DateTime { return $this->deletedAt; }
    public function setDeletedAt(?\DateTime $deletedAt): void { $this->deletedAt = $deletedAt; }
}
