<?php

namespace Phpactor\WorseReflection\Core\Reflection;

use Phpactor\WorseReflection\Core\Position;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Docblock;
use Phpactor\WorseReflection\Core\Visibility;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\NodeText;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionParameterCollection;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClassLike;

interface ReflectionMethod
{
    public function position(): Position;

    public function declaringClass(): ReflectionClassLike;

    public function class(): ReflectionClassLike;

    public function name(): string;

    public function frame(): Frame;

    public function isAbstract(): bool;

    public function isStatic(): bool;

    public function parameters(): ReflectionParameterCollection;

    public function docblock(): Docblock;

    public function visibility(): Visibility;

    /**
     * If type not explicitly set, try and infer it from the docblock.
     */
    public function inferredReturnType(): Type;

    public function returnType(): Type;

    public function body(): NodeText;
}
