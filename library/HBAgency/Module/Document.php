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
 
namespace HBAgency\Module;

use HBAgency\Model\DocumentArchive as DocumentArchiveModel;
use HBAgency\Model\Document as DocumentModel;

/**
 * Class Document
 *
 * Base class for document modules
 * @copyright  HB Agency 2014
 * @author     Blair Winans <bwinans@hbagency.com>
 * @package    Document_Management
 */
abstract class Document extends \Module
{

	/**
	 * URL cache array
	 * @var array
	 */
	private static $arrUrlCache = array();
	
	/**
	 * URL cache array
	 * @var array
	 */
	private static $arrDownloadCache = array();


	/**
	 * Display a wildcard in the back end
	 * @return string
	 */
	public function generate()
	{
		// Store this ID so the NewsModel class and accompanying
		// hooks know which module is currently being generated.
		$GLOBALS['DOCUMENT']['LAST_GENERATED_MODULE'] = $this->id;
		
		return parent::generate();
	}


	/**
	 * Sort out protected archives
	 * @param array
	 * @return array
	 */
	protected function sortOutProtected($arrArchives)
	{
		if (BE_USER_LOGGED_IN || !is_array($arrArchives) || empty($arrArchives))
		{
			return $arrArchives;
		}

		$this->import('FrontendUser', 'User');
		$objArchive = DocumentArchiveModel::findMultipleByIds($arrArchives);
		$arrArchives = array();

		if ($objArchive !== null)
		{
			while ($objArchive->next())
			{
				if ($objArchive->protected)
				{
					if (!FE_USER_LOGGED_IN)
					{
						continue;
					}

					$groups = deserialize($objArchive->groups);

					if (!is_array($groups) || empty($groups) || !count(array_intersect($groups, $this->User->groups)))
					{
						continue;
					}
				}

				$arrArchives[] = $objArchive->id;
			}
		}

		return $arrArchives;
	}


	/**
	 * Parse an item and return it as string
	 * @param object
	 * @param boolean
	 * @param string
	 * @param integer
	 * @return string
	 */
	protected function parseDocument($objDocument, $strClass='', $intCount=0)
	{
		global $objPage;

		$objTemplate = new \FrontendTemplate($this->document_template);
		$objTemplate->setData($objDocument->row());

		$objTemplate->class = (($objDocument->cssClass != '') ? ' ' . $objDocument->cssClass : '') . $strClass;
		$objTemplate->documentHeadline = $objDocument->headline;
		$objTemplate->subHeadline = $objDocument->subheadline;
		$objTemplate->hasSubHeadline = $objDocument->subheadline ? true : false;
		$objTemplate->link = $this->generateDocumentUrl($objDocument);
		$objTemplate->download = $this->generateDownloadUrl($objDocument);
		$objTemplate->archive = $objDocument->getRelated('pid');
		$objTemplate->count = $intCount; // see #5708

		// Clean the RTE output
		if ($objDocument->teaser != '')
		{
			if ($objPage->outputFormat == 'xhtml')
			{
				$objTemplate->teaser = \StringUtil::toXhtml($objDocument->teaser);
			}
			else
			{
				$objTemplate->teaser = \StringUtil::toHtml5($objDocument->teaser);
			}

			$objTemplate->teaser = \StringUtil::encodeEmail($objTemplate->teaser);
		}

		$arrMeta = $this->getMetaFields($objDocument);

		// Add the meta information
		$objTemplate->date = $arrMeta['date'];
		$objTemplate->hasMetaFields = !empty($arrMeta);
		$objTemplate->timestamp = $objDocument->date;
		$objTemplate->author = $arrMeta['author'];
		$objTemplate->datetime = date('Y-m-d\TH:i:sP', $objDocument->date);

		// Add the document
		if ($objDocument->singleSRC != '')
		{
			$objModel = \FilesModel::findByUuid($objDocument->singleSRC);

			if ($objModel === null)
			{
				if (!\Validator::isUuid($objDocument->singleSRC))
				{
					$objTemplate->text = '<p class="error">'.$GLOBALS['TL_LANG']['ERR']['version2format'].'</p>';
				}
			}
			elseif (is_file(TL_ROOT . '/' . $objModel->path))
			{
				// Do not override the field now that we have a model registry (see #6303)
				$arrDocument = $objDocument->row();

				// Override the default image size
				if ($this->imgSize != '')
				{
					$size = deserialize($this->imgSize);

					if ($size[0] > 0 || $size[1] > 0 || is_numeric($size[2]))
					{
						$arrDocument['size'] = $this->imgSize;
					}
				}

				$arrDocument['singleSRC'] = $objModel->path;
				//$this->addImageToTemplate($objTemplate, $arrDocument);
			}
		}

		// HOOK: add custom logic
		if (isset($GLOBALS['TL_HOOKS']['parseDocuments']) && is_array($GLOBALS['TL_HOOKS']['parseDocuments']))
		{
			foreach ($GLOBALS['TL_HOOKS']['parseDocuments'] as $callback)
			{
				$this->import($callback[0]);
				$this->{$callback[0]}->{$callback[1]}($objTemplate, $objDocument->row(), $this);
			}
		}

		return $objTemplate->parse();
	}


