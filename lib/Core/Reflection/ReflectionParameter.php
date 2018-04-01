<?php

namespace Phpactor\WorseReflection\Core\Reflection;

use Phpactor\WorseReflection\Core\Position;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\DefaultValue;
use Phpactor\WorseReflection\Core\Types;

interface ReflectionParameter
{
    public function position(): Position;

    public function name(): string;

    public function method(): ReflectionMethod;

    public function type(): Type;

    public function inferredTypes(): Types;

    public function default(): DefaultValue;

    public function byReference(): bool;
}
