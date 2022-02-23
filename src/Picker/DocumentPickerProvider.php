<?php

/**
 * Copyright (C) 2021 Rhyme Digital, LLC.
 *
 * @link       https://rhyme.digital
 * @license    http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 */

namespace Rhyme\ContaoDocumentsBundle\Picker;

use Contao\CoreBundle\Framework\FrameworkAwareInterface;
use Contao\CoreBundle\Framework\FrameworkAwareTrait;
use Contao\CoreBundle\Picker\AbstractPickerProvider;
use Contao\CoreBundle\Picker\DcaPickerProviderInterface;
use Contao\CoreBundle\Picker\PickerConfig;

class DocumentPickerProvider extends AbstractPickerProvider implements DcaPickerProviderInterface, FrameworkAwareInterface
{
    use FrameworkAwareTrait;

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'rhyme_documentPicker';
    }

    /**
     * {@inheritdoc}
     */
    public function supportsContext($context): bool
    {
        return ('link' === $context || 'document' === $context);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsValue(PickerConfig $config): bool
    {
        return is_integer($config->getValue()) || false !== strpos($config->getValue(), '{{rhyme_document_url::');
    }

    /**
     * {@inheritdoc}
     */
    public function getDcaTable(): string
    {
        return 'tl_document';
    }

    /**
     * {@inheritdoc}
     */
    public function getDcaAttributes(PickerConfig $config): array
    {
        $attributes = [
            'fieldType' => $config->getExtra('fieldType') ?: 'radio'
        ];

        if ($source = $config->getExtra('source')) {
            $attributes['preserveRecord'] = $source;
        }


        if ('document' === $config->getContext()) {
            $value = $config->getValue();

            if ($value) {
                $attributes['value'] = [];

                foreach (explode(',', $value) as $v) {
                    $attributes['value'][] = intval($v);
                }
            }
        }
        else
        {
            $attributes['value'] = str_replace(['{{rhyme_document_url::', '}}'], '', $config->getValue());
        }


        return $attributes;
    }

    /**
     * {@inheritdoc}
     */
    public function convertDcaValue(PickerConfig $config, $value): string
    {
        if ('document' === $config->getContext()) {
            return (int) $value;
        }

        return '{{rhyme_document_url::'.$value.'}}';
    }

    /**
     * {@inheritdoc}
     */
    protected function getRouteParameters(PickerConfig $config = null): array
    {
        $params = ['do' => 'document'];

        if (null === $config || !$config->getValue() || false === strpos($config->getValue(), '{{rhyme_document_url::')) {
            return $params;
        }

        return $params;
    }

}
