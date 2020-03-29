<?php

namespace Phpactor\WorseReflection\Core\Cache;

use Phpactor\WorseReflection\Core\Cache;

class NullCache implements Cache
{
    public function put(string $key, $value, $ttl = null)
    {
    }

    public function get(string $key, $value)
    {
        return null;
    }
}
