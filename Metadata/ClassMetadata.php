<?php

namespace DTL\PhpcrTaxonomyBundle\Metadata;

use Metadata\ClassMetadata as BaseClassMetadata;
use DTL\PhpcrTaxonomyBundle\Metadata\Property\TaxonsMetadata;

class ClassMetadata extends BaseClassMetadata
{
    protected $defaultTaxonsField = array(
        'taxonClass' => 'DTL\PhpcrTaxonomyBundle\Document\Taxon',
    );

    protected $taxonsFields = array();

    /**
     * taxonsField = array('name' => $fieldName, 'path' => $taxonsPath)
     */
    public function addTaxonsField(array $taxonsField)
    {
        if (!isset($taxonsField['name'])) {
            throw new \InvalidArgumentException(sprintf(
                'No "name" specified for taxons field in class "%s"',
                $this->getReflection()->name
            ));
        }
        if (!isset($taxonsField['path'])) {
            throw new \InvalidArgumentException(sprintf(
                'No path specified for taxons field "%s" in class "%s"'
            ), $taxonsField['name'], $this->getReflection()->name);
        }

        $taxonsField = array_merge(
            $this->defaultTaxonsField, $taxonsField
        );

        // @todo: Move this back to driver?
        $taxonMetadata = new TaxonsMetadata($this->getReflection()->name, $taxonsField['name']);
        $taxonMetadata->setPath($taxonsField['path']);
        $taxonMetadata->setTaxonClass($taxonsField['taxonClass']);
        
        $this->addPropertyMetadata($taxonMetadata);
    }

    public function getTaxonsFields() 
    {
        return $this->taxonsFields;
    }
    
    public function getReflection()
    {
        return $this->reflection;
    }
}
