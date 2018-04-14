<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\Parser;

use Microsoft\PhpParser\Node\SourceFileNode;
use Microsoft\PhpParser\Parser;

class CachedParser extends Parser
{
    /**
     * @var array
     */
    private $cache = [];

    public function parseSourceFile(string $source, string $uri = null): SourceFileNode
    {
        if (isset($this->cache[$source])) {
            return $this->cache[$source];
        }

        $node = parent::parseSourceFile($source);

        $this->cache[$source] = $node;

        return $node;
    }
}
