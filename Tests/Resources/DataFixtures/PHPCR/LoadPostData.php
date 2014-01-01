<?php

namespace DTL\PhpcrTaxonomyBundle\Tests\Resources\DataFixtures\PHPCR;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ODM\PHPCR\DocumentManager;
use DTL\PhpcrTaxonomyBundle\Document\Taxon;
use PHPCR\Util\NodeHelper;
use DTL\PhpcrTaxonomyBundle\Tests\Resources\Document\Post;

class LoadPostData implements FixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $phpcrSession = $manager->getPhpcrSession();
        NodeHelper::createPath($phpcrSession, '/test');
        NodeHelper::createPath($phpcrSession, '/test/taxons');
        $root = $manager->find(null, '/test');
        $post = new Post();
        $post->setParent($root);
        $post->setTitle('Post 1');
        $post->setTags(array('one', 'two'));
        $manager->persist($post);

        $manager->flush();
    }
}

