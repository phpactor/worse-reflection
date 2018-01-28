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
    private $symbolContext;

    private function __construct(string $name, Offset $offset, SymbolContext $symbolContext)
    {
        $this->name = $name;
        $this->offset = $offset;
        $this->symbolContext = $symbolContext;
    }

    public static function fromSymbolContext(SymbolContext $symbolContext)
    {
        return new self(
            $symbolContext->symbol()->name(),
            Offset::fromInt($symbolContext->symbol()->position()->start()),
            $symbolContext
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

    public function symbolContext(): SymbolContext
    {
        return $this->symbolContext;
    }

    public function isNamed(string $name)
    {
        $name = ltrim($name, '$');

        return $this->name == $name;
    }
}
