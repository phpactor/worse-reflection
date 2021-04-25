<?php

namespace Phpactor\WorseReflection\Core\Inference;

use IteratorAggregate;
use Countable;
use ArrayIterator;
use RuntimeException;

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

    public function merge(Problems $problems): void
    {
        $this->problems = array_merge(
            $this->problems,
            $problems->toArray()
        );
    }

    public function __toString(): string
    {
        return implode(", ", array_map(function (Problem $problem) {
            return $problem->message();
        }, $this->problems));
    }

    public function first(): Problem
    {
        $problem = array_shift($this->problems);
        if (null === $problem) {
            throw new RuntimeException(
                'No problems, cannot get first'
            );
        }
        return $problem;
    }

}
