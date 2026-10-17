<?php

// Load the host app's autoloader and register this package's test namespace.
$host = getenv('SM_HOST_APP') ?: dirname(__DIR__, 2) . '/app';
if (!is_file($host . '/vendor/autoload.php')) {
    fwrite(STDERR, "Host app not found at {$host}. Set SM_HOST_APP to the Laravel app that installs this package.\n");
    exit(1);
}
$loader = require $host . '/vendor/autoload.php';
$loader->addPsr4('ME\\SchoolManagement\\Tests\\', __DIR__);
define('SM_HOST_APP', $host);
