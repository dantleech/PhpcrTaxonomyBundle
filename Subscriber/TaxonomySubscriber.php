<?php

namespace DTL\PhpcrTaxonomyBundle\Subscriber;

use Doctrine\ODM\PHPCR\Event\OnFlushEventArgs;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Cmf\Bundle\TagBundle\Document\Tag;
use Doctrine\Common\Persistence\Event\LoadClassMetadataEventArgs;
use Doctrine\Common\EventSubscriber;
use Doctrine\ODM\PHPCR\Event;
use Doctrine\Common\Persistence\Event\ManagerEventArgs;
use Doctrine\ODM\PHPCR\Mapping\ClassMetadata;
use Doctrine\Common\Util\ClassUtils;
use DTL\PhpcrTaxonomyBundle\Metadata\Property\TaxonsMetadata;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use DTL\PhpcrTaxonomyBundle\Document\Taxon;
use DTL\PhpcrTaxonomyBundle\Metadata\Property\TaxonObjectsMetadata;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Doctrine PHPCR ODM listener for automatically managing
 * Document taxons.
 *
 * @author Daniel Leech <daniel@dantleech.com>
 */
class TaxonomySubscriber implements EventSubscriber
{
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getSubscribedEvents()
    {
        return array(
            Event::loadClassMetadata,
            Event::onFlush,
        );
    }

    protected function getTmdf()
    {
        return $this->container->get('dtl_phpcr_taxonomy.metadata.factory');
    }

    protected function getTaxMeta($className)
    {
        $taxMeta = $this->getTmdf()->getMetadataForClass($className);

        if ($taxMeta) {
            $taxMeta = $taxMeta->getOutsideClassMetadata();
            return $taxMeta;
        }

        return null;
    }

    public function loadClassMetadata(LoadClassMetadataEventArgs $args)
    {
        $odmMeta = $args->getClassMetadata();
        $taxMeta = $this->getTaxMeta($odmMeta->name);

        if (null !== $taxMeta) {
            foreach ($taxMeta->propertyMetadata as $propertyMetadata) {
                if ($propertyMetadata instanceof TaxonsMetadata) {
                    $odmMeta->mapField(array(
                        'fieldName' => $propertyMetadata->name,
                        'type' => 'string',
                        'multivalue' => true,
                    ));

                    $taxonsProperty = $propertyMetadata;
                }
            }

            foreach ($taxMeta->propertyMetadata as $propertyMetadata) {
                if ($propertyMetadata instanceof TaxonObjectsMetadata) {
                    if (null === $taxonsProperty) {
                        throw new \RuntimeException(
                            'There must be a Taxons mapping before a TaxonObjects mapping'
                        );
                    }

                    $odmMeta->mapManyToMany(array(
                        'fieldName' => $propertyMetadata->name,
                        'sourceDocument' => $odmMeta->name,
                        'targetDocument' => $taxonsProperty->getTaxonClass(),
                    ));
                }
            }
        }
    }

    public function onFlush(ManagerEventArgs $args)
    {
        /** @var $dm DocumentManager */
        $dm = $args->getObjectManager();
        $uow = $dm->getUnitOfWork();

        $scheduledInserts = $uow->getScheduledInserts();
        $scheduledUpdates = $uow->getScheduledUpdates();
        $updates = array_merge($scheduledInserts, $scheduledUpdates);

        foreach ($updates as $document) {
            $realDocumentClass = ClassUtils::getRealClass(get_class($document));
            $taxMeta = $this->getTaxMeta($realDocumentClass);

            if (null !== $taxMeta) {
                foreach ($taxMeta->getTaxonsFields() as $taxonField) {
                    $taxonNames = $taxonField->getValue($document);

                    foreach ($taxonNames as $taxonName) {
                        $path = join('/', array($taxonField->getPath(), $taxonName));
                        $taxon = $dm->find(null, $path);
                        $taxonClass = $taxonField->getTaxonClass();

                        // if no taxon, create one
                        if (null === $taxon) {
                            $parentPath = $taxonField->getPath();
                            $parentDocument = $dm->find(null, $parentPath);

                            if (null === $parentDocument) {
                                throw new \InvalidArgumentException(sprintf(
                                    'Parent path "%s" for taxon field "%s" in class "%s" does not exist.',
                                    $parentPath, $taxonField->name, $realDocumentClass
                                ));
                            }

                            $taxon = new $taxonClass();
                            $taxon->setName($taxonName);
                            $taxon->setParent($parentDocument);
                        }

                        // validate taxon class
                        if (!$taxon instanceof $taxonClass) {
                            throw new \RuntimeException(sprintf(
                                'Expected taxon at path "%s" to be instance of "%s" but got an instance of "%s"',
                                $path, $taxonClass, get_class($taxon)
                            ));
                        }

                        // add taxon objects
                        $taxonObjectsFields = $taxMeta->getTaxonObjectsFields();

                        if (count($taxonObjectsFields) > 1) {
                            throw new \InvalidArgumentException(
                                'Multiple taxonomies for a single class not currently supported'
                            );
                        }

                        // rather pointless loop given the above exception, but we want to support
                        // this in the future probably.
                        foreach ($taxonObjectsFields as $taxonObjectField) {
                            $existingTaxons = $taxonObjectField->reflection->getValue($document);

                            $hasTaxon = false;
                            foreach ($existingTaxons as $existingTaxon) {
                                if ($existingTaxon === $taxon) {
                                    $hasTaxon = true;
                                }
                            }

                            if (false === $hasTaxon) {
                                $existingTaxons->add($taxon);
                            }
                        }

                        $dm->persist($taxon);
                    }
                }

                $dm->persist($document);
                $uow->computeChangeSets();
            }
        }

        //$removes = $uow->getScheduledRemovals();

        //foreach ($removes as $document) {
        //    if ($this->getArm()->isAutoRouteable($document)) {
        //        $referrers = $dm->getReferrers($document);
        //        $referrers = $referrers->filter(function ($referrer) {
        //            if ($referrer instanceof AutoRoute) {
        //                return true;
        //            }

        //            return false;
        //        });
        //        foreach ($referrers as $route) {
        //            $uow->scheduleRemove($route);
        //        }
        //    }
        //}
    }
}
