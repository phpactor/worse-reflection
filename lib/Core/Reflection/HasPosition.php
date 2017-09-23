<?php

namespace Phpactor\WorseReflection\Core\Reflection;

use Phpactor\WorseReflection\Core\Position;

interface HasPosition
{
    public function position(): Position;
}
