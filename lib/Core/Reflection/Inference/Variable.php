<?php

namespace Phpactor\WorseReflection\Core\Reflection\Inference;

use Phpactor\WorseReflection\Core\Offset;

/**
 * TODO: This class is now potentially redundant as all of it's information is contained within SymbolInformation
 */
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

    private function __construct(string $name, Offset $offset, SymbolInformation $value)
    {
        $this->name = $name;
        $this->offset = $offset;
        $this->value = $value;
    }

    public static function fromOffsetNameAndValue(Offset $offset, string $name, SymbolInformation $value): Variable
    {
        return new self($name, $offset, $value);
    }

    public function __toString()
    {
        return $this->name;
    }

    public function offset(): Offset
    {
        return $this->offset;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function value(): SymbolInformation
    {
        return $this->value;
    }
}
