<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\TraitImport;

class TraitImport
{
    private $traitName;
    private $traitAliases = null;

    public function __construct(string $traitName, array $traitAliases = null)
    {
        $this->traitName = $traitName;
        $this->traitAliases = $traitAliases;
    }

    public function hasTraitAliases()
    {
        return null !== $this->traitAliases;
    }

    public function name(): string
    {
        return $this->traitName;
    }

    public function traitAliases(): array
    {
        return $this->traitAliases;
    }
}
