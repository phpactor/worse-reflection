<?php

namespace Phpactor\WorseReflection\Bridge\Phpactor;

use Phpactor\WorseReflection\Core\DocBlock\DocBlockFactory as CoreDocblockPhpactory;
use Phpactor\WorseReflection\Core\DocBlock\DocBlock as CoreDocblock;
use Phpactor\Docblock\DocblockFactory as PhpactorDocblockFactory;

class DocblockFactory implements CoreDocblockPhpactory
{
    
    private PhpactorDocblockFactory $factory;

    public function __construct(PhpactorDocblockFactory $factory = null)
    {
        $this->factory = $factory ?: new PhpactorDocblockFactory();
    }

    public function create(string $docblock): CoreDocblock
    {
        return new Docblock($docblock, $this->factory->create($docblock));
    }
}
