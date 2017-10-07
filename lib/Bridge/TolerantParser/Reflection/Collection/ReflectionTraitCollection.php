<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\Collection;

use Phpactor\WorseReflection\Core\ServiceLocator;
use Microsoft\PhpParser\Node\Statement\ClassDeclaration;
use Phpactor\WorseReflection\Core\ClassName;
use Microsoft\PhpParser\Node\TraitUseClause;
use Phpactor\WorseReflection\Bridge\TolerantParser\Patch\TolerantQualifiedNameResolver;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\Collection\AbstractReflectionCollection;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionTraitCollection as CoreReflectionTraitCollection;

/**
 * @method \Phpactor\WorseReflection\Core\Reflection\ReflectionTrait get()
 */
class ReflectionTraitCollection extends AbstractReflectionCollection implements CoreReflectionTraitCollection
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

            $items[] = $serviceLocator->reflector()->reflectTrait(ClassName::fromString($traitName));
        }

        return new static($serviceLocator, $items);
    }
}
