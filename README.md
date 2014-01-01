PHPCR Taxonomy Bundle
=====================

**NOTE::** This is a work in progress.

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
}
````

Tags can be added as follows:

````php
$post = new Post();
$post->setTags(array(
    new Taxon('one'),
    new Taxon('two'),
));
````

The parent document for each tag is determined by the `path` property of the
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
