<?php

declare(strict_types=1);

defined('TYPO3') or die();

use Maispace\MaiNewsletter\Controller\NewsletterController;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

ExtensionUtility::configurePlugin(
    'MaiNewsletter',
    'Newsletter',
    [
        NewsletterController::class => 'subscribeForm,subscribe,confirm,unsubscribe',
    ],
    [
        NewsletterController::class => 'subscribe,confirm,unsubscribe',
    ],
    ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT,
);
