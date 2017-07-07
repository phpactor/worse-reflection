<?php

namespace Phpactor\WorseReflection\Reflection;

use Microsoft\PhpParser\Node\SourceFileNode;
use Microsoft\PhpParser\Node\Statement\ClassDeclaration;
use Phpactor\WorseReflection\ClassName;
use Phpactor\WorseReflection\Reflector;
use Microsoft\PhpParser\Node\Statement\InterfaceDeclaration;
use Microsoft\PhpParser\Node;

class ReflectionSourceCode
{
    private $reflector;
    private $node;

    public function __construct(Reflector $reflector, SourceFileNode $node)
    {
        $this->node = $node;
        $this->reflector = $reflector;
    }

    public function findClass(ClassName $name)
    {
        foreach ($this->node->getChildNodes() as $child) {
            if (
                false === $child instanceof ClassDeclaration &&
                false === $child instanceof InterfaceDeclaration
            ) {
                continue;
            }

            if ((string) $child->getNamespacedName() === (string) $name) {
                if ($child instanceof InterfaceDeclaration) {
                    return new ReflectionInterface($this->reflector, $child);
                }

                return new ReflectionClass($this->reflector, $child);
            }
        }
    }

    protected function node(): Node
    {
        return $this->node;
    }
}
