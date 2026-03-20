<?php
declare(strict_types=1);
namespace MaiSpace\Newsletter\Service;

use MaiSpace\Newsletter\Domain\Model\Newsletter;
use MaiSpace\Newsletter\Domain\Model\Subscriber;
use MaiSpace\Newsletter\Domain\Repository\NewsletterRepository;
use Symfony\Component\Mime\Address;
use TYPO3\CMS\Core\Mail\MailMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface;

class NewsletterSendService
{
    private int $batchSize = 50;

    public function __construct(
        private readonly NewsletterRepository $newsletterRepository,
        private readonly PersistenceManagerInterface $persistenceManager,
    ) {}

    public function sendNewsletter(Newsletter $newsletter): array
    {
        $statistics = $newsletter->getStatistics();
        $statistics['sent'] = $statistics['sent'] ?? 0;
        $statistics['failed'] = $statistics['failed'] ?? 0;
        $statistics['startedAt'] = $statistics['startedAt'] ?? (new \DateTime())->format(\DateTime::ATOM);

        $targetLists = $newsletter->getTargetLists();
        if ($targetLists === null) {
            return $statistics;
        }

        $processedEmails = [];
        foreach ($targetLists as $subscriberList) {
            $subscribers = $subscriberList->getSubscribers();
            if ($subscribers === null) {
                continue;
            }
            $batch = [];
            foreach ($subscribers as $subscriber) {
                if (!$subscriber->isConfirmed()) {
                    continue;
                }
                $email = $subscriber->getEmail();
                if (in_array($email, $processedEmails, true)) {
                    continue;
                }
                $processedEmails[] = $email;
                $batch[] = $subscriber;
                if (count($batch) >= $this->batchSize) {
                    $stats = $this->sendBatch($newsletter, $batch);
                    $statistics['sent'] += $stats['sent'];
                    $statistics['failed'] += $stats['failed'];
                    $batch = [];
                }
            }
            if (!empty($batch)) {
                $stats = $this->sendBatch($newsletter, $batch);
                $statistics['sent'] += $stats['sent'];
                $statistics['failed'] += $stats['failed'];
            }
        }

        $statistics['finishedAt'] = (new \DateTime())->format(\DateTime::ATOM);
        $newsletter->setStatistics($statistics);
        $newsletter->setStatus(Newsletter::STATUS_SENT);
        $this->newsletterRepository->update($newsletter);
        $this->persistenceManager->persistAll();

        return $statistics;
    }

    private function sendBatch(Newsletter $newsletter, array $subscribers): array
    {
        $sent = 0;
        $failed = 0;
        foreach ($subscribers as $subscriber) {
            try {
                $this->sendToSubscriber($newsletter, $subscriber);
                $sent++;
            } catch (\Throwable $e) {
                $failed++;
            }
        }
        return ['sent' => $sent, 'failed' => $failed];
    }

    private function sendToSubscriber(Newsletter $newsletter, Subscriber $subscriber): void
    {
        $mail = GeneralUtility::makeInstance(MailMessage::class);
        $mail->to(new Address($subscriber->getEmail()))
            ->subject($newsletter->getSubject())
            ->html($newsletter->getContent());
        $mail->send();
    }

    public function processScheduledNewsletters(): void
    {
        $newsletters = $this->newsletterRepository->findScheduledForSending();
        foreach ($newsletters as $newsletter) {
            $this->sendNewsletter($newsletter);
        }
    }
}
