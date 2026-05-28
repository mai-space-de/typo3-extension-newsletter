<?php

declare(strict_types=1);

namespace Maispace\MaiNewsletter\Tests\Unit\Service;

use Maispace\MaiMail\Service\MailQueueInterface;
use Maispace\MaiNewsletter\Domain\Model\Campaign;
use Maispace\MaiNewsletter\Domain\Model\Subscriber;
use Maispace\MaiNewsletter\Domain\Repository\CampaignRepository;
use Maispace\MaiNewsletter\Domain\Repository\SubscriberRepository;
use Maispace\MaiNewsletter\Service\CampaignDispatcher;
use Maispace\MaiNewsletter\Service\TrackingIdGeneratorInterface;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Expression\ExpressionBuilder;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Http\Uri;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Routing\PageRouter;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;

final class CampaignDispatcherTest extends TestCase
{
    private CampaignRepository&MockObject $campaignRepository;
    private SubscriberRepository&MockObject $subscriberRepository;
    private MailQueueInterface&MockObject $mailService;
    private PersistenceManagerInterface&MockObject $persistenceManager;
    private SiteFinder&MockObject $siteFinder;
    private ConnectionPool&MockObject $connectionPool;
    private TrackingIdGeneratorInterface&MockObject $trackingIdGenerator;
    private CampaignDispatcher $subject;

    protected function setUp(): void
    {
        $this->campaignRepository = $this->createMock(CampaignRepository::class);
        $this->subscriberRepository = $this->createMock(SubscriberRepository::class);
        $this->mailService = $this->createMock(MailQueueInterface::class);
        $this->persistenceManager = $this->createMock(PersistenceManagerInterface::class);
        $this->siteFinder = $this->createMock(SiteFinder::class);
        $this->connectionPool = $this->createMock(ConnectionPool::class);
        $this->trackingIdGenerator = $this->createMock(TrackingIdGeneratorInterface::class);
        $this->trackingIdGenerator->method('generate')->willReturnCallback(
            static fn(int $uid): string => sprintf('%d-testtrackingid', $uid),
        );

        $this->subject = new CampaignDispatcher(
            $this->campaignRepository,
            $this->subscriberRepository,
            $this->mailService,
            $this->persistenceManager,
            $this->siteFinder,
            $this->connectionPool,
            $this->trackingIdGenerator,
        );
    }

    private function makeSubscriber(string $email): Subscriber
    {
        $subscriber = new Subscriber();
        $subscriber->setEmail($email);
        $subscriber->setStatus(Subscriber::STATUS_SUBSCRIBED);
        return $subscriber;
    }

    private function makeQueryResult(array $items): QueryResultInterface&MockObject
    {
        $result = $this->createMock(QueryResultInterface::class);
        $iterator = new \ArrayIterator($items);
        $result->method('current')->willReturnCallback(fn() => $iterator->current());
        $result->method('next')->willReturnCallback(fn() => $iterator->next());
        $result->method('key')->willReturnCallback(fn() => $iterator->key());
        $result->method('valid')->willReturnCallback(fn() => $iterator->valid());
        $result->method('rewind')->willReturnCallback(fn() => $iterator->rewind());
        $result->method('count')->willReturn(count($items));
        return $result;
    }

    #[Test]
    public function dispatchQueuesOneMailPerSubscriber(): void
    {
        $campaign = new Campaign();
        $campaign->setTitle('Test Campaign');
        $campaign->setSubject('Hello');
        $campaign->setBody('<p>Body</p>');
        $campaign->setStatus(Campaign::STATUS_DRAFT);

        $subscribers = [
            $this->makeSubscriber('a@example.com'),
            $this->makeSubscriber('b@example.com'),
        ];

        $this->subscriberRepository
            ->method('findSubscribed')
            ->willReturn($this->makeQueryResult($subscribers));

        $this->mailService
            ->expects(self::exactly(2))
            ->method('queue')
            ->with(
                self::logicalOr(self::equalTo('a@example.com'), self::equalTo('b@example.com')),
                'Hello',
                '<p>Body</p>',
                self::anything(),
                self::anything(),
            );

        $this->campaignRepository->expects(self::once())->method('update');
        $this->persistenceManager->expects(self::once())->method('persistAll');

        $count = $this->subject->dispatch($campaign);

        self::assertSame(2, $count);
    }

