<?php

namespace Phpactor\WorseReflection\Core\Reflection\Collection;

use Phpactor\WorseReflection\Core\ServiceLocator;
use Phpactor\WorseReflection\Core\Reflection\ReflectionProperty;
use Microsoft\PhpParser\Node\Statement\ClassDeclaration;
use Microsoft\PhpParser\Node\PropertyDeclaration;
use Microsoft\PhpParser\Node\Expression\Variable;
use Microsoft\PhpParser\Node\SourceFileNode;
use Microsoft\PhpParser\Node\Statement\InterfaceDeclaration;
use Phpactor\WorseReflection\Core\Reflection\ReflectionInterface;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClass;
use Microsoft\PhpParser\Node\Statement\TraitDeclaration;
use Phpactor\WorseReflection\Core\Reflection\ReflectionTrait;
use Phpactor\WorseReflection\Core\Reflection\Collection\AbstractReflectionCollection;

class ReflectionClassCollection extends AbstractReflectionCollection
{
    public static function fromSourceFileNode(ServiceLocator $serviceLocator, SourceFileNode $source)
    {
        $items = [];

        foreach ($source->getChildNodes() as $child) {
            if (
                false === $child instanceof ClassDeclaration &&
                false === $child instanceof InterfaceDeclaration &&
                false === $child instanceof TraitDeclaration
            ) {
                continue;
            }

            if ($child instanceof TraitDeclaration) {
                $items[(string) $child->getNamespacedName()] =  new ReflectionTrait($serviceLocator, $child);
                continue;
            }

            if ($child instanceof InterfaceDeclaration) {
                $items[(string) $child->getNamespacedName()] =  new ReflectionInterface($serviceLocator, $child);
                continue;
            }

            $items[(string) $child->getNamespacedName()] = new ReflectionClass($serviceLocator, $child);
        }

        return new static($serviceLocator, $items);
    }

    public function concrete()
    {
        return new self($this->serviceLocator, array_filter($this->items, function ($item) {
            return $item->isConcrete();
        }));
    }
}
