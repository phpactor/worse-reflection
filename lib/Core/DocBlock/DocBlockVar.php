<?php

namespace Phpactor\WorseReflection\Core\DocBlock;

use Phpactor\WorseReflection\Core\Type;

class DocBlockVar
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var Type
     */
    private $type;

    public function __construct(string $name, Type $type)
    {
        $this->name = $name;
        $this->type = $type;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function type(): Type
    {
        return $this->type;
    }

    public static function fromVarNameAndType(string $varName, string $type)
    {
        return new self($varName, Type::fromString($type));
    }
}
