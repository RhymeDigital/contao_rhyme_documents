<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace HBAgency\Module\Document;

use HBAgency\Module\Document as DocumentModule;
use HBAgency\Model\Document as DocumentModel;


/**
 * Front end module "Reader".
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class Reader extends DocumentModule
{

	/**
	 * Template
	 * @var string
	 */
	protected $strTemplate = 'mod_documentreader';


	/**
	 * Display a wildcard in the back end
	 * @return string
	 */
	public function generate()
	{
		if (TL_MODE == 'BE')
		{
			$objTemplate = new \BackendTemplate('be_wildcard');

			$objTemplate->wildcard = '### ' . utf8_strtoupper($GLOBALS['TL_LANG']['FMD']['documentreader'][0]) . ' ###';
			$objTemplate->title = $this->headline;
			$objTemplate->id = $this->id;
			$objTemplate->link = $this->name;
			$objTemplate->href = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;

			return $objTemplate->parse();
		}

		if (!$this->document)
		{
			//global $objPage;
			//$objPage->noSearch = 1;
			//$objPage->cache = 0;
			return '';
		}

		// All archives
		$this->document_archives = array_keys(array_fill(0, 99999, 1));
		
		return parent::generate();
	}


	/**
	 * Generate the module
	 */
	protected function compile()
	{
		global $objPage;

		$this->Template->articles = '';
		$this->Template->referer = 'javascript:history.go(-1)';
		$this->Template->back = $GLOBALS['TL_LANG']['MSC']['goBack'];

		// Get the item
		$objItem = DocumentModel::findByPk($this->document);

		if ($objItem === null)
		{
			// Do not index or cache the page
			//$objPage->noSearch = 1;
			//$objPage->cache = 0;

			// Send a 404 header
			//header('HTTP/1.1 404 Not Found');
			//$this->Template->articles = '<p class="error">' . sprintf($GLOBALS['TL_LANG']['MSC']['invalidPage'], \Input::get('items')) . '</p>';
			return;
		}

		$arrItems = $this->parseDocument($objItem);
		$this->Template->document = $arrItems;
	}
}
