<?php
defined('TYPO3') or die();

/**
 * Registers a Plugin to be listed in the Backend. You also have to configure the Dispatcher in ext_localconf.php.
 */
\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
	'Find', // The extension name (in UpperCamelCase) with vendor prefix
	'Find', // A unique name of the plugin in UpperCamelCase
	'Find' // A title shown in the backend dropdown field
);
