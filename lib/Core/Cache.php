<?php

namespace Phpactor\WorseReflection\Core;

use Closure;

interface Cache
{
    public function getOrSet(string $key, Closure $closure);
}
