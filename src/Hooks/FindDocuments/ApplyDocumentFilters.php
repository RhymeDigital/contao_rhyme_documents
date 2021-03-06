<?php

/**
 * Copyright (C) 2019 Rhyme Digital, LLC.
 *
 * @link		http://rhyme.digital
 * @license		http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 */

namespace Rhyme\ContaoDocumentsBundle\Hooks\FindDocuments;

use Contao\StringUtil;
use Rhyme\ContaoDocumentsBundle\Model\Document as DocumentModel;


/**
 * Class ApplyDocumentFilters
 * @package Rhyme\ContaoDocumentsBundle\Hooks\FindDocuments
 */
class ApplyDocumentFilters extends \Frontend
{

    /**
     * Store the IDs of items that have been obtained by searching for their body text
     * @var array
     */
    protected static $arrCachedBodyIds = array();


    /**
     * Apply filter values to the custom document model
     *
     * Class:		DocumentModel
     * Method:		find
     * Hook:		$GLOBALS['TL_HOOKS']['findDocuments']
     *
     * @access		public
     * @param		array
     * @param		string
     * @return		void
     */
    public static function run(&$arrOptions, $strCurrentMethod)
    {
        if (!static::validateFilterAndLister())
        {
            return;
        }

        $arrFilters = static::getFilters();

        if (!empty($arrFilters))
        {
            $t = DocumentModel::getTable();

            \System::loadLanguageFile($t);
            \Controller::loadDataContainer($t);

            foreach ($arrFilters as $key=>$val)
            {
                $strFilterType = $GLOBALS['TL_DCA'][$t]['fields'][$key]['attributes']['fe_filter_type'] ?: 'rgxp';

                if ($key == 'body')
                {
                    $where = [];
                    $arrValues = [];
                    $arrFields = ['headline', 'teaser', 'subheadline'];
                    $arrWords = StringUtil::trimsplit(' ', $val);

                    foreach ($arrFields as $field)
                    {
                        foreach ($arrWords as $wordKey=>$word)
                        {
                            if (empty($word) ||
                                \in_array(\strtolower($word), \array_map('strtolower', $GLOBALS['DOCUMENTS_KEYWORD_STOP_WORDS']))
                            ) {
                                unset($arrWords[$wordKey]);
                                continue;
                            }

                            $where[] = "$t.$field REGEXP ?";
                            $arrValues[] = $word;
                        }
                    }

                    if (count($where))
                    {
                        $arrOptions['column'][] = "(".\implode(" OR ", $where).")";

                        foreach ($arrValues as $value) {
                            $arrOptions['value'][] = $value;
                        }
                    }
                    continue;
                }

                if (is_array($val) && !empty($val))
                {
                    $strWhere = "(";
                    foreach ($val as $i=>$opt)
                    {
                        if ($i != 0)
                        {
                            $strWhere .= " OR ";
                        }

                        list($key, $val, $strWhere, $strFilterType, $arrOptions) = static::getWhere($key, $val, $strWhere, $strFilterType, $arrOptions);

                        $arrOptions['value'][] = $opt;
                    }
                    $strWhere .= ")";

                    $arrOptions['column'][] = $strWhere;

                }
                else
                {
                    $strWhere = "";
                    list($key, $val, $strWhere, $strFilterType, $arrOptions) = static::getWhere($key, $val, $strWhere, $strFilterType, $arrOptions);
                    $arrOptions['column'][] = $strWhere;
                    $arrOptions['value'][] = $val;
                }
            }
        }
    }

    protected static function getWhere($key, $val, $strWhere, $strFilterType, $arrOptions)
    {
        $t = DocumentModel::getTable();
        $strWhere .= "$t.$key ";
        switch ($strFilterType)
        {
            // todo: add things like gte, lte, etc.
            case 'equals':
                $strWhere .= "=?";
                break;

            case 'rgxp':
            case 'rgxpstr':
                $strWhere .= "REGEXP ?";
                break;

            case 'rgxpint':
                $strWhere .= "REGEXP CONCAT(':', ?, ';')";
                break;

            case 'rgxpintstr':
                $strWhere .= "REGEXP CONCAT('\"', ?, '\"')";
                break;

            default:
                // !HOOK: custom...
                if (isset($GLOBALS['TL_HOOKS']['documentFiltersGetWhere']) && is_array($GLOBALS['TL_HOOKS']['documentFiltersGetWhere'])) {
                    foreach ($GLOBALS['TL_HOOKS']['documentFiltersGetWhere'] as $callback) {
                        $objCallback = \System::importStatic($callback[0]);
                        list($key, $val, $strWhere, $strFilterType, $arrOptions) = $objCallback->{$callback[1]}($key, $val, $strWhere, $strFilterType, $arrOptions);
                    }
                }
                break;
        }

        return array($key, $val, $strWhere, $strFilterType, $arrOptions);
    }


    protected static function getFilters()
    {
        $arrFilters = array();
        $arrGetKeys = array_keys((array)$_GET);

        foreach ((array)$arrGetKeys as $key)
        {
            if ((\Database::getInstance()->fieldExists($key, DocumentModel::getTable()) || $key == 'body') && \Input::get($key))
            {
                if (is_array(\Input::get($key)))
                {
                    $arrValues = array();

                    foreach (\Input::get($key) as $val)
                    {
                        if ($val)
                        {
                            $arrValues[] = $val;
                        }
                    }

                    if (!empty($arrValues))
                    {
                        $arrFilters[$key] = $arrValues;
                    }
                }
                else
                {
                    $arrFilters[$key] = \Input::get($key);
                }
            }
        }

        return $arrFilters;
    }


    protected static function validateFilterAndLister()
    {
        // See if we have a "last generated module" ID, lists in the GET params, and that the last generated module is one of the lists
        if (!isset($GLOBALS['DOCUMENT']['LAST_GENERATED_MODULE']) ||
            !$GLOBALS['DOCUMENT']['LAST_GENERATED_MODULE'] ||
            !\Input::get('lists') ||
            !in_array($GLOBALS['DOCUMENT']['LAST_GENERATED_MODULE'], trimsplit(',', \Input::get('lists')))
        )
        {
            return false;
        }

        $objRow = \ModuleModel::findByPk($GLOBALS['DOCUMENT']['LAST_GENERATED_MODULE']);

        // See if we have a row, and check the visibility (see #6311)
        if ($objRow === null || !\Controller::isVisibleElement($objRow))
        {
            return false;
        }

        return true;
    }
}
