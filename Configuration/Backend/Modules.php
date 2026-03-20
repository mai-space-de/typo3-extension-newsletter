<?php
declare(strict_types=1);

use MaiSpace\Newsletter\Controller\Backend\NewsletterController;

return [
    'newsletter' => [
        'parent' => 'web',
        'position' => ['after' => 'web_info'],
        'access' => 'user',
        'workspaces' => 'live',
        'iconIdentifier' => 'ext-newsletter-module',
        'path' => '/module/newsletter',
        'labels' => 'LLL:EXT:newsletter/Resources/Private/Language/locallang.xlf',
        'extensionName' => 'Newsletter',
        'controllerActions' => [
            NewsletterController::class => [
                'list', 'new', 'create', 'edit', 'update', 'delete', 'preview', 'send', 'statistics', 'archive',
            ],
        ],
    ],
];
