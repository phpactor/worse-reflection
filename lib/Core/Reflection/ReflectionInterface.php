<?php

namespace Phpactor\WorseReflection\Core\Reflection;

use Phpactor\WorseReflection\Core\Position;
use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Core\SourceCode;
use Phpactor\WorseReflection\Core\Docblock;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionConstantCollection;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionInterfaceCollection;

interface ReflectionInterface extends ReflectionClassLike
{
    public function constants(): ReflectionConstantCollection;

    public function parents(): ReflectionInterfaceCollection;

    public function isInstanceOf(ClassName $className): bool;
}
