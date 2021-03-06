<?php

/**
 * Document management for Contao Open Source CMS
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */
 
namespace Rhyme\ContaoDocumentsBundle\Backend\Document;


/**
 * Class Callbacks
 *
 * Provide miscellaneous methods that are used by the data configuration array.
 * @copyright  Rhyme 2021


 * @package    Document_Management
 */
class Callbacks extends \Backend
{

	/**
	 * Import the back end user object
	 */
	public function __construct()
	{
		parent::__construct();
		$this->import('BackendUser', 'User');
	}


	/**
	 * Check permissions to edit table tl_document
	 */
	public function checkPermission()
	{
		if ($this->User->isAdmin)
		{
			return;
		}

		// Set the root IDs
		if (!is_array($this->User->document) || empty($this->User->document))
		{
			$root = array(0);
		}
		else
		{
			$root = $this->User->document;
		}

		$id = strlen(\Input::get('id')) ? \Input::get('id') : CURRENT_ID;

		// Check current action
		switch (\Input::get('act'))
		{
			case 'paste':
				// Allow
				break;

			case 'create':
				if (!strlen(\Input::get('pid')) || !in_array(\Input::get('pid'), $root))
				{
					$this->log('Not enough permissions to create document items in document archive ID "'.\Input::get('pid').'"', __METHOD__, TL_ERROR);
					$this->redirect('contao/main.php?act=error');
				}
				break;

			case 'cut':
			case 'copy':
				if (!in_array(\Input::get('pid'), $root))
				{
					$this->log('Not enough permissions to '.\Input::get('act').' document item ID "'.$id.'" to document archive ID "'.\Input::get('pid').'"', __METHOD__, TL_ERROR);
					$this->redirect('contao/main.php?act=error');
				}
				// NO BREAK STATEMENT HERE

			case 'edit':
			case 'show':
			case 'delete':
			case 'toggle':
			case 'feature':
				$objArchive = $this->Database->prepare("SELECT pid FROM tl_document WHERE id=?")
											 ->limit(1)
											 ->execute($id);

				if ($objArchive->numRows < 1)
				{
					$this->log('Invalid document item ID "'.$id.'"', __METHOD__, TL_ERROR);
					$this->redirect('contao/main.php?act=error');
				}

				if (!in_array($objArchive->pid, $root))
				{
					$this->log('Not enough permissions to '.\Input::get('act').' document item ID "'.$id.'" of document archive ID "'.$objArchive->pid.'"', __METHOD__, TL_ERROR);
					$this->redirect('contao/main.php?act=error');
				}
				break;

			case 'select':
			case 'editAll':
			case 'deleteAll':
			case 'overrideAll':
			case 'cutAll':
			case 'copyAll':
				if (!in_array($id, $root))
				{
					$this->log('Not enough permissions to access document archive ID "'.$id.'"', __METHOD__, TL_ERROR);
					$this->redirect('contao/main.php?act=error');
				}

				$objArchive = $this->Database->prepare("SELECT id FROM tl_document WHERE pid=?")
											 ->execute($id);

				if ($objArchive->numRows < 1)
				{
					$this->log('Invalid document archive ID "'.$id.'"', __METHOD__, TL_ERROR);
					$this->redirect('contao/main.php?act=error');
				}

				$session = $this->Session->getData();
				$session['CURRENT']['IDS'] = array_intersect($session['CURRENT']['IDS'], $objArchive->fetchEach('id'));
				$this->Session->setData($session);
				break;

			default:
				if (strlen(\Input::get('act')))
				{
					$this->log('Invalid command "'.\Input::get('act').'"', __METHOD__, TL_ERROR);
					$this->redirect('contao/main.php?act=error');
				}
				elseif (!in_array($id, $root))
				{
					$this->log('Not enough permissions to access document archive ID ' . $id, __METHOD__, TL_ERROR);
					$this->redirect('contao/main.php?act=error');
				}
				break;
		}
	}


