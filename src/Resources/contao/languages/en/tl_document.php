<?php

/**
 * Document management for Contao Open Source CMS
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */

declare(strict_types=1);

namespace
{
    use Rhyme\ContaoDocumentsBundle\Model\Document;

    $lang = &$GLOBALS['TL_LANG'][Document::getTable()];

    /**
     * Backend search
     */
    $lang['tableLabel'] = 'Document';

    /**
     * Fields
     */
    $lang['headline']    = array('Title', 'Please enter the document title.');
    $lang['alias']       = array('Document alias', 'The document alias is a unique reference to the document which can be called instead of its numeric ID.');
    $lang['author']      = array('Author', 'Here you can change the author of the document item.');
    $lang['date']        = array('Date', 'Please enter the date according to the global date format.');
    $lang['time']        = array('Time', 'Please enter the time according to the global time format.');
    $lang['subheadline'] = array('Subheadline', 'Here you can enter a subheadline.');
    $lang['teaser']      = array('Document teaser', 'The document teaser can be shown in a document list instead of the full document. A "read more ..." link will be added automatically.',);
    $lang['text']        = array('Document text', 'Here you can enter the document text.');
    $lang['cssClass']    = array('CSS class', 'Here you can enter one or more classes.');
    $lang['featured']    = array('Feature item', 'Show the document item in a featured document list.');
    $lang['published']   = array('Publish item', 'Make the document item publicly visible on the website.');
    $lang['start']       = array('Show from', 'Do not show the document item on the website before this day.');
    $lang['stop']        = array('Show until', 'Do not show the document item on the website on and after this day.');
    $lang['singleSRC']   = array('Document file', 'Please select a document file.');
    $lang['target']      = array('Open in new window', 'Open the link in a new browser window.');
    $lang['url']         = array('Override with link', 'Override the document link with a custom URL.');

    /**
     * Legends
     */
    $lang['title_legend']   = 'Title and author';
    $lang['date_legend']    = 'Date and time';
    $lang['teaser_legend']  = 'Subheadline and teaser';
    $lang['text_legend']    = 'Document text';
    $lang['expert_legend']  = 'Expert settings';
    $lang['source_legend']  = 'Source settings';
    $lang['publish_legend'] = 'Publish settings';

    /**
     * Buttons
     */
    $lang['new']        = array('New document', 'Create a new document.');
    $lang['show']       = array('Document details', 'Show the details of document ID %s');
    $lang['edit']       = array('Edit document', 'Edit document ID %s');
    $lang['copy']       = array('Duplicate document', 'Duplicate document ID %s');
    $lang['cut']        = array('Move document', 'Move document ID %s');
    $lang['delete']     = array('Delete document', 'Delete document ID %s');
    $lang['toggle']     = array('Publish/unpublish document', 'Publish/unpublish document ID %s');
    $lang['feature']    = array('Feature/unfeature document', 'Feature/unfeature document ID %s');
    $lang['editheader'] = array('Edit archive settings', 'Edit the archive settings');
    $lang['pasteafter'] = array('Paste into this archive', 'Paste after document ID %s');
}