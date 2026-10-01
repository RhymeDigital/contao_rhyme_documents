<?php

/**
 *  Document management for Contao Open Source CMS
 *
 *  Copyright (c) 2026 Rhyme Digital, LLC.
 *
 *  @link			https://rhyme.digital
 *  @license		https://www.gnu.org/licenses/lgpl-3.0.txt LGPL
 */

declare(strict_types=1);

namespace Rhyme\ContaoDocumentsBundle\Backend\Document;

use Contao\Backend;
use Contao\BackendUser;
use Contao\Config;
use Contao\Controller;
use Contao\CoreBundle\Exception\AccessDeniedException;
use Contao\DataContainer;
use Contao\Database;
use Contao\Date;
use Contao\Image;
use Contao\Input;
use Contao\StringUtil;
use Contao\System;
use Contao\Versions;
use Rhyme\ContaoDocumentsBundle\Model\Document;

class Callbacks extends Backend
{

	/**
	 * Make constructor public
	 */
	public function __construct()
	{
		parent::__construct();
	}


	/**
	 * Check permissions to edit table tl_document
	 */
	public function checkPermission($dc)
	{
        $t = Document::getTable();
        $beUser = BackendUser::getInstance();

        if ($beUser?->isAdmin) {
            return;
        }

        $objSession = System::getContainer()->get('request_stack')->getSession();

        // Set root IDs
        if (empty($beUser->document) || !is_array($beUser->document))
        {
            $root = array(0);
        }
        else
        {
            $root = $beUser->document;
        }

        $id = \strlen((string)Input::get('id')) ? Input::get('id') : $dc->currentPid;

        // Check current action
        switch (Input::get('act'))
        {
            case 'paste':
            case 'select':
                // Check currentId here (see #247)
                if (!\in_array($dc->currentPid, $root))
                {
                    throw new AccessDeniedException('Not enough permissions to access document archive ID ' . $id . '.');
                }
                break;

            case 'create':
            case 'cut':
            case 'copy':
                $pid = Input::get('pid');

                // Get document ID
                if (Input::get('mode') == 1)
                {
                    $objField = Database::getInstance()->prepare("SELECT pid FROM {$t} WHERE id=?")
                                               ->limit(1)
                                               ->execute(Input::get('pid'));

                    if ($objField->numRows < 1)
                    {
                        throw new AccessDeniedException('Invalid document field ID ' . Input::get('pid') . '.');
                    }

                    $pid = $objField->pid;
                }

                if (!in_array($pid, $root))
                {
                    throw new AccessDeniedException('Not enough permissions to ' . Input::get('act') . ' document field ID ' . $id . ' to document ID ' . $pid . '.');
                }

                if (Input::get('act') === 'create')
                {
                    break;
                }
            // no break

            case 'edit':
            case 'show':
            case 'delete':
            case 'toggle':
                $objField = Database::getInstance()->prepare("SELECT pid FROM {$t} WHERE id=?")
                                           ->limit(1)
                                           ->execute($id);

                if ($objField->numRows < 1)
                {
                    throw new AccessDeniedException('Invalid document ID ' . $id . '.');
                }

                if (!in_array($objField->pid, $root))
                {
                    throw new AccessDeniedException('Not enough permissions to ' . Input::get('act') . ' document ID ' . $id . ' of document ID ' . $objField->pid . '.');
                }
                break;

            case 'editAll':
            case 'deleteAll':
            case 'overrideAll':
            case 'cutAll':
            case 'copyAll':
                if (!in_array($id, $root))
                {
                    throw new AccessDeniedException('Not enough permissions to access document ID ' . $id . '.');
                }

                $objDocument = Database::getInstance()->prepare("SELECT id FROM {$t} WHERE pid=?")
                                          ->execute($id);

                $session = $objSession->all();
                $session['CURRENT']['IDS'] = array_intersect((array) $session['CURRENT']['IDS'], $objDocument->fetchEach('id'));
                $objSession->replace($session);
                break;

            default:
                if (Input::get('act'))
                {
                    throw new AccessDeniedException('Invalid command "' . Input::get('act') . '".');
                }

                if (!\in_array($id, $root))
                {
                    throw new AccessDeniedException('Not enough permissions to access document ID ' . $id . '.');
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
	public function generateAlias($varValue, DataContainer $dc)
	{
		$autoAlias = false;

		// Generate alias if there is none
		if (empty($varValue))
		{
			$autoAlias = true;
			$varValue = StringUtil::standardize(StringUtil::restoreBasicEntities($dc->activeRecord->headline));
		}

		$objAlias = Database::getInstance()->prepare("SELECT id FROM tl_document WHERE alias=?")
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
	public function checkRequired($varValue, DataContainer $dc)
	{
		// Return if there is no active record (override all)
		if ($dc->activeRecord)
		{
			if (empty($varValue) && empty(Input::post('url')) && empty(Input::post('singleSRC')))
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
		return '<div class="tl_content_left">' . $arrRow['headline'] . ' <span style="color:#b3b3b3;padding-left:3px">[' . Date::parse(Config::get('datimFormat'), $arrRow['date']) . ']</span></div>';
	}


	/**
	 * Get all documents and return them as array
	 * @param \\DataContainer
	 * @return array
	 */
	public function getDocumentAlias(DataContainer $dc)
	{
		$arrPids = array();
		$arrAlias = array();

        $beUser = BackendUser::getInstance();

		if (!$beUser->isAdmin)
		{
			foreach ($beUser->pagemounts as $id)
			{
				$arrPids[] = $id;
				$arrPids = \array_merge($arrPids, Database::getInstance()->getChildRecords($id, 'tl_page'));
			}

			if (empty($arrPids))
			{
				return $arrAlias;
			}

			$objAlias = Database::getInstance()->prepare("SELECT a.id, a.title, a.inColumn, p.title AS parent FROM tl_article a LEFT JOIN tl_page p ON p.id=a.pid WHERE a.pid IN(". \implode(',', \array_map('intval', \array_unique($arrPids))) .") ORDER BY parent, a.sorting")
									   ->execute($dc->id);
		}
		else
		{
			$objAlias = Database::getInstance()->prepare("SELECT a.id, a.title, a.inColumn, p.title AS parent FROM tl_article a LEFT JOIN tl_page p ON p.id=a.pid ORDER BY parent, a.sorting")
									   ->execute($dc->id);
		}

		if ($objAlias->numRows)
		{
            System::loadLanguageFile('tl_article');

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
	public function getSourceOptions(DataContainer $dc)
	{
        $beUser = BackendUser::getInstance();

		if ($beUser?->isAdmin) {
			return array('default', 'internal', 'article', 'external');
		}

		$arrOptions = array('default');

		// Add the "internal" option
		if ($beUser->hasAccess('tl_document::jumpTo', 'alexf'))
		{
			$arrOptions[] = 'internal';
		}

		// Add the "article" option
		if ($beUser->hasAccess('tl_document::articleId', 'alexf'))
		{
			$arrOptions[] = 'article';
		}

		// Add the "external" option
		if ($beUser->hasAccess('tl_document::url', 'alexf') && $beUser->hasAccess('tl_document::target', 'alexf'))
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
	public function adjustTime(DataContainer $dc)
	{
		// Return if there is no active record (override all)
		if (!$dc->activeRecord)
		{
			return;
		}

		$arrSet['date'] = strtotime(date('Y-m-d', $dc->activeRecord->date) . ' ' . date('H:i:s', $dc->activeRecord->time));
		$arrSet['time'] = $arrSet['date'];

		Database::getInstance()->prepare("UPDATE tl_document %s WHERE id=?")->set($arrSet)->execute($dc->id);
	}
	

	/**
	 * Return the link picker wizard
	 * @param \\DataContainer
	 * @return string
	 */
	public function pagePicker(DataContainer $dc)
	{
		return ' <a href="contao/page.php?do='.Input::get('do').'&amp;table='.$dc->table.'&amp;field='.$dc->field.'&amp;value='.str_replace(array('{{link_url::', '}}'), '', $dc->value).'" onclick="Backend.getScrollOffset();Backend.openModalSelector({\'width\':768,\'title\':\''.StringUtil::specialchars(\str_replace("'", "\\'", $GLOBALS['TL_LANG']['MOD']['page'][0])).'\',\'url\':this.href,\'id\':\''.$dc->field.'\',\'tag\':\'ctrl_'.$dc->field . ((Input::get('act') === 'editAll') ? '_' . $dc->id : '').'\',\'self\':this});return false">' . Image::getHtml('pickpage.gif', $GLOBALS['TL_LANG']['MSC']['pagepicker'], 'style="vertical-align:top;cursor:pointer"') . '</a>';
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
		if (\trim((string)Input::get('fid')) !== '')
		{
			$this->toggleFeatured(Input::get('fid'), (Input::get('state') == 1));
			Controller::redirect(System::getReferer());
		}

		// Check permissions AFTER checking the fid, so hacking attempts are logged
		if (!BackendUser::getInstance()->hasAccess('tl_document::featured', 'alexf'))
		{
			return '';
		}

		$href .= '&amp;fid='.$row['id'].'&amp;state='.($row['featured'] ? '' : 1);

		if (!$row['featured'])
		{
			$icon = 'featured_.gif';
		}

		return '<a href="'.Controller::addToUrl($href).'" title="'.StringUtil::specialchars($title).'"'.$attributes.'>'.Image::getHtml($icon, $label).'</a> ';
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
		Input::setGet('id', $intId);
		Input::setGet('act', 'feature');
		$this->checkPermission();
        $beUser = BackendUser::getInstance();

		// Check permissions to feature
		if (!$beUser->hasAccess('tl_document::featured', 'alexf'))
		{
            $message = 'Not enough permissions to feature/unfeature document item ID "'.$intId.'"';
            System::getContainer()->get('monolog.logger.contao.error')->error($message);
            throw new AccessDeniedException($message);
		}

		$objVersions = new Versions('tl_document', $intId);
		$objVersions->initialize();

		// Trigger the save_callback
		if (!empty($GLOBALS['TL_DCA']['tl_document']['fields']['featured']['save_callback'])
            && is_array($GLOBALS['TL_DCA']['tl_document']['fields']['featured']['save_callback']))
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
        Database::getInstance()->prepare("UPDATE tl_document SET tstamp=". time() .", featured='" . ($blnVisible ? 1 : '') . "' WHERE id=?")
					   ->execute($intId);

		$objVersions->create();
        System::getContainer()->get('monolog.logger.contao.general')->info('A new version of record "tl_document.id='.$intId.'" has been created'.$this->getParentEntries('tl_document', $intId), __METHOD__, TL_GENERAL);
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
        $beUser = BackendUser::getInstance();

        if (\trim((string)Input::get('tid')) !== '')
		{
			$this->toggleVisibility(Input::get('tid'), (Input::get('state') == 1), (@func_get_arg(12) ?: null));
            Controller::redirect(System::getReferer());
		}

		// Check permissions AFTER checking the tid, so hacking attempts are logged
		if (!$beUser->hasAccess('tl_document::published', 'alexf'))
		{
			return '';
		}

		$href .= '&amp;tid='.$row['id'].'&amp;state='.($row['published'] ? '' : 1);

		if (!$row['published'])
		{
			$icon = 'invisible.gif';
		}

		return '<a href="'.Controller::addToUrl($href).'" title="'.StringUtil::specialchars($title).'"'.$attributes.'>'.Image::getHtml($icon, $label).'</a> ';
	}


	/**
	 * Disable/enable a user group
	 * @param integer
	 * @param boolean
	 * @param DataContainer
	 */
	public function toggleVisibility($intId, $blnVisible, DataContainer $dc=null)
	{
		// Check permissions to edit
		Input::setGet('id', $intId);
		Input::setGet('act', 'toggle');
		$this->checkPermission();
        $beUser = BackendUser::getInstance();

		// Check permissions to publish
		if (!$beUser->hasAccess('tl_document::published', 'alexf'))
		{
            $message = 'Not enough permissions to publish/unpublish document item ID "'.$intId.'"';
            System::getContainer()->get('monolog.logger.contao.error')->error($message);
            throw new AccessDeniedException($message);
		}

		$objVersions = new Versions('tl_document', $intId);
		$objVersions->initialize();

		// Trigger the save_callback
		if (!empty($GLOBALS['TL_DCA']['tl_document']['fields']['published']['save_callback'])
            && is_array($GLOBALS['TL_DCA']['tl_document']['fields']['published']['save_callback']))
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
		Database::getInstance()->prepare("UPDATE tl_document SET tstamp=". time() .", published='" . ($blnVisible ? 1 : '') . "' WHERE id=?")
					   ->execute($intId);

		$objVersions->create();
        System::getContainer()->get('monolog.logger.contao.general')->info('A new version of record "tl_document.id='.$intId.'" has been created'.$this->getParentEntries('tl_document', $intId), __METHOD__, TL_GENERAL);
	}
}
