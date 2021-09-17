<?php

/**
 * Document management for Contao Open Source CMS
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */

namespace Rhyme\ContaoDocumentsBundle\Model;

use Contao\Model;
use Contao\System;

/**
 * Class Document
 * @package Rhyme\Model
 */
class Document extends Model
{

	/**
	 * Table name
	 * @var string
	 */
	protected static $strTable = 'tl_document';
	
	/**
	 * Current method being called
	 * @var string
	 */
	protected static $strCurrentMethod = '';


	/**
	 * Find records and return the model or model collection
	 *
	 * Supported options:
	 *
	 * * column: the field name
	 * * value:  the field value
	 * * limit:  the maximum number of rows
	 * * offset: the number of rows to skip
	 * * order:  the sorting order
	 * * eager:  load all related records eagerly
	 *
	 * @param array $arrOptions The options array
	 *
	 * @return \Model|\Model\Collection|null A model, model collection or null if the result is empty
	 */
	protected static function find(array $arrOptions)
	{
        // !HOOK: custom actions
        if (isset($GLOBALS['TL_HOOKS']['findDocuments']) && is_array($GLOBALS['TL_HOOKS']['findDocuments'])) {
            foreach ($GLOBALS['TL_HOOKS']['findDocuments'] as $callback) {
                $objCallback = System::importStatic($callback[0]);
                $objCallback->{$callback[1]}($arrOptions, static::$strCurrentMethod);
            }
        }
        
        return parent::find($arrOptions);
	}


	/**
	 * Return the number of records matching certain criteria
	 *
	 * @param mixed $strColumn  An optional property name
	 * @param mixed $varValue   An optional property value
	 * @param array $arrOptions An optional options array
	 *
	 * @return integer The number of matching rows
	 */
	public static function countBy($strColumn=null, $varValue=null, array $arrOptions=array())
	{
        // !HOOK: custom actions
        if (isset($GLOBALS['TL_HOOKS']['countByDocuments']) && is_array($GLOBALS['TL_HOOKS']['countByDocuments'])) {
            foreach ($GLOBALS['TL_HOOKS']['countByDocuments'] as $callback) {
                $objCallback = System::importStatic($callback[0]);
                $objCallback->{$callback[1]}($strColumn, $varValue, $arrOptions, static::$strCurrentMethod);
            }
        }
        
		return parent::countBy($strColumn, $varValue, $arrOptions);
	}


	/**
	 * Find published document items by their parent ID and ID or alias
	 *
	 * @param mixed $varId      The numeric ID or alias name
	 * @param array $arrPids    An array of parent IDs
	 * @param array $arrOptions An optional options array
	 *
	 * @return \Model|null The NewsModel or null if there are no document
	 */
	public static function findPublishedByParentAndIdOrAlias($varId, $arrPids, array $arrOptions=array())
	{
		if (!is_array($arrPids) || empty($arrPids))
		{
			return null;
		}

		$t = static::$strTable;
		$arrColumns = array("($t.id=? OR $t.alias=?) AND $t.pid IN(" . implode(',', array_map('intval', $arrPids)) . ")");

		if (BE_USER_LOGGED_IN !== true)
		{
			$time = time();
			$arrColumns[] = "($t.start='' OR $t.start<$time) AND ($t.stop='' OR $t.stop>$time) AND $t.published=1";
		}

		static::$strCurrentMethod = 'findPublishedByParentAndIdOrAlias';
		$varBuffer = static::findBy($arrColumns, array((is_numeric($varId) ? $varId : 0), $varId), $arrOptions);
		static::$strCurrentMethod = '';
		return $varBuffer;
	}


    /**
     * Find published document items by ID or alias
     *
     * @param mixed $varId      The numeric ID or alias name
     * @param array $arrOptions An optional options array
     *
     * @return \Model|null The NewsModel or null if there are no document
     */
    public static function findPublishedByIdOrAlias($varId, array $arrOptions=array())
    {
        $t = static::$strTable;
        $arrColumns = !preg_match('/^[1-9]\d*$/', $varId) ? array("$t.alias=?") : array("$t.id=?");

        if (BE_USER_LOGGED_IN !== true)
        {
            $time = time();
            $arrColumns[] = "($t.start='' OR $t.start<$time) AND ($t.stop='' OR $t.stop>$time) AND $t.published=1";
        }

        static::$strCurrentMethod = 'findPublishedByIdOrAlias';
        $varBuffer = static::findBy($arrColumns, $varId, $arrOptions);
        static::$strCurrentMethod = '';
        return $varBuffer;
    }


