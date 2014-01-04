<?php

namespace DTL\PhpcrTaxonomyBundle\Tests\Functional\Command;

use Symfony\Cmf\Component\Testing\Functional\BaseTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use DTL\PhpcrTaxonomyBundle\Command\UpdateTaxonReferrerCountCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

class UpdateTaxonReferrerCountCommandTest extends BaseTestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->db('PHPCR')->loadFixtures(array(
            'DTL\PhpcrTaxonomyBundle\Tests\Resources\DataFixtures\PHPCR\LoadPostData'
        ));

        $dm = $this->getContainer()->get('doctrine_phpcr')->getManager();
        $repo = $dm->getRepository('DTL\PhpcrTaxonomyBundle\Document\Taxon');
        $taxons = $repo->findAll();
        foreach ($taxons as $taxon) {
            $node = $dm->getNodeForDocument($taxon);
            $node->setProperty('referrerCount', 0);
        }
        $dm->getPhpcrSession()->save();
        $dm->clear();

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
        $output = new BufferedOutput();
        $this->command->run($input, $output);

        $output = $output->fetch();
        $this->assertRegExp('&Fixing taxon one .* count to 1\n&', $output);
        $this->assertRegExp('&Fixing taxon two .* count to 1\n&', $output);
        $this->assertContains('Done', $output);
    }
}
