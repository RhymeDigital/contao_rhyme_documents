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
 * Add palettes to tl_module
 */
$GLOBALS['TL_DCA']['tl_module']['palettes']['documentlist']    = '{title_legend},name,headline,type;{config_legend},document_archives,numberOfItems,document_featured,perPage,skipFirst;{template_legend:hide},document_metaFields,document_template,customTpl;{image_legend:hide},imgSize;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';
$GLOBALS['TL_DCA']['tl_module']['palettes']['documentreader']  = '{title_legend},name,headline,type;{config_legend},document_archives;{template_legend:hide},document_metaFields,document_template,customTpl;{image_legend:hide},imgSize;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';
$GLOBALS['TL_DCA']['tl_module']['palettes']['documentmenu']    = '{title_legend},name,headline,type;{config_legend},document_archives,document_showQuantity,document_format,document_startDay,document_order;{redirect_legend},jumpTo;{template_legend:hide},customTpl;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';


/**
 * Add fields to tl_module
 */
$GLOBALS['TL_DCA']['tl_module']['fields']['document_archives'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_module']['document_archives'],
	'exclude'                 => true,
	'inputType'               => 'checkbox',
	'options_callback'        => array('HBAgency\Backend\Module\Document\Callbacks', 'getDocumentArchives'),
	'eval'                    => array('multiple'=>true, 'mandatory'=>true),
	'sql'                     => "blob NULL"
);

$GLOBALS['TL_DCA']['tl_module']['fields']['document_featured'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_module']['document_featured'],
	'default'                 => 'all_items',
	'exclude'                 => true,
	'inputType'               => 'select',
	'options'                 => array('all_items', 'featured', 'unfeatured'),
	'reference'               => &$GLOBALS['TL_LANG']['tl_module'],
	'eval'                    => array('tl_class'=>'w50'),
	'sql'                     => "varchar(16) NOT NULL default ''"
);

$GLOBALS['TL_DCA']['tl_module']['fields']['document_jumpToCurrent'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_module']['document_jumpToCurrent'],
	'exclude'                 => true,
	'inputType'               => 'select',
	'options'                 => array('hide_module', 'show_current', 'all_items'),
	'reference'               => &$GLOBALS['TL_LANG']['tl_module'],
	'eval'                    => array('tl_class'=>'w50'),
	'sql'                     => "varchar(16) NOT NULL default ''"
);

$GLOBALS['TL_DCA']['tl_module']['fields']['document_readerModule'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_module']['document_readerModule'],
	'exclude'                 => true,
	'inputType'               => 'select',
	'options_callback'        => array('HBAgency\Backend\Module\Document\Callbacks', 'getReaderModules'),
	'reference'               => &$GLOBALS['TL_LANG']['tl_module'],
	'eval'                    => array('includeBlankOption'=>true, 'tl_class'=>'w50'),
	'sql'                     => "int(10) unsigned NOT NULL default '0'"
);

$GLOBALS['TL_DCA']['tl_module']['fields']['document_metaFields'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_module']['document_metaFields'],
	'default'                 => array('date', 'author'),
	'exclude'                 => true,
	'inputType'               => 'checkbox',
	'options'                 => array('date'),
	'reference'               => &$GLOBALS['TL_LANG']['MSC'],
	'eval'                    => array('multiple'=>true),
	'sql'                     => "varchar(255) NOT NULL default ''"
);

$GLOBALS['TL_DCA']['tl_module']['fields']['document_template'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_module']['document_template'],
	'default'                 => 'document_latest',
	'exclude'                 => true,
	'inputType'               => 'select',
	'options_callback'        => array('HBAgency\Backend\Module\Document\Callbacks', 'getDocumentTemplates'),
	'eval'                    => array('tl_class'=>'w50'),
	'sql'                     => "varchar(32) NOT NULL default ''"
);

$GLOBALS['TL_DCA']['tl_module']['fields']['document_format'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_module']['document_format'],
	'default'                 => 'document_month',
	'exclude'                 => true,
	'inputType'               => 'select',
	'options'                 => array('document_day', 'document_month', 'document_year'),
	'reference'               => &$GLOBALS['TL_LANG']['tl_module'],
	'eval'                    => array('tl_class'=>'w50'),
	'wizard' => array
	(
		array('HBAgency\Backend\Module\Document\Callbacks', 'hideStartDay')
	),
	'sql'                     => "varchar(32) NOT NULL default ''"
);

$GLOBALS['TL_DCA']['tl_module']['fields']['document_startDay'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_module']['document_startDay'],
	'default'                 => 0,
	'exclude'                 => true,
	'inputType'               => 'select',
	'options'                 => array(0, 1, 2, 3, 4, 5, 6),
	'reference'               => &$GLOBALS['TL_LANG']['DAYS'],
	'eval'                    => array('tl_class'=>'w50'),
	'sql'                     => "smallint(5) unsigned NOT NULL default '0'"
);

$GLOBALS['TL_DCA']['tl_module']['fields']['document_order'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_module']['document_order'],
	'default'                 => 'descending',
	'exclude'                 => true,
	'inputType'               => 'select',
	'options'                 => array('ascending', 'descending'),
	'reference'               => &$GLOBALS['TL_LANG']['MSC'],
	'eval'                    => array('tl_class'=>'w50'),
	'sql'                     => "varchar(255) NOT NULL default ''"
);

$GLOBALS['TL_DCA']['tl_module']['fields']['document_showQuantity'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_module']['document_showQuantity'],
	'exclude'                 => true,
	'inputType'               => 'checkbox',
	'sql'                     => "char(1) NOT NULL default ''"
);

