<?php

/**
 * Document management for Contao Open Source CMS
 * @license		http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 */
 
namespace Rhyme\ContaoDocumentsBundle\Module\Document;

use Contao\Input;
use Contao\System;
use Contao\Widget;
use Contao\PageModel;
use Contao\Controller;
use Contao\FormTextField;
use Contao\BackendTemplate;
use Rhyme\ContaoDocumentsBundle\Module\Document as Document_Module;
use Rhyme\ContaoDocumentsBundle\Model\DocumentArchive as DocumentArchiveModel;
use Rhyme\ContaoDocumentsBundle\Model\Document as DocumentModel;
use Rhyme\ContaoDocumentsBundle\Module\Document\Lister as Document_Lister;

/**
 * Class Filter
 * @package Rhyme\Module\Document
 */
class Filter extends Document_Module
{

    /**
     * Template
     * @var string
     */
    protected $strTemplate = 'mod_documentfilter';

    /**
     * Selected filters
     * @var array
     */
    protected $arrFilters = array();

	/**
	 * Display a wildcard in the back end
	 * @return string
	 */
	public function generate()
	{
        $request = System::getContainer()->get('request_stack')->getCurrentRequest();
        if ($request && System::getContainer()->get('contao.routing.scope_matcher')->isBackendRequest($request)) {

			$objTemplate = new BackendTemplate('be_wildcard');

			$objTemplate->wildcard = '### ' . \utf8_strtoupper($GLOBALS['TL_LANG']['FMD']['documentfilter'][0]) . ' ###';
			$objTemplate->title = $this->headline;
			$objTemplate->id = $this->id;
			$objTemplate->link = $this->name;
			$objTemplate->href = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;

			return $objTemplate->parse();
		}

        System::loadLanguageFile(DocumentModel::getTable());
        Controller::loadDataContainer(DocumentModel::getTable());

		return parent::generate();
	}


    /**
     * Generate the module
     */
    protected function compile()
    {
        global $objPage;
        $arrFilters = array();
        $arrFields = \deserialize($this->document_filterfields, true);

        if (!empty($arrFields))
        {
            foreach ($arrFields as $strField)
            {
                $varValue = Input::get($strField) ?: null;

                if ($strField == 'body')
                {
                    $objWidget = new FormTextField(array
                    (
                        'name'			=> $strField,
                        'id'			=> $strField,
                        'value'			=> $varValue,
                    ));
                }
                else
                {
                    $GLOBALS['TL_DCA'][DocumentModel::getTable()]['fields'][$strField]['eval']['mandatory'] = false;
                    $GLOBALS['TL_DCA'][DocumentModel::getTable()]['fields'][$strField]['eval']['required'] = false;
                    $GLOBALS['TL_DCA'][DocumentModel::getTable()]['fields'][$strField]['eval']['includeBlankOption'] = true;

                    $objWidget = static::getFrontendWidgetFromDca(DocumentModel::getTable(), $strField, $varValue);
                }

                if (isset($GLOBALS['TL_HOOKS']['getDocumentFilterWidget']) && is_array($GLOBALS['TL_HOOKS']['getDocumentFilterWidget'])) {
                    foreach ($GLOBALS['TL_HOOKS']['getDocumentFilterWidget'] as $callback) {
                        $objCallback = System::importStatic($callback[0]);
                        $objWidget = $objCallback->{$callback[1]}($objWidget, $this);
                    }
                }

                $strBuffer = $objWidget->generate();

                $arrFilters[$strField] = $strBuffer;
                $this->Template->{'filter_'.$strField} = $strBuffer;
            }
        }

        $objJumpTo = $this->jumpTo ? PageModel::findByPk($this->jumpTo) : PageModel::findByPk($objPage->id);

        $this->Template->action					= $this->generateFrontendUrl($objJumpTo->row());
        $this->Template->filters				= $arrFilters;
        $this->Template->targetlistmodules 		= \implode(',', \deserialize($this->document_targetlistmodules, true));
        $this->Template->submit_label			= $GLOBALS['TL_LANG']['MSC']['documentfilter_submit'];
    }



    /**
     * Create a widget using the table, field, and optional current value
     */
    protected static function getFrontendWidgetFromDca($strTable, $strField, $varValue=null)
    {
        $strClass = $GLOBALS['TL_FFL'][$GLOBALS['TL_DCA'][$strTable]['fields'][$strField]['inputType']];

        if (\class_exists($strClass))
        {
            return new $strClass(Widget::getAttributesFromDca($GLOBALS['TL_DCA'][$strTable]['fields'][$strField], $strField, $varValue, $strField, $strTable));
        }

        return null;
    }

}
