<?php

namespace Phpactor\WorseReflection\Core;

class DocblockType
{
    /**
     * @var array
     */
    private $types;

    /**
     * @var string
     */
    private $target;

    public function __construct(array $types, string $target = null)
    {
        $this->types = $types;
        $this->target = $target;
    }

    public function types(): array
    {
        return $this->types;
    }

    public function target(): string
    {
        return $this->target;
    }
}
