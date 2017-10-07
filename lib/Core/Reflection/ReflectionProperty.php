<?php

namespace Phpactor\WorseReflection\Core\Reflection;

use Phpactor\WorseReflection\Core\Position;
use Phpactor\WorseReflection\Core\Visibility;
use Phpactor\WorseReflection\Core\Type;

interface ReflectionProperty
{
    public function position(): Position;

    public function declaringClass(): ReflectionClassLike;

    public function class(): AbstractReflectionClass;

    public function name(): string;

    public function visibility(): Visibility;

    public function type(): Type;

    public function isStatic(): bool;
}
