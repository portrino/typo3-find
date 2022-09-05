<?php

/***************************************************************
 * Extension Manager/Repository config file for ext "find".
 *
 * Auto generated 12-11-2013 18:35
 *
 * Manual updates:
 * Only the data in the array - everything else is removed by next
 * writing. "version" and "dependencies" must not be touched!
 ***************************************************************/

$EM_CONF[$_EXTKEY] = array(
	'title' => 'Find',
	'description' => 'A frontend for Solr indexes',
	'version' => '3.0.0',
	'state' => 'stable',
	'category' => 'frontend',
	'shy' => 0,
	'priority' => '',
	'loadOrder' => '',
	'module' => '',
	'uploadfolder' => 0,
	'createDirs' => '',
	'modify_tables' => '',
	'clearcacheonload' => 1,
	'lockType' => '',
	'author' => 'Sven-S. Porst, Ingo Pfennigstorf',
	'author_email' => 'porst@sub.uni-goettingen.de',
	'author_company' => 'SUB Göttingen',
	'CGLcompliance' => '',
	'CGLcompliance_note' => '',
	'conflicts' => '',
	'constraints' => array(
		'depends' => array(
            'php' => '7.4.0-8.0.99',
			'typo3' => '10.4.0-11.5.99'
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
);
