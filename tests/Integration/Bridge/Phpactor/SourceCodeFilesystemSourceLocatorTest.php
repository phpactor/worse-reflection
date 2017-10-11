<?php

namespace Phpactor\WorseReflection\Tests\Integration\Bridge\Phpactor;

use PHPUnit\Framework\TestCase;
use Phpactor\Filesystem\Adapter\Simple\SimpleFilesystem;
use Phpactor\Filesystem\Domain\FilePath;
use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Bridge\Phpactor\SourceCodeFilesystemSourceLocator;
use Phpactor\WorseReflection\Tests\Integration\IntegrationTestCase;
use Phpactor\WorseReflection\Core\SourceCode;
use Phpactor\WorseReflection\Core\Exception\SourceNotFound;

class SourceCodeFilesystemSourceLocatorTest extends IntegrationTestCase
{
    const NON_EXISTING_CLASS_NAME = 'ThisIsANameThatWillNeverExist';

    public function setUp()
    {
        $this->locator = new SourceCodeFilesystemSourceLocator(
            new SimpleFilesystem(
                FilePath::fromString(__DIR__ . '/files')
            ),
            $this->createReflector('')
        );
    }

    public function testLocate()
    {
        $source = $this->locator->locate(ClassName::fromString('Acme\\NamespacedClass'));
        $this->assertPath('Folder1/NamespacedClass.php', $source);
    }

    public function testLocateNoNamespace()
    {
        $source = $this->locator->locate(ClassName::fromString('DB_ManagerNEW1'));
        $this->assertPath('Folder1/LEGACY/DB_ManagerNEW1.php', $source);
    }

    public function testNotFound()
    {
        $this->expectException(SourceNotFound::class);
        $source = $this->locator->locate(ClassName::fromString(self::NON_EXISTING_CLASS_NAME));
        $this->assertEquals(__FILE__, $source->path());
    }

    private function assertPath(string $expectedPath, SourceCode $source)
    {
        $this->assertEquals(__DIR__ . '/files/' . $expectedPath, $source->path());
    }
}

