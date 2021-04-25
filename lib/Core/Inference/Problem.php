<?php

namespace Phpactor\WorseReflection\Core\Inference;

use Phpactor\WorseReflection\Core\Offset;

class Problem
{
    public const UNDEFINED = 'unknown';
    public const CLASS_NOT_FOUND = 'class_not_found';
    public const VARIABLE_UNDEFINED = 'variable_undefined';

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
}
