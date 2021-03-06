<?php

namespace DTL\PhpcrTaxonomyBundle\Metadata;

use Metadata\ClassMetadata as BaseClassMetadata;
use DTL\PhpcrTaxonomyBundle\Metadata\Property\TaxonsMetadata;
use DTL\PhpcrTaxonomyBundle\Metadata\Property\TaxonObjectsMetadata;
use Metadata\PropertyMetadata;
use Doctrine\Common\Persistence\Mapping\MappingException;

class ClassMetadata extends BaseClassMetadata
{
    protected $defaultTaxonsField = array(
        'taxonClass' => 'DTL\PhpcrTaxonomyBundle\Document\Taxon',
    );

    protected $hasMetadata = false;

    public function hasMetadata() 
    {
        return $this->hasMetadata;
    }


    public function addPropertyMetadata(PropertyMetadata $propertyMetadata)
    {
        $this->hasMetadata = true;
        return parent::addPropertyMetadata($propertyMetadata);
    }

    /**
     * taxonsField = array('name' => $fieldName, 'path' => $taxonsPath)
     */
    public function setTaxonsField(array $taxonsField)
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

        $taxonMetadata = new TaxonsMetadata($this->getReflection()->name, $taxonsField['name']);
        $taxonMetadata->setPath($taxonsField['path']);
        $taxonMetadata->setTaxonClass($taxonsField['taxonClass']);
        
        $this->addPropertyMetadata($taxonMetadata);
    }

    public function setTaxonObjectsField(array $taxonObjectsField)
    {
        if (!isset($taxonObjectsField['name'])) {
            throw new \InvalidArgumentException(sprintf(
                'No "name" specified for taxons field in class "%s"',
                $this->getReflection()->name
            ));
        }

        $taxonObjectsMetadata = new TaxonObjectsMetadata($this->getReflection()->name, $taxonObjectsField['name']);
        $this->addPropertyMetadata($taxonObjectsMetadata);
    }

    public function getTaxonsField() 
    {
        $ret = array();
        foreach ($this->propertyMetadata as $propertyMeta) {
            if ($propertyMeta instanceof TaxonsMetadata) {
                return $propertyMeta;
            }
        }

        throw new MappingException(sprintf(
            'No taxons field mapped for class "%s"', $this->getReflection()->name, $this->getReflection()->name
        ));
    }

    public function getTaxons($document)
    {
        $prop = $this->getReflection()->getProperty(
            $this->getTaxonsField()->name
        );
        $prop->setAccessible(true);

        return $prop->getValue($document);
    }

    public function getTaxonObjects($document)
    {
        $prop = $this->getReflection()->getProperty(
            $this->getTaxonObjectsField()->name
        );
        $prop->setAccessible(true);

        return $prop->getValue($document);
    }

    public function getTaxonObjectsField() 
    {
        $ret = array();
        foreach ($this->propertyMetadata as $propertyMeta) {
            if ($propertyMeta instanceof TaxonObjectsMetadata) {
                return $propertyMeta;
            }
        }

        throw new MappingException(sprintf(
            'No taxon objects field mapped for class "%s"', $this->getReflection()->name
        ));
    }
    
    public function getReflection()
    {
        return $this->reflection;
    }
}
