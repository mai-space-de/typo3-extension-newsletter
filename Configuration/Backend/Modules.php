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
        'labels' => 'LLL:EXT:mai_newsletter/Resources/Private/Language/locallang_mod.xlf',
        'routes' => [
            '_default' => [
                'target' => NewsletterController::class . '::listAction',
            ],
            'new' => [
                'target' => NewsletterController::class . '::newAction',
            ],
            'create' => [
                'target' => NewsletterController::class . '::createAction',
            ],
            'edit' => [
                'target' => NewsletterController::class . '::editAction',
            ],
            'update' => [
                'target' => NewsletterController::class . '::updateAction',
            ],
            'delete' => [
                'target' => NewsletterController::class . '::deleteAction',
            ],
            'preview' => [
                'target' => NewsletterController::class . '::previewAction',
            ],
            'send' => [
                'target' => NewsletterController::class . '::sendAction',
            ],
            'statistics' => [
                'target' => NewsletterController::class . '::statisticsAction',
            ],
            'archive' => [
                'target' => NewsletterController::class . '::archiveAction',
            ],
        ],
    ],
];
