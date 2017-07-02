<?php

namespace DTL\WorseReflection\Reflection;

use DTL\WorseReflection\Reflector;
use PhpParser\Node\Stmt\ClassLike;
use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\NamespacedNameInterface;
use Microsoft\PhpParser\Node\Statement\InterfaceDeclaration;

class ReflectionInterface extends AbstractReflectionClass
{
    /**
     * @var Reflector
     */
    private $reflector;

    /**
     * @var ClassLike
     */
    private $node;

    public function __construct(
        Reflector $reflector,
        InterfaceDeclaration $node
    ) {
        $this->reflector = $reflector;
        $this->node = $node;
    }

    protected function node(): NamespacedNameInterface
    {
        return $this->node;
    }

    protected function reflector(): Reflector
    {
        return $this->reflector;
    }

    public function properties(): array
    {
    }
}
