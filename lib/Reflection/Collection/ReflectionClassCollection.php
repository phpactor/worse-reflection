<?php

namespace Phpactor\WorseReflection\Reflection\Collection;

use Phpactor\WorseReflection\ServiceLocator;
use Phpactor\WorseReflection\Reflection\ReflectionProperty;
use Microsoft\PhpParser\Node\Statement\ClassDeclaration;
use Microsoft\PhpParser\Node\PropertyDeclaration;
use Microsoft\PhpParser\Node\Expression\Variable;
use Microsoft\PhpParser\Node\SourceFileNode;
use Microsoft\PhpParser\Node\Statement\InterfaceDeclaration;
use Phpactor\WorseReflection\Reflection\ReflectionInterface;
use Phpactor\WorseReflection\Reflection\ReflectionClass;

class ReflectionClassCollection extends AbstractReflectionCollection
{
    public static function fromSourceFileNode(ServiceLocator $serviceLocator, SourceFileNode $source)
    {
        $items = [];

        foreach ($source->getChildNodes() as $child) {
            if (
                false === $child instanceof ClassDeclaration &&
                false === $child instanceof InterfaceDeclaration
            ) {
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