	/**
	 * Find published document items by their parent ID
	 *
	 * @param array   $arrPids     An array of document archive IDs
	 * @param boolean $blnFeatured If true, return only featured document, if false, return only unfeatured document
	 * @param integer $intLimit    An optional limit
	 * @param integer $intOffset   An optional offset
	 * @param array   $arrOptions  An optional options array
	 *
	 * @return \Model\Collection|null A collection of models or null if there are no document
	 */
	public static function findPublishedByPids($arrPids, $blnFeatured=null, $intLimit=0, $intOffset=0, array $arrOptions=array())
	{
		if (!is_array($arrPids) || empty($arrPids))
		{
			return null;
		}

		$t = static::$strTable;
		$arrColumns = array("$t.pid IN(" . implode(',', array_map('intval', $arrPids)) . ")");

		if ($blnFeatured === true)
		{
			$arrColumns[] = "$t.featured=1";
		}
		elseif ($blnFeatured === false)
		{
			$arrColumns[] = "$t.featured=''";
		}

		// Never return unpublished elements in the back end, so they don't end up in the RSS feed
		if (BE_USER_LOGGED_IN !== true || TL_MODE === 'BE')
		{
			$time = time();
			$arrColumns[] = "($t.start='' OR $t.start<$time) AND ($t.stop='' OR $t.stop>$time) AND $t.published=1";
		}

		if (!isset($arrOptions['order']))
		{
			$arrOptions['order']  = "$t.date DESC";
		}

		$arrOptions['limit']  = $intLimit;
		$arrOptions['offset'] = $intOffset;

		static::$strCurrentMethod = 'findPublishedByPids';
		$varBuffer = static::findBy($arrColumns, null, $arrOptions);
		static::$strCurrentMethod = '';
		return $varBuffer;
	}


	/**
	 * Count published document items by their parent ID
	 *
	 * @param array   $arrPids     An array of document archive IDs
	 * @param boolean $blnFeatured If true, return only featured document, if false, return only unfeatured document
	 * @param array   $arrOptions  An optional options array
	 *
	 * @return integer The number of document items
	 */
	public static function countPublishedByPids($arrPids, $blnFeatured=null, array $arrOptions=array())
	{
		if (!is_array($arrPids) || empty($arrPids))
		{
			return 0;
		}

		$t = static::$strTable;
		$arrColumns = array("$t.pid IN(" . implode(',', array_map('intval', $arrPids)) . ")");

		if ($blnFeatured === true)
		{
			$arrColumns[] = "$t.featured=1";
		}
		elseif ($blnFeatured === false)
		{
			$arrColumns[] = "$t.featured=''";
		}

        if (BE_USER_LOGGED_IN !== true)
		{
			$time = time();
			$arrColumns[] = "($t.start='' OR $t.start<$time) AND ($t.stop='' OR $t.stop>$time) AND $t.published=1";
		}

		static::$strCurrentMethod = 'countPublishedByPids';
		$varBuffer = static::countBy($arrColumns, null, $arrOptions);
		static::$strCurrentMethod = '';
		return $varBuffer;
	}


	/**
	 * Find published document items with the default redirect target by their parent ID
	 *
	 * @param integer $intPid     The document archive ID
	 * @param array   $arrOptions An optional options array
	 *
	 * @return \Model\Collection|null A collection of models or null if there are no document
	 */
	public static function findPublishedDefaultByPid($intPid, array $arrOptions=array())
	{
		$t = static::$strTable;
		$arrColumns = array("$t.pid=?");

        if (BE_USER_LOGGED_IN !== true)
		{
			$time = time();
			$arrColumns[] = "($t.start='' OR $t.start<$time) AND ($t.stop='' OR $t.stop>$time) AND $t.published=1";
		}

		if (!isset($arrOptions['order']))
		{
			$arrOptions['order'] = "$t.date DESC";
		}

		static::$strCurrentMethod = 'findPublishedDefaultByPid';
		$varBuffer = static::findBy($arrColumns, $intPid, $arrOptions);
		static::$strCurrentMethod = '';
		return $varBuffer;
	}


