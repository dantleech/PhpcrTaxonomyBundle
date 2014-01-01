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
            Event::prePersist,
        );
    }

    protected function getTmdf()
    {
        return $this->container->get('dtl_phpcr_taxonomy.metadata.factory');
    }


    public function loadClassMetadata(LoadClassMetadataEventArgs $args)
    {
        $odmMeta = $args->getClassMetadata();
        $taxMeta = $this->getTmdf()->getMetadataForClass($odmMeta->name);

        if (null !== $taxMeta) {
            $taxMeta = $taxMeta->getOutsideClassMetadata();

            foreach ($taxMeta->propertyMetadata as $propertyMetadata) {
                if ($propertyMetadata instanceof TaxonsMetadata) {
                    $odmMeta->mapManyToMany(array(
                        'fieldName' => $propertyMetadata->name,
                        'cascade' => ClassMetadata::CASCADE_ALL,
                        'sourceDocument' => $odmMeta->name,
                        'targetDocument' => $propertyMetadata->getTaxonClass(),
                    ));
                }
            }
        }
    }

    public function prePersist(LifecycleEventArgs $args)
    {
        $doc = $args->getObject();
        $taxMeta = $this->getTmdf()->getMetadataForClass(ClassUtils::getRealClass(get_class($doc)));
        $dm = $args->getObjectManager();

        if ($taxMeta) {
            $taxMeta = $taxMeta->getOutsideClassMetadata();
            foreach ($taxMeta->propertyMetadata as $propertyMetadata) {
                if ($propertyMetadata instanceof TaxonsMetadata) {
                    $taxons = $propertyMetadata->getValue($doc);
                    $parentPath = $propertyMetadata->getPath();
                    $parent = $dm->find(null, $parentPath);

                    if (null === $parent) {
                        throw new \RuntimeException(sprintf(
                            'Parent taxon path "%s" does not exist in content repository.', $parentPath
                        ));
                    }

                    foreach ($taxons as $taxon) {
                        $taxon->setParent($parent);
                    }
                }
            }
        }
    }
}
