<?php

$ruleset = new TwigCsFixer\Ruleset\Ruleset();
$ruleset->addStandard(new TwigCsFixer\Standard\TwigCsFixer());
$ruleset->removeRule(TwigCsFixer\Rules\Whitespace\BlankEOFRule::class);
$ruleset->removeRule(TwigCsFixer\Rules\String\SingleQuoteRule::class);

$config = new TwigCsFixer\Config\Config();
$config->allowNonFixableRules();
$config->setRuleset($ruleset);

return $config;
