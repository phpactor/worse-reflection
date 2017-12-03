<?php

namespace Phpactor\WorseReflection\Core;

use Microsoft\PhpParser\Node;

class Docblock
{
    private $docblock;

    private function __construct(string $docblock)
    {
        $this->docblock = $docblock;
    }

    public static function fromNode(Node $node): Docblock
    {
        return new self($node->getLeadingCommentAndWhitespaceText());
    }

    public static function fromString(string $docblock): Docblock
    {
        return new self($docblock);
    }

    public function isDefined()
    {
        return trim($this->docblock) != '';
    }

    public function raw(): string
    {
        return $this->docblock;
    }

    public function __toString()
    {
        return $this->raw();
    }

    public function formatted(): string
    {
        $lines = explode(PHP_EOL, $this->docblock);
        $formatted = [];
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) {
                continue;
            }

            if ($line == '/**') {
                continue;
            }

            if ($line == '*') {
                $line = '';
            }

            if (substr($line, 0, 2) == '* ') {
                $line = substr($line, 2);
            }

            if (substr($line, 0, 2) == '*/') {
                continue;
            }

            $formatted[] = $line;
        }

        return implode(PHP_EOL, $formatted);
    }

    public function returnTypes(): array
    {
        $types = [];
        $tags = $this->tags('return');

        foreach ($tags as $tag) {
            foreach ($this->explodeTypeString($tag) as $type) {
                $types[] = Type::fromString($type);
            }
        }

        return $types;
    }

    public function methodTypes(): array
    {
        $types = [];
        $tags = $this->tags('method');

        foreach ($tags as $methodName => $tag) {
            foreach ($this->explodeTypeString($tag) as $type) {
                $types[$methodName] = Type::fromString($type);
            }
        }

        return $types;
    }

    public function varTypes(): array
    {
        $types = [];
        $tags = $this->tags('var');

        foreach ($tags as $varName => $tag) {
            foreach ($this->explodeTypeString($tag) as $type) {
                $types[] = Type::fromString($type);
            }
        }

        return $types;
    }

    private function tags(string $tag)
    {
        if (!preg_match_all(sprintf(
            '{@%s ([\^|\$\w+\\\]+)\s?(\w+)?}',
            $tag
        ), $this->docblock, $matches)) {
            return [];
        }

        if (isset($matches[2])) {
            return array_combine($matches[2], $matches[1]);
        }

        return $matches[1];
    }

    public function inherits(): bool
    {
        return (bool)preg_match('#inheritdoc#i', $this->docblock);
    }

    private function explodeTypeString($typeString)
    {
        $typeString = str_replace('^', '|', $typeString);

        return explode('|', $typeString);
    }
}
