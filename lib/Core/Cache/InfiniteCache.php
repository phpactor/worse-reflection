<?php

namespace Phpactor\WorseReflection\Core\Cache;

use Closure;
use Phpactor\WorseReflection\Core\Cache;

class InfiniteCache implements Cache
{
    private $cache = [];

    public function getOrSet(string $key, Closure $setter)
    {
        if (isset($this->cache[$key])) {
            return $this->cache[$key];
        }

        $this->cache[$key] = $setter();

        return $this->cache[$key];
    }
}
