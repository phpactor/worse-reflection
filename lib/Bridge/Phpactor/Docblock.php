<?php

namespace Phpactor\WorseReflection\Bridge\Phpactor;

use Phpactor\WorseReflection\Core\Docblock as CoreDocblock;
use Phpactor\WorseReflection\Core\Types;
use Phpactor\Docblock\Docblock as PhpactorDocblock;
use Phpactor\Docblock\Tag\ReturnTag;
use Phpactor\WorseReflection\Core\Type;

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

    public function returnTypes(): array
    {
        return $this->typesFromTag('return');
    }

    public function methodTypes(string $methodName): array
    {
        $types = [];

        foreach ($this->docblock->tags()->byName('method') as $tag) {
            if ($tag->methodName() !== $methodName) {
                continue;
            }

            foreach ($tag->types() as $type) {
                $types[] = Type::fromString((string) $type);
            }
        }

        return $types;
    }

    public function varTypes(): array
    {
        return $this->typesFromTag('var');
    }

    public function inherits(): bool
    {
        return 0 !== $this->docblock->tags()->byName('inheritDoc')->count();
    }

    private function typesFromTag(string $tag)
    {
        $types = [];

        foreach ($this->docblock->tags()->byName($tag) as $tag) {
            foreach ($tag->types() as $type) {
                $types[] = Type::fromString((string) $type);
            }
        }

        return $types;
    }
}
