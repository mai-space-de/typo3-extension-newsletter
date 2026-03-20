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

    // Register TypoScript
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig(
        '@import "EXT:mai_newsletter/Configuration/TypoScript/setup.typoscript"'
    );

    // FlexForm
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue(
        '*',
        'FILE:EXT:mai_newsletter/Configuration/FlexForms/SubscriptionPlugin.xml',
        'mainewsletter_subscription'
    );
})();
