<?php
$EM_CONF[$_EXTKEY] = [
    'title' => 'Mai Newsletter',
    'description' => 'Newsletter management extension. **Owns the canonical subscriber record** — all opt-in/opt-out operations across the project write to this extension\'s subscriber table. Email dispatch goes through `mai_mail`.',
    'category' => 'module',
    'author' => 'Maispace',
    'author_email' => '',
    'state' => 'stable',
    'version' => '1.0.0',
    'constraints' => [
        'depends' => [
            'typo3' => '13.4.0-14.99.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
