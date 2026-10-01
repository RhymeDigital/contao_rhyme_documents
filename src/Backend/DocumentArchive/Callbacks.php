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
 
namespace Rhyme\ContaoDocumentsBundle\Backend\DocumentArchive;

use Contao\Backend;
use Contao\BackendUser;
use Contao\Controller;
use Contao\CoreBundle\Exception\AccessDeniedException;
use Contao\Image;
use Contao\Input;
use Contao\StringUtil;
use Contao\System;
use Rhyme\ContaoDocumentsBundle\Security\Permissions;
use Rhyme\ContaoDocumentsBundle\Model\DocumentArchive;

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
	 * Check permissions to edit table tl_document_archive
	 */
	public function checkPermission()
	{
        $t = DocumentArchive::getTable();
        $beUser = BackendUser::getInstance();

        if ($beUser?->isAdmin) {
            return;
        }

        // Set root IDs
        if (empty($beUser->document) || !is_array($beUser->document)) {
            $root = array(0);
        }
        else {
            $root = $beUser->document;
        }

        $GLOBALS['TL_DCA'][$t]['list']['sorting']['root'] = $root;
        $security = System::getContainer()->get('security.helper');

        // Check permissions to add documents
        if (!$security->isGranted(Permissions::USER_CAN_CREATE_DOCUMENTS))
        {
            $GLOBALS['TL_DCA'][$t]['config']['closed'] = true;
            $GLOBALS['TL_DCA'][$t]['config']['notCreatable'] = true;
            $GLOBALS['TL_DCA'][$t]['config']['notCopyable'] = true;
        }

        // Check permissions to delete documents
        if (!$security->isGranted(Permissions::USER_CAN_DELETE_DOCUMENTS))
        {
            $GLOBALS['TL_DCA'][$t]['config']['notDeletable'] = true;
        }

        $objSession = System::getContainer()->get('request_stack')->getSession();

        // Check current action
        switch (Input::get('act'))
        {
            case 'select':
                // Allow
                break;

            case 'create':
                if (!$security->isGranted(Permissions::USER_CAN_CREATE_DOCUMENTS))
                {
                    throw new AccessDeniedException('Not enough permissions to create documents.');
                }
                break;

            case 'edit':
            case 'copy':
            case 'delete':
            case 'show':
                if (!in_array(Input::get('id'), $root) || (Input::get('act') === 'delete' && !$security->isGranted(Permissions::USER_CAN_DELETE_DOCUMENTS)))
                {
                    throw new AccessDeniedException('Not enough permissions to ' . Input::get('act') . ' document archive ID ' . Input::get('id') . '.');
                }
                break;

            case 'editAll':
            case 'deleteAll':
            case 'overrideAll':
            case 'copyAll':
                $session = $objSession->all();

                if (Input::get('act') === 'deleteAll' && !$security->isGranted(Permissions::USER_CAN_DELETE_DOCUMENTS))
                {
                    $session['CURRENT']['IDS'] = array();
                }
                else
                {
                    $session['CURRENT']['IDS'] = array_intersect((array) $session['CURRENT']['IDS'], $root);
                }
                $objSession->replace($session);
                break;

            default:
                if (Input::get('act'))
                {
                    throw new AccessDeniedException('Not enough permissions to ' . Input::get('act') . ' documents.');
                }
                break;
        }
	}


    /**
     * Return the edit header button
     * @param $row
     * @param $href
     * @param $label
     * @param $title
     * @param $icon
     * @param $attributes
     * @return string
     */
	public function editHeader($row, $href, $label, $title, $icon, $attributes)
	{
        $security = System::getContainer()->get('security.helper');

        // Check permissions to add archives
        if (!$security?->isGranted(Permissions::USER_CAN_EDIT_DOCUMENTS))
        {
            return Image::getHtml(preg_replace('/\.gif$/i', '_.gif', $icon)).' ';
        }

		return '<a href="'.Controller::addToUrl($href.'&amp;id='.$row['id']).'" title="'.StringUtil::specialchars($title).'"'.$attributes.'>'.Image::getHtml($icon, $label).'</a> ';
	}


    /**
     * Return the copy archive button
     * @param $row
     * @param $href
     * @param $label
     * @param $title
     * @param $icon
     * @param $attributes
     * @return string
     */
	public function copyArchive($row, $href, $label, $title, $icon, $attributes)
	{
        $security = System::getContainer()->get('security.helper');

        // Check permissions to add archives
        if (!$security?->isGranted(Permissions::USER_CAN_CREATE_DOCUMENTS))
        {
            return Image::getHtml(preg_replace('/\.gif$/i', '_.gif', $icon)).' ';
        }

		return '<a href="'.Controller::addToUrl($href.'&amp;id='.$row['id']).'" title="'.StringUtil::specialchars($title).'"'.$attributes.'>'.Image::getHtml($icon, $label).'</a> ';
	}


	/**
	 * Return the delete archive button
	 *
	 * @param array  $row
     * @param string $href
     * @param string $label
     * @param string $title
	 * @param string $icon
     * @param string $attributes
     * @return string
	 */
	public function deleteArchive($row, $href, $label, $title, $icon, $attributes)
	{
        $security = System::getContainer()->get('security.helper');

        // Check permissions to add archives
        if (!$security?->isGranted(Permissions::USER_CAN_DELETE_DOCUMENTS))
        {
            return Image::getHtml(preg_replace('/\.gif$/i', '_.gif', $icon)).' ';
        }

		return '<a href="'.Controller::addToUrl($href.'&amp;id='.$row['id']).'" title="'.StringUtil::specialchars($title).'"'.$attributes.'>'.Image::getHtml($icon, $label).'</a> ';
	}
}
