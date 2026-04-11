<?php

declare(strict_types=1);

use Maispace\MaiBase\TableConfigurationArray\Helper;
use Maispace\MaiBase\TableConfigurationArray\Table;

$lang = Helper::localLangHelperFactory('mai_newsletter', 'Default/locallang_tca.xlf');

return (new Table($lang('table.tx_mainewsletter_campaign')))
    ->setDefaultConfig()
    ->setLabel('title')
    ->setSearchFields('title, subject')
    ->setIconFile('EXT:mai_newsletter/Resources/Public/Icons/tx_mainewsletter_campaign.svg')
    ->setDefaultSorting('ORDER BY crdate DESC')
    ->addColumn(
        'title',
        $lang('tx_mainewsletter_campaign.title'),
        ['type' => 'input', 'size' => 50, 'max' => 255, 'eval' => 'trim,required']
    )
    ->addColumn(
        'subject',
        $lang('tx_mainewsletter_campaign.subject'),
        ['type' => 'input', 'size' => 50, 'max' => 255, 'eval' => 'trim,required']
    )
    ->addColumn(
        'body',
        $lang('tx_mainewsletter_campaign.body'),
        [
            'type' => 'text',
            'rows' => 20,
            'cols' => 50,
            'enableRichtext' => true,
            'richtextConfiguration' => 'default',
        ]
    )
    ->addColumn(
        'status',
        $lang('tx_mainewsletter_campaign.status'),
        [
            'type' => 'select',
            'renderType' => 'selectSingle',
            'items' => [
                ['label' => $lang('tx_mainewsletter_campaign.status.draft'), 'value' => 'draft'],
                ['label' => $lang('tx_mainewsletter_campaign.status.scheduled'), 'value' => 'scheduled'],
                ['label' => $lang('tx_mainewsletter_campaign.status.sent'), 'value' => 'sent'],
            ],
            'default' => 'draft',
        ]
    )
    ->addColumn(
        'scheduled_at',
        $lang('tx_mainewsletter_campaign.scheduled_at'),
        ['type' => 'datetime', 'format' => 'datetime']
    )
    ->addColumn(
        'sent_at',
        $lang('tx_mainewsletter_campaign.sent_at'),
        ['type' => 'datetime', 'format' => 'datetime', 'readOnly' => true]
    )
    ->addColumn(
        'recipient_count',
        $lang('tx_mainewsletter_campaign.recipient_count'),
        ['type' => 'number', 'format' => 'integer', 'default' => 0, 'readOnly' => true]
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
