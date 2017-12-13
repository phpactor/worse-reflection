<?php

namespace Phpactor\WorseReflection\Bridge\Phpactor;

use Phpactor\WorseReflection\Core\DocblockInspector;
use Phpactor\Docblock\DocblockFactory;
use Phpactor\Docblock\Tag\MethodTag;

class PhpactorDocblockInspector implements DocblockInspector
{
    /**
     * @var DocblockFactory
     */
    private $factory;

    public function __construct(DocblockFactory $factory)
    {
        $this->factory = $factory;
    }

    public function methodTypeMap(string $docblock): array
    {
        $docblock = $this->factory->create($docblock);
        $map = [];

        /** @var MethodTag $methodTag */
        foreach ($docblock->tags()->byName('method') as $methodTag) {
            if (!isset($map[$methodTag->methodName()])) {
                $map[$methodTag->methodName()] = [];
            }

            $map[$methodTag->methodName()] = $methodTag->types()->toArray();
        }

        return $map;
    }
}
