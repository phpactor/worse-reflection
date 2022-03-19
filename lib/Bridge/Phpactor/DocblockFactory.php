<?php

namespace Phpactor\WorseReflection\Bridge\Phpactor;

use Phpactor\WorseReflection\Core\DocBlock\DocBlockFactory as CoreDocblockPhpactory;
use Phpactor\WorseReflection\Core\DocBlock\DocBlock as CoreDocblock;
use Phpactor\Docblock\DocblockFactory as PhpactorDocblockFactory;
use Phpactor\WorseReflection\Reflector;

class DocblockFactory implements CoreDocblockPhpactory
{
    private PhpactorDocblockFactory $factory;

    private Reflector $reflector;

    public function __construct(Reflector $reflector, PhpactorDocblockFactory $factory = null)
    {
        $this->factory = $factory ?: new PhpactorDocblockFactory();
        $this->reflector = $reflector;
    }

    public function create(string $docblock): CoreDocblock
    {
        return new Docblock($docblock, $this->factory->create($docblock), $this->reflector);
    }
}
