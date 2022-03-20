<?php

namespace Phpactor\WorseReflection\Bridge\Phpactor\DocblockParser;

use Phpactor\DocblockParser\Ast\Node;
use Phpactor\DocblockParser\Ast\TypeNode;
use Phpactor\DocblockParser\Ast\Type\ArrayNode;
use Phpactor\DocblockParser\Ast\Type\ClassNode;
use Phpactor\DocblockParser\Ast\Type\GenericNode;
use Phpactor\DocblockParser\Ast\Type\ScalarNode;
use Phpactor\DocblockParser\Ast\Type\UnionNode;
use Phpactor\Docblock\DocblockTypes;
use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Core\TemplateMap;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\Type\ArrayType;
use Phpactor\WorseReflection\Core\Type\ClassType;
use Phpactor\WorseReflection\Core\Type\FloatType;
use Phpactor\WorseReflection\Core\Type\GenericClassType;
use Phpactor\WorseReflection\Core\Type\IntType;
use Phpactor\WorseReflection\Core\Type\MissingType;
use Phpactor\WorseReflection\Core\Type\MixedType;
use Phpactor\WorseReflection\Core\Type\ReflectedClassType;
use Phpactor\WorseReflection\Core\Type\StringType;
use Phpactor\WorseReflection\Core\Type\UnionType;
use Phpactor\WorseReflection\Core\Types;
use Phpactor\WorseReflection\Reflector;
use RuntimeException;

class TypeConverter
{
    private Reflector $reflector;

    public function __construct(Reflector $reflector)
    {
        $this->reflector = $reflector;
    }

    public function convert(?TypeNode $type): Type
    {
        if ($type instanceof ScalarNode) {
            return $this->convertScalar($type->toString());
        }
        if ($type instanceof ArrayNode) {
            return $this->convertArray($type);
        }
        if ($type instanceof UnionNode) {
            return $this->convertUnion($type);
        }
        if ($type instanceof GenericNode) {
            return $this->convertGeneric($type);
        }
        if ($type instanceof ClassNode) {
            return $this->convertClass($type);
        }

        return new MissingType();
    }

    private function convertScalar(string $type): Type
    {
        if ($type === 'int') {
            return new IntType();
        }
        if ($type === 'string') {
            return new StringType();
        }
        if ($type === 'float') {
            return new FloatType();
        }
        if ($type === 'mixed') {
            return new MixedType();
        }

        return new MissingType();
    }

    private function convertArray(ArrayNode $type): Type
    {
        return new ArrayType(new MissingType());
    }

    private function convertUnion(UnionNode $union): Type
    {
        return new UnionType(...array_map(
            fn (Node $node) => $this->convert($node),
            $union->types->types()->list
        ));
    }

    private function convertGeneric(GenericNode $type): Type
    {
        if ($type->type instanceof ArrayNode) {
            $parameters = array_values($type->parameters()->types()->list);
            if (count($parameters) === 1) {
                return new ArrayType(
                    new MissingType(),
                    $this->convert($parameters[0])
                );
            }
            if (count($parameters) === 2) {
                return new ArrayType(
                    $this->convert($parameters[0]),
                    $this->convert($parameters[1]),
                );
            }
            return new MissingType();
        }

        $classType = $this->convert($type->type);

        if (!$classType instanceof ClassType) {
            return new MissingType();
        }

        return new GenericClassType(
            $this->reflector,
            $classType->name(),
            new TemplateMap(array_map(
                fn (TypeNode $node) => $this->convert($node),
                $type->parameters()->types()->list
            ))
        );
    }

    private function convertClass(ClassNode $type): Type
    {
        return new ReflectedClassType($this->reflector, ClassName::fromString($type->name()->toString()));
    }
}
