<?php

namespace Phpactor\WorseReflection\Core;

use Closure;

interface Cache
{
    /**
     * @param Closure(): mixed $closure
     */
    public function getOrSet(string $key, Closure $closure);

    public function purge(): void;
}
