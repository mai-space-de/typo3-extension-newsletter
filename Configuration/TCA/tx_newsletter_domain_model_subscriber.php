<?php
declare(strict_types=1);

return [
    'ctrl' => [
        'title' => 'LLL:EXT:newsletter/Resources/Private/Language/locallang_db.xlf:tx_newsletter_domain_model_subscriber',
        'label' => 'email',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'delete' => 'deleted',
        'enablecolumns' => [
            'disabled' => 'hidden',
        ],
        'searchFields' => 'email,interest_tags',
        'iconfile' => 'EXT:newsletter/Resources/Public/Icons/Extension.svg',
    ],
    'types' => [
        '1' => [
            'showitem' => 'hidden, email, fe_user_uid, interest_tags, token, confirmed, confirmed_at, deleted_at',
        ],
    ],
    'columns' => [
        'hidden' => [
            'exclude' => true,
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.visible',
            'config' => [
                'type' => 'check',
                'renderType' => 'checkboxToggle',
                'items' => [['label' => '', 'invertStateDisplay' => true]],
            ],
        ],
        'email' => [
            'label' => 'LLL:EXT:newsletter/Resources/Private/Language/locallang_db.xlf:tx_newsletter_domain_model_subscriber.email',
            'config' => [
                'type' => 'input',
                'size' => 50,
                'eval' => 'trim,email',
                'required' => true,
            ],
        ],
        'fe_user_uid' => [
            'label' => 'LLL:EXT:newsletter/Resources/Private/Language/locallang_db.xlf:tx_newsletter_domain_model_subscriber.feUserUid',
            'config' => [
                'type' => 'group',
                'allowed' => 'fe_users',
                'size' => 1,
                'maxitems' => 1,
            ],
        ],
        'interest_tags' => [
            'label' => 'LLL:EXT:newsletter/Resources/Private/Language/locallang_db.xlf:tx_newsletter_domain_model_subscriber.interestTags',
            'config' => [
                'type' => 'input',
                'size' => 50,
                'eval' => 'trim',
            ],
        ],
        'token' => [
            'label' => 'LLL:EXT:newsletter/Resources/Private/Language/locallang_db.xlf:tx_newsletter_domain_model_subscriber.token',
            'config' => [
                'type' => 'input',
                'size' => 50,
                'readOnly' => true,
            ],
        ],
        'confirmed' => [
            'label' => 'LLL:EXT:newsletter/Resources/Private/Language/locallang_db.xlf:tx_newsletter_domain_model_subscriber.confirmed',
            'config' => [
                'type' => 'check',
                'renderType' => 'checkboxToggle',
            ],
        ],
        'confirmed_at' => [
            'label' => 'LLL:EXT:newsletter/Resources/Private/Language/locallang_db.xlf:tx_newsletter_domain_model_subscriber.confirmedAt',
            'config' => [
                'type' => 'datetime',
                'format' => 'datetime',
            ],
        ],
        'deleted_at' => [
            'label' => 'LLL:EXT:newsletter/Resources/Private/Language/locallang_db.xlf:tx_newsletter_domain_model_subscriber.deletedAt',
            'config' => [
                'type' => 'datetime',
                'format' => 'datetime',
            ],
        ],
    ],
];
