<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\Reflection;

use Phpactor\WorseReflection\Core\Reflection\ReflectionScope as CoreReflectionScope;
use Microsoft\PhpParser\Node;
use Phpactor\WorseReflection\Core\NameImports;
use Phpactor\WorseReflection\Core\Name;
use Microsoft\PhpParser\ResolvedName;

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
}
