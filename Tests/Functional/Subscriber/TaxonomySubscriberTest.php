<?php

namespace DTL\PhpcrTaxonomyBundle\Tests\Functional\Subscriber;

use Symfony\Cmf\Component\Testing\Functional\BaseTestCase;
use DTL\PhpcrTaxonomyBundle\Document\Taxon;

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
        $one = $this->dm->find(null, '/test/taxons/one');
        $two = $this->dm->find(null, '/test/taxons/two');
        $this->assertNotNull($one);
        $this->assertNotNull($two);
    }

    public function testChangeTaxons()
    {
        $post = $this->dm->find(null, '/test/Post 1');
        $this->assertNotNull($post);

        $post->setTitle('asd');
        $post->setTags(array(
            new Taxon('one'),
            new Taxon('two'),
        ));

        $this->dm->persist($post);
        $this->dm->flush();
    }
}
