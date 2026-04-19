<?php

declare(strict_types=1);

namespace Maispace\MaiNewsletter\Service;

use Maispace\MaiMail\Service\MailService;
use Maispace\MaiNewsletter\Domain\Model\Subscriber;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;

class ConfirmationMailer
{
    public function __construct(
        private readonly MailService $mailService,
    ) {
    }

    public function send(Subscriber $subscriber, string $confirmUrl, string $unsubscribeUrl): void
    {
        $view = new StandaloneView();
        $view->setTemplateRootPaths(['EXT:mai_newsletter/Resources/Private/Templates/Email/']);
        $view->setPartialRootPaths(['EXT:mai_newsletter/Resources/Private/Partials/']);
        $view->setLayoutRootPaths(['EXT:mai_newsletter/Resources/Private/Layouts/']);
        $view->setTemplate('Confirm');
        $view->setFormat('html');
        $view->assignMultiple([
            'subscriber' => $subscriber,
            'confirmUrl' => $confirmUrl,
            'unsubscribeUrl' => $unsubscribeUrl,
        ]);

        $subject = (string)(LocalizationUtility::translate('email.confirm.subject', 'mai_newsletter')
            ?: 'Please confirm your newsletter subscription');

        $this->mailService->queue($subscriber->getEmail(), $subject, $view->render());
    }
}
