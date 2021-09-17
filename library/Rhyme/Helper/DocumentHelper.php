<?php

/**
 * Document management for Contao Open Source CMS
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */

namespace Rhyme\Helper;

use Contao\Date;
use Contao\Config;
use Contao\Input;
use Contao\System;
use Contao\Environment;
use Contao\PageModel;
use Contao\Controller;
use Contao\FilesModel;
use Contao\Validator;
use Contao\StringUtil;
use Contao\Model\Collection;
use Contao\FrontendTemplate;
use Rhyme\Model\Document as DocumentModel;

/**
 * Class DocumentHelper
 * @package Rhyme\Helper
 */
class DocumentHelper extends Controller
{

    /**
     * URL cache array
     * @var array
     */
    protected static $arrUrlCache = array();

    /**
     * URL cache array
     * @var array
     */
    protected static $arrDownloadCache = array();

    /**
     * Parse an item and return it as string
     * @param DocumentModel $objDocument
     * @param string $strClass
     * @param integer $intCount
     * @param object|null $objSource
     * @param string $strTemplate
     * @return string
     * @throws \Exception
     */
    public static function parseDocument(DocumentModel $objDocument, $strClass='', $intCount=0, $objSource=null, $strTemplate='')
    {
        global $objPage;

        $strTemplate = $strTemplate ?: ($objSource !== null && $objSource->document_template ? $objSource->document_template : 'document_nameonly');
        $objTemplate = new FrontendTemplate($strTemplate);
        $objTemplate->setData($objDocument->row());

        $objTemplate->class = (($objDocument->cssClass != '') ? ' ' . $objDocument->cssClass : '') . $strClass;
        $objTemplate->documentHeadline = $objDocument->headline;
        $objTemplate->subHeadline = $objDocument->subheadline;
        $objTemplate->hasSubHeadline = $objDocument->subheadline ? true : false;
        $objTemplate->link = static::generateDocumentUrl($objDocument);
        $objTemplate->download = static::generateDownloadUrl($objDocument);
        $objTemplate->archive = $objDocument->getRelated('pid');
        $objTemplate->count = $intCount;

        // Clean the RTE output
        if ($objDocument->teaser != '')
        {
            if ($objPage->outputFormat == 'xhtml')
            {
                $objTemplate->teaser = StringUtil::toXhtml($objDocument->teaser);
            }
            else
            {
                $objTemplate->teaser = StringUtil::toHtml5($objDocument->teaser);
            }

            $objTemplate->teaser = StringUtil::encodeEmail($objTemplate->teaser);
        }

        $arrMeta = static::getMetaFields($objDocument, $objSource);

        // Add the meta information
        $objTemplate->date = $arrMeta['date'];
        $objTemplate->hasMetaFields = !empty($arrMeta);
        $objTemplate->timestamp = $objDocument->date;
        $objTemplate->author = $arrMeta['author'];
        $objTemplate->datetime = Date::parse($objPage->datimFormat, $objDocument->date);

        // Add the document
        if ($objDocument->singleSRC != '')
        {
            $objModel = FilesModel::findByUuid($objDocument->singleSRC);

            if ($objModel === null)
            {
                if (!Validator::isUuid($objDocument->singleSRC))
                {
                    $objTemplate->text = '<p class="error">'.$GLOBALS['TL_LANG']['ERR']['version2format'].'</p>';
                }
            }
            elseif (\is_file(TL_ROOT . '/' . $objModel->path))
            {
                // Do not override the field now that we have a model registry (see #6303)
                $arrDocument = $objDocument->row();

                // Override the default image size
                if ($objSource !== null && $objSource->imgSize != '')
                {
                    $size = \deserialize($objSource->imgSize, true);

                    if ($size[0] > 0 || $size[1] > 0 || is_numeric($size[2]))
                    {
                        $arrDocument['size'] = $objSource->imgSize;
                    }
                }

                $arrDocument['singleSRC'] = $objModel->path;
            }
        }

        if (isset($GLOBALS['TL_HOOKS']['parseDocuments']) && is_array($GLOBALS['TL_HOOKS']['parseDocuments']))
        {
            foreach ($GLOBALS['TL_HOOKS']['parseDocuments'] as $callback)
            {
                $objCallback = static::importStatic($callback[0]);
                $objCallback->{$callback[0]}->{$callback[1]}($objTemplate, $objDocument->row(), $intCount, $objSource);
            }
        }

        return $objTemplate->parse();
    }


