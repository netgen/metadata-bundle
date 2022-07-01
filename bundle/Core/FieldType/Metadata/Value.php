<?php

declare(strict_types=1);

namespace Netgen\Bundle\MetadataBundle\Core\FieldType\Metadata;

use ArrayAccess;
use DOMDocument;
use Ibexa\Core\FieldType\Value as BaseValue;
use function explode;
use function implode;
use function is_array;
use function json_decode;
use function json_encode;
use function simplexml_load_string;

final class Value extends BaseValue implements ArrayAccess
{
    public const EMPTY_VALUE = <<<'EOT'
<?xml version="1.0" encoding="utf-8"?>
<MetaData/>
EOT;

    /**
     * XML content as DOMDocument.
     *
     * @var \DOMDocument
     */
    public $xml;

    public $priority;

    public $change;

    public $title;

    public $keywords = [];

    public $description;

    public $sitemap_use;

    /**
     * Initializes a new Metadata Value object with $xmlDoc in.
     *
     * @param \DOMDocument|string $xmlDoc
     */
    public function __construct($xmlDoc = null)
    {
        if ($xmlDoc instanceof DOMDocument) {
            $this->xml = $xmlDoc;
        } else {
            $this->xml = new DOMDocument();
            $this->xml->loadXML($xmlDoc ?? self::EMPTY_VALUE);
        }

        $xmlStruct = simplexml_load_string($this->xml->saveXML());
        $json = json_encode($xmlStruct);
        $array = json_decode($json, true);

        if (!empty($array['title'])) {
            $this->title = $array['title'];
        }
        if (!empty($array['keywords'])) {
            $this->keywords = explode(',', $array['keywords']);
        }
        if (!empty($array['description'])) {
            $this->description = $array['description'];
        }
        if (!empty($array['priority'])) {
            $this->priority = $array['priority'];
        }
        if (!empty($array['change'])) {
            $this->change = $array['change'];
        }
        if (!empty($array['sitemap_use'])) {
            $this->sitemap_use = $array['sitemap_use'];
        }
    }

    public function __toString(): string
    {
        return isset($this->xml) ? (string) $this->xml->saveXML() : self::EMPTY_VALUE;
    }

    public function offsetExists($offset): bool
    {
        return isset($this->{$offset});
    }

    public function offsetGet($offset)
    {
        if (is_array($this->{$offset})) {
            return implode(',', $this->{$offset});
        }

        return $this->{$offset};
    }

    public function offsetSet($offset, $value): void
    {
    }

    public function offsetUnset($offset): void
    {
    }
}
