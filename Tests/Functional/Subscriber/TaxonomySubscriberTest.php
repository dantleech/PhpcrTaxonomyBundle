<?php

namespace DTL\PhpcrTaxonomyBundle\Tests\Functional\Subscriber;

use Symfony\Cmf\Component\Testing\Functional\BaseTestCase;

class TaxonomySubscriberTest extends BaseTestCase
{
    public function setUp()
    {
        $this->db('PHPCR')->loadFixtures(array(
            'DTL\PhpcrTaxonomyBundle\Tests\Resources\DataFixtures\PHPCR\LoadPostData'
        ));

        $this->dm = $this->getContainer()->get('doctrine_phpcr')->getManager();
    }

    public function testTaxons()
    {
        // assert that the taxons have been persisted
    }
}
