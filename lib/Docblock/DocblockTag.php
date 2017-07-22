<?php

namespace Phpactor\TypeInference\Domain\Docblock;

final class DocblockTag
{
    private $name;
    private $value;
    private $target;

    private function __construct(string $name, $value, string $target = null)
    {
        $this->name = $name;
        $this->value = $value;
        $this->target = $target;
    }

    public static function fromNameAndValue(string $name, $value): DocblockTag
    {
        return new self($name, $value);
    }

    public static function fromNameTargetAndValue(string $name, string $target, $value): DocblockTag
    {
        return new self($name, $value, $target);
    }

    public function name(): string
    {
        return $this->name;
    }

    public function value()
    {
        return $this->value;
    }

    public function target()
    {
        return $this->target;
    }
}
