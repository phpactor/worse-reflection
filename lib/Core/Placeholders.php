<?php

namespace Phpactor\WorseReflection\Core;

use RuntimeException;

final class Placeholders
{
    /**
     * @var Placeholder[]
     */
    private $placeholders;

    /**
     * @param Placeholder[]
     */
    public function __construct(array $placeholders = [])
    {
        $this->placeholders = $placeholders;
    }

    public function merge(Placeholders $placeholders): self
    {
        return new self(array_merge($this->placeholders, $placeholders->placeholders));
    }

    public function has(string $name): bool
    {
        return isset($this->placeholders[$name]);
    }

    public function get(string $name): Placeholder
    {
        if (!$this->has($name)) {
            throw new RuntimeException(sprintf(
                'Unknown placeholder "%s"',
                $name
            ));
        }

        return $this->placeholders[$name];
    }
}
