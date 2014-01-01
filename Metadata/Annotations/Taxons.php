<?php

namespace DTL\PhpcrTaxonomyBundle\Metadata\Annotations;

/**
 * @Annotation
 * @Target({"PROPERTY"})
 */
class Taxons
{
    public $path;

    public $taxonClass;
}

