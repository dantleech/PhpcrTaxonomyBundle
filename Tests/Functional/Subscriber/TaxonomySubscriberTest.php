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

        // assert taxon objects
        $post = $this->dm->find(null, '/test/Post 1');
        $taxonObjects = $post->getTagObjects();

        $this->assertNotNull($taxonObjects);
        $this->assertCount(2, $taxonObjects);
    }

    public function provideChangeTaxons()
    {
        // Post fixture has tags "one" and "two"
        return array(
            array(
                array('one', 'two', 'three', 'four'),
            ),
            array(
                array('one'),
            ),
            array(
                array(),
            ),
        );
    }

    /**
     * @dataProvider provideChangeTaxons
     */
    public function testChangeTaxons($taxonNames)
    {
        $post = $this->dm->find(null, '/test/Post 1');
        $this->assertNotNull($post);

        $post->setTitle('New Post Title');
        $post->setTags($taxonNames);

        $this->dm->persist($post);
        $this->dm->flush();

        foreach ($taxonNames as $taxonName) {
            $taxon = $this->dm->find(null, '/test/taxons/'.$taxonName);
            $this->assertNotNull($taxon);
        }

        $this->dm->refresh($post);
        $taxons = $post->getTagObjects();
        $this->assertNotNull($taxons);
        $this->assertCount(count($taxonNames), $taxons);
    }
}
