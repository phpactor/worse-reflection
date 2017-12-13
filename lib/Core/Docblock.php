<?php

namespace Phpactor\WorseReflection\Core;

interface Docblock
{
    public function isDefined(): bool;

    public function raw(): string;

    public function formatted(): string;

    public function returnTypes(): array;

    public function methodTypes(string $methodName): array;

    public function varTypes(): array;

    public function inherits(): bool;
}
