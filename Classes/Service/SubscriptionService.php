<?php
declare(strict_types=1);
namespace Maispace\MaiNewsletter\Service;

use Maispace\MaiNewsletter\Domain\Model\Subscriber;
use Maispace\MaiNewsletter\Domain\Model\SubscriberList;
use Maispace\MaiNewsletter\Domain\Repository\SubscriberRepository;
use Maispace\MaiNewsletter\Event\SubscribedEvent;
use Maispace\MaiNewsletter\Event\UnsubscribedEvent;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Mime\Address;
use TYPO3\CMS\Core\Mail\MailMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface;

class SubscriptionService
{
    public function __construct(
        private readonly SubscriberRepository $subscriberRepository,
        private readonly PersistenceManagerInterface $persistenceManager,
        private readonly EventDispatcherInterface $eventDispatcher,
    ) {}

    public function subscribe(string $email, SubscriberList $list, string $interestTags = ''): Subscriber
    {
        $subscriber = $this->subscriberRepository->findOneByEmail($email);
        if ($subscriber === null) {
            $subscriber = new Subscriber();
            $subscriber->setEmail($email);
            $subscriber->setToken(bin2hex(random_bytes(32)));
            $subscriber->setInterestTags($interestTags);
            $subscriber->setConfirmed(false);
            $this->subscriberRepository->add($subscriber);
            $this->persistenceManager->persistAll();
        }
        $list->addSubscriber($subscriber);
        $this->persistenceManager->persistAll();
        return $subscriber;
    }

    public function sendConfirmationEmail(Subscriber $subscriber, string $confirmUrl): void
    {
        $mail = GeneralUtility::makeInstance(MailMessage::class);
        $mail->to(new Address($subscriber->getEmail()))
            ->subject('Please confirm your subscription')
            ->html(
                '<p>Thank you for subscribing!</p>'
                . '<p><a href="' . htmlspecialchars($confirmUrl) . '">Click here to confirm your subscription</a></p>'
                . '<p>Or copy this link: ' . htmlspecialchars($confirmUrl) . '</p>'
            );
        $mail->send();
    }

    public function confirm(string $token): ?Subscriber
    {
        $subscriber = $this->subscriberRepository->findByToken($token);
        if ($subscriber === null) {
            return null;
        }
        $subscriber->setConfirmed(true);
        $subscriber->setConfirmedAt(new \DateTime());
        $this->subscriberRepository->update($subscriber);
        $this->persistenceManager->persistAll();
        $this->eventDispatcher->dispatch(new SubscribedEvent($subscriber));
        return $subscriber;
    }

    public function unsubscribe(string $token): ?Subscriber
    {
        $subscriber = $this->subscriberRepository->findByToken($token);
        if ($subscriber === null) {
            return null;
        }
        $subscriber->setConfirmed(false);
        $subscriber->setDeletedAt(new \DateTime());
        $this->subscriberRepository->update($subscriber);
        $this->persistenceManager->persistAll();
        $this->eventDispatcher->dispatch(new UnsubscribedEvent($subscriber));
        return $subscriber;
    }

    public function anonymize(Subscriber $subscriber): void
    {
        $subscriber->setEmail('anonymized_' . hash('sha256', $subscriber->getEmail()) . '@deleted.invalid');
        $subscriber->setToken('');
        $subscriber->setInterestTags('');
        $subscriber->setFeUserUid(0);
        $this->subscriberRepository->update($subscriber);
        $this->persistenceManager->persistAll();
    }
}
