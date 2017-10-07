<?php

namespace Phpactor\WorseReflection\Core\Reflection;

use Phpactor\WorseReflection\Core\Position;
use string;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\DefaultValue;

interface ReflectionParameter
{
    public function position(): Position;

    public function name(): string;

    public function type(): Type;

    public function default(): DefaultValue;
}