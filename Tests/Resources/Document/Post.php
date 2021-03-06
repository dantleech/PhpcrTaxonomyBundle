<?php

namespace DTL\PhpcrTaxonomyBundle\Tests\Resources\Document;

use Doctrine\ODM\PHPCR\Mapping\Annotations as PHPCR;
use DTL\PhpcrTaxonomyBundle\Metadata\Annotations as PhpcrTaxonomy;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @PHPCR\Document(referenceable=true)
 */
class Post
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
     * @PhpcrTaxonomy\Taxons(path="/test/taxons")
     */
    protected $tags;

    /**
     * @PhpcrTaxonomy\TaxonObjects()
     */
    protected $tagObjects;

    /**
     * @PHPCR\NodeName()
     */
    protected $title;

    public function __construct()
    {
        $this->tagObjects = new ArrayCollection();
    }

    public function getId() 
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getParent() 
    {
        return $this->parent;
    }

    public function setParent($parent)
    {
        $this->parent = $parent;
    }

    public function getTitle() 
    {
        return $this->title;
    }

    public function setTitle($title)
    {
        $this->title = $title;
    }


    public function getTags() 
    {
        return $this->tags;
    }
    
    public function setTags($tags)
    {
        $this->tags = $tags;
    }

    public function getTagObjects() 
    {
        return $this->tagObjects;
    }
}
