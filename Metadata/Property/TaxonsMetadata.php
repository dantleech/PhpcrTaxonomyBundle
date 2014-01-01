<?php

namespace DTL\PhpcrTaxonomyBundle\Metadata\Property;

use Metadata\PropertyMetadata;

class TaxonsMetadata extends PropertyMetadata
{
    protected $path;
    protected $taxonClass;

    public function getPath() 
    {
        return $this->path;
    }
    
    public function setPath($path)
    {
        $this->path = $path;
    }

    public function getTaxonClass() 
    {
        return $this->taxonClass;
    }
    
    public function setTaxonClass($taxonClass)
    {
        $this->taxonClass = $taxonClass;
    }
    
}
