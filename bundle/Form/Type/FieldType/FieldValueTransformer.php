<?php

namespace Netgen\Bundle\MetadataBundle\Form\Type\FieldType;

use eZ\Publish\SPI\FieldType\FieldType;
use eZ\Publish\SPI\FieldType\Value;
use Symfony\Component\Form\DataTransformerInterface;

class FieldValueTransformer implements DataTransformerInterface
{
    /**
     * @var \eZ\Publish\SPI\FieldType\FieldType
     */
    private $fieldType;

    public function __construct(FieldType $fieldType)
    {
        $this->fieldType = $fieldType;
    }

    /**
     * @param \Netgen\Bundle\MetadataBundle\Core\FieldType\Metadata\Value $value
     *
     * @return array
     */
    public function transform($value)
    {
        if (!$value instanceof Value) {
            return null;
        }

        return array(
            'title' => $value->title,
            'description' => $value->description,
            'keywords' => implode(',', $value->keywords),
            'priority' => $value->priority,
            'change' => $value->change,
            'sitemap_use' => !empty($value->sitemap_use),
        );
    }

    public function reverseTransform($value)
    {
        if ($value === null) {
            return $this->fieldType->getEmptyValue();
        }

        $value['keywords'] = explode(',', $value['keywords']);
        $value['sitemap_use'] = $value['sitemap_use'] ? '1' : '0';

        return $this->fieldType->acceptValue($value);
    }
}
