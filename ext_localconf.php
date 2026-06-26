<?php

defined('TYPO3') || exit;

$autoexec = static function () {
    TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
        'Find',
        'Find',
        [
            Subugoe\Find\Controller\SearchController::class => 'index, detail, suggest, term, citation',
        ],
        [
            Subugoe\Find\Controller\SearchController::class => 'index, detail, suggest, term, citation',
        ],
        TYPO3\CMS\Extbase\Utility\ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT
    );
};
$autoexec();
unset($autoexec);
