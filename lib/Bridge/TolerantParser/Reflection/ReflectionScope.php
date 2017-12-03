<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\Reflection;

use Phpactor\WorseReflection\Core\Reflection\ReflectionScope as CoreReflectionScope;
use Microsoft\PhpParser\Node;
use Phpactor\WorseReflection\Core\NameImports;
use Phpactor\WorseReflection\Core\Name;
use Microsoft\PhpParser\ResolvedName;
use Phpactor\WorseReflection\Core\Type;

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

    public function resolveFullyQualifiedName(string $type): Type
    {
        if (substr($type, 0, 1) == '\\') {
            return Type::fromString($type);
        }

        $type = Type::fromString($type);

        if (false === $type->isClass()) {
            return $type;
        }

        if ($this->nameImports()->hasAlias($type->short())) {
            return Type::fromString((string) $this->nameImports()->getByAlias($type->short()));
        }

        return $type->prependNamespace($this->namespace());
    }
}
