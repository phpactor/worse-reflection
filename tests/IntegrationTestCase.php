<?php

namespace DTL\WorseReflection\Tests;

use DTL\WorseReflection\Reflector;
use DTL\WorseReflection\SourceLocator;
use PhpParser\ParserFactory;
use DTL\WorseReflection\SourceContextFactory;
use DTL\WorseReflection\SourceLocator\ComposerSourceLocator;
use DTL\WorseReflection\Source;
use DTL\WorseReflection\SourceLocator\StringSourceLocator;
use PhpParser\Lexer;

class IntegrationTestCase extends \PHPUnit_Framework_TestCase
{
    public function getOffsetAndSource(string $source)
    {
        $source = '<?php ' . $source;
        $offset = strpos($source, '_');

        if (false !== $offset) {
            $source = substr($source, 0, $offset) . substr($source, $offset + 1);
        }

        return [ $offset, Source::fromString($source) ];
    }

    public function getReflectorForSource(Source $source)
    {
        return new Reflector(
            new StringSourceLocator($source),
            new SourceContextFactory($this->getParser())
        );
    }

    public function getParser()
    {
        $lexer = new Lexer([ 'usedAttributes' => [ 'comments', 'startLine', 'endLine', 'startFilePos', 'endFilePos' ] ]);
        return (new ParserFactory)->create(ParserFactory::PREFER_PHP7, $lexer, []);
    }
}
