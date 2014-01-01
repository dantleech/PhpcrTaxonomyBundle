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
        $taxonsFields = $meta->getTaxonsFields();

        $this->assertCount(1, $taxonsFields);
        $taxonsField = current($taxonsFields);
        $this->assertEquals(array(
            'name' => 'tags',
            'path' => '/test/taxons',
            'taxonClass' => 'DTL\PhpcrTaxonomyBundle\Document\Taxon',
        ), array(
            'name' => $taxonsField->name,
            'path' => $taxonsField->getPath(),
            'taxonClass' => $taxonsField->getTaxonClass(),
        ));
    }
}
