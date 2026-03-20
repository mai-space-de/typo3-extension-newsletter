<?php
declare(strict_types=1);
namespace Maispace\MaiNewsletter\Event;

use Maispace\MaiNewsletter\Domain\Model\Subscriber;

final class UnsubscribedEvent
{
    public function __construct(
        private readonly Subscriber $subscriber
    ) {}

    public function getSubscriber(): Subscriber
    {
        return $this->subscriber;
    }
}
