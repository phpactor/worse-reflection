<?php

namespace Phpactor\WorseReflection\Core\Inference;

use Phpactor\WorseReflection\Core\Offset;

class Problem
{
    public const UNDEFINED = 'unknown';
    public const CLASS_NOT_FOUND = 'class_not_found';
    public const VARIABLE_UNDEFINED = 'variable_undefined';
    public const METHOD_NOT_FOUND = 'method_not_found';
    public const PROPERTY_NOT_FOUND = 'property_not_found';
    public const CONSTANT_NOT_FOUND = 'constant_not_found';

    /**
     * @var string
     */
    private $code;
    /**
     * @var string
     */
    private $message;
    /**
     * @var int
     */
    private $start;
    /**
     * @var int
     */
    private $end;

    public function __construct(string $code, string $message, int $start, int $end = null)
    {
        $this->code = $code;
        $this->message = $message;
        $this->start = $start;
        $this->end = $end;
    }

    public function message(): string
    {
        return $this->message;
    }

    public function code(): string
    {
        return $this->code;
    }
}
