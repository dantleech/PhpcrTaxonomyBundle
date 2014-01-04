<?php

namespace DTL\PhpcrTaxonomyBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use DTL\PhpcrTaxonomyBundle\Document\TaxonInterface;
use Symfony\Component\Console\Input\InputOption;

class UpdateTaxonReferrerCountCommand extends ContainerAwareCommand
{
    protected $dm;
    protected $output;
    protected $dryRun;

    public function configure()
    {
        $this->setName('phpcr-taxonomy:update-referrer-count');
        $this->addOption('dry-run', null, InputOption::VALUE_NONE, 'Do not make any changes to the database');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->dm = $this->getContainer()->get('doctrine_phpcr')->getManager();
        $this->output = $output;
        $this->dryRun = $input->getOption('dry-run');

        $taxMetadataFactory = $this->getContainer()->get('dtl_phpcr_taxonomy.metadata.factory');
        $allOdmMetadata = $this->dm->getMetadataFactory()->getAllMetadata();

        foreach ($allOdmMetadata as $odmMetadata) {
            $refl = $odmMetadata->getReflectionClass();

            if ($refl->implementsInterface('DTL\PhpcrTaxonomyBundle\Document\TaxonInterface')) {
                $taxons = $this->dm->getRepository($odmMetadata->name)->findAll();

                foreach ($taxons as $taxon) {
                    $this->updateReferrerCount($taxon);
                }
            }
        }

        if (false === $this->dryRun) {
            $this->dm->flush();
        }

        $output->writeln('<info>Done</info>');
        return 0;
    }

    protected function updateReferrerCount(TaxonInterface $taxon)
    {
        $referrerCount = count($this->dm->getReferrers($taxon));

        if ($referrerCount != $taxon->getReferrerCount()) {
            $taxon->setReferrerCount($referrerCount);
            $this->dm->persist($taxon);

            $this->output->writeln(sprintf(
                '%s<info>Fixing taxon </info>%s<info> of class </info>%s<info> setting referrer count to </info>%s',
                true === $this->dryRun ? '<comment>DRY RUN</comment> ' : '',
                $taxon->getName(), get_class($taxon), $referrerCount
            ));
        }
    }
}
