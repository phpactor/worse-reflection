<?php

namespace Phpactor\WorseReflection\Bridge\Phpactor\DocblockParser;

use Phpactor\DocblockParser\Ast\Node;
use Phpactor\DocblockParser\Ast\ParameterList;
use Phpactor\DocblockParser\Ast\Tag\DeprecatedTag;
use Phpactor\DocblockParser\Ast\Tag\MethodTag;
use Phpactor\DocblockParser\Ast\Tag\ParameterTag;
use Phpactor\DocblockParser\Ast\Tag\PropertyTag;
use Phpactor\DocblockParser\Ast\Tag\ReturnTag;
use Phpactor\DocblockParser\Ast\Tag\VarTag;
use Phpactor\WorseReflection\Core\DefaultValue;
use Phpactor\WorseReflection\Core\Deprecation;
use Phpactor\WorseReflection\Core\DocBlock\DocBlock;
use Phpactor\WorseReflection\Core\DocBlock\DocBlockVar;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\NodeText;
use Phpactor\WorseReflection\Core\Position;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionMethodCollection;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionPropertyCollection;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClassLike;
use Phpactor\WorseReflection\Core\Reflection\ReflectionFunctionLike;
use Phpactor\WorseReflection\Core\Types;
use Phpactor\WorseReflection\Core\DocBlock\DocBlockVars;
use Phpactor\WorseReflection\Core\Virtual\Collection\VirtualReflectionMethodCollection;
use Phpactor\WorseReflection\Core\Virtual\Collection\VirtualReflectionParameterCollection;
use Phpactor\WorseReflection\Core\Virtual\Collection\VirtualReflectionPropertyCollection;
use Phpactor\WorseReflection\Core\Virtual\VirtualReflectionMethod;
use Phpactor\WorseReflection\Core\Virtual\VirtualReflectionParameter;
use Phpactor\WorseReflection\Core\Virtual\VirtualReflectionProperty;
use Phpactor\WorseReflection\Core\Visibility;
use function array_map;

class ParsedDocblock implements DocBlock
{
    private Node $node;

    private TypeConverter $typeConverter;

    public function __construct(Node $node, TypeConverter $typeConverter)
    {
        $this->node = $node;
        $this->typeConverter = $typeConverter;
    }

    public function methodTypes(string $methodName): Types
    {
        foreach ($this->node->descendantElements(MethodTag::class) as $methodTag) {
            assert($methodTag instanceof MethodTag);
            if ($methodTag->name->toString() !== $methodName) {
                continue;
            }
            return Types::fromTypes([$this->typeConverter->convert($methodTag->type)]);
        }

        return Types::empty();
    }

    public function inherits(): bool
    {
        return false;
    }

    public function vars(): DocBlockVars
    {
        $vars = [];
        foreach ($this->node->descendantElements(VarTag::class) as $varTag) {
            assert($varTag instanceof VarTag);
            $vars[] = new DocBlockVar(
                $varTag->variable ? ltrim($varTag->variable->name()->toString(), '$') : '',
                Types::fromTypes([
                    $this->typeConverter->convert($varTag->type),
                ])
            );
        }

        return new DocBlockVars($vars);
    }

    public function parameterTypes(string $paramName): Types
    {
    }

    public function propertyTypes(string $methodName): Types
    {
    }

    public function formatted(): string
    {
    }

    public function returnTypes(): Types
    {
        foreach ($this->node->descendantElements(ReturnTag::class) as $tag) {
            assert($tag instanceof ReturnTag);
            return Types::fromTypes([$this->typeConverter->convert($tag->type())]);
        }
        return Types::empty();
    }


    public function raw(): string
    {
    }

    public function isDefined(): bool
    {
        return true;
    }

    public function properties(ReflectionClassLike $declaringClass): ReflectionPropertyCollection
    {
        $properties = [];
        foreach ($this->node->descendantElements(PropertyTag::class) as $propertyTag) {
            assert($propertyTag instanceof PropertyTag);
            $method = new VirtualReflectionProperty(
                $declaringClass->position(),
                $declaringClass,
                $declaringClass,
                ltrim($propertyTag->name->toString(), '$'),
                new Frame('docblock'),
                $this,
                $declaringClass->scope(),
                Visibility::public(),
                Types::fromTypes([$this->typeConverter->convert($propertyTag->type)]),
                $this->typeConverter->convert($propertyTag->type),
                new Deprecation(false),
            );
            $properties[] = $method;
        }

        return VirtualReflectionPropertyCollection::fromReflectionProperties($properties);
    }

    public function methods(ReflectionClassLike $declaringClass): ReflectionMethodCollection
    {
        $methods = [];
        foreach ($this->node->descendantElements(MethodTag::class) as $methodTag) {
            assert($methodTag instanceof MethodTag);
            $params = VirtualReflectionParameterCollection::empty();
            $method = new VirtualReflectionMethod(
                $declaringClass->position(),
                $declaringClass,
                $declaringClass,
                $methodTag->name->toString(),
                new Frame('docblock'),
                $this,
                $declaringClass->scope(),
                Visibility::public(),
                Types::fromTypes([$this->typeConverter->convert($methodTag->type)]),
                $this->typeConverter->convert($methodTag->type),
                $params,
                NodeText::fromString(''),
                false,
                false,
                new Deprecation(false),
            );
            $this->addParameters($method, $params, $methodTag->parameters);
            $methods[] = $method;
        }

        return VirtualReflectionMethodCollection::fromReflectionMethods($methods);
    }

    public function deprecation(): Deprecation
    {
        foreach ($this->node->descendantElements(DeprecatedTag::class) as $deprecatedTag) {
            assert($deprecatedTag instanceof DeprecatedTag);
            return new Deprecation(true, $deprecatedTag->text->toString());
        }

        return new Deprecation(false);
    }

    private function addParameters(VirtualReflectionMethod $method, VirtualReflectionParameterCollection $collection, ?ParameterList $parameterList): void
    {
        if (null === $parameterList) {
            return;
        }
        foreach ($parameterList->parameters() as $parameterTag) {
            $type = $this->typeConverter->convert($parameterTag->type);
            $collection->add(new VirtualReflectionParameter(
                ltrim($parameterTag->name->name->toString(), '$'),
                $method,
                Types::fromTypes([$type]),
                $type,
                DefaultValue::undefined(),
                false,
                $method->scope(),
                $method->position()
            ));
        }
    }
}
