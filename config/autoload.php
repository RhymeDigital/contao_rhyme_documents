<?php

/**
 * Document management for Contao Open Source CMS
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */

/**
 * Register PSR-0 namespace
 */
if (\class_exists('NamespaceClassLoader')) {
    NamespaceClassLoader::add('Rhyme', 'system/modules/documents/library');
}


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
