<?php

namespace {

    /**
     * Document management for Contao Open Source CMS
     * @license    http://opensource.org/licenses/lgpl-3.0.html
     */

    if(TL_MODE==='BE') {
        array_insert($GLOBALS['TL_JAVASCRIPT'], 99, array    (
            'bundles/rhymecontaodocuments/assets/js/docman.js'
        ));

        $GLOBALS['TL_CSS'][] = 'bundles/rhymecontaodocuments/assets/css/be_styles.css';
    }


    /**
     * Back end modules
     */
    \array_insert($GLOBALS['BE_MOD']['content'], 10, array
    (
        'document' => array
        (
            'tables'      => array('tl_document_archive', 'tl_document'),
            'icon'        => 'system/modules/documents/assets/img/icon.png',
        )
    ));


    /**
     * Front end modules
     */
    \array_insert($GLOBALS['FE_MOD'], 2, array
    (
        'document' => array
        (
            'documentlist'      => 'Rhyme\ContaoDocumentsBundle\Module\Document\Lister',
            'documentreader'    => 'Rhyme\ContaoDocumentsBundle\Module\Document\Reader',
            'documentfilter'    => 'Rhyme\ContaoDocumentsBundle\Module\Document\Filter',
        )
    ));


    /**
     * Content elements
     */
    $GLOBALS['TL_CTE']['document']['document_single'] 		= 'Rhyme\ContaoDocumentsBundle\ContentElement\Document';
    $GLOBALS['TL_CTE']['document']['document_list'] 		= 'Rhyme\ContaoDocumentsBundle\ContentElement\DocumentList';


    /**
     * Form fields
     */
    $GLOBALS['BE_FFL']['documentWizard'] 			        = 'Rhyme\ContaoDocumentsBundle\Widget\DocumentWizard';


    /**
     * Models
     */
    $GLOBALS['TL_MODELS']['tl_document']            = 'Rhyme\ContaoDocumentsBundle\Model\Document';
    $GLOBALS['TL_MODELS']['tl_document_archive']    = 'Rhyme\ContaoDocumentsBundle\Model\DocumentArchive';


    /**
     * Hooks
     */
    $GLOBALS['TL_HOOKS']['executePostActions'][]        = array('Rhyme\ContaoDocumentsBundle\Hooks\ExecutePostActions\ToggleFeaturedDoc', 'run');
    $GLOBALS['TL_HOOKS']['findDocuments'][]             = array('Rhyme\ContaoDocumentsBundle\Hooks\FindDocuments\ApplyDocumentFilters', 'run');
    $GLOBALS['TL_HOOKS']['countByDocuments'][]          = array('Rhyme\ContaoDocumentsBundle\Hooks\CountByDocuments\ApplyDocumentFilters', 'run');


    /**
     * Add permissions
     */
    $GLOBALS['TL_PERMISSIONS'][] = 'document';
    $GLOBALS['TL_PERMISSIONS'][] = 'documentp';



    /**
     * "Stop" words for keyword search
     */
    $GLOBALS['DOCUMENTS_KEYWORD_STOP_WORDS'] = array("a","all","also","am","among","an","and","any","anyhow","anyone","anything","anyway","anywhere","are","around","as","at","back","be","became","because","become","becomes","been","before","being","below","beside","besides","beyond","both","bottom","but","by","call","can","cannot","cant","co","con","could","couldnt","de","describe","do","done","due","during","each","eg","eight","either","eleven","else","elsewhere","enough","etc","even","ever","every","except","few","fifteen","fifty","fill","find","fire","for","former","formerly","forty","found","four","from","further","get","give","go","had","has","hasnt","have","he","hence","her","here","hers","his","how","however","hundred","ie","if","in","inc","indeed","is","it","its","itself","latter","latterly","less","ltd","many","may","me","more","moreover","most","mostly","must","my","myself","namely","neither","nevertheless","nine","no","nobody","none","noone","nor","not","now","nowhere","of","off","often","on","once","one","only","onto","or","other","others","otherwise","our","ours","ourselves","over","own","part","per","perhaps","put","rather","re","see","seem","several","she","should","side","since","sincere","six","sixty","so","some","someone","something","sometime","sometimes","somewhere","still","such","system","take","ten","than","that","the","their","them","themselves","then","thence","there","thereafter","thereby","therefore","therein","thereupon","these","they","thickv","third","this","those","though","three","through","throughout","thru","thus","to","too","toward","towards","twelve","twenty","two","un","under","until", "up", "upon", "us", "very","via","was","we","well","were","what","whatever","when","whence","whenever","where","whereafter","whereas","whereby","wherein","whereupon","wherever","whether","which","while","whither","who","whoever","whom","whose","why","with","within","without","would","yet","you","your","yours","yourself","yourselves");

}