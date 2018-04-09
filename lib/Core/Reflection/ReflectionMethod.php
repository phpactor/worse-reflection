<?php

namespace Phpactor\WorseReflection\Core\Reflection;

use Phpactor\WorseReflection\Core\Position;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\DocBlock\DocBlock;
use Phpactor\WorseReflection\Core\Visibility;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\NodeText;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionParameterCollection;
use Phpactor\WorseReflection\Core\Types;

interface ReflectionMethod extends ReflectionMember
{
    public function parameters(): ReflectionParameterCollection;

    /**
     * @deprecated - use type()
     */
    public function returnType(): Type;

    public function body(): NodeText;

    public function isAbstract(): bool;

    public function isStatic(): bool;
}
