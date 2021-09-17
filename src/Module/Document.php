<?php

/**
 * Document management for Contao Open Source CMS
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */
 
namespace Rhyme\ContaoDocumentsBundle\Module;

use Rhyme\ContaoDocumentsBundle\Model\DocumentArchive as DocumentArchiveModel;
use Rhyme\ContaoDocumentsBundle\Model\Document as DocumentModel;

/**
 * Class Document
 * @package Rhyme\ContaoDocumentsBundle\Module
 */
abstract class Document extends \Module
{


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
		if (BE_USER_LOGGED_IN === true || !\is_array($arrArchives) || empty($arrArchives))
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
					if (FE_USER_LOGGED_IN !== true)
					{
						continue;
					}

					$groups = deserialize($objArchive->groups);

					if (!\is_array($groups) || empty($groups) || !\count(\array_intersect($groups, $this->User->groups)))
					{
						continue;
					}
				}

				$arrArchives[] = $objArchive->id;
			}
		}

		return $arrArchives;
	}

}
