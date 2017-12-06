<?php

namespace Phpactor\WorseReflection\Tests\Integration\Core;

use PHPUnit\Framework\TestCase;
use Phpactor\WorseReflection\Reflector;
use Phpactor\WorseReflection\Tests\Integration\IntegrationTestCase;

class LinterTest extends IntegrationTestCase
{
    /**
     * @dataProvider provideLinter
     */
    public function testLinter(string $sourceCode, string $expectedMessage)
    {
        $logger = $this->logger();
        $errorString = (string) Reflector::create(null, $logger)->lint($sourceCode);
        $this->assertContains($expectedMessage, $errorString);
    }

    public function provideLinter()
    {
        return [
            [
                <<<'EOT'
<?php

$bar = Barfoo::foobar();
EOT
                ,
                'Could not find container class "Barfoo"',
            ]
        ];
    }
}
