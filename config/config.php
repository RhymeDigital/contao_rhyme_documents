<?php

/**
 * Document management for Contao Open Source CMS
 *
 * Copyright (C) 2014-2015 HB Agency
 *
 * @package    Document_Management
 * @link       http://www.hbagency.com
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */
 

/**
 * Back end modules
 */
array_insert($GLOBALS['BE_MOD']['content'], 10, array
(
	'document' => array
	(
		'tables'      => array('tl_document_archive', 'tl_document'),
		'icon'        => 'system/modules/documents/assets/img/icon.png',
		'javascript'  => 'system/modules/documents/assets/js/docman.js'
	)
));


/**
 * Front end modules
 */
array_insert($GLOBALS['FE_MOD'], 2, array
(
	'document' => array
	(
		'documentlist'    => 'HBAgency\Module\Document\Lister',
		'documentreader'  => 'HBAgency\Module\Document\Reader',
		'documentmenu'    => 'HBAgency\Module\Document\Menu'
	)
));


/**
 * Content elements
 */
$GLOBALS['TL_CTE']['document']['document_single'] 		= 'HBAgency\ContentElement\Document';


/**
 * Models
 */
$GLOBALS['TL_MODELS']['tl_document']            = 'HBAgency\Model\Document';
$GLOBALS['TL_MODELS']['tl_document_archive']    = 'HBAgency\Model\DocumentArchive';


/**
 * Register hook to add document items to the indexer
 */
//$GLOBALS['TL_HOOKS']['getSearchablePages'][] = array('HBAgency\Frontend\Document', 'getSearchablePages');
$GLOBALS['TL_HOOKS']['executePostActions'][] = array('HBAgency\Hooks\ExecutePostActions\ToggleFeaturedDoc', 'run');

/**
 * Add permissions
 */
$GLOBALS['TL_PERMISSIONS'][] = 'document';
$GLOBALS['TL_PERMISSIONS'][] = 'documentp';
