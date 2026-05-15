<?php

declare(strict_types=1);

use Maispace\MaiBase\TableConfigurationArray\FieldConfig\DatetimeConfig;
use Maispace\MaiBase\TableConfigurationArray\FieldConfig\InputConfig;
use Maispace\MaiBase\TableConfigurationArray\FieldConfig\NumberConfig;
use Maispace\MaiBase\TableConfigurationArray\FieldConfig\SelectSingleConfig;
use Maispace\MaiBase\TableConfigurationArray\FieldConfig\TextConfig;
use Maispace\MaiBase\TableConfigurationArray\Helper;
use Maispace\MaiBase\TableConfigurationArray\Table;

$lang = Helper::localLangHelperFactory('mai_newsletter', 'Default/locallang_tca.xlf');

return (new Table($lang('table.tx_mainewsletter_campaign')))
    ->setDefaultConfig()
    ->setLabel('title')
    ->setIconFile('EXT:mai_base/Resources/Public/Icons/generic_table.svg')
    ->setDefaultSorting('ORDER BY crdate DESC')
    ->addColumn(
        'title',
        $lang('tx_mainewsletter_campaign.title'),
        (new InputConfig())->setSize(50)->setMax(255)->setEval('trim')->setRequired()
    )
    ->addColumn(
        'subject',
        $lang('tx_mainewsletter_campaign.subject'),
        (new InputConfig())->setSize(50)->setMax(255)->setEval('trim')->setRequired()
    )
    ->addColumn(
        'body',
        $lang('tx_mainewsletter_campaign.body'),
        (new TextConfig())->setRows(20)->setCols(50)->enableRte()->setRichtextConfiguration('default')
    )
    ->addColumn(
        'status',
        $lang('tx_mainewsletter_campaign.status'),
        (new SelectSingleConfig())
            ->setItems([
                ['label' => $lang('tx_mainewsletter_campaign.status.draft'), 'value' => 'draft'],
                ['label' => $lang('tx_mainewsletter_campaign.status.scheduled'), 'value' => 'scheduled'],
                ['label' => $lang('tx_mainewsletter_campaign.status.sent'), 'value' => 'sent'],
            ])
            ->setDefault('draft')
    )
    ->addColumn(
        'scheduled_at',
        $lang('tx_mainewsletter_campaign.scheduled_at'),
        (new DatetimeConfig())->setFormat('datetime')
    )
    ->addColumn(
        'sent_at',
        $lang('tx_mainewsletter_campaign.sent_at'),
        (new DatetimeConfig())->setFormat('datetime')->setReadOnly()
    )
    ->addColumn(
        'recipient_count',
        $lang('tx_mainewsletter_campaign.recipient_count'),
        (new NumberConfig())->setFormat('integer')->setDefault(0)->setReadOnly()
    )
    ->addPalette(
        'dispatch',
        $lang('palette.dispatch'),
        'scheduled_at, sent_at, recipient_count'
    )
    ->addTypeShowItem(
        '0',
        'title, subject, body,
        --div--;' . $lang('tab.dispatch') . ', status, --palette--;;dispatch,
        --div--;' . $lang('tab.language') . ', --palette--;;language,
        --div--;' . $lang('tab.access') . ', --palette--;;hidden, --palette--;;access'
    )
    ->getConfig();
