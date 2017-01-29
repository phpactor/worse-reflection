<?php

namespace DTL\WorseReflection\Reflection;

class ReflectionDocComment
{
    private $rawComment;

    private function __construct()
    {
    }

    public static function fromRaw($text)
    {
        $instance = new self();
        $instance->rawComment = $text;

        return $instance;
    }

    public function getRaw()
    {
        return $this->rawComment;
    }
}
