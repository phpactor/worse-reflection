<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\Reflection;

use Phpactor\WorseReflection\Core\Position;
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
