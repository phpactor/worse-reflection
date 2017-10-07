<?php

namespace Phpactor\WorseReflection\Core\Reflection;

use Phpactor\WorseReflection\Core\Position;
use Phpactor\WorseReflection\Core\Type;

interface ReflectionConstant
{
    public function position(): Position;

    public function declaringClass(): ReflectionClassLike;

    public function class(): AbstractReflectionClass;

    public function name();

    public function type(): Type;
}
