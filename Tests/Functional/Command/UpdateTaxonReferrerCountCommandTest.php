<?php

namespace DTL\PhpcrTaxonomyBundle\Tests\Functional\Command;

use Symfony\Cmf\Component\Testing\Functional\BaseTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use DTL\PhpcrTaxonomyBundle\Command\UpdateTaxonReferrerCountCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\NullOutput;

class UpdateTaxonReferrerCountCommandTest extends BaseTestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->db('PHPCR')->loadFixtures(array(
            'DTL\PhpcrTaxonomyBundle\Tests\Resources\DataFixtures\PHPCR\LoadPostData'
        ));

        $this->dm = $this->getContainer()->get('doctrine_phpcr')->getManager();
        $repo = $this->dm->getRepository('DTL\PhpcrTaxonomyBundle\Document\Taxon');
        $taxons = $repo->findAll();
        foreach ($taxons as $taxon) {
            $node = $this->dm->getNodeForDocument($taxon);
            $node->setProperty('referrerCount', 0);
        }
        $this->dm->getPhpcrSession()->save();

        // reset referrer count
        $application = new Application(static::$kernel);
        $this->command = new UpdateTaxonReferrerCountCommand();
        $this->command->setApplication($application);

    }

    public function testCommand()
    {
        $input = new ArrayInput(array(
            'foo:bar'
        ));
        // sf 2.4+ $output = new BufferedOutput();
        $output = new NullOutput();
        $this->command->run($input, $output);

        $taxon = $this->dm->find(null, '/test/taxons/one');
        $this->assertNotNull($taxon);
        $this->assertEquals(1, $taxon->getReferrerCount());
        $taxon = $this->dm->find(null, '/test/taxons/two');
        $this->assertNotNull($taxon);
        $this->assertEquals(1, $taxon->getReferrerCount());
    }
}
