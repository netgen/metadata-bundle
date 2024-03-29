<?php

declare(strict_types=1);

namespace Netgen\Bundle\MetadataBundle\Form\Type\FieldType;

use Ibexa\Contracts\Core\FieldType\FieldType;
use Ibexa\Contracts\Core\FieldType\Value;
use Symfony\Component\Form\DataTransformerInterface;
use function explode;
use function implode;

final class FieldValueTransformer implements DataTransformerInterface
{
    /**
     * @var \Ibexa\Contracts\Core\FieldType\FieldType
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
    public function transform($value): ?array
    {
        if (!$value instanceof Value) {
            return null;
        }

        return [
            'title' => $value->title,
            'description' => $value->description,
            'keywords' => implode(',', $value->keywords),
            'priority' => $value->priority,
            'change' => $value->change,
            'sitemap_use' => !empty($value->sitemap_use),
        ];
    }

    public function reverseTransform($value): Value
    {
        if ($value === null) {
            return $this->fieldType->getEmptyValue();
        }

        $value['keywords'] = explode(',', $value['keywords'] ?? '');
        $value['sitemap_use'] = $value['sitemap_use'] ? '1' : '0';

        return $this->fieldType->acceptValue($value);
    }
}
