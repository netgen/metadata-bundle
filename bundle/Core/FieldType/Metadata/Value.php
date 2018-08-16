<?php

namespace Netgen\Bundle\MetadataBundle\Core\FieldType\Metadata;

use eZ\Publish\Core\FieldType\Value as BaseValue;
use DOMDocument;
use ArrayAccess;

class Value extends BaseValue implements ArrayAccess
{
    const EMPTY_VALUE = <<<'EOT'
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

    public $keywords = array();

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
            $this->xml->loadXML($xmlDoc === null ? self::EMPTY_VALUE : $xmlDoc);
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

    /**
     * @see \eZ\Publish\Core\FieldType\Value
     */
    public function __toString()
    {
        return isset($this->xml) ? (string)$this->xml->saveXML() : self::EMPTY_VALUE;
    }

    /**
     * Whether a offset exists.
     *
     * @param mixed $offset An offset to check for
     *
     * @return bool true on success or false on failure.
     *                 The return value will be casted to boolean if non-boolean was returned.
     */
    public function offsetExists($offset)
    {
        return isset($this->{$offset});
    }

    /**
     * Offset to retrieve.
     *
     * @param mixed $offset The offset to retrieve
     *
     * @return mixed
     */
    public function offsetGet($offset)
    {
        if (is_array($this->{$offset})) {
            return implode(',', $this->{$offset});
        }

        return $this->{$offset};
    }

    /**
     * Offset to set.
     *
     * @param mixed $offset The offset to set
     * @param mixed $value The value to set
     */
    public function offsetSet($offset, $value)
    {
    }

    /**
     * Offset to unset.
     *
     * @param mixed $offset The offset to unset
     */
    public function offsetUnset($offset)
    {
    }
}
