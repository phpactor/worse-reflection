<?php

namespace Phpactor\WorseReflection\Core\Reflection\Collection;

use Phpactor\WorseReflection\Core\ClassName;

interface ReflectionPropertyCollection extends ReflectionCollection
{
    public function byVisibilities(array $visibilities);

    public function belongingTo(ClassName $class);
}
