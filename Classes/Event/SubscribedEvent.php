<?php
declare(strict_types=1);
namespace MaiSpace\Newsletter\Event;

use MaiSpace\Newsletter\Domain\Model\Subscriber;

final class SubscribedEvent
{
    public function __construct(
        private readonly Subscriber $subscriber
    ) {}

    public function getSubscriber(): Subscriber
    {
        return $this->subscriber;
    }
}
