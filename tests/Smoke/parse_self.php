<?php

use Phpactor\WorseReflection\Bridge\Composer\ComposerSourceLocator;
use Phpactor\WorseReflection\Core\Exception\NotFound;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClass;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMethod;
use Phpactor\WorseReflection\ReflectorBuilder;

$autoload = require __DIR__ . '/../../vendor/autoload.php';
$path = __DIR__ . '/../..';

$reflector = ReflectorBuilder::create()
    ->addLocator(new ComposerSourceLocator($autoload))
    ->build();

$files = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS),
    RecursiveIteratorIterator::SELF_FIRST
);
$files  = new RegexIterator($files, '{.*\.php}');
$exceptions = [];

/** @var SplFileInfo $file */
foreach ($files as $file) {
    try {
        $classes = $reflector->reflectClassesIn(file_get_contents($file->getPathname()));
    /** @var ReflectionClass $class */
    foreach ($classes as $class) {
        /** @var ReflectionMethod $method */
        foreach ($class->methods() as $method) {
            echo '.';
            $method->frame();
        }
    }
    } catch (NotFound $e) {
        echo 'ERROR! : ' . $e->getMessage();
        $exceptions[] = $e;
    }
}

if ($exceptions) {
    foreach ($exception as $exception) {
        echo 'EXCEPTION: ' . $exception->getMessage();
    }
    die(255);
}
