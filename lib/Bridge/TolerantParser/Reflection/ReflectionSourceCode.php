<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\Reflection;

use Microsoft\PhpParser\Node\SourceFileNode;
use Microsoft\PhpParser\Node\Statement\ClassDeclaration;
use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Core\ServiceLocator;
use Microsoft\PhpParser\Node\Statement\InterfaceDeclaration;
use Microsoft\PhpParser\Node;
use Phpactor\WorseReflection\Core\Reflection\ReflectionSourceCode as CoreReflectionSourceCode;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionNamespaceCollection;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\Collection\ReflectionNamespaceCollection as TolerantReflectionNamespaceCollection;
use Phpactor\WorseReflection\Core\SourceCode;

class ReflectionSourceCode extends AbstractReflectedNode implements CoreReflectionSourceCode
{
    /**
     * @var SourceFileNode
     */
    private $node;

    /**
     * @var ServiceLocator
     */
    private $serviceLocator;

    /**
     * @var SourceCode
     */
    private $sourceCode;

    public function __construct(ServiceLocator $serviceLocator, SourceCode $sourceCode, SourceFileNode $node )
    {
        $this->node = $node;
        $this->serviceLocator = $serviceLocator;
        $this->sourceCode = $sourceCode;
    }

    protected function node(): Node
    {
        return $this->node;
    }

    public function namespaces(): ReflectionNamespaceCollection
    {
        return TolerantReflectionNamespaceCollection::fromSourceNode($this->serviceLocator, $this->sourceCode, $this->node);
    }
}
