<?php

namespace Phpactor\WorseReflection\Core\Reflection;

use Phpactor\WorseReflection\Core\Position;
use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Core\SourceCode;
use Phpactor\WorseReflection\Core\Docblock;
use Phpactor\WorseReflection\Core\Reflection\HasPosition;

interface ReflectionClassLike extends HasPosition
{
    public function name(): ClassName;

    public function sourceCode(): SourceCode;

    public function isInterface();

    public function isTrait();

    public function isConcrete();

    public function docblock(): Docblock;
}
