<?php

declare(strict_types=1);

namespace Maispace\MaiNewsletter\Service;

use Maispace\MaiMail\Service\MailQueueInterface;
use Maispace\MaiNewsletter\Domain\Model\Campaign;
use Maispace\MaiNewsletter\Domain\Model\Subscriber;
use Maispace\MaiNewsletter\Domain\Repository\CampaignRepository;
use Maispace\MaiNewsletter\Domain\Repository\SubscriberRepository;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface;

final class CampaignDispatcher
{
    private ?int $unsubscribePageId = null;

    public function __construct(
        private readonly CampaignRepository $campaignRepository,
        private readonly SubscriberRepository $subscriberRepository,
        private readonly MailQueueInterface $mailService,
        private readonly PersistenceManagerInterface $persistenceManager,
        private readonly SiteFinder $siteFinder,
        private readonly ConnectionPool $connectionPool,
    ) {}

    public function dispatch(Campaign $campaign): int
    {
        if ($campaign->getStatus() === Campaign::STATUS_SENT) {
            throw new \InvalidArgumentException(
                sprintf('Campaign "%s" has already been sent.', $campaign->getTitle()),
                1_748_383_200,
            );
        }

        $subscribers = $this->subscriberRepository->findSubscribed();
        $count = 0;

        foreach ($subscribers as $subscriber) {
            $headers = $this->buildUnsubscribeHeaders($subscriber);
            $this->mailService->queue(
                $subscriber->getEmail(),
                $campaign->getSubject(),
                $campaign->getBody(),
                headers: $headers,
            );
            $count++;
        }

        $campaign->setRecipientCount($count);
        $campaign->setSentAt(new \DateTimeImmutable());
        $campaign->setStatus(Campaign::STATUS_SENT);

        $this->campaignRepository->update($campaign);
        $this->persistenceManager->persistAll();

        return $count;
    }

    /**
     * @return array<string, string>
     */
    private function buildUnsubscribeHeaders(Subscriber $subscriber): array
    {
        $url = $this->buildUnsubscribeUrl($subscriber);

        if ($url === '') {
            return [];
        }

        return [
            'List-Unsubscribe' => sprintf('<%s>', $url),
            'List-Unsubscribe-Post' => 'List-Unsubscribe=One-Click',
        ];
    }

    private function buildUnsubscribeUrl(Subscriber $subscriber): string
    {
        $pageId = $this->resolveUnsubscribePageId();

        if ($pageId === 0) {
            return '';
        }

        try {
            $site = $this->siteFinder->getSiteByIdentifier($subscriber->getSite());
        } catch (\Throwable) {
            return '';
        }

        try {
            $uri = $site->getRouter()->generateUri(
                $pageId,
                [
                    'tx_mainewsletter_newsletter[action]' => 'unsubscribe',
                    'tx_mainewsletter_newsletter[token]' => $subscriber->getToken(),
                ],
            );

            return (string) $uri;
        } catch (\Throwable) {
            return '';
        }
    }

    private function resolveUnsubscribePageId(): int
    {
        if ($this->unsubscribePageId !== null) {
            return $this->unsubscribePageId;
        }

        try {
            $queryBuilder = $this->connectionPool->getQueryBuilderForTable('tt_content');
            $row = $queryBuilder
                ->select('pid')
                ->from('tt_content')
                ->where(
                    $queryBuilder->expr()->eq(
                        'CType',
                        $queryBuilder->createNamedParameter('mainewsletter_newsletter'),
                    ),
                )
                ->setMaxResults(1)
                ->executeQuery()
                ->fetchAssociative();
        } catch (\Throwable) {
            return 0;
        }

        $this->unsubscribePageId = $row !== false ? (int) $row['pid'] : 0;

        return $this->unsubscribePageId;
    }
}
