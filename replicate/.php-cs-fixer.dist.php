<?php

$finder = PhpCsFixer\Finder::create()
    ->exclude('var')
    ->exclude('vendor')
    ->exclude('node_modules')
    ->in(__DIR__);

$config = new PhpCsFixer\Config();
return $config->setRules([
        '@PhpCsFixer' => true,
        '@Symfony' => true,
        '@PER' => true,
        '@DoctrineAnnotation' => true,
		'php_unit_test_class_requires_covers' => false,
		'php_unit_internal_class' => false,
		'heredoc_to_nowdoc' => false,
		'escape_implicit_backslashes' => false,
		'explicit_indirect_variable' => false,
		'explicit_string_variable' => false,
		'combine_consecutive_issets' => false,
		'combine_consecutive_unsets' => false,
		'doctrine_annotation_array_assignment' => false,
        'yoda_style' => ['equal' => false, 'identical' => false],
        'increment_style' => ['style' => 'post'],
        'concat_space' => false,
		'single_quote' => false,
    ])
    ->setFinder($finder);
