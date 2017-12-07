<?php

namespace Phpactor\WorseReflection\Tests\Integration\Core;

use PHPUnit\Framework\TestCase;
use Phpactor\WorseReflection\Reflector;
use Phpactor\WorseReflection\Tests\Integration\IntegrationTestCase;
use DirectoryIterator;
use SplFileInfo;

class DiagnoseTest extends IntegrationTestCase
{
    /**
     * @dataProvider provideDiagnose
     */
    public function testDiagnose(string $testName, string $source, array $assertions)
    {
        $reflector = $this->createReflector($source);
        $problems = $reflector->diagnose($source);

        $this->assertContains($assertions['message'], (string) $problems, $testName);
    }

    public function provideDiagnose()
    {
        return $this->parseTests();
    }

    private function parseTests()
    {
        $tests = [];
        $directoryIterator = new DirectoryIterator(__DIR__ . '/diagnosis');

        /** @var SplFileInfo $testFile */
        foreach ($directoryIterator as $testFile) {
            if (false === $testFile->isFile()) {
                continue;
            }

            $testContents = file_get_contents($testFile->getPathname());
            list($source, $assertions) = preg_split('{<--->}', $testContents);
            preg_match_all('{(.*):\s*(.*)}', $assertions, $matches);
            $assertions = array_combine($matches[1], $matches[2]);

            $tests[] = [
                'name' => $testFile->getFilename(),
                'source' => $source,
                'assertions' => $assertions
            ];
        }

        return $tests;
    }
}
