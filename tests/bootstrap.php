<?php
if (!is_file($autoloadFile = __DIR__.'/../vendor/autoload.php')) {
    throw new \LogicException('Could not find autoload.php in vendor/. Did you run "composer install --dev"?');
}

$loader = require $autoloadFile;

Doctrine\Common\Annotations\AnnotationRegistry::registerLoader(array($loader, 'loadClass'));