    /**
     * Generate a URL and return it as string
     * @param object
     * @return string
     */
    public static function generateDocumentUrl($objItem)
    {
        $strCacheKey = 'id_' . $objItem->id;

        // Load the URL from cache
        if (isset(static::$arrUrlCache[$strCacheKey]))
        {
            return static::$arrUrlCache[$strCacheKey];
        }

        // Link to the jumpTo page
        if (static::$arrUrlCache[$strCacheKey] === null)
        {
            $objPage = PageModel::findByPk($objItem->getRelated('pid')->jumpTo);

            if ($objPage === null)
            {
                static::$arrUrlCache[$strCacheKey] = ampersand(Environment::get('request'), true);
            }
            else
            {
                static::$arrUrlCache[$strCacheKey] = ampersand(Controller::generateFrontendUrl($objPage->row(), ((Config::get('useAutoItem') && !Config::get('disableAlias')) ?  '/' : '/items/') . ((!Config::get('disableAlias') && $objItem->alias != '') ? $objItem->alias : $objItem->id)));
            }
        }

        return static::$arrUrlCache[$strCacheKey];
    }

    /**
     * Generate a download URL and return it as string
     * @param object
     * @return string
     */
    public static function generateDownloadUrl($objItem)
    {
        $strCacheKey = 'id_' . $objItem->id;

        // Load the URL from cache
        if (isset(static::$arrDownloadCache[$strCacheKey]))
        {
            return static::$arrDownloadCache[$strCacheKey];
        }

        // Initialize the cache
        static::$arrDownloadCache[$strCacheKey] = null;

        if (!empty($objItem->url))
        {
            // Link to an external page
            static::$arrDownloadCache[$strCacheKey] = \ampersand($objItem->url);
        }

        // Link to the document
        if (static::$arrDownloadCache[$strCacheKey] === null)
        {
            // Return if there is no file
            if ($objItem->singleSRC == '')
            {
                static::$arrDownloadCache[$strCacheKey] = '#';
            }

            $objFile = FilesModel::findByUuid($objItem->singleSRC);

            if ($objFile === null)
            {
                static::$arrDownloadCache[$strCacheKey] = '#';
            }

            $allowedDownload = \trimsplit(',', \strtolower(Config::get('allowedDownload')));

            // Return if the file type is not allowed
            if (!\in_array($objFile->extension, $allowedDownload))
            {
                static::$arrDownloadCache[$strCacheKey] = '#';
            }

            $file = Input::get('file', true);

            // Send the file to the browser and do not send a 404 header (see #4632)
            if ($file != '' && $file == $objFile->path)
            {
                Controller::sendFileToBrowser($file);
            }

            $strHref = Environment::get('request');

            // Remove an existing file parameter (see #5683)
            if (\preg_match('/(&(amp;)?|\?)file=/', $strHref))
            {
                $strHref = \preg_replace('/(&(amp;)?|\?)file=[^&]+/', '', $strHref);
            }

            $strHref .= ((Config::get('disableAlias') || \strpos($strHref, '?') !== false) ? '&amp;' : '?') . 'file=' . System::urlEncode($objFile->path);

            static::$arrDownloadCache[$strCacheKey] = $strHref;
        }

        return static::$arrDownloadCache[$strCacheKey];
    }


    /**
     * Parse one or more items and return them as array
     * @param Collection $objDocuments
     * @param object|null $objSource
     * @param string $strTemplate
     * @return array
     * @throws \Exception
     */
    public static function parseDocuments(Collection $objDocuments, $objSource=null, $strTemplate='')
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
            /** @var DocumentModel $objDocument */
            $objDocument = $objDocuments->current();
            $arrDocuments[] = static::parseDocument(
                $objDocument,
                ((++$count == 1) ? ' first' : '') . (($count == $limit) ? ' last' : '') . ((($count % 2) == 0) ? ' odd' : ' even'),
                $count,
                $objSource,
                $strTemplate
            );
        }

        return $arrDocuments;
    }


    /**
     * Return the meta fields of a document article as array
     * @param DocumentModel $objDocument
     * @param object $objSource
     * @return array
     * @throws \Exception
     */
    public static function getMetaFields(DocumentModel $objDocument, $objSource)
    {
        if ($objSource === null)
        {
            return array();
        }

        $meta = \deserialize($objSource->document_metaFields);

        if (!\is_array($meta))
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
                    $return['date'] = Date::parse($objPage->datimFormat, $objDocument->date);
                    break;

                case 'author':
                    if (($objAuthor = $objDocument->getRelated('author')) !== null)
                    {
                        $return['author'] = $GLOBALS['TL_LANG']['MSC']['by'] . ' ' . $objAuthor->name;
                    }
                    break;
            }
        }

        if (isset($GLOBALS['TL_HOOKS']['getDocumentMetaFields']) && is_array($GLOBALS['TL_HOOKS']['getDocumentMetaFields']))
        {
            foreach ($GLOBALS['TL_HOOKS']['getDocumentMetaFields'] as $callback)
            {
                $objCallback = \System::importStatic($callback[0]);
                $return = $objCallback->{$callback[1]}($return, $objDocument, $objSource);
            }
        }

        return $return;
    }
}
