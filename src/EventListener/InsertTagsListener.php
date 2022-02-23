<?php

/**
 * Copyright (C) 2021 Rhyme Digital, LLC.
 *
 * @link       https://rhyme.digital
 * @license    http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 */

namespace Rhyme\ContaoDocumentsBundle\EventListener;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\StringUtil;
use Rhyme\ContaoDocumentsBundle\Model\Document as DocumentModel;
use Rhyme\ContaoDocumentsBundle\Helper\DocumentHelper;

/**
 * Class InsertTagsListener
 * @package Rhyme\ContaoDocumentsBundle\EventListener
 */
class InsertTagsListener
{
    /**
     * @var ContaoFramework
     */
    private $framework;

    public function __construct(ContaoFramework $framework)
    {
        $this->framework = $framework;
    }

    /**
     * @return string|false
     */
    public function onReplaceInsertTags(string $tag, bool $useCache, $cacheValue, array $flags)
    {
        $elements = StringUtil::trimsplit('::', $tag);
        $key = \strtolower($elements[0]);

        static $supportedVideoTags = [
            'rhyme_document_url',
        ];

        if (\in_array($key, $supportedVideoTags, true)) {
            return $this->replaceDocumentInsertTag($key, $elements[1], $flags);
        }

        return false;
    }


    private function replaceDocumentInsertTag(string $insertTag, string $idOrAlias, array $flags): string
    {
        $this->framework->initialize();

        /** @var DocumentModel $adapter */
        $adapter = $this->framework->getAdapter(DocumentModel::class);

        if (null === ($model = $adapter->findByIdOrAlias($idOrAlias))) {
            return '';
        }

        switch ($insertTag) {
            case 'rhyme_document_url':
                return DocumentHelper::generateDocumentUrl($model);
        }

        return '';
    }
}
