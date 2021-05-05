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
 
namespace HBAgency\Hooks\ExecutePostActions;

/**
 * Class ToggleFeaturedDoc
 *
 * Provide miscellaneous methods that are used by the data configuration array.
 * @copyright  HB Agency 2015
 * @author     Blair Winans <bwinans@hbagency.com>
 * @author     Adam Fisher <afisher@hbagency.com>
 * @package    Document_Management
 */
class ToggleFeaturedDoc extends \Backend
{
    
    public function run($strAction, $dc)
    {
        if($strAction=='toggleFeaturedDoc')
        {
            $this->import('HBAgency\Backend\Document\Callbacks', 'Callbacks');
            $this->Callbacks->toggleFeatured(\Input::post('id'), ((\Input::post('state') == 1) ? true : false));
        }
    }
    
}