	/**
	 * Auto-generate the document alias if it has not been set yet
	 * @param mixed
	 * @param \\DataContainer
	 * @return string
	 * @throws \Exception
	 */
	public function generateAlias($varValue, \DataContainer $dc)
	{
		$autoAlias = false;

		// Generate alias if there is none
		if ($varValue == '')
		{
			$autoAlias = true;
			$varValue = standardize(\StringUtil::restoreBasicEntities($dc->activeRecord->headline));
		}

		$objAlias = $this->Database->prepare("SELECT id FROM tl_document WHERE alias=?")
								   ->execute($varValue);

		// Check whether the document alias exists
		if ($objAlias->numRows > 1 && !$autoAlias)
		{
			throw new \Exception(sprintf($GLOBALS['TL_LANG']['ERR']['aliasExists'], $varValue));
		}

		// Add ID to alias
		if ($objAlias->numRows && $autoAlias)
		{
			$varValue .= '-' . $dc->id;
		}

		return $varValue;
	}
    
    
    /**
	 * Check that either singleSRC field OR url field is filled out!
	 * @param mixed
	 * @param \\DataContainer
	 * @return string
	 * @throws \Exception
	 */
	public function checkRequired($varValue, \DataContainer $dc)
	{
		// Return if there is no active record (override all)
		if ($dc->activeRecord)
		{
			if($varValue=='' && \Input::post('url') == '' && \Input::post('singleSRC') == '')
			{
    			throw new \Exception(sprintf($GLOBALS['TL_LANG']['ERR']['requiredDocumentField'], $varValue));
			}
		}
		
		return $varValue;
	}

	/**
	 * Add the type of input field
	 * @param array
	 * @return string
	 */
	public function listDocuments($arrRow)
	{
		return '<div class="tl_content_left">' . $arrRow['headline'] . ' <span style="color:#b3b3b3;padding-left:3px">[' . \Date::parse(\Config::get('datimFormat'), $arrRow['date']) . ']</span></div>';
	}


	/**
	 * Get all documents and return them as array
	 * @param \\DataContainer
	 * @return array
	 */
	public function getDocumentAlias(\DataContainer $dc)
	{
		$arrPids = array();
		$arrAlias = array();

		if (!$this->User->isAdmin)
		{
			foreach ($this->User->pagemounts as $id)
			{
				$arrPids[] = $id;
				$arrPids = array_merge($arrPids, $this->Database->getChildRecords($id, 'tl_page'));
			}

			if (empty($arrPids))
			{
				return $arrAlias;
			}

			$objAlias = $this->Database->prepare("SELECT a.id, a.title, a.inColumn, p.title AS parent FROM tl_article a LEFT JOIN tl_page p ON p.id=a.pid WHERE a.pid IN(". implode(',', array_map('intval', array_unique($arrPids))) .") ORDER BY parent, a.sorting")
									   ->execute($dc->id);
		}
		else
		{
			$objAlias = $this->Database->prepare("SELECT a.id, a.title, a.inColumn, p.title AS parent FROM tl_article a LEFT JOIN tl_page p ON p.id=a.pid ORDER BY parent, a.sorting")
									   ->execute($dc->id);
		}

		if ($objAlias->numRows)
		{
			\System::loadLanguageFile('tl_article');

			while ($objAlias->next())
			{
				$arrAlias[$objAlias->parent][$objAlias->id] = $objAlias->title . ' (' . ($GLOBALS['TL_LANG']['tl_article'][$objAlias->inColumn] ?: $objAlias->inColumn) . ', ID ' . $objAlias->id . ')';
			}
		}

		return $arrAlias;
	}


