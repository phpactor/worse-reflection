<?php

namespace Phpactor\WorseReflection\Core\Type;

use Phpactor\WorseReflection\Core\Name;
use Phpactor\WorseReflection\Core\Type;

final class ClassType implements Type
{
    public ?Name $name;

    public function __construct(?Name $name)
    {
        $this->name = $name;
    }
}
