<?php

namespace Phpactor\WorseReflection\Core\Reflection;

use Phpactor\WorseReflection\Core\Docblock;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionPropertyCollection;

interface ReflectionTrait extends ReflectionClassLike
{
    public function docblock(): Docblock;

    public function properties(): ReflectionPropertyCollection;
}
