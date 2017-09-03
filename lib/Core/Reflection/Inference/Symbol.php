<?php

namespace Phpactor\WorseReflection\Core\Reflection\Inference;

use Phpactor\WorseReflection\Core\Position;

final class Symbol
{
    const CLASS_ = 'class';
    const VARIABLE = 'variable';
    const METHOD = 'method';
    const PROPERTY = 'property';
    const CONSTANT = 'constant';
    const UNKNOWN = '<unknown>';

    const VALID_SYMBOLS = [
        self::CLASS_,
        self::VARIABLE,
        self::UNKNOWN,
        self::PROPERTY,
        self::CONSTANT,
        self::METHOD,
    ];

    /**
     * @var string
     */
    private $symbolType;

    /**
     * @var string
     */
    private $name;

    /**
     * @var Position
     */
    private $position;

    private function __construct(string $symbolType, string $name, Position $position)
    {
        $this->symbolType = $symbolType;
        $this->name = $name;
        $this->position = $position;
    }

    public static function unknown(): Symbol
    {
        return new self('<unknown>', '<unknown>', Position::fromStartAndEnd(0, 0));
    }

    public static function assertValidSymbolType(string $symbolType)
    {
        if (false === in_array($symbolType, self::VALID_SYMBOLS)) {
            throw new \InvalidArgumentException(sprintf(
                'Invalid symbol type "%s", valid symbol names: "%s"',
                $symbolType,
                implode('", "', self::VALID_SYMBOLS)
            ));
        }
    }

    public static function fromTypeNameAndPosition(string $symbolType, string $name, Position $position): Symbol
    {
        self::assertValidSymbolType($symbolType);
        return new self($symbolType, $name, $position);
    }

    public function __toString()
    {
        return sprintf('%s:%s [%s] %s', $this->position->start(), $this->position->end(), $this->symbolType, $this->name);
    }

    public function symbolType(): string
    {
        return $this->symbolType;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function position(): Position
    {
        return $this->position;
    }
}