    #[Test]
    public function dispatchQueuesWithUnsubscribeHeaders(): void
    {
        $campaign = new Campaign();
        $campaign->setTitle('Test Campaign');
        $campaign->setSubject('Hello');
        $campaign->setBody('<p>Body</p>');
        $campaign->setStatus(Campaign::STATUS_DRAFT);

        $subscriber = $this->makeSubscriber('a@example.com');
        $subscriber->setSite('bgm-pulheim');
        $subscriber->setToken('test-token-123');

        $this->subscriberRepository
            ->method('findSubscribed')
            ->willReturn($this->makeQueryResult([$subscriber]));

        // Mock ConnectionPool → QueryBuilder to return a newsletter plugin page
        $queryBuilder = $this->createMock(QueryBuilder::class);
        $queryBuilder->method('select')->with('pid')->willReturnSelf();
        $queryBuilder->method('from')->with('tt_content')->willReturnSelf();
        $queryBuilder->method('where')->willReturnSelf();
        $queryBuilder->method('setMaxResults')->with(1)->willReturnSelf();
        $queryBuilder->method('executeQuery')->willReturnSelf();
        $queryBuilder->method('fetchAssociative')->willReturn(['pid' => 42]);

        $exprBuilder = $this->createMock(ExpressionBuilder::class);
        $exprBuilder->method('eq')->willReturn('CType = :dcValue1');
        $queryBuilder->method('expr')->willReturn($exprBuilder);
        $queryBuilder->method('createNamedParameter')->willReturn(':dcValue1');

        // Properly mock the full QueryBuilder chain
        $queryBuilder = $this->createMock(QueryBuilder::class);
        $queryBuilder->method('select')->willReturnSelf();
        $queryBuilder->method('from')->willReturnSelf();
        $queryBuilder->method('where')->willReturnSelf();
        $queryBuilder->method('setMaxResults')->willReturnSelf();

        $result = $this->createMock(\Doctrine\DBAL\Result::class);
        $result->method('fetchAssociative')->willReturn(['pid' => 42]);
        $queryBuilder->method('executeQuery')->willReturn($result);
        $exprBuilder = $this->createMock(ExpressionBuilder::class);
        $exprBuilder->method('eq')->willReturn('CType = :dcValue1');
        $queryBuilder->method('expr')->willReturn($exprBuilder);
        $queryBuilder->method('createNamedParameter')->willReturn(':dcValue1');

        $this->connectionPool
            ->method('getQueryBuilderForTable')
            ->willReturn($queryBuilder);

        // Mock SiteFinder → Site → PageRouter → Uri
        $site = $this->createMock(Site::class);
        $router = $this->createMock(PageRouter::class);
        $uri = new Uri('https://example.com/newsletter/unsubscribe?tx_mainewsletter_newsletter%5Baction%5D=unsubscribe&tx_mainewsletter_newsletter%5Btoken%5D=test-token-123');

        $this->siteFinder
            ->method('getSiteByIdentifier')
            ->willReturn($site);

        $site->method('getRouter')->willReturn($router);

        $router
            ->method('generateUri')
            ->willReturn($uri);

        $this->mailService
            ->expects(self::once())
            ->method('queue')
            ->willReturnCallback(function (string $recipient, string $subject, string $htmlBody, ?\DateTimeInterface $scheduledAt, array $headers) {
                self::assertSame('a@example.com', $recipient);
                self::assertSame('Hello', $subject);
                self::assertSame('<p>Body</p>', $htmlBody);
                self::assertSame([
                    'List-Unsubscribe' => '<https://example.com/newsletter/unsubscribe?tx_mainewsletter_newsletter%5Baction%5D=unsubscribe&tx_mainewsletter_newsletter%5Btoken%5D=test-token-123>',
                    'List-Unsubscribe-Post' => 'List-Unsubscribe=One-Click',
                    'X-Campaign-Tracking-Id' => '0-testtrackingid',
                ], $headers);
            });

        $this->campaignRepository->method('update');
        $this->persistenceManager->method('persistAll');

        $this->subject->dispatch($campaign);
    }

