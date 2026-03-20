<?php

declare(strict_types=1);

defined('TYPO3') or die();

$GLOBALS['TCA']['tt_content']['types']['mainewsletter_subscription']['columnsOverrides']['pi_flexform']['config']['ds'] =
    'FILE:EXT:mai_newsletter/Configuration/FlexForms/SubscriptionPlugin.xml';
