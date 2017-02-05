<?php

namespace DTL\WorseReflection\Evaluation;

class RawType
{
    private $type;

    public static function unknown()
    {
        $instance = new self();
        return $instance;
    }

    public static function fromString(string $type = null)
    {
        $instance = new self();
        $instance->type = $type;

        return $instance;
    }
}
