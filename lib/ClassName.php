<?php

namespace DTL\WorseReflection;

class ClassName
{
    private $classFqn;

    private function __construct()
    {
    }

    public static function fromFqn(string $classFqn)
    {
        $instance = new self();
        $instance->classFqn = $classFqn;

        return $instance;
    }

    public function getFqn()
    {
        return $this->classFqn;
    }
}
