<?php

namespace Phpactor\WorseReflection\Core\Type;

use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Core\Name;
use Phpactor\WorseReflection\Core\Type;

final class ClassType implements Type
{
    public ClassName $name;

    public function __construct(ClassName $name)
    {
        $this->name = $name;
    }
}
