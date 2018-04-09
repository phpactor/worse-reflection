<?php

namespace Phpactor\WorseReflection\Core\Reflection;

use Phpactor\WorseReflection\Core\Position;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\DocBlock\DocBlock;
use Phpactor\WorseReflection\Core\Visibility;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\NodeText;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionParameterCollection;
use Phpactor\WorseReflection\Core\Types;

interface ReflectionMember
{
    public function position(): Position;

    public function declaringClass(): ReflectionClassLike;

    public function class(): ReflectionClassLike;

    public function name(): string;

    public function frame(): Frame;

    public function isAbstract(): bool;

    public function isStatic(): bool;

    public function docblock(): DocBlock;

    public function scope(): ReflectionScope;

    public function visibility(): Visibility;

    public function inferredReturnTypes(): Types;

    public function type(): Type;
}
