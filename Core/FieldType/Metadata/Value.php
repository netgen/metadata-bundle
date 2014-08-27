<?php

namespace Netgen\Bundle\MetadataBundle\Core\FieldType\Metadata;

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

    public $priority;

    public $change;

    public $title;

    public $keywords = array();

    public $description;

    public $sitemap_use;

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

        $xmlStruct = simplexml_load_string($xmlDoc->saveXML());
        $json = json_encode($xmlStruct);
        $array = json_decode($json, true);

        if( !empty($array['title'] ) )
        {
            $this->title = $array['title'];
        }
        if( !empty($array['keywords'] ) )
        {
            $this->keywords = explode(',', $array['keywords'] );
        }
        if( !empty($array['description'] ) )
        {
            $this->description = $array['description'];
        }
        if( !empty($array['priority'] ) )
        {
            $this->priority = $array['priority'];
        }
        if( !empty($array['change'] ) )
        {
            $this->change = $array['change'];
        }
        if( !empty($array['sitemap_use'] ) )
        {
            $this->sitemap_use = $array['sitemap_use'];
        }
    }

    /**
     * @see \eZ\Publish\Core\FieldType\Value
     */
    public function __toString()
    {
        return isset( $this->xml ) ? (string)$this->xml->saveXML() : self::EMPTY_VALUE;
    }
}
