<?php
declare(strict_types=1);

namespace Maispace\MaiNewsletter\Controller\Backend;

use Maispace\MaiNewsletter\Domain\Model\Newsletter;
use Maispace\MaiNewsletter\Domain\Repository\NewsletterRepository;
use Maispace\MaiNewsletter\Domain\Repository\SubscriberListRepository;
use Maispace\MaiNewsletter\Service\NewsletterSendService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Core\Http\RedirectResponse;
use TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface;

class NewsletterController
{
    public function __construct(
        private readonly NewsletterRepository $newsletterRepository,
        private readonly SubscriberListRepository $subscriberListRepository,
        private readonly NewsletterSendService $newsletterSendService,
        private readonly PersistenceManagerInterface $persistenceManager,
        private readonly ModuleTemplateFactory $moduleTemplateFactory,
        private readonly UriBuilder $uriBuilder,
    ) {}

    public function listAction(ServerRequestInterface $request): ResponseInterface
    {
        $moduleTemplate = $this->moduleTemplateFactory->create($request);
        $newsletters = $this->newsletterRepository->findAll();
        $moduleTemplate->assign('newsletters', $newsletters);
        return $moduleTemplate->renderResponse('Newsletter/List');
    }

    public function newAction(ServerRequestInterface $request): ResponseInterface
    {
        $moduleTemplate = $this->moduleTemplateFactory->create($request);
        $newsletter = new Newsletter();
        $subscriberLists = $this->subscriberListRepository->findAll();
        $moduleTemplate->assign('newsletter', $newsletter);
        $moduleTemplate->assign('subscriberLists', $subscriberLists);
        return $moduleTemplate->renderResponse('Newsletter/New');
    }

    public function createAction(ServerRequestInterface $request): ResponseInterface
    {
        $parsedBody = $request->getParsedBody();
        $newsletter = new Newsletter();
        if (is_array($parsedBody)) {
            $newsletter->setSubject(trim((string)($parsedBody['subject'] ?? '')));
            $newsletter->setContent((string)($parsedBody['content'] ?? ''));
        }
        $this->newsletterRepository->add($newsletter);
        $this->persistenceManager->persistAll();
        return new RedirectResponse(
            (string)$this->uriBuilder->buildUriFromRoute('mai_newsletter'),
            303
        );
    }

    public function editAction(ServerRequestInterface $request): ResponseInterface
    {
        $moduleTemplate = $this->moduleTemplateFactory->create($request);
        $newsletter = $this->getNewsletterFromRequest($request);
        $subscriberLists = $this->subscriberListRepository->findAll();
        $moduleTemplate->assign('newsletter', $newsletter);
        $moduleTemplate->assign('subscriberLists', $subscriberLists);
        return $moduleTemplate->renderResponse('Newsletter/Edit');
    }

    public function updateAction(ServerRequestInterface $request): ResponseInterface
    {
        $newsletter = $this->getNewsletterFromRequest($request);
        $parsedBody = $request->getParsedBody();
        if ($newsletter !== null && is_array($parsedBody)) {
            $newsletter->setSubject(trim((string)($parsedBody['subject'] ?? '')));
            $newsletter->setContent((string)($parsedBody['content'] ?? ''));
            $this->newsletterRepository->update($newsletter);
            $this->persistenceManager->persistAll();
        }
        return new RedirectResponse(
            (string)$this->uriBuilder->buildUriFromRoute('mai_newsletter'),
            303
        );
    }

    public function deleteAction(ServerRequestInterface $request): ResponseInterface
    {
        $newsletter = $this->getNewsletterFromRequest($request);
        if ($newsletter !== null) {
            $this->newsletterRepository->remove($newsletter);
            $this->persistenceManager->persistAll();
        }
        return new RedirectResponse(
            (string)$this->uriBuilder->buildUriFromRoute('mai_newsletter'),
            303
        );
    }

    public function previewAction(ServerRequestInterface $request): ResponseInterface
    {
        $moduleTemplate = $this->moduleTemplateFactory->create($request);
        $newsletter = $this->getNewsletterFromRequest($request);
        $moduleTemplate->assign('newsletter', $newsletter);
        return $moduleTemplate->renderResponse('Newsletter/Preview');
    }

    public function sendAction(ServerRequestInterface $request): ResponseInterface
    {
        $newsletter = $this->getNewsletterFromRequest($request);
        if ($newsletter === null) {
            return new RedirectResponse(
                (string)$this->uriBuilder->buildUriFromRoute('mai_newsletter'),
                303
            );
        }
        $statistics = $this->newsletterSendService->sendNewsletter($newsletter);
        $moduleTemplate = $this->moduleTemplateFactory->create($request);
        $moduleTemplate->assign('newsletter', $newsletter);
        $moduleTemplate->assign('statistics', $statistics);
        return $moduleTemplate->renderResponse('Newsletter/Statistics');
    }

    public function statisticsAction(ServerRequestInterface $request): ResponseInterface
    {
        $moduleTemplate = $this->moduleTemplateFactory->create($request);
        $newsletter = $this->getNewsletterFromRequest($request);
        $moduleTemplate->assign('newsletter', $newsletter);
        $moduleTemplate->assign('statistics', $newsletter?->getStatistics());
        return $moduleTemplate->renderResponse('Newsletter/Statistics');
    }

    public function archiveAction(ServerRequestInterface $request): ResponseInterface
    {
        $moduleTemplate = $this->moduleTemplateFactory->create($request);
        $newsletters = $this->newsletterRepository->findByStatus(Newsletter::STATUS_SENT);
        $moduleTemplate->assign('newsletters', $newsletters);
        return $moduleTemplate->renderResponse('Newsletter/Archive');
    }

    private function getNewsletterFromRequest(ServerRequestInterface $request): ?Newsletter
    {
        $queryParams = $request->getQueryParams();
        $parsedBody = $request->getParsedBody();
        $uid = (int)(
            (is_array($parsedBody) ? ($parsedBody['newsletter'] ?? null) : null)
            ?? $queryParams['newsletter']
            ?? 0
        );
        if ($uid === 0) {
            return null;
        }
        /** @var Newsletter|null $newsletter */
        $newsletter = $this->newsletterRepository->findByUid($uid);
        return $newsletter;
    }
}
