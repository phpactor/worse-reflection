<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\TraitImport;

use Phpactor\WorseReflection\Core\Visibility;

class TraitAlias
{
    private $originalName;
    private $visiblity;
    private $newName;

    public function __construct(string $originalName, Visibility $visiblity, string $newName)
    {
        $this->originalName = $originalName;
        $this->visiblity = $visiblity;
        $this->newName = $newName;
    }

    public function originalName(): string
    {
        return $this->originalName;
    }

    public function visiblity(): Visibility
    {
        return $this->visiblity;
    }

    public function newName(): string
    {
        return $this->newName;
    }
}
