<?php

namespace Phpactor\WorseReflection\Core\Reflection\Collection;

interface ReflectionPropertyCollection extends ReflectionCollection
{
    public function byVisibilities(array $visibilities);
}
