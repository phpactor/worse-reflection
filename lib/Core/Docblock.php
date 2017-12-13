<?php

namespace Phpactor\WorseReflection\Core;

interface Docblock
{
    public function isDefined(): bool;

    public function raw(): string;

    public function formatted(): string;

    public function returnTypes(): DocblockTypes;

    public function methodTypes(string $methodName): DocblockTypes;

    public function varTypes(): DocblockTypes;

    public function inherits(): bool;
}
