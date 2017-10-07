<?php

namespace Phpactor\WorseReflection\Core\Reflection;

use Phpactor\WorseReflection\Core\Position;
use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Core\SourceCode;
use Phpactor\WorseReflection\Core\Docblock;

interface ReflectionTrait extends ReflectionClassLike
{
    public function docblock(): Docblock;

    public function properties(): ReflectionClassLike;
}
