<?php

namespace Phpactor\WorseReflection\Core\Virtual;

use Phpactor\WorseReflection\Core\DefaultValue;
use Phpactor\WorseReflection\Core\Position;
use Phpactor\WorseReflection\Core\Reflection\ReflectionFunctionLike;
use Phpactor\WorseReflection\Core\Reflection\ReflectionParameter;
use Phpactor\WorseReflection\Core\Reflection\ReflectionScope;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\Types;

class VirtualReflectionParameter implements ReflectionParameter
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var ReflectionFunctionLike
     */
    private $functionLike;

    /**
     * @var Types
     */
    private $inferredTypes;

    /**
     * @var Type
     */
    private $type;

    /**
     * @var DefaultValue
     */
    private $default;

    /**
     *
     * @var bool
     */
    private $byReference;

    /**
     * @var ReflectionScope
     */
    private $scope;

    /**
     * @var Position
     */
    private $position;

    public function __construct(
        string $name,
        ReflectionFunctionLike $functionLike,
        Types $inferredTypes,
        Type $type,
        DefaultValue $default,
        bool $byReference,
        ReflectionScope $scope,
        Position $position
    ) {
        $this->name = $name;
        $this->functionLike = $functionLike;
        $this->inferredTypes = $inferredTypes;
        $this->type = $type;
        $this->default = $default;
        $this->byReference = $byReference;
        $this->scope = $scope;
        $this->position = $position;
    }

    public function scope(): ReflectionScope
    {
        return $this->scope;
    }

    public function position(): Position
    {
        return $this->position;
    }

    public function name(): string
    {
        return $this->name;
    }

    /**
     * {@inheritDoc}
     */
    public function method(): ReflectionFunctionLike
    {
        return $this->functionLike;
    }

    public function functionLike(): ReflectionFunctionLike
    {
        return $this->functionLike;
    }

    public function type(): Type
    {
        return $this->type;
    }

    public function inferredTypes(): Types
    {
        return $this->inferredTypes;
    }

    public function default(): DefaultValue
    {
        return $this->default;
    }

    public function byReference(): bool
    {
        return $this->byReference;
    }

    public function isPromoted(): bool
    {
        return false;
    }
}
