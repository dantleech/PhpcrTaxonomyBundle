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

    public function updatePost($title, array $tags)
    {
        $post = $this->dm->find(null, '/test/' . $title);
        $post->setTags($tags);
        $this->dm->persist($post);
        $this->dm->flush();
    }

    public function deletePost($title)
    {
        $post = $this->dm->find(null, '/test/'.$title);
        $this->dm->remove($post);
        $this->dm->flush();
    }

    public function provideTagCount()
    {
        return array(
            array(
                // create
                array(
                    array('P1', array('chips', 'cheese', 'peas')),
                    array('P2', array('chips', 'cheese', 'peas')),
                    array('P3', array('chips', 'cheese', 'peas')),
                ),
                array(
                    'chips' => 3,
                    'cheese' => 3,
                    'peas' => 3,
                ),
                // update
                array(
                ),
                array(
                ),
                // delete
                array(
                    'P1'
                ),
                array(
                    'chips' => 2,
                    'cheese' => 2,
                    'peas' => 2,
                ),
            ),
            array(
                // create
                array(
                    array('P1', array('chips', 'cheese', 'peas')),
                    array('P2', array('cheese')),
                    array('P3', array('peas')),
                ),
                array(
                    'chips' => 1,
                    'cheese' => 2,
                    'peas' => 2,
                ),
                // update
                array(
                    array('P1', array('chips')),
                ),
                array(
                    'chips' => 1,
                    'cheese' => 1,
                    'peas' => 1,
                ),
                // deelete
                array(
                    'P1',
                    'P2',
                ),
                array(
                    'chips' => 0,
                    'cheese' => 0,
                    'peas' => 1,
                ),
            ),
            array(
                // create
                array(
                    array('P1', array('chips', 'cheese', 'peas')),
                    array('P2', array('cheese')),
                    array('P3', array('peas')),
                ),
                array(
                    'chips' => 1,
                    'cheese' => 2,
                    'peas' => 2,
                ),
                // update
                array(
                    array('P1', array()),
                    array('P2', array()),
                    array('P3', array()),
                ),
                array(
                    'chips' => 0,
                    'cheese' => 0,
                    'peas' => 0,
                ),
                // delete
                array(
                    'P1',
                    'P2',
                    'P3',
                ),
                array(
                    'chips' => 0,
                    'cheese' => 0,
                    'peas' => 0,
                ),
            )
        );
    }


    /**
     * @dataProvider provideTagCount
     */
    public function testTagCount($postData, $assertions, $updateData, $updateAssertions, $deleteData, $deleteAssertions)
    {
        foreach ($postData as $postDatum) {
            $this->createPost($postDatum[0], $postDatum[1]);
        }

        foreach ($assertions as $tag => $count) {
            $tag = $this->dm->find(null, '/test/taxons/'.$tag);
            $this->assertEquals($count, $tag->getReferrerCount(), 'Refferer count after create');
        }

        foreach ($updateData as $updateDatum) {
            $this->updatePost($updateDatum[0], $updateDatum[1]);
        }

        foreach ($updateAssertions as $tagName => $count) {
            $tag = $this->dm->find(null, '/test/taxons/'.$tagName);
            $this->assertEquals($count, $tag->getReferrerCount(), 'Referrer count for "' . $tagName . '" after update');
        }

        foreach ($deleteData as $title) {
            $this->deletePost($title);
        }
        foreach ($deleteAssertions as $tagName => $count) {
            $tag = $this->dm->find(null, '/test/taxons/'.$tagName);
            $this->assertEquals($count, $tag->getReferrerCount(), 'Referrer count for "' . $tagName . '" after delete');
        }
    }
}
