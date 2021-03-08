<?php

namespace Phpactor\WorseReflection\Core\DocBlock;

use Phpactor\WorseReflection\Core\Reflection\ReflectionScope;

interface DocBlockTypeResolverFactory
{
    public function create(ReflectionScope $scope, string $docblock): DocBlockTypeResolver;
}
