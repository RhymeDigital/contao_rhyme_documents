<?php

/**
 * Document management for Contao Open Source CMS
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */

namespace Rhyme\ContaoDocumentsBundle\ContentElement;

use Contao\Controller;
use Contao\StringUtil;
use Contao\ContentElement;
use Contao\BackendTemplate;
use Contao\System;
use Rhyme\ContaoDocumentsBundle\Model\Document as DocumentModel;
use Rhyme\ContaoDocumentsBundle\Helper\DocumentHelper;


/**
 * Content element "Document".
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class DocumentList extends ContentElement
{

    /**
     * Template
     * @var string
     */
    protected $strTemplate = 'mod_documentlist';

    /**
     * Documents
     * @var array
     */
    protected $arrDocuments = [];

	/**
	 * Parse the template
	 * @return string
     * @throws \Exception
	 */
	public function generate()
	{
        $request = System::getContainer()->get('request_stack')->getCurrentRequest();
        if ($request && System::getContainer()->get('contao.routing.scope_matcher')->isBackendRequest($request)) {

	        return parent::generate();
        }

		$this->arrDocuments = StringUtil::deserialize($this->documents, true);

		return empty($this->arrDocuments) ? '' : parent::generate();
	}


	/**
	 * Generate the content element
	 */
	protected function compile()
	{
	    $arrDocuments = [];

	    foreach ($this->arrDocuments as $doc) {
            // Make sure the document is published
            $objPublished = DocumentModel::findPublishedByIdOrAlias($doc['doc']);
            if ($objPublished !== null) {
                $objPublished->current()->label__override = $doc['label'];
                $arrDocuments[$objPublished->current()->id] = DocumentHelper::parseDocument($objPublished->current(), '', 1, $this, $this->document_template);
            }
        }

        $this->Template->articles = $arrDocuments;
	}
}
