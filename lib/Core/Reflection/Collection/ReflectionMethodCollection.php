<?php

namespace Phpactor\WorseReflection\Core\Reflection\Collection;

use Phpactor\WorseReflection\Core\ClassName;

/**
 * @method \Phpactor\WorseReflection\Core\Reflection\ReflectionMethod first()
 * @method \Phpactor\WorseReflection\Core\Reflection\ReflectionMethod last()
 * @method \Phpactor\WorseReflection\Core\Reflection\ReflectionMethod get(string $name)
 */
interface ReflectionMethodCollection extends ReflectionCollection
{
    public function byVisibilities(array $visibilities);

    public function belongingTo(ClassName $class);

    public function atOffset(int $offset);

    public function abstract();
}
