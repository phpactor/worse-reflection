<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\Reflection;

use Phpactor\WorseReflection\Core\Reflection\ReflectionScope as CoreReflectionScope;
use Microsoft\PhpParser\Node;
use Phpactor\WorseReflection\Core\NameImports;
use Phpactor\WorseReflection\Core\Name;
use Microsoft\PhpParser\ResolvedName;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClassLike;
use Phpactor\WorseReflection\Core\Inference\FullyQualifiedNameResolver;
use Phpactor\WorseReflection\Core\Logger\ArrayLogger;

class ReflectionScope implements CoreReflectionScope
{
    /**
     * @var Node
     */
    private $node;

    public function __construct(Node $node)
    {
        $this->node = $node;
    }

    public function nameImports(): NameImports
    {
        list($nameImports) = $this->node->getImportTablesForCurrentScope();
        return NameImports::fromNames(array_map(function (ResolvedName $name) {
            return Name::fromParts($name->getNameParts());
        }, $nameImports));
    }

    public function namespace(): Name
    {
        $namespaceDefinition = $this->node->getNamespaceDefinition();

        if (null === $namespaceDefinition) {
            return Name::fromString('');
        }

        if (null === $namespaceDefinition->name) {
            return Name::fromString('');
        }

        return Name::fromString($namespaceDefinition->name->getText());
    }

    public function resolveFullyQualifiedName($type, ReflectionClassLike $class = null): Type
    {
        $resolver = new FullyQualifiedNameResolver(new ArrayLogger());
        return $resolver->resolve($this->node, $type, $class ? $class->name() : null);
    }
}
