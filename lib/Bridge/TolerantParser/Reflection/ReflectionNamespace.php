<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\Reflection;

use Phpactor\WorseReflection\Core\ServiceLocator;
use Microsoft\PhpParser\Node;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionClassCollection;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\Collection\ReflectionClassCollection as TolerantReflectionClassCollection;
use Phpactor\WorseReflection\Core\SourceCode;
use Phpactor\WorseReflection\Core\FullyQualifiedName;
use Microsoft\PhpParser\Node\Statement\NamespaceDefinition;

class ReflectionNamespace
{
    /**
     * @var ServiceLocator
     */
    private $locator;

    /**
     * @var Node
     */
    private $namespaceNode;

    /**
     * @var SourceCode
     */
    private $sourceCode;

    public function __construct(
        ServiceLocator $locator,
        Node $namespaceNode,
        SourceCode $sourceCode
    ) {
        $this->locator = $locator;
        $this->namespaceNode = $namespaceNode;
        $this->sourceCode = $sourceCode;
    }

    public function name(): FullyQualifiedName
    {
        $name = '';
        if ($this->namespaceNode instanceof NamespaceDefinition) {
            $name = $this->namespaceNode->getText();
        }

        return FullyQualifiedName::fromString($name);
    }

    public function classes(): ReflectionClassCollection
    {
        if (false === $this->namespaceNode instanceof NamespaceDefinition) {
            return TolerantReflectionClassCollection::fromNamespaceDefinition($this->locator, $this->sourceCode, $this->namespaceNode);
        }

        return TolerantReflectionClassCollection::fromNode($this->locator, $this->sourceCode, $this->namespaceNode);
    }
}
