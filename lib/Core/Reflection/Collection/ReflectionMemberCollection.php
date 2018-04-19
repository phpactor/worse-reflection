<?php

namespace Phpactor\WorseReflection\Core\Reflection\Collection;

use Phpactor\WorseReflection\Core\ClassName;

/**
 * @method \Phpactor\WorseReflection\Core\Reflection\ReflectionMember first()
 * @method \Phpactor\WorseReflection\Core\Reflection\ReflectionMember last()
 * @method \Phpactor\WorseReflection\Core\Reflection\ReflectionMember get()
 */
interface ReflectionMemberCollection extends ReflectionCollection
{
    public function byVisibilities(array $visibilities): ReflectionMemberCollection;

    public function belongingTo(ClassName $class): ReflectionMemberCollection;

    public function atOffset(int $offset): ReflectionMemberCollection;
}
