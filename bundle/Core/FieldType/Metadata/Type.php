<?php

declare(strict_types=1);

namespace Netgen\Bundle\MetadataBundle\Core\FieldType\Metadata;

use DOMDocument;
use Ibexa\Contracts\Core\Repository\Values\ContentType\FieldDefinition;
use Ibexa\Core\Base\Exceptions\InvalidArgumentType;
use Ibexa\Core\FieldType\FieldType;
use Ibexa\Core\FieldType\Value as BaseValue;
use Ibexa\Contracts\Core\FieldType\Value as SPIValue;
use Ibexa\Contracts\Core\Persistence\Content\FieldValue;
use function htmlspecialchars;
use function implode;
use function is_array;
use function trim;
use const ENT_QUOTES;

final class Type extends FieldType
{
    public function getFieldTypeIdentifier(): string
    {
        return 'xrowmetadata';
    }

    /**
     * @return \Ibexa\Contracts\Core\FieldType\Value|\Netgen\Bundle\MetadataBundle\Core\FieldType\Metadata\Value
     */
    public function getEmptyValue(): SPIValue
    {
        return new Value();
    }

    /**
     * @param \Ibexa\Contracts\Core\FieldType\Value|\Netgen\Bundle\MetadataBundle\Core\FieldType\Metadata\Value $value
     */
    public function isEmptyValue(SPIValue $value): bool
    {
        return $value === null || $value->xml->saveXML() === $this->getEmptyValue()->xml->saveXML();
    }

    public function fromHash($hash): SPIValue
    {
        if (empty($hash) || empty($hash['xml'])) {
            return $this->getEmptyValue();
        }

        return new Value($hash['xml']);
    }

    /**
     * @param \Ibexa\Contracts\Core\FieldType\Value|\Netgen\Bundle\MetadataBundle\Core\FieldType\Metadata\Value $value
     *
     * @return mixed
     */
    public function toHash(SPIValue $value)
    {
        return ['xml' => $value->xml->saveXML()];
    }

    /**
     * @param \Ibexa\Contracts\Core\FieldType\Value|\Netgen\Bundle\MetadataBundle\Core\FieldType\Metadata\Value $value
     */
    public function getName(SPIValue $value, FieldDefinition $fieldDefinition, string $languageCode): string
    {
        return $this->innerGetName($value);
    }

    /**
     * @param \Ibexa\Contracts\Core\FieldType\Value|\Netgen\Bundle\MetadataBundle\Core\FieldType\Metadata\Value $value
     */
    public function toPersistenceValue(SPIValue $value): FieldValue
    {
        return new FieldValue(
            [
                'data' => $value->xml->saveXML(),
                'externalData' => null,
                'sortKey' => $this->getSortInfo($value),
            ]
        );
    }

    public function fromPersistenceValue(FieldValue $fieldValue): SPIValue
    {
        return new Value($fieldValue->data);
    }

    public function isSearchable(): bool
    {
        return true;
    }

    protected function createValueFromInput($inputValue)
    {
        if (is_array($inputValue) && !empty($inputValue)) {
            $title = !empty($inputValue['title']) ? $inputValue['title'] : '';
            $keywords = !empty($inputValue['keywords']) ? $inputValue['keywords'] : [];
            $description = !empty($inputValue['description']) ? $inputValue['description'] : '';

            if (empty($inputValue['priority'])) {
                $priority = null;
            } else {
                $priority = $inputValue['priority'];
            }
            if (empty($inputValue['change'])) {
                $change = 'daily';
            } else {
                $change = $inputValue['change'];
            }

            if ($inputValue['sitemap_use'] === false) {
                $sitemap_use = '1';
            } elseif (empty($inputValue['sitemap_use'])) {
                $sitemap_use = '0';
            } else {
                $sitemap_use = '1';
            }

            $xml = new DOMDocument('1.0');
            $xmlDom = $xml->createElement('MetaData');
            $node = $xml->createElement('title', htmlspecialchars($title, ENT_QUOTES));
            $xmlDom->appendChild($node);
            $node = $xml->createElement('keywords', htmlspecialchars(implode(',', $keywords), ENT_QUOTES));
            $xmlDom->appendChild($node);
            $node = $xml->createElement('description', htmlspecialchars($description, ENT_QUOTES));
            $xmlDom->appendChild($node);
            if (!empty($priority)) {
                $node = $xml->createElement('priority', htmlspecialchars($priority, ENT_QUOTES));
            } else {
                $node = $xml->createElement('priority');
            }
            $xmlDom->appendChild($node);
            $node = $xml->createElement('change', htmlspecialchars($change, ENT_QUOTES));
            $xmlDom->appendChild($node);
            $node = $xml->createElement('sitemap_use', htmlspecialchars($sitemap_use, ENT_QUOTES));
            $xmlDom->appendChild($node);
            $xml->appendChild($xmlDom);

            return new Value($xml);
        }

        if ($inputValue === null) {
            return $this->getEmptyValue();
        }

        return $inputValue;
    }

    /**
     * @param \Ibexa\Core\FieldType\Value|\Netgen\Bundle\MetadataBundle\Core\FieldType\Metadata\Value $value
     */
    protected function checkValueStructure(BaseValue $value): void
    {
        if (!$value->xml instanceof DomDocument) {
            throw new InvalidArgumentType(
                '$value->xml',
                'DomDocument',
                $value
            );
        }
    }

    protected function getSortInfo(BaseValue $value)
    {
        return $this->innerGetName($value);
    }

    /**
     * @param \Ibexa\Contracts\Core\FieldType\Value|\Netgen\Bundle\MetadataBundle\Core\FieldType\Metadata\Value $value
     */
    private function innerGetName(SPIValue $value): string
    {
        $result = null;
        if ($metadata = $value->xml->documentElement->firstChild) {
            $textDom = $metadata->firstChild;

            if ($textDom && $textDom->hasChildNodes()) {
                $result = $textDom->firstChild->textContent;
            } elseif ($textDom) {
                $result = $textDom->textContent;
            }
        }

        if ($result === null) {
            $result = $value->xml->documentElement->textContent;
        }

        return trim($result);
    }
}
