<?php

declare(strict_types=1);

defined('TYPO3') or die();

use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

ExtensionUtility::registerPlugin(
    'MaiNewsletter',
    'Newsletter',
    'LLL:EXT:mai_newsletter/Resources/Private/Language/Default/locallang.xlf:plugin.newsletter.title',
    'mai-content',
    'maispace_plugins_interactive',
);
