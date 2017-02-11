<?php

namespace DTL\WorseReflection;

interface NameLike
{
    public function getFqn(): string;

    public function getParts(): array;
}
