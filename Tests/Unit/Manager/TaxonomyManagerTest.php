<?php

namespace Unit\Manager;

use DTL\PhpcrTaxonomyBundle\Manager\TaxonomyManager;

class TaxonomyManagerTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $mdf = $this->getMockBuilder('Metadata\MetadataFactory')
            ->disableOriginalConstructor()->getMock();
        $this->tm = new TaxonomyManager($mdf);
    }

    public function testHasMetadata()
    {
    }
}
