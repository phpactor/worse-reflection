<?php

namespace Phpactor\WorseReflection\Core\Reflection;

use Phpactor\WorseReflection\Core\Position;
use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\Collection\ReflectionMethodCollection;
use Phpactor\WorseReflection\Core\SourceCode;
use bool;
use Phpactor\WorseReflection\Core\Docblock;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\Collection\ReflectionPropertyCollection;

interface ReflectionTrait
{
    public function position(): Position;

    public function name(): ClassName;

    public function methods(): ReflectionMethodCollection;

    public function sourceCode(): SourceCode;

    public function isInterface(): bool;

    public function isTrait(): bool;

    public function isClass(): bool;

    public function isConcrete();

    public function docblock(): Docblock;

    public function properties(): ReflectionPropertyCollection;
}