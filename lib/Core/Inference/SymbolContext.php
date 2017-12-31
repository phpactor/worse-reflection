<?php

namespace Phpactor\WorseReflection\Core\Inference;

use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\Types;

final class SymbolContext
{
    /**
     * @var mixed
     */
    private $value;

    /**
     * @var Types
     */
    private $types;

    /**
     * @var Symbol
     */
    private $symbol;

    /**
     * @var Type
     */
    private $containerType;

    /**
     * @var string[]
     */
    private $issues = [];

    private function __construct(Symbol $symbol, Types $types, $value = null, Type $containerType = null)
    {
        $this->value = $value;
        $this->symbol = $symbol;
        $this->containerType = $containerType;
        $this->types = $types;
        $this->containerType = $containerType;
    }

    public static function for(Symbol $symbol): SymbolContext
    {
        return new self($symbol, Types::fromTypes([ Type::unknown() ]));
    }

    /**
     * @deprecated
     */
    public static function fromTypeAndValue(Type $type, $value): SymbolContext
    {
        return new self(Symbol::unknown(), Types::fromTypes([ $type ]), $value);
    }

    /**
     * @deprecated Types are plural
     */
    public static function fromType(Type $type)
    {
        return new self(Symbol::unknown(), Types::fromTypes([ $type ]));
    }

    public static function none(): SymbolContext
    {
        return new self(Symbol::unknown(), Types::empty());
    }

    public function withValue($value): SymbolContext
    {
        return new self($this->symbol, $this->types, $value, $this->containerType);
    }

    public function withContainerType(Type $containerType): SymbolContext
    {
        return new self($this->symbol, $this->types, $this->value, $containerType);
    }

    /**
     * @deprecated Types are plural
     */
    public function withType(Type $type): SymbolContext
    {
        return new self($this->symbol, Types::fromTypes([ $type ]), $this->value, $this->containerType);
    }

    public function withTypes(Types $types): SymbolContext
    {
        return new self($this->symbol, $types, $this->value, $this->containerType);
    }

    public function withIssue(string $message): SymbolContext
    {
        $new = new self($this->symbol, $this->types, $this->value, $this->containerType);
        $new->issues[] = $message;

        return $new;
    }

    /**
     * @deprecated
     */
    public function type(): Type
    {
        foreach ($this->types() as $type) {
            return $type;
        }

        return Type::unknown();
    }

    public function types(): Types
    {
        return $this->types;
    }

    public function value()
    {
        return $this->value;
    }

    public function symbol(): Symbol
    {
        return $this->symbol;
    }

    public function hasContainerType(): bool
    {
        return null !== $this->containerType;
    }

    /**
     * @return Type
     */
    public function containerType()
    {
        return $this->containerType;
    }

    public function issues(): array
    {
        return $this->issues;
    }
}
