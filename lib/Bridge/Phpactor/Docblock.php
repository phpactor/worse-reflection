<?php

namespace Phpactor\WorseReflection\Bridge\Phpactor;

use Phpactor\Docblock\Ast\Docblock as PhpactorDocblock;
use Phpactor\Docblock\Ast\Tag\DeprecatedTag;
use Phpactor\Docblock\Ast\Tag\MethodTag;
use Phpactor\Docblock\Ast\Tag\ParamTag;
use Phpactor\Docblock\Ast\Tag\PropertyTag;
use Phpactor\Docblock\Ast\Tag\ReturnTag;
use Phpactor\Docblock\Ast\Tag\VarTag;
use Phpactor\Docblock\Ast\Token;
use Phpactor\WorseReflection\Core\Deprecation;
use Phpactor\WorseReflection\Core\DocBlock\DocBlock as CoreDocblock;
use Phpactor\WorseReflection\Core\DocBlock\DocBlockVar;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Position;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionMethodCollection;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionPropertyCollection;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClassLike;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\Types;
use Phpactor\WorseReflection\Core\DocBlock\DocBlockVars;
use Phpactor\WorseReflection\Core\Virtual\Collection\VirtualReflectionMethodCollection;
use Phpactor\WorseReflection\Core\Virtual\Collection\VirtualReflectionPropertyCollection;
use Phpactor\WorseReflection\Core\Virtual\VirtualReflectionMethod;
use Phpactor\WorseReflection\Core\Virtual\VirtualReflectionProperty;
use Phpactor\WorseReflection\Core\Visibility;
use function Phpactor\WorseReflection\Core\DocBlock\DocBlockVar;

class Docblock implements CoreDocblock
{
    /**
     * @var string
     */
    private $raw;

    /**
     * @var PhpactorDocblock
     */
    private $node;

    public function __construct(
        string $raw,
        PhpactorDocblock $node
    )
    {
        $this->raw = $raw;
        $this->node = $node;
    }

    public function parameterTypes(string $paramName): Types
    {
        $types = [];
        foreach ($this->node->tags(ParamTag::class) as $child) {
            $types[] = Type::fromString($child->type->toString());
        }
        return Types::fromTypes($types);
    }

    public function propertyTypes(string $methodName): Types
    {
        $types = [];
        foreach ($this->node->tags(PropertyTag::class) as $child) {
            $types[] = Type::fromString($child->type->toString());
        }
        return Types::fromTypes($types);
    }

    public function vars(): DocBlockVars
    {
        $types = [];
        foreach ($this->node->tags(VarTag::class) as $child) {
            $types[] = new DocBlockVar(
                $child->variable->toString(),
                $child->type->toString()
            );
        }
        return new DocBlockVars($types);
    }

    public function inherits(): bool
    {
        return false;
    }

    public function methodTypes(string $methodName): Types
    {
        $types = [];
        foreach ($this->node->tags(MethodTag::class) as $child) {
            $types[] = Type::fromString($child->type->toString());
        }
        return Types::fromTypes($types);
    }

    public function formatted(): string
    {
        return $this->node->prose();
    }

    public function returnTypes(): Types
    {
        $types = [];
        foreach ($this->node->tags(ReturnTag::class) as $child) {
            $types[] = Type::fromString($child->type->toString());
        }
        return Types::fromTypes($types);
    }


    public function raw(): string
    {
        return $this->raw;
    }

    public function isDefined(): bool
    {
        return !empty($this->raw);
    }

    public function properties(ReflectionClassLike $declaringClass): ReflectionPropertyCollection
    {
        $properties = [];
        foreach ($this->node->tags(PropertyTag::class) as $property) {
            assert($property instanceof PropertyTag);
            $properties[] = new VirtualReflectionProperty(
                Position::fromStartAndEnd($property->start(), $property->start()),
                $declaringClass,
                $declaringClass,
                $property->name->toString(),
                new Frame(''),
                null,
                $declaringClass->scope(),
                Visibility::public(),
                Types::fromTypes([Type::fromString($property->type->toString())]),
                Type::fromString($property->type->toString()),
                new Deprecation($property->hasChild(DeprecationTag::class))
            );
        }

        return VirtualReflectionPropertyCollection::fromMembers([]);
    }

    public function methods(ReflectionClassLike $declaringClass): ReflectionMethodCollection
    {
        $methods = [];
        foreach ($this->node->tags(MethodTag::class) as $method) {
            assert($method instanceof MethodTag);
            $methods[] = new VirtualReflectionMethod(
                Position::fromStartAndEnd($method->start(), $method->start()),
                $declaringClass,
                $declaringClass,
                $method->name->toString(),
                new Frame(''),
                $this,
                $declaringClass->scope(),
                Visibility::public(),
                Types::fromTypes([Type::fromString($method->type->toString())]),
                Type::fromString($method->type->toString()),
                new Deprecation($this->node->hasTag(DeprecationTag::class))
            );
        }

        return VirtualReflectionMethodCollection::fromMembers($methods);
    }

    public function deprecation(): Deprecation
    {
        foreach ($this->node->tags(DeprecatedTag::class) as $child) {
            assert($child instanceof DeprecatedTag);
            return new Deprecation(true, $child->text->toString());
        }
        return new Deprecation(false);
    }

}
