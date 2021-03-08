<?php

namespace Phpactor\WorseReflection\Core\DocBlock;

use Phpactor\WorseReflection\Core\Reflection\ReflectionClassLike;
use Phpactor\WorseReflection\Core\Reflection\ReflectionNode;
use Phpactor\WorseReflection\Core\Reflection\ReflectionScope;

interface DocBlockTypeResolverFactory
{
    public function create(ReflectionNode $scope, string $docblock): DocBlockTypeResolver;
}
