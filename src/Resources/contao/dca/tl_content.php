<?php

/**
 * Document management for Contao Open Source CMS
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */


/**
 * Add palettes to tl_content
 */
$GLOBALS['TL_DCA']['tl_content']['palettes']['document_single'] = str_replace(array(',type',',module'), array(',type,headline',',document,document_template,customTpl'), $GLOBALS['TL_DCA']['tl_content']['palettes']['module']);
$GLOBALS['TL_DCA']['tl_content']['palettes']['document_list']   = str_replace(array(',type',',module'), array(',type,headline',',documents,document_template,customTpl'), $GLOBALS['TL_DCA']['tl_content']['palettes']['module']);



/**
 * Add fields to tl_content
 */
$GLOBALS['TL_DCA']['tl_content']['fields']['document'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_content']['document'],
	'exclude'                 => true,
	'inputType'               => 'select',
	'foreignKey'			  => 'tl_document.headline',
	'eval'                    => array('helpwizard'=>true, 'chosen'=>true, 'submitOnChange'=>true),
	'sql'                     => "int(10) NOT NULL default '0'"
);
$GLOBALS['TL_DCA']['tl_content']['fields']['documents'] = array
(
    'label'                   => &$GLOBALS['TL_LANG']['tl_content']['documents'],
    'exclude'                 => true,
    'inputType'               => 'documentWizard',
    'eval'                    => array('tl_class'=>'clr'),
    'sql'                     => "blob NULL",
);
$GLOBALS['TL_DCA']['tl_content']['fields']['document_template'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_content']['document_template'],
	'default'                 => 'document_short',
	'exclude'                 => true,
	'inputType'               => 'select',
	'options_callback'        => array('Rhyme\ContaoDocumentsBundle\Backend\Module\Document\Callbacks', 'getDocumentTemplates'),
	'eval'                    => array('tl_class'=>'w50', 'chosen'=>true),
	'sql'                     => "varchar(32) NOT NULL default ''"
);