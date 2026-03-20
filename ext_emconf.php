<?php
$EM_CONF[$_EXTKEY] = [
    'title' => 'MaiNewsletter',
    'description' => 'Newsletter management extension for TYPO3',
    'category' => 'plugin',
    'author' => 'MaiSpace',
    'author_email' => '',
    'state' => 'stable',
    'version' => '1.0.0',
    'constraints' => [
        'depends' => [
            'typo3' => '12.4.0-12.99.99',
            'extbase' => '12.4.0-12.99.99',
            'fluid' => '12.4.0-12.99.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
