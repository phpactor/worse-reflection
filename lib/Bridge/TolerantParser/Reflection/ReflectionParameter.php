<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\Reflection;

use Phpactor\WorseReflection\Core\Reflection\ReflectionFunctionLike;
use Phpactor\WorseReflection\Core\ServiceLocator;
use Microsoft\PhpParser\Node\Parameter;
use Phpactor\WorseReflection\Core\Type;
use Microsoft\PhpParser\Node;
use Phpactor\WorseReflection\Core\DefaultValue;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Reflection\ReflectionParameter as CoreReflectionParameter;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\TypeResolver\DeclaredMemberTypeResolver;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMethod as CoreReflectionMethod;
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
     * @var CoreReflectionMethod
     */
    private $functionLike;

    public function __construct(ServiceLocator $serviceLocator, ReflectionFunctionLike $functionLike, Parameter $parameter)
    {
        $this->serviceLocator = $serviceLocator;
        $this->parameter = $parameter;
        $this->memberTypeResolver = new DeclaredMemberTypeResolver();
        $this->functionLike = $functionLike;
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
        $className = $this->functionLike instanceof ReflectionMethod ? $this->functionLike->class()->name() : null;

        $type = $this->memberTypeResolver->resolve(
            $this->parameter,
            $this->parameter->typeDeclaration,
            $className
        );

        if ($this->parameter->dotDotDotToken) {
            return Type::array($type->__toString());
        }

        return $type;
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

    /**
     * @deprecated use functionLike instead
     */
    public function method(): ReflectionFunctionLike
    {
        return $this->functionLike;
    }

    public function functionLike(): ReflectionFunctionLike
    {
        return $this->functionLike;
    }
}
