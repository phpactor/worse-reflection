<?php

namespace Phpactor\WorseReflection\Core\Reflection;

use Microsoft\PhpParser\Node\SourceFileNode;
use Microsoft\PhpParser\Node\Statement\ClassDeclaration;
use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Core\ServiceLocator;
use Microsoft\PhpParser\Node\Statement\InterfaceDeclaration;
use Microsoft\PhpParser\Node;
use Phpactor\WorseReflection\Core\Reflection\AbstractReflectedNode;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClass;
use Phpactor\WorseReflection\Core\Reflection\ReflectionInterface;

class ReflectionSourceCode extends AbstractReflectedNode
{
    /**
     * @var SourceFileNode
     */
    private $node;

    /**
     * @var ServiceLocator
     */
    private $serviceLocator;

    public function __construct(ServiceLocator $serviceLocator, SourceFileNode $node)
    {
        $this->node = $node;
        $this->serviceLocator = $serviceLocator;
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
                    return new ReflectionInterface($this->serviceLocator, $child);
                }

                return new ReflectionClass($this->serviceLocator, $child);
            }
        }
    }

    protected function node(): Node
    {
        return $this->node;
    }
}

