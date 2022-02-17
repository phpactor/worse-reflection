<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\Collection;

use Microsoft\PhpParser\Node\ConstElement;
use Microsoft\PhpParser\Node\EnumCaseDeclaration;
use Microsoft\PhpParser\Node\Statement\EnumDeclaration;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionEnumCaseCollection as PhpactorReflectionEnumCaseCollection;
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
    public static function fromEnumDeclaration(ServiceLocator $serviceLocator, EnumDeclaration $enum)
    {
        $items = [];
        foreach ($enum->enumMembers->enumMemberDeclarations as $member) {
            if (!$member instanceof EnumCaseDeclaration) {
                continue;
            }
            $items[] = $member;
        }

        return new static($serviceLocator, $items);
    }

    protected function collectionType(): string
    {
        return PhpactorReflectionEnumCaseCollection::class;
    }
}
