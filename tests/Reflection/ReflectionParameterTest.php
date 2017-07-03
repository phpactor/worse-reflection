<?php

namespace DTL\WorseReflection\Tests\Reflection;

use DTL\WorseReflection\Tests\ReflectionTestCase;
use DTL\WorseReflection\ClassName;
use DTL\WorseReflection\Reflection\ReflectionParameter;
use DTL\WorseReflection\Visibility;
use DTL\WorseReflection\Type;

class ReflectionParameterTest extends ReflectionTestCase
{
    /**
     * @dataProvider provideReflectionParameter
     */
    public function testReflectParameter(string $source, \Closure $assertion)
    {
        $source = sprintf('<?php namespace Acme; class Foobar { public function method(%s) }', $source);
        $class = $this->createReflector($source)->reflectClass(ClassName::fromString('Acme\Foobar'));
        $assertion($class->methods()->get('method'));
    }

    public function provideReflectionParameter()
    {
        return [
            'It reflects a an empty list with no parameters' => [
                '',
                function ($method) {
                    $this->assertCount(0, $method->parameters());
                },
            ],
            'It reflects a single parameter' => [
                '$foobar',
                function ($method) {
                    $this->assertCount(1, $method->parameters());
                    $parameter = $method->parameters()->get('foobar');
                    $this->assertInstanceOf(ReflectionParameter::class, $parameter);
                    $this->assertEquals('foobar', $parameter->name());
                },
            ],
            'It returns false if the parameter has no type' => [
                '$foobar',
                function ($method) {
                    $this->assertFalse($method->parameters()->get('foobar')->hasType());
                },
            ],
            'It returns the parameter type' => [
                'Foobar $foobar',
                function ($method) {
                    $this->assertEquals(Type::fromString('Acme\Foobar'), $method->parameters()->get('foobar')->type());
                },
            ],
            'It returns false if the parameter has no default' => [
                '$foobar',
                function ($method) {
                    $this->assertFalse($method->parameters()->get('foobar')->hasDefault());
                },
            ],
            'It returns the default value for a string' => [
                '$foobar = "foo"',
                function ($method) {
                    $this->assertTrue($method->parameters()->get('foobar')->hasDefault());
                    $this->assertEquals(
                        'foo', $method->parameters()->get('foobar')->default()
                    );
                },
            ],
            'It returns the default value for a number' => [
                '$foobar = 1234',
                function ($method) {
                    $this->assertEquals(
                        1234, $method->parameters()->get('foobar')->default()
                    );
                },
            ],
            'It returns the default value for an array' => [
                '$foobar = [ "foobar" ]',
                function ($method) {
                    $this->assertEquals(
                        ['foobar'], $method->parameters()->get('foobar')->default()
                    );
                },
            ],
            'It returns the default value for null' => [
                '$foobar = null',
                function ($method) {
                    $this->assertEquals(
                        null, $method->parameters()->get('foobar')->default()
                    );
                },
            ],
            'It returns the default value for empty array' => [
                '$foobar = []',
                function ($method) {
                    $this->assertEquals(
                        [], $method->parameters()->get('foobar')->default()
                    );
                },
            ],
            'It returns the default value for a boolean' => [
                '$foobar = false',
                function ($method) {
                    $this->assertEquals(
                        false, $method->parameters()->get('foobar')->default()
                    );
                },
            ],
        ];
    }
}
