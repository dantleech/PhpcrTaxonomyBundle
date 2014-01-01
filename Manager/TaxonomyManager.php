<?php

namespace DTL\PhpcrTaxonomyBundle\Manager;

use Metadata\MetadataFactory;

class TaxonomyManager
{
    protected $metadataFactory;

    public function __construct(MetadataFactory $metadataFactory)
    {
        $this->metadataFactory = $metadataFactory;
    }
}
