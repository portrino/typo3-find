<?php
defined('TYPO3') or die();

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
	'Find',
	'Find',
	[ // An array holding the enabled controller-action-combinations
		\Subugoe\Find\Controller\SearchController::class => 'index, detail, suggest, citation', // The first controller and its first action will be the default
	],
	[ // An array holding the non-cachable controller-action-combinations
        \Subugoe\Find\Controller\SearchController::class => 'index, detail, suggest, citation', // The first controller and its first action will be the default
	]
);
