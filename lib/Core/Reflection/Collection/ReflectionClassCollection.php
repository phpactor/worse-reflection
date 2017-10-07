<?php

namespace Phpactor\WorseReflection\Core\Reflection\Collection;

use Phpactor\WorseReflection\Core\ServiceLocator;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\Collection\AbstractReflectionCollection;
use Phpactor\WorseReflection\Core\SourceCode;

interface ReflectionClassCollection extends ReflectionCollection
{
    public function concrete();
}
