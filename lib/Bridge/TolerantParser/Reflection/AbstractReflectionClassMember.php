<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\Reflection;

use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Core\ServiceLocator;
use Microsoft\PhpParser\ClassLike;

use Phpactor\WorseReflection\Core\Reflection\ReflectionClassLike;

abstract class AbstractReflectionClassMember extends AbstractReflectedNode
{
    public function declaringClass(): ReflectionClassLike
    {
        $class = $this->node()->getFirstAncestor(ClassLike::class)->getNamespacedName();

        if (null === $class) {
            throw new \InvalidArgumentException(sprintf(
                'Could not locate class-like ancestor node for member "%s"',
                $this->name()
            ));
        }

        return $this->serviceLocator()->reflector()->reflectClassLike(ClassName::fromString($class));
    }

    abstract protected function serviceLocator(): ServiceLocator;

    abstract protected function name(): string;
}
