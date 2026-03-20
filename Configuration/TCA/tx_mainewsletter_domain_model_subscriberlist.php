<?php
declare(strict_types=1);

return [
    'ctrl' => [
        'title' => 'LLL:EXT:mai_newsletter/Resources/Private/Language/locallang_db.xlf:tx_mainewsletter_domain_model_subscriberlist',
        'label' => 'name',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'delete' => 'deleted',
        'enablecolumns' => [
            'disabled' => 'hidden',
        ],
        'searchFields' => 'name,description',
        'iconfile' => 'EXT:mai_newsletter/Resources/Public/Icons/Extension.svg',
    ],
    'types' => [
        '1' => [
            'showitem' => 'hidden, name, description, interest_tag, subscribers',
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
        'name' => [
            'label' => 'LLL:EXT:mai_newsletter/Resources/Private/Language/locallang_db.xlf:tx_mainewsletter_domain_model_subscriberlist.name',
            'config' => [
                'type' => 'input',
                'size' => 50,
                'eval' => 'trim',
                'required' => true,
            ],
        ],
        'description' => [
            'label' => 'LLL:EXT:mai_newsletter/Resources/Private/Language/locallang_db.xlf:tx_mainewsletter_domain_model_subscriberlist.description',
            'config' => [
                'type' => 'text',
                'cols' => 50,
                'rows' => 5,
            ],
        ],
        'interest_tag' => [
            'label' => 'LLL:EXT:mai_newsletter/Resources/Private/Language/locallang_db.xlf:tx_mainewsletter_domain_model_subscriberlist.interestTag',
            'config' => [
                'type' => 'input',
                'size' => 50,
                'eval' => 'trim',
            ],
        ],
        'subscribers' => [
            'label' => 'LLL:EXT:mai_newsletter/Resources/Private/Language/locallang_db.xlf:tx_mainewsletter_domain_model_subscriberlist.subscribers',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectMultipleSideBySide',
                'foreign_table' => 'tx_mainewsletter_domain_model_subscriber',
                'MM' => 'tx_mainewsletter_subscriberlist_subscriber_mm',
                'size' => 5,
                'maxitems' => 9999,
            ],
        ],
    ],
];
