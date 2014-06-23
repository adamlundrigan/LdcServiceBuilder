<?php

require __DIR__ . '/../vendor/autoload.php';

$file = $argv[1];
if ( ! file_exists($file) ) {
    die("ERROR: You must specify a configuration file!\n\nUsage: bin/builder.php <file>\n");
}

$config = include $file;
if ( ! is_array($config) ) {
    die("ERROR: You must specify a configuration file which returns a PHP array!\n\nUsage: bin/builder.php <file>\n");
}

try {
    $options = new LdcServiceBuilder\Options\BuilderOptions($config);
} catch ( Exception $e ) {
    die(sprintf("ERROR: Could not read configuration!\n(Message: %s)\n\nUsage: bin/builder.php <file>\n", $e->getMessage()));
}

$builder = new LdcServiceBuilder\Builder($options);
$builder->run();