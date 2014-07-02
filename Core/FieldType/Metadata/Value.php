<?php

namespace Netgen\MetadataBundle\Core\FieldType\Metadata;

use eZ\Publish\Core\FieldType\Value as BaseValue;

use DOMDocument;

class Value extends BaseValue
{
    const EMPTY_VALUE = <<<EOT
<?xml version="1.0" encoding="utf-8"?>
<MetaData/>
EOT;

    /**
     * XML content as DOMDocument
     *
     * @var \DOMDocument
     */
    public $xml;

    /**
     * Initializes a new Metadata Value object with $xmlDoc in
     *
     * @param \DOMDocument $xmlDoc
     */
    public function __construct( DOMDocument $xmlDoc = null )
    {
        if ( $xmlDoc === null )
        {
            $xmlDoc = new DOMDocument;
            $xmlDoc->loadXML( self::EMPTY_VALUE );
        }

        $this->xml = $xmlDoc;
    }

    /**
     * @see \eZ\Publish\Core\FieldType\Value
     */
    public function __toString()
    {
        return isset( $this->xml ) ? (string)$this->xml->saveXML() : self::EMPTY_VALUE;
    }
}
