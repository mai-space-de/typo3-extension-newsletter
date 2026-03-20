<?php
declare(strict_types=1);

use MaiSpace\Newsletter\Controller\Backend\NewsletterController;

return [
    'newsletter_list' => [
        'path' => '/newsletter/list',
        'target' => NewsletterController::class . '::listAction',
    ],
    'newsletter_new' => [
        'path' => '/newsletter/new',
        'target' => NewsletterController::class . '::newAction',
    ],
    'newsletter_create' => [
        'path' => '/newsletter/create',
        'target' => NewsletterController::class . '::createAction',
    ],
    'newsletter_edit' => [
        'path' => '/newsletter/edit',
        'target' => NewsletterController::class . '::editAction',
    ],
    'newsletter_update' => [
        'path' => '/newsletter/update',
        'target' => NewsletterController::class . '::updateAction',
    ],
    'newsletter_delete' => [
        'path' => '/newsletter/delete',
        'target' => NewsletterController::class . '::deleteAction',
    ],
    'newsletter_preview' => [
        'path' => '/newsletter/preview',
        'target' => NewsletterController::class . '::previewAction',
    ],
    'newsletter_send' => [
        'path' => '/newsletter/send',
        'target' => NewsletterController::class . '::sendAction',
    ],
    'newsletter_statistics' => [
        'path' => '/newsletter/statistics',
        'target' => NewsletterController::class . '::statisticsAction',
    ],
];
