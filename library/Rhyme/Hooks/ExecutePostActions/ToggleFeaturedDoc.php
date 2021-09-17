<?php

/**
 * Document management for Contao Open Source CMS
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */
 
namespace Rhyme\Hooks\ExecutePostActions;

/**
 * Class ToggleFeaturedDoc
 *
 * Provide miscellaneous methods that are used by the data configuration array.
 * @copyright  Rhyme 2021


 * @package    Document_Management
 */
class ToggleFeaturedDoc extends \Backend
{
    
    public function run($strAction, $dc)
    {
        if($strAction=='toggleFeaturedDoc')
        {
            $this->import('Rhyme\Backend\Document\Callbacks', 'Callbacks');
            $this->Callbacks->toggleFeatured(\Input::post('id'), ((\Input::post('state') == 1) ? true : false));
        }
    }
    
}