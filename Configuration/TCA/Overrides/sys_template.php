<?php

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

defined('TYPO3') or die();

// TypoScript
ExtensionManagementUtility::addStaticFile(
    'find',
    'Configuration/TypoScript',
    'Find'
);
