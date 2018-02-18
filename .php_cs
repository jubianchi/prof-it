<?php

use PhpCsFixer as CS;

$finder = CS\Finder::create()
    ->in(__DIR__.DIRECTORY_SEPARATOR.'examples')
    ->in(__DIR__.DIRECTORY_SEPARATOR.'src')
    ->in(__DIR__.DIRECTORY_SEPARATOR.'tests')
;

return CS\Config::create()
    ->setRules([
        '@Symfony' => true,
    ])
    ->setFinder($finder)
;