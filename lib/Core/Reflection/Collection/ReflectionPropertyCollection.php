<?php

namespace Phpactor\WorseReflection\Core\Reflection\Collection;

use Phpactor\WorseReflection\Core\ClassName;

/**
 * @method \Phpactor\WorseReflection\Core\Reflection\ReflectionProperty first()
 * @method \Phpactor\WorseReflection\Core\Reflection\ReflectionProperty last()
 * @method \Phpactor\WorseReflection\Core\Reflection\ReflectionProperty get()
 */
interface ReflectionPropertyCollection extends ReflectionCollection
{
    public function byVisibilities(array $visibilities);

    public function belongingTo(ClassName $class);
}
