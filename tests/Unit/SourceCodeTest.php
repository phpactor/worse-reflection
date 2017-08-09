<?php

namespace Phpactor\WorseReflection\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Phpactor\WorseReflection\Core\SourceCode;

class SourceCodeTest extends TestCase
{
    /**
     * @testdox It can load source code from a file.
     */
    public function testFromPath()
    {
        $code = SourceCode::fromPath(__FILE__);
        $this->assertEquals(file_get_contents(__FILE__), (string) $code);
    }

    /**
     * @testdox It throws an exception if file not found.
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage File "Improbable_Path_62.xyz" does not exist
     */
    public function testFileNotFound()
    {
        SourceCode::fromPath('Improbable_Path_62.xyz');
    }
}