    #[Test]
    public function dispatchMarksCampaignAsSent(): void
    {
        $campaign = new Campaign();
        $campaign->setStatus(Campaign::STATUS_SCHEDULED);
        $campaign->setSubject('Subject');
        $campaign->setBody('Body');
        $campaign->setTitle('Campaign');

        $this->subscriberRepository
            ->method('findSubscribed')
            ->willReturn($this->makeQueryResult([$this->makeSubscriber('x@example.com')]));

        $this->mailService->method('queue');
        $this->campaignRepository->method('update');
        $this->persistenceManager->method('persistAll');

        $this->subject->dispatch($campaign);

        self::assertSame(Campaign::STATUS_SENT, $campaign->getStatus());
    }

    #[Test]
    public function dispatchSetsRecipientCount(): void
    {
        $campaign = new Campaign();
        $campaign->setStatus(Campaign::STATUS_DRAFT);
        $campaign->setSubject('S');
        $campaign->setBody('B');
        $campaign->setTitle('C');

        $subscribers = [
            $this->makeSubscriber('one@example.com'),
            $this->makeSubscriber('two@example.com'),
            $this->makeSubscriber('three@example.com'),
        ];

        $this->subscriberRepository
            ->method('findSubscribed')
            ->willReturn($this->makeQueryResult($subscribers));

        $this->mailService->method('queue');
        $this->campaignRepository->method('update');
        $this->persistenceManager->method('persistAll');

        $this->subject->dispatch($campaign);

        self::assertSame(3, $campaign->getRecipientCount());
    }

    #[Test]
    public function dispatchSetsSentAt(): void
    {
        $campaign = new Campaign();
        $campaign->setStatus(Campaign::STATUS_DRAFT);
        $campaign->setSubject('S');
        $campaign->setBody('B');
        $campaign->setTitle('C');

        $before = new \DateTimeImmutable();

        $this->subscriberRepository
            ->method('findSubscribed')
            ->willReturn($this->makeQueryResult([]));

        $this->mailService->method('queue');
        $this->campaignRepository->method('update');
        $this->persistenceManager->method('persistAll');

        $this->subject->dispatch($campaign);

        self::assertNotNull($campaign->getSentAt());
        self::assertGreaterThanOrEqual($before->getTimestamp(), $campaign->getSentAt()->getTimestamp());
    }

    #[Test]
    public function dispatchReturnsZeroWhenNoSubscribers(): void
    {
        $campaign = new Campaign();
        $campaign->setStatus(Campaign::STATUS_DRAFT);
        $campaign->setSubject('S');
        $campaign->setBody('B');
        $campaign->setTitle('C');

        $this->subscriberRepository
            ->method('findSubscribed')
            ->willReturn($this->makeQueryResult([]));

        $this->mailService->expects(self::never())->method('queue');
        $this->campaignRepository->method('update');
        $this->persistenceManager->method('persistAll');

        $count = $this->subject->dispatch($campaign);

        self::assertSame(0, $count);
    }

    #[Test]
    public function dispatchThrowsWhenCampaignAlreadySent(): void
    {
        $campaign = new Campaign();
        $campaign->setStatus(Campaign::STATUS_SENT);
        $campaign->setTitle('Already sent');
        $campaign->setSubject('S');
        $campaign->setBody('B');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/already been sent/');

        $this->subject->dispatch($campaign);
    }

    #[Test]
    public function dispatchPersistsAfterQueuingMails(): void
    {
        $campaign = new Campaign();
        $campaign->setStatus(Campaign::STATUS_DRAFT);
        $campaign->setSubject('S');
        $campaign->setBody('B');
        $campaign->setTitle('C');

        $this->subscriberRepository
            ->method('findSubscribed')
            ->willReturn($this->makeQueryResult([$this->makeSubscriber('y@example.com')]));

        $this->mailService->method('queue');
        $this->campaignRepository->expects(self::once())->method('update')->with($campaign);
        $this->persistenceManager->expects(self::once())->method('persistAll');

        $this->subject->dispatch($campaign);
    }
}
