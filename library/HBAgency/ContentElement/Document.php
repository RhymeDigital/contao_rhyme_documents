<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace HBAgency\ContentElement;

use HBAgency\Module\DocumentGroup\Reader as DocumentGroupReaderModule;
use HBAgency\Model\DocumentGroup as DocumentGroupModel;
use HBAgency\Model\DocumentGroupItem as DocumentGroupItemModel;
use HBAgency\Model\Document as DocumentModel;


/**
 * Content element "Document".
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class Document extends \ContentElement
{

	/**
	 * Parse the template
	 * @return string
	 */
	public function generate()
	{
		if (TL_MODE == 'BE')
		{
			$objTemplate = new \BackendTemplate('be_wildcard');

			$objTemplate->wildcard = '### ' . utf8_strtoupper($GLOBALS['TL_LANG']['CTE']['document_single']) . ($this->document ? ' (ID '.$this->document.')' : '') . ' ###';
			$objTemplate->id = $this->id;
			$objTemplate->href = 'contao/main.php?do=article&amp;table=tl_content&amp;act=edit&amp;id=' . $this->id;

			return $objTemplate->parse();
		}
		
		if (TL_MODE == 'FE' && !BE_USER_LOGGED_IN && ($this->invisible || ($this->start != '' && $this->start > time()) || ($this->stop != '' && $this->stop < time())))
		{
			return '';
		}
		
		$this->objDocument = DocumentModel::findByPk($this->document);
		if ($this->objDocument === null) {
			return '';
		}
		
		$objPublishCheck = DocumentModel::findPublishedByParentAndIdOrAlias($this->objDocument->id, [$this->objDocument->pid]);
		if ($objPublishCheck === null) {
			return '';
		}
		
		$objModule 								= new \ModuleModel();
		$objModule->type 						= 'documentreader';
		$objModule->document 					= $this->document;
		$objModule->document_template			= $this->document_template;

		$strClass = \Module::findClass($objModule->type);

		if (!class_exists($strClass))
		{
			return '';
		}

		$objModule->typePrefix = 'ce_';
		$objModule = new $strClass($objModule, $this->strColumn);

		// Overwrite spacing and CSS ID
		$objModule->origSpace = $objModule->space;
		$objModule->space = $this->space;
		$objModule->origCssID = $objModule->cssID;
		$objModule->cssID = $this->cssID;

		return $objModule->generate();
	}


	/**
	 * Generate the content element
	 */
	protected function compile()
	{
		return;
	}
}
