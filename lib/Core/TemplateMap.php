<?php

namespace Phpactor\WorseReflection\Core;

use Phpactor\WorseReflection\Core\Type\MissingType;

final class TemplateMap
{
    private array $map;

    public function __construct(array $map)
    {
        $this->map = $map;
    }

    public function toArray(): array
    {
        return $this->map;
    }

    public function replace(string $key, Type $type): self
    {
        $this->map[$key] = $type;

        return $this;
    }

    public function get(string $key): Type
    {
        if (!isset($this->map[$key])) {
            return new MissingType();
        }

        return $this->map[$key];
    }
}
