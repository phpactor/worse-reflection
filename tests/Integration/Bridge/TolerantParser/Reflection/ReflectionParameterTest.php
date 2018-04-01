<?php

namespace Phpactor\WorseReflection\Tests\Integration\Bridge\TolerantParser\Reflection;

use Phpactor\WorseReflection\Tests\Integration\IntegrationTestCase;
use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\Reflection\ReflectionParameter;

class ReflectionParameterTest extends IntegrationTestCase
{
    /**
     * @dataProvider provideReflectionParameter
     */
    public function testReflectParameter(string $source, \Closure $assertion)
    {
        $source = sprintf('<?php namespace Acme; class Foobar { public function method(%s) }', $source);
        $class = $this->createReflector($source)->reflectClassLike(ClassName::fromString('Acme\Foobar'));
        $assertion($class->methods()->get('method'));
    }

    public function provideReflectionParameter()
    {
        yield 'It reflects a an empty list with no parameters' => [
            '',
            function ($method) {
                $this->assertCount(0, $method->parameters());
            },
        ];

        yield 'It reflects a single parameter' => [
            '$foobar',
            function ($method) {
                $this->assertCount(1, $method->parameters());
                $parameter = $method->parameters()->get('foobar');
                $this->assertInstanceOf(ReflectionParameter::class, $parameter);
                $this->assertEquals('foobar', $parameter->name());
            },
        ];

        yield 'It returns false if the parameter has no type' => [
            '$foobar',
            function ($method) {
                $this->assertFalse($method->parameters()->get('foobar')->type()->isDefined());
            },
        ];

        yield 'It returns the parameter type' => [
            'Foobar $foobar',
            function ($method) {
                $this->assertEquals(Type::fromString('Acme\Foobar'), $method->parameters()->get('foobar')->type());
            },
        ];

        yield 'It returns false if the parameter has no default' => [
            '$foobar',
            function ($method) {
                $this->assertFalse($method->parameters()->get('foobar')->default()->isDefined());
            },
        ];

        yield 'It returns the default value for a string' => [
            '$foobar = "foo"',
            function ($method) {
                $this->assertTrue($method->parameters()->get('foobar')->default()->isDefined());
                $this->assertEquals(
                    'foo',
                    $method->parameters()->get('foobar')->default()->value()
                );
            },
        ];

        yield 'It returns the default value for a number' => [
            '$foobar = 1234',
            function ($method) {
                $this->assertEquals(
                    1234,
                    $method->parameters()->get('foobar')->default()->value()
                );
            },
        ];

        yield 'It returns the default value for an array' => [
            '$foobar = [ "foobar" ]',
            function ($method) {
                $this->assertEquals(
                    ['foobar'],
                    $method->parameters()->get('foobar')->default()->value()
                );
            },
        ];

        yield 'It returns the default value for null' => [
            '$foobar = null',
            function ($method) {
                $this->assertEquals(
                    null,
                    $method->parameters()->get('foobar')->default()->value()
                );
            },
        ];

        yield 'It returns the default value for empty array' => [
            '$foobar = []',
            function ($method) {
                $foobar = $method->parameters()->get('foobar');
                $this->assertEquals(
                    [],
                    $foobar->default()->value()
                );
            },
        ];

        yield 'It returns the default value for a boolean' => [
            '$foobar = false',
            function ($method) {
                $this->assertEquals(
                    false,
                    $method->parameters()->get('foobar')->default()->value()
                );
            },
        ];

        yield 'Passed by reference' => [
            '&$foobar',
            function ($method) {
                $this->assertTrue(
                    $method->parameters()->get('foobar')->byReference()
                );
            },
        ];

        yield 'Not passed by reference' => [
            '$foobar',
            function ($method) {
                $this->assertFalse(
                    $method->parameters()->get('foobar')->byReference()
                );
            },
        ];
    }

    /**
     * @dataProvider provideReflectionParameterWithDocblock
     */
    public function testReflectParameterWithDocblock(string $source, string $docblock, \Closure $assertion)
    {
        $source = sprintf('<?php namespace Acme; class Foobar { %s public function method(%s) }', $docblock, $source);
        $class = $this->createReflector($source)->reflectClassLike(ClassName::fromString('Acme\Foobar'));
        $assertion($class->methods()->get('method'));
    }

    public function provideReflectionParameterWithDocblock()
    {
        yield 'It returns docblock parameter type' => [
            '$foobar',
            '/** @param Foobar $foobar */',
            function ($method) {
                $this->assertCount(1, $method->parameters());
                $this->assertEquals('Acme\Foobar', (string) $method->parameters()->get('foobar')->inferredTypes()->best());
            },
        ];
    }
}
