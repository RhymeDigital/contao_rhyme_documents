<?php

/**
 * Document management for Contao Open Source CMS
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */

if(TL_MODE==='BE') {
    array_insert($GLOBALS['TL_JAVASCRIPT'], 99, array    (
        'bundles/rhymecontaodocuments/assets/js/docman.js'
    ));

    $GLOBALS['TL_CSS'][] = 'bundles/rhymecontaodocuments/assets/css/be_styles.css';
}


/**
 * Back end modules
 */
\array_insert($GLOBALS['BE_MOD']['content'], 10, array
(
	'document' => array
	(
		'tables'      => array('tl_document_archive', 'tl_document'),
		'icon'        => 'system/modules/documents/assets/img/icon.png',
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
$GLOBALS['TL_CTE']['document']['document_list'] 		= 'Rhyme\ContaoDocumentsBundle\ContentElement\DocumentList';


/**
 * Form fields
 */
$GLOBALS['BE_FFL']['documentWizard'] 			        = 'Rhyme\ContaoDocumentsBundle\Widget\DocumentWizard';


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
