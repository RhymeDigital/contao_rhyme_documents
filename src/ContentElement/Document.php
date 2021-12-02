<?php

/**
 * Document management for Contao Open Source CMS
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */

namespace Rhyme\ContaoDocumentsBundle\ContentElement;

use Contao\ContentElement;
use Contao\BackendTemplate;
use Rhyme\ContaoDocumentsBundle\Model\Document as DocumentModel;
use Rhyme\ContaoDocumentsBundle\Helper\DocumentHelper;


/**
 * Content element "Document".
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class Document extends ContentElement
{

    /**
     * Document
     * @var DocumentModel
     */
    protected $objDocument;

	/**
	 * Parse the template
	 * @return string
     * @throws \Exception
	 */
	public function generate()
	{
        $this->objDocument = $this->document ? DocumentModel::findByPk($this->document) : null;

		if (TL_MODE === 'BE')
		{
			$objTemplate = new BackendTemplate('be_wildcard');

            $objTemplate->wildcard = '### ' . utf8_strtoupper($GLOBALS['TL_LANG']['CTE']['document_single']) . ($this->objDocument ? ' ('.$this->objDocument->headline.')' : '') . ' ###';
			$objTemplate->id = $this->id;
			$objTemplate->href = 'contao/main.php?do=article&amp;table=tl_content&amp;act=edit&amp;id=' . $this->id;

			return $objTemplate->parse();
		}

		// Make sure we found the document
		if ($this->objDocument === null) {
		    return '';
        }

		// Make sure this element is published
		if (TL_MODE === 'FE' && BE_USER_LOGGED_IN !== true && ($this->invisible || ($this->start != '' && $this->start > time()) || ($this->stop != '' && $this->stop < time())))
		{
			return '';
		}

		return DocumentHelper::parseDocument($this->objDocument, '', 1, $this);
	}


	/**
	 * Generate the content element
	 */
	protected function compile()
	{
		return;
	}
}
