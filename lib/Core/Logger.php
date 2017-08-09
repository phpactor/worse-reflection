<?php

namespace Phpactor\WorseReflection\Core;

interface Logger
{
    public function warning(string $message);

    public function debug(string $message);
}