	/**
	 * Parse one or more items and return them as array
	 * @param object
	 * @param boolean
	 * @return array
	 */
	protected function parseDocuments($objDocuments)
	{
		$limit = $objDocuments->count();

		if ($limit < 1)
		{
			return array();
		}

		$count = 0;
		$arrDocuments = array();

		while ($objDocuments->next())
		{
			$arrDocuments[] = $this->parseDocument($objDocuments, ((++$count == 1) ? ' first' : '') . (($count == $limit) ? ' last' : '') . ((($count % 2) == 0) ? ' odd' : ' even'), $count);
		}

		return $arrDocuments;
	}


	/**
	 * Return the meta fields of a document article as array
	 * @param object
	 * @return array
	 */
	protected function getMetaFields($objDocument)
	{
		$meta = deserialize($this->document_metaFields);

		if (!is_array($meta))
		{
			return array();
		}

		global $objPage;
		$return = array();

		foreach ($meta as $field)
		{
			switch ($field)
			{
				case 'date':
					$return['date'] = \Date::parse($objPage->datimFormat, $objDocument->date);
					break;

				case 'author':
					if (($objAuthor = $objDocument->getRelated('author')) !== null)
					{
						$return['author'] = $GLOBALS['TL_LANG']['MSC']['by'] . ' ' . $objAuthor->name;
					}
					break;
			}
		}

		return $return;
	}


	/**
	 * Generate a URL and return it as string
	 * @param object
	 * @return string
	 */
	protected function generateDocumentUrl($objItem)
	{
		$strCacheKey = 'id_' . $objItem->id;

		// Load the URL from cache
		if (isset(self::$arrUrlCache[$strCacheKey]))
		{
			return self::$arrUrlCache[$strCacheKey];
		}

		// Link to the jumpTo page
		if (self::$arrUrlCache[$strCacheKey] === null)
		{
			$objPage = \PageModel::findByPk($objItem->getRelated('pid')->jumpTo);

			if ($objPage === null)
			{
				self::$arrUrlCache[$strCacheKey] = ampersand(\Environment::get('request'), true);
			}
			else
			{
				self::$arrUrlCache[$strCacheKey] = ampersand($this->generateFrontendUrl($objPage->row(), ((\Config::get('useAutoItem') && !\Config::get('disableAlias')) ?  '/' : '/items/') . ((!\Config::get('disableAlias') && $objItem->alias != '') ? $objItem->alias : $objItem->id)));
			}
		}

		return self::$arrUrlCache[$strCacheKey];
	}
	
	/**
	 * Generate a download URL and return it as string
	 * @param object
	 * @return string
	 */
	protected function generateDownloadUrl($objItem)
	{
		$strCacheKey = 'id_' . $objItem->id;

		// Load the URL from cache
		if (isset(self::$arrDownloadCache[$strCacheKey]))
		{
			return self::$arrDownloadCache[$strCacheKey];
		}

		// Initialize the cache
		self::$arrDownloadCache[$strCacheKey] = null;

		if (!empty($objItem->url))
		{
			// Link to an external page
			self::$arrDownloadCache[$strCacheKey] = ampersand($objItem->url);
		}

		// Link to the document
		if (self::$arrDownloadCache[$strCacheKey] === null)
		{
    		// Return if there is no file
    		if ($objItem->singleSRC == '')
    		{
    			self::$arrDownloadCache[$strCacheKey] = '#';
    		}
    
    		$objFile = \FilesModel::findByUuid($objItem->singleSRC);
    
    		if ($objFile === null)
    		{
    			self::$arrDownloadCache[$strCacheKey] = '#';
    		}
    
    		$allowedDownload = trimsplit(',', strtolower(\Config::get('allowedDownload')));
    
    		// Return if the file type is not allowed
    		if (!in_array($objFile->extension, $allowedDownload))
    		{
    			self::$arrDownloadCache[$strCacheKey] = '#';
    		}
    
    		$file = \Input::get('file', true);
    
    		// Send the file to the browser and do not send a 404 header (see #4632)
    		if ($file != '' && $file == $objFile->path)
    		{
    			\Controller::sendFileToBrowser($file);
		    }
    		
			$strHref = \Environment::get('request');

            // Remove an existing file parameter (see #5683)
    		if (preg_match('/(&(amp;)?|\?)file=/', $strHref))
    		{
    			$strHref = preg_replace('/(&(amp;)?|\?)file=[^&]+/', '', $strHref);
    		}

    		$strHref .= ((\Config::get('disableAlias') || strpos($strHref, '?') !== false) ? '&amp;' : '?') . 'file=' . \System::urlEncode($objFile->path);

			self::$arrDownloadCache[$strCacheKey] = $strHref;
		}

		return self::$arrDownloadCache[$strCacheKey];
	}

}
