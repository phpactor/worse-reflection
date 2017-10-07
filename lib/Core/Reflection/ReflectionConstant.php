<?php

namespace Phpactor\WorseReflection\Core\Reflection;

use Phpactor\WorseReflection\Core\Position;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\AbstractReflectionClass;
use Phpactor\WorseReflection\Core\Type;

interface ReflectionConstant
{
    public function position(): Position;

    public function declaringClass(): AbstractReflectionClass;

    public function class(): AbstractReflectionClass;

    public function name();

    public function type(): Type;
}