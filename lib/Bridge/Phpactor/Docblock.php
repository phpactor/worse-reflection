<?php

namespace Phpactor\WorseReflection\Bridge\Phpactor;

use Phpactor\Docblock\DocblockType;
use Phpactor\Docblock\DocblockTypes;
use Phpactor\Docblock\Tag\MethodTag;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\ReflectionInterface;
use Phpactor\WorseReflection\Core\Deprecation;
use Phpactor\WorseReflection\Core\DocBlock\DocBlock as CoreDocblock;
use Phpactor\Docblock\Docblock as PhpactorDocblock;
use Phpactor\WorseReflection\Core\DocBlock\DocBlockVars;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionMethodCollection;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionPropertyCollection;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClassLike;
use Phpactor\WorseReflection\Core\DocBlock\DocBlockVar;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\TypeFactory;
use Phpactor\WorseReflection\Core\Types;
use Phpactor\WorseReflection\Core\Virtual\Collection\VirtualReflectionMethodCollection;
use Phpactor\WorseReflection\Core\Virtual\Collection\VirtualReflectionPropertyCollection;
use Phpactor\WorseReflection\Reflector;

class Docblock implements CoreDocblock
{
    private PhpactorDocblock $docblock;
    
    private string $raw;
    
    private DocblockReflectionMethodFactory $methodFactory;
    
    private DocblockReflectionPropertyFactory $propertyFactory;

    private Reflector $reflector;

    public function __construct(
        string $raw,
        PhpactorDocblock $docblock,
        Reflector $reflector,
        DocblockReflectionMethodFactory $methodFactory = null,
        DocblockReflectionPropertyFactory $propertyFactory = null
    ) {
        $this->docblock = $docblock;
        $this->raw = $raw;
        $this->methodFactory = $methodFactory ?: new DocblockReflectionMethodFactory();
        $this->propertyFactory = $propertyFactory ?: new DocblockReflectionPropertyFactory($reflector);
        $this->reflector = $reflector;
    }

    public function isDefined(): bool
    {
        return trim($this->raw) != '';
    }

    public function raw(): string
    {
        return $this->raw;
    }

    public function formatted(): string
    {
        return $this->docblock->prose();
    }

    public function returnTypes(): Types
    {
        return $this->typesFromTag('return');
    }

    public function parameterTypes(string $paramName): Types
    {
        $types = [];

        foreach ($this->docblock->tags()->byName('param') as $tag) {
            if ($tag->varName() !== '$' . $paramName) {
                continue;
            }

            foreach ($tag->types() as $type) {
                $types[] = $this->typesFromDocblockType($type);
            }
        }

        return Types::fromTypes($types);
    }

    public function methodTypes(string $methodName): Types
    {
        $types = [];
        
        foreach ($this->docblock->tags()->byName('method') as $tag) {
            if ($tag->methodName() !== $methodName) {
                continue;
            }
        
            foreach ($tag->types() as $type) {
                $types[] = $this->typesFromDocblockType($type);
            }
        }
        
        return Types::fromTypes($types);
    }

    public function vars(): DocBlockVars
    {
        $vars = [];
        foreach ($this->docblock->tags()->byName('var') as $tag) {
            $vars[] = new DocBlockVar($tag->varName() ?: '', $this->typesFromDocblockTypes($tag->types()));
        }

        return new DocBlockVars($vars);
    }

    public function inherits(): bool
    {
        return 0 !== $this->docblock->tags()->byName('inheritDoc')->count();
    }

    public function methods(ReflectionClassLike $declaringClass): ReflectionMethodCollection
    {
        $methods = [];
        /** @var MethodTag $methodTag */
        foreach ($this->docblock->tags()->byName('method') as $methodTag) {
            if (!$methodTag->methodName()) {
                continue;
            }
            $methods[$methodTag->methodName()] = $this->methodFactory->create($this, $declaringClass, $methodTag);
        }

        return VirtualReflectionMethodCollection::fromReflectionMethods($methods);
    }

    public function properties(ReflectionClassLike $declaringClass): ReflectionPropertyCollection
    {
        $properties = [];
        foreach ($this->docblock->tags()->byName('property') as $propertyTag) {
            if (!$propertyTag->propertyName()) {
                continue;
            }
            if ($declaringClass instanceof ReflectionInterface) {
                continue;
            }
            $properties[$propertyTag->propertyName()] = $this->propertyFactory->create($this, $declaringClass, $propertyTag);
        }

        return VirtualReflectionPropertyCollection::fromReflectionProperties($properties);
    }

    public function propertyTypes(string $propertyName): Types
    {
        $types = [];
        
        foreach ($this->docblock->tags()->byName('property') as $tag) {
            if ($tag->propertyName() !== $propertyName) {
                continue;
            }
        
            foreach ($tag->types() as $type) {
                $types[] = $this->typesFromDocblockType($type);
            }
        }
        
        return Types::fromTypes($types);
    }

    public function deprecation(): Deprecation
    {
        foreach ($this->docblock->tags()->byName('deprecated') as $tag) {
            return new Deprecation(true, $tag->message());
        }
        return new Deprecation(false);
    }

    private function typesFromTag(string $tag): Types
    {
        $types = [];

        foreach ($this->docblock->tags()->byName($tag) as $tag) {
            return $this->typesFromDocblockTypes($tag->types());
        }

        return Types::empty();
    }

    private function typesFromDocblockTypes(DocblockTypes $types): Types
    {
        $types = array_map(function (DocblockType $type) {
            return $this->typesFromDocblockType($type);
        }, iterator_to_array($types));

        return Types::fromTypes($types);
    }

    private function typesFromDocblockType(DocblockType $type): Type
    {
        if ($type->isArray()) {
            return TypeFactory::array((string) $type->iteratedType());
        }
        
        if ($type->isCollection()) {
            return TypeFactory::collection($this->reflector, (string) $type, $type->iteratedType());
        }
        
        return TypeFactory::fromStringWithReflector($type->__toString(), $this->reflector);
    }
}
