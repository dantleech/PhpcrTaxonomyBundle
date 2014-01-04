<?php

namespace DTL\PhpcrTaxonomyBundle\Metadata\Driver;

use Doctrine\Common\Annotations\AnnotationReader;
use Symfony\Cmf\Bundle\TreeUi\CoreBundle\Tree\Metadata\TreeMetadata;
use Metadata\Driver\AdvancedDriverInterface;
use DTL\PhpcrTaxonomyBundle\Metadata\ClassMetadata;

class AnnotationDriver implements AdvancedDriverInterface
{
    protected $reader;
    protected $annotatedClasses;

    /**
     * @param AnnotationReader $reader           - AnnorationReader implementation
     * @param array            $annotatedClasses - List of all known annotated classes 
     *     (determined in DI builder)
     */
    public function __construct(AnnotationReader $reader, $annotatedClasses = array())
    {
        $this->reader = $reader;
        $this->annotatedClasses = $annotatedClasses;
    }

    public function loadMetadataForClass(\ReflectionClass $class)
    {
        $meta = new ClassMetadata($class->name);
        foreach ($class->getProperties() as $property) {
            $taxonAnn = $this->reader->getPropertyAnnotation(
                $property,
                'DTL\PhpcrTaxonomyBundle\Metadata\Annotations\Taxons'
            );

            $taxonObjectsAnn = $this->reader->getPropertyAnnotation(
                $property,
                'DTL\PhpcrTaxonomyBundle\Metadata\Annotations\TaxonObjects'
            );

            if ($taxonAnn) {
                $meta->setTaxonsField(array(
                    'name' => $property->name,
                    'path' => $taxonAnn->path,
                ));
            }

            if ($taxonObjectsAnn) {
                $meta->setTaxonObjectsField(array(
                    'name' => $property->name,
                ));
            }
        }

        return $meta;
    }

    public function getAllClassNames()
    {
        return $this->annotatedClasses;
    }
}
