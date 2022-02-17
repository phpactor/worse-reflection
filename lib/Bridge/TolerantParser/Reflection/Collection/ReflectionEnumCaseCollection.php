<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\Collection;

use Microsoft\PhpParser\Node\ConstElement;
use Microsoft\PhpParser\Node\EnumCaseDeclaration;
use Microsoft\PhpParser\Node\Statement\EnumDeclaration;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\ReflectionEnum;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\ReflectionEnumCase as PhpactorReflectionEnumCase;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionEnumCaseCollection as PhpactorReflectionEnumCaseCollection;
use Phpactor\WorseReflection\Core\Reflection\ReflectionEnumCase;
use Phpactor\WorseReflection\Core\ServiceLocator;
use Microsoft\PhpParser\Node\Statement\ClassDeclaration;
use Microsoft\PhpParser\Node\Statement\InterfaceDeclaration;
use Microsoft\PhpParser\Node\ClassConstDeclaration;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\ReflectionClass;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\ReflectionInterface;

/**
 * @method \Phpactor\WorseReflection\Core\Reflection\ReflectionConstant get()
 * @method \Phpactor\WorseReflection\Core\Reflection\ReflectionConstant first()
 * @method \Phpactor\WorseReflection\Core\Reflection\ReflectionConstant last()
 */
class ReflectionEnumCaseCollection extends ReflectionMemberCollection implements PhpactorReflectionEnumCaseCollection
{
    public static function fromEnumDeclaration(ServiceLocator $serviceLocator, EnumDeclaration $enum, ReflectionEnum $reflectionEnum)
    {
        $items = [];
        foreach ($enum->enumMembers->enumMemberDeclarations as $member) {
            if (!$member instanceof EnumCaseDeclaration) {
                continue;
            }
            $enumCase = new PhpactorReflectionEnumCase($serviceLocator, $reflectionEnum, $member);
            $items[$enumCase->name()] = $enumCase;
        }

        return new static($serviceLocator, $items);
    }

    protected function collectionType(): string
    {
        return PhpactorReflectionEnumCaseCollection::class;
    }
}
