<?php

namespace Phpactor\WorseReflection\Core\DocBlock;

use IteratorAggregate;
use Phpactor\WorseReflection\Core\Types;

class DocBlockVars implements IteratorAggregate
{
    /**
     * @var array
     */
    private $vars = [];

    public function __construct(array $vars)
    {
        foreach ($vars as $var) {
            $this->add($var);
        }
    }

    public function types(): Types
    {
        return Types::fromTypes(array_map(function (DocBlockVar $var) {
            return $var->type();
        }, $this->vars));
    }

    /**
     * {@inheritDoc}
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->vars);
    }

    private function add(DocBlockVar $var)
    {
        $this->vars[] = $var;
    }
}
