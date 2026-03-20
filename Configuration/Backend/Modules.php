<?php
declare(strict_types=1);

use Maispace\MaiNewsletter\Controller\Backend\NewsletterController;

return [
    'mai_newsletter' => [
        'parent' => 'web',
        'position' => ['after' => 'web_info'],
        'access' => 'user',
        'workspaces' => 'live',
        'iconIdentifier' => 'ext-mai-newsletter-module',
        'path' => '/module/mai-newsletter',
        'labels' => 'LLL:EXT:mai_newsletter/Resources/Private/Language/locallang.xlf',
        'extensionName' => 'MaiNewsletter',
        'controllerActions' => [
            NewsletterController::class => [
                'list', 'new', 'create', 'edit', 'update', 'delete', 'preview', 'send', 'statistics', 'archive',
            ],
        ],
    ],
];