	/**
	 * Add the source options depending on the allowed fields (see #5498)
	 * @param \\DataContainer
	 * @return array
	 */
	public function getSourceOptions(\DataContainer $dc)
	{
		if ($this->User->isAdmin)
		{
			return array('default', 'internal', 'article', 'external');
		}

		$arrOptions = array('default');

		// Add the "internal" option
		if ($this->User->hasAccess('tl_document::jumpTo', 'alexf'))
		{
			$arrOptions[] = 'internal';
		}

		// Add the "article" option
		if ($this->User->hasAccess('tl_document::articleId', 'alexf'))
		{
			$arrOptions[] = 'article';
		}

		// Add the "external" option
		if ($this->User->hasAccess('tl_document::url', 'alexf') && $this->User->hasAccess('tl_document::target', 'alexf'))
		{
			$arrOptions[] = 'external';
		}

		// Add the option currently set
		if ($dc->activeRecord && $dc->activeRecord->source != '')
		{
			$arrOptions[] = $dc->activeRecord->source;
			$arrOptions = array_unique($arrOptions);
		}

		return $arrOptions;
	}


	/**
	 * Adjust start end end time of the event based on date, span, startTime and endTime
	 * @param \\DataContainer
	 */
	public function adjustTime(\DataContainer $dc)
	{
		// Return if there is no active record (override all)
		if (!$dc->activeRecord)
		{
			return;
		}

		$arrSet['date'] = strtotime(date('Y-m-d', $dc->activeRecord->date) . ' ' . date('H:i:s', $dc->activeRecord->time));
		$arrSet['time'] = $arrSet['date'];

		$this->Database->prepare("UPDATE tl_document %s WHERE id=?")->set($arrSet)->execute($dc->id);
	}
	

	/**
	 * Return the link picker wizard
	 * @param \\DataContainer
	 * @return string
	 */
	public function pagePicker(\DataContainer $dc)
	{
		return ' <a href="contao/page.php?do='.\Input::get('do').'&amp;table='.$dc->table.'&amp;field='.$dc->field.'&amp;value='.str_replace(array('{{link_url::', '}}'), '', $dc->value).'" onclick="Backend.getScrollOffset();Backend.openModalSelector({\'width\':768,\'title\':\''.specialchars(str_replace("'", "\\'", $GLOBALS['TL_LANG']['MOD']['page'][0])).'\',\'url\':this.href,\'id\':\''.$dc->field.'\',\'tag\':\'ctrl_'.$dc->field . ((\Input::get('act') == 'editAll') ? '_' . $dc->id : '').'\',\'self\':this});return false">' . \Image::getHtml('pickpage.gif', $GLOBALS['TL_LANG']['MSC']['pagepicker'], 'style="vertical-align:top;cursor:pointer"') . '</a>';
	}


	/**
	 * Return the "feature/unfeature element" button
	 * @param array
	 * @param string
	 * @param string
	 * @param string
	 * @param string
	 * @param string
	 * @return string
	 */
	public function iconFeatured($row, $href, $label, $title, $icon, $attributes)
	{
		if (strlen(\Input::get('fid')))
		{
			$this->toggleFeatured(\Input::get('fid'), (\Input::get('state') == 1));
			$this->redirect($this->getReferer());
		}

		// Check permissions AFTER checking the fid, so hacking attempts are logged
		if (!$this->User->hasAccess('tl_document::featured', 'alexf'))
		{
			return '';
		}

		$href .= '&amp;fid='.$row['id'].'&amp;state='.($row['featured'] ? '' : 1);

		if (!$row['featured'])
		{
			$icon = 'featured_.gif';
		}

		return '<a href="'.$this->addToUrl($href).'" title="'.specialchars($title).'"'.$attributes.'>'.\Image::getHtml($icon, $label).'</a> ';
	}


