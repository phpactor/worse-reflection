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

    public static function fromNode(Node $node)
    {
        return new self($node->getLeadingCommentAndWhitespaceText());
    }

    public static function fromString(string $docblock)
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

    public function  __toString()
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
}
