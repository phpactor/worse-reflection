<?php

namespace DTL\WorseReflection;

use PhpParser\Node;
use PhpParser\Node\Scalar;
use PhpParser\Node\Expr;

class Type
{
    const TYPE_STRING = 'string';
    const TYPE_INT = 'int';
    const TYPE_FLOAT = 'float';
    const TYPE_ARRAY = 'array';
    const TYPE_CLASS = 'class';

    private $type;
    private $className;

    /**
     * Create from a parser node from which the type can be directly inferred.
     */
    public static function fromParserNode(SourceContext $context, Node $node): Type
    {
        if ($node instanceof Scalar\String_) {
            return self::string();
        }

        if ($node instanceof Scalar\LNumber) {
            return self::int();
        }

        if ($node instanceof Scalar\DNumber) {
            return self::float();
        }

        if ($node instanceof Scalar\ArrayNode) {
            return self::array();
        }

        if ($node instanceof Node\Name) {
            return self::fromString($context, (string) $node);
        }

        if ($node instanceof Node\Param) {
            return self::fromString($context, (string) $node->type);
        }

        if ($node instanceof Node\Stmt\Class_) {
            return self::fromString($context, (string) $node->name);
        }

        if ($node instanceof Scalar\Encapsed) {
            return self::string();
        }

        if ($node instanceof Scalar\EncapsedStringPart) {
            return self::string();
        }

        if ($node instanceof Scalar\MagicConst) {
            if ($node instanceof Scalar\MagicConst\Line) {
                return self::int();
            }

            return self::string();
        }

        if ($node instanceof Expr\New_) {
            return self::fromString($context, (string) $node->class);
        }

        return self::unknown();
    }

    public static function fromString(SourceContext $context, string $type): Type
    {
        if ('' === $type) {
            return self::unknown();
        }

        if ($type === 'string') {
            return self::string();
        }

        if ($type === 'int') {
            return self::int();
        }

        if ($type === 'float') {
            return self::float();
        }

        if ($type === 'array') {
            return self::array();
        }


        return self::class($context->resolveClassName(ClassName::fromString($type)));
    }

    public static function unknown()
    {
        return new self();
    }

    public static function string()
    {
        return self::create(self::TYPE_STRING);
    }

    public static function array()
    {
        return self::create(self::TYPE_ARRAY);
    }

    public static function int()
    {
        return self::create(self::TYPE_INT);
    }

    public static function float()
    {
        return self::create(self::TYPE_FLOAT);
    }

    public static function class(ClassName $className)
    {
        $instance = new self();
        $instance->type = self::TYPE_CLASS;
        $instance->className = $className;

        return $instance;
    }

    public static function none()
    {
        return new self();
    }

    public function __toString()
    {
        return $this->isClass() ? $this->className->getFqn() : ($this->type ?: '<unknown>');
    }

    public function getClassName()
    {
        return $this->className;
    }

    public function isClass()
    {
        return (bool) $this->className;
    }

    private static function create($type)
    {
        $instance = new self();
        $instance->type = $type;

        return $instance;
    }
}
