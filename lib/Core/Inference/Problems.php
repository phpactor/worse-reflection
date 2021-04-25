<?php

namespace Phpactor\WorseReflection\Core\Inference;

use IteratorAggregate;
use Countable;
use ArrayIterator;

final class Problems implements IteratorAggregate, Countable
{
    /**
     * @var Problem[]
     */
    private $problems = [];

    public function __construct(array $problems = [])
    {
        $this->problems = $problems;
    }

    public static function empty(): Problems
    {
        return new self();
    }

    public function getIterator()
    {
        return new ArrayIterator($this->problems);
    }

    public function add(Problem $problem): void
    {
        $this->problems[] = $problem;
    }

    public function hasNone(): bool
    {
        return count($this->problems) === 0;
    }

    public function count(): int
    {
        return count($this->problems);
    }

    public function toArray(): array
    {
        return $this->problems;
    }

    public function merge(Problems $problems)
    {
        return new self(array_merge(
            $this->problems,
            $problems->toArray()
        ));
    }
}
