<?php
declare(strict_types=1);

defined('TYPO3') or die();

(static function (): void {
    // Register extension icon
    $iconRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
        \TYPO3\CMS\Core\Imaging\IconRegistry::class
    );
    $iconRegistry->registerIcon(
        'ext-mai-newsletter-module',
        \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
        ['source' => 'EXT:mai_newsletter/Resources/Public/Icons/Extension.svg']
    );

    // Register static TypoScript template
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(
        'mai_newsletter',
        'Configuration/TypoScript',
        'MaiNewsletter'
    );
})();
