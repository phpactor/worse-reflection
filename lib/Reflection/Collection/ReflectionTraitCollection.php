<?php

namespace Phpactor\WorseReflection\Reflection\Collection;

use Phpactor\WorseReflection\ServiceLocator;
use Phpactor\WorseReflection\Reflection\ReflectionProperty;
use Microsoft\PhpParser\Node\Statement\ClassDeclaration;
use Microsoft\PhpParser\Node\PropertyDeclaration;
use Microsoft\PhpParser\Node\Expression\Variable;
use Microsoft\PhpParser\Node\QualifiedName;
use Phpactor\WorseReflection\ClassName;
use Microsoft\PhpParser\Node\Statement\TraitDeclaration;
use Microsoft\PhpParser\Node\TraitUseClause;

class ReflectionTraitCollection extends AbstractReflectionCollection
{
    public static function fromClassDeclaration(ServiceLocator $serviceLocator, ClassDeclaration $class)
    {
        $items = [];
        /** @var $memberDeclaration TraitUseClause */
        foreach ($class->classMembers->classMemberDeclarations as $memberDeclaration) {
            if (false === $memberDeclaration instanceof TraitUseClause) {
                continue;
            }

            foreach ($memberDeclaration->traitNameList->getValues() as $traitName) {
                $traitName = (string) $traitName->getNamespacedName();
            }
            $items[] = $serviceLocator->reflector()->reflectClass(ClassName::fromString($traitName));
        }

        return new static($serviceLocator, $items);
    }
}
