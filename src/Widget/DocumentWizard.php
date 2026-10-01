<?php

/**
 *  Document management for Contao Open Source CMS
 *
 *  Copyright (c) 2026 Rhyme Digital, LLC.
 *
 *  @link			https://rhyme.digital
 *  @license		https://www.gnu.org/licenses/lgpl-3.0.txt LGPL
 */

namespace Rhyme\ContaoDocumentsBundle\Widget;

use Contao\Database;
use Contao\Input;
use Contao\Image;
use Contao\System;
use Contao\Widget;
use Contao\StringUtil;
use Rhyme\ContaoDocumentsBundle\Model\Document;

/**
 * Class DocumentWizard
 * @package FastenMaster\Widget
 */
class DocumentWizard extends Widget
{

	/**
	 * Submit user input
	 * @var boolean
	 */
	protected $blnSubmitInput = false;

	/**
	 * Template
	 * @var string
	 */
	protected $strTemplate = 'be_widget';


	/**
	 * Generate the widget and return it as string
	 *
	 * @return string
	 */
	public function generate()
	{
		$arrButtons = array('copy', 'delete', 'drag', 'up', 'down');
		$strCommand = 'cmd_' . $this->strField;

		// Change the order
		if (Input::get($strCommand) && is_numeric(Input::get('cid')) && Input::get('id') == $this->currentRecord)
		{
			$intIndex = (int) Input::get('cid');
			$arrValue = \array_values((array) $this->varValue);

			if (isset($arrValue[$intIndex]))
			{
				switch (Input::get($strCommand))
				{
					case 'copy':
						// Insert a duplicate right after the original
						array_splice($arrValue, $intIndex + 1, 0, array($arrValue[$intIndex]));
						break;

					case 'up':
						if ($intIndex > 0)
						{
							[$arrValue[$intIndex - 1], $arrValue[$intIndex]] = [$arrValue[$intIndex], $arrValue[$intIndex - 1]];
						}
						else
						{
							// Move the first element to the end
							$arrValue[] = \array_shift($arrValue);
						}
						break;

					case 'down':
						if ($intIndex + 1 < \count($arrValue))
						{
							[$arrValue[$intIndex + 1], $arrValue[$intIndex]] = [$arrValue[$intIndex], $arrValue[$intIndex + 1]];
						}
						else
						{
							// Move the last element to the beginning
							\array_unshift($arrValue, \array_pop($arrValue));
						}
						break;

					case 'delete':
						array_splice($arrValue, $intIndex, 1);
						break;
				}

				$this->varValue = $arrValue;
			}
		}

		// Get all documents
		$objDocuments = Database::getInstance()->prepare("SELECT id, headline FROM tl_document ORDER BY headline")
									 ->execute();

		// Add the articles module
		$documents[] = array('id'=>0, 'headline'=>'---', 'doc'=>0);

		if ($objDocuments->numRows)
		{
			$documents = array_merge($documents, $objDocuments->fetchAllAssoc());
		}

		// Get the new value
		if (Input::post('FORM_SUBMIT') === $this->strTable)
		{
			$this->varValue = Input::post($this->strId);
		}

        // Make sure there is at least an empty array
        if (!is_array($this->varValue) || !$this->varValue[0])
        {
            $this->varValue = array(array('doc'=>0, 'label'=>''));
        }

        // Adjust rows if they were sorted
        $this->varValue = \array_values((array)$this->varValue);

		// Save the value
		if (Input::get($strCommand) || Input::post('FORM_SUBMIT') === $this->strTable)
		{
			Database::getInstance()->prepare("UPDATE " . $this->strTable . " SET " . $this->strField . "=? WHERE id=?")
						   ->execute(serialize($this->varValue), $this->currentRecord);
		}

		// Add the label and the return wizard
		$return = '<table id="ctrl_'.$this->strId.'" class="tl_documentwizard tl_modulewizard" style="margin-top: 15px;">
  <thead>
  <tr>
    <th></th>
    <th>'.$GLOBALS['TL_LANG']['MSC']['dw_document'].'</th>
    <th>'.$GLOBALS['TL_LANG']['MSC']['dw_label'].'</th>
    <th>&nbsp;</th>
  </tr>
  </thead>
  <tbody class="sortable">';

		// Load the document language file
		System::loadLanguageFile(Document::getTable());

		// Add the input fields
		for ($i=0, $c=count($this->varValue); $i<$c; $i++)
		{
			$options = '';

			// Add documents
			foreach ($documents as $v)
			{
				$options .= '<option value="'.StringUtil::specialchars($v['id']).'"'.static::optionSelected($v['id'], $this->varValue[$i]['doc']).'>'.$v['headline'].'</option>';
			}

			$return .= '
  <tr>
  <td></td>
    <td><select name="'.$this->strId.'['.$i.'][doc]" class="tl_select tl_chosen" tabindex="'.$tabindex++.'" onfocus="Backend.getScrollOffset()">'.$options.'</select></td>';

			$return .= '
    <td><input type="text" name="'.$this->strId.'['.$i.'][label]" class="tl_label tl_text" tabindex="'.$tabindex++.'" onfocus="Backend.getScrollOffset()" value="'. $this->varValue[$i]['label'] .'" /></td>
    <td>';

            foreach ($arrButtons as $button)
            {
                $class = ($button === 'up' || $button === 'down') ? ' class="button-move" style="visibility: hidden;"' : '';

                if ($button === 'drag')
                {
                    $return .= ' <button type="button" class="drag-handle" title="' . StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['move']) . '" aria-hidden="true">' . Image::getHtml('drag.svg') . '</button>';
                }
                else
                {
                    $return .= ' <button type="button" data-command="' . $button . '" title="' . StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['ow_' . $button]) . '"' . $class . ' onclick="DocMan.documentWizard(this,\''.$button.'\',\'ctrl_'.$this->strId.'\');return false">' . Image::getHtml($button . '.svg') . '</button>';
                }
            }

			$return .= '</td>
  </tr>';
		}

		return $return.'
  </tbody>
  </table>
  <script>
  document.addEventListener(\'DOMContentLoaded\', () => {
    // Make this sortable 
    new Sortables(document.querySelectorAll(\'ctrl_'.$this->strId.' tbody\')[0], {
        constrain: true,
        opacity: 0.6,
        handle: \'.drag-handle\'
    });
  });
  </script>';
	}
}
