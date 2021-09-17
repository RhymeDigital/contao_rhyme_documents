<?php

/**
 * Document management for Contao Open Source CMS
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */
 
namespace Rhyme\ContaoDocumentsBundle\Module\Document;

use Contao\Input;
use Contao\Config;
use Contao\Pagination;
use Contao\BackendTemplate;
use Rhyme\ContaoDocumentsBundle\Module\Document as Document_Module;
use Rhyme\ContaoDocumentsBundle\Helper\DocumentHelper;
use Rhyme\ContaoDocumentsBundle\Model\Document as DocumentModel;

/**
 * Class Lister
 * @package Rhyme\Module\Document
 */
class Lister extends Document_Module
{

	/**
	 * Template
	 * @var string
	 */
	protected $strTemplate = 'mod_documentlist';


	/**
	 * Display a wildcard in the back end
	 * @return string
	 */
	public function generate()
	{
		if (TL_MODE == 'BE')
		{
			$objTemplate = new BackendTemplate('be_wildcard');

			$objTemplate->wildcard = '### ' . \utf8_strtoupper($GLOBALS['TL_LANG']['FMD']['documentlist'][0]) . ' ###';
			$objTemplate->title = $this->headline;
			$objTemplate->id = $this->id;
			$objTemplate->link = $this->name;
			$objTemplate->href = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;

			return $objTemplate->parse();
		}

		$this->document_archives = $this->sortOutProtected(\deserialize($this->document_archives));

		// Return if there are no archives
		if (!\is_array($this->document_archives) || empty($this->document_archives))
		{
			return '';
		}

		return parent::generate();
	}


	/**
	 * Generate the module
     * @throws \Exception
	 */
	protected function compile()
	{
		$offset = \intval($this->skipFirst);
		$limit = null;

		// Maximum number of items
		if ($this->numberOfItems > 0)
		{
			$limit = $this->numberOfItems;
		}

		// Handle featured document
		if ($this->document_featured == 'featured')
		{
			$blnFeatured = true;
		}
		elseif ($this->document_featured == 'unfeatured')
		{
			$blnFeatured = false;
		}
		else
		{
			$blnFeatured = null;
		}

		$this->Template->articles = array();
		$this->Template->empty = $GLOBALS['TL_LANG']['MSC']['emptyList'];

		// Get the total number of items
		$intTotal = DocumentModel::countPublishedByPids($this->document_archives, $blnFeatured);

		if ($intTotal < 1)
		{
			return;
		}

		$total = $intTotal - $offset;

		// Split the results
		if ($this->perPage > 0 && (!isset($limit) || $this->numberOfItems > $this->perPage))
		{
			// Adjust the overall limit
			if (isset($limit))
			{
				$total = \min($limit, $total);
			}

			// Get the current page
			$id = 'page_doc_' . $this->id;
			$page = Input::get($id) ?: 1;

			// Do not index or cache the page if the page number is outside the range
			if ($page < 1 || $page > \max(\ceil($total/$this->perPage), 1))
			{
				global $objPage;
				$objPage->noSearch = 1;
				$objPage->cache = 0;

				// Send a 404 header
				\header('HTTP/1.1 404 Not Found');
				return;
			}

			// Set limit and offset
			$limit = $this->perPage;
			$offset += (\max($page, 1) - 1) * $this->perPage;
			$skip = \intval($this->skipFirst);

			// Overall limit
			if ($offset + $limit > $total + $skip)
			{
				$limit = $total + $skip - $offset;
			}

			// Add the pagination menu
			$objPagination = new Pagination($total, $this->perPage, Config::get('maxPaginationLinks'), $id);
			$this->Template->pagination = $objPagination->generate("\n  ");
		}

		// Get the items
		if (isset($limit))
		{
			$objDocuments = DocumentModel::findPublishedByPids($this->document_archives, $blnFeatured, $limit, $offset);
		}
		else
		{
			$objDocuments = DocumentModel::findPublishedByPids($this->document_archives, $blnFeatured, 0, $offset);
		}

		// Add the articles
		if ($objDocuments !== null)
		{
			$this->Template->articles = DocumentHelper::parseDocuments($objDocuments, $this);
		}

		$this->Template->archives = $this->document_archives;
	}
}
