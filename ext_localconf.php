<?php

defined('TYPO3') or die();

$autoexec = static function () {
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
        'Find',
        'Find',
        [
            \Subugoe\Find\Controller\SearchController::class => 'index, detail, suggest, term, citation',
        ],
        [
            \Subugoe\Find\Controller\SearchController::class => 'index, detail, suggest, term, citation',
        ]
    );

    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig('<INCLUDE_TYPOSCRIPT: source="FILE:EXT:find/Configuration/TSconfig/ContentElementWizard.tsconfig">');
};
$autoexec();
unset($autoexec);
