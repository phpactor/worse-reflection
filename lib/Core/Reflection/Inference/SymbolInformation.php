<?php

namespace Phpactor\WorseReflection\Core\Reflection\Inference;

use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\Reflection\Inference\SymbolInformation;

final class SymbolInformation
{
    /**
     * @var mixed
     */
    private $value;

    /**
     * @var Type
     */
    private $type;

    /**
     * @var Symbol
     */
    private $symbol;

    private function __construct(Symbol $symbol, Type $type, $value = null)
    {
        $this->type = $type;
        $this->value = $value;
        $this->symbol = $symbol;
    }

    public static function for(Symbol $symbol): SymbolInformation
    {
        return new self($symbol, Type::unknown());
    }

    /**
     * @deprecated
     */
    public static function fromTypeAndValue(Type $type, $value): SymbolInformation
    {
        return new self(Symbol::unknown(), $type, $value);
    }

    /**
     * @deprecated
     */
    public static function fromType(Type $type)
    {
        return new self(Symbol::unknown(), $type);
    }

    public static function none()
    {
        return new self(Symbol::unknown(), Type::undefined());
    }

    public function withValue($value)
    {
        return new self($this->symbol, $this->type, $value);
    }

    public function withType($type)
    {
        return new self($this->symbol, $type, $this->value);
    }

    public function type(): Type
    {
        return $this->type;
    }

    public function value()
    {
        return $this->value;
    }

    public function symbol(): Symbol
    {
        return $this->symbol;
    }
}

