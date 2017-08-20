<?php

namespace Phpactor\WorseReflection\Tests\Integration\Bridge\Phpactor;

use Phpactor\WorseReflection\Tests\Integration\IntegrationTestCase;
use Phpactor\ClassFileConverter\ClassToFileConverter;
use Phpactor\WorseReflection\Bridge\Phpactor\ClassToFileSourceLocator;
use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Core\Exception\SourceNotFound;

class ClassToFileSourceLocatorTest extends IntegrationTestCase
{
    /**
     * @var ClassToFileSourceLocator
     */
    private $locator;

    public function setUp()
    {
        $classToFile = ClassToFileConverter::fromComposerAutoloader(include(__DIR__ . '/../../../../vendor/autoload.php'));
        $this->locator = new ClassToFileSourceLocator($classToFile);
    }
    /**
     * It should locate source.
     */
    public function testLocator()
    {
        $source = $this->locator->locate(ClassName::fromString(__CLASS__));
        $this->assertEquals(file_get_contents(__FILE__), (string) $source);
    }

    /**
     * It should throw an exception if class was not found.
     */
    public function testLocateNotFound()
    {
        $this->expectException(SourceNotFound::class);
        $this->locator->locate(ClassName::fromString('asdDSA'));
    }
}
