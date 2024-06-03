#!/usr/bin/env php
<?php

use Symfony\Component\Filesystem\Filesystem;

require __DIR__ . '/../vendor/autoload.php';

$fs = new Filesystem();
$fs->remove('src');
$fs->remove('tests');
$fs->mirror('../phpactor/lib/WorseReflection', 'src');
$fs->mirror('src/Tests', 'tests');
