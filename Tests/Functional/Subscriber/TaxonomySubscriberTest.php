<?php

namespace DTL\PhpcrTaxonomyBundle\Tests\Functional\Subscriber;

use Symfony\Cmf\Component\Testing\Functional\BaseTestCase;
use DTL\PhpcrTaxonomyBundle\Document\Taxon;
use DTL\PhpcrTaxonomyBundle\Tests\Resources\Document\Post;

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

    public function createPost($title, array $tags)
    {
        $parent = $this->dm->find(null, '/test');
        $post = new Post();
        $post->setParent($parent);
        $post->setTitle($title);
        $post->setTags($tags);
        $this->dm->persist($post);
        $this->dm->flush();
    }

    public function deletePost($title)
    {
        $post = $this->dm->find('/test/'.$title);
        $this->dm->remove($post);
        $this->dm->flush();
    }

    public function provideTagCount()
    {
        return array(
            array(
                array(
                    array('P1', array('one', 'two', 'three')),
                    array('P2', array('one', 'two', 'three')),
                    array('P3', array('one', 'two', 'three')),
                ),
                array(
                    'one' => 3,
                    'two' => 3,
                    'three' => 3,
                ),
                array(
                    'Post 1'
                ),
                array(
                    'one' => 2,
                    'two' => 2,
                    'three' => 2,
                ),
            )
        );
    }


    /**
     * @dataProvider provideTagCount
     */
    public function testTagCount($postData, $assertions, $deleteData, $deleteAssertions)
    {
        foreach ($postData as $postDatum) {
            $this->createPost($postDatum[0], $postDatum[1]);
        }

        foreach ($assertions as $tag => $count) {
            $tag = $this->dm->find(null, '/test/taxons/'.$tag);
            $this->assertEquals($count, $tag->getReferrerCount());
        }

        foreach ($deleteData as $title) {
            $this->deletePost($title);
        }
        foreach ($deleteAssertions as $tag => $count) {
            $tag = $this->dm->find('/test/taxons/'.$tag);
            $this->assertEquals($count, $tag->getReferrerCount());
        }
    }
}
