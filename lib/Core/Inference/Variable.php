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

    private function __construct(string $name, Offset $offset, SymbolInformation $symbolInformation)
    {
        $this->name = $name;
        $this->offset = $offset;
        $this->symbolInformation = $symbolInformation;
    }

    public static function fromSymbolInformation(SymbolInformation $symbolInformation)
    {
        return new self(
            $symbolInformation->symbol()->name(),
            Offset::fromInt($symbolInformation->symbol()->position()->start()),
            $symbolInformation
        );
    }

    public function __toString()
    {
        return $this->type;
    }

    public function offset(): Offset
    {
        return $this->offset;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function symbolInformation(): SymbolInformation
    {
        return $this->symbolInformation;
    }
}
