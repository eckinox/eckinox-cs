<?php

$finder = PhpCsFixer\Finder::create()
    ->exclude('var')
    ->exclude('vendor')
    ->exclude('node_modules')
    ->in(__DIR__);

$config = new PhpCsFixer\Config();
return $config->setRules([
        '@PhpCsFixer' => true,
        'yoda_style' => ['equal' => false, 'identical' => false],
        'increment_style' => ['style' => 'post'],
		'return_assignment' => false,
		'heredoc_to_nowdoc' => false,
		'php_unit_internal_class' => false,
		'php_unit_test_class_requires_covers' => false,
		'multiline_whitespace_before_semicolons' => false,
		'method_argument_space' => false,
		'single_quote' => false,
		'escape_implicit_backslashes' => false,
		'explicit_indirect_variable' => false,
		'method_chaining_indentation' => false,
    ])
    ->setIndent("\t")
    ->setFinder($finder);
