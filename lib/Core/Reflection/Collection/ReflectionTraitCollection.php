<?php

namespace Phpactor\WorseReflection\Core\Reflection\Collection;

use Phpactor\WorseReflection\Core\ServiceLocator;
use Phpactor\WorseReflection\Core\Reflection\ReflectionProperty;
use Microsoft\PhpParser\Node\Statement\ClassDeclaration;
use Microsoft\PhpParser\Node\PropertyDeclaration;
use Microsoft\PhpParser\Node\Expression\Variable;
use Microsoft\PhpParser\Node\QualifiedName;
use Phpactor\WorseReflection\Core\ClassName;
use Microsoft\PhpParser\Node\Statement\TraitDeclaration;
use Microsoft\PhpParser\Node\TraitUseClause;
use Phpactor\WorseReflection\Core\Tolerant\TolerantQualifiedNameResolver;
use Phpactor\WorseReflection\Core\Reflection\Collection\AbstractReflectionCollection;

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
                $traitName = TolerantQualifiedNameResolver::getResolvedName($traitName);
            }

            $items[] = $serviceLocator->reflector()->reflectClass(ClassName::fromString($traitName));
        }

        return new static($serviceLocator, $items);
    }
}
