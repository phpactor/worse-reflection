<?php

namespace Phpactor\WorseReflection\Core\Inference;

use Phpactor\WorseReflection\Core\Offset;

class Problem
{
    public const UNDEFINED = 'unknown';

    /**
     * @var string
     */
    private $code;
    /**
     * @var string
     */
    private $message;
    /**
     * @var Offset
     */
    private $start;
    /**
     * @var Offset|null
     */
    private $end;

    public function __construct(string $code, string $message, Offset $start, Offset $end = null)
    {
        $this->code = $code;
        $this->message = $message;
        $this->start = $start;
        $this->end = $end;
    }
}
