<?php

$loader->import(CMF_TEST_CONFIG_DIR.'/default.php');
$container->setParameter('cmf_testing.bundle_fqn', 'DTL\PhpcrTaxonomyBundle');
$loader->import(CMF_TEST_CONFIG_DIR.'/phpcr_odm.php');
