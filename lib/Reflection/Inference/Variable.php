<?php

namespace Phpactor\WorseReflection\Reflection\Inference;

use Phpactor\WorseReflection\Type;
use Phpactor\WorseReflection\Offset;

final class Variable
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var Offset
     */
    private $offset;

    /**
     * @var mixed
     */
    private $value;

    private function __construct(string $name, Offset $offset, Value $value)
    {
        $this->name = $name;
        $this->offset = $offset;
        $this->value = $value;
    }

    public static function fromOffsetNameAndValue(Offset $offset, string $name, Value $value): Variable
    {
         return new self($name, $offset, $value);
    }

    public function __toString()
    {
        return $this->type;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function value(): Value
    {
        return $this->value;
    }
}
