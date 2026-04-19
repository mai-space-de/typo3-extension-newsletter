<?php

declare(strict_types=1);

namespace Maispace\MaiNewsletter\Controller;

use Maispace\MaiBase\Controller\AbstractActionController;
use Maispace\MaiBase\Controller\Traits\FlashMessageTrait;
use Maispace\MaiNewsletter\Domain\Model\Subscriber;
use Maispace\MaiNewsletter\Service\ConfirmationMailer;
use Maispace\MaiNewsletter\Service\SubscriberService;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Site\SiteFinder;

class NewsletterController extends AbstractActionController
{
    use FlashMessageTrait;

    public function __construct(
        private readonly SubscriberService $subscriberService,
        private readonly ConfirmationMailer $confirmationMailer,
        private readonly SiteFinder $siteFinder,
        private readonly Context $context,
    ) {
    }

    public function subscribeFormAction(): ResponseInterface
    {
        return $this->htmlResponse();
    }

    public function subscribeAction(string $email = ''): ResponseInterface
    {
        $email = trim($email);

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->flashError($this->translate('subscribe.invalidEmail', 'Please enter a valid email address.'));
            return $this->redirect('subscribeForm');
        }

        $storagePid = (int)($this->settings['subscriberStoragePid'] ?? 0);
        $siteIdentifier = $this->resolveSiteIdentifier();
        $feUserUid = (int)$this->context->getPropertyFromAspect('frontend.user', 'id');

        $subscriber = $this->subscriberService->optIn($email, $siteIdentifier, $storagePid, $feUserUid);

        if ($subscriber->isSubscribed()) {
            $this->flashInfo($this->translate('subscribe.alreadySubscribed', 'This email is already subscribed.'));
            return $this->redirect('subscribeForm');
        }

        $this->dispatchConfirmationEmail($subscriber);

        $this->flashSuccess($this->translate('subscribe.confirmationSent', 'Please check your inbox to confirm your subscription.'));

        return $this->redirect('subscribeForm');
    }

    public function confirmAction(string $token = ''): ResponseInterface
    {
        $subscriber = $this->subscriberService->confirm($token);

        $this->view->assign('success', $subscriber !== null);
        $this->view->assign('subscriber', $subscriber);

        return $this->htmlResponse();
    }

    public function unsubscribeAction(string $token = ''): ResponseInterface
    {
        $subscriber = $this->subscriberService->unsubscribe($token);

        $this->view->assign('success', $subscriber !== null);
        $this->view->assign('subscriber', $subscriber);

        return $this->htmlResponse();
    }

    private function dispatchConfirmationEmail(Subscriber $subscriber): void
    {
        $confirmUrl = $this->buildPublicUri('confirm', ['token' => $subscriber->getToken()]);
        $unsubscribeUrl = $this->buildPublicUri('unsubscribe', ['token' => $subscriber->getToken()]);

        $this->confirmationMailer->send($subscriber, $confirmUrl, $unsubscribeUrl);
    }

    private function buildPublicUri(string $action, array $arguments): string
    {
        return (string)$this->uriBuilder
            ->reset()
            ->setCreateAbsoluteUri(true)
            ->uriFor($action, $arguments, 'Newsletter', 'MaiNewsletter', 'Newsletter');
    }

    private function resolveSiteIdentifier(): string
    {
        $pageArguments = $this->request->getAttribute('routing');
        $pageUid = $pageArguments?->getPageId() ?? 0;

        if ($pageUid > 0) {
            try {
                return $this->siteFinder->getSiteByPageId($pageUid)->getIdentifier();
            } catch (\Throwable) {
                // Fall through to default.
            }
        }

        return 'default';
    }

    private function translate(string $key, string $default): string
    {
        $translated = \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
            $key,
            'mai_newsletter',
        );

        return ($translated ?? '') !== '' ? (string)$translated : $default;
    }
}
