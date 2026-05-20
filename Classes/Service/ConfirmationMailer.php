<?php

declare(strict_types=1);

namespace Maispace\MaiNewsletter\Service;

use Maispace\MaiMail\Service\MailService;
use Maispace\MaiNewsletter\Domain\Model\Subscriber;
use TYPO3\CMS\Core\View\ViewFactoryData;
use TYPO3\CMS\Core\View\ViewFactoryInterface;
use TYPO3\CMS\Core\View\ViewInterface;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

class ConfirmationMailer
{
    public function __construct(
        private readonly MailService $mailService,
        private readonly ViewFactoryInterface $viewFactory,
    ) {}

    public function send(Subscriber $subscriber, string $confirmUrl, string $unsubscribeUrl): void
    {
        $view = $this->createView();
        $view->assignMultiple([
            'subscriber' => $subscriber,
            'confirmUrl' => $confirmUrl,
            'unsubscribeUrl' => $unsubscribeUrl,
        ]);

        $subject = (string) (LocalizationUtility::translate('email.confirm.subject', 'mai_newsletter')
            ?: 'Please confirm your newsletter subscription');

        $this->mailService->queue($subscriber->getEmail(), $subject, $view->render('Confirm'));
    }

    private function createView(): ViewInterface
    {
        return $this->viewFactory->create(new ViewFactoryData(
            templateRootPaths: ['EXT:mai_newsletter/Resources/Private/Templates/Email/'],
            partialRootPaths: ['EXT:mai_newsletter/Resources/Private/Partials/'],
            layoutRootPaths: ['EXT:mai_newsletter/Resources/Private/Layouts/'],
            format: 'html',
        ));
    }
}
