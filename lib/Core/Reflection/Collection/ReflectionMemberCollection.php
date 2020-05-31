<?php

namespace Phpactor\WorseReflection\Core\Reflection\Collection;

use Phpactor\WorseReflection\Core\ClassName;

/**
 * @method \Phpactor\WorseReflection\Core\Reflection\ReflectionMember first()
 * @method \Phpactor\WorseReflection\Core\Reflection\ReflectionMember last()
 * @method \Phpactor\WorseReflection\Core\Reflection\ReflectionMember get(string $name)
 */
interface ReflectionMemberCollection extends ReflectionCollection
{
    /**
     * By member type: constant, method, or property
     */
    public function byMemberType(string $type): ReflectionMemberCollection;

    public function byVisibilities(array $visibilities): ReflectionMemberCollection;

    public function belongingTo(ClassName $class): ReflectionMemberCollection;

    public function atOffset(int $offset): ReflectionMemberCollection;

    public function byName(string $name): ReflectionMemberCollection;

    public function virtual(): ReflectionMemberCollection;

    public function real(): ReflectionMemberCollection;

    public function methods(): ReflectionMethodCollection;

    public function properties(): ReflectionPropertyCollection;
}
