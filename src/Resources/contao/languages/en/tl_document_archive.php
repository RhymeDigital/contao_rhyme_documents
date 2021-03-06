<?php

/**
 * Document management for Contao Open Source CMS
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */


/**
 * Fields
 */
$GLOBALS['TL_LANG']['tl_document_archive']['title']          = array('Title', 'Please enter a document archive title.');
$GLOBALS['TL_LANG']['tl_document_archive']['jumpTo']         = array('Redirect page', 'Please choose the document reader page to which visitors will be redirected when clicking a document item.');
$GLOBALS['TL_LANG']['tl_document_archive']['allowComments']  = array('Enable comments', 'Allow visitors to comment document items.');
$GLOBALS['TL_LANG']['tl_document_archive']['notify']         = array('Notify', 'Please choose who to notify when comments are added.');
$GLOBALS['TL_LANG']['tl_document_archive']['sortOrder']      = array('Sort order', 'By default, comments are sorted ascending, starting with the oldest one.');
$GLOBALS['TL_LANG']['tl_document_archive']['perPage']        = array('Comments per page', 'Number of comments per page. Set to 0 to disable pagination.');
$GLOBALS['TL_LANG']['tl_document_archive']['moderate']       = array('Moderate comments', 'Approve comments before they are published on the website.');
$GLOBALS['TL_LANG']['tl_document_archive']['bbcode']         = array('Allow BBCode', 'Allow visitors to format their comments with BBCode.');
$GLOBALS['TL_LANG']['tl_document_archive']['requireLogin']   = array('Require login to comment', 'Allow only authenticated users to create comments.');
$GLOBALS['TL_LANG']['tl_document_archive']['disableCaptcha'] = array('Disable the security question', 'Use this option only if you have limited comments to authenticated users.');
$GLOBALS['TL_LANG']['tl_document_archive']['protected']      = array('Protect archive', 'Show document items to certain member groups only.');
$GLOBALS['TL_LANG']['tl_document_archive']['groups']         = array('Allowed member groups', 'These groups will be able to see the document items in this archive.');
$GLOBALS['TL_LANG']['tl_document_archive']['makeFeed']       = array('Generate feed', 'Generate an RSS or Atom feed from the document archive.');
$GLOBALS['TL_LANG']['tl_document_archive']['format']         = array('Feed format', 'Please choose a feed format.');
$GLOBALS['TL_LANG']['tl_document_archive']['language']       = array('Feed language', 'Please enter the feed language according to the ISO-639 standard (e.g. <em>en</em> or <em>en-us</em>).');
$GLOBALS['TL_LANG']['tl_document_archive']['source']         = array('Export settings', 'Here you can choose what will be exported.');
$GLOBALS['TL_LANG']['tl_document_archive']['maxItems']       = array('Maximum number of items', 'Here you can limit the number of feed items. Set to 0 to export all.');
$GLOBALS['TL_LANG']['tl_document_archive']['feedBase']       = array('Base URL', 'Please enter the base URL with protocol (e.g. <em>http://</em>).');
$GLOBALS['TL_LANG']['tl_document_archive']['alias']          = array('Feed alias', 'Here you can enter a unique filename (without extension). The XML feed file will be auto-generated in the root directory of your Contao installation, e.g. as <em>name.xml</em>.');
$GLOBALS['TL_LANG']['tl_document_archive']['description']    = array('Feed description', 'Please enter a short description of the document feed.');
$GLOBALS['TL_LANG']['tl_document_archive']['tstamp']         = array('Revision date', 'Date and time of the latest revision');


/**
 * Legends
 */
$GLOBALS['TL_LANG']['tl_document_archive']['title_legend']     = 'Title and redirect page';
$GLOBALS['TL_LANG']['tl_document_archive']['protected_legend'] = 'Access protection';


/**
 * Buttons
 */
$GLOBALS['TL_LANG']['tl_document_archive']['new']        = array('New archive', 'Create a new archive');
$GLOBALS['TL_LANG']['tl_document_archive']['show']       = array('Archive details', 'Show the details of archive ID %s');
$GLOBALS['TL_LANG']['tl_document_archive']['edit']       = array('Edit archive', 'Edit archive ID %s');
$GLOBALS['TL_LANG']['tl_document_archive']['editheader'] = array('Edit archive settings', 'Edit the settings of archive ID %s');
$GLOBALS['TL_LANG']['tl_document_archive']['copy']       = array('Duplicate archive', 'Duplicate archive ID %s');
$GLOBALS['TL_LANG']['tl_document_archive']['delete']     = array('Delete archive', 'Delete archive ID %s');
$GLOBALS['TL_LANG']['tl_document_archive']['comments']   = array('Comments', 'Show comments of archive ID %s');

