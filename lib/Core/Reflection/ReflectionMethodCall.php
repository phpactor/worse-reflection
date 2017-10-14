<?php

namespace Phpactor\WorseReflection\Core\Reflection;

use Phpactor\WorseReflection\Core\Position;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Docblock;
use Phpactor\WorseReflection\Core\Visibility;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\NodeText;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionParameterCollection;

interface ReflectionMethodCall
{
    public function position(): Position;

    public function class(): ReflectionClassLike;

    public function name(): string;

    public function isStatic(): bool;

    public function parameters(): ReflectionParameterCollection;

    public function visibility(): Visibility;

    public function inferredReturnType(): Type;
}
