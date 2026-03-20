<?php
declare(strict_types=1);
namespace Maispace\MaiNewsletter\Controller;

use Maispace\MaiNewsletter\Domain\Repository\SubscriberListRepository;
use Maispace\MaiNewsletter\Service\SubscriptionService;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

class SubscriptionController extends ActionController
{
    public function __construct(
        private readonly SubscriptionService $subscriptionService,
        private readonly SubscriberListRepository $subscriberListRepository,
    ) {}

    public function subscribeAction(): ResponseInterface
    {
        $this->view->assign('contentArguments', $this->request->getArguments());
        return $this->htmlResponse();
    }

    public function processSubscribeAction(): ResponseInterface
    {
        $email = trim($this->request->getArgument('email'));
        $listUid = (int)($this->request->getArgument('listUid') ?? 0);

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->view->assign('error', 'invalid_email');
            return $this->htmlResponse();
        }

        $list = $this->subscriberListRepository->findByUid($listUid);
        if ($list === null) {
            $this->view->assign('error', 'list_not_found');
            return $this->htmlResponse();
        }

        $subscriber = $this->subscriptionService->subscribe($email, $list);

        $confirmUrl = $this->uriBuilder
            ->reset()
            ->setCreateAbsoluteUri(true)
            ->uriFor('confirm', ['token' => $subscriber->getToken()], 'Subscription', 'Newsletter', 'Subscription');

        $this->subscriptionService->sendConfirmationEmail($subscriber, $confirmUrl);

        $this->view->assign('success', true);
        $this->view->assign('email', $email);
        return $this->htmlResponse();
    }

    public function confirmAction(string $token = ''): ResponseInterface
    {
        $subscriber = null;
        $success = false;
        if ($token !== '') {
            $subscriber = $this->subscriptionService->confirm($token);
            $success = $subscriber !== null;
        }
        $this->view->assign('success', $success);
        $this->view->assign('subscriber', $subscriber);
        return $this->htmlResponse();
    }

    public function unsubscribeAction(string $token = ''): ResponseInterface
    {
        $subscriber = null;
        $success = false;
        if ($token !== '') {
            $subscriber = $this->subscriptionService->unsubscribe($token);
            $success = $subscriber !== null;
        }
        $this->view->assign('success', $success);
        $this->view->assign('token', $token);
        $this->view->assign('subscriber', $subscriber);
        return $this->htmlResponse();
    }
}
