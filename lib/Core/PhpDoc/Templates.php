<?php

namespace Phpactor\WorseReflection\Core\PhpDoc;

use RuntimeException;
use Phpactor\WorseReflection\Core\PhpDoc\Template;
use Phpactor\WorseReflection\Core\PhpDoc\Templates;

final class Templates
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

    public function merge(Templates $placeholders): self
    {
        return new self(array_merge($this->placeholders, $placeholders->placeholders));
    }

    public function has(string $name): bool
    {
        return isset($this->placeholders[$name]);
    }

    public function get(string $name): Template
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
