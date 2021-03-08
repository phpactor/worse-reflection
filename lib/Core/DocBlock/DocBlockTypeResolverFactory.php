<?php

namespace Phpactor\WorseReflection\Core\DocBlock;

use Phpactor\WorseReflection\Core\Reflection\ReflectionClassLike;
use Phpactor\WorseReflection\Core\Reflection\ReflectionScope;

interface DocBlockTypeResolverFactory
{
    public function create(ReflectionClassLike $scope, string $docblock): DocBlockTypeResolver;
}
