<?php

namespace DTL\PhpcrTaxonomyBundle\Document;

use Doctrine\ODM\PHPCR\Mapping\Annotations as PHPCR;

/**
 * @PHPCR\Document(referenceable=true)
 */
class Taxon implements TaxonInterface
{
    /**
     * @PHPCR\Id()
     */
    protected $id;

    /**
     * @PHPCR\ParentDocument()
     */
    protected $parent;

    /**
     * @PHPCR\NodeName()
     */
    protected $name;

    /**
     * @PHPCR\MixedReferrers()
     */
    protected $referrers = array();

    /**
     * @PHPCR\Long()
     */
    protected $referrerCount = 0;

    public function getName() 
    {
        return $this->name;
    }
    
    public function setName($name)
    {
        $this->name = $name;
    }

    public function getParent() 
    {
        return $this->parent;
    }
    
    public function setParent($parent)
    {
        $this->parent = $parent;
    }

    public function getReferrers()
    {
        return $this->referrers;
    }

    public function getReferrerCount() 
    {
        return $this->referrerCount;
    }
    
    public function setReferrerCount($referrerCount)
    {
        $this->referrerCount = $referrerCount;
    }
    
}
