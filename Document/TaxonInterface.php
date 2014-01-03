<?php

namespace DTL\PhpcrTaxonomyBundle\Document;

/**
 * Intreface to implement for Phpcr Taxons
 *
 * @author Daniel Leech <daniel@dantleech.com>
 */
interface TaxonInterface
{
    /**
     * Set the parent document
     *
     * @param object $document
     */
    public function setParent($document);

    /**
     * Set the name of the taxon
     *
     * @param string $name
     */
    public function setName($name);

    /**
     * Get the referrer count
     *
     * @return integer
     */
    public function getReferrerCount();

    /**
     * Set the referrer count
     *
     * NOTE: This is for internal use, you should never set this!
     *
     * @param integer $referrerCount
     */
    public function setReferrerCount($referrerCount);
}
