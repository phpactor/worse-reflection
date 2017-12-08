<?php

namespace Phpactor\WorseReflection\Tests\Unit\Core\Inference;

use PHPUnit\Framework\TestCase;
use Phpactor\WorseReflection\Core\Inference\Problems;
use Phpactor\WorseReflection\Core\Inference\SymbolInformation;

class ProblemsTest extends TestCase
{
    public function testMerge()
    {
        $s1 = SymbolInformation::none();
        $s2 = SymbolInformation::none();
        $s3 = SymbolInformation::none();
        $s4 = SymbolInformation::none();

        $p1 = Problems::create();
        $p1->add($s1);
        $p1->add($s2);

        $p2 = Problems::create();
        $p2->add($s3);
        $p2->add($s4);

        $p3 = $p2->merge($p1);

        $this->assertEquals([ $s3, $s4, $s1, $s2 ], $p3->toArray());
    }
}
