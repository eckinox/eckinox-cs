<?php

require dirname(__DIR__).'/vendor/autoload.php';

$classLoader = new \Composer\Autoload\ClassLoader();
$classLoader->addPsr4("Eckinox\\CodingStandards\\", __DIR__ . "/../src/", true);
$classLoader->register();
