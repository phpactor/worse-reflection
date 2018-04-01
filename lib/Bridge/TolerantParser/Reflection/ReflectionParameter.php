<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\Reflection;

use Phpactor\WorseReflection\Core\ServiceLocator;
use Microsoft\PhpParser\Node\Parameter;
use Phpactor\WorseReflection\Core\Type;
use Microsoft\PhpParser\Node;
use Phpactor\WorseReflection\Core\DefaultValue;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Reflection\ReflectionParameter as CoreReflectionParameter;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\TypeResolver\DeclaredMemberTypeResolver;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMethod;
use Phpactor\WorseReflection\Core\Types;
use Phpactor\WorseReflection\Core\Reflection\TypeResolver\ParameterTypeResolver;

class ReflectionParameter extends AbstractReflectedNode implements CoreReflectionParameter
{
    /**
     * @var ServiceLocator
     */
    private $serviceLocator;

    /**
     * @var Parameter
     */
    private $parameter;

    /**
     * @var DeclaredMemberTypeResolver
     */
    private $memberTypeResolver;

    /**
     * @var ReflectionMethod
     */
    private $method;

    public function __construct(ServiceLocator $serviceLocator, ReflectionMethod $method, Parameter $parameter)
    {
        $this->serviceLocator = $serviceLocator;
        $this->parameter = $parameter;
        $this->memberTypeResolver = new DeclaredMemberTypeResolver();
        $this->method = $method;
    }

    public function name(): string
    {
        if (null === $this->parameter->getName()) {
            $this->serviceLocator->logger()->warning(sprintf(
                'Parameter has no variable at offset "%s"',
                $this->parameter->getStart()
            ));
            return '';
        }

        return $this->parameter->getName();
    }

    public function type(): Type
    {
        return $this->memberTypeResolver->resolve(
            $this->method->class()->name(),
            $this->parameter,
            $this->parameter->typeDeclaration
        );
    }

    public function inferredTypes(): Types
    {
        return (new ParameterTypeResolver($this))->resolve();
    }

    public function default(): DefaultValue
    {
        if (null === $this->parameter->default) {
            return DefaultValue::undefined();
        }
        $value = $this->serviceLocator->symbolContextResolver()->resolveNode(new Frame('test'), $this->parameter->default)->value();

        return DefaultValue::fromValue($value);
    }

    protected function node(): Node
    {
        return $this->parameter;
    }

    public function byReference(): bool
    {
        return (bool) $this->parameter->byRefToken;
    }

    public function method(): ReflectionMethod
    {
        return $this->method;
    }
}
