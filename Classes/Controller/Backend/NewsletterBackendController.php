<?php

declare(strict_types=1);

namespace Maispace\MaiNewsletter\Controller\Backend;

use Maispace\MaiBase\Controller\Backend\AbstractBackendController;
use Maispace\MaiBase\Controller\Traits\ResponseHelpersTrait;
use Maispace\MaiNewsletter\Domain\Model\Campaign;
use Maispace\MaiNewsletter\Domain\Model\Subscriber;
use Maispace\MaiNewsletter\Domain\Repository\CampaignRepository;
use Maispace\MaiNewsletter\Domain\Repository\SubscriberRepository;
use Maispace\MaiNewsletter\Service\CampaignDispatcher;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Backend\Attribute\AsController;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Core\Imaging\IconFactory;

#[AsController]
class NewsletterBackendController extends AbstractBackendController
{
    use ResponseHelpersTrait;

    public function __construct(
        ModuleTemplateFactory $moduleTemplateFactory,
        IconFactory $iconFactory,
        private readonly SubscriberRepository $subscriberRepository,
        private readonly CampaignRepository $campaignRepository,
        private readonly CampaignDispatcher $campaignDispatcher,
    ) {
        parent::__construct($moduleTemplateFactory, $iconFactory);
    }

    public function indexAction(): ResponseInterface
    {
        $moduleTemplate = $this->createModuleTemplate();
        $this->addShortcutButton(
            $moduleTemplate,
            'mai_newsletter',
            'Newsletter',
        );

        $subscribed = $this->subscriberRepository->findSubscribed();
        $pending = $this->subscriberRepository->findByStatus(Subscriber::STATUS_PENDING);
        $unsubscribed = $this->subscriberRepository->findByStatus(Subscriber::STATUS_UNSUBSCRIBED);
        $campaigns = $this->campaignRepository->findAll();

        $this->assignMultiple($moduleTemplate, [
            'subscriberCount' => $subscribed->count(),
            'pendingCount' => $pending->count(),
            'unsubscribedCount' => $unsubscribed->count(),
            'subscribers' => $subscribed,
            'pendingSubscribers' => $pending,
            'campaigns' => $campaigns,
        ]);

        return $this->renderModuleResponse($moduleTemplate, 'Index');
    }

    public function exportCsvAction(): ResponseInterface
    {
        $subscribers = $this->subscriberRepository->findAll();

        $rows = [['email', 'status', 'confirmed_at', 'unsubscribed_at', 'site', 'fe_user']];
        foreach ($subscribers as $subscriber) {
            $rows[] = [
                $subscriber->getEmail(),
                $subscriber->getStatus(),
                $subscriber->getConfirmedAt() !== null ? $subscriber->getConfirmedAt()->format('Y-m-d H:i:s') : '',
                $subscriber->getUnsubscribedAt() !== null ? $subscriber->getUnsubscribedAt()->format('Y-m-d H:i:s') : '',
                $subscriber->getSite(),
                (string) $subscriber->getFeUser(),
            ];
        }

        return $this->csvResponse($rows, 'newsletter-subscribers.csv');
    }

    public function sendAction(Campaign $campaign): ResponseInterface
    {
        try {
            $count = $this->campaignDispatcher->dispatch($campaign);
            $this->flashSuccess(
                sprintf('Campaign "%s" dispatched to %d recipients.', $campaign->getTitle(), $count),
            );
        } catch (\InvalidArgumentException $e) {
            $this->flashError($e->getMessage());
        }

        return $this->redirect('index');
    }

    public function scheduleAction(Campaign $campaign): ResponseInterface
    {
        if ($campaign->getStatus() === Campaign::STATUS_SENT) {
            $this->flashError(
                sprintf('Campaign "%s" has already been sent and cannot be scheduled.', $campaign->getTitle()),
            );
            return $this->redirect('index');
        }

        $campaign->setStatus(Campaign::STATUS_SCHEDULED);
        if ($campaign->getScheduledAt() === null) {
            $campaign->setScheduledAt(new \DateTimeImmutable());
        }
        $this->campaignRepository->update($campaign);

        $this->flashSuccess(
            sprintf('Campaign "%s" has been scheduled for dispatch.', $campaign->getTitle()),
        );

        return $this->redirect('index');
    }

    public function approvePendingAction(Subscriber $subscriber): ResponseInterface
    {
        if (!$subscriber->isPending()) {
            $this->flashInfo(
                sprintf('Subscriber "%s" is not in pending status.', $subscriber->getEmail()),
            );
            return $this->redirect('index');
        }

        $subscriber->setStatus(Subscriber::STATUS_SUBSCRIBED);
        $subscriber->setConfirmedAt(new \DateTimeImmutable());
        $this->subscriberRepository->update($subscriber);

        $this->flashSuccess(
            sprintf('Pending subscriber "%s" has been approved.', $subscriber->getEmail()),
        );

        return $this->redirect('index');
    }

    public function rejectPendingAction(Subscriber $subscriber): ResponseInterface
    {
        if (!$subscriber->isPending()) {
            $this->flashInfo(
                sprintf('Subscriber "%s" is not in pending status.', $subscriber->getEmail()),
            );
            return $this->redirect('index');
        }

        $this->subscriberRepository->remove($subscriber);

        $this->flashInfo(
            sprintf('Pending subscriber "%s" has been rejected and removed.', $subscriber->getEmail()),
        );

        return $this->redirect('index');
    }
}
