<?php

namespace Phpactor\WorseReflection\Tests\Unit\Bridge\TolerantParser\Parser;

use PHPUnit\Framework\TestCase;
use Phpactor\WorseReflection\Bridge\TolerantParser\Parser\CachedParser;
use Phpactor\WorseReflection\Core\Cache;
use Phpactor\WorseReflection\Core\Cache\TtlCache;

class CachedParserTest extends TestCase
{
    public function testCachesResults()
    {
        $parser = new CachedParser(new TtlCache());
        $node1 = $parser->parseSourceFile(file_get_contents(__FILE__));
        $node2 = $parser->parseSourceFile(file_get_contents(__FILE__));

        $this->assertSame($node1, $node2);
    }

    public function testReturnsDifferentResultsForDifferentSourceCodes()
    {
        $parser = new CachedParser(new TtlCache());
        $node1 = $parser->parseSourceFile(file_get_contents(__FILE__));
        $node2 = $parser->parseSourceFile('Foobar' . file_get_contents(__FILE__));

        $this->assertNotSame($node1, $node2);
    }
}
