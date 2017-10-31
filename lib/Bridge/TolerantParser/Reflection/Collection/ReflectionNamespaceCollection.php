<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\Collection;

use Microsoft\PhpParser\Node\SourceFileNode;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\Collection\AbstractReflectionCollection;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\ReflectionNamespace;
use Phpactor\WorseReflection\Core\FullyQualifiedName;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionNamespaceCollection as CoreReflectionNamespaceCollection;
use Phpactor\WorseReflection\Core\ServiceLocator;
use Phpactor\WorseReflection\Core\SourceCode;
use Microsoft\PhpParser\Node\Statement\NamespaceDefinition;

class ReflectionNamespaceCollection extends AbstractReflectionCollection implements CoreReflectionNamespaceCollection
{
    public static function fromSourceNode(ServiceLocator $serviceLocator, SourceCode $sourceCode, SourceFileNode $sourceNode): ReflectionNamespaceCollection
    {
        $items = [];

        foreach ($sourceNode->getChildNodes() as $childNode) {
            if ($childNode instanceof NamespaceDefinition) {
                $items[$childNode->name->getText()] = new ReflectionNamespace($serviceLocator, $childNode, $sourceCode);
            }
        }

        // if there are no namespace definitions or only 1 namespace, use the source node
        if (empty($items) || count($items) === 1) {
            $items = [ new ReflectionNamespace($serviceLocator, $sourceNode, $sourceCode) ];
        }

        return new self($serviceLocator, $items);
    }
}
