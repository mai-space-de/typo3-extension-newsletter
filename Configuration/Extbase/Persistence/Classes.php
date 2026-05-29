<?php

declare(strict_types=1);

return [
    \Maispace\MaiNewsletter\Domain\Model\Subscriber::class => [
        'tableName' => 'tx_mainewsletter_subscriber',
    ],
    \Maispace\MaiNewsletter\Domain\Model\Campaign::class => [
        'tableName' => 'tx_mainewsletter_campaign',
    ],
];
