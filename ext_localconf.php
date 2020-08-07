<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
	'Subugoe.' . $_EXTKEY, // The extension name (in UpperCamelCase) with vendor prefix
	'Find', // A unique name of the plugin in UpperCamelCase
	[ // An array holding the enabled controller-action-combinations
		'Search' => 'index, detail, suggest, citation', // The first controller and its first action will be the default
	],
	[ // An array holding the non-cachable controller-action-combinations
		'Search' => 'index, detail, suggest, citation', // The first controller and its first action will be the default
	]
);
