<?php

namespace Phpactor\WorseReflection\Core\Reflection\Collection;

use Phpactor\WorseReflection\Core\ClassName;

interface ReflectionMethodCollection extends ReflectionCollection
{
    public function byVisibilities(array $visibilities);

    public function belongingTo(ClassName $class);
}
