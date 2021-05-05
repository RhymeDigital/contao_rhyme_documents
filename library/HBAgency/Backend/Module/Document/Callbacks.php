<?php

/**
 * Document management for Contao Open Source CMS
 *
 * Copyright (C) 2014-2015 HB Agency
 *
 * @package    Document_Management
 * @link       http://www.hbagency.com
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */

/**
 * Run in a custom namespace, so the class can be replaced
 */
namespace HBAgency\Backend\Module\Document;

/**
 * Class Callbacks
 *
 * Provide miscellaneous methods that are used by the data configuration array.
 * @copyright  HB Agency 2015
 * @author     Blair Winans <bwinans@hbagency.com>
 * @author     Adam Fisher <afisher@hbagency.com>
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
	 * Get all document archives and return them as array
	 * @return array
	 */
	public function getDocumentArchives()
	{
		if (!$this->User->isAdmin && !is_array($this->User->document))
		{
			return array();
		}

		$arrArchives = array();
		$objArchives = $this->Database->execute("SELECT id, title FROM tl_document_archive ORDER BY title");

		while ($objArchives->next())
		{
			if ($this->User->hasAccess($objArchives->id, 'document'))
			{
				$arrArchives[$objArchives->id] = $objArchives->title;
			}
		}

		return $arrArchives;
	}


	/**
	 * Get all document reader modules and return them as array
	 * @return array
	 */
	public function getReaderModules()
	{
		$arrModules = array();
		$objModules = $this->Database->execute("SELECT m.id, m.name, t.name AS theme FROM tl_module m LEFT JOIN tl_theme t ON m.pid=t.id WHERE m.type='documentreader' ORDER BY t.name, m.name");

		while ($objModules->next())
		{
			$arrModules[$objModules->theme][$objModules->id] = $objModules->name . ' (ID ' . $objModules->id . ')';
		}

		return $arrModules;
	}


	/**
	 * Hide the start day drop-down if not applicable
	 * @return string
	 */
	public function hideStartDay()
	{
		return '
  <script>
    var enableStartDay = function() {
      var e1 = $("ctrl_document_startDay").getParent("div");
      var e2 = $("ctrl_document_order").getParent("div");
      if ($("ctrl_document_format").value == "document_day") {
        e1.setStyle("display", "block");
        e2.setStyle("display", "none");
	  } else {
        e1.setStyle("display", "none");
        e2.setStyle("display", "block");
	  }
    };
    window.addEvent("domready", function() {
      if ($("ctrl_document_startDay")) {
        enableStartDay();
        $("ctrl_document_format").addEvent("change", enableStartDay);
      }
    });
  </script>';
	}


	/**
	 * Return all document templates as array
	 * @return array
	 */
	public function getDocumentTemplates()
	{
		return $this->getTemplateGroup('document_');
	}
}
