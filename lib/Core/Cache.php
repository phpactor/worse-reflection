<?php

namespace Phpactor\WorseReflection\Core;

interface Cache
{
    public function put(string $key, $value, $ttl = null);

    public function get(string $key, $value);
}
