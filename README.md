PHPCR Taxonomy Bundle
=====================

[![Build Status](https://secure.travis-ci.org/dantleech/PhpcrTaxonomyBundle.png)](http://travis-ci.org/dantleech/PhpcrTaxonomyBundle)
[![Latest Stable Version](https://poser.pugx.org/dantleech/phpcr-taxonomy-bundle/version.png)](https://packagist.org/packages/dantleech/phpcr-taxonomy-bundle)
[![Total Downloads](https://poser.pugx.org/dantleech/phpcr-taxonomy-bundle/d/total.png)](https://packagist.org/packages/dantleech/phpcr-taxonomy-bundle)

What works:

- Automatically creating tag objects at spsecified paths
  - Paths effectively act as taxonomies
- Automatic tag object association with target document
- Taxon referrer count, because PHPCR-ODM doesn't do aggregation. (for tag clouds for example)
- Command to "fix" or initialize taxon referrer counts

What is planned:

- Orphan removal
- Static taxonomies - i.e. specify if new taxons can be created.
- Hierachical tagging, e.g. specify "Laptops > Levono > X200" as a tag,
  creating a 3 level heierachy.

This is a very specific taxonomy bundle for PHPCR.

Basic Usage
-----------

For each document you wish to be tagged you need to add two property annotations, 
`@Taxons` and `@TaxonObjects`. The first will automatically be mapped to an
array, the second will contain a collection of the actual taxon objects.

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

Tags can be set as follows:

````php
$post = new Post();
$post->tags = array('one', 'two');
````

The parent document for each taxon is determined by the `path` property of the
`Taxons` annotation. It must exist before persisting the document.

Alternative Taxon Classes
-------------------------

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

Taxon Referrer Count
--------------------

The Doctrine subscriber automatically records the number of referrers each
taxon document has. For example if a taxon is used by 5 posts, then that taxon
has 5 referrers.

This is especially useful if you want to show a tag cloud which weights taxons
by the number of times that they are referenced.

Note that if you implement a custom taxon document you must implement both
`getReferrerCount` and `setReferrerCount` and store the field as a `Long`.

The taxon referrer count for each taxon is updated whenever a taxon is
associated or disassociated with a document. If for some reason this data
becomes corrupted you can launch the following command to reinitialize this
data:

````bash
$ php app/console phpcr-taxonomy:update-referrer-count
````
