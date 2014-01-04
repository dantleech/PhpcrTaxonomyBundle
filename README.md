PHPCR Taxonomy Bundle
=====================

[![Build Status](https://secure.travis-ci.org/dantleech/PhpcrTaxonomyBundle.png)](http://travis-ci.org/dantleech/PhpcrTaxonomyBundle)
[![Latest Stable Version](https://poser.pugx.org/dantleech/phpcr-taxonomy-bundle/version.png)](https://packagist.org/packages/dantleech/phpcr-taxonomy-bundle)
[![Total Downloads](https://poser.pugx.org/dantleech/phpcr-taxonomy-bundle/d/total.png)](https://packagist.org/packages/dantleech/phpcr-taxonomy-bundle)

**NOTE::** This is a work in progress.

What works:

- Automatically creating tag objects at spsecified paths
  - Paths effectively act as taxonomies
- Automatic tag object association with target document
- Taxon referrer count, because PHPCR-ODM doesn't do aggregation. (for tag clouds for example)

What is planned:

- Command to "fix" or initialize taxon referrer counts
- Orphan removal
- Static taxonomies - i.e. specify if new taxons can be created.
- Hierachical tagging, e.g. specify "Laptops > Levono > X200" as a tag,
  creating a 3 level heierachy.

This is a very specific taxonomy bundle for PHPCR.

Basic Usage
-----------

The `PhpcrTaxonomy\Taxons` annotation will automatically map the annotated
property instance as a many-to-many relationship to 
`DTL\PhpcrTaxonomyBundle\Document\Taxon`:

````php
namespace DTL\PhpcrTaxonomyBundle\Tests\Resources\Document;

use Doctrine\ODM\PHPCR\Mapping\Annotations as PHPCR;
use DTL\PhpcrTaxonomyBundle\Metadata\Annotations as PhpcrTaxonomy;

/**
 * @PHPCR\Document(referenceable=true)
 */
class Post
{
    /**
     * @PHPCR\Id()
     */
    public $id;

    /**
     * @PHPCR\ParentDocument()
     */
    public $parent;

    /**
     * @PhpcrTaxonomy\Taxons(path="/test/taxons")
     */
    public $tags;

    /**
     * @PhpcrTaxonomy\TaxonObjects()
     */
    public $tagObjects;
}
````

Tags can be added as follows:

````php
$post = new Post();
$post->setTags(array('one', 'two'));
````

The parent document for each taxon is determined by the `path` property of the
`Taxons` annotation. It must exist before persisting the document.

You can specify an alternative taxon class as follows:

````php
namespace DTL\PhpcrTaxonomyBundle\Tests\Resources\Document;

use Doctrine\ODM\PHPCR\Mapping\Annotations as PHPCR;
use DTL\PhpcrTaxonomyBundle\Metadata\Annotations as PhpcrTaxonomy;

/**
 * @PHPCR\Document(referenceable=true)
 */
class Post
{
    // ...

    /**
     * @PhpcrTaxonomy\Taxons(path="/test/taxons", taxonClass="MyBundle\MyTaxonClass")
     */
    public $tags;
}
````