	/**
	 * Feature/unfeature a document item
	 * @param integer
	 * @param boolean
	 * @return string
	 */
	public function toggleFeatured($intId, $blnVisible)
	{
		// Check permissions to edit
		\Input::setGet('id', $intId);
		\Input::setGet('act', 'feature');
		$this->checkPermission();

		// Check permissions to feature
		if (!$this->User->hasAccess('tl_document::featured', 'alexf'))
		{
			$this->log('Not enough permissions to feature/unfeature document item ID "'.$intId.'"', __METHOD__, TL_ERROR);
			$this->redirect('contao/main.php?act=error');
		}

		$objVersions = new \Versions('tl_document', $intId);
		$objVersions->initialize();

		// Trigger the save_callback
		if (is_array($GLOBALS['TL_DCA']['tl_document']['fields']['featured']['save_callback']))
		{
			foreach ($GLOBALS['TL_DCA']['tl_document']['fields']['featured']['save_callback'] as $callback)
			{
				if (is_array($callback))
				{
					$this->import($callback[0]);
					$blnVisible = $this->$callback[0]->$callback[1]($blnVisible, $this);
				}
				elseif (is_callable($callback))
				{
					$blnVisible = $callback($blnVisible, $this);
				}
			}
		}

		// Update the database
		$this->Database->prepare("UPDATE tl_document SET tstamp=". time() .", featured='" . ($blnVisible ? 1 : '') . "' WHERE id=?")
					   ->execute($intId);

		$objVersions->create();
		$this->log('A new version of record "tl_document.id='.$intId.'" has been created'.$this->getParentEntries('tl_document', $intId), __METHOD__, TL_GENERAL);
	}


	/**
	 * Return the "toggle visibility" button
	 * @param array
	 * @param string
	 * @param string
	 * @param string
	 * @param string
	 * @param string
	 * @return string
	 */
	public function toggleIcon($row, $href, $label, $title, $icon, $attributes)
	{
		if (strlen(\Input::get('tid')))
		{
			$this->toggleVisibility(\Input::get('tid'), (\Input::get('state') == 1), (@func_get_arg(12) ?: null));
			$this->redirect($this->getReferer());
		}

		// Check permissions AFTER checking the tid, so hacking attempts are logged
		if (!$this->User->hasAccess('tl_document::published', 'alexf'))
		{
			return '';
		}

		$href .= '&amp;tid='.$row['id'].'&amp;state='.($row['published'] ? '' : 1);

		if (!$row['published'])
		{
			$icon = 'invisible.gif';
		}

		return '<a href="'.$this->addToUrl($href).'" title="'.specialchars($title).'"'.$attributes.'>'.\Image::getHtml($icon, $label).'</a> ';
	}


	/**
	 * Disable/enable a user group
	 * @param integer
	 * @param boolean
	 * @param \\DataContainer
	 */
	public function toggleVisibility($intId, $blnVisible, \DataContainer $dc=null)
	{
		// Check permissions to edit
		\Input::setGet('id', $intId);
		\Input::setGet('act', 'toggle');
		$this->checkPermission();

		// Check permissions to publish
		if (!$this->User->hasAccess('tl_document::published', 'alexf'))
		{
			$this->log('Not enough permissions to publish/unpublish document item ID "'.$intId.'"', __METHOD__, TL_ERROR);
			$this->redirect('contao/main.php?act=error');
		}

		$objVersions = new \Versions('tl_document', $intId);
		$objVersions->initialize();

		// Trigger the save_callback
		if (is_array($GLOBALS['TL_DCA']['tl_document']['fields']['published']['save_callback']))
		{
			foreach ($GLOBALS['TL_DCA']['tl_document']['fields']['published']['save_callback'] as $callback)
			{
				if (is_array($callback))
				{
					$this->import($callback[0]);
					$blnVisible = $this->$callback[0]->$callback[1]($blnVisible, ($dc ?: $this));
				}
				elseif (is_callable($callback))
				{
					$blnVisible = $callback($blnVisible, ($dc ?: $this));
				}
			}
		}

		// Update the database
		$this->Database->prepare("UPDATE tl_document SET tstamp=". time() .", published='" . ($blnVisible ? 1 : '') . "' WHERE id=?")
					   ->execute($intId);

		$objVersions->create();
		$this->log('A new version of record "tl_document.id='.$intId.'" has been created'.$this->getParentEntries('tl_document', $intId), __METHOD__, TL_GENERAL);
	}
}
