<?php

/**
 * Document management for Contao Open Source CMS
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */
 
/**
 * Fields
 */
$GLOBALS['TL_LANG']['tl_document']['headline']     = array('Title', 'Please enter the document title.');
$GLOBALS['TL_LANG']['tl_document']['alias']        = array('Document alias', 'The document alias is a unique reference to the document which can be called instead of its numeric ID.');
$GLOBALS['TL_LANG']['tl_document']['author']       = array('Author', 'Here you can change the author of the document item.');
$GLOBALS['TL_LANG']['tl_document']['date']         = array('Date', 'Please enter the date according to the global date format.');
$GLOBALS['TL_LANG']['tl_document']['time']         = array('Time', 'Please enter the time according to the global time format.');
$GLOBALS['TL_LANG']['tl_document']['subheadline']  = array('Subheadline', 'Here you can enter a subheadline.');
$GLOBALS['TL_LANG']['tl_document']['teaser']       = array('Document teaser', 'The document teaser can be shown in a document list instead of the full document. A "read more ..." link will be added automatically.');
$GLOBALS['TL_LANG']['tl_document']['text']         = array('Document text', 'Here you can enter the document text.');
$GLOBALS['TL_LANG']['tl_document']['cssClass']     = array('CSS class', 'Here you can enter one or more classes.');
$GLOBALS['TL_LANG']['tl_document']['featured']     = array('Feature item', 'Show the document item in a featured document list.');
$GLOBALS['TL_LANG']['tl_document']['published']    = array('Publish item', 'Make the document item publicly visible on the website.');
$GLOBALS['TL_LANG']['tl_document']['start']        = array('Show from', 'Do not show the document item on the website before this day.');
$GLOBALS['TL_LANG']['tl_document']['stop']         = array('Show until', 'Do not show the document item on the website on and after this day.');


/**
 * Legends
 */
$GLOBALS['TL_LANG']['tl_document']['title_legend']     = 'Title and author';
$GLOBALS['TL_LANG']['tl_document']['date_legend']      = 'Date and time';
$GLOBALS['TL_LANG']['tl_document']['teaser_legend']    = 'Subheadline and teaser';
$GLOBALS['TL_LANG']['tl_document']['text_legend']      = 'Document text';
$GLOBALS['TL_LANG']['tl_document']['expert_legend']    = 'Expert settings';
$GLOBALS['TL_LANG']['tl_document']['source_legend']    = 'Source settings';
$GLOBALS['TL_LANG']['tl_document']['publish_legend']   = 'Publish settings';

/**
 * Buttons
 */
$GLOBALS['TL_LANG']['tl_document']['new']        = array('New document', 'Create a new document.');
$GLOBALS['TL_LANG']['tl_document']['show']       = array('Document details', 'Show the details of document ID %s');
$GLOBALS['TL_LANG']['tl_document']['edit']       = array('Edit document', 'Edit document ID %s');
$GLOBALS['TL_LANG']['tl_document']['copy']       = array('Duplicate document', 'Duplicate document ID %s');
$GLOBALS['TL_LANG']['tl_document']['cut']        = array('Move document', 'Move document ID %s');
$GLOBALS['TL_LANG']['tl_document']['delete']     = array('Delete document', 'Delete document ID %s');
$GLOBALS['TL_LANG']['tl_document']['toggle']     = array('Publish/unpublish document', 'Publish/unpublish document ID %s');
$GLOBALS['TL_LANG']['tl_document']['feature']    = array('Feature/unfeature document', 'Feature/unfeature document ID %s');
$GLOBALS['TL_LANG']['tl_document']['editheader'] = array('Edit archive settings', 'Edit the archive settings');
$GLOBALS['TL_LANG']['tl_document']['pasteafter'] = array('Paste into this archive', 'Paste after document ID %s');