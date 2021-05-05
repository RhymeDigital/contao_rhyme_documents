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
 * Register PSR-0 namespace
 */
NamespaceClassLoader::add('HBAgency', 'system/modules/documents/library');


/**
 * Register classes outside the namespace folder
 */
NamespaceClassLoader::addClassMap(array
(
    // TBD
));


/**
 * Register the templates
 */
TemplateLoader::addFiles(array
(
    //Modules
    'mod_documentlist'       => 'system/modules/documents/templates/modules',
    'mod_documentreader'     => 'system/modules/documents/templates/modules',
    
    //Documents
    'document_full'          => 'system/modules/documents/templates/document',
    'document_short'         => 'system/modules/documents/templates/document',
    'document_nameonly'      => 'system/modules/documents/templates/document',
));
