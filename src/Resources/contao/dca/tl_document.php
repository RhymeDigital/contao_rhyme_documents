<?php

/**
 * Document management for Contao Open Source CMS
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */

namespace {

    /**
     * Load tl_content language file
     */
    $this->loadLanguageFile('tl_content');

    use Contao\BackendUser;
    use Contao\DC_Table;
    use Rhyme\ContaoDocumentsBundle\Backend\Document\Callbacks;

    /**
     * Table tl_document
     */
    $GLOBALS['TL_DCA']['tl_document'] = array
    (

        // Config
        'config' => array
        (
            'dataContainer'               => DC_Table::class,
            'ptable'                      => 'tl_document_archive',
            'switchToEdit'                => true,
            'enableVersioning'            => true,
            'onload_callback' => array
            (
                array(Callbacks::class, 'checkPermission'),
            ),
            'onsubmit_callback' => array
            (
                array(Callbacks::class, 'adjustTime'),
            ),
            'sql' => array
            (
                'keys' => array
                (
                    'id' => 'primary',
                    'pid' => 'index',
                    'alias' => 'index'
                )
            )
        ),

        // List
        'list' => array
        (
            'sorting' => array
            (
                'mode'                    => 4,
                'fields'                  => array('date DESC'),
                'headerFields'            => array('headline', 'jumpTo', 'tstamp', 'protected'),
                'panelLayout'             => 'filter;sort,search,limit',
                'child_record_callback'   => array(Callbacks::class, 'listDocuments'),
                'child_record_class'      => 'no_padding'
            ),
            'global_operations' => array
            (
                'all' => array
                (
                    'label'               => &$GLOBALS['TL_LANG']['MSC']['all'],
                    'href'                => 'act=select',
                    'class'               => 'header_edit_all',
                    'attributes'          => 'onclick="Backend.getScrollOffset()" accesskey="e"'
                )
            ),
            'operations' => array
            (
                '!edit',
                '!copy',
                '!cut',
                '!delete',
                'toggle' => array
                (
                    'label'               => &$GLOBALS['TL_LANG']['tl_document']['toggle'],
                    'icon'                => 'visible.svg',
                    'href'                => 'act=toggle&amp;field=published',
                    'primary'             => true,
                ),
                'feature' => array
                (
                    'label'           => &$GLOBALS['TL_LANG']['tl_document']['feature'],
                    'primary'         => true,
                    'icon'            => 'featured.svg',
                    'href'            => 'act=toggle&amp;field=featured',
                ),
                'show'
            )
        ),

        // Palettes
        'palettes' => array
        (
            '__selector__'                => array(),
            'default'                     => '{title_legend},headline,alias,author;{date_legend},date,time;{teaser_legend},subheadline,teaser;{source_legend},singleSRC,url,target;{expert_legend:hide},cssClass,featured;{publish_legend},published,start,stop'
        ),

        // Subpalettes
        'subpalettes' => array
        (

        ),

        // Fields
        'fields' => array
        (
            'id' => array
            (
                'sql'                     => "int(10) unsigned NOT NULL auto_increment"
            ),
            'pid' => array
            (
                'foreignKey'              => 'tl_document_archive.title',
                'sql'                     => "int(10) unsigned NOT NULL default '0'",
                'relation'                => array('type'=>'belongsTo', 'load'=>'eager')
            ),
            'tstamp' => array
            (
                'sql'                     => "int(10) unsigned NOT NULL default '0'"
            ),
            'headline' => array
            (
                'exclude'                 => true,
                'search'                  => true,
                'sorting'                 => true,
                'flag'                    => 1,
                'inputType'               => 'text',
                'eval'                    => array('mandatory'=>true, 'maxlength'=>255),
                'sql'                     => "varchar(255) NOT NULL default ''"
            ),
            'alias' => array
            (
                'exclude'                 => true,
                'search'                  => true,
                'inputType'               => 'text',
                'eval'                    => array('rgxp'=>'alias', 'unique'=>true, 'maxlength'=>128, 'tl_class'=>'w50'),
                'save_callback' => array
                (
                    array(Callbacks::class, 'generateAlias')
                ),
                'sql'                     => "varchar(128) BINARY NOT NULL default ''"
            ),
            'author' => array
            (
                'default'                 => BackendUser::getInstance()->id,
                'exclude'                 => true,
                'filter'                  => true,
                'sorting'                 => true,
                'flag'                    => 11,
                'inputType'               => 'select',
                'foreignKey'              => 'tl_user.name',
                'eval'                    => array('doNotCopy'=>true, 'chosen'=>true, 'mandatory'=>true, 'includeBlankOption'=>true, 'tl_class'=>'w50'),
                'sql'                     => "int(10) unsigned NOT NULL default '0'",
                'relation'                => array('type'=>'hasOne', 'load'=>'eager')
            ),
            'date' => array
            (
                'default'                 => \time(),
                'exclude'                 => true,
                'filter'                  => true,
                'sorting'                 => true,
                'flag'                    => 8,
                'inputType'               => 'text',
                'eval'                    => array('rgxp'=>'date', 'doNotCopy'=>true, 'datepicker'=>true, 'tl_class'=>'w50 wizard'),
                'sql'                     => "int(10) unsigned NOT NULL default '0'"
            ),
            'time' => array
            (
                'default'                 => \time(),
                'exclude'                 => true,
                'inputType'               => 'text',
                'eval'                    => array('rgxp'=>'time', 'doNotCopy'=>true, 'tl_class'=>'w50'),
                'sql'                     => "int(10) unsigned NOT NULL default '0'"
            ),
            'subheadline' => array
            (
                'exclude'                 => true,
                'search'                  => true,
                'inputType'               => 'text',
                'eval'                    => array('maxlength'=>255, 'tl_class'=>'long'),
                'sql'                     => "varchar(255) NOT NULL default ''"
            ),
            'teaser' => array
            (
                'exclude'                 => true,
                'search'                  => true,
                'inputType'               => 'textarea',
                'eval'                    => array('rte'=>'tinyMCE', 'tl_class'=>'clr'),
                'sql'                     => "text NULL"
            ),
            'singleSRC' => array
            (
                'exclude'                 => true,
                'inputType'               => 'fileTree',
                'eval'                    => array('filesOnly'=>true, 'fieldType'=>'radio'),
                'save_callback'           => array
                (
                    array(Callbacks::class, 'checkRequired'),
                ),
                'sql'                     => "binary(16) NULL"
            ),
            'url' => array
            (
                'exclude'                 => true,
                'search'                  => true,
                'inputType'               => 'text',
                'eval'                    => array('decodeEntities'=>true, 'maxlength'=>255, 'tl_class'=>'w50'),
                'save_callback'           => array
                (
                    array(Callbacks::class, 'checkRequired'),
                ),
                'sql'                     => "varchar(255) NOT NULL default ''"
            ),
            'target' => array
            (
                'exclude'                 => true,
                'inputType'               => 'checkbox',
                'eval'                    => array('tl_class'=>'w50 m12'),
                'sql'                     => "char(1) NOT NULL default ''"
            ),
            'cssClass' => array
            (
                'exclude'                 => true,
                'inputType'               => 'text',
                'sql'                     => "varchar(255) NOT NULL default ''"
            ),
            'featured' => array
            (
                'exclude'                 => true,
                'filter'                  => true,
                'toggle'                  => true,
                'inputType'               => 'checkbox',
                'eval'                    => array('tl_class'=>'w50'),
                'sql'                     => "char(1) NOT NULL default ''"
            ),
            'published' => array
            (
                'exclude'                 => true,
                'filter'                  => true,
                'toggle'                  => true,
                'flag'                    => 1,
                'inputType'               => 'checkbox',
                'eval'                    => array('doNotCopy'=>true),
                'sql'                     => "char(1) NOT NULL default ''"
            ),
            'start' => array
            (
                'exclude'                 => true,
                'inputType'               => 'text',
                'eval'                    => array('rgxp'=>'datim', 'datepicker'=>true, 'tl_class'=>'w50 wizard'),
                'sql'                     => "varchar(10) NOT NULL default ''"
            ),
            'stop' => array
            (
                'exclude'                 => true,
                'inputType'               => 'text',
                'eval'                    => array('rgxp'=>'datim', 'datepicker'=>true, 'tl_class'=>'w50 wizard'),
                'sql'                     => "varchar(10) NOT NULL default ''"
            )
        )
    );
}