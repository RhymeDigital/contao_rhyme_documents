<?php

/*
 * This file is part of Contao.
 *
 * (c) Leo Feyer
 *
 * @license LGPL-3.0-or-later
 */

namespace Rhyme\ContaoDocumentsBundle\Widget;

use Contao\Image;
use Contao\Widget;
use Contao\StringUtil;

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
		$this->import('Database');

		$arrButtons = array('copy', 'delete', 'drag', 'up', 'down');
		$strCommand = 'cmd_' . $this->strField;

		// Change the order
		if (\Input::get($strCommand) && is_numeric(\Input::get('cid')) && \Input::get('id') == $this->currentRecord)
		{
			switch (\Input::get($strCommand))
			{
				case 'copy':
					$this->varValue = array_duplicate($this->varValue, \Input::get('cid'));
					break;

				case 'up':
					$this->varValue = array_move_up($this->varValue, \Input::get('cid'));
					break;

				case 'down':
					$this->varValue = array_move_down($this->varValue, \Input::get('cid'));
					break;

				case 'delete':
					$this->varValue = array_delete($this->varValue, \Input::get('cid'));
					break;
			}
		}

		// Get all documents
		$objDocuments = $this->Database->prepare("SELECT id, headline FROM tl_document ORDER BY headline")
									 ->execute();

		// Add the articles module
		$documents[] = array('id'=>0, 'headline'=>'---', 'doc'=>0);

		if ($objDocuments->numRows)
		{
			$documents = array_merge($documents, $objDocuments->fetchAllAssoc());
		}

		// Get the new value
		if (\Input::post('FORM_SUBMIT') == $this->strTable)
		{
			$this->varValue = \Input::post($this->strId);
		}

        // Make sure there is at least an empty array
        if (!is_array($this->varValue) || !$this->varValue[0])
        {
            $this->varValue = array(array('doc'=>0, 'label'=>''));
        }

        // Adjust rows if they were sorted
        $this->varValue = array_values($this->varValue);

		// Save the value
		if (\Input::get($strCommand) || \Input::post('FORM_SUBMIT') == $this->strTable)
		{
			$this->Database->prepare("UPDATE " . $this->strTable . " SET " . $this->strField . "=? WHERE id=?")
						   ->execute(serialize($this->varValue), $this->currentRecord);
		}

		// Initialize the tab index
		if (!\Cache::has('tabindex'))
		{
			\Cache::set('tabindex', 1);
		}

		$tabindex = \Cache::get('tabindex');

		// Add the label and the return wizard
		$return = '<table id="ctrl_'.$this->strId.'" class="tl_documentwizard tl_modulewizard" style="margin-top: 15px;">
  <thead>
  <tr>
    <th>'.$GLOBALS['TL_LANG']['MSC']['dw_document'].'</th>
    <th>'.$GLOBALS['TL_LANG']['MSC']['dw_label'].'</th>
    <th>&nbsp;</th>
  </tr>
  </thead>
  <tbody class="sortable" data-tabindex="'.$tabindex.'">';

		// Load the tl_article language file
		\System::loadLanguageFile('tl_document');

		// Add the input fields
		for ($i=0, $c=count($this->varValue); $i<$c; $i++)
		{
			$options = '';

			// Add documents
			foreach ($documents as $v)
			{
				$options .= '<option value="'.specialchars($v['id']).'"'.static::optionSelected($v['id'], $this->varValue[$i]['doc']).'>'.$v['headline'].'</option>';
			}

			$return .= '
  <tr>
    <td><select name="'.$this->strId.'['.$i.'][doc]" class="tl_select tl_chosen" tabindex="'.$tabindex++.'" onfocus="Backend.getScrollOffset()">'.$options.'</select></td>';

			$return .= '
    <td><input type="text" name="'.$this->strId.'['.$i.'][label]" class="tl_label tl_text" tabindex="'.$tabindex++.'" onfocus="Backend.getScrollOffset()" value="'. $this->varValue[$i]['label'] .'" /></td>
    <td>';

            foreach ($arrButtons as $button)
            {
                $class = ($button == 'up' || $button == 'down') ? ' class="button-move" style="visibility: hidden;"' : '';

                if ($button == 'drag')
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

		// Store the tab index
		\Cache::set('tabindex', $tabindex);

		return $return.'
  </tbody>
  </table>
  <script>
  window.addEvent(\'domready\', function(){
    // Make this sortable 
    new Sortables($$(\'#ctrl_'.$this->strId.' tbody\')[0], {
        constrain: true,
        opacity: 0.6,
        handle: \'.drag-handle\'
    });
  });
  </script>';
	}
}
