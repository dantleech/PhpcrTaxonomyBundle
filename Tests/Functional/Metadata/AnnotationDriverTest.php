<?php

namespace Functional\Metadata;

use Symfony\Cmf\Component\Testing\Functional\BaseTestCase;

class AnnotationDriverTest extends BaseTestCase
{
    public function setUp()
    {
        $this->driver = $this->getContainer()->get('dtl_phpcr_taxonomy.metadata.factory');
    }

    public function testMetadata()
    {
        $meta = $this->driver->getMetadataForClass('DTL\PhpcrTaxonomyBundle\Tests\Resources\Document\Post')
            ->getOutsideClassMetadata();

        // taxonsFields
        $taxonsField = $meta->getTaxonsField();

        $this->assertNotNull($taxonsField);
        $this->assertEquals(array(
            'name' => 'tags',
            'path' => '/test/taxons',
            'taxonClass' => 'DTL\PhpcrTaxonomyBundle\Document\Taxon',
        ), array(
            'name' => $taxonsField->name,
            'path' => $taxonsField->getPath(),
            'taxonClass' => $taxonsField->getTaxonClass(),
        ));

        // taxonObjectsFields
        $taxonObjectsField = $meta->getTaxonObjectsField();

        $this->assertNotNull(1, $taxonObjectsField);
        $this->assertEquals('tagObjects', $taxonObjectsField->name);
    }
}
