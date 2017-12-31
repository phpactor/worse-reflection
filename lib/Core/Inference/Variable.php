<?php

namespace Phpactor\WorseReflection\Core\Inference;

use Phpactor\WorseReflection\Core\Offset;

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
    private $symbolInformation;

    private function __construct(string $name, Offset $offset, SymbolContext $symbolInformation)
    {
        $this->name = $name;
        $this->offset = $offset;
        $this->symbolInformation = $symbolInformation;
    }

    public static function fromSymbolContext(SymbolContext $symbolInformation)
    {
        return new self(
            $symbolInformation->symbol()->name(),
            Offset::fromInt($symbolInformation->symbol()->position()->start()),
            $symbolInformation
        );
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

    public function symbolInformation(): SymbolContext
    {
        return $this->symbolInformation;
    }

    public function isNamed(string $name)
    {
        $name = ltrim($name, '$');

        return $this->name == $name;
    }
}
