<?php

namespace Phpactor\WorseReflection\Core;

interface Type
{
    public function __toString(): string;
    public function toPhpString(): string;
}
