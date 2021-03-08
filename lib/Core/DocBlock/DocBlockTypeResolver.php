<?php

namespace Phpactor\WorseReflection\Core\DocBlock;

use Phpactor\WorseReflection\Core\Placeholders;
use Phpactor\WorseReflection\Core\Reflection\ReflectionType;

interface DocBlockTypeResolver
{
    public function resolveReturn(): ReflectionType;

    public function placeholders(): Placeholders;
}