	/**
	 * Find published document items by their parent ID
	 *
	 * @param integer $intId      The document archive ID
	 * @param integer $intLimit   An optional limit
	 * @param array   $arrOptions An optional options array
	 *
	 * @return \Model\Collection|null A collection of models or null if there are no document
	 */
	public static function findPublishedByPid($intId, $intLimit=0, array $arrOptions=array())
	{
		$time = time();
		$t = static::$strTable;

		$arrColumns = array();

        if (BE_USER_LOGGED_IN !== true)
        {
            $arrColumns[] = "$t.pid=? AND ($t.start='' OR $t.start<$time) AND ($t.stop='' OR $t.stop>$time) AND $t.published=1";
        }

		if (!isset($arrOptions['order']))
		{
			$arrOptions['order'] = "$t.date DESC";
		}

		if ($intLimit > 0)
		{
			$arrOptions['limit'] = $intLimit;
		}

		static::$strCurrentMethod = 'findPublishedByPid';
		$varBuffer = static::findBy($arrColumns, $intId, $arrOptions);
		static::$strCurrentMethod = '';
		return $varBuffer;
	}


	/**
	 * Find all published document items of a certain period of time by their parent ID
	 *
	 * @param integer $intFrom    The start date as Unix timestamp
	 * @param integer $intTo      The end date as Unix timestamp
	 * @param array   $arrPids    An array of document archive IDs
	 * @param integer $intLimit   An optional limit
	 * @param integer $intOffset  An optional offset
	 * @param array   $arrOptions An optional options array
	 *
	 * @return \Model\Collection|null A collection of models or null if there are no document
	 */
	public static function findPublishedFromToByPids($intFrom, $intTo, $arrPids, $intLimit=0, $intOffset=0, array $arrOptions=array())
	{
		if (!is_array($arrPids) || empty($arrPids))
		{
			return null;
		}

		$t = static::$strTable;
		$arrColumns = array("$t.date>=? AND $t.date<=? AND $t.pid IN(" . implode(',', array_map('intval', $arrPids)) . ")");

		if (BE_USER_LOGGED_IN !== true)
		{
			$time = time();
			$arrColumns[] = "($t.start='' OR $t.start<$time) AND ($t.stop='' OR $t.stop>$time) AND $t.published=1";
		}

		if (!isset($arrOptions['order']))
		{
			$arrOptions['order']  = "$t.date DESC";
		}

		$arrOptions['limit']  = $intLimit;
		$arrOptions['offset'] = $intOffset;

		static::$strCurrentMethod = 'findPublishedFromToByPids';
		$varBuffer = static::findBy($arrColumns, array($intFrom, $intTo), $arrOptions);
		static::$strCurrentMethod = '';
		return $varBuffer;
	}


	/**
	 * Count all published document items of a certain period of time by their parent ID
	 *
	 * @param integer $intFrom    The start date as Unix timestamp
	 * @param integer $intTo      The end date as Unix timestamp
	 * @param array   $arrPids    An array of document archive IDs
	 * @param array   $arrOptions An optional options array
	 *
	 * @return integer The number of document items
	 */
	public static function countPublishedFromToByPids($intFrom, $intTo, $arrPids, array $arrOptions=array())
	{
		if (!is_array($arrPids) || empty($arrPids))
		{
			return null;
		}

		$t = static::$strTable;
		$arrColumns = array("$t.date>=? AND $t.date<=? AND $t.pid IN(" . implode(',', array_map('intval', $arrPids)) . ")");

        if (BE_USER_LOGGED_IN !== true)
		{
			$time = time();
			$arrColumns[] = "($t.start='' OR $t.start<$time) AND ($t.stop='' OR $t.stop>$time) AND $t.published=1";
		}

		static::$strCurrentMethod = 'countPublishedFromToByPids';
		$varBuffer = static::countBy($arrColumns, array($intFrom, $intTo), $arrOptions);
		static::$strCurrentMethod = '';
		return $varBuffer;
	}
}
