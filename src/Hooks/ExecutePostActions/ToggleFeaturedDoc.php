<?php

/**
 * Document management for Contao Open Source CMS
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */
 
namespace Rhyme\ContaoDocumentsBundle\Hooks\ExecutePostActions;

use Contao\Input;
use Contao\System;
use Rhyme\ContaoDocumentsBundle\Backend\Document\Callbacks;

/**
 * Class ToggleFeaturedDoc
 *
 * Provide miscellaneous methods that are used by the data configuration array.
 * @copyright  Rhyme 2021


 * @package    Document_Management
 */
class ToggleFeaturedDoc
{
    
    public function run($strAction, $dc)
    {
        if ($strAction === 'toggleFeaturedDoc')
        {
			$callbacks = System::importStatic(Callbacks::class);
			$callbacks->toggleFeatured(Input::post('id'), ((Input::post('state') == 1) ? true : false));
        }
    }
    
}