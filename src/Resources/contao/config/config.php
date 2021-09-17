<?php

/**
 * Document management for Contao Open Source CMS
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */

/**
 * Back end modules
 */
\array_insert($GLOBALS['BE_MOD']['content'], 10, array
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
\array_insert($GLOBALS['FE_MOD'], 2, array
(
	'document' => array
	(
		'documentlist'      => 'Rhyme\ContaoDocumentsBundle\Module\Document\Lister',
		'documentreader'    => 'Rhyme\ContaoDocumentsBundle\Module\Document\Reader',
        'documentfilter'    => 'Rhyme\ContaoDocumentsBundle\Module\Document\Filter',
	)
));


/**
 * Content elements
 */
$GLOBALS['TL_CTE']['document']['document_single'] 		= 'Rhyme\ContaoDocumentsBundle\ContentElement\Document';


/**
 * Models
 */
$GLOBALS['TL_MODELS']['tl_document']            = 'Rhyme\ContaoDocumentsBundle\Model\Document';
$GLOBALS['TL_MODELS']['tl_document_archive']    = 'Rhyme\ContaoDocumentsBundle\Model\DocumentArchive';


/**
 * Register hook to add document items to the indexer
 */
$GLOBALS['TL_HOOKS']['executePostActions'][] = array('Rhyme\ContaoDocumentsBundle\Hooks\ExecutePostActions\ToggleFeaturedDoc', 'run');

/**
 * Add permissions
 */
$GLOBALS['TL_PERMISSIONS'][] = 'document';
$GLOBALS['TL_PERMISSIONS'][] = 'documentp';
