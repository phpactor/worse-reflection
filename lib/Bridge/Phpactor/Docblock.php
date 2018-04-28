<?php

namespace Phpactor\WorseReflection\Bridge\Phpactor;

use Phpactor\Docblock\DocblockType;
use Phpactor\Docblock\Tag\DocblockTypes;
use Phpactor\WorseReflection\Core\DocBlock\DocBlock as CoreDocblock;
use Phpactor\Docblock\Docblock as PhpactorDocblock;
use Phpactor\WorseReflection\Core\DocBlock\DocBlockVars;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\DocBlock\DocBlockVar;
use Phpactor\WorseReflection\Core\Types;

class Docblock implements CoreDocblock
{
    /**
     * @var PhpactorDocblock
     */
    private $docblock;

    /**
     * @var string
     */
    private $raw;

    public function __construct(string $raw, PhpactorDocblock $docblock)
    {
        $this->docblock = $docblock;
        $this->raw = $raw;
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

    private function typesFromTag(string $tag)
    {
        $types = [];

        foreach ($this->docblock->tags()->byName($tag) as $tag) {
            return $this->typesFromDocblockTypes($tag->types());
        }

        return Types::empty();
    }

    private function typesFromDocblockTypes(DocblockTypes $types)
    {
        $types = array_map(function (DocblockType $type) {
            return $this->typesFromDocblockType($type);
        }, iterator_to_array($types));

        return Types::fromTypes($types);
    }

    private function typesFromDocblockType(DocblockType $type)
    {
        if ($type->isArray()) {
            return Type::array($type->iteratedType());
        }
        
        if ($type->isCollection()) {
            return Type::collection((string) $type, $type->iteratedType());
        }
        
        return Type::fromString($type->__toString());
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
}
