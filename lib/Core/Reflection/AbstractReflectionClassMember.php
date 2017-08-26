<?php

namespace Phpactor\WorseReflection\Core\Reflection;

use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionMethodCollection;
use Phpactor\WorseReflection\Core\Docblock;
use Microsoft\PhpParser\Node\Statement\ClassDeclaration;
use Microsoft\PhpParser\Node\Statement\InterfaceDeclaration;
use Microsoft\PhpParser\Node\Statement\TraitDeclaration;
use Phpactor\WorseReflection\Core\ServiceLocator;

abstract class AbstractReflectionClassMember extends AbstractReflectedNode
{
    public function declaringClass(): AbstractReflectionClass
    {
        $class = $this->node()->getFirstAncestor(ClassDeclaration::class, InterfaceDeclaration::class, TraitDeclaration::class)->getNamespacedName();

        if (null === $class) {
            throw new \InvalidArgumentException(sprintf(
                'Could not locate class-like ancestor node for member "%s"',
                $this->name()
            ));
        }

        return $this->serviceLocator()->reflector()->reflectClass(ClassName::fromString($class));
    }

    abstract public function class(): AbstractReflectionClass;

    abstract protected function serviceLocator(): ServiceLocator;
}
