<?php
declare(strict_types=1);
namespace Maispace\MaiNewsletter\Controller\Backend;

use Maispace\MaiNewsletter\Domain\Model\Newsletter;
use Maispace\MaiNewsletter\Domain\Repository\NewsletterRepository;
use Maispace\MaiNewsletter\Domain\Repository\SubscriberListRepository;
use Maispace\MaiNewsletter\Service\NewsletterSendService;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Extbase\Annotation\IgnoreValidation;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface;

class NewsletterController extends ActionController
{
    public function __construct(
        private readonly NewsletterRepository $newsletterRepository,
        private readonly SubscriberListRepository $subscriberListRepository,
        private readonly NewsletterSendService $newsletterSendService,
        private readonly PersistenceManagerInterface $persistenceManager,
        private readonly ModuleTemplateFactory $moduleTemplateFactory,
    ) {}

    public function listAction(): ResponseInterface
    {
        $moduleTemplate = $this->moduleTemplateFactory->create($this->request);
        $newsletters = $this->newsletterRepository->findAll();
        $moduleTemplate->assign('newsletters', $newsletters);
        return $moduleTemplate->renderResponse('Newsletter/List');
    }

    public function newAction(): ResponseInterface
    {
        $moduleTemplate = $this->moduleTemplateFactory->create($this->request);
        $newsletter = new Newsletter();
        $subscriberLists = $this->subscriberListRepository->findAll();
        $moduleTemplate->assign('newsletter', $newsletter);
        $moduleTemplate->assign('subscriberLists', $subscriberLists);
        return $moduleTemplate->renderResponse('Newsletter/New');
    }

    public function createAction(Newsletter $newsletter): ResponseInterface
    {
        $this->newsletterRepository->add($newsletter);
        $this->persistenceManager->persistAll();
        return $this->redirect('list');
    }

    #[IgnoreValidation(['value' => 'newsletter'])]
    public function editAction(Newsletter $newsletter): ResponseInterface
    {
        $moduleTemplate = $this->moduleTemplateFactory->create($this->request);
        $subscriberLists = $this->subscriberListRepository->findAll();
        $moduleTemplate->assign('newsletter', $newsletter);
        $moduleTemplate->assign('subscriberLists', $subscriberLists);
        return $moduleTemplate->renderResponse('Newsletter/Edit');
    }

    public function updateAction(Newsletter $newsletter): ResponseInterface
    {
        $this->newsletterRepository->update($newsletter);
        $this->persistenceManager->persistAll();
        return $this->redirect('list');
    }

    public function deleteAction(Newsletter $newsletter): ResponseInterface
    {
        $this->newsletterRepository->remove($newsletter);
        $this->persistenceManager->persistAll();
        return $this->redirect('list');
    }

    #[IgnoreValidation(['value' => 'newsletter'])]
    public function previewAction(Newsletter $newsletter): ResponseInterface
    {
        $moduleTemplate = $this->moduleTemplateFactory->create($this->request);
        $moduleTemplate->assign('newsletter', $newsletter);
        return $moduleTemplate->renderResponse('Newsletter/Preview');
    }

    public function sendAction(Newsletter $newsletter): ResponseInterface
    {
        $statistics = $this->newsletterSendService->sendNewsletter($newsletter);
        $moduleTemplate = $this->moduleTemplateFactory->create($this->request);
        $moduleTemplate->assign('newsletter', $newsletter);
        $moduleTemplate->assign('statistics', $statistics);
        return $moduleTemplate->renderResponse('Newsletter/Statistics');
    }

    #[IgnoreValidation(['value' => 'newsletter'])]
    public function statisticsAction(Newsletter $newsletter): ResponseInterface
    {
        $moduleTemplate = $this->moduleTemplateFactory->create($this->request);
        $moduleTemplate->assign('newsletter', $newsletter);
        $moduleTemplate->assign('statistics', $newsletter->getStatistics());
        return $moduleTemplate->renderResponse('Newsletter/Statistics');
    }

    public function archiveAction(): ResponseInterface
    {
        $moduleTemplate = $this->moduleTemplateFactory->create($this->request);
        $newsletters = $this->newsletterRepository->findByStatus(Newsletter::STATUS_SENT);
        $moduleTemplate->assign('newsletters', $newsletters);
        return $moduleTemplate->renderResponse('Newsletter/Archive');
    }
}
