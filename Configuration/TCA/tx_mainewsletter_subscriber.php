<?php

declare(strict_types=1);

use Maispace\MaiBase\TableConfigurationArray\Helper;
use Maispace\MaiBase\TableConfigurationArray\Table;

$lang = Helper::localLangHelperFactory('mai_newsletter', 'Default/locallang_tca.xlf');

return (new Table($lang('table.tx_mainewsletter_subscriber')))
    ->setDefaultConfig()
    ->setLabel('email')
    ->setSearchFields('email')
    ->setIconFile('EXT:mai_newsletter/Resources/Public/Icons/tx_mainewsletter_subscriber.svg')
    ->setDefaultSorting('ORDER BY crdate DESC')
    ->recordsAreOnlyAllowedInRoot()
    ->addColumn(
        'email',
        $lang('tx_mainewsletter_subscriber.email'),
        ['type' => 'email', 'eval' => 'required,unique']
    )
    ->addColumn(
        'status',
        $lang('tx_mainewsletter_subscriber.status'),
        [
            'type' => 'select',
            'renderType' => 'selectSingle',
            'items' => [
                ['label' => $lang('tx_mainewsletter_subscriber.status.pending'), 'value' => 'pending'],
                ['label' => $lang('tx_mainewsletter_subscriber.status.subscribed'), 'value' => 'subscribed'],
                ['label' => $lang('tx_mainewsletter_subscriber.status.unsubscribed'), 'value' => 'unsubscribed'],
            ],
            'default' => 'pending',
        ]
    )
    ->addColumn(
        'token',
        $lang('tx_mainewsletter_subscriber.token'),
        ['type' => 'input', 'size' => 50, 'max' => 255, 'readOnly' => true]
    )
    ->addColumn(
        'confirmed_at',
        $lang('tx_mainewsletter_subscriber.confirmed_at'),
        ['type' => 'datetime', 'format' => 'datetime', 'readOnly' => true]
    )
    ->addColumn(
        'unsubscribed_at',
        $lang('tx_mainewsletter_subscriber.unsubscribed_at'),
        ['type' => 'datetime', 'format' => 'datetime', 'readOnly' => true]
    )
    ->addColumn(
        'site',
        $lang('tx_mainewsletter_subscriber.site'),
        ['type' => 'input', 'size' => 30, 'max' => 100, 'readOnly' => true]
    )
    ->addPalette(
        'dates',
        $lang('palette.dates'),
        'confirmed_at, unsubscribed_at'
    )
    ->addTypeShowItem(
        '0',
        'email, status, site, --palette--;;dates, token'
    )
    ->getConfig();
