<?php

namespace Phpactor\WorseReflection\Reflection;

use Phpactor\WorseReflection\Position;
use Microsoft\PhpParser\Node;

abstract class AbstractReflectedNode
{
    abstract protected function node(): Node;

    public function position(): Position
    {
        return Position::fromFullStartStartAndEnd(
            $this->node()->getFullStart(),
            $this->node()->getStart(),
            $this->node()->getEndPosition()
        );
    }
}
