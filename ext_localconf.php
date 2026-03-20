<?php
declare(strict_types=1);

use Maispace\MaiNewsletter\Controller\SubscriptionController;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

defined('TYPO3') or die();

(static function (): void {
    ExtensionUtility::configurePlugin(
        'MaiNewsletter',
        'Subscription',
        [SubscriptionController::class => 'subscribe,processSubscribe,confirm,unsubscribe'],
        [SubscriptionController::class => 'subscribe,processSubscribe,confirm,unsubscribe'],
        ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT
    );
})();
