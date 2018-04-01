<?php

namespace Phpactor\WorseReflection\Core\Reflection\Collection;

use Phpactor\WorseReflection\Core\ClassName;

/**
 * @method ReflectionMethod first()
 * @method ReflectionMethod last()
 * @method ReflectionMethod get()
 */
interface ReflectionMethodCollection extends ReflectionCollection
{
    public function byVisibilities(array $visibilities);

    public function belongingTo(ClassName $class);

    public function atOffset(int $offset);
}
