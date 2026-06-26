<?php

declare(strict_types=1);

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

defined('TYPO3') || exit;

// TypoScript
ExtensionManagementUtility::addStaticFile(
    'find',
    'Configuration/TypoScript',
    'Find'
);
