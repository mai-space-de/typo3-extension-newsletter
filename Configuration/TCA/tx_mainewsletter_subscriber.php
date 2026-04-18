<?php

declare(strict_types=1);

use Maispace\MaiBase\TableConfigurationArray\FieldConfig\DatetimeConfig;
use Maispace\MaiBase\TableConfigurationArray\FieldConfig\EmailConfig;
use Maispace\MaiBase\TableConfigurationArray\FieldConfig\InputConfig;
use Maispace\MaiBase\TableConfigurationArray\FieldConfig\SelectSingleConfig;
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
        (new EmailConfig())->setEval('required,unique')
    )
    ->addColumn(
        'status',
        $lang('tx_mainewsletter_subscriber.status'),
        (new SelectSingleConfig())
            ->setItems([
                ['label' => $lang('tx_mainewsletter_subscriber.status.pending'), 'value' => 'pending'],
                ['label' => $lang('tx_mainewsletter_subscriber.status.subscribed'), 'value' => 'subscribed'],
                ['label' => $lang('tx_mainewsletter_subscriber.status.unsubscribed'), 'value' => 'unsubscribed'],
            ])
            ->setDefault('pending')
    )
    ->addColumn(
        'token',
        $lang('tx_mainewsletter_subscriber.token'),
        (new InputConfig())->setSize(50)->setMax(255)->setReadOnly()
    )
    ->addColumn(
        'confirmed_at',
        $lang('tx_mainewsletter_subscriber.confirmed_at'),
        (new DatetimeConfig())->setFormat('datetime')->setReadOnly()
    )
    ->addColumn(
        'unsubscribed_at',
        $lang('tx_mainewsletter_subscriber.unsubscribed_at'),
        (new DatetimeConfig())->setFormat('datetime')->setReadOnly()
    )
    ->addColumn(
        'site',
        $lang('tx_mainewsletter_subscriber.site'),
        (new InputConfig())->setSize(30)->setMax(100)->setReadOnly()
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
