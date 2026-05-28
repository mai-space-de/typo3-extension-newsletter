<?php

declare(strict_types=1);

use Maispace\MaiNewsletter\Controller\Backend\NewsletterBackendController;

return [
    'mai_newsletter' => [
        'parent' => 'web',
        'access' => 'user',
        'workspaces' => 'online',
        'path' => '/module/mai-newsletter',
        'iconIdentifier' => 'mai-backend-module',
        'labels' => 'LLL:EXT:mai_newsletter/Resources/Private/Language/locallang_mod.xlf',
        'extensionName' => 'MaiNewsletter',
        'controllerActions' => [
            NewsletterBackendController::class => ['index', 'exportCsv', 'send', 'schedule', 'approvePending', 'rejectPending'],
        ],
    ],
];